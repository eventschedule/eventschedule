<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The Free plan's boundaries: paid ticket selling is Pro/Enterprise, and one appointment type.
 *
 * Both are hosted-only, so every test that exercises a limit must force app.hosted=true AND build
 * a genuinely free schedule - CreatesScheduleData::createFreeRole() does both, because
 * createRole() defaults to enterprise and Role::isPro() short-circuits to true off-hosted.
 *
 * Note on fixtures: the grandfather is a stored column written once by the 2026_09_20 migration,
 * never derived from the sales table, so creating a paid sale in a test does NOT grandfather the
 * event. A test that wants a grandfathered event sets tickets_grandfathered_at explicitly.
 */
class FreePlanLimitsTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    public function test_the_fixture_really_is_free(): void
    {
        $role = $this->createFreeRole();

        $this->assertFalse($role->fresh()->isPro(), 'sanity check: the fixture is not Pro');
    }

    public function test_a_free_schedule_cannot_sell_a_priced_ticket(): void
    {
        $role = $this->createFreeRole();
        $event = $this->createEvent($role, ['tickets_enabled' => true, 'payment_method' => 'stripe']);
        $ticket = $this->createTicket($event, ['price' => 20, 'quantity' => 100]);

        $this->assertFalse($event->fresh()->canSellPaidTickets());
        $this->assertFalse($event->fresh()->canSellTickets());
        $this->assertFalse($ticket->fresh()->isSellable());
    }

    public function test_cash_is_refused_too_when_the_schedule_may_not_sell(): void
    {
        $role = $this->createFreeRole();
        $event = $this->createEvent($role, ['tickets_enabled' => true, 'payment_method' => 'cash']);
        $this->createTicket($event, ['price' => 20, 'quantity' => 100]);

        $this->assertFalse(
            $event->fresh()->canSellTickets(),
            'the old allowance never blocked offline money; the plan gate does'
        );
    }

    public function test_a_zero_price_row_keeps_selling_without_unlocking_the_paid_rows(): void
    {
        $role = $this->createFreeRole();
        $event = $this->createEvent($role, ['tickets_enabled' => true, 'payment_method' => 'stripe']);
        $free = $this->createTicket($event, ['price' => 0, 'quantity' => 100]);
        $paid = $this->createTicket($event, ['price' => 20, 'quantity' => 100]);

        $this->assertTrue($event->fresh()->canOfferTickets(), 'the buy button still renders');
        $this->assertTrue($event->fresh()->canSellTickets());
        $this->assertTrue($free->fresh()->isSellable(), 'free registration is unlimited on every tier');
        $this->assertFalse($paid->fresh()->isSellable(), 'the free row must not carry the paid one through');
    }

    public function test_a_grandfathered_event_keeps_selling(): void
    {
        $role = $this->createFreeRole();
        $event = $this->createEvent($role, ['tickets_enabled' => true, 'payment_method' => 'stripe']);
        $ticket = $this->createTicket($event, ['price' => 20, 'quantity' => 100]);

        $this->assertFalse($event->fresh()->canSellTickets(), 'not stamped, so it cannot sell');

        // Written only by the migration in production; set directly here for the same reason.
        $event->forceFill(['tickets_grandfathered_at' => now()])->saveQuietly();

        $this->assertTrue($event->fresh()->canSellTickets());
        $this->assertTrue($ticket->fresh()->isSellable());
    }

    public function test_the_stamp_does_not_leak_to_the_schedules_other_events(): void
    {
        $role = $this->createFreeRole();
        $stamped = $this->createEvent($role, ['tickets_enabled' => true, 'payment_method' => 'stripe']);
        $this->createTicket($stamped, ['price' => 20, 'quantity' => 100]);
        $stamped->forceFill(['tickets_grandfathered_at' => now()])->saveQuietly();

        $sibling = $this->createEvent($role, ['tickets_enabled' => true, 'payment_method' => 'stripe']);
        $this->createTicket($sibling, ['price' => 20, 'quantity' => 100]);

        $this->assertTrue($stamped->fresh()->canSellTickets());
        $this->assertFalse($sibling->fresh()->canSellTickets(), 'the amnesty is per event, not per schedule');
    }

    /**
     * There is no grace window any more. It existed to stop a monthly CAP killing an event's final
     * push; with no cap it was the only remaining bypass, and a daily recurring event always has an
     * occurrence inside 48 hours, so it amounted to permanent free selling.
     */
    public function test_an_imminent_event_does_not_sell_on_free(): void
    {
        $role = $this->createFreeRole();
        $event = $this->createEvent($role, [
            'tickets_enabled' => true,
            'payment_method' => 'stripe',
            'starts_at' => now()->addHours(6)->format('Y-m-d H:i:s'),
        ]);
        $this->createTicket($event, ['price' => 20, 'quantity' => 500]);

        $this->assertFalse($event->fresh()->canSellTickets(), 'imminence is not an entitlement');
    }

    public function test_a_daily_recurring_event_does_not_sell_on_free(): void
    {
        $role = $this->createFreeRole();
        // The shape that made the old grace permanent: the anchor is months old but there is always
        // an occurrence within 48 hours.
        $event = $this->createEvent($role, [
            'tickets_enabled' => true,
            'payment_method' => 'stripe',
            'starts_at' => now()->subMonths(3)->setTime(20, 0)->format('Y-m-d H:i:s'),
            'days_of_week' => '1111111',
        ]);
        $this->createTicket($event, ['price' => 20, 'quantity' => 500]);

        $this->assertFalse($event->fresh()->canSellTickets(now()->addDay()->format('Y-m-d')));
        $this->assertFalse($event->fresh()->canSellTickets(now()->addDays(20)->format('Y-m-d')));
    }

    /**
     * The gate follows the event's OWNING schedule, not the storefront and not whichever role
     * happens to be attached. Using Event::isPro() here would mean a free organizer could attach
     * any Pro venue or talent and open paid selling on their own subdomain, and a Pro curator
     * auto-sourcing events would silently grant it to every free schedule it sources from.
     */
    public function test_a_pro_schedule_listing_the_event_does_not_open_selling_for_a_free_creator(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createFreeRole($owner, 'venue');
        $curator = $this->createRole($this->createOwner(), 'curator');

        $event = $this->createEvent($venue, ['tickets_enabled' => true, 'payment_method' => 'stripe']);
        $event->roles()->attach($curator->id, ['is_accepted' => true]);
        $this->createTicket($event, ['price' => 20, 'quantity' => 500]);

        $this->assertTrue($curator->fresh()->isPro(), 'sanity check: the listing schedule really is Pro');
        $this->assertSame($venue->id, $event->fresh()->ticketingRole()->id);
        $this->assertFalse(
            $event->fresh()->canSellTickets(),
            'the creator schedule decides, through every subdomain that lists the event'
        );
    }

    public function test_a_pro_creator_sells_through_a_free_curator_subdomain(): void
    {
        $venue = $this->createRole($this->createOwner(), 'venue');
        $curator = $this->createFreeRole($this->createOwner(), 'curator');

        $event = $this->createEvent($venue, ['tickets_enabled' => true, 'payment_method' => 'stripe']);
        $event->roles()->attach($curator->id, ['is_accepted' => true]);
        $this->createTicket($event, ['price' => 20, 'quantity' => 500]);

        $this->assertSame($venue->id, $event->fresh()->ticketingRole()->id);
        $this->assertTrue($event->fresh()->canSellTickets(), 'the free curator does not gate the Pro creator');
    }

    public function test_the_demo_schedule_sells_although_it_is_on_the_free_plan(): void
    {
        // DemoService never sets a plan, so is_demo_role() is the only thing keeping a paywall off
        // the public demo - which is seeded with paid sales precisely to show ticketing working.
        // Checked live rather than stamped, because reseeding the demo creates new events.
        $owner = $this->createOwner();
        $role = $this->createFreeRole($owner);
        $role->forceFill(['subdomain' => \App\Services\DemoService::DEMO_ROLE_SUBDOMAIN])->save();

        $event = $this->createEvent($role->fresh(), [
            'tickets_enabled' => true,
            'payment_method' => 'stripe',
        ]);
        $this->createTicket($event, ['price' => 20, 'quantity' => 100]);

        $this->assertTrue(is_demo_role($role->fresh()), 'sanity check: the fixture is the demo');
        $this->assertTrue($event->fresh()->canSellTickets());
    }

    public function test_an_event_with_no_owning_schedule_still_sells_on_selfhost(): void
    {
        // canSellPaidTickets() resolves ticketingRole() and fails closed when there is none. On
        // selfhost there is no plan to sell, so the hosted check has to come first or an orphaned
        // event is refused on someone's own server.
        config(['app.hosted' => false]);

        $role = $this->createRole($this->createOwner());
        $event = $this->createEvent($role, ['tickets_enabled' => true, 'payment_method' => 'stripe']);
        $this->createTicket($event, ['price' => 20, 'quantity' => 100]);
        $event->forceFill(['creator_role_id' => null])->saveQuietly();
        $event->roles()->detach();

        $this->assertNull($event->fresh()->ticketingRole(), 'sanity check: nothing owns this event');
        $this->assertTrue($event->fresh()->canSellTickets());
    }

    public function test_an_addon_follows_the_creator_schedule_not_any_attached_pro_one(): void
    {
        // The hole this closes: Event::isPro() ORs over every attached role, so a free creator with
        // a Pro venue attached could sell an arbitrarily priced add-on beside a $0 admission row.
        $venue = $this->createFreeRole($this->createOwner(), 'venue');
        $curator = $this->createRole($this->createOwner(), 'curator');

        $event = $this->createEvent($venue, ['tickets_enabled' => true, 'payment_method' => 'stripe']);
        $event->roles()->attach($curator->id, ['is_accepted' => true]);
        $this->createTicket($event, ['price' => 0, 'quantity' => 100]);
        $addon = $this->createTicket($event, ['price' => 15, 'quantity' => 100, 'is_addon' => true]);

        $this->assertTrue($curator->fresh()->isPro(), 'sanity check: an attached schedule is Pro');
        $this->assertTrue($event->fresh()->isPro(), 'and Event::isPro() therefore says true');
        $this->assertFalse($event->fresh()->canSellAddons(), 'but the creator schedule decides');
        $this->assertFalse($addon->fresh()->isSellable());
    }

    /**
     * The same add-on hole, end to end through the guest checkout rather than at the model.
     *
     * $event->tickets is scoped where('is_addon', false), so the per-row loop in
     * TicketController::checkout() cannot see an add-on. A free schedule with one $0 admission row
     * keeps canSellTickets() true, which is what made this reachable.
     */
    public function test_a_free_schedule_cannot_check_out_a_priced_addon(): void
    {
        $role = $this->createFreeRole();
        $event = $this->createEvent($role, ['tickets_enabled' => true]);
        $ticket = $this->createTicket($event, ['price' => 0, 'quantity' => 50]);
        $addon = $this->createTicket($event, ['type' => 'Parking', 'price' => 15, 'quantity' => 50, 'is_addon' => true]);

        $this->post(route('event.checkout', ['subdomain' => $role->subdomain]), [
            'event_id' => \App\Utils\UrlUtils::encodeId($event->id),
            'event_date' => \Carbon\Carbon::parse($event->starts_at)->format('Y-m-d'),
            'name' => 'Addon Buyer',
            'email' => 'addon@gmail.com',
            'tickets' => [\App\Utils\UrlUtils::encodeId($ticket->id) => 1],
            'addons' => [\App\Utils\UrlUtils::encodeId($addon->id) => 2],
        ]);

        $this->assertDatabaseMissing('sales', ['email' => 'addon@gmail.com']);
    }

    public function test_paid_plans_selfhost_and_demo_may_all_sell(): void
    {
        $pro = $this->createRole($this->createOwner());
        $proEvent = $this->createEvent($pro, ['tickets_enabled' => true, 'payment_method' => 'stripe']);
        $this->createTicket($proEvent, ['price' => 20, 'quantity' => 100]);
        $this->assertTrue($proEvent->fresh()->canSellTickets(), 'Pro sells');

        $free = $this->createFreeRole();
        $freeEvent = $this->createEvent($free, ['tickets_enabled' => true, 'payment_method' => 'stripe']);
        $this->createTicket($freeEvent, ['price' => 20, 'quantity' => 100]);
        $this->assertFalse($freeEvent->fresh()->canSellTickets(), 'free does not');

        // Selfhost: isPro() short-circuits before any of the gate's other arms.
        config(['app.hosted' => false]);
        $this->assertTrue($freeEvent->fresh()->canSellTickets(), 'selfhost is unlimited');
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

    /**
     * The appointment mirror of the paid-ticket rule: the price is the line, not the count.
     */
    public function test_a_priced_appointment_type_needs_pro(): void
    {
        $role = $this->createFreeRole();

        $free = $this->createAppointmentType($role, ['name' => 'Intro Call']);
        $paid = $this->createAppointmentType($role, ['name' => 'Consultation', 'price' => 50, 'currency_code' => 'USD']);

        // canTakePayment() is a PLAN question, so it is false here for both. What matters is that
        // isBookable() never asks it for a free type - the price is what pulls the gate in.
        $this->assertTrue($free->fresh()->isBookable(), 'a free type books regardless of plan');
        $this->assertFalse($paid->fresh()->canTakePayment());
        $this->assertFalse($paid->fresh()->isBookable(), 'and it drops out of the guest surface');

        $bookable = $role->fresh()->bookableAppointmentTypes();
        $this->assertCount(1, $bookable);
        $this->assertSame($free->id, $bookable->first()->id, 'the free type is unaffected');

        // Nothing is deleted: upgrading lights the priced one up without it being re-saved.
        $role->plan_type = 'pro';
        $role->plan_expires = now()->addYear()->format('Y-m-d');
        $role->save();
        $this->assertCount(2, $role->fresh()->bookableAppointmentTypes(), 'upgrading restores it');
    }

    public function test_a_grandfathered_priced_type_keeps_booking_on_free(): void
    {
        $role = $this->createFreeRole();

        $stamped = $this->createAppointmentType($role, ['name' => 'Consultation', 'price' => 50, 'currency_code' => 'USD']);
        $stamped->forceFill(['paid_grandfathered_at' => now()])->save();

        $fresh = $this->createAppointmentType($role, ['name' => 'Coaching', 'price' => 80, 'currency_code' => 'USD']);

        $this->assertTrue($stamped->fresh()->canTakePayment(), 'the stamp survives the plan');
        $this->assertFalse($fresh->fresh()->canTakePayment(), 'a NEW priced type still needs Pro');

        $bookable = $role->fresh()->bookableAppointmentTypes();
        $this->assertCount(1, $bookable);
        $this->assertSame($stamped->id, $bookable->first()->id);
    }

    public function test_selfhost_never_gates_a_priced_appointment_type(): void
    {
        $role = $this->createFreeRole();
        $paid = $this->createAppointmentType($role, ['name' => 'Consultation', 'price' => 50, 'currency_code' => 'USD']);

        config(['app.hosted' => false]);
        $this->assertTrue($paid->fresh()->canTakePayment(), 'selfhost short-circuits the gate');
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

        // And so is looking an attendee up by name or seat, which is part of the same dashboard.
        // It shipped guarded only by canViewEventData, so it answered on a free schedule.
        $this->actingAs($owner)->getJson(route('checkin.search', [
            'event_id' => \App\Utils\UrlUtils::encodeId($event->id),
        ]).'?q=smith')->assertStatus(403);
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

        // A sold-out $0 event is the case that actually exercises the plan gate. The event above
        // is refused for a reason that has nothing to do with the plan - a free schedule cannot
        // sell its $10 row at all - so on its own it would pass with the gate removed. Here
        // canSellTickets() is TRUE (a surviving $0 row keeps the event selling), and only
        // canOfferWaitlist() stands between a free schedule and a Pro feature.
        $freeTier = $this->createEvent($role, [
            'starts_at' => now()->addDays(7)->setTime(12, 0)->format('Y-m-d H:i:s'),
            'tickets_enabled' => true,
        ]);
        $freeRow = $this->createTicket($freeTier, ['price' => 0, 'quantity' => 1]);
        $this->createSale($freeTier, $role, ['status' => 'paid', 'event_date' => $date], $freeRow, 1);

        $this->assertTrue($freeTier->fresh()->canSellTickets($date), 'the $0 row keeps it selling');

        $this->post(route('waitlist.join', ['subdomain' => $role->subdomain]), [
            'event_id' => \App\Utils\UrlUtils::encodeId($freeTier->id),
            'event_date' => $date,
            'name' => 'Hopeful',
            'email' => 'freetier-waitlist@gmail.com',
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

    /**
     * The ticket embed follows the event's own gate and says so, rather than 404ing.
     *
     * It used to abort(404), which made show-guest-ticket-embed's own "not available" state
     * unreachable and left PaymentGatewayDriver::handleCancel() bouncing an abandoned buyer onto a
     * whole schedule grid inside the ticket iframe. A 404 in an iframe on someone else's website is
     * a worse answer than a sentence and a link out.
     */
    public function test_the_ticket_embed_explains_itself_when_the_event_cannot_sell(): void
    {
        $role = $this->createFreeRole();
        $event = $this->createEvent($role, [
            'starts_at' => now()->addWeek()->format('Y-m-d H:i:s'),
            'tickets_enabled' => true,
            'payment_method' => 'stripe',
        ]);
        $this->createTicket($event, ['price' => 20, 'quantity' => 50]);

        $base = route('event.view_guest', [
            'subdomain' => $role->subdomain,
            'slug' => $event->slug,
        ]);

        $this->get($base.'?embed=1&tickets=true')
            ->assertOk()
            ->assertSee(__('messages.tickets_not_available_embed'));

        $this->get($base.'?embed=1&rsvp=true')->assertOk();
    }

    /**
     * The ticket embed is a Pro feature in its own right, so a grandfathered event does NOT get it.
     *
     * The stamp restores paid ticket selling on the event's own page; it is not a licence for the
     * embeddable widget, which has been Pro throughout. Keying the embed on canSellTickets() would
     * also have handed it to any free schedule with a surviving $0 row.
     */
    public function test_the_ticket_embed_stays_pro_even_for_a_grandfathered_event(): void
    {
        $role = $this->createFreeRole();
        $event = $this->createEvent($role, [
            'starts_at' => now()->addWeek()->format('Y-m-d H:i:s'),
            'tickets_enabled' => true,
            'payment_method' => 'stripe',
        ]);
        $this->createTicket($event, ['price' => 20, 'quantity' => 50]);
        $event->forceFill(['tickets_grandfathered_at' => now()])->saveQuietly();

        $base = route('event.view_guest', [
            'subdomain' => $role->subdomain,
            'slug' => $event->slug,
        ]);

        $this->assertTrue($event->fresh()->canSellTickets(), 'it does still sell on its own page');

        $this->get($base.'?embed=1&tickets=true')
            ->assertOk()
            ->assertSee(__('messages.tickets_not_available_embed'));
    }

    public function test_the_ticket_embed_renders_for_a_pro_schedule(): void
    {
        $role = $this->createRole($this->createOwner());
        $event = $this->createEvent($role, [
            'starts_at' => now()->addWeek()->format('Y-m-d H:i:s'),
            'tickets_enabled' => true,
            'payment_method' => 'stripe',
        ]);
        $this->createTicket($event, ['price' => 20, 'quantity' => 50]);

        $this->get(route('event.view_guest', [
            'subdomain' => $role->subdomain,
            'slug' => $event->slug,
        ]).'?embed=1&tickets=true')
            ->assertOk()
            ->assertDontSee(__('messages.tickets_not_available_embed'));
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
     * "Clamped, never deleted" has to hold for individual tickets too.
     *
     * The scrub used to clear the flag whenever it was true, which wiped it for a lapsed Pro
     * schedule on its next save of any event - and it did so however the form was rendered, because
     * saveEvent()'s boolean loop leaves an unposted flag at its stored value rather than at false.
     * An event that had already sold per-guest tickets would silently change how its checkout
     * behaves. Mirrors the pass scrub, which only ever refuses to turn one ON.
     */
    public function test_a_lapsed_pro_schedule_keeps_individual_tickets_it_already_had(): void
    {
        $owner = $this->createOwner();
        $role = $this->createFreeRole($owner);
        $event = $this->createEvent($role, ['tickets_enabled' => true, 'individual_tickets' => true]);
        $this->createTicket($event, ['price' => 20, 'type' => 'General']);

        $this->assertTrue((bool) $event->fresh()->individual_tickets, 'sanity check: stored on');

        // The form does not post the flag at all now that the control is locked below Pro.
        $request = \Illuminate\Http\Request::create('/', 'POST', [
            'name' => 'Lapsed Pro Event',
            'starts_at' => now()->addDays(3)->format('Y-m-d H:i:s'),
            'duration' => 2,
            'schedule_type' => 'one_time',
            'tickets_enabled' => 1,
        ]);
        $request->setUserResolver(fn () => $owner);
        $this->app->instance('request', $request);

        app(\App\Repos\EventRepo::class)->saveEvent($role, $request, $event->fresh(), false);

        $this->assertTrue(
            (bool) $event->fresh()->individual_tickets,
            'a setting the schedule already had is kept when it drops to free, not destroyed'
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

    /**
     * Clone is the second creation path, and it is now checked against the allowance too - its
     * comment had claimed that for months without it being true.
     */
    public function test_cloning_is_refused_once_the_free_allowance_is_used(): void
    {
        $role = $this->createFreeRole();
        $type = $this->createAppointmentType($role, ['name' => 'Consultation']);

        $this->actingAs($role->user)->post(route('appointments.duplicate', [
            'subdomain' => $role->subdomain,
            'hash' => $type->hashedId(),
        ]))->assertRedirect(route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'appointments']));

        $this->assertSame(1, \App\Models\AppointmentType::where('role_id', $role->id)->count(), 'no second type');
    }

    /**
     * Clone is also the one path that could mint a grandfather stamp, because replicate() copies
     * every attribute and does not consult $fillable. Tested on Pro, since the allowance refuses
     * the clone outright on Free.
     */
    public function test_cloning_a_grandfathered_type_does_not_carry_the_stamp(): void
    {
        $role = $this->createRole($this->createOwner(), 'talent');
        $this->assertTrue($role->isPro(), 'sanity check: the fixture is Pro, so the cap does not apply');

        $stamped = $this->createAppointmentType($role, ['name' => 'Consultation', 'price' => 50, 'currency_code' => 'USD']);
        $stamped->forceFill(['paid_grandfathered_at' => now()])->save();

        $this->actingAs($role->user)->post(route('appointments.duplicate', [
            'subdomain' => $role->subdomain,
            'hash' => $stamped->hashedId(),
        ]));

        $copy = \App\Models\AppointmentType::where('role_id', $role->id)
            ->where('id', '!=', $stamped->id)
            ->latest('id')
            ->firstOrFail();

        $this->assertNull($copy->paid_grandfathered_at, 'the copy must not inherit the stamp');

        // Drop the copy's schedule to free and it may not charge: the stamp was the only thing that
        // could have carried the right across, and it did not.
        $role->forceFill(['plan_type' => 'free', 'plan_expires' => now()->subDay()->format('Y-m-d'), 'trial_ends_at' => null])->save();
        config(['app.hosted' => true]);
        $this->assertFalse($copy->fresh()->canTakePayment(), 'so the copy may not charge');
        $this->assertTrue($stamped->fresh()->canTakePayment(), 'and the original keeps its own');
    }
}
