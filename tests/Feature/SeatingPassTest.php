<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventSeatingMap;
use App\Models\Role;
use App\Models\Sale;
use App\Models\SeatingLevel;
use App\Models\SeatingPlan;
use App\Models\SeatingSeat;
use App\Models\SeatingSection;
use App\Models\Ticket;
use App\Repos\EventRepo;
use App\Services\PassBookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Passes on an allocated event.
 *
 * A pass books in advance without ever seeing the picker, so the booking has to take a real seat on
 * the holder's behalf. Before this, seatsLeft() read occurrenceSeatsRemaining(), which is null by
 * design for an allocated event - so an unlimited number of pass holders could book a house with
 * eight seats in it, and every one of them would have arrived holding a valid QR code.
 */
class SeatingPassTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private function passes(): PassBookingService
    {
        return app(PassBookingService::class);
    }

    /** Stalls: one row of 4. Circle: one row of 2. */
    private function makePlan(Role $role): SeatingPlan
    {
        $plan = SeatingPlan::create(['role_id' => $role->id, 'name' => 'Small House']);
        $level = SeatingLevel::create(['seating_plan_id' => $plan->id, 'name' => 'Ground']);

        foreach ([['Stalls', 4, 0], ['Circle', 2, 1]] as [$name, $count, $pos]) {
            $section = SeatingSection::create([
                'seating_plan_id' => $plan->id, 'seating_level_id' => $level->id,
                'name' => $name, 'band' => $name, 'kind' => 'seated', 'position' => $pos,
            ]);
            for ($n = 1; $n <= $count; $n++) {
                SeatingSeat::create([
                    'seating_plan_id' => $plan->id, 'seating_section_id' => $section->id,
                    'row_label' => 'A', 'row_position' => 1, 'seat_label' => (string) $n,
                    'position' => $n, 'x' => $n * 26,
                ]);
            }
        }

        return $plan->fresh();
    }

    private function seatedEvent(Role $role, SeatingPlan $plan): Event
    {
        $request = Request::create('/', 'POST', [
            'name' => 'Seated Show',
            'starts_at' => now()->addMonths(6)->setTime(19, 30)->format('Y-m-d H:i:s'),
            'tickets_enabled' => 1, 'payment_method' => 'cash', 'ticket_currency_code' => 'USD',
            'creator_role_id' => $role->id,
            'seating_plan_id' => $plan->id,
            'tickets' => [
                ['type' => 'Stalls', 'price' => 40, 'quantity' => 999, 'seating_band' => 'Stalls'],
                ['type' => 'Circle', 'price' => 25, 'quantity' => 999, 'seating_band' => 'Circle'],
            ],
        ]);
        $request->setUserResolver(fn () => $role->user);
        $event = app(EventRepo::class)->saveEvent($role, $request)->fresh();
        $event->roles()->updateExistingPivot($role->id, ['is_accepted' => true]);

        return $event->fresh();
    }

    private function passTicket(Event $event, array $attrs = []): Ticket
    {
        return $this->createTicket($event, array_merge([
            'type' => 'Season Pass', 'quantity' => 100, 'price' => 50,
            'is_pass' => true, 'pass_usage_type' => 'unlimited', 'pass_scope' => 'this_event',
            'pass_allow_booking' => true,
        ], $attrs));
    }

    private function usage(Sale $holder): array
    {
        return $holder->fresh()->saleTickets()->first()->pass_usages[0] ?? [];
    }

    private function soldSeats(Event $event): int
    {
        $map = EventSeatingMap::where('event_id', $event->id)->first();

        return $map ? SeatingSeat::where('event_seating_map_id', $map->id)->where('status', 'sold')->count() : 0;
    }

    public function test_pass_booking_claims_a_real_seat_and_records_it_on_the_usage(): void
    {
        $role = $this->createRole($this->createOwner());
        $event = $this->seatedEvent($role, $this->makePlan($role));
        $date = $event->saleEventDateFromStartsAt();
        $pass = $this->passTicket($event);
        $holder = $this->createSale($event, $role, ['email' => 'h@gmail.com'], $pass);

        $result = $this->passes()->book($holder->fresh(), $event->id, $date);
        $this->assertTrue($result->ok, 'booking failed: '.$result->status);

        $usage = $this->usage($holder);
        $this->assertArrayHasKey('seat_id', $usage, 'the booking must record which seat it took');

        $seat = SeatingSeat::find($usage['seat_id']);
        $this->assertSame('sold', $seat->status);
        $this->assertSame($holder->id, (int) $seat->sale_id);
        $this->assertSame(1, $this->soldSeats($event));
    }

    public function test_pass_bookings_stop_when_the_house_is_physically_full(): void
    {
        $role = $this->createRole($this->createOwner());
        // Six seats in the plan: four Stalls, two Circle.
        $event = $this->seatedEvent($role, $this->makePlan($role));
        $date = $event->saleEventDateFromStartsAt();
        $pass = $this->passTicket($event);

        $holders = [];
        for ($i = 0; $i < 7; $i++) {
            $holders[] = $this->createSale($event, $role, ['email' => "h{$i}@gmail.com"], $pass);
        }

        $booked = 0;
        foreach ($holders as $holder) {
            if ($this->passes()->book($holder->fresh(), $event->id, $date)->ok) {
                $booked++;
            }
        }

        $this->assertSame(6, $booked, 'a six-seat house must not book seven pass holders');
        $this->assertSame('sold_out', $this->passes()->book($holders[6]->fresh(), $event->id, $date)->status);
        $this->assertSame(6, $this->soldSeats($event));
    }

    public function test_pass_and_regular_buyers_compete_for_the_same_seats(): void
    {
        $role = $this->createRole($this->createOwner());
        $event = $this->seatedEvent($role, $this->makePlan($role));
        $date = $event->saleEventDateFromStartsAt();
        $pass = $this->passTicket($event);
        $holder = $this->createSale($event, $role, ['email' => 'h@gmail.com'], $pass);

        // Sell every seat in the map out from under the pass.
        $map = app(\App\Services\SeatingMapService::class)->materialize($event, $date);
        SeatingSeat::where('event_seating_map_id', $map->id)->update(['status' => 'sold']);

        $result = $this->passes()->book($holder->fresh(), $event->id, $date);
        $this->assertFalse($result->ok);
        $this->assertSame('sold_out', $result->status);
    }

    public function test_the_bookable_list_shows_a_full_house_as_sold_out(): void
    {
        $role = $this->createRole($this->createOwner());
        $event = $this->seatedEvent($role, $this->makePlan($role));
        $date = $event->saleEventDateFromStartsAt();
        $pass = $this->passTicket($event);
        $holder = $this->createSale($event, $role, ['email' => 'h@gmail.com'], $pass);

        // Six seats in the plan, nothing sold: the holder is told six.
        $before = collect($this->passes()->bookableOccurrences($holder->fresh()))->firstWhere('date', $date);
        $this->assertSame(6, $before['seats_left']);
        $this->assertFalse($before['sold_out']);

        $map = app(\App\Services\SeatingMapService::class)->materialize($event, $date);
        SeatingSeat::where('event_seating_map_id', $map->id)->update(['status' => 'sold']);

        // Otherwise the date is offered as bookable and the click fails at the far end.
        $after = collect($this->passes()->bookableOccurrences($holder->fresh()))->firstWhere('date', $date);
        $this->assertSame(0, $after['seats_left']);
        $this->assertTrue($after['sold_out']);
    }

    public function test_cancelling_a_booking_gives_the_seat_back(): void
    {
        $role = $this->createRole($this->createOwner());
        $event = $this->seatedEvent($role, $this->makePlan($role));
        $date = $event->saleEventDateFromStartsAt();
        $pass = $this->passTicket($event);
        $holder = $this->createSale($event, $role, ['email' => 'h@gmail.com'], $pass);

        $this->assertTrue($this->passes()->book($holder->fresh(), $event->id, $date)->ok);
        $seatId = $this->usage($holder)['seat_id'];

        $this->assertTrue($this->passes()->cancel($holder->fresh(), $event->id, $date)->ok);

        $seat = SeatingSeat::find($seatId);
        $this->assertSame('available', $seat->status);
        $this->assertNull($seat->sale_id);
        $this->assertSame(0, $this->soldSeats($event));
    }

    public function test_forfeiting_a_late_cancel_also_gives_the_seat_back(): void
    {
        $role = $this->createRole($this->createOwner());
        $event = $this->seatedEvent($role, $this->makePlan($role));
        $date = $event->saleEventDateFromStartsAt();
        // A cutoff a year before the occurrence puts the deadline in the past already, so the
        // forfeit branch is the one that runs.
        $pass = $this->passTicket($event, [
            'pass_cancel_cutoff_hours' => 24 * 365, 'pass_late_cancel_policy' => 'forfeit',
        ]);
        $holder = $this->createSale($event, $role, ['email' => 'h@gmail.com'], $pass);

        $this->assertTrue($this->passes()->book($holder->fresh(), $event->id, $date)->ok);
        $seatId = $this->usage($holder)['seat_id'];

        // Past the undo grace, so the forfeit branch is the one that runs.
        $later = now()->addHours(2);
        $result = $this->passes()->cancel($holder->fresh(), $event->id, $date, $later, true);
        $this->assertTrue($result->ok, 'expected a forfeit, got: '.$result->status);
        $this->assertSame('forfeited', $result->status);

        $this->assertSame('available', SeatingSeat::find($seatId)->status);
        $this->assertSame(0, $this->soldSeats($event));
        $this->assertArrayNotHasKey('seat_id', $this->usage($holder), 'a forfeited entry must not keep a seat');
    }

    public function test_a_banded_pass_only_takes_seats_from_its_own_band(): void
    {
        $role = $this->createRole($this->createOwner());
        $event = $this->seatedEvent($role, $this->makePlan($role));
        $date = $event->saleEventDateFromStartsAt();
        $pass = $this->passTicket($event);
        $pass->forceFill(['seating_band' => 'Circle'])->save();

        // Circle holds exactly two seats; the third holder must be refused even though
        // four Stalls seats are sitting empty.
        $ids = [];
        for ($i = 0; $i < 3; $i++) {
            $holder = $this->createSale($event, $role, ['email' => "c{$i}@gmail.com"], $pass);
            $result = $this->passes()->book($holder->fresh(), $event->id, $date);
            if ($result->ok) {
                $ids[] = $this->usage($holder)['seat_id'];
            }
        }

        $this->assertCount(2, $ids, 'a Circle pass must not spill into the Stalls');

        $circleId = EventSeatingMap::where('event_id', $event->id)->first()
            ->sections()->where('name', 'Circle')->value('id');
        foreach ($ids as $id) {
            $this->assertSame($circleId, (int) SeatingSeat::find($id)->seating_section_id);
        }
    }

    public function test_pass_seats_are_not_bound_to_the_pass_line(): void
    {
        $role = $this->createRole($this->createOwner());
        $event = $this->seatedEvent($role, $this->makePlan($role));
        $date = $event->saleEventDateFromStartsAt();
        $pass = $this->passTicket($event);
        $holder = $this->createSale($event, $role, ['email' => 'h@gmail.com'], $pass);

        $this->assertTrue($this->passes()->book($holder->fresh(), $event->id, $date)->ok);

        $line = $holder->fresh()->saleTickets()->first();
        $this->assertNull(SeatingSeat::find($this->usage($holder)['seat_id'])->sale_ticket_id);
        // Otherwise every ticket the holder ever prints lists every seat they ever booked.
        $this->assertSame([], $line->seatLabels());
    }

    public function test_cancel_does_not_free_a_seat_the_box_office_reassigned(): void
    {
        $role = $this->createRole($this->createOwner());
        $event = $this->seatedEvent($role, $this->makePlan($role));
        $date = $event->saleEventDateFromStartsAt();
        $pass = $this->passTicket($event);
        $holder = $this->createSale($event, $role, ['email' => 'h@gmail.com'], $pass);

        $this->assertTrue($this->passes()->book($holder->fresh(), $event->id, $date)->ok);
        $seatId = $this->usage($holder)['seat_id'];

        // Staff released it and someone else bought that exact seat.
        $other = $this->createSale($event, $role, ['email' => 'other@gmail.com']);
        SeatingSeat::whereKey($seatId)->update(['sale_id' => $other->id, 'status' => 'sold']);

        $this->assertTrue($this->passes()->cancel($holder->fresh(), $event->id, $date)->ok);

        $seat = SeatingSeat::find($seatId);
        $this->assertSame('sold', $seat->status, 'the new buyer must keep the seat');
        $this->assertSame($other->id, (int) $seat->sale_id);
    }

    public function test_the_holder_is_told_which_seat_the_booking_took(): void
    {
        $role = $this->createRole($this->createOwner());
        $event = $this->seatedEvent($role, $this->makePlan($role));
        $date = $event->saleEventDateFromStartsAt();
        $pass = $this->passTicket($event);
        $holder = $this->createSale($event, $role, ['email' => 'h@gmail.com'], $pass);

        $this->assertTrue($this->passes()->book($holder->fresh(), $event->id, $date)->ok);

        $label = SeatingSeat::find($this->usage($holder)['seat_id'])->fullLabel();
        $this->assertNotSame('', $label);

        $booked = collect($this->passes()->bookedOccurrences($holder->fresh()))->firstWhere('date', $date);
        $this->assertSame($label, $booked['seat_label']);

        // ...and it actually reaches the page, which is the only place they see it before the door.
        $this->get(route('ticket.view', [
            'event_id' => \App\Utils\UrlUtils::encodeId($event->id),
            'secret' => $holder->secret,
        ]))->assertOk()->assertSee($label, false);
    }

    public function test_a_seat_taken_between_the_pick_and_the_claim_is_not_sold_twice(): void
    {
        $role = $this->createRole($this->createOwner());
        $event = $this->seatedEvent($role, $this->makePlan($role));
        $date = $event->saleEventDateFromStartsAt();
        $pass = $this->passTicket($event);
        $holder = $this->createSale($event, $role, ['email' => 'h@gmail.com'], $pass);

        $map = app(\App\Services\SeatingMapService::class)->materialize($event, $date);
        $ticket = $event->tickets->firstWhere('seating_band', 'Stalls');

        // Stand where the race stands: pick a seat, let somebody else take it, then claim it.
        // BestAvailableService reads WITHOUT a lock by design, so the pick is only a proposal.
        $picked = app(\App\Services\BestAvailableService::class)->pick($map, $ticket, 1);
        $this->assertCount(1, $picked);

        $other = $this->createSale($event, $role, ['email' => 'other@gmail.com']);
        SeatingSeat::whereKey($picked[0])->update(['status' => 'sold', 'sale_id' => $other->id]);

        $claimed = app(\App\Services\SeatHoldService::class)
            ->claimPickedSeats($map, $picked, $holder->id, null);

        $this->assertFalse($claimed, 'a stale pick must not overwrite a live booking');

        $seat = SeatingSeat::find($picked[0]);
        $this->assertSame($other->id, (int) $seat->sale_id, 'the seat still belongs to whoever took it');

        // ...and the booking itself still works, on a different seat.
        $this->assertTrue($this->passes()->book($holder->fresh(), $event->id, $date)->ok);
        $this->assertNotSame($picked[0], $this->usage($holder)['seat_id']);
    }

    public function test_a_non_allocated_event_is_unaffected(): void
    {
        $role = $this->createRole($this->createOwner());
        $event = $this->createEvent($role, ['creator_role_id' => $role->id, 'tickets_enabled' => true]);
        $date = $event->saleEventDateFromStartsAt();
        $this->createTicket($event, ['type' => 'General', 'quantity' => 5, 'price' => 10]);
        $pass = $this->passTicket($event);
        $holder = $this->createSale($event, $role, ['email' => 'h@gmail.com'], $pass);

        $this->assertTrue($this->passes()->book($holder->fresh(), $event->id, $date)->ok);
        $this->assertArrayNotHasKey('seat_id', $this->usage($holder));
        $this->assertSame(1, $event->fresh()->passReservedSeats($date));
    }
}
