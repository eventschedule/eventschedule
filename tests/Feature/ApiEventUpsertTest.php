<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Role;
use App\Models\Ticket;
use App\Models\User;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Sleep;
use Illuminate\Support\Str;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * POST /api/events/{subdomain} with "upsert": true: the event that already holds the request's
 * external_id is updated instead of answered with a 409.
 *
 * The point of everything here is that the update is an UPDATE. The same address makes new
 * events, and a new event's steps (required fields, the schedule's default visibility, the
 * default payment method, the daily cap) run on an event that exists would publish a draft the
 * owner is holding and reset what the request never mentioned. So the branch is taken before any
 * of them, and runs the rules of PUT through the schedule in the address.
 */
class ApiEventUpsertTest extends TestCase
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
    private function schedule(string $type = 'talent', array $attrs = []): array
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, $type, $attrs);

        return [$owner, $role, $this->keyFor($owner)];
    }

    private function upsert(Role $role, array $key, array $body)
    {
        return $this->postJson('/api/events/'.$role->subdomain, $body + ['upsert' => true], $key);
    }

    private function start(int $hour = 18): string
    {
        return now()->addWeek()->setTime($hour, 0)->format('Y-m-d H:i:s');
    }

    public function test_the_first_call_creates_and_the_second_updates_the_same_event(): void
    {
        [, $role, $key] = $this->schedule();

        $created = $this->upsert($role, $key, ['external_id' => 'post-1', 'name' => 'First title', 'starts_at' => $this->start()])
            ->assertCreated()
            ->assertJsonPath('meta.created', true)
            ->assertJsonPath('meta.message', 'Event created successfully')
            ->json('data.id');

        $this->upsert($role, $key, ['external_id' => 'post-1', 'name' => 'Corrected title', 'starts_at' => $this->start(20)])
            ->assertOk()
            ->assertJsonPath('meta.created', false)
            ->assertJsonPath('meta.message', 'Event updated successfully')
            ->assertJsonPath('data.id', $created)
            ->assertJsonPath('data.name', 'Corrected title')
            ->assertJsonPath('data.external_id', 'post-1');

        $this->assertSame(1, Event::count());
        $this->assertSame($this->start(20), Event::first()->starts_at);
    }

    /** A create that does not ask says nothing about `created`, as it never has. */
    public function test_a_plain_create_reports_no_created_flag_and_a_plain_conflict_stays_a_conflict(): void
    {
        [, $role, $key] = $this->schedule();

        $body = ['external_id' => 'post-1', 'name' => 'Plain', 'starts_at' => $this->start()];
        $response = $this->postJson('/api/events/'.$role->subdomain, $body, $key)->assertCreated();
        $this->assertArrayNotHasKey('created', $response->json('meta'));

        $this->postJson('/api/events/'.$role->subdomain, $body + ['upsert' => false], $key)->assertStatus(409);
        $this->assertSame(1, Event::count());
    }

    /** The rules of PUT: what the body does not carry keeps its stored value, a name included. */
    public function test_the_update_changes_what_it_was_sent_and_nothing_else(): void
    {
        [, $role, $key] = $this->schedule();
        $event = $this->createEvent($role, ['creator_role_id' => $role->id, 'name' => 'Kept name', 'description' => 'Kept text', 'tickets_enabled' => true]);
        $event->forceFill(['external_id' => 'post-2'])->save();
        $ticket = $this->createTicket($event, ['type' => 'General', 'price' => 15]);
        $startsAt = $event->starts_at;

        // No name and no start: a new event would be refused for both.
        $this->upsert($role, $key, ['external_id' => 'post-2', 'short_description' => 'Only this changed'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Kept name')
            ->assertJsonPath('data.short_description', 'Only this changed');

        $event->refresh();
        $this->assertSame('Kept text', $event->description);
        $this->assertSame($startsAt, $event->starts_at);
        $this->assertTrue((bool) $event->tickets_enabled);
        $this->assertFalse((bool) Ticket::find($ticket->id)->is_deleted, 'a ticket type the update never mentioned was retired');
    }

    /**
     * The schedule publishes new events at once. The owner pulled this one back to a draft, and
     * the source's next sync says nothing about visibility: a new event's default must not be
     * laid over the owner's decision.
     */
    public function test_an_update_does_not_put_a_new_events_defaults_over_the_owners_choices(): void
    {
        [, $role, $key] = $this->schedule();
        $id = $this->upsert($role, $key, ['external_id' => 'post-3', 'name' => 'Published at first', 'starts_at' => $this->start()])
            ->assertCreated()
            ->assertJsonPath('data.is_draft', false)
            ->json('data.id');

        $event = Event::find(UrlUtils::decodeId($id));
        $event->forceFill(['is_draft' => true, 'payment_method' => 'payment_url'])->save();

        $this->upsert($role, $key, ['external_id' => 'post-3', 'name' => 'Renamed at the source', 'starts_at' => $this->start()])
            ->assertOk()
            ->assertJsonPath('data.is_draft', true);

        $event->refresh();
        $this->assertTrue((bool) $event->is_draft);
        $this->assertSame('payment_url', $event->payment_method);
        $this->assertSame('Renamed at the source', $event->name);
    }

    public function test_an_update_is_outside_the_daily_cap_and_a_new_event_is_not(): void
    {
        config([
            'usage.event_create_daily_limit_trial' => 1,
            'usage.event_create_daily_limit_pro' => 1,
            'usage.event_create_daily_limit_enterprise' => 1,
        ]);
        [, $role, $key] = $this->schedule();

        $this->upsert($role, $key, ['external_id' => 'a', 'name' => 'The one the cap allows', 'starts_at' => $this->start()])->assertCreated();
        $this->upsert($role, $key, ['external_id' => 'a', 'name' => 'Updated past the cap'])->assertOk()->assertJsonPath('data.name', 'Updated past the cap');

        $this->upsert($role, $key, ['external_id' => 'b', 'name' => 'One too many', 'starts_at' => $this->start()])
            ->assertStatus(422)
            ->assertJsonPath('code', 'event_create_limit');
        $this->assertSame(1, Event::count());
    }

    /**
     * A PUT runs through the first of the event's schedules the caller runs, which here is the
     * venue. The upsert names the talent schedule in its address, and the talent schedule owns
     * the event: its sub-schedules are the ones a `schedule` slug means.
     */
    public function test_the_update_runs_through_the_schedule_in_the_address(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue');
        $talent = $this->createRole($owner, 'talent');
        $key = $this->keyFor($owner);
        $stage = $this->createGroup($talent, ['name' => 'Main stage', 'slug' => 'main-stage']);

        $event = $this->createEvent($venue, ['creator_role_id' => $talent->id]);
        $event->roles()->attach($talent->id, ['is_accepted' => true]);
        $event->forceFill(['external_id' => 'post-4'])->save();

        $this->putJson('/api/events/'.UrlUtils::encodeId($event->id), ['schedule' => 'main-stage'], $key)
            ->assertStatus(422)
            ->assertJsonPath('error', 'Sub-schedule not found');

        $this->upsert($talent, $key, ['external_id' => 'post-4', 'schedule' => 'main-stage'])->assertOk();

        $this->assertSame($stage->id, (int) $event->roles()->where('roles.id', $talent->id)->first()->pivot->group_id);
        $this->assertSame($talent->id, $event->fresh()->creator_role_id);
    }

    public function test_an_upsert_needs_an_id_and_a_true_or_false(): void
    {
        [, $role, $key] = $this->schedule();
        $body = ['name' => 'No id', 'starts_at' => $this->start()];

        $this->upsert($role, $key, $body)->assertStatus(422)->assertJsonValidationErrors('external_id');
        $this->postJson('/api/events/'.$role->subdomain, $body + ['external_id' => 'x', 'upsert' => 'maybe'], $key)
            ->assertStatus(422)
            ->assertJsonValidationErrors('upsert');
        $this->postJson('/api/events/'.$role->subdomain, $body + ['external_id' => 'x', 'upsert' => ['yes']], $key)
            ->assertStatus(422)
            ->assertJsonValidationErrors('upsert');
        $this->assertSame(0, Event::count());

        // false with no id is an ordinary create.
        $this->postJson('/api/events/'.$role->subdomain, $body + ['upsert' => false], $key)->assertCreated();
    }

    public function test_only_someone_who_runs_the_schedule_can_upsert_into_it(): void
    {
        [, $role] = $this->schedule();
        $event = $this->createEvent($role, ['creator_role_id' => $role->id, 'name' => 'Theirs']);
        $event->forceFill(['external_id' => 'post-5'])->save();

        $this->upsert($role, $this->keyFor($this->createOwner()), ['external_id' => 'post-5', 'name' => 'Taken over'])
            ->assertStatus(403);
        $this->upsert($role, [], ['external_id' => 'post-5', 'name' => 'Taken over'])->assertStatus(401);

        $this->assertSame('Theirs', $event->fresh()->name);
    }

    public function test_a_cancelled_event_is_updated_and_stays_cancelled(): void
    {
        [, $role, $key] = $this->schedule();
        $event = $this->createEvent($role, ['creator_role_id' => $role->id]);
        $event->forceFill(['external_id' => 'post-6', 'is_cancelled' => true, 'cancelled_at' => now()])->save();

        $this->upsert($role, $key, ['external_id' => 'post-6', 'name' => 'Still off'])
            ->assertOk()
            ->assertJsonPath('data.is_cancelled', true)
            ->assertJsonPath('data.name', 'Still off');

        // And the body is no more a way to restore it here than it is on a PUT.
        $this->upsert($role, $key, ['external_id' => 'post-6', 'is_cancelled' => false])
            ->assertStatus(422)
            ->assertJsonValidationErrors('is_cancelled');
        $this->assertTrue($event->fresh()->is_cancelled);
    }

    /**
     * Two upserts with the same new id at once. The second passes the first look, waits its turn
     * (the fake sleep is that wait), and by then the first has made the event. It must update
     * that event from what it was SENT: by then the request has been rewritten for a new event,
     * with the schedule's default visibility filled in, and the event here is a draft.
     */
    public function test_an_upsert_that_loses_the_race_for_a_new_id_updates_the_winner(): void
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
            $winner = $this->createEvent($role, ['creator_role_id' => $role->id, 'name' => 'Got there first', 'is_draft' => true]);
            $winner->forceFill(['external_id' => 'race'])->save();
            $held->release();
        });

        $response = $this->upsert($role, $key, ['external_id' => 'race', 'name' => 'From the one that waited', 'starts_at' => $this->start()]);

        $this->assertNotNull($winner, 'the request never waited for the lock');
        $response->assertOk()
            ->assertJsonPath('meta.created', false)
            ->assertJsonPath('data.id', UrlUtils::encodeId($winner->id))
            ->assertJsonPath('data.name', 'From the one that waited')
            ->assertJsonPath('data.is_draft', true);
        $this->assertSame(1, Event::count());
        $this->assertSame($this->start(), $winner->fresh()->starts_at);
    }
}
