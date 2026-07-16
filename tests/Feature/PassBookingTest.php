<?php

namespace Tests\Feature;

use App\Jobs\NotifyWaitlist;
use App\Mail\PassBookingConfirmation;
use App\Models\Ticket;
use App\Services\PassBookingService;
use App\Utils\UrlUtils;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
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

    public function test_guest_event_page_renders_buy_cta_with_pass_reservations(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner); // enterprise by default => isPro() true => canSellTickets passes
        $event = $this->createEvent($role, [
            'creator_role_id' => $role->id, 'tickets_enabled' => true, 'name' => 'Capacity Concert',
        ]);
        $date = Carbon::parse($event->starts_at)->format('Y-m-d');

        // Two seat tickets (individual mode) + a booking-enabled pass: shared house = 5.
        $this->createTicket($event, ['type' => 'GA', 'quantity' => 3, 'price' => 10]);
        $this->createTicket($event, ['type' => 'VIP', 'quantity' => 2, 'price' => 30]);
        $pass = $this->createTicket($event, [
            'type' => 'Season Pass', 'quantity' => 100, 'price' => 50,
            'is_pass' => true, 'pass_usage_type' => 'unlimited', 'pass_scope' => 'this_event',
            'pass_allow_booking' => true,
        ]);

        // One advance booking leaves 4 seats: the guest page must still render the CTA (no 500,
        // not spuriously sold out). This drives toData/occurrenceSeatsRemaining/setRelation through
        // a real render - the exact path the Dusk "Buy Tickets" check exercises.
        $holder = $this->createSale($event, $role, ['email' => 'h@gmail.com'], $pass);
        $this->bookingService()->book($holder->fresh(), $event->id, $date);

        $url = $this->guestEventUrl($role, $event);
        $this->get($url)
            ->assertOk()
            ->assertSee('Capacity Concert')
            ->assertSee($role->customLabel('buy_tickets'));

        // Reserve the remaining 4 seats => sold out; the page must still render (no 500) and the
        // buy CTA flips off - proving the gate is driven correctly, not spuriously.
        for ($i = 0; $i < 4; $i++) {
            $f = $this->createSale($event, $role, ['email' => "f{$i}@gmail.com"], $pass);
            $this->bookingService()->book($f->fresh(), $event->id, $date);
        }
        $this->assertSame(0, $event->fresh()->occurrenceSeatsRemaining($date));
        $this->assertTrue($event->fresh()->allTicketsSoldOut($date));

        $this->get($url)->assertOk()->assertDontSee($role->customLabel('buy_tickets'));
    }

    /**
     * One event at 2026-08-10 18:00 UTC with a bookable visit pass configured
     * with the given cancellation policy, plus a booking made well in advance.
     * Returns [$event, $pass, $holder, $date].
     */
    private function setUpCancellableBooking(?int $cutoffHours, string $policy, array $passAttrs = []): array
    {
        Carbon::setTestNow(Carbon::parse('2026-08-01 12:00:00'));

        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['timezone' => 'UTC']);
        $event = $this->createEvent($role, [
            'creator_role_id' => $role->id, 'tickets_enabled' => true,
            'starts_at' => '2026-08-10 18:00:00', 'duration' => 2,
        ]);
        $date = '2026-08-10';

        $this->createTicket($event, ['type' => 'General', 'quantity' => 5, 'price' => 10]);
        $pass = $this->createTicket($event, array_merge([
            'type' => 'Visit Pass', 'quantity' => 100, 'price' => 50,
            'is_pass' => true, 'pass_usage_type' => 'unlimited', 'pass_scope' => 'this_event',
            'pass_allow_booking' => true,
            'pass_cancel_cutoff_hours' => $cutoffHours,
            'pass_late_cancel_policy' => $policy,
        ], $passAttrs));
        $holder = $this->createSale($event, $role, ['email' => 'h@gmail.com'], $pass);

        $booked = $this->bookingService()->book($holder->fresh(), $event->id, $date);
        $this->assertTrue($booked->ok, 'setup booking failed: '.$booked->status);
        $this->assertSame(1, $this->reserved($event, $date));

        return [$event, $pass, $holder, $date];
    }

    private function passUsages($holder): array
    {
        return $holder->fresh()->saleTickets->first(fn ($s) => $s->ticket->is_pass)->pass_usages ?? [];
    }

    public function test_cancel_with_no_cutoff_still_credits_after_the_event(): void
    {
        [$event, $pass, $holder, $date] = $this->setUpCancellableBooking(null, 'forfeit');

        // Two days after the event ended: the legacy behavior is preserved -
        // the visit is credited back and the entry removed.
        Carbon::setTestNow(Carbon::parse('2026-08-12 12:00:00'));
        $result = $this->bookingService()->cancel($holder->fresh(), $event->id, $date);

        $this->assertTrue($result->ok);
        $this->assertSame('cancelled', $result->status);
        $this->assertCount(0, $this->passUsages($holder));
        $this->assertSame(0, $this->reserved($event, $date));

        Carbon::setTestNow();
    }

    public function test_cancel_before_cutoff_credits_and_notifies_waitlist(): void
    {
        [$event, $pass, $holder, $date] = $this->setUpCancellableBooking(24, 'block');

        // 9 days before the event, well before the 24h deadline. Go through the
        // HTTP route so the waitlist dispatch is exercised too.
        Queue::fake();
        $response = $this->post(route('pass.cancel_booking', [
            'event_id' => UrlUtils::encodeId($event->id),
            'secret' => $holder->secret,
        ]), [
            'book_event_id' => UrlUtils::encodeId($event->id),
            'date' => $date,
        ]);

        $response->assertRedirect()->assertSessionHas('message', __('messages.pass_booking_cancelled'));
        $this->assertCount(0, $this->passUsages($holder));
        $this->assertSame(0, $this->reserved($event, $date));
        Queue::assertPushed(NotifyWaitlist::class);

        Carbon::setTestNow();
    }

    public function test_cancel_after_cutoff_is_blocked_when_policy_is_block(): void
    {
        [$event, $pass, $holder, $date] = $this->setUpCancellableBooking(24, 'block');

        // 12 hours before the event: past the 24h deadline.
        Carbon::setTestNow(Carbon::parse('2026-08-10 06:00:00'));
        $result = $this->bookingService()->cancel($holder->fresh(), $event->id, $date);

        $this->assertFalse($result->ok);
        $this->assertSame('too_late', $result->status);
        // The booking stands: entry intact, seat still reserved.
        $usages = $this->passUsages($holder);
        $this->assertCount(1, $usages);
        $this->assertSame('booking', $usages[0]['kind']);
        $this->assertSame(1, $this->reserved($event, $date));

        Carbon::setTestNow();
    }

    public function test_cancel_after_cutoff_forfeits_the_visit_and_frees_the_seat(): void
    {
        [$event, $pass, $holder, $date] = $this->setUpCancellableBooking(24, 'forfeit', [
            'pass_usage_type' => 'total', 'pass_max_uses' => 2,
        ]);

        // 12 hours before the event: past the deadline. HTTP route so the flash
        // message and waitlist dispatch are covered. A first submit WITHOUT the
        // forfeit acknowledgement (a stale pre-deadline page) must bounce with a
        // warning instead of silently forfeiting.
        Carbon::setTestNow(Carbon::parse('2026-08-10 06:00:00'));
        Queue::fake();
        $unacked = $this->post(route('pass.cancel_booking', [
            'event_id' => UrlUtils::encodeId($event->id),
            'secret' => $holder->secret,
        ]), [
            'book_event_id' => UrlUtils::encodeId($event->id),
            'date' => $date,
        ]);
        $unacked->assertRedirect()->assertSessionHas('error', __('messages.pass_forfeit_confirm_notice'));
        $this->assertSame('booking', $this->passUsages($holder)[0]['kind']);
        Queue::assertNotPushed(NotifyWaitlist::class);

        $response = $this->post(route('pass.cancel_booking', [
            'event_id' => UrlUtils::encodeId($event->id),
            'secret' => $holder->secret,
        ]), [
            'book_event_id' => UrlUtils::encodeId($event->id),
            'date' => $date,
            'forfeit_ack' => 1,
        ]);

        $response->assertRedirect()->assertSessionHas('message', __('messages.pass_booking_forfeited'));
        Queue::assertPushed(NotifyWaitlist::class);

        // Visit stays consumed, seat returns to the pool.
        $usages = $this->passUsages($holder);
        $this->assertCount(1, $usages);
        $this->assertSame('forfeited', $usages[0]['kind']);
        $this->assertSame(0, $this->reserved($event, $date));

        // The forfeited date may be rebooked as a NEW visit (2-visit pass: now full).
        $rebook = $this->bookingService()->book($holder->fresh(), $event->id, $date);
        $this->assertTrue($rebook->ok, 'rebooking failed: '.$rebook->status);
        $usages = $this->passUsages($holder);
        $this->assertCount(2, $usages);
        $this->assertSame('forfeited', $usages[0]['kind']);
        $this->assertSame('booking', $usages[1]['kind']);
        $this->assertSame(1, $this->reserved($event, $date));

        Carbon::setTestNow();
    }

    public function test_cutoff_zero_with_forfeit_covers_the_no_show_case(): void
    {
        // Feedback option 1: after the event, an unscanned booking cancels
        // without credit ("no refund, organiser doesn't lose out").
        [$event, $pass, $holder, $date] = $this->setUpCancellableBooking(0, 'forfeit');

        Carbon::setTestNow(Carbon::parse('2026-08-11 12:00:00'));
        $result = $this->bookingService()->cancel($holder->fresh(), $event->id, $date, allowForfeit: true);

        $this->assertTrue($result->ok);
        $this->assertSame('forfeited', $result->status);
        $this->assertCount(1, $this->passUsages($holder));
        $this->assertSame(0, $this->reserved($event, $date));

        Carbon::setTestNow();
    }

    public function test_redeeming_after_a_forfeit_consumes_a_new_visit(): void
    {
        [$event, $pass, $holder, $date] = $this->setUpCancellableBooking(24, 'forfeit', [
            'pass_usage_type' => 'total', 'pass_max_uses' => 2, 'pass_valid_days' => 90,
        ]);

        // Forfeit past the deadline, then the holder turns up at the door anyway.
        Carbon::setTestNow(Carbon::parse('2026-08-10 06:00:00'));
        $this->assertSame('forfeited', $this->bookingService()->cancel($holder->fresh(), $event->id, $date, allowForfeit: true)->status);

        Carbon::setTestNow(Carbon::parse('2026-08-10 18:30:00'));
        $result = app(\App\Services\PassRedemptionService::class)->redeem($holder->fresh(), $event->fresh(), Carbon::now());

        // The forfeited entry never revives: a fresh redemption is appended,
        // spending the pass's second (last) visit.
        $this->assertSame('valid', $result->pass_status);
        $usages = $this->passUsages($holder);
        $this->assertCount(2, $usages);
        $this->assertSame('forfeited', $usages[0]['kind']);
        $this->assertSame('redemption', $usages[1]['kind']);

        Carbon::setTestNow();
    }

    public function test_redeeming_after_a_forfeit_respects_the_visit_limit(): void
    {
        [$event, $pass, $holder, $date] = $this->setUpCancellableBooking(24, 'forfeit', [
            'pass_usage_type' => 'total', 'pass_max_uses' => 1, 'pass_valid_days' => 90,
        ]);

        Carbon::setTestNow(Carbon::parse('2026-08-10 06:00:00'));
        $this->assertSame('forfeited', $this->bookingService()->cancel($holder->fresh(), $event->id, $date, allowForfeit: true)->status);

        // The single visit was spent by the forfeit, so the walk-up is refused.
        Carbon::setTestNow(Carbon::parse('2026-08-10 18:30:00'));
        $result = app(\App\Services\PassRedemptionService::class)->redeem($holder->fresh(), $event->fresh(), Carbon::now());

        $this->assertSame('limit_reached', $result->pass_status);
        $usages = $this->passUsages($holder);
        $this->assertCount(1, $usages);
        $this->assertSame('forfeited', $usages[0]['kind']);

        Carbon::setTestNow();
    }

    public function test_booking_confirmation_email_includes_the_cancellation_deadline(): void
    {
        [$event, $pass, $holder, $date] = $this->setUpCancellableBooking(24, 'forfeit');

        $content = (new PassBookingConfirmation($holder->fresh(), $event, $date, $event->roles->first()))->content();

        // Deadline = 2026-08-10 18:00 UTC minus 24h, rendered in the schedule TZ (UTC)
        // with the schedule's locale (roles default to language_code 'en').
        $this->assertSame('9 August 2026 • 6:00 PM', $content->with['cancelDeadlineLabel']);
        $this->assertSame('forfeit', $content->with['lateCancelPolicy']);
        $this->assertFalse($content->with['cancelDeadlinePassed']);

        $text = view('emails.pass_booking_confirmation_text', array_merge($content->with, [
            'manageUrl' => 'https://example.com/manage',
        ]))->render();
        $this->assertStringContainsString(
            __('messages.pass_cancel_email_deadline_forfeit', ['deadline' => '9 August 2026 • 6:00 PM']),
            $text
        );

        // No cutoff -> no deadline line.
        $pass->forceFill(['pass_cancel_cutoff_hours' => null])->save();
        $bare = (new PassBookingConfirmation($holder->fresh(), $event->fresh(), $date, $event->roles->first()))->content();
        $this->assertNull($bare->with['cancelDeadlineLabel']);

        Carbon::setTestNow();
    }

    public function test_booking_made_inside_the_cutoff_window_has_an_undo_grace(): void
    {
        [$event, $pass, $holder, $date] = $this->setUpCancellableBooking(24, 'block');

        // Free the setup booking (made well before the deadline) and rebook
        // INSIDE the cutoff window: 12h before start, already past the 24h
        // deadline the moment it is made.
        $this->assertTrue($this->bookingService()->cancel($holder->fresh(), $event->id, $date)->ok);
        Carbon::setTestNow(Carbon::parse('2026-08-10 06:00:00'));
        $this->assertTrue($this->bookingService()->book($holder->fresh(), $event->id, $date)->ok);

        // 5 minutes later the mis-click is still undoable with full credit.
        Carbon::setTestNow(Carbon::parse('2026-08-10 06:05:00'));
        $undo = $this->bookingService()->cancel($holder->fresh(), $event->id, $date);
        $this->assertTrue($undo->ok);
        $this->assertSame('cancelled', $undo->status);
        $this->assertCount(0, $this->passUsages($holder));

        // Rebook; 20 minutes later the grace is over and block applies.
        Carbon::setTestNow(Carbon::parse('2026-08-10 06:10:00'));
        $this->assertTrue($this->bookingService()->book($holder->fresh(), $event->id, $date)->ok);
        Carbon::setTestNow(Carbon::parse('2026-08-10 06:30:00'));
        $late = $this->bookingService()->cancel($holder->fresh(), $event->id, $date);
        $this->assertFalse($late->ok);
        $this->assertSame('too_late', $late->status);

        Carbon::setTestNow();
    }

    public function test_forfeit_requires_acknowledgement_at_service_level(): void
    {
        [$event, $pass, $holder, $date] = $this->setUpCancellableBooking(24, 'forfeit');

        Carbon::setTestNow(Carbon::parse('2026-08-10 06:00:00'));
        $result = $this->bookingService()->cancel($holder->fresh(), $event->id, $date);

        $this->assertFalse($result->ok);
        $this->assertSame('confirm_forfeit', $result->status);
        // Nothing mutated: the booking still stands and holds its seat.
        $this->assertSame('booking', $this->passUsages($holder)[0]['kind']);
        $this->assertSame(1, $this->reserved($event, $date));

        Carbon::setTestNow();
    }

    public function test_waitlist_is_not_notified_for_a_started_occurrence(): void
    {
        [$event, $pass, $holder, $date] = $this->setUpCancellableBooking(0, 'forfeit');

        // An hour after the event started, the no-show forfeits their booking.
        Carbon::setTestNow(Carbon::parse('2026-08-10 19:00:00'));
        Queue::fake();
        $this->post(route('pass.cancel_booking', [
            'event_id' => UrlUtils::encodeId($event->id),
            'secret' => $holder->secret,
        ]), [
            'book_event_id' => UrlUtils::encodeId($event->id),
            'date' => $date,
            'forfeit_ack' => 1,
        ])->assertRedirect()->assertSessionHas('message', __('messages.pass_booking_forfeited'));

        // The seat is worthless now - no "spot opened" email for a running event.
        Queue::assertNotPushed(NotifyWaitlist::class);

        Carbon::setTestNow();
    }

    public function test_ended_bookings_are_hidden_only_when_a_cutoff_is_configured(): void
    {
        [$event, $pass, $holder, $date] = $this->setUpCancellableBooking(24, 'block');

        // Day after the occurrence (2h duration) ended: the dead "cancellation
        // closed" row disappears from the holder's booked list...
        Carbon::setTestNow(Carbon::parse('2026-08-11 12:00:00'));
        $this->assertCount(0, $this->bookingService()->bookedOccurrences($holder->fresh()));
        // ...but the visit stays consumed.
        $this->assertCount(1, $this->passUsages($holder));

        // Legacy passes (no cutoff) keep listing ended bookings, still
        // cancellable with credit.
        $pass->forceFill(['pass_cancel_cutoff_hours' => null])->save();
        $listed = $this->bookingService()->bookedOccurrences($holder->fresh());
        $this->assertCount(1, $listed);
        $this->assertTrue($this->bookingService()->cancel($holder->fresh(), $event->id, $date)->ok);

        Carbon::setTestNow();
    }

    public function test_timeless_recurring_event_has_no_deadline_and_stays_cancellable(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-08-01 12:00:00'));

        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['timezone' => 'UTC']);
        // days_of_week with NO starts_at: a supported state that must not crash
        // occurrenceStartUtc via the deadline helpers. (createEvent defaults a
        // null starts_at, so clear it after the fact.)
        $event = $this->createEvent($role, [
            'creator_role_id' => $role->id, 'tickets_enabled' => true,
            'days_of_week' => '1111111',
        ]);
        $event->starts_at = null;
        $event->saveQuietly();
        $event = $event->fresh();

        $pass = $this->createTicket($event, [
            'type' => 'Visit Pass', 'quantity' => 100, 'price' => 20,
            'is_pass' => true, 'pass_usage_type' => 'unlimited', 'pass_scope' => 'this_event',
            'pass_allow_booking' => true,
            'pass_cancel_cutoff_hours' => 24, 'pass_late_cancel_policy' => 'block',
        ]);
        $holder = $this->createSale($event, $role, ['email' => 'h@gmail.com', 'event_date' => '2026-08-05'], $pass);

        $this->assertNull($pass->passCancelDeadlineUtc($event, '2026-08-05'));

        // Seed a booking entry directly (booking such events goes through other
        // date plumbing; F4 is about the deadline surfaces not crashing).
        $st = $holder->fresh()->saleTickets->first(fn ($s) => $s->ticket->is_pass);
        $st->pass_usages = [[
            'event_id' => $event->id, 'date' => '2026-08-05',
            'at' => Carbon::parse('2026-08-01 12:00:00')->timestamp, 'kind' => 'booking',
        ]];
        $st->save();

        // No computable deadline -> listing renders and credit-cancel works.
        // Cancel while the date is still upcoming, via HTTP: the waitlist must
        // be notified even without a start instant (date-comparison fallback).
        Carbon::setTestNow(Carbon::parse('2026-08-04 12:00:00'));
        $rows = $this->bookingService()->bookedOccurrences($holder->fresh());
        $this->assertCount(1, $rows);
        $this->assertNull($rows[0]['cancel_deadline_label']);

        Queue::fake();
        $this->post(route('pass.cancel_booking', [
            'event_id' => UrlUtils::encodeId($event->id),
            'secret' => $holder->secret,
        ]), [
            'book_event_id' => UrlUtils::encodeId($event->id),
            'date' => '2026-08-05',
        ])->assertRedirect()->assertSessionHas('message', __('messages.pass_booking_cancelled'));
        Queue::assertPushed(NotifyWaitlist::class);
        $this->assertCount(0, $this->passUsages($holder));

        Carbon::setTestNow();
    }

    public function test_per_event_pass_does_not_offer_dates_of_spent_events(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-08-01 12:00:00'));

        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['timezone' => 'UTC']);
        $event = $this->createEvent($role, [
            'creator_role_id' => $role->id, 'tickets_enabled' => true,
            'starts_at' => '2026-08-02 18:00:00', 'duration' => 2, 'days_of_week' => '1111111',
        ]);
        $this->createTicket($event, ['type' => 'General', 'quantity' => 5, 'price' => 10]);
        $pass = $this->createTicket($event, [
            'type' => 'Festival Pass', 'quantity' => 100, 'price' => 50,
            'is_pass' => true, 'pass_usage_type' => 'per_event', 'pass_scope' => 'this_event',
            'pass_allow_booking' => true,
            'pass_cancel_cutoff_hours' => 24, 'pass_late_cancel_policy' => 'forfeit',
        ]);
        $holder = $this->createSale($event, $role, ['email' => 'h@gmail.com'], $pass);

        $this->assertNotEmpty($this->bookingService()->bookableOccurrences($holder->fresh()));

        // Book a date, then forfeit it past the deadline: the per-event visit is
        // spent, so no date of this event may be offered again (book() would
        // reject with limit_reached - the UI must agree with the limit).
        $date = '2026-08-05';
        $this->assertTrue($this->bookingService()->book($holder->fresh(), $event->id, $date)->ok);
        Carbon::setTestNow(Carbon::parse('2026-08-05 06:00:00'));
        $this->assertSame('forfeited', $this->bookingService()->cancel($holder->fresh(), $event->id, $date, allowForfeit: true)->status);

        $this->assertSame([], $this->bookingService()->bookableOccurrences($holder->fresh()));
        $this->assertSame('limit_reached', $this->bookingService()->book($holder->fresh(), $event->id, '2026-08-06')->status);

        Carbon::setTestNow();
    }

    public function test_date_only_event_has_no_phantom_deadline(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['timezone' => 'America/New_York']);
        $event = $this->createEvent($role, [
            'creator_role_id' => $role->id, 'tickets_enabled' => true,
        ]);
        $pass = $this->createTicket($event, [
            'type' => 'Visit Pass', 'quantity' => 100, 'price' => 20,
            'is_pass' => true, 'pass_usage_type' => 'unlimited', 'pass_scope' => 'this_event',
            'pass_allow_booking' => true,
            'pass_cancel_cutoff_hours' => 0, 'pass_late_cancel_policy' => 'block',
        ]);

        // All-day event: a date-only starts_at anchors to midnight UTC, which is
        // the PREVIOUS evening in New York - no trustworthy start instant, so no
        // deadline. (MySQL normalizes persisted values to midnight datetimes;
        // the date-only representation occurs on in-memory models, so exercise
        // it that way - same as occurrenceStartUtc's own strlen-10 branch.)
        $event->starts_at = '2026-08-10';
        $this->assertNull($pass->passCancelDeadlineUtc($event, '2026-08-10'));
    }

    public function test_grace_keeps_a_last_minute_booking_visible_after_the_occurrence_ends(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-08-10 20:10:00'));

        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['timezone' => 'UTC']);
        // Occurrence 18:00-20:00 has just ENDED; the booking was made 5 minutes
        // ago (a sell_after_start mis-click) and is still inside the undo grace.
        $event = $this->createEvent($role, [
            'creator_role_id' => $role->id, 'tickets_enabled' => true,
            'starts_at' => '2026-08-10 18:00:00', 'duration' => 2,
        ]);
        $pass = $this->createTicket($event, [
            'type' => 'Visit Pass', 'quantity' => 100, 'price' => 20,
            'is_pass' => true, 'pass_usage_type' => 'unlimited', 'pass_scope' => 'this_event',
            'pass_allow_booking' => true,
            'pass_cancel_cutoff_hours' => 24, 'pass_late_cancel_policy' => 'block',
        ]);
        $holder = $this->createSale($event, $role, ['email' => 'h@gmail.com'], $pass);

        $st = $holder->fresh()->saleTickets->first(fn ($s) => $s->ticket->is_pass);
        $st->pass_usages = [[
            'event_id' => $event->id, 'date' => '2026-08-10',
            'at' => Carbon::parse('2026-08-10 20:05:00')->timestamp, 'kind' => 'booking',
        ]];
        $st->save();

        // In grace: the row stays (with its credited cancel), past_cutoff is
        // suppressed, and the stale deadline is flagged so the UI hides it.
        $rows = $this->bookingService()->bookedOccurrences($holder->fresh());
        $this->assertCount(1, $rows);
        $this->assertFalse($rows[0]['past_cutoff']);
        $this->assertTrue($rows[0]['deadline_past']);
        $this->assertSame('cancelled', $this->bookingService()->cancel($holder->fresh(), $event->id, '2026-08-10')->status);

        // Re-seed and let the grace lapse: the ended booking now disappears.
        $st = $holder->fresh()->saleTickets->first(fn ($s) => $s->ticket->is_pass);
        $st->pass_usages = [[
            'event_id' => $event->id, 'date' => '2026-08-10',
            'at' => Carbon::parse('2026-08-10 20:05:00')->timestamp, 'kind' => 'booking',
        ]];
        $st->save();
        Carbon::setTestNow(Carbon::parse('2026-08-10 20:30:00'));
        $this->assertCount(0, $this->bookingService()->bookedOccurrences($holder->fresh()));

        Carbon::setTestNow();
    }

    public function test_deadline_label_follows_the_authenticated_viewer(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['timezone' => 'America/New_York']);
        $event = $this->createEvent($role, [
            'creator_role_id' => $role->id, 'tickets_enabled' => true,
            'starts_at' => '2026-08-10 22:00:00', 'duration' => 2,
        ]);
        $instant = Carbon::parse('2026-08-10 22:00:00', 'UTC');

        // Anonymous guest: schedule timezone (New York = UTC-4 in August).
        $this->assertStringContainsString('6:00 PM', $event->localizedInstantLabel($instant));

        // Logged-in viewer in Los Angeles with 24-hour time: their zone, their clock -
        // matching how localStartsAt renders the date beside it.
        $viewer = $this->createOwner();
        $viewer->timezone = 'America/Los_Angeles';
        $viewer->use_24_hour_time = true;
        $viewer->save();
        $this->actingAs($viewer);
        $this->assertStringContainsString('15:00', $event->fresh()->localizedInstantLabel($instant));

        auth()->logout();
    }

    public function test_deadline_label_is_localized_to_the_schedule(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-08-01 12:00:00'));

        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', [
            'timezone' => 'UTC', 'language_code' => 'fr', 'use_24_hour_time' => true,
        ]);
        $event = $this->createEvent($role, [
            'creator_role_id' => $role->id, 'tickets_enabled' => true,
            'starts_at' => '2026-08-10 18:00:00', 'duration' => 2,
        ]);

        $label = $event->localizedInstantLabel(Carbon::parse('2026-08-09 18:00:00', 'UTC'));

        $this->assertSame('9 août 2026 • 18:00', $label);

        Carbon::setTestNow();
    }
}
