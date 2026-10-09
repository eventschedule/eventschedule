<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventFeed;
use App\Models\Role;
use App\Models\User;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * What a place on a team gives, and what is left of it afterwards.
 *
 *   - a VIEWER reads the calendar, marks their own days and scans tickets. Newsletters (writing,
 *     sending, importing addresses, reading recipient lists) and the tabs that hold other
 *     people's details (followers, bookings, requests, team, plan) are for owners and admins,
 *     and that is asked on the server, not only hidden in the tab strip;
 *   - a person who LEAVES a team (or is made a viewer) keeps no rights over the events they made
 *     there. Those events pass to the schedule's owner, except one that has already taken money:
 *     refunds look up the maker's payment account, so that row is left as it is, and only the
 *     rights go;
 *   - on hosted, a team belongs to the Enterprise plan. After a downgrade only the owner changes
 *     things: the tab page already said so, and every save now agrees with it (isEditor()).
 */
class TeamRolesAndLeaversTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    /** @return array{0: User, 1: Role, 2: User} the owner, the schedule, and a member at $level */
    private function team(string $level, bool $free = false): array
    {
        $owner = $this->createOwner();
        $role = $free ? $this->createFreeRole($owner, 'venue') : $this->createRole($owner, 'venue');
        $member = $this->createOwner();
        $role->users()->attach($member->id, ['level' => $level]);

        return [$owner, $role, $member->fresh()];
    }

    // ---- a viewer ------------------------------------------------------------------------------

    public function test_a_viewer_does_not_reach_newsletters(): void
    {
        [, $role, $viewer] = $this->team('viewer');
        $rid = UrlUtils::encodeId($role->id);

        $this->assertContains($this->actingAs($viewer)->get(route('newsletter.index', ['role_id' => $rid]))->status(), [403, 404]);
        $this->assertContains($this->actingAs($viewer)->post(route('newsletter.store').'?role_id='.$rid, ['subject' => 'Hello'])->status(), [403, 404]);
        $this->assertSame(0, DB::table('newsletters')->where('role_id', $role->id)->count());
    }

    public function test_a_viewer_opens_the_calendar_and_none_of_the_tabs_that_hold_peoples_details(): void
    {
        [, $role, $viewer] = $this->team('viewer');

        $this->actingAs($viewer)->get(route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'schedule']))->assertOk();

        foreach (['followers', 'team', 'requests', 'appointments', 'templates'] as $tab) {
            $this->actingAs($viewer)->get(route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => $tab]))
                ->assertRedirect(route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'schedule']));
        }
    }

    public function test_an_admin_still_reaches_both(): void
    {
        [, $role, $admin] = $this->team('admin');

        $this->actingAs($admin)->get(route('newsletter.index', ['role_id' => UrlUtils::encodeId($role->id)]))->assertOk();
        $this->actingAs($admin)->get(route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'team']))->assertOk();
    }

    // ---- leaving a team ---------------------------------------------------------------------------

    public function test_a_removed_members_events_pass_to_the_owner_and_their_rights_end(): void
    {
        [$owner, $role, $admin] = $this->team('admin');
        $event = $this->createEvent($role, ['creator_role_id' => $role->id, 'user_id' => $admin->id]);
        $this->assertTrue($admin->canEditEvent($event), 'sanity check: on the team, they edit what they made');

        $this->actingAs($owner)->delete(route('role.remove_member', ['subdomain' => $role->subdomain, 'hash' => UrlUtils::encodeId($admin->id)]));

        $event = Event::find($event->id);
        $this->assertSame($owner->id, (int) $event->user_id);
        $this->assertFalse($admin->fresh()->canEditEvent($event));
        $this->assertFalse($admin->fresh()->runsEvent($event));
        $this->assertFalse($admin->fresh()->canViewEventData($event));
    }

    public function test_an_event_that_has_taken_money_keeps_its_row_and_the_leaver_still_loses_the_rights(): void
    {
        [$owner, $role, $admin] = $this->team('admin');
        $event = $this->createEvent($role, ['creator_role_id' => $role->id, 'user_id' => $admin->id]);
        $this->createSale($event, $role, ['status' => 'paid']);

        $this->actingAs($owner)->delete(route('role.remove_member', ['subdomain' => $role->subdomain, 'hash' => UrlUtils::encodeId($admin->id)]));

        $event = Event::find($event->id);
        $this->assertSame($admin->id, (int) $event->user_id, 'refunds look up the maker\'s payment account');
        $this->assertFalse($admin->fresh()->canEditEvent($event));
        $this->assertFalse($admin->fresh()->canViewEventData($event));
    }

    public function test_being_made_a_viewer_ends_the_same_rights(): void
    {
        [$owner, $role, $admin] = $this->team('admin');
        $event = $this->createEvent($role, ['creator_role_id' => $role->id, 'user_id' => $admin->id]);

        $this->actingAs($owner)->patch(route('role.update_member_level', ['subdomain' => $role->subdomain, 'hash' => UrlUtils::encodeId($admin->id)]), ['level' => 'viewer']);

        $event = Event::find($event->id);
        $this->assertSame($owner->id, (int) $event->user_id);
        $this->assertFalse($admin->fresh()->canEditEvent($event));
    }

    // ---- a downgrade (hosted) ---------------------------------------------------------------------

    public function test_after_a_downgrade_only_the_owner_changes_things(): void
    {
        [$owner, $role, $admin] = $this->team('admin', free: true);
        $this->assertFalse($role->fresh()->isEnterprise(), 'sanity check: a plan without a team');
        $event = $this->createEvent($role, ['creator_role_id' => $role->id, 'user_id' => $owner->id]);

        $this->assertFalse($admin->isEditor($role->subdomain));
        $this->assertFalse($admin->canEditEvent($event));
        $this->assertFalse($admin->canViewEventData($event));

        $this->actingAs($admin)->from(route('home'))->put(route('role.update', ['subdomain' => $role->subdomain]), [
            'name' => 'Renamed By A Team Admin', 'email' => $role->email, 'timezone' => $role->timezone, 'new_subdomain' => $role->subdomain,
        ]);
        $this->assertNotSame('Renamed By A Team Admin', $role->fresh()->name);

        $this->assertTrue($owner->isEditor($role->subdomain));
        $this->assertTrue($owner->canEditEvent($event));
    }

    public function test_an_enterprise_teams_admin_is_unchanged(): void
    {
        config(['app.hosted' => true]);
        [, $role, $admin] = $this->team('admin');
        $event = $this->createEvent($role, ['creator_role_id' => $role->id]);

        $this->assertTrue($role->fresh()->isEnterprise());
        $this->assertTrue($admin->isEditor($role->subdomain));
        $this->assertTrue($admin->canEditEvent($event));
    }

    public function test_after_a_downgrade_a_team_admin_does_not_reach_the_schedules_feeds(): void
    {
        [$owner, $role, $admin] = $this->team('admin', free: true);
        $url = 'https://calendar.example.org/'.Str::random(8).'.ics';
        $feed = EventFeed::create([
            'role_id' => $role->id, 'name' => 'A feed', 'url' => $url, 'url_hash' => EventFeed::hashOf($url), 'host' => 'calendar.example.org',
            'kind' => EventFeed::KIND_CALENDAR, 'source_timezone' => 'Europe/Vienna', 'baseline_done_at' => now(), 'last_success_at' => now(), 'next_check_at' => now()->addHour(),
        ]);
        $hash = UrlUtils::encodeId($feed->id);

        // What the owner may still do with a feed on a plan without feeds is ApFeedPageTest's.
        $this->actingAs($admin)->delete(route('role.feeds.destroy', ['subdomain' => $role->subdomain, 'hash' => $hash]), ['its_events' => 'delete'])->assertSessionHas('error');
        $this->assertNotNull(EventFeed::find($feed->id));

        $this->actingAs($owner)->post(route('role.feeds.pause', ['subdomain' => $role->subdomain, 'hash' => $hash]))->assertSessionMissing('error');
    }

    // ---- selfhost: signing up with an address that is already waiting -----------------------------

    public function test_a_selfhost_sign_up_does_not_take_over_an_invited_account(): void
    {
        config(['app.hosted' => false, 'app.allow_registration' => true]);
        [, $role] = $this->team('viewer');
        $invited = User::factory()->create(['email' => 'invited.person@gmail.com', 'password' => null, 'email_verified_at' => null]);
        $role->users()->attach($invited->id, ['level' => 'admin']);
        $this->assertTrue($invited->fresh()->isStub(), 'sanity check: invited, with no password yet');

        $this->post('/sign_up', ['terms' => '1', 'name' => 'Somebody Else', 'email' => 'invited.person@gmail.com', 'password' => 'password123']);

        $this->assertGuest();
        $this->assertNull($invited->fresh()->password);
        $this->assertSame($invited->fresh()->name === 'Somebody Else', false);
    }
}
