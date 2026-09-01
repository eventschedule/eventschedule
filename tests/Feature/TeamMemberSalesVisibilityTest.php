<?php

namespace Tests\Feature;

use App\Models\Sale;
use App\Models\TicketWaitlist;
use App\Models\User;
use App\Utils\UrlUtils;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * A team member added on the Team tab could not see the schedule's ticket sales, because every
 * list on /sales (and the /scan and /checkin pickers) was scoped to events.user_id - "events I
 * personally created" - rather than to the schedules the user runs.
 *
 * events.user_id is the payment account a sale settles into (CheckoutContext::owner() returns
 * $event->user), which is why the scope looked reasonable. But the ACTIONS on those same pages
 * always authorized through the role pivot, so an admin could refund a sale they could not find.
 *
 * Each list now matches the permission check that guards its own action:
 *   /sales, /checkin  -> Event::managedBy()   mirrors canViewEventData()  (owner+admin, curator exception)
 *   /scan             -> Event::scannableBy() mirrors canScanEvent()      (+viewer, no curator exception)
 */
class TeamMemberSalesVisibilityTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // The Sales list hides past events by default, and several assertions below depend on the
        // event still being in that window. Freeze so the suite cannot rot into it.
        $this->travelTo('2026-06-15 12:00:00');
    }

    /** Owner + one paid sale, with the buyer named distinctively so assertSee cannot false-positive. */
    private function scheduleWithASale(string $buyer = 'Zaphod Beeblebrox'): array
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->createEvent($role, ['tickets_enabled' => true, 'creator_role_id' => $role->id]);
        $ticket = $this->createTicket($event, ['price' => 25]);
        $sale = $this->createSale($event, $role, [
            'name' => $buyer,
            'email' => 'buyer@gmail.com',
            'payment_amount' => 25,
            'status' => 'paid',
        ], $ticket);

        return [$owner, $role, $event, $sale];
    }

    public function test_an_admin_member_sees_the_schedules_sales(): void
    {
        [, $role] = $this->scheduleWithASale();

        $admin = $this->createOwner();
        $this->followRole($admin, $role, 'admin');

        $this->actingAs($admin)->get(route('sales'))
            ->assertOk()
            ->assertSee('Zaphod Beeblebrox');
    }

    public function test_a_viewer_a_follower_and_a_stranger_do_not_see_sales(): void
    {
        [, $role] = $this->scheduleWithASale();

        // Positive control. Without it every assertDontSee below would also pass on a fixture
        // that simply has no sale in it, which is exactly how a test like this rots into a no-op.
        $admin = $this->createOwner();
        $this->followRole($admin, $role, 'admin');
        $this->actingAs($admin)->get(route('sales'))->assertOk()->assertSee('Zaphod Beeblebrox');

        foreach (['viewer', 'follower'] as $level) {
            $user = $this->createOwner();
            $this->followRole($user, $role, $level);

            $this->actingAs($user)->get(route('sales'))
                ->assertOk()
                ->assertDontSee('Zaphod Beeblebrox', "a {$level} must not see buyer details");
        }

        $stranger = $this->createOwner();
        $this->actingAs($stranger)->get(route('sales'))
            ->assertOk()
            ->assertDontSee('Zaphod Beeblebrox');
    }

    public function test_a_curator_admin_sees_only_what_the_curator_created(): void
    {
        [, $venue, $listedEvent] = $this->scheduleWithASale('Ford Prefect');

        $curatorOwner = $this->createOwner();
        $curator = $this->createCurator($curatorOwner);

        // The curator merely LISTS the venue's event.
        $listedEvent->roles()->attach($curator->id, ['is_accepted' => true]);

        // ...and separately runs one of its own.
        $ownEvent = $this->createEvent($curator, [
            'name' => 'Curator Own Event',
            'tickets_enabled' => true,
            'creator_role_id' => $curator->id,
        ]);
        $ownTicket = $this->createTicket($ownEvent, ['price' => 10]);
        $this->createSale($ownEvent, $curator, [
            'name' => 'Trillian Astra',
            'email' => 'trillian@gmail.com',
            'payment_amount' => 10,
            'status' => 'paid',
        ], $ownTicket);

        $curatorAdmin = $this->createOwner();
        $this->followRole($curatorAdmin, $curator, 'admin');

        $this->actingAs($curatorAdmin)->get(route('sales'))
            ->assertOk()
            ->assertSee('Trillian Astra')
            ->assertDontSee('Ford Prefect', 'a curator that only lists an event does not own its buyer data');
    }

    public function test_the_scan_picker_includes_viewers_but_the_checkin_picker_does_not(): void
    {
        [, $role, $event] = $this->scheduleWithASale();
        $hash = UrlUtils::encodeId($event->id);

        $admin = $this->createOwner();
        $this->followRole($admin, $role, 'admin');
        $viewer = $this->createOwner();
        $this->followRole($viewer, $role, 'viewer');

        // Both views hand Vue an @json($events) list and a pre-selected id derived from it. Assert
        // the selection too: an event present in the list but with selectedEventId null still
        // renders as an empty picker, which is the symptom being fixed.
        $selected = 'selectedEventId: "'.$hash.'"';

        // canScanEvent() admits viewers, so the picker that feeds it must too.
        $this->actingAs($admin)->get(route('ticket.scan'))->assertOk()->assertSee($hash)->assertSee($selected, false);
        $this->actingAs($viewer)->get(route('ticket.scan'))->assertOk()->assertSee($hash)->assertSee($selected, false);

        // checkin/stats is guarded by canViewEventData(), which does not admit viewers.
        $this->actingAs($admin)->get(route('checkin.index'))->assertOk()->assertSee($hash)->assertSee($selected, false);
        $this->actingAs($viewer)->get(route('checkin.index'))->assertOk()->assertDontSee($hash);
    }

    public function test_hosted_team_access_closes_when_the_schedule_is_not_enterprise(): void
    {
        // Mirrors RoleController::viewAdmin(): on hosted, a team member only reaches somebody
        // else's schedule while it is Enterprise. Without this a previous owner demoted to
        // 'admin' by a transfer would keep reading the new owner's sales.
        config(['app.hosted' => true]);

        [$owner, $role] = $this->scheduleWithASale();

        $admin = $this->createOwner();
        $this->followRole($admin, $role, 'admin');

        // createRole() defaults to Enterprise, so this is the working case - and it is what stops
        // the downgrade assertion below from passing merely because nothing is ever visible.
        $this->actingAs($admin)->get(route('sales'))->assertOk()->assertSee('Zaphod Beeblebrox');

        $role->plan_type = 'free';
        $role->plan_expires = now()->subYear()->format('Y-m-d');
        $role->save();

        // Re-resolve the user. manageableRoles() memoizes on the model instance, and a real
        // second request re-hydrates auth()->user() from the session; reusing the same PHP object
        // across two simulated requests would assert against the pre-downgrade memo.
        $this->actingAs($admin->fresh())->get(route('sales'))
            ->assertOk()
            ->assertDontSee('Zaphod Beeblebrox');

        // The owner is never gated by their own schedule's plan.
        $this->actingAs($owner)->get(route('sales'))->assertOk()->assertSee('Zaphod Beeblebrox');
    }

    /**
     * The gate above is right; what it never did was say anything. A blocked member got the page's
     * ordinary empty state - "No sales found. Create events to start selling tickets." - which is
     * false twice over: the sales exist, and creating events is not what unblocks them. That is
     * what turned a plan limit into three rounds of support email.
     */
    public function test_a_plan_blocked_member_is_told_why_rather_than_shown_no_sales(): void
    {
        config(['app.hosted' => true]);

        [, $role] = $this->scheduleWithASale();
        $role->name = 'Ed Presents';
        $role->save();

        $admin = $this->createOwner();
        $this->followRole($admin, $role, 'admin');

        // Positive control: while the schedule is Enterprise there is no notice, so the
        // assertions below cannot pass on a page that renders one unconditionally.
        $this->actingAs($admin)->get(route('sales'))
            ->assertOk()
            ->assertSee('Zaphod Beeblebrox')
            ->assertDontSee('Team member access requires the Enterprise plan');

        $role->plan_type = 'free';
        $role->plan_expires = now()->subYear()->format('Y-m-d');
        $role->save();

        // Re-resolve: manageableRoles() and planBlockedRoles() both memoize on the model instance.
        $this->actingAs($admin->fresh())->get(route('sales'))
            ->assertOk()
            ->assertDontSee('Zaphod Beeblebrox')
            ->assertSee('Team member access requires the Enterprise plan')
            // Names the schedule, so a member who helps run several knows which one went dark.
            ->assertSee('Ed Presents')
            ->assertDontSee('Create events to start selling tickets');
    }

    public function test_the_scan_and_checkin_pickers_explain_the_plan_block(): void
    {
        config(['app.hosted' => true]);

        [, $role] = $this->scheduleWithASale();

        $admin = $this->createOwner();
        $this->followRole($admin, $role, 'admin');

        $this->actingAs($admin)->get(route('ticket.scan'))
            ->assertOk()
            ->assertDontSee('Team member access requires the Enterprise plan');

        $role->plan_type = 'free';
        $role->plan_expires = now()->subYear()->format('Y-m-d');
        $role->save();

        // Scanning is available on every plan, so an empty picker here reads as "you have no
        // events" rather than "this schedule stopped covering you".
        $this->actingAs($admin->fresh())->get(route('ticket.scan'))
            ->assertOk()
            ->assertSee('Team member access requires the Enterprise plan');

        $this->actingAs($admin->fresh())->get(route('checkin.index'))
            ->assertOk()
            ->assertSee('Team member access requires the Enterprise plan');
    }

    /**
     * The other side of the same silence: members added while the schedule was Enterprise stay
     * listed after a downgrade, with nothing telling the owner their staff have gone blind.
     */
    public function test_the_owner_is_warned_when_the_plan_no_longer_covers_their_members(): void
    {
        config(['app.hosted' => true]);

        $owner = $this->createOwner();
        $role = $this->createRole($owner);

        $admin = $this->createOwner();
        $this->followRole($admin, $role, 'admin');

        $team = route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'team']);

        $this->actingAs($owner)->get($team)
            ->assertOk()
            ->assertDontSee('Team members cannot see this schedule');

        $role->plan_type = 'free';
        $role->plan_expires = now()->subYear()->format('Y-m-d');
        $role->save();

        $this->actingAs($owner->fresh())->get($team)
            ->assertOk()
            ->assertSee('Team members cannot see this schedule');
    }

    public function test_only_the_owner_may_change_levels_or_remove_other_members(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);

        $admin = $this->createOwner();
        $this->followRole($admin, $role, 'admin');
        $other = $this->createOwner();
        $this->followRole($other, $role, 'viewer');

        $otherHash = UrlUtils::encodeId($other->id);

        $this->actingAs($admin)->patch(route('role.update_member_level', [
            'subdomain' => $role->subdomain, 'hash' => $otherHash,
        ]), ['level' => 'admin'])->assertForbidden();

        $this->actingAs($admin)->delete(route('role.remove_member', [
            'subdomain' => $role->subdomain, 'hash' => $otherHash,
        ]))->assertForbidden();

        $this->assertDatabaseHas('role_user', [
            'role_id' => $role->id, 'user_id' => $other->id, 'level' => 'viewer',
        ]);

        // Inviting is still an admin's job, per the docs.
        User::factory()->create(['email' => 'invitee@gmail.com']);
        $this->actingAs($admin)->post(route('role.store_member', ['subdomain' => $role->subdomain]), [
            'email' => 'invitee@gmail.com', 'name' => 'Invitee', 'level' => 'admin',
        ]);
        $this->assertDatabaseHas('role_user', [
            'role_id' => $role->id, 'level' => 'admin',
            'user_id' => User::where('email', 'invitee@gmail.com')->value('id'),
        ]);

        // And the owner can still do both.
        $this->actingAs($owner)->patch(route('role.update_member_level', [
            'subdomain' => $role->subdomain, 'hash' => $otherHash,
        ]), ['level' => 'admin']);
        $this->assertDatabaseHas('role_user', [
            'role_id' => $role->id, 'user_id' => $other->id, 'level' => 'admin',
        ]);
    }

    public function test_a_curator_admin_cannot_act_on_a_sale_for_an_event_it_only_lists(): void
    {
        [, , $listedEvent, $sale] = $this->scheduleWithASale();

        $curator = $this->createCurator($this->createOwner());
        $listedEvent->roles()->attach($curator->id, ['is_accepted' => true]);

        $curatorAdmin = $this->createOwner();
        $this->followRole($curatorAdmin, $curator, 'admin');

        $this->actingAs($curatorAdmin)->post(route('sales.action', [
            'sale_id' => UrlUtils::encodeId($sale->id),
        ]), ['action' => 'refund'])->assertForbidden();

        // Positive control: the same admin CAN act on a sale for an event the curator created,
        // so the assertion above cannot pass just because the action path is broken for everyone.
        $own = $this->createEvent($curator, ['name' => 'Curator Own', 'creator_role_id' => $curator->id]);
        $ownSale = $this->createSale($own, $curator, ['name' => 'Own Buyer', 'status' => 'paid']);

        $this->actingAs($curatorAdmin)
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->post(route('sales.action', [
                'sale_id' => UrlUtils::encodeId($ownSale->id),
            ]), ['action' => 'cancel'])
            ->assertOk()
            ->assertJson(['success' => true]);
    }

    public function test_an_admin_can_see_and_clear_the_waitlist(): void
    {
        // WaitlistController::remove() has no separate gate - its whereHas IS the authorization,
        // so the list and the delete have to widen together or the button orphans.
        [, $role, $event] = $this->scheduleWithASale();

        $entry = TicketWaitlist::create([
            'event_id' => $event->id,
            'event_date' => Carbon::parse($event->starts_at)->format('Y-m-d'),
            'name' => 'Slartibartfast',
            'email' => 'slarti@gmail.com',
            'subdomain' => $role->subdomain,
            'status' => 'waiting',
        ]);

        $admin = $this->createOwner();
        $this->followRole($admin, $role, 'admin');

        $this->actingAs($admin)->get(route('waitlist.index'))
            ->assertOk()
            ->assertSee('Slartibartfast');

        $this->actingAs($admin)->post(route('waitlist.remove', ['id' => UrlUtils::encodeId($entry->id)]))
            ->assertOk();
        $this->assertDatabaseMissing('ticket_waitlists', ['id' => $entry->id]);

        // A stranger still gets nothing to act on.
        $other = TicketWaitlist::create([
            'event_id' => $event->id,
            'event_date' => Carbon::parse($event->starts_at)->format('Y-m-d'),
            'name' => 'Slartibartfast',
            'email' => 'slarti@gmail.com',
            'subdomain' => $role->subdomain,
            'status' => 'waiting',
        ]);
        $this->actingAs($this->createOwner())
            ->post(route('waitlist.remove', ['id' => UrlUtils::encodeId($other->id)]))
            ->assertNotFound();
    }

    public function test_a_schedule_that_declined_an_event_does_not_see_its_sales(): void
    {
        // Declining does NOT detach the pivot - EventController::decline() and ::uncurate() both
        // just set is_accepted = false - so a scope that ignored is_accepted handed the buyer
        // list to a schedule that turned the event down.
        [, $venue, $event] = $this->scheduleWithASale();

        $declined = $this->createRole($this->createOwner(), 'venue', ['name' => 'Declining Venue']);
        $event->roles()->attach($declined->id, ['is_accepted' => false]);
        $declinedAdmin = $this->createOwner();
        $this->followRole($declinedAdmin, $declined, 'admin');

        // Positive control on the SAME event, so this cannot pass on an empty fixture.
        $accepted = $this->createRole($this->createOwner(), 'venue', ['name' => 'Accepting Venue']);
        $event->roles()->attach($accepted->id, ['is_accepted' => true]);
        $acceptedAdmin = $this->createOwner();
        $this->followRole($acceptedAdmin, $accepted, 'admin');

        $this->actingAs($acceptedAdmin)->get(route('sales'))
            ->assertOk()->assertSee('Zaphod Beeblebrox');

        $this->actingAs($declinedAdmin)->get(route('sales'))
            ->assertOk()
            ->assertDontSee('Zaphod Beeblebrox', 'a schedule that declined an event must not see its buyers');

        // A pending request (pivot null) has not been accepted either.
        $pending = $this->createRole($this->createOwner(), 'venue', ['name' => 'Pending Venue']);
        $event->roles()->attach($pending->id, ['is_accepted' => null]);
        $pendingAdmin = $this->createOwner();
        $this->followRole($pendingAdmin, $pending, 'admin');

        $this->actingAs($pendingAdmin)->get(route('sales'))
            ->assertOk()->assertDontSee('Zaphod Beeblebrox');

        $this->assertTrue($venue->exists);
    }

    public function test_an_admin_still_sees_their_own_pending_and_cancelled_bookings(): void
    {
        // AppointmentService attaches a booking at is_accepted null when the type requires
        // approval, and flips it to false on cancel. Those are the schedule's OWN bookings and
        // the docs promise paid ones appear on the Sales page, so the accepted-pivot rule must
        // not swallow them.
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $type = $this->createAppointmentType($role, ['requires_approval' => true, 'price' => 40]);

        foreach ([['is_accepted' => null, 'buyer' => 'Pending Booker'],
            ['is_accepted' => false, 'buyer' => 'Cancelled Booker']] as $case) {
            $event = $this->createEvent($role, [
                'name' => 'Booking '.$case['buyer'],
                'appointment_type_id' => $type->id,
                'creator_role_id' => $role->id,
                'is_private' => true,
                'is_accepted' => $case['is_accepted'],
            ]);
            $this->createSale($event, $role, [
                'name' => $case['buyer'],
                'email' => 'booker@gmail.com',
                'payment_amount' => 40,
                'status' => 'paid',
            ]);
        }

        $admin = $this->createOwner();
        $this->followRole($admin, $role, 'admin');

        $this->actingAs($admin)->get(route('sales'))
            ->assertOk()
            ->assertSee('Pending Booker')
            ->assertSee('Cancelled Booker');
    }

    public function test_every_sale_the_list_returns_is_one_the_user_can_act_on(): void
    {
        // The invariant the scopes exist to hold: managedBy() is a strict SUBSET of
        // canViewEventData(), so no row can be listed whose action buttons would 403. Asserted
        // over a deliberately awkward mix rather than one hand-picked fixture.
        $owner = $this->createOwner();
        $venue = $this->createRole($owner);
        $admin = $this->createOwner();
        $this->followRole($admin, $venue, 'admin');

        $curator = $this->createCurator($this->createOwner());
        $this->followRole($admin, $curator, 'admin');

        $mine = $this->createEvent($venue, ['name' => 'Mine', 'creator_role_id' => $venue->id]);
        $this->createSale($mine, $venue, ['name' => 'Buyer Mine', 'status' => 'paid']);

        // Created by my curator.
        $curated = $this->createEvent($curator, ['name' => 'Curated', 'creator_role_id' => $curator->id]);
        $this->createSale($curated, $curator, ['name' => 'Buyer Curated', 'status' => 'paid']);

        // Only listed by my curator - must not be listed at all.
        $foreign = $this->createEvent($this->createRole($this->createOwner()), ['name' => 'Foreign']);
        $foreign->roles()->attach($curator->id, ['is_accepted' => true]);
        $this->createSale($foreign, $curator, ['name' => 'Buyer Foreign', 'status' => 'paid']);

        // Detached pivot, stale creator_role_id - the state CheckData::checkEventCreatorRoles finds.
        $orphan = $this->createEvent($venue, ['name' => 'Orphan', 'creator_role_id' => $venue->id]);
        $orphan->roles()->detach($venue->id);
        $this->createSale($orphan, $venue, ['name' => 'Buyer Orphan', 'status' => 'paid']);

        // My own booking, still awaiting approval.
        $booking = $this->createEvent($venue, [
            'name' => 'Booking', 'creator_role_id' => $venue->id, 'is_accepted' => null,
        ]);
        $this->createSale($booking, $venue, ['name' => 'Buyer Booking', 'status' => 'paid']);

        $listed = Sale::whereHas('event', fn ($q) => $q->managedBy($admin))
            ->with('event.roles')->get();

        $this->assertNotEmpty($listed, 'fixture produced no rows, so the loop below proves nothing');

        foreach ($listed as $sale) {
            $this->assertTrue(
                $admin->canViewEventData($sale->event),
                "listed but not actionable: {$sale->name} on {$sale->event->name}"
            );
        }

        $names = $listed->pluck('name')->all();
        $this->assertContains('Buyer Mine', $names);
        $this->assertContains('Buyer Curated', $names);
        $this->assertContains('Buyer Booking', $names);
        $this->assertNotContains('Buyer Foreign', $names, 'a curator that only lists does not own the buyers');
        $this->assertNotContains('Buyer Orphan', $names, 'creator_role_id alone must not grant - the actions would 403');
    }

    public function test_following_a_pro_schedule_does_not_unlock_the_csv_export(): void
    {
        config(['app.hosted' => true]);

        // My own schedule is free and lapsed, so I have no Pro of my own.
        $me = $this->createOwner();
        $this->createRole($me, 'venue', [
            'plan_type' => 'free',
            'plan_expires' => now()->subYear()->format('Y-m-d'),
        ]);

        // Merely FOLLOWING somebody's Pro schedule used to satisfy the gate.
        $proRole = $this->createRole($this->createOwner(), 'venue', [
            'plan_type' => 'pro',
            'plan_expires' => now()->addYear()->format('Y-m-d'),
        ]);
        $this->followRole($me, $proRole, 'follower');

        $this->actingAs($me)->get(route('sales.export'))->assertForbidden();
    }
}
