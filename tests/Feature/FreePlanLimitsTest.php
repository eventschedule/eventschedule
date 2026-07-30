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
