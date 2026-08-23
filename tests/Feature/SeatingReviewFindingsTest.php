<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventSeatingMap;
use App\Models\Role;
use App\Models\SeatingLevel;
use App\Models\SeatingPlan;
use App\Models\SeatingSeat;
use App\Models\SeatingSection;
use App\Repos\EventRepo;
use App\Services\SeatingMapService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The four defects the end-to-end review found, and the lock order it corrected.
 *
 * Each of these reproduced as a failing test before the fix. Three were killed during the same
 * review and are deliberately absent: the expired-hold sweep not bumping the poll cursor (refuted -
 * hold expiry is evaluated at read time, so the seat is sellable the moment it lapses), and the
 * amount_mismatch counter drift and the soft-deletable sold ticket (both reproduce with no seating
 * involved and are pre-existing).
 */
class SeatingReviewFindingsTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    /** One section, one row of $count seats. */
    private function makePlan(Role $role, int $count = 2): SeatingPlan
    {
        $plan = SeatingPlan::create(['role_id' => $role->id, 'name' => 'Tiny House']);
        $level = SeatingLevel::create(['seating_plan_id' => $plan->id, 'name' => 'Ground']);
        $section = SeatingSection::create([
            'seating_plan_id' => $plan->id, 'seating_level_id' => $level->id,
            'name' => 'Stalls', 'band' => 'Stalls', 'kind' => 'seated',
        ]);
        for ($n = 1; $n <= $count; $n++) {
            SeatingSeat::create([
                'seating_plan_id' => $plan->id, 'seating_section_id' => $section->id,
                'row_label' => 'A', 'row_position' => 1, 'seat_label' => (string) $n,
                'position' => $n, 'x' => $n * 26,
            ]);
        }

        return $plan->fresh();
    }

    private function seatedEvent(Role $role, SeatingPlan $plan, array $extra = []): Event
    {
        $request = Request::create('/', 'POST', array_merge([
            'name' => 'Seated Show',
            'starts_at' => now()->addMonths(6)->setTime(19, 30)->format('Y-m-d H:i:s'),
            'tickets_enabled' => 1, 'payment_method' => 'cash', 'ticket_currency_code' => 'USD',
            'creator_role_id' => $role->id, 'seating_plan_id' => $plan->id,
            'tickets' => [['type' => 'Stalls', 'price' => 40, 'quantity' => 999, 'seating_band' => 'Stalls']],
        ], $extra));
        $request->setUserResolver(fn () => $role->user);
        $event = app(EventRepo::class)->saveEvent($role, $request)->fresh();
        $event->roles()->updateExistingPivot($role->id, ['is_accepted' => true]);

        return $event->fresh();
    }

    // ---------------------------------------------------------------- FINDING 1

    public function test_a_multi_admit_pass_cannot_wave_a_guest_into_a_full_allocated_house(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        // A two-seat house, both seats sold to regular buyers.
        $event = $this->seatedEvent($role, $this->makePlan($role, 2));
        $date = $event->saleEventDateFromStartsAt();
        $map = app(SeatingMapService::class)->materialize($event, $date);
        SeatingSeat::where('event_seating_map_id', $map->id)->update(['status' => 'sold']);

        $this->assertSame(0, app(SeatingMapService::class)->availableSeatCount($map),
            'fixture: the house is physically full');

        $pass = $this->createTicket($event, [
            'type' => 'Group Pass', 'quantity' => 100, 'price' => 20,
            'is_pass' => true, 'pass_usage_type' => 'total', 'pass_max_uses' => 5,
            'pass_valid_days' => 400, 'pass_scope' => 'this_event', 'pass_admits_per_event' => 3,
        ]);
        $holder = $this->createSale($event, $role, ['email' => 'h@gmail.com'], $pass);
        $holder->forceFill(['event_date' => $date])->save();

        // A pass is redeemed AT the door, so stand on the night itself.
        Carbon::setTestNow(Carbon::parse($event->starts_at, 'UTC'));

        $svc = app(\App\Services\PassRedemptionService::class);
        $first = $svc->redeem($holder->fresh(), $event->fresh(), Carbon::now());
        $this->assertSame('valid', $first->pass_status, 'the holder themselves gets in');

        // PassBookingTest:340 pins exactly this for a QUANTITY event: the second scan is refused
        // because the room is full. occurrenceSeatsRemaining() returns null on an allocated event,
        // and the gate reads null as "unlimited house", so it fails open on the one kind of house
        // that is most definitely finite.
        $second = $svc->redeem($holder->fresh(), $event->fresh(), Carbon::now());

        $this->assertSame('already_today', $second->pass_status,
            'a walk-in guest was admitted to a house with zero free seats');

        Carbon::setTestNow();
    }

    // ---------------------------------------------------------------- FINDING 3

    public function test_revert_reads_its_guards_under_a_row_lock(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role, 2));
        $map = app(SeatingMapService::class)->materialize($event, $event->saleEventDateFromStartsAt());

        $seatReads = [];
        \Illuminate\Support\Facades\DB::listen(function ($q) use (&$seatReads) {
            $sql = strtolower($q->sql);
            if (str_starts_with($sql, 'select') && str_contains($sql, 'seating_seats')) {
                $seatReads[] = str_contains($sql, 'for update');
            }
        });

        app(SeatingMapService::class)->revertToTemplate($map);

        $this->assertNotEmpty($seatReads, 'fixture: revert must read the seats at all');
        $this->assertTrue($seatReads[0],
            'the guard read is a consistent read, so a seat-selling transaction still in flight is '
            .'invisible to it - and the cascade then deletes that seat once the sale commits');
    }

    public function test_revert_still_refuses_a_sold_seat_and_a_live_cart_hold(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role, 3));
        $map = app(SeatingMapService::class)->materialize($event, $event->saleEventDateFromStartsAt());
        $seats = SeatingSeat::where('event_seating_map_id', $map->id)->orderBy('position')->get();

        $seats[0]->forceFill(['status' => 'sold'])->save();
        $this->assertFalse(app(SeatingMapService::class)->revertToTemplate($map->fresh()), 'sold seat');
        $this->assertNotNull(EventSeatingMap::find($map->id));

        $seats[0]->forceFill(['status' => 'available'])->save();
        $seats[1]->forceFill([
            'status' => 'held', 'hold_kind' => 'cart', 'hold_token' => str_repeat('a', 32),
            'hold_expires_at' => now()->addMinutes(5),
        ])->save();
        $this->assertFalse(app(SeatingMapService::class)->revertToTemplate($map->fresh()), 'live cart hold');

        // A staff hold is NOT a reason to refuse - discarding per-date customisation is the point.
        $seats[1]->forceFill([
            'status' => 'held', 'hold_kind' => 'house', 'hold_token' => null, 'hold_expires_at' => null,
        ])->save();
        $this->assertTrue(app(SeatingMapService::class)->revertToTemplate($map->fresh()), 'staff hold');
        $this->assertNull(EventSeatingMap::find($map->id));
    }

    // ---------------------------------------------------------------- FINDING 7

    public function test_freeing_a_seat_at_the_box_office_offers_it_to_the_waitlist(): void
    {
        \Illuminate\Support\Facades\Queue::fake();

        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role, 2));
        $date = $event->saleEventDateFromStartsAt();
        $map = app(SeatingMapService::class)->materialize($event, $date);
        $ticket = $event->tickets->first();

        // Sell the whole house, so the event is full and a waitlist is the only way in.
        $sale = $this->createSale($event, $role, ['email' => 'buyer@gmail.com', 'event_date' => $date], $ticket, 2);
        $line = $sale->saleTickets()->first();
        SeatingSeat::where('event_seating_map_id', $map->id)
            ->update(['status' => 'sold', 'sale_id' => $sale->id, 'sale_ticket_id' => $line->id]);

        \App\Models\TicketWaitlist::create([
            'event_id' => $event->id, 'event_date' => $date, 'subdomain' => $role->subdomain,
            'name' => 'Hopeful', 'email' => 'hopeful@gmail.com', 'status' => 'waiting',
        ]);

        \Illuminate\Support\Facades\Queue::fake();

        // Staff put one seat back on sale. Every OTHER way a seat frees up runs through
        // Sale::booted, which dispatches NotifyWaitlist.
        app(\App\Services\BoxOfficeSeatingService::class)->releaseSeat($map, $line->seatingSeats()->first()->id ?? SeatingSeat::where('event_seating_map_id', $map->id)->value('id'));

        $this->assertSame(1, app(SeatingMapService::class)->availableSeatCount($map),
            'fixture: a seat really did go back on sale');

        // assertPushed's 2nd arg is a CALLBACK, not a message - passing a string makes PHPUnit
        // try to call it as a function, which reads as a failure for the wrong reason.
        \Illuminate\Support\Facades\Queue::assertPushed(\App\Jobs\NotifyWaitlist::class);
    }

    // ---------------------------------------------------------------- FINDING 8

    /**
     * Record the tables each lock-taking statement touches, in order.
     *
     * Watching only `for update` was the blind spot that made the first version of this check
     * wrong: a plain UPDATE takes an exclusive row lock held to commit, and bumpVersion() is
     * exactly that on event_seating_maps - the row that closes the real cycle.
     */
    private function lockOrderOf(\Closure $path): array
    {
        $order = [];
        \Illuminate\Support\Facades\DB::listen(function ($q) use (&$order) {
            $sql = strtolower($q->sql);
            if (str_contains($sql, 'for update') && preg_match('/from\s+`?(\w+)`?/i', $q->sql, $m)) {
                $order[] = $m[1];
            } elseif (str_starts_with($sql, 'update ') && preg_match('/^update\s+`?(\w+)`?/i', $q->sql, $m)) {
                $order[] = $m[1];
            }
        });

        $path();

        return array_values(array_filter($order, fn ($t, $i) => $i === 0 || $order[$i - 1] !== $t, ARRAY_FILTER_USE_BOTH));
    }

    public function test_box_office_and_pass_booking_agree_on_lock_order(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role, 6));
        $date = $event->saleEventDateFromStartsAt();
        $map = app(SeatingMapService::class)->materialize($event, $date);

        $ticket = $event->tickets->first();
        $sale = $this->createSale($event, $role, ['email' => 'a@gmail.com', 'event_date' => $date], $ticket, 2);
        $line = $sale->saleTickets()->first();
        $seatA = SeatingSeat::where('event_seating_map_id', $map->id)->orderBy('position')->first();
        $seatA->forceFill(['status' => 'sold', 'sale_id' => $sale->id, 'sale_ticket_id' => $line->id])->save();

        $orderA = $this->lockOrderOf(function () use ($map, $seatA) {
            app(\App\Services\BoxOfficeSeatingService::class)->releaseSeat($map, $seatA->id);
        });

        $pass = $this->createTicket($event, [
            'type' => 'Season Pass', 'quantity' => 100, 'price' => 50, 'is_pass' => true,
            'pass_usage_type' => 'unlimited', 'pass_scope' => 'this_event', 'pass_allow_booking' => true,
        ]);
        $holder = $this->createSale($event, $role, ['email' => 'h@gmail.com'], $pass);

        $orderB = $this->lockOrderOf(function () use ($holder, $event, $date) {
            app(\App\Services\PassBookingService::class)->book($holder->fresh(), $event->id, $date);
        });

        // The pair that actually closes a cycle: both rows are genuinely shared (same occurrence
        // map, and book() locks ALL of the event's tickets). The seats/sale_tickets rows are not
        // shared between these paths, so their relative order cannot deadlock.
        $pair = ['event_seating_maps', 'tickets'];
        $a = array_values(array_intersect($orderA, $pair));
        $b = array_values(array_intersect($orderB, $pair));

        $this->assertCount(2, $a, 'fixture: the box office path must touch both rows');
        $this->assertCount(2, $b, 'fixture: the pass path must touch both rows');

        $this->assertSame($b, $a,
            'lock-order inversion on {event_seating_maps, tickets}: box office takes ['
            .implode(' -> ', $a).'] while pass booking takes ['.implode(' -> ', $b).']. '
            .'DB::transaction is single-attempt here, so the deadlock victim gets a 500.');
    }

    public function test_moving_starts_at_outside_eventrepo_carries_the_seat_map(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role, 2));
        $originalDate = $event->saleEventDateFromStartsAt();

        $map = app(SeatingMapService::class)->materialize($event, $originalDate);
        $seat = SeatingSeat::where('event_seating_map_id', $map->id)->orderBy('position')->first();
        $sale = $this->createSale($event, $role, ['email' => 'buyer@gmail.com', 'event_date' => $originalDate]);
        $seat->forceFill(['status' => 'sold', 'sale_id' => $sale->id])->save();

        // Inbound calendar sync moves the event. GoogleCalendarService, MicrosoftCalendarService,
        // CalDAVService, the booking-request flow and the Eventbrite importer all write starts_at
        // straight on the model - they never go through EventRepo::saveEvent(), which is the only
        // caller of SeatingMapService::rekeyOccurrence(). Event::saving DOES re-key tickets.sold
        // and sales.event_date, so everything else follows the move.
        $event->starts_at = now()->addMonths(7)->setTime(19, 30)->format('Y-m-d H:i:s');
        $event->save();

        $event = $event->fresh();
        $newDate = $event->saleEventDateFromStartsAt();
        $this->assertNotSame($originalDate, $newDate, 'fixture: the event really moved');

        $this->assertSame($newDate, EventSeatingMap::where('event_id', $event->id)->first()->event_date,
            'the seat map is stranded on the old date: the sold seat is unreachable and the new '
            .'date will snapshot a fresh, empty house');
    }
}
