<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The Free plan's two allowances: 25 paid tickets a month per schedule, and one appointment type.
 *
 * Both are hosted-only, so every test that exercises a limit must force app.hosted=true AND build
 * a genuinely free schedule - CreatesScheduleData::createRole() defaults to enterprise, and
 * Role::isPro() short-circuits to true off-hosted, so a role is Pro by default in this suite.
 */
class FreePlanLimitsTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    /**
     * A genuinely free schedule on a hosted install. Every piece matters: isPro() short-circuits
     * to true off-hosted, createRole() defaults to enterprise, and onGenericTrial() would make a
     * role with a future trial_ends_at Pro again.
     */
    private function createFreeRole(?User $owner = null, string $type = 'venue'): Role
    {
        config(['app.hosted' => true]);

        return $this->createRole($owner ?? $this->createOwner(), $type, [
            'plan_type' => 'free',
            'plan_expires' => now()->subYear()->format('Y-m-d'),
            'trial_ends_at' => null,
        ]);
    }

    public function test_the_fixture_really_is_free(): void
    {
        $role = $this->createFreeRole();

        $this->assertFalse($role->fresh()->isPro(), 'sanity check: the fixture is not Pro');
        $this->assertSame(25, $role->fresh()->ticketSaleLimit());
        $this->assertSame(1, $role->fresh()->appointmentTypeLimit());
    }

    public function test_paid_plans_selfhost_and_demo_are_unlimited_and_never_count(): void
    {
        $pro = $this->createFreeRole();
        $pro->plan_type = 'pro';
        $pro->plan_expires = now()->addYear()->format('Y-m-d');
        $pro->save();
        $this->assertNull($pro->fresh()->ticketSaleLimit(), 'Pro is unlimited');
        $this->assertNull($pro->fresh()->appointmentTypeLimit());

        $free = $this->createFreeRole();
        config(['app.hosted' => false]);
        $this->assertNull($free->fresh()->ticketSaleLimit(), 'selfhost is unlimited');

        // The demo schedule is seeded with hundreds of paid sales and exists to show the product
        // working, so it must never render the exhausted paywall.
        config(['app.hosted' => true]);
        $demoOwner = $this->createOwner();
        $demoOwner->email = \App\Services\DemoService::DEMO_EMAIL;
        $demoOwner->save();
        $demo = $this->createFreeRole($demoOwner);
        $this->assertNull($demo->fresh()->ticketSaleLimit(), 'the demo schedule is unlimited');
    }

    public function test_the_allowance_counts_paid_tickets_and_blocks_at_the_cap(): void
    {
        $role = $this->createFreeRole();
        $event = $this->createEvent($role);
        $ticket = $this->createTicket($event, ['price' => 20, 'quantity' => 500]);

        $this->createSale($event, $role, ['status' => 'paid'], $ticket, 24);
        $this->assertSame(24, $role->fresh()->ticketsSoldThisMonth());
        $this->assertTrue($role->fresh()->canSellPaidTickets(), 'one below the cap still sells');

        $this->createSale($event, $role, ['status' => 'paid'], $ticket, 1);
        $this->assertSame(25, $role->fresh()->ticketsSoldThisMonth());
        $this->assertFalse($role->fresh()->canSellPaidTickets(), 'at the cap paid checkout stops');
    }

    /**
     * Cash is counted but never refused. There is no processing cost to us, and an organizer taking
     * money at the door must never find the app refusing to record it.
     *
     * This lives in hasTicketAllowance(), not the controller: when the carve-out sat only in
     * checkout(), canSellTickets() had already refused one gate earlier and it was unreachable.
     */
    public function test_cash_is_counted_but_never_blocked_at_the_cap(): void
    {
        $role = $this->createFreeRole();
        $event = $this->createEvent($role, ['tickets_enabled' => true, 'payment_method' => 'cash']);
        $ticket = $this->createTicket($event, ['price' => 20, 'quantity' => 500]);

        $this->createSale($event, $role, ['status' => 'paid'], $ticket, 25);

        $this->assertSame(25, $role->fresh()->ticketsSoldThisMonth(), 'cash sales still count');
        $this->assertFalse($role->fresh()->canSellPaidTickets(), 'the schedule is over its allowance');

        $this->assertTrue(
            $event->fresh()->canSellTickets(),
            'an offline-payment event keeps selling at the cap - this is what the guest page reads'
        );
        $this->assertTrue($ticket->fresh()->isSellable(), 'and so does the individual paid row');
    }

    public function test_an_online_payment_event_is_blocked_at_the_cap(): void
    {
        $role = $this->createFreeRole();
        $event = $this->createEvent($role, ['tickets_enabled' => true, 'payment_method' => 'stripe']);
        $ticket = $this->createTicket($event, ['price' => 20, 'quantity' => 500]);

        $this->createSale($event, $role, ['status' => 'paid'], $ticket, 25);

        $this->assertFalse($event->fresh()->canSellTickets());
        $this->assertFalse($ticket->fresh()->isSellable());
    }

    /**
     * A $0 tier keeps the event selling, but must not carry the PAID rows through with it - that
     * was a route to unlimited paid sales on a capped schedule.
     */
    public function test_a_free_tier_keeps_selling_at_the_cap_without_unlocking_the_paid_rows(): void
    {
        $role = $this->createFreeRole();
        $event = $this->createEvent($role, ['tickets_enabled' => true, 'payment_method' => 'stripe']);
        $paid = $this->createTicket($event, ['price' => 20, 'quantity' => 500]);
        $free = $this->createTicket($event, ['price' => 0, 'quantity' => 500]);

        $this->createSale($event, $role, ['status' => 'paid'], $paid, 25);

        $this->assertTrue($event->fresh()->canSellTickets(), 'the event still sells its free tier');
        $this->assertTrue($free->fresh()->isSellable(), 'the free tier is unlimited on every plan');
        $this->assertFalse($paid->fresh()->isSellable(), 'the paid tier is still refused');
    }

    /** The allowance never stops sales for an event that is actually happening. */
    public function test_the_grace_window_applies_to_a_recurring_occurrence(): void
    {
        $role = $this->createFreeRole();
        // A weekly event whose ANCHOR is long past - the case where passing no date silently
        // resolved to the anchor and the grace never fired.
        $event = $this->createEvent($role, [
            'tickets_enabled' => true,
            'payment_method' => 'stripe',
            'starts_at' => now()->subMonths(3)->setTime(20, 0)->format('Y-m-d H:i:s'),
            'days_of_week' => '1111111',
        ]);
        $ticket = $this->createTicket($event, ['price' => 20, 'quantity' => 500]);
        $this->createSale($event, $role, ['status' => 'paid'], $ticket, 25);

        $tomorrow = now()->addDay()->format('Y-m-d');

        $this->assertTrue(
            $event->fresh()->withinTicketAllowanceGrace($tomorrow),
            'an occurrence inside the window is exempt, even though the recurrence anchor is months old'
        );
        $this->assertTrue($event->fresh()->canSellTickets($tomorrow));

        $farOff = now()->addDays(20)->format('Y-m-d');
        $this->assertFalse($event->fresh()->canSellTickets($farOff), 'a distant occurrence is still capped');
    }

    /**
     * The allowance follows the event's owning schedule, not the storefront. Attributing to the
     * subdomain let a free account create events on one schedule, list them on a curator schedule,
     * and sell through the curator - whose own count stayed permanently zero.
     */
    public function test_selling_through_a_curator_subdomain_still_spends_the_owning_schedules_allowance(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createFreeRole($owner, 'venue');
        $curator = $this->createFreeRole($owner, 'curator');

        $event = $this->createEvent($venue, ['tickets_enabled' => true, 'payment_method' => 'stripe']);
        $event->roles()->attach($curator->id, ['is_accepted' => true]);
        $ticket = $this->createTicket($event, ['price' => 20, 'quantity' => 500]);
        $this->createSale($event, $venue, ['status' => 'paid'], $ticket, 25);

        $this->assertSame(0, $curator->fresh()->ticketsSoldThisMonth(), 'the curator itself sold nothing');
        $this->assertSame($venue->id, $event->fresh()->ticketAllowanceRole()->id);
        $this->assertFalse(
            $event->fresh()->canSellTickets(),
            'the cap binds through every subdomain, including the curator that lists the event'
        );
    }

    public function test_the_allowance_ignores_everything_that_is_not_a_paid_ticket(): void
    {
        $role = $this->createFreeRole();
        $event = $this->createEvent($role);
        $paid = $this->createTicket($event, ['price' => 20, 'quantity' => 500]);
        $freeTicket = $this->createTicket($event, ['price' => 0, 'quantity' => 500]);
        $addon = $this->createTicket($event, ['price' => 5, 'quantity' => 500, 'is_addon' => true]);

        // Zero-price rows: a $0 order through the ticketing system is stored with the event's
        // payment method and status paid, so without the price filter free registration would
        // silently consume the paid allowance.
        $this->createSale($event, $role, ['status' => 'paid'], $freeTicket, 10);
        $this->createSale($event, $role, ['status' => 'paid'], $addon, 10);
        $this->createSale($event, $role, ['status' => 'paid', 'payment_method' => 'rsvp'], $paid, 10);
        $this->createSale($event, $role, ['status' => 'paid', 'payment_method' => 'import'], $paid, 10);
        $this->createSale($event, $role, ['status' => 'unpaid'], $paid, 10);
        $this->createSale($event, $role, ['status' => 'refunded'], $paid, 10);
        $this->createSale($event, $role, ['status' => 'paid', 'is_deleted' => true], $paid, 10);

        $this->assertSame(0, $role->fresh()->ticketsSoldThisMonth());

        // And an appointment booking, which creates a real Sale and a real SaleTicket.
        $type = $this->createAppointmentType($role);
        $booking = $this->createEvent($role, ['appointment_type_id' => $type->id]);
        $bookingTicket = $this->createTicket($booking, ['price' => 50]);
        $this->createSale($booking, $role, ['status' => 'paid'], $bookingTicket, 1);

        $this->assertSame(0, $role->fresh()->ticketsSoldThisMonth(), 'appointment bookings have their own allowance');
    }

    public function test_the_window_starts_when_the_schedule_dropped_to_free(): void
    {
        $role = $this->createFreeRole();
        $event = $this->createEvent($role);
        $ticket = $this->createTicket($event, ['price' => 20, 'quantity' => 500]);

        // Sold while still on Pro, earlier this month.
        $sale = $this->createSale($event, $role, ['status' => 'paid'], $ticket, 200);
        $sale->paid_at = now()->startOfMonth()->addDay();
        $sale->save();

        // The plan lapsed after that.
        $role->plan_expires = now()->startOfMonth()->addDays(5)->format('Y-m-d');
        $role->save();

        $this->assertSame(
            0,
            $role->fresh()->ticketsSoldThisMonth(),
            'sales made while the schedule was paid do not count against its free allowance'
        );
        $this->assertTrue($role->fresh()->canSellPaidTickets());
    }

    public function test_the_per_owner_backstop_blocks_spreading_across_schedules(): void
    {
        config(['usage.ticket_sale_user_monthly_limit_free' => 30]);

        $owner = $this->createOwner();
        $a = $this->createFreeRole($owner, 'venue');
        $b = $this->createFreeRole($owner, 'talent');

        foreach ([$a, $b] as $role) {
            $event = $this->createEvent($role);
            $ticket = $this->createTicket($event, ['price' => 20, 'quantity' => 500]);
            $this->createSale($event, $role, ['status' => 'paid'], $ticket, 20);
        }

        // Each schedule is under its own 25, but the owner is over the 30 backstop.
        $this->assertSame(20, $a->fresh()->ticketsSoldThisMonth(), 'the per-schedule count stays per schedule');
        $this->assertSame(40, $a->fresh()->ownerTicketsSoldThisMonth());
        $this->assertFalse($a->fresh()->canSellPaidTickets(), 'the owner backstop bites');
    }

    public function test_a_curator_is_not_charged_for_events_it_only_lists(): void
    {
        $venueOwner = $this->createOwner();
        $venue = $this->createFreeRole($venueOwner, 'venue');
        $curator = $this->createFreeRole($this->createOwner(), 'curator');

        $event = $this->createEvent($venue);
        $event->roles()->attach($curator->id, ['is_accepted' => true]);
        $ticket = $this->createTicket($event, ['price' => 20, 'quantity' => 500]);
        $this->createSale($event, $venue, ['status' => 'paid'], $ticket, 5);

        $this->assertSame(5, $venue->fresh()->ticketsSoldThisMonth(), 'the venue that owns the event pays for it');
        $this->assertSame(0, $curator->fresh()->ticketsSoldThisMonth(), 'the curator merely listing it does not');
    }

    public function test_appointment_types_are_capped_at_one_on_free(): void
    {
        $role = $this->createFreeRole();

        $this->assertTrue($role->fresh()->canCreateAppointmentType());

        $this->createAppointmentType($role, ['name' => 'Consultation']);
        $this->assertSame(1, $role->fresh()->appointmentTypeCount());
        $this->assertFalse($role->fresh()->canCreateAppointmentType(), 'the second type is refused');

        // Soft-deleted types do not hold a slot.
        $role->appointmentTypes()->update(['is_deleted' => true]);
        $this->assertTrue($role->fresh()->canCreateAppointmentType());
    }

    public function test_an_over_cap_schedule_keeps_only_its_oldest_bookable_type(): void
    {
        $role = $this->createFreeRole();

        // A lapsed Pro schedule can hold several. They are clamped, never deleted.
        $first = $this->createAppointmentType($role, ['name' => 'Consultation']);
        $this->createAppointmentType($role, ['name' => 'Lesson']);
        $this->createAppointmentType($role, ['name' => 'Rehearsal']);

        $this->assertSame(3, $role->fresh()->appointmentTypeCount(), 'nothing is deleted');

        $bookable = $role->fresh()->bookableAppointmentTypes();
        $this->assertCount(1, $bookable);
        $this->assertSame($first->id, $bookable->first()->id, 'the oldest bookable type stays live');

        // On Pro every type is bookable again.
        $role->plan_type = 'pro';
        $role->plan_expires = now()->addYear()->format('Y-m-d');
        $role->save();
        $this->assertCount(3, $role->fresh()->bookableAppointmentTypes(), 'upgrading lights them all up');
    }

    // ---------------------------------------------------------------------------------------
    // Leak tests.
    //
    // Every feature below was gated ONLY by the fact that a free schedule could never produce a
    // paid sale. Opening ticket selling removed that protection, so each now needs an explicit
    // gate - and each of these tests fails if one is ever removed. The fixtures deliberately give
    // the free schedule a real paid ticket and a real paid sale, which is precisely the state that
    // used to be unreachable.
    // ---------------------------------------------------------------------------------------

    /** @return array{0: Role, 1: \App\Models\Event, 2: \App\Models\Ticket, 3: Sale, 4: User} */
    private function freeScheduleWithAPaidSale(): array
    {
        $owner = $this->createOwner();
        $role = $this->createFreeRole($owner);
        $event = $this->createEvent($role, [
            'starts_at' => now()->format('Y-m-d H:i:s'),
            'tickets_enabled' => true,
        ]);
        $ticket = $this->createTicket($event, ['price' => 20, 'quantity' => 50]);
        $sale = $this->createSale($event, $role, [
            'status' => 'paid',
            'event_date' => now()->format('Y-m-d'),
        ], $ticket, 1);

        return [$role, $event, $ticket, $sale, $owner];
    }

    /**
     * Scanning follows the TICKET, not the plan: a schedule that sold a ticket must be able to
     * admit its holder, including after its plan lapses. The Pro feature is the check-in dashboard
     * (live stats, per-ticket breakdown), not the door.
     */
    public function test_scanning_works_on_free_but_the_checkin_dashboard_stays_pro(): void
    {
        [, $event, , $sale, $owner] = $this->freeScheduleWithAPaidSale();

        $this->assertTrue(
            $owner->canScanEvent($event->fresh()),
            'a free schedule can admit the holder of a ticket it sold'
        );

        $this->actingAs($owner)->post(route('ticket.scanned', [
            'event_id' => \App\Utils\UrlUtils::encodeId($event->id),
            'secret' => $sale->secret,
        ]))->assertOk();

        // The richer dashboard is what Pro buys.
        $this->actingAs($owner)->get(route('checkin.stats', [
            'event_id' => \App\Utils\UrlUtils::encodeId($event->id),
            'date' => now()->format('Y-m-d'),
        ]))->assertStatus(403);
    }

    public function test_the_ticket_waitlist_stays_pro_but_the_rsvp_waitlist_stays_free(): void
    {
        $owner = $this->createOwner();
        $role = $this->createFreeRole($owner);
        $date = now()->addDays(7)->format('Y-m-d');

        // Sold-out PAID event: the waitlist is Pro, so the join endpoint must not exist for it.
        $ticketed = $this->createEvent($role, [
            'starts_at' => now()->addDays(7)->setTime(12, 0)->format('Y-m-d H:i:s'),
            'tickets_enabled' => true,
        ]);
        $ticket = $this->createTicket($ticketed, ['price' => 10, 'quantity' => 1]);
        $this->createSale($ticketed, $role, ['status' => 'paid', 'event_date' => $date], $ticket, 1);

        $this->post(route('waitlist.join', ['subdomain' => $role->subdomain]), [
            'event_id' => \App\Utils\UrlUtils::encodeId($ticketed->id),
            'event_date' => $date,
            'name' => 'Hopeful',
            'email' => 'waitlist@gmail.com',
        ])->assertNotFound();

        // The RSVP branch was always free and stays free - gating it would take working
        // functionality away from schedules that already rely on it.
        $rsvp = $this->createEvent($role, [
            'starts_at' => now()->addDays(7)->setTime(12, 0)->format('Y-m-d H:i:s'),
            'rsvp_enabled' => true,
            'rsvp_limit' => 1,
        ]);
        $rsvp->updateRsvpSold($date, 1);

        $this->post(route('waitlist.join', ['subdomain' => $role->subdomain]), [
            'event_id' => \App\Utils\UrlUtils::encodeId($rsvp->id),
            'event_date' => $date,
            'name' => 'Hopeful',
            'email' => 'rsvp-waitlist@gmail.com',
        ])->assertOk();
    }

    public function test_promo_codes_stay_pro_at_both_the_validate_and_apply_layers(): void
    {
        [$role, $event, $ticket] = $this->freeScheduleWithAPaidSale();

        $promo = \App\Models\PromoCode::create([
            'event_id' => $event->id,
            'code' => 'FREEBIE',
            'type' => 'percentage',
            'value' => 50,
            'is_active' => true,
        ]);

        // isValid() is the shared chokepoint, so one gate covers the guest validate endpoint and
        // the discount applied during checkout.
        $this->assertFalse($promo->fresh()->isValid(), 'a free schedule\'s promo code never validates');

        $this->post(route('promo_code.validate', ['subdomain' => $role->subdomain]), [
            'event_id' => \App\Utils\UrlUtils::encodeId($event->id),
            'code' => 'FREEBIE',
            'tickets' => [\App\Utils\UrlUtils::encodeId($ticket->id) => 1],
        ])->assertOk()->assertJson(['valid' => false]);
    }

    public function test_sales_csv_export_stays_pro(): void
    {
        [$role, , , , $owner] = $this->freeScheduleWithAPaidSale();

        $this->actingAs($owner)
            ->get(route('sales.export', ['role_id' => \App\Utils\UrlUtils::encodeId($role->id)]))
            ->assertStatus(403);
    }

    public function test_the_ticket_embed_widget_stays_pro_but_the_rsvp_embed_stays_free(): void
    {
        [$role, $event] = $this->freeScheduleWithAPaidSale();

        $base = route('event.view_guest', [
            'subdomain' => $role->subdomain,
            'slug' => $event->slug,
        ]);

        // Reachable by anyone who knows the URL shape, which is why the gate cannot live in the
        // editor view alone.
        $this->get($base.'?embed=1&tickets=true')->assertNotFound();
        $this->get($base.'?embed=1&rsvp=true')->assertOk();
    }

    public function test_pass_booking_stays_pro(): void
    {
        [, $event, $ticket, $sale] = $this->freeScheduleWithAPaidSale();

        $ticket->forceFill(['is_pass' => true, 'pass_allow_booking' => true])->save();

        $this->assertFalse(
            app(\App\Services\PassBookingService::class)->isBookable($sale->fresh()),
            'the pass-booking chokepoint must refuse a free schedule, including the public secret-link routes'
        );
    }

    public function test_ticket_extras_are_scrubbed_on_save_but_existing_rows_are_never_destroyed(): void
    {
        $owner = $this->createOwner();
        $role = $this->createFreeRole($owner);
        $event = $this->createEvent($role, ['tickets_enabled' => true]);

        // Rows a schedule kept from when it was Pro. Saving as a free schedule must leave them
        // alone: dormant is correct, deleted is not, and they come back on upgrade.
        $addon = $this->createTicket($event, ['price' => 5, 'is_addon' => true, 'type' => 'Parking']);
        $promo = \App\Models\PromoCode::create([
            'event_id' => $event->id, 'code' => 'KEEPME', 'type' => 'percentage',
            'value' => 10, 'is_active' => true,
        ]);

        $request = \Illuminate\Http\Request::create('/', 'POST', [
            'name' => 'Free Plan Event',
            'starts_at' => now()->addDays(3)->format('Y-m-d H:i:s'),
            'duration' => 2,
            'schedule_type' => 'one_time',
            'tickets_enabled' => 1,
            // All four are Pro and must be ignored rather than rejected, so a hand-posted payload
            // saves as an ordinary event instead of failing the whole save.
            'individual_tickets' => 1,
            'tickets' => [['type' => 'Season Pass', 'price' => 30, 'quantity' => 5, 'is_pass' => 1]],
            'addons' => [['type' => 'T-shirt', 'price' => 15, 'quantity' => 5]],
            'promo_codes' => [['code' => 'NEWCODE', 'type' => 'percentage', 'value' => 20]],
        ]);
        $request->setUserResolver(fn () => $owner);
        $this->app->instance('request', $request);

        app(\App\Repos\EventRepo::class)->saveEvent($role, $request, $event->fresh(), false);

        $event->refresh();
        $this->assertFalse((bool) $event->individual_tickets, 'individual tickets is Pro');
        $this->assertFalse(
            $event->tickets()->where('is_pass', true)->exists(),
            'a hand-posted pass flag is scrubbed to an ordinary ticket'
        );
        $this->assertFalse(
            \App\Models\PromoCode::where('event_id', $event->id)->where('code', 'NEWCODE')->exists(),
            'new promo codes are not persisted below Pro'
        );

        $this->assertTrue(
            \App\Models\PromoCode::where('id', $promo->id)->exists(),
            'an existing promo code from a lapsed Pro plan is left intact'
        );
        $this->assertFalse(
            (bool) $addon->fresh()->is_deleted,
            'an existing add-on is left dormant, never destroyed by a later save'
        );
    }

    /**
     * The scrub has to read the STORED flag, not whether an id was posted: the edit form always
     * sends an id for existing rows, so gating on `empty($data['id'])` let a free schedule create an
     * ordinary ticket and flip it into a pass on the next save.
     */
    public function test_a_free_schedule_cannot_turn_an_existing_ticket_into_a_pass(): void
    {
        $owner = $this->createOwner();
        $role = $this->createFreeRole($owner);
        $event = $this->createEvent($role, ['tickets_enabled' => true]);
        $ticket = $this->createTicket($event, ['price' => 20, 'type' => 'General']);

        $this->assertFalse((bool) $ticket->is_pass);

        $request = \Illuminate\Http\Request::create('/', 'POST', [
            'name' => 'Free Plan Event',
            'starts_at' => now()->addDays(3)->format('Y-m-d H:i:s'),
            'duration' => 2,
            'schedule_type' => 'one_time',
            'tickets_enabled' => 1,
            'tickets' => [[
                'id' => $ticket->id,
                'type' => 'General',
                'price' => 20,
                'quantity' => 50,
                'is_pass' => 1,
                'pass_allow_booking' => 1,
            ]],
        ]);
        $request->setUserResolver(fn () => $owner);
        $this->app->instance('request', $request);

        app(\App\Repos\EventRepo::class)->saveEvent($role, $request, $event->fresh(), false);

        $this->assertFalse(
            (bool) $ticket->fresh()->is_pass,
            'posting an existing ticket id must not smuggle the pass flag past the scrub'
        );
    }

    public function test_paid_at_is_stamped_when_a_sale_is_created_paid(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $event = $this->createEvent($role);
        $ticket = $this->createTicket($event, ['price' => 20]);

        $sale = $this->createSale($event, $role, ['status' => 'paid'], $ticket, 2);

        $this->assertNotNull($sale->paid_at, 'a sale created already paid is stamped on save');
    }

    public function test_paid_at_is_stamped_on_the_transition_and_not_moved_afterwards(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $event = $this->createEvent($role);
        $ticket = $this->createTicket($event, ['price' => 20]);

        // Cash sales are created unpaid and only become paid when the owner marks them paid, which
        // is exactly why the allowance counts paid_at rather than created_at.
        $sale = $this->createSale($event, $role, ['status' => 'unpaid', 'payment_method' => 'cash'], $ticket);
        $this->assertNull($sale->paid_at, 'an unpaid sale carries no payment time');

        $sale->status = 'paid';
        $sale->save();

        $stamped = $sale->fresh()->paid_at;
        $this->assertNotNull($stamped, 'marking a cash sale paid stamps the payment time');

        // A later unrelated save must not move it.
        $sale->name = 'Renamed Buyer';
        $sale->save();

        $this->assertEquals(
            $stamped->toDateTimeString(),
            $sale->fresh()->paid_at->toDateTimeString(),
            'paid_at is stamped once and never moved by later saves'
        );
    }

    public function test_grouped_guest_sales_get_paid_at_when_the_primary_is_paid(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $event = $this->createEvent($role);
        $ticket = $this->createTicket($event, ['price' => 20]);

        $primary = $this->createSale($event, $role, ['status' => 'unpaid'], $ticket);
        $primary->group_id = $primary->id;
        $primary->save();

        $guest = $this->createSale($event, $role, ['status' => 'unpaid', 'email' => 'guest@gmail.com'], $ticket);
        $guest->group_id = $primary->id;
        $guest->save();

        $primary->status = 'paid';
        $primary->save();

        // The cascade is a query-builder update, which fires no model hooks, so paid_at has to be
        // written explicitly there or grouped individual-ticket sales would never be counted.
        $cascaded = Sale::find($guest->id);
        $this->assertSame('paid', $cascaded->status, 'the guest sale is cascaded to paid');
        $this->assertNotNull($cascaded->paid_at, 'the cascaded guest sale is stamped too');
    }
}
