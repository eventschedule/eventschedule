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
use App\Repos\EventRepo;
use App\Services\SeatHoldService;
use App\Services\SeatingMapService;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Stage two of the two-stage claim: held seats become sold seats bound to a sale, and every way a
 * sale can die gives them back.
 *
 * The claim runs inside the checkout transaction and BEFORE any payment page, matching how
 * SaleTicket::created already takes quantity stock. Deferring it until payment succeeds is the one
 * ordering that produces "I paid and my seats are gone".
 */
class SeatingCheckoutTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private function maps(): SeatingMapService
    {
        return app(SeatingMapService::class);
    }

    private function holds(): SeatHoldService
    {
        return app(SeatHoldService::class);
    }

    /** Stalls: one row of 8. Circle: one row of 4. */
    private function makePlan(Role $role): SeatingPlan
    {
        $plan = SeatingPlan::create(['role_id' => $role->id, 'name' => 'Main House']);
        $level = SeatingLevel::create(['seating_plan_id' => $plan->id, 'name' => 'Ground']);

        foreach ([['Stalls', 8, 0], ['Circle', 4, 1]] as [$name, $count, $pos]) {
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

    private function seatsIn(EventSeatingMap $map, string $section)
    {
        return SeatingSeat::where('event_seating_map_id', $map->id)
            ->where('seating_section_id', $map->sections()->where('name', $section)->value('id'))
            ->orderBy('position')->get();
    }

    /** Hold through the real endpoint so the session token is the one checkout will trust. */
    private function hold(Role $role, Event $event, array $seatIds)
    {
        return $this->postJson(route('seating.hold', ['subdomain' => $role->subdomain]), [
            'event_id' => UrlUtils::encodeId($event->id),
            'date' => $event->saleEventDateFromStartsAt(),
            'seat_ids' => $seatIds,
        ]);
    }

    private function checkout(Role $role, Event $event, array $tickets)
    {
        return $this->post(route('event.checkout', ['subdomain' => $role->subdomain]), [
            'event_id' => UrlUtils::encodeId($event->id),
            'event_date' => $event->saleEventDateFromStartsAt(),
            'name' => 'Seat Buyer',
            'email' => 'buyer@gmail.com',
            'tickets' => $tickets,
        ]);
    }

    public function test_checkout_binds_the_held_seats_to_the_sale(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role));
        $map = $this->maps()->materialize($event);
        $stalls = $event->tickets->firstWhere('type', 'Stalls');
        $seats = $this->seatsIn($map, 'Stalls')->take(2);

        $this->hold($role, $event, $seats->pluck('id')->all())->assertOk();
        $this->checkout($role, $event, [UrlUtils::encodeId($stalls->id) => 2])->assertRedirect();

        $sale = Sale::latest('id')->firstOrFail();
        $saleTicket = $sale->saleTickets()->firstOrFail();

        foreach ($seats as $seat) {
            $seat->refresh();
            $this->assertSame('sold', $seat->status);
            $this->assertSame($sale->id, $seat->sale_id);
            $this->assertSame($saleTicket->id, $seat->sale_ticket_id, 'the seat names the line that paid for it');
            $this->assertNull($seat->hold_token, 'the cart hold is consumed, not left behind');
        }
    }

    public function test_a_quantity_typed_past_the_held_seats_is_refused_and_writes_nothing(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role));
        $map = $this->maps()->materialize($event);
        $stalls = $event->tickets->firstWhere('type', 'Stalls');
        $seats = $this->seatsIn($map, 'Stalls')->take(2);

        $this->hold($role, $event, $seats->pluck('id')->all())->assertOk();

        // Two seats held, four claimed. The books must balance or the whole thing rolls back.
        $this->checkout($role, $event, [UrlUtils::encodeId($stalls->id) => 4]);

        $this->assertSame(0, Sale::count(), 'no half-seated sale is written');
        foreach ($seats as $seat) {
            $this->assertSame('held', $seat->fresh()->status, 'and the hold survives for another try');
        }
    }

    public function test_seats_held_for_a_band_the_order_did_not_buy_are_released(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role));
        $map = $this->maps()->materialize($event);
        $stalls = $event->tickets->firstWhere('type', 'Stalls');

        $stallsSeat = $this->seatsIn($map, 'Stalls')->first();
        $circleSeat = $this->seatsIn($map, 'Circle')->first();

        // The guest looked at the Circle, then bought only the Stalls.
        $this->hold($role, $event, [$stallsSeat->id, $circleSeat->id])->assertOk();
        $this->checkout($role, $event, [UrlUtils::encodeId($stalls->id) => 1])->assertRedirect();

        $this->assertSame('sold', $stallsSeat->fresh()->status);
        $this->assertSame('available', $circleSeat->fresh()->status,
            'an unbought hold is handed back at once rather than left to expire');
    }

    public function test_cancelling_a_sale_gives_the_seats_back(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role));
        $map = $this->maps()->materialize($event);
        $stalls = $event->tickets->firstWhere('type', 'Stalls');
        $seats = $this->seatsIn($map, 'Stalls')->take(2);

        $this->hold($role, $event, $seats->pluck('id')->all())->assertOk();
        $this->checkout($role, $event, [UrlUtils::encodeId($stalls->id) => 2])->assertRedirect();

        $sale = Sale::latest('id')->firstOrFail();
        $sale->status = 'cancelled';
        $sale->save();

        foreach ($seats as $seat) {
            $seat->refresh();
            $this->assertSame('available', $seat->status);
            $this->assertNull($seat->sale_id);
            $this->assertNull($seat->sale_ticket_id);
        }
    }

    public function test_refunding_gives_the_seats_back_too(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role));
        $map = $this->maps()->materialize($event);
        $stalls = $event->tickets->firstWhere('type', 'Stalls');
        $seat = $this->seatsIn($map, 'Stalls')->first();

        $this->hold($role, $event, [$seat->id])->assertOk();
        $this->checkout($role, $event, [UrlUtils::encodeId($stalls->id) => 1])->assertRedirect();

        $sale = Sale::latest('id')->firstOrFail();
        $sale->status = 'paid';
        $sale->save();
        $this->assertSame('sold', $seat->fresh()->status, 'paying does not disturb the seat');

        $sale->status = 'refunded';
        $sale->save();

        $this->assertSame('available', $seat->fresh()->status);
    }

    public function test_a_seat_sold_to_someone_else_cannot_be_held_again(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role));
        $map = $this->maps()->materialize($event);
        $stalls = $event->tickets->firstWhere('type', 'Stalls');
        $seat = $this->seatsIn($map, 'Stalls')->first();

        $this->hold($role, $event, [$seat->id])->assertOk();
        $this->checkout($role, $event, [UrlUtils::encodeId($stalls->id) => 1])->assertRedirect();

        // A different visitor, so a different session token.
        $this->flushSession();
        $this->hold($role, $event, [$seat->id])->assertStatus(409);
        $this->assertSame('sold', $seat->fresh()->status);
    }

    public function test_the_seat_reference_reaches_the_ticket_page(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role));
        $map = $this->maps()->materialize($event);
        $stalls = $event->tickets->firstWhere('type', 'Stalls');
        $seats = $this->seatsIn($map, 'Stalls')->take(2);

        $this->hold($role, $event, $seats->pluck('id')->all())->assertOk();
        $this->checkout($role, $event, [UrlUtils::encodeId($stalls->id) => 2])->assertRedirect();

        $sale = Sale::latest('id')->firstOrFail();
        $saleTicket = $sale->saleTickets()->firstOrFail();

        $this->assertSame(['Stalls, Row A, Seat 1', 'Stalls, Row A, Seat 2'], $saleTicket->seatLabels());

        $html = $this->get(route('ticket.view', [
            'event_id' => UrlUtils::encodeId($event->id), 'secret' => $sale->secret,
        ]))->assertOk()->getContent();

        // Front of house should never have to look a seat up.
        $this->assertStringContainsString('Row A, Seat 1', $html);
        $this->assertStringContainsString('Row A, Seat 2', $html);
    }

    public function test_the_sweep_clears_lapsed_cart_holds(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role));
        $map = $this->maps()->materialize($event);
        $seats = $this->seatsIn($map, 'Stalls');

        $this->holds()->acquire($map, [$seats[0]->id, $seats[1]->id], 'tok-a');
        SeatingSeat::where('id', $seats[0]->id)->update(['hold_expires_at' => now()->subMinute()]);

        $this->assertSame(1, $this->holds()->sweepExpiredHolds());

        $this->assertSame('available', $seats[0]->fresh()->status);
        $this->assertSame('held', $seats[1]->fresh()->status, 'a live hold is left alone');
    }

    public function test_an_unallocated_event_checks_out_exactly_as_before(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $request = Request::create('/', 'POST', [
            'name' => 'Plain Show',
            'starts_at' => now()->addMonths(6)->setTime(19, 30)->format('Y-m-d H:i:s'),
            'tickets_enabled' => 1, 'payment_method' => 'cash', 'ticket_currency_code' => 'USD',
            'creator_role_id' => $role->id,
            'tickets' => [['type' => 'General', 'price' => 10, 'quantity' => 40]],
        ]);
        $request->setUserResolver(fn () => $role->user);
        $event = app(EventRepo::class)->saveEvent($role, $request)->fresh();
        $event->roles()->updateExistingPivot($role->id, ['is_accepted' => true]);
        $event = $event->fresh();

        $ticket = $event->tickets->first();
        $this->checkout($role, $event, [UrlUtils::encodeId($ticket->id) => 3])->assertRedirect();

        $sale = Sale::latest('id')->firstOrFail();
        $this->assertSame(3, (int) $sale->saleTickets()->first()->quantity);
        $this->assertSame(0, SeatingSeat::whereNotNull('sale_id')->count());
    }
}
