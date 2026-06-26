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
}
