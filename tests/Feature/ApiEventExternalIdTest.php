<?php

namespace Tests\Feature;

use App\Jobs\SendWebhook;
use App\Models\Event;
use App\Models\Role;
use App\Models\User;
use App\Models\Webhook;
use App\Services\BackupService;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Sleep;
use Illuminate\Support\Str;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * events.external_id: the id another system knows an event by.
 *
 * It exists so that a sync can find the event it made last time. Everything here is about that
 * holding: the id is unique for the schedule that OWNS the event and compared exactly, it cannot
 * be reached from the event form, only the owning schedule's admins can change it, and the two
 * things that move events between schedules (a merge, a backup) do not trip over it.
 */
class ApiEventExternalIdTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.hosted' => true]);
        Http::preventStrayRequests();
    }

    private function keyFor(User $owner): array
    {
        $raw = 'testapikey_'.Str::random(24);
        $owner->api_key = substr(hash('sha256', $raw), 0, 8);
        $owner->api_key_hash = Hash::make($raw);
        $owner->save();

        return ['X-API-Key' => $raw];
    }

    /** @return array{0: User, 1: Role, 2: array<string, string>} */
    private function schedule(string $type = 'talent'): array
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, $type);

        return [$owner, $role, $this->keyFor($owner)];
    }

    private function body(array $overrides = []): array
    {
        return $overrides + [
            'name' => 'From the other system',
            'starts_at' => now()->addWeek()->setTime(18, 0)->format('Y-m-d H:i:s'),
        ];
    }

    private function create(Role $role, array $key, array $overrides = [])
    {
        return $this->postJson('/api/events/'.$role->subdomain, $this->body($overrides), $key);
    }

    public function test_create_stores_it_returns_it_and_the_webhook_already_carries_it(): void
    {
        [$owner, $role, $key] = $this->schedule();
        Webhook::create([
            'user_id' => $owner->id,
            'url' => 'https://example.test/hook',
            'secret' => 'shh',
            'event_types' => ['event.created'],
            'is_active' => true,
        ]);

        Bus::fake();
        $id = $this->create($role, $key, ['external_id' => 'post-4686933'])
            ->assertCreated()
            ->assertJsonPath('data.external_id', 'post-4686933')
            ->json('data.id');

        $this->assertSame('post-4686933', Event::find(UrlUtils::decodeId($id))->external_id);
        Bus::assertDispatched(SendWebhook::class, function (SendWebhook $job) {
            return (fn () => $this->payload['data']->external_id)->call($job) === 'post-4686933';
        });
    }

    public function test_an_event_made_without_one_reads_null(): void
    {
        [, $role, $key] = $this->schedule();

        $this->create($role, $key)->assertCreated()->assertJsonPath('data.external_id', null);
        $this->create($role, $key, ['name' => 'Blank', 'external_id' => '   '])->assertCreated()->assertJsonPath('data.external_id', null);
        $this->assertSame(2, Event::whereNull('external_id')->count());
    }

    public function test_a_number_is_taken_as_its_digits_and_spaces_around_an_id_are_dropped(): void
    {
        [, $role, $key] = $this->schedule();

        $this->create($role, $key, ['external_id' => 4686933])->assertCreated()->assertJsonPath('data.external_id', '4686933');
        $this->create($role, $key, ['name' => 'Padded', 'external_id' => '  abc  '])->assertCreated()->assertJsonPath('data.external_id', 'abc');

        // And the lookup finds it however it was typed.
        $this->getJson('/api/events?external_id=%20abc%20', $key)->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.name', 'Padded');
    }

    public function test_something_that_is_not_text_or_is_too_long_is_refused_and_nothing_is_made(): void
    {
        [, $role, $key] = $this->schedule();

        $this->create($role, $key, ['external_id' => ['a']])->assertStatus(422)->assertJsonValidationErrors('external_id');
        $this->create($role, $key, ['external_id' => str_repeat('x', 256)])->assertStatus(422)->assertJsonValidationErrors('external_id');
        $this->create($role, $key, ['name' => 'Fits', 'external_id' => str_repeat('x', 255)])->assertCreated();

        $this->assertSame(1, Event::count());
    }

    public function test_the_same_id_again_is_a_conflict_that_names_the_event_and_makes_nothing(): void
    {
        [, $role, $key] = $this->schedule();
        $first = $this->create($role, $key, ['external_id' => 'A-1'])->assertCreated()->json('data.id');
        $schedulesBefore = Role::count();

        $this->create($role, $key, [
            'name' => 'Second try',
            'external_id' => 'A-1',
            'venue_name' => 'A venue the second try would have made',
        ])
            ->assertStatus(409)
            ->assertJsonPath('data.id', $first)
            ->assertJsonPath('error', 'An event with this external_id already exists on this schedule.');

        $this->assertSame(1, Event::count());
        $this->assertSame($schedulesBefore, Role::count());

        // Answered before the body is judged as a new event: a caller that sends its id and
        // little else is told the event exists, not that a name is missing.
        $this->postJson('/api/events/'.$role->subdomain, ['external_id' => 'A-1'], $key)
            ->assertStatus(409)
            ->assertJsonPath('data.id', $first);
    }

    /**
     * Two requests with the same new id: both pass the first look, and the second waits for the
     * first. While it waits (the fake sleep is that wait) the first one finishes. The second must
     * then find that event rather than start a save of its own: a save makes the venue before it
     * reaches the event row, so stopping at the unique index would leave a venue nobody asked for.
     */
    public function test_a_request_that_loses_the_race_for_an_id_makes_nothing(): void
    {
        [, $role, $key] = $this->schedule();
        $held = Cache::lock('api-event-external-id:'.$role->id.':'.sha1('race'), 30);
        $this->assertTrue($held->get());

        $winner = null;
        Sleep::fake(true, true);
        Sleep::whenFakingSleep(function () use (&$winner, $role, $held) {
            if ($winner) {
                return;
            }
            $winner = $this->createEvent($role, ['creator_role_id' => $role->id, 'name' => 'Got there first']);
            $winner->forceFill(['external_id' => 'race'])->save();
            $held->release();
        });
        $schedulesBefore = Role::count();

        $response = $this->create($role, $key, ['external_id' => 'race', 'venue_name' => 'A venue only the loser named']);

        $this->assertNotNull($winner, 'the request never waited for the lock');
        $response->assertStatus(409)->assertJsonPath('data.id', UrlUtils::encodeId($winner->id));
        $this->assertSame(1, Event::count());
        $this->assertSame($schedulesBefore, Role::count());
    }

    public function test_a_request_that_cannot_get_its_turn_says_so_and_makes_nothing(): void
    {
        [, $role, $key] = $this->schedule();
        $this->assertTrue(Cache::lock('api-event-external-id:'.$role->id.':'.sha1('stuck'), 30)->get());
        Sleep::fake(true, true);

        $this->create($role, $key, ['external_id' => 'stuck'])
            ->assertStatus(409)
            ->assertJsonPath('error', 'Another request is writing an event with this external_id. Try again.');

        $this->assertSame(0, Event::count());

        // An event without an id never waits for anybody.
        $this->create($role, $key, ['name' => 'No id'])->assertCreated();
    }

    /**
     * The table's own collation would call these one key, and the second record of the source
     * would be written onto the first one's event.
     */
    public function test_ids_that_differ_only_in_case_are_two_events(): void
    {
        [, $role, $key] = $this->schedule();

        $this->create($role, $key, ['external_id' => 'aB3'])->assertCreated();
        $this->create($role, $key, ['name' => 'Other case', 'external_id' => 'Ab3'])->assertCreated();

        $this->assertSame(2, Event::count());
        $this->getJson('/api/events?external_id=Ab3', $key)->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.name', 'Other case');
    }

    public function test_two_schedules_may_use_the_same_id(): void
    {
        [$owner, $role, $key] = $this->schedule();
        $other = $this->createRole($owner, 'talent');

        $this->create($role, $key, ['external_id' => '1'])->assertCreated();
        $this->create($other, $key, ['name' => 'On the other schedule', 'external_id' => '1'])->assertCreated();

        $this->getJson('/api/events?external_id=1', $key)->assertOk()->assertJsonCount(2, 'data');
        $this->getJson('/api/events?external_id=1&subdomain='.$other->subdomain, $key)
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'On the other schedule');
        $this->getJson('/api/events?external_id=nobody-has-this', $key)->assertOk()->assertJsonCount(0, 'data');
    }

    /**
     * "One event or none." `subdomain` matches every schedule an event is listed on, and the id
     * is only unique per OWNING schedule: a schedule that lists somebody else's event carrying
     * the same id in their numbering got two rows back, and a sync wrote onto the wrong one.
     */
    public function test_a_lookup_by_id_answers_with_the_event_the_schedule_owns(): void
    {
        [$owner, $role, $key] = $this->schedule();
        [, $theirs, $theirKey] = $this->schedule();
        $mine = $this->create($role, $key, ['name' => 'Ours, number 42', 'external_id' => '42'])->assertCreated()->json('data.id');
        $foreign = $this->create($theirs, $theirKey, ['name' => 'Theirs, also 42', 'external_id' => '42'])->assertCreated()->json('data.id');
        // We list theirs on our schedule, accepted.
        Event::find(UrlUtils::decodeId($foreign))->roles()->attach($role->id, ['is_accepted' => true]);

        $this->getJson('/api/events?subdomain='.$role->subdomain, $key)->assertOk()->assertJsonCount(2, 'data');
        $this->getJson('/api/events?external_id=42&subdomain='.$role->subdomain, $key)
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $mine);
        // With no schedule named: the events of the schedules the caller runs.
        $this->getJson('/api/events?external_id=42', $key)->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $mine);
    }

    public function test_an_update_sets_it_changes_it_keeps_it_when_not_named_and_clears_it(): void
    {
        [, $role, $key] = $this->schedule();
        $event = $this->createEvent($role, ['creator_role_id' => $role->id]);
        $url = '/api/events/'.UrlUtils::encodeId($event->id);

        $this->putJson($url, ['external_id' => 'first'], $key)->assertOk()->assertJsonPath('data.external_id', 'first');
        $this->putJson($url, ['external_id' => 'second'], $key)->assertOk()->assertJsonPath('data.external_id', 'second');
        $this->putJson($url, ['name' => 'Renamed'], $key)->assertOk()->assertJsonPath('data.external_id', 'second');
        // What was read, sent back.
        $this->putJson($url, ['name' => 'Renamed again', 'external_id' => 'second'], $key)->assertOk();
        $this->assertSame('second', $event->fresh()->external_id);

        $this->putJson($url, ['external_id' => null], $key)->assertOk()->assertJsonPath('data.external_id', null);
        $this->assertNull($event->fresh()->external_id);
    }

    public function test_an_update_cannot_take_an_id_another_event_of_the_schedule_holds(): void
    {
        [, $role, $key] = $this->schedule();
        $holder = $this->createEvent($role, ['creator_role_id' => $role->id, 'name' => 'Holder']);
        $holder->forceFill(['external_id' => 'taken'])->save();
        $event = $this->createEvent($role, ['creator_role_id' => $role->id]);

        $this->putJson('/api/events/'.UrlUtils::encodeId($event->id), ['name' => 'Would be renamed', 'external_id' => 'taken'], $key)
            ->assertStatus(422)
            ->assertJsonPath('errors.external_id.0', 'The event '.UrlUtils::encodeId($holder->id).' on the same schedule already has this external_id.');

        $this->assertNull($event->fresh()->external_id);
        $this->assertNotSame('Would be renamed', $event->fresh()->name);
    }

    /**
     * Whoever may edit an event includes an admin of the venue it is at. The id is in the OWNER's
     * namespace: a venue that changed or cleared it would make the owner's next sync create a
     * duplicate. Until 2026-10 this was asked of a curator that merely listed the event; listing
     * is no longer editing (EventListingRightsTest), so that curator is turned away before the
     * question is reached, which the last lines hold.
     */
    public function test_only_the_owning_schedules_admins_can_change_it(): void
    {
        [, $role] = $this->schedule();
        $event = $this->createEvent($role, ['creator_role_id' => $role->id]);
        $event->forceFill(['external_id' => 'the-owners'])->save();

        [, $venue, $venueKey] = $this->schedule('venue');
        $event->roles()->attach($venue->id, ['is_accepted' => true]);
        $url = '/api/events/'.UrlUtils::encodeId($event->id);

        foreach (['theirs', null] as $attempt) {
            $this->putJson($url, ['external_id' => $attempt], $venueKey)
                ->assertStatus(422)
                ->assertJsonValidationErrors('external_id');
        }
        $this->assertSame('the-owners', $event->fresh()->external_id);

        // The object read and written back carries the same value, and that is no change.
        $this->putJson($url, ['short_description' => 'At our place', 'external_id' => 'the-owners'], $venueKey)->assertOk();
        $this->assertSame('the-owners', $event->fresh()->external_id);

        $curatorOwner = $this->createOwner();
        $curator = $this->createCurator($curatorOwner);
        $event->roles()->attach($curator->id, ['is_accepted' => true]);

        $this->putJson($url, ['external_id' => 'theirs'], $this->keyFor($curatorOwner))->assertStatus(403);
        $this->assertSame('the-owners', $event->fresh()->external_id);
    }

    public function test_an_event_with_no_owning_schedule_cannot_carry_one(): void
    {
        [, $role, $key] = $this->schedule();
        $event = $this->createEvent($role);
        $this->assertNull($event->creator_role_id);

        $this->putJson('/api/events/'.UrlUtils::encodeId($event->id), ['external_id' => 'x'], $key)
            ->assertStatus(422)
            ->assertJsonPath('errors.external_id.0', 'This event has no owning schedule, so it cannot carry an external_id.');
        $this->assertNull($event->fresh()->external_id);
    }

    public function test_the_event_form_cannot_write_it(): void
    {
        $this->assertNotContains('external_id', (new Event)->getFillable());

        [$owner, $role] = $this->schedule();
        $event = $this->createEvent($role, ['creator_role_id' => $role->id]);
        $event->forceFill(['external_id' => 'kept'])->save();

        $event->fill(['name' => 'Filled', 'external_id' => 'from a form'])->save();

        $this->assertSame('kept', $event->fresh()->external_id);
        $this->assertSame('Filled', $event->fresh()->name);
    }

    /** The merge re-points every event of the merged-away venue at the survivor, in one statement. */
    public function test_merging_two_schedules_that_use_the_same_id_goes_through(): void
    {
        $owner = $this->createOwner();
        $survivor = $this->createRole($owner, 'venue', ['name' => 'Ozen Bar', 'city' => 'Tel Aviv', 'country_code' => 'il']);

        $duplicate = new Role;
        $duplicate->subdomain = 'stub'.strtolower(Str::random(10));
        $duplicate->type = 'venue';
        $duplicate->name = 'Ozen Bar';
        $duplicate->city = 'Tel Aviv';
        $duplicate->country_code = 'il';
        $duplicate->save();
        $this->followRole($owner, $duplicate);

        $kept = $this->createEvent($survivor, ['creator_role_id' => $survivor->id, 'name' => 'Kept']);
        $kept->forceFill(['external_id' => 'same'])->save();
        $moved = $this->createEvent($duplicate, ['creator_role_id' => $duplicate->id, 'user_id' => $owner->id, 'name' => 'Moved']);
        $moved->forceFill(['external_id' => 'same'])->save();
        $own = $this->createEvent($duplicate, ['creator_role_id' => $duplicate->id, 'user_id' => $owner->id, 'name' => 'Its own id']);
        $own->forceFill(['external_id' => 'only-here'])->save();

        $this->actingAs($owner)->post(route('following.merge_venues_group'), [
            'target_id' => UrlUtils::encodeId($survivor->id),
            'source_ids' => [UrlUtils::encodeId($duplicate->id)],
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertSame($survivor->id, $moved->fresh()->creator_role_id, 'the merge did not go through');
        $this->assertSame('same', $kept->fresh()->external_id);
        $this->assertNull($moved->fresh()->external_id);
        $this->assertSame('only-here', $own->fresh()->external_id);
    }

    /**
     * A restore makes the new schedule the owner of every event in the archive, the ones the old
     * schedule merely listed included. Their ids are another schedule's and stay behind.
     */
    public function test_a_backup_carries_it_for_the_events_the_schedule_owns_and_no_others(): void
    {
        [$owner, $role] = $this->schedule('venue');
        $other = $this->createRole($this->createOwner(), 'talent');

        $owned = $this->createEvent($role, ['creator_role_id' => $role->id, 'name' => 'Owned']);
        $owned->forceFill(['external_id' => 'mine'])->save();
        $listed = $this->createEvent($other, ['creator_role_id' => $other->id, 'name' => 'Listed']);
        $listed->forceFill(['external_id' => 'theirs'])->save();
        $listed->roles()->attach($role->id, ['is_accepted' => true]);

        $service = app(BackupService::class);
        $export = new \ReflectionMethod($service, 'exportEvent');
        $export->setAccessible(true);
        $images = [];

        $ownedData = $export->invokeArgs($service, [$owned->fresh(), $role, false, &$images]);
        $listedData = $export->invokeArgs($service, [$listed->fresh(), $role, false, &$images]);

        $this->assertSame('mine', $ownedData['external_id']);
        $this->assertArrayNotHasKey('external_id', $listedData);

        $restoredInto = $this->createRole($owner, 'venue');
        $import = new \ReflectionMethod($service, 'importEvent');
        $import->setAccessible(true);
        $idMap = [];
        $restored = $import->invokeArgs($service, [$ownedData, $restoredInto, $owner->id, &$idMap]);

        $this->assertSame('mine', $restored->fresh()->external_id);
        $this->assertSame($restoredInto->id, $restored->fresh()->creator_role_id);

        // An archive that names the id twice restores both events, and the first keeps it.
        $again = $import->invokeArgs($service, [$ownedData, $restoredInto, $owner->id, &$idMap]);
        $this->assertNull($again->fresh()->external_id);
        $this->assertSame('mine', $restored->fresh()->external_id);
    }
}
