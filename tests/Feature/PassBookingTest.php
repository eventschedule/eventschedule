<?php

namespace Tests\Feature;

use App\Models\Ticket;
use App\Services\PassBookingService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

class PassBookingTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private function bookingService(): PassBookingService
    {
        return app(PassBookingService::class);
    }

    /** Fresh remaining-seat count the guest would see for the sole regular ticket. */
    private function regularAvailable(int $ticketId, string $date): int
    {
        return Ticket::with('event')->find($ticketId)->toData($date)['quantity'];
    }

    private function reserved($event, string $date): int
    {
        return $event->fresh()->passReservedSeats($date);
    }

    public function test_single_ticket_pass_booking_shares_capacity_and_prevents_overbooking(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        // Customer's case: one recurring/standalone event, one regular ticket + a season pass.
        $event = $this->createEvent($role, ['creator_role_id' => $role->id, 'tickets_enabled' => true]);
        $date = Carbon::parse($event->starts_at)->format('Y-m-d');

        $regular = $this->createTicket($event, ['type' => 'General', 'quantity' => 2, 'price' => 10]);
        $pass = $this->createTicket($event, [
            'type' => 'Season Pass', 'quantity' => 100, 'price' => 50,
            'is_pass' => true, 'pass_usage_type' => 'unlimited', 'pass_scope' => 'this_event',
            'pass_allow_booking' => true,
        ]);

        $holderA = $this->createSale($event, $role, ['email' => 'a@gmail.com'], $pass);
        $holderB = $this->createSale($event, $role, ['email' => 'b@gmail.com'], $pass);

        // Capacity 2, nothing booked: both seats visible to regular buyers.
        $this->assertSame(0, $this->reserved($event, $date));
        $this->assertSame(2, $this->regularAvailable($regular->id, $date));

        // Holder A books a seat in advance -> one fewer for regular buyers.
        $r1 = $this->bookingService()->book($holderA->fresh(), $event->id, $date);
        $this->assertTrue($r1->ok, 'first booking should succeed');
        $this->assertSame(1, $this->reserved($event, $date));
        $this->assertSame(1, $this->regularAvailable($regular->id, $date));

        // A regular buyer takes the last seat.
        $this->createSale($event, $role, ['email' => 'c@gmail.com'], $regular, 1);
        $this->assertSame(0, $this->regularAvailable($regular->id, $date));

        // The house is full: holder B can no longer book.
        $r2 = $this->bookingService()->book($holderB->fresh(), $event->id, $date);
        $this->assertFalse($r2->ok);
        $this->assertSame('sold_out', $r2->status);

        // Refunding holder A frees the seat automatically (reservations are computed
        // from paid sales only - no decrement needed).
        $holderA->fresh()->update(['status' => 'refunded']);
        $this->assertSame(0, $this->reserved($event, $date));

        // ...and now holder B can book the freed seat.
        $r3 = $this->bookingService()->book($holderB->fresh(), $event->id, $date);
        $this->assertTrue($r3->ok, 'booking should succeed once a seat frees up');
        $this->assertSame(1, $this->reserved($event, $date));
    }

    public function test_booking_can_be_cancelled_and_seat_released(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->createEvent($role, ['creator_role_id' => $role->id, 'tickets_enabled' => true]);
        $date = Carbon::parse($event->starts_at)->format('Y-m-d');

        $this->createTicket($event, ['type' => 'General', 'quantity' => 5, 'price' => 10]);
        $pass = $this->createTicket($event, [
            'type' => 'Season Pass', 'quantity' => 100, 'price' => 50,
            'is_pass' => true, 'pass_usage_type' => 'unlimited', 'pass_scope' => 'this_event',
            'pass_allow_booking' => true,
        ]);
        $holder = $this->createSale($event, $role, ['email' => 'h@gmail.com'], $pass);

        $first = $this->bookingService()->book($holder->fresh(), $event->id, $date);
        $this->assertTrue($first->ok, 'booking failed: '.$first->status);
        $this->assertSame(1, $this->reserved($event, $date));

        // Double-booking the same date is rejected.
        $dup = $this->bookingService()->book($holder->fresh(), $event->id, $date);
        $this->assertFalse($dup->ok);
        $this->assertSame('already_booked', $dup->status);

        // Cancelling releases the seat.
        $this->assertTrue($this->bookingService()->cancel($holder->fresh(), $event->id, $date)->ok);
        $this->assertSame(0, $this->reserved($event, $date));
    }

    public function test_visit_limit_caps_bookings(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        // Daily recurring so there are multiple bookable occurrences.
        $event = $this->createEvent($role, [
            'creator_role_id' => $role->id,
            'tickets_enabled' => true,
            'starts_at' => Carbon::now()->addDay()->setTime(12, 0)->format('Y-m-d H:i:s'),
            'days_of_week' => '1111111',
        ]);
        $this->createTicket($event, ['type' => 'General', 'quantity' => 50, 'price' => 10]);
        $pass = $this->createTicket($event, [
            'type' => '1-Visit Pass', 'quantity' => 100, 'price' => 20,
            'is_pass' => true, 'pass_usage_type' => 'total', 'pass_max_uses' => 1,
            'pass_valid_days' => 90, 'pass_scope' => 'this_event', 'pass_allow_booking' => true,
        ]);
        $holder = $this->createSale($event, $role, ['email' => 'v@gmail.com'], $pass);

        $d1 = Carbon::now()->addDays(2)->format('Y-m-d');
        $d2 = Carbon::now()->addDays(3)->format('Y-m-d');

        $first = $this->bookingService()->book($holder->fresh(), $event->id, $d1);
        $this->assertTrue($first->ok, 'booking failed: '.$first->status);

        // Second booking exceeds the 1-visit cap.
        $second = $this->bookingService()->book($holder->fresh(), $event->id, $d2);
        $this->assertFalse($second->ok);
        $this->assertSame('limit_reached', $second->status);
    }

    public function test_drop_in_pass_is_not_bookable(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->createEvent($role, ['creator_role_id' => $role->id, 'tickets_enabled' => true]);
        $date = Carbon::parse($event->starts_at)->format('Y-m-d');

        $this->createTicket($event, ['type' => 'General', 'quantity' => 5, 'price' => 10]);
        $pass = $this->createTicket($event, [
            'type' => 'Drop-in Pass', 'quantity' => 100, 'price' => 50,
            'is_pass' => true, 'pass_usage_type' => 'unlimited', 'pass_scope' => 'this_event',
            'pass_allow_booking' => false,
        ]);
        $holder = $this->createSale($event, $role, ['email' => 'd@gmail.com'], $pass);

        $this->assertFalse($this->bookingService()->isBookable($holder->fresh()));
        $result = $this->bookingService()->book($holder->fresh(), $event->id, $date);
        $this->assertFalse($result->ok);
        $this->assertSame('not_bookable', $result->status);
        $this->assertSame(0, $this->reserved($event, $date));
    }

    public function test_multi_ticket_individual_event_does_not_oversell(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->createEvent($role, ['creator_role_id' => $role->id, 'tickets_enabled' => true]);
        $date = Carbon::parse($event->starts_at)->format('Y-m-d');

        // Individual mode (default), two seat tickets => shared house = 20 + 10 = 30.
        $ga = $this->createTicket($event, ['type' => 'GA', 'quantity' => 20, 'price' => 10]);
        $vip = $this->createTicket($event, ['type' => 'VIP', 'quantity' => 10, 'price' => 30]);
        $pass = $this->createTicket($event, [
            'type' => 'Season Pass', 'quantity' => 100, 'price' => 50,
            'is_pass' => true, 'pass_usage_type' => 'unlimited', 'pass_scope' => 'this_event',
            'pass_allow_booking' => true,
        ]);

        $this->assertSame(30, $event->fresh()->occurrenceSeatsRemaining($date));

        // 25 holders book in advance.
        for ($i = 0; $i < 25; $i++) {
            $holder = $this->createSale($event, $role, ['email' => "h{$i}@gmail.com"], $pass);
            $r = $this->bookingService()->book($holder->fresh(), $event->id, $date);
            $this->assertTrue($r->ok, "booking {$i} failed: {$r->status}");
        }

        // Only 5 seats remain for regular buyers - not the full 30 (the oversell bug).
        $this->assertSame(5, $event->fresh()->occurrenceSeatsRemaining($date));
        $this->assertLessThanOrEqual(5, $this->regularAvailable($ga->id, $date));
        $this->assertLessThanOrEqual(5, $this->regularAvailable($vip->id, $date));

        // Fill the last 5 with regular GA sales => the event now reads sold out
        // (previously it never did, because passes were excluded from the check).
        $this->createSale($event, $role, ['email' => 'buyer@gmail.com'], $ga, 5);
        $this->assertSame(0, $event->fresh()->occurrenceSeatsRemaining($date));
        $this->assertTrue($event->fresh()->allTicketsSoldOut($date));

        // A further advance booking is rejected.
        $extra = $this->createSale($event, $role, ['email' => 'extra@gmail.com'], $pass);
        $this->assertSame('sold_out', $this->bookingService()->book($extra->fresh(), $event->id, $date)->status);
    }

    public function test_one_time_date_only_event_is_bookable_without_error(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        // A one-time event with a DATE-ONLY starts_at (10 chars) - previously threw a 500.
        $dateOnly = Carbon::now()->addDays(10)->format('Y-m-d');
        $event = $this->createEvent($role, [
            'creator_role_id' => $role->id, 'tickets_enabled' => true, 'starts_at' => $dateOnly,
        ]);
        $this->createTicket($event, ['type' => 'GA', 'quantity' => 10, 'price' => 10]);
        $pass = $this->createTicket($event, [
            'type' => 'Pass', 'quantity' => 100, 'price' => 50,
            'is_pass' => true, 'pass_usage_type' => 'unlimited', 'pass_scope' => 'this_event',
            'pass_allow_booking' => true,
        ]);
        $holder = $this->createSale($event, $role, ['email' => 'h@gmail.com'], $pass);

        // No exception, and the canonical occurrence date is offered.
        $occ = $this->bookingService()->bookableOccurrences($holder->fresh(), Carbon::now());
        $this->assertNotEmpty($occ);
        $this->assertSame($event->saleEventDateFromStartsAt(), $occ[0]['date']);

        // Booking round-trips into a reservation under the same date the pool keys on.
        $r = $this->bookingService()->book($holder->fresh(), $event->id, $occ[0]['date']);
        $this->assertTrue($r->ok, 'date-only booking failed: '.$r->status);
        $this->assertSame(1, $event->fresh()->passReservedSeats($occ[0]['date']));
    }

    public function test_expiry_boundary_excludes_unredeemable_date(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        // Evening event; pass expires that morning => the date is not redeemable, so it
        // must not be offered or bookable (book/redeem agree on the instant boundary).
        $eveningUtc = Carbon::now()->addDays(5)->setTime(23, 0)->format('Y-m-d H:i:s');
        $event = $this->createEvent($role, [
            'creator_role_id' => $role->id, 'tickets_enabled' => true, 'starts_at' => $eveningUtc,
        ]);
        $date = Carbon::parse($eveningUtc)->format('Y-m-d');
        $this->createTicket($event, ['type' => 'GA', 'quantity' => 10, 'price' => 10]);
        $pass = $this->createTicket($event, [
            'type' => 'Pass', 'quantity' => 100, 'price' => 50,
            'is_pass' => true, 'pass_usage_type' => 'unlimited', 'pass_scope' => 'this_event',
            'pass_allow_booking' => true,
        ]);
        $holder = $this->createSale($event, $role, ['email' => 'h@gmail.com'], $pass);
        // Force expiry to that morning, before the 23:00 occurrence start.
        $st = $holder->saleTickets->first(fn ($s) => $s->ticket && $s->ticket->is_pass);
        $st->update(['pass_expires_at' => Carbon::parse($date.' 06:00:00')]);

        $this->assertEmpty($this->bookingService()->bookableOccurrences($holder->fresh(), Carbon::now()));
        $this->assertSame('expired', $this->bookingService()->book($holder->fresh(), $event->id, $date)->status);
    }

    public function test_non_bookable_pass_redemptions_count_against_the_house(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->createEvent($role, ['creator_role_id' => $role->id, 'tickets_enabled' => true]);
        $date = Carbon::parse($event->starts_at)->format('Y-m-d');

        $ga = $this->createTicket($event, ['type' => 'GA', 'quantity' => 5, 'price' => 10]);
        // Drop-in / membership pass - NOT booking-enabled (the default for memberships).
        $pass = $this->createTicket($event, [
            'type' => 'Membership', 'quantity' => 100, 'price' => 50,
            'is_pass' => true, 'pass_usage_type' => 'unlimited', 'pass_scope' => 'this_event',
            'pass_allow_booking' => false,
        ]);

        // Three members scanned in (redemption usages, written by redeem() for any pass).
        for ($i = 0; $i < 3; $i++) {
            $sale = $this->createSale($event, $role, ['email' => "m{$i}@gmail.com"], $pass);
            $st = $sale->saleTickets->first(fn ($s) => $s->ticket->is_pass);
            $st->update(['pass_usages' => [['event_id' => $event->id, 'date' => $date, 'at' => 1, 'kind' => 'redemption']]]);
        }

        // Those occupied seats must reduce the house (the short-circuit regression dropped them).
        $this->assertSame(3, $event->fresh()->passReservedSeats($date));
        $this->assertSame(2, $event->fresh()->occurrenceSeatsRemaining($date));
        $this->assertLessThanOrEqual(2, $this->regularAvailable($ga->id, $date));

        // Filling the last 2 with regular sales sells the event out.
        $this->createSale($event, $role, ['email' => 'b@gmail.com'], $ga, 2);
        $this->assertTrue($event->fresh()->allTicketsSoldOut($date));
    }

    public function test_scanning_a_booked_date_upgrades_it_without_consuming_an_extra_use(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-07-15 18:00:00'));

        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['timezone' => 'UTC']);
        $event = $this->createEvent($role, [
            'creator_role_id' => $role->id, 'tickets_enabled' => true,
            'starts_at' => '2026-07-15 18:00:00', 'duration' => 4,
        ]);
        $date = '2026-07-15';
        $this->createTicket($event, ['type' => 'GA', 'quantity' => 10, 'price' => 10]);
        $pass = $this->createTicket($event, [
            'type' => 'Visit Pass', 'quantity' => 100, 'price' => 20,
            'is_pass' => true, 'pass_usage_type' => 'total', 'pass_max_uses' => 1,
            'pass_valid_days' => 90, 'pass_scope' => 'this_event', 'pass_allow_booking' => true,
        ]);
        $holder = $this->createSale($event, $role, ['email' => 'h@gmail.com'], $pass);

        // Book today's occurrence (consumes the single allowed use up front).
        $this->assertTrue($this->bookingService()->book($holder->fresh(), $event->id, $date)->ok);
        $st = $holder->fresh()->saleTickets->first(fn ($s) => $s->ticket->is_pass);
        $this->assertSame('booking', $st->pass_usages[0]['kind']);
        $this->assertSame(1, $event->fresh()->passReservedSeats($date));

        // Scanning at the door upgrades the booking to a redemption - no new entry, no extra use.
        $result = app(\App\Services\PassRedemptionService::class)->redeem($holder->fresh(), $event->fresh(), Carbon::now());
        $this->assertSame('valid', $result->pass_status);
        $st = $holder->fresh()->saleTickets->first(fn ($s) => $s->ticket->is_pass);
        $this->assertCount(1, $st->pass_usages);
        $this->assertSame('redemption', $st->pass_usages[0]['kind']);
        $this->assertSame(1, $event->fresh()->passReservedSeats($date));

        Carbon::setTestNow();
    }
}
