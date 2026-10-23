<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventComment;
use App\Models\Role;
use App\Models\User;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The API asks the same team rule as the web, and List Events lists what Show Event answers.
 *
 * On hosted a team belongs to the Enterprise plan (User::planAllowsTeamAccess(), behind
 * isEditor() and canEditEvent()). The event endpoints and the appointment API asked it, while
 * the schedule, sub-schedule, Create Event, List Events and fan content endpoints asked only for
 * an owner or admin row: an admin of a Pro schedule, or a team member after a downgrade from
 * Enterprise, renamed the schedule and made events over the API while the portal refused them.
 *
 * And List Events listed every event on any schedule the caller runs, including one a curator
 * only lists and one a schedule turned down, which Show then answered with 403.
 */
class ApiTeamGateTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.hosted' => true]);
    }

    private function keyFor(User $user): array
    {
        $raw = 'testapikey_'.Str::random(24);
        $user->api_key = substr(hash('sha256', $raw), 0, 8);
        $user->api_key_hash = Hash::make($raw);
        $user->save();

        return ['X-API-Key' => $raw];
    }

    /** @return array{0: User, 1: Role, 2: User, 3: Event} the owner, a Pro schedule, its admin, its event */
    private function proTeam(string $plan = 'pro'): array
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['plan_type' => $plan]);
        $admin = $this->createOwner();
        $role->users()->attach($admin->id, ['level' => 'admin']);
        $event = $this->createEvent($role, ['creator_role_id' => $role->id, 'user_id' => $owner->id]);

        return [$owner, $role, $admin->fresh(), $event];
    }

    private function listedIds(array $headers): array
    {
        return collect($this->getJson('/api/events', $headers)->assertOk()->json('data'))->pluck('id')->all();
    }

    public function test_a_pro_schedules_admin_is_refused_where_the_portal_refuses_them(): void
    {
        [, $role, $admin, $event] = $this->proTeam();
        $this->assertTrue($role->fresh()->isPro(), 'sanity check: the schedule may use the API');
        $this->assertFalse($role->fresh()->isEnterprise(), 'sanity check: a plan without a team');
        $this->assertFalse($admin->isEditor($role->subdomain), 'sanity check: the portal refuses them');
        $key = $this->keyFor($admin);
        $sub = $role->subdomain;

        $this->assertNotContains($sub, collect($this->getJson('/api/schedules', $key)->assertOk()->json('data'))->pluck('subdomain')->all());
        $this->getJson('/api/schedules/'.$sub, $key)->assertStatus(403)->assertJsonPath('error', 'Unauthorized');

        $this->putJson('/api/schedules/'.$sub, ['name' => 'Renamed By A Team Admin'], $key)->assertStatus(403)->assertJsonPath('error', 'Unauthorized');
        $this->assertNotSame('Renamed By A Team Admin', $role->fresh()->name);

        $this->getJson('/api/schedules/'.$sub.'/groups', $key)->assertStatus(403)->assertJsonPath('error', 'Unauthorized');
        $this->postJson('/api/schedules/'.$sub.'/groups', ['name' => 'Side room'], $key)->assertStatus(403);
        $this->assertSame(0, $role->groups()->count());

        $this->postJson('/api/events/'.$sub, ['name' => 'Made By A Team Admin', 'starts_at' => now()->addDays(3)->format('Y-m-d H:i:s')], $key)
            ->assertStatus(403)->assertJsonPath('error', 'Unauthorized');
        $this->assertFalse(Event::where('name', 'Made By A Team Admin')->exists());

        $this->assertNotContains(UrlUtils::encodeId($event->id), $this->listedIds($key));

        EventComment::create(['event_id' => $event->id, 'guest_name' => 'Dana Guest', 'comment' => 'Waiting', 'is_approved' => false]);
        $this->getJson('/api/fan-content?is_approved=0', $key)->assertOk()->assertJsonCount(0, 'data');
    }

    public function test_the_owner_of_the_same_schedule_is_not_limited(): void
    {
        [$owner, $role, , $event] = $this->proTeam();
        $key = $this->keyFor($owner);
        $sub = $role->subdomain;

        $this->assertContains($sub, collect($this->getJson('/api/schedules', $key)->assertOk()->json('data'))->pluck('subdomain')->all());
        $this->getJson('/api/schedules/'.$sub, $key)->assertOk();
        $this->getJson('/api/schedules/'.$sub.'/groups', $key)->assertOk();
        $this->assertContains(UrlUtils::encodeId($event->id), $this->listedIds($key));

        EventComment::create(['event_id' => $event->id, 'guest_name' => 'Dana Guest', 'comment' => 'Waiting', 'is_approved' => false]);
        $this->getJson('/api/fan-content?is_approved=0', $key)->assertOk()->assertJsonCount(1, 'data');
    }

    public function test_an_enterprise_schedules_admin_is_let_in(): void
    {
        [, $role, $admin, $event] = $this->proTeam('enterprise');
        $key = $this->keyFor($admin);
        $sub = $role->subdomain;

        $this->assertContains($sub, collect($this->getJson('/api/schedules', $key)->assertOk()->json('data'))->pluck('subdomain')->all());
        $this->getJson('/api/schedules/'.$sub, $key)->assertOk();
        $this->putJson('/api/schedules/'.$sub, ['name' => 'Renamed By A Team Admin'], $key)->assertOk();
        $this->getJson('/api/schedules/'.$sub.'/groups', $key)->assertOk();
        $this->postJson('/api/events/'.$sub, ['name' => 'Made By A Team Admin', 'starts_at' => now()->addDays(3)->format('Y-m-d H:i:s')], $key)
            ->assertSuccessful();
        $this->assertContains(UrlUtils::encodeId($event->id), $this->listedIds($key));
    }

    public function test_list_events_lists_what_show_answers(): void
    {
        $owner = $this->createOwner();
        $key = $this->keyFor($owner);

        // Somebody else's event that the caller's curator only lists.
        $other = $this->createOwner();
        $theirs = $this->createRole($other, 'venue');
        $listed = $this->createEvent($theirs, ['creator_role_id' => $theirs->id, 'user_id' => $other->id]);
        $curator = $this->createCurator($owner);
        $listed->roles()->attach($curator->id, ['is_accepted' => true]);

        // Somebody else's event that the caller's venue turned down, and one it has not answered.
        $venue = $this->createRole($owner, 'venue');
        $declined = $this->createEvent($theirs, ['creator_role_id' => $theirs->id, 'user_id' => $other->id]);
        $declined->roles()->attach($venue->id, ['is_accepted' => false]);
        $pending = $this->createEvent($theirs, ['creator_role_id' => $theirs->id, 'user_id' => $other->id]);
        $pending->roles()->attach($venue->id, ['is_accepted' => null]);

        // The caller's own events, on the curator and on the venue.
        $ownCurated = $this->createEvent($curator, ['creator_role_id' => $curator->id]);
        $ownAtVenue = $this->createEvent($venue, ['creator_role_id' => $venue->id]);

        // From before events.creator_role_id: the schedule it was first put on owns it. One first
        // put on the caller's curator is the curator's own; one first put on somebody else's
        // venue and then listed by the curator is not, even though the caller made it.
        $legacyOwn = $this->createEvent($curator, ['user_id' => $other->id]);
        $legacyTheirs = $this->createEvent($theirs, ['user_id' => $owner->id]);
        $legacyTheirs->roles()->attach($curator->id, ['is_accepted' => true]);
        Event::whereIn('id', [$legacyOwn->id, $legacyTheirs->id])->update(['creator_role_id' => null]);

        // And one the caller made that is on no schedule at all: theirs to edit, though the list
        // (which asks for a Pro schedule) and Show (likewise) never reach it.
        $noSchedule = $this->createEvent($venue, ['creator_role_id' => null]);
        $noSchedule->roles()->detach();
        Event::whereKey($noSchedule->id)->update(['creator_role_id' => null]);

        $all = Event::whereIn('id', [$listed->id, $declined->id, $pending->id, $ownCurated->id, $ownAtVenue->id, $legacyOwn->id, $legacyTheirs->id, $noSchedule->id])->with('roles')->get();
        $scoped = Event::editableBy($owner)->pluck('id')->all();
        foreach ($all as $event) {
            $this->assertSame($owner->canEditEvent($event), in_array($event->id, $scoped), "editableBy() and canEditEvent() agree on event {$event->id}");
        }
        $this->assertContains($legacyOwn->id, $scoped, 'a legacy event first put on my curator is mine');
        $this->assertNotContains($legacyTheirs->id, $scoped, 'a legacy event first put on somebody else\'s schedule is not, whoever made it');
        $this->assertContains($noSchedule->id, $scoped, 'an event on no schedule stays its maker\'s');

        $listedIds = $this->listedIds($key);

        foreach (['listed' => $listed, 'declined' => $declined, 'pending' => $pending, 'own curated' => $ownCurated, 'own at venue' => $ownAtVenue, 'legacy own' => $legacyOwn, 'legacy theirs' => $legacyTheirs] as $what => $event) {
            $id = UrlUtils::encodeId($event->id);
            $answered = $this->getJson('/api/events/'.$id, $key)->status() === 200;

            $this->assertSame($answered, in_array($id, $listedIds, true), "List Events and Show Event agree on the {$what} event");
        }

        $this->assertNotContains(UrlUtils::encodeId($listed->id), $listedIds, 'a curator that only lists an event does not run it');
        $this->assertNotContains(UrlUtils::encodeId($declined->id), $listedIds, 'a schedule that turned an event down has no say over it');
        $this->assertContains(UrlUtils::encodeId($pending->id), $listedIds, 'a venue that has not answered yet still edits it');
        $this->assertContains(UrlUtils::encodeId($ownCurated->id), $listedIds);
        $this->assertContains(UrlUtils::encodeId($ownAtVenue->id), $listedIds);
    }

    /**
     * Update Event runs the save through one of the event's schedules. It used to take the first
     * one where the caller had an owner or admin row, which could be a schedule whose plan has
     * closed the team to them, or a curator that only lists the event. The save then ran through
     * that schedule: its sub-schedules, categories and calendar connections.
     */
    public function test_an_update_runs_through_a_schedule_the_caller_may_act_on(): void
    {
        $caller = $this->createOwner();
        $key = $this->keyFor($caller);

        // Made first, so it comes first among the event's schedules: a Pro talent schedule the
        // caller is an admin of, which the plan closes to them, and a curator the caller owns.
        $blocked = $this->createRole($this->createOwner(), 'talent', ['plan_type' => 'pro']);
        $blocked->users()->attach($caller->id, ['level' => 'admin']);
        $curator = $this->createCurator($caller);

        $venueOwner = $this->createOwner();
        $venue = $this->createRole($venueOwner, 'venue', ['plan_type' => 'enterprise']);
        $venue->users()->attach($caller->id, ['level' => 'admin']);
        $room = new \App\Models\Group;
        $room->role_id = $venue->id;
        $room->name = 'Main room';
        $room->slug = 'main-room';
        $room->save();

        $event = $this->createEvent($blocked, ['creator_role_id' => $venue->id, 'user_id' => $venueOwner->id]);
        $event->roles()->attach($curator->id, ['is_accepted' => true]);
        $event->roles()->attach($venue->id, ['is_accepted' => true]);
        $this->assertTrue($caller->fresh()->canEditEvent($event->fresh()), 'sanity check: the venue lets the caller edit it');

        $this->putJson('/api/events/'.UrlUtils::encodeId($event->id), ['schedule' => 'main-room'], $key)->assertOk();
        $this->assertSame($room->id, (int) $event->fresh()->roles->firstWhere('id', $venue->id)->pivot->group_id);
    }

    /**
     * A new external_id is the owning schedule's to give, and "runs the owning schedule" is the
     * team rule: an admin of a Pro schedule (no team on that plan) does not run it.
     */
    public function test_a_plan_blocked_admin_of_the_owning_schedule_cannot_change_its_external_id(): void
    {
        [$owner, $role, $admin, $event] = $this->proTeam();

        // The caller may still edit the event through another schedule it is on.
        $venue = $this->createRole($this->createOwner(), 'venue', ['plan_type' => 'enterprise']);
        $venue->users()->attach($admin->id, ['level' => 'admin']);
        $event->roles()->attach($venue->id, ['is_accepted' => true]);
        $this->assertTrue($admin->fresh()->canEditEvent($event->fresh()), 'sanity check: the venue lets the admin edit it');

        $id = UrlUtils::encodeId($event->id);
        $this->putJson('/api/events/'.$id, ['external_id' => 'ext-1'], $this->keyFor($admin))
            ->assertStatus(422)
            ->assertJsonPath('errors.external_id.0', 'Only an owner or admin of the schedule that owns this event can set its external_id.');
        $this->assertNull($event->fresh()->external_id);

        $this->putJson('/api/events/'.$id, ['external_id' => 'ext-1'], $this->keyFor($owner))->assertOk();
        $this->assertSame('ext-1', $event->fresh()->external_id);
    }

    public function test_an_event_its_maker_no_longer_runs_is_not_listed(): void
    {
        [, $role, $admin] = $this->proTeam();
        $made = $this->createEvent($role, ['creator_role_id' => $role->id, 'user_id' => $admin->id]);

        $this->assertFalse($admin->canEditEvent($made), 'sanity check: Show refuses it');
        $this->assertNotContains(UrlUtils::encodeId($made->id), $this->listedIds($this->keyFor($admin)));
    }
}
