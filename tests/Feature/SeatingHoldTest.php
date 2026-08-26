<?php

namespace Tests\Feature;

use App\Exceptions\BusinessException;
use App\Models\Event;
use App\Models\Role;
use App\Models\SeatingLevel;
use App\Models\SeatingPlan;
use App\Models\SeatingSeat;
use App\Models\SeatingSection;
use App\Models\Ticket;
use App\Repos\EventRepo;
use App\Services\BestAvailableService;
use App\Services\SeatHoldService;
use App\Services\SeatingMapService;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Cart holds and best-available.
 *
 * The hold is stage one of the two-stage claim: seats are held while the guest chooses, then flip
 * to sold at Sale creation BEFORE Stripe. Expiry is evaluated at read time, so a lapsed hold is
 * sellable the moment it lapses without any sweeper having run.
 */
class SeatingHoldTest extends TestCase
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

    /**
     * Stalls: rows A and B, 8 seats each, gangway after seat 4. Circle: row A, 4 seats.
     * Section order puts Stalls first, so best-available should reach for it.
     */
    private function makePlan(Role $role): SeatingPlan
    {
        $plan = SeatingPlan::create(['role_id' => $role->id, 'name' => 'Main House']);
        $level = SeatingLevel::create(['seating_plan_id' => $plan->id, 'name' => 'Ground']);

        $stalls = SeatingSection::create([
            'seating_plan_id' => $plan->id, 'seating_level_id' => $level->id,
            'name' => 'Stalls', 'band' => 'Stalls', 'kind' => 'seated', 'position' => 0,
        ]);
        foreach ([['A', 1], ['B', 2]] as [$row, $rp]) {
            for ($n = 1; $n <= 8; $n++) {
                SeatingSeat::create([
                    'seating_plan_id' => $plan->id, 'seating_section_id' => $stalls->id,
                    'row_label' => $row, 'row_position' => $rp, 'seat_label' => (string) $n,
                    'position' => $n, 'aisle_after' => $n === 4,
                ]);
            }
        }

        $circle = SeatingSection::create([
            'seating_plan_id' => $plan->id, 'seating_level_id' => $level->id,
            'name' => 'Circle', 'band' => 'Circle', 'kind' => 'seated', 'position' => 1,
        ]);
        for ($n = 1; $n <= 4; $n++) {
            SeatingSeat::create([
                'seating_plan_id' => $plan->id, 'seating_section_id' => $circle->id,
                'row_label' => 'A', 'row_position' => 1, 'seat_label' => (string) $n, 'position' => $n,
            ]);
        }

        return $plan->fresh();
    }

    /**
     * Hold through the ROUTE, not the service, so the seats are held under the session's own token
     * - which is the one the state endpoint will read back on the next request.
     */
    private function holdVia(Event $event, Role $role, array $seatIds)
    {
        return $this->postJson(route('seating.hold', ['subdomain' => $role->subdomain]), [
            'event_id' => UrlUtils::encodeId($event->id),
            'date' => $event->saleEventDateFromStartsAt(),
            'seat_ids' => $seatIds,
        ]);
    }

    private function stateUrl(Event $event, Role $role): string
    {
        return route('seating.state', [
            'subdomain' => $role->subdomain,
            'event_id' => UrlUtils::encodeId($event->id),
            'date' => $event->saleEventDateFromStartsAt(),
        ]);
    }

    private function seatedEvent(Role $role, SeatingPlan $plan): Event
    {
        $request = Request::create('/', 'POST', [
            'name' => 'Test Event',
            'starts_at' => now()->addMonths(6)->setTime(12, 0)->format('Y-m-d H:i:s'),
            'tickets_enabled' => 1, 'payment_method' => 'stripe', 'ticket_currency_code' => 'USD',
            'creator_role_id' => $role->id,
            'seating_plan_id' => $plan->id,
            'tickets' => [
                ['type' => 'Stalls', 'price' => 40, 'quantity' => 999, 'seating_band' => 'Stalls'],
                ['type' => 'Circle', 'price' => 25, 'quantity' => 999, 'seating_band' => 'Circle'],
            ],
        ]);
        $request->setUserResolver(fn () => $role->user);

        $event = app(EventRepo::class)->saveEvent($role, $request)->fresh();

        // saveEvent leaves the schedule pivot pending, and is_accepted is the universal visibility
        // gate - without this the guest page redirects instead of rendering.
        $event->roles()->updateExistingPivot($role->id, ['is_accepted' => true]);

        return $event->fresh();
    }

    private function seatsIn($map, string $section, string $row)
    {
        return SeatingSeat::where('event_seating_map_id', $map->id)
            ->where('seating_section_id', $map->sections()->where('name', $section)->value('id'))
            ->where('row_label', $row)->orderBy('position')->get();
    }

    // ---------------------------------------------------------------- holds

    /**
     * The premise the venue-wide picker rests on.
     *
     * acquire() takes a flat seat-id array with no band scoping, so ONE hold can span two price
     * bands. Nothing exercised that before, because the guest client mounts one picker per band and
     * each posts only its own seats - which is why picking in a second band silently releases the
     * first. The server was always ready; pin it so the client can rely on it.
     */
    /**
     * A wheelchair seat drawn in an ORDINARY section is bookable by nobody - AccessibleSeatingRule
     * says so deliberately. Best available did not know that, so on a plan like this it picked the
     * unbookable seat and acquire() refused it in the same request: the guest got
     * "Stalls, Row A, Seat 9 is a wheelchair space and is not available for general booking."
     * every single time, because the ordering is deterministic.
     */
    private function planWithMisplacedWheelchair(Role $role): SeatingPlan
    {
        $plan = SeatingPlan::create(['role_id' => $role->id, 'name' => 'Main House']);
        $level = SeatingLevel::create(['seating_plan_id' => $plan->id, 'name' => 'Ground']);

        // Not flagged accessibility_only - that is the whole point.
        $stalls = SeatingSection::create([
            'seating_plan_id' => $plan->id, 'seating_level_id' => $level->id,
            'name' => 'Stalls', 'band' => 'Stalls', 'kind' => 'seated', 'position' => 0,
            'accessibility_only' => false,
        ]);

        // The user's row A: an aisle after 6 and 12, wheelchair spaces dead centre at 9 and 10,
        // which is exactly where "closest to the centre of its row" sends best available.
        for ($n = 1; $n <= 18; $n++) {
            SeatingSeat::create([
                'seating_plan_id' => $plan->id, 'seating_section_id' => $stalls->id,
                'row_label' => 'A', 'row_position' => 1, 'seat_label' => (string) $n,
                'position' => $n, 'aisle_after' => in_array($n, [6, 12], true),
                'kind' => in_array($n, [9, 10], true) ? 'wheelchair' : 'standard',
            ]);
        }

        return $plan->fresh();
    }

    public function test_best_available_never_offers_a_wheelchair_seat_it_cannot_sell(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $plan = $this->planWithMisplacedWheelchair($role);

        $request = Request::create('/', 'POST', [
            'name' => 'Seated Show',
            'starts_at' => now()->addMonths(6)->setTime(19, 30)->format('Y-m-d H:i:s'),
            'tickets_enabled' => 1, 'payment_method' => 'stripe', 'ticket_currency_code' => 'USD',
            'creator_role_id' => $role->id, 'seating_plan_id' => $plan->id,
            'tickets' => [['type' => 'Stalls', 'price' => 40, 'quantity' => 999, 'seating_band' => 'Stalls']],
        ]);
        $request->setUserResolver(fn () => $role->user);
        $event = app(EventRepo::class)->saveEvent($role, $request)->fresh();
        $event->roles()->updateExistingPivot($role->id, ['is_accepted' => true]);
        $event = $event->fresh();

        $map = $this->maps()->materialize($event);
        $ticket = $event->tickets->firstWhere('type', 'Stalls');
        $ticket->setRelation('event', $event);

        $picked = app(BestAvailableService::class)->pick($map, $ticket, 2);
        $this->assertCount(2, $picked, 'there are plenty of ordinary seats to offer');

        $kinds = SeatingSeat::whereIn('id', $picked)->pluck('kind')->unique()->all();
        $this->assertSame(['standard'], $kinds, 'best available offered a seat the rules refuse');

        // And the whole round trip works, which is what the guest actually experiences.
        $this->holds()->acquire($map, $picked, 'tok-best');
        $this->assertSame(2, SeatingSeat::where('hold_token', 'tok-best')->where('status', 'held')->count());
    }

    /**
     * The other side of the same fix, and the more dangerous one to get wrong: a wheelchair space
     * in a section that IS flagged accessibility_only must still be offered, or the fix above would
     * make accessible seating unbuyable by best available - worse than the bug it closes.
     */
    public function test_best_available_still_offers_accessible_seats_from_an_accessible_section(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $plan = SeatingPlan::create(['role_id' => $role->id, 'name' => 'Main House']);
        $level = SeatingLevel::create(['seating_plan_id' => $plan->id, 'name' => 'Ground']);

        $accessible = SeatingSection::create([
            'seating_plan_id' => $plan->id, 'seating_level_id' => $level->id,
            'name' => 'Accessible', 'band' => 'Accessible', 'kind' => 'seated', 'position' => 0,
            'accessibility_only' => true,
        ]);
        // Four, and two are taken below: picking one out of two would strand the other and the
        // ORPHAN rule would throw first, so the test would pass or fail for the wrong reason.
        foreach ([1, 2, 3, 4] as $n) {
            SeatingSeat::create([
                'seating_plan_id' => $plan->id, 'seating_section_id' => $accessible->id,
                'row_label' => 'A', 'row_position' => 1, 'seat_label' => (string) $n,
                'position' => $n, 'kind' => 'wheelchair',
            ]);
        }

        $request = Request::create('/', 'POST', [
            'name' => 'Seated Show',
            'starts_at' => now()->addMonths(6)->setTime(19, 30)->format('Y-m-d H:i:s'),
            'tickets_enabled' => 1, 'payment_method' => 'stripe', 'ticket_currency_code' => 'USD',
            'creator_role_id' => $role->id, 'seating_plan_id' => $plan->id,
            'tickets' => [['type' => 'Accessible', 'price' => 20, 'quantity' => 999, 'seating_band' => 'Accessible']],
        ]);
        $request->setUserResolver(fn () => $role->user);
        $event = app(EventRepo::class)->saveEvent($role, $request)->fresh();
        $event->roles()->updateExistingPivot($role->id, ['is_accepted' => true]);
        $event = $event->fresh();

        $map = $this->maps()->materialize($event);

        // Orphan rule off: best available aims at the centre of the row, so on a four-seat row it
        // picks 2 and 3 and strands 1 and 4 singly - a real and separate shortcoming, and one that
        // would make this test pass or fail for a reason unrelated to accessible seating.
        $map->update(['orphan_rule_enabled' => false]);
        $map = $map->fresh();

        $ticket = $event->tickets->firstWhere('type', 'Accessible');
        $ticket->setRelation('event', $event);

        $picked = app(BestAvailableService::class)->pick($map, $ticket, 2);
        $this->assertCount(2, $picked, 'an accessible band must still be buyable by best available');
        $this->assertSame(
            ['wheelchair'],
            SeatingSeat::whereIn('id', $picked)->pluck('kind')->unique()->all(),
            'and the seats it offers are the accessible ones'
        );

        $this->holds()->acquire($map, $picked, 'tok-acc');
        $this->assertSame(2, SeatingSeat::where('hold_token', 'tok-acc')->where('status', 'held')->count());
    }

    /**
     * The guest payload must say a rule-blocked seat is unavailable, or the picker draws it as an
     * ordinary seat, the buyer clicks it, and the hold is refused - which is what "I can't select
     * two seats" turned out to be.
     */
    public function test_the_guest_payload_marks_an_unsellable_wheelchair_seat_unavailable(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $plan = $this->planWithMisplacedWheelchair($role);

        $request = Request::create('/', 'POST', [
            'name' => 'Seated Show',
            'starts_at' => now()->addMonths(6)->setTime(19, 30)->format('Y-m-d H:i:s'),
            'tickets_enabled' => 1, 'payment_method' => 'stripe', 'ticket_currency_code' => 'USD',
            'creator_role_id' => $role->id, 'seating_plan_id' => $plan->id,
            'tickets' => [['type' => 'Stalls', 'price' => 40, 'quantity' => 999, 'seating_band' => 'Stalls']],
        ]);
        $request->setUserResolver(fn () => $role->user);
        $event = app(EventRepo::class)->saveEvent($role, $request)->fresh();
        $event->roles()->updateExistingPivot($role->id, ['is_accepted' => true]);
        $event = $event->fresh();

        $this->maps()->materialize($event);

        $payload = $this->getJson(route('seating.state', [
            'subdomain' => $role->subdomain,
            'event_id' => UrlUtils::encodeId($event->id),
            'date' => $event->saleEventDateFromStartsAt(),
        ]))->assertOk()->json();

        $seats = collect($payload['levels'])->flatMap(fn ($l) => collect($l['sections'])->flatMap(fn ($s) => $s['seats']));
        $byLabel = $seats->keyBy('seat');

        $this->assertSame('unavailable', $byLabel['9']['state'], 'the wheelchair space must not look bookable');
        $this->assertSame('unavailable', $byLabel['10']['state']);
        $this->assertSame('available', $byLabel['8']['state'], 'ordinary seats beside it are unaffected');
        $this->assertSame('available', $byLabel['11']['state']);
    }

    /**
     * A selection is built one click at a time, so judging every intermediate state makes some
     * valid final selections unreachable: to take a whole row of eight you must pass through
     * seven, which strands the eighth. The buyer who reported this was stuck exactly there.
     */
    public function test_a_selection_that_would_strand_a_seat_is_warned_about_not_refused(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role));
        $map = $this->maps()->materialize($event);
        $row = $this->seatsIn($map, 'Stalls', 'A');

        // Seven of eight: the eighth is left on its own.
        $ids = $row->take(7)->pluck('id')->all();
        $result = $this->holds()->acquire($map, $ids, 'tok-warn', null, false, true);

        $this->assertSame($ids, $result['seat_ids'], 'the seats must still be held');
        $this->assertNotNull($result['warning'], 'the buyer should be told, not blocked');
        $this->assertSame(7, SeatingSeat::where('hold_token', 'tok-warn')->where('status', 'held')->count());

        // And the eighth click, which was previously unreachable, completes the row cleanly.
        $all = $row->pluck('id')->all();
        $done = $this->holds()->acquire($map, $all, 'tok-warn', null, false, true);

        $this->assertNull($done['warning'], 'a full row strands nobody');
        $this->assertSame(8, SeatingSeat::where('hold_token', 'tok-warn')->where('status', 'held')->count());
    }

    /**
     * The reported bug: "I can click and then unclick a seat to clear the error".
     *
     * acquire() validates BEFORE it releases the token's own holds, so on every call after the
     * first the buyer's existing seats are still `held` and read as taken. Both orphan passes then
     * see the same room and the delta is always zero. Here the eighth seat is still held while the
     * rule is asked about a selection that has just dropped it, so it counts as taken in the
     * "after" pass and its stranding goes unnoticed.
     */
    public function test_deselecting_a_seat_does_not_clear_a_warning_that_is_still_true(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role));
        $map = $this->maps()->materialize($event);
        $row = $this->seatsIn($map, 'Stalls', 'A');

        // The whole row, which strands nobody.
        $all = $row->pluck('id')->all();
        $this->assertNull($this->holds()->acquire($map, $all, 'tok-back', null, false, true)['warning']);

        // Now unclick the eighth. Seat 8 is free again and on its own.
        $seven = $row->take(7)->pluck('id')->all();
        $result = $this->holds()->acquire($map, $seven, 'tok-back', null, false, true);

        $this->assertNotNull($result['warning'], 'dropping the eighth seat strands it, and the buyer must still be told');
        $this->assertSame(7, SeatingSeat::where('hold_token', 'tok-back')->where('status', 'held')->count());
    }

    /** The other half of the report: "or I can choose another seat elsewhere". */
    public function test_picking_a_seat_in_another_row_does_not_clear_the_warning(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role));
        $map = $this->maps()->materialize($event);
        $row = $this->seatsIn($map, 'Stalls', 'A');

        $seven = $row->take(7)->pluck('id')->all();
        $this->assertNotNull($this->holds()->acquire($map, $seven, 'tok-else', null, false, true)['warning']);

        // A seat in the Circle. It strands nothing of its own - four seats, one taken, three left -
        // so any warning here can only be the Stalls row still being wrong.
        $elsewhere = array_merge($seven, [$this->seatsIn($map, 'Circle', 'A')->first()->id]);
        $result = $this->holds()->acquire($map, $elsewhere, 'tok-else', null, false, true);

        $this->assertNotNull($result['warning'], 'the Stalls row still strands its eighth seat');
        $this->assertSame(8, SeatingSeat::where('hold_token', 'tok-else')->where('status', 'held')->count());
    }

    /**
     * The second report: "if I refresh the page I still have the seats but no invalid error".
     *
     * The warning used to exist only as a field on a hold RESPONSE, so a reload restored the
     * buyer's seats and silently dropped the reason they could not check out with them.
     */
    public function test_the_state_endpoint_carries_the_advisory_for_seats_already_held(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role));
        $map = $this->maps()->materialize($event);
        $row = $this->seatsIn($map, 'Stalls', 'A');

        $seven = $row->take(7)->pluck('id')->all();
        $this->holdVia($event, $role, $seven)->assertOk();

        // Exactly what a reload does.
        $state = $this->getJson($this->stateUrl($event, $role))->assertOk()->json();

        $this->assertNotNull($state['warning'] ?? null, 'a reload must not lose the reason');
        $this->assertSame([$row[7]->id], $state['warning']['seat_ids'], 'it must name the stranded seat');
        $this->assertStringContainsString($row[7]->seat_label, $state['warning']['label']);

        // Filling the row clears it, so the notice cannot get stuck on.
        $this->holdVia($event, $role, $row->pluck('id')->all())->assertOk();
        $this->assertNull($this->getJson($this->stateUrl($event, $role))->json('warning'));
    }

    /**
     * The guarantee that makes the notice worth showing at all: it agrees with checkout.
     *
     * A warning that says "fine" while claimForSale() says "no" would be worse than no warning -
     * the buyer would only find out after filling in their details.
     */
    public function test_the_advisory_and_the_checkout_refusal_never_disagree(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role));
        $map = $this->maps()->materialize($event);
        $row = $this->seatsIn($map, 'Stalls', 'A');
        $rule = app(\App\Services\OrphanSeatRule::class);

        foreach ([5, 6, 7, 8] as $take) {
            $ids = $row->take($take)->pluck('id')->all();

            $advisory = $rule->advisoryFor($map, $ids);

            $refused = false;
            try {
                $rule->validateFinal($map, $ids);
            } catch (BusinessException $e) {
                $refused = true;
            }

            $this->assertSame($refused, $advisory !== null, "the two disagreed on a selection of {$take}");
        }
    }

    /** A hold that has lapsed is already free, so there is nothing to warn about. */
    public function test_a_lapsed_hold_produces_no_advisory(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role));
        $map = $this->maps()->materialize($event);
        $row = $this->seatsIn($map, 'Stalls', 'A');

        $this->holdVia($event, $role, $row->take(7)->pluck('id')->all())->assertOk();
        $this->assertNotNull($this->getJson($this->stateUrl($event, $role))->json('warning'));

        SeatingSeat::where('event_seating_map_id', $map->id)->update(['hold_expires_at' => now()->subMinute()]);

        $this->assertNull(
            $this->getJson($this->stateUrl($event, $role))->json('warning'),
            'seats the buyer no longer holds must not be held against them',
        );
    }

    /** Advisory is opt-in. Every other caller still gets a hard refusal. */
    public function test_the_orphan_rule_still_refuses_when_not_advisory(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role));
        $map = $this->maps()->materialize($event);
        $ids = $this->seatsIn($map, 'Stalls', 'A')->take(7)->pluck('id')->all();

        $this->expectException(BusinessException::class);
        $this->holds()->acquire($map, $ids, 'tok-hard');
    }

    public function test_one_hold_can_span_two_price_bands(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role));
        $map = $this->maps()->materialize($event);

        $stalls = $this->seatsIn($map, 'Stalls', 'A');
        $circle = $this->seatsIn($map, 'Circle', 'A');

        // Taken from the end of each row so neither pick strands a single seat.
        $ids = [$stalls[6]->id, $stalls[7]->id, $circle[2]->id, $circle[3]->id];
        $this->holds()->acquire($map, $ids, 'tok-mixed');

        $held = SeatingSeat::where('hold_token', 'tok-mixed')->where('status', 'held')->pluck('id')->all();
        sort($held);
        sort($ids);
        $this->assertSame($ids, $held, 'a single hold must keep seats from both bands');

        // And the two bands really are different tickets, or this proves nothing.
        $tickets = SeatingSection::whereIn('id', SeatingSeat::whereIn('id', $ids)->pluck('seating_section_id'))
            ->pluck('ticket_id')->unique()->filter()->values();
        $this->assertCount(2, $tickets, 'the fixture must span two ticket types');
    }

    public function test_a_hold_replaces_the_previous_selection(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role));
        $map = $this->maps()->materialize($event);

        // This test is about REPLACE-not-add. Moving a selection from seats 1-2 to 2-3 strands
        // seat 1, which the orphan rule (correctly) refuses - so switch it off here rather than
        // contorting the selection and losing what is being tested.
        $map->update(['orphan_rule_enabled' => false]);
        $map = $map->fresh();

        $seats = $this->seatsIn($map, 'Stalls', 'A');

        $this->holds()->acquire($map, [$seats[0]->id, $seats[1]->id], 'tok-a');
        $this->assertSame(2, SeatingSeat::where('hold_token', 'tok-a')->where('status', 'held')->count());

        // The guest deselects one and picks a different one. Additive holds would leak the first.
        $this->holds()->acquire($map, [$seats[1]->id, $seats[2]->id], 'tok-a');

        $this->assertSame('available', $seats[0]->fresh()->status);
        $this->assertSame('held', $seats[1]->fresh()->status);
        $this->assertSame('held', $seats[2]->fresh()->status);
        $this->assertSame(2, SeatingSeat::where('hold_token', 'tok-a')->where('status', 'held')->count());
    }

    public function test_a_second_cart_cannot_take_a_held_seat_and_is_told_which(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role));
        $map = $this->maps()->materialize($event);
        $seats = $this->seatsIn($map, 'Stalls', 'A');

        $this->holds()->acquire($map, [$seats[0]->id], 'tok-a');

        try {
            $this->holds()->acquire($map, [$seats[0]->id, $seats[1]->id], 'tok-b');
            $this->fail('the second cart should have been refused');
        } catch (BusinessException $e) {
            // Named, so the picker can drop that one seat and keep the rest of the selection.
            $this->assertStringContainsString('Row A', $e->getMessage());
        }

        // And nothing was written for the loser.
        $this->assertSame('tok-a', $seats[0]->fresh()->hold_token);
        $this->assertSame('available', $seats[1]->fresh()->status);
    }

    public function test_a_lapsed_hold_is_takeable_with_no_sweeper_having_run(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role));
        $map = $this->maps()->materialize($event);
        $seat = $this->seatsIn($map, 'Stalls', 'A')->first();

        $this->holds()->acquire($map, [$seat->id], 'tok-a');
        $seat->fresh()->update(['hold_expires_at' => now()->subMinute()]);

        $this->holds()->acquire($map, [$seat->id], 'tok-b');
        $this->assertSame('tok-b', $seat->fresh()->hold_token);
    }

    public function test_release_frees_everything_the_token_held(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role));
        $map = $this->maps()->materialize($event);
        $seats = $this->seatsIn($map, 'Stalls', 'A');

        $this->holds()->acquire($map, [$seats[0]->id, $seats[1]->id], 'tok-a');
        $this->holds()->release($map, 'tok-a');

        $this->assertSame(0, SeatingSeat::where('hold_token', 'tok-a')->count());
        $this->assertSame('available', $seats[0]->fresh()->status);
    }

    public function test_extend_will_not_revive_a_lapsed_hold(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role));
        $map = $this->maps()->materialize($event);
        $seat = $this->seatsIn($map, 'Stalls', 'A')->first();

        $this->holds()->acquire($map, [$seat->id], 'tok-a');
        $this->assertNotNull($this->holds()->extend($map, 'tok-a'));

        $seat->fresh()->update(['hold_expires_at' => now()->subMinute()]);

        // Reviving it would take back a seat somebody else may already have.
        $this->assertNull($this->holds()->extend($map, 'tok-a'));
        $this->assertTrue($seat->fresh()->isAvailable());
    }

    // ---------------------------------------------------------------- best available

    public function test_best_available_picks_a_contiguous_block_in_the_first_section(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role));
        $map = $this->maps()->materialize($event);
        $stalls = $event->tickets->firstWhere('type', 'Stalls');

        $picked = app(BestAvailableService::class)->pick($map, $stalls, 3);
        $seats = SeatingSeat::whereIn('id', $picked)->orderBy('position')->get();

        $this->assertCount(3, $picked);
        $this->assertSame(['Stalls', 'Stalls', 'Stalls'], $seats->map(fn ($s) => $s->section->name)->all());
        $this->assertSame(['A', 'A', 'A'], $seats->pluck('row_label')->all(), 'the first row wins');
        // Consecutive, and never straddling the gangway after seat 4.
        $positions = $seats->pluck('position')->all();
        $this->assertSame(range($positions[0], $positions[0] + 2), $positions);
        $this->assertNotContains(4, array_slice($positions, 0, 2), 'a block must not cross the aisle');
    }

    public function test_best_available_will_not_straddle_a_gangway(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role));
        $map = $this->maps()->materialize($event);
        $stalls = $event->tickets->firstWhere('type', 'Stalls');

        // Seats 1-6 free in row A (7 and 8 sold), nothing in row B. The gangway is after seat 4.
        //
        // Chosen so the two behaviours DISAGREE: scored purely on distance from the centre of what
        // is free, seats 2-5 is the most central four - and it straddles the gangway. Respecting
        // the aisle rejects it and 3-6 with it, leaving 1-4, which ends AT the gangway rather than
        // crossing it. An earlier version of this test used a layout where both rules happened to
        // land on the same seats, so it passed with the aisle check deleted.
        $a = $this->seatsIn($map, 'Stalls', 'A');
        SeatingSeat::whereIn('id', $a->whereIn('position', [7, 8])->pluck('id'))->update(['status' => 'sold']);
        SeatingSeat::whereIn('id', $this->seatsIn($map, 'Stalls', 'B')->pluck('id'))->update(['status' => 'sold']);

        $picked = app(BestAvailableService::class)->pick($map, $stalls, 4);
        $seats = SeatingSeat::whereIn('id', $picked)->orderBy('position')->get();

        $this->assertCount(4, $picked, 'it still fills the order');
        $this->assertSame([1, 2, 3, 4], $seats->pluck('position')->all(),
            'a party must not be split across a gangway just because the block looks more central');
        $this->assertTrue($a->firstWhere('position', 4)->aisle_after, 'fixture sanity: the gangway is after seat 4');
    }

    public function test_best_available_returns_what_it_can_when_the_band_is_nearly_full(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role));
        $map = $this->maps()->materialize($event);
        $circle = $event->tickets->firstWhere('type', 'Circle');

        SeatingSeat::whereIn('id', $this->seatsIn($map, 'Circle', 'A')->slice(0, 3)->pluck('id'))
            ->update(['status' => 'sold']);

        $this->assertCount(1, app(BestAvailableService::class)->pick($map, $circle, 4));
        $this->assertSame([], app(BestAvailableService::class)->pick($map, $circle, 0));
    }

    // ---------------------------------------------------------------- the guest endpoint

    public function test_the_guest_payload_never_leaks_who_holds_a_seat(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role));
        $map = $this->maps()->materialize($event);
        $seats = $this->seatsIn($map, 'Stalls', 'A');

        $seats[0]->update([
            'status' => 'held', 'hold_kind' => 'house', 'hold_note' => 'Reserved for the producer',
        ]);
        $seats[1]->update(['status' => 'sold']);

        $body = $this->getJson(route('seating.state', [
            'subdomain' => $role->subdomain,
            'event_id' => UrlUtils::encodeId($event->id),
            'date' => $event->saleEventDateFromStartsAt(),
        ]))->assertOk()->getContent();

        $this->assertStringNotContainsString('Reserved for the producer', $body);
        $this->assertStringNotContainsString('hold_note', $body);
        $this->assertStringNotContainsString('hold_kind', $body);
        $this->assertStringNotContainsString('hold_token', $body);

        $states = collect(json_decode($body, true)['levels'][0]['sections'])
            ->firstWhere('name', 'Stalls')['seats'];
        $byId = collect($states)->keyBy('id');
        $this->assertSame('taken', $byId[$seats[0]->id]['state'], 'a house block reads as simply taken');
        $this->assertSame('taken', $byId[$seats[1]->id]['state']);
        $this->assertSame('available', $byId[$seats[2]->id]['state']);
    }

    public function test_holding_through_the_endpoint_marks_the_seats_mine(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role));
        $map = $this->maps()->materialize($event);
        $seats = $this->seatsIn($map, 'Stalls', 'A');

        $args = ['subdomain' => $role->subdomain];
        $payload = [
            'event_id' => UrlUtils::encodeId($event->id),
            'date' => $event->saleEventDateFromStartsAt(),
            'seat_ids' => [$seats[0]->id, $seats[1]->id],
        ];

        $this->postJson(route('seating.hold', $args), $payload)
            ->assertOk()
            ->assertJsonStructure(['held', 'expires_at', 'version']);

        // Same session, so the token matches and the seats read back as mine.
        $state = $this->getJson(route('seating.state', $args + [
            'event_id' => $payload['event_id'], 'date' => $payload['date'],
        ]))->json();

        $byId = collect(collect($state['levels'][0]['sections'])->firstWhere('name', 'Stalls')['seats'])->keyBy('id');
        $this->assertSame('mine', $byId[$seats[0]->id]['state']);
        $this->assertSame('mine', $byId[$seats[1]->id]['state']);
    }

    /**
     * The guest page has to actually RENDER the picker, not merely serve its endpoints.
     *
     * Every other test here talks to JSON endpoints, so none of them compiles the ticket form -
     * and this exact form already died twice during the build on Blade parse errors that only
     * unrelated tests happened to catch.
     */
    public function test_the_guest_page_renders_the_picker_for_an_allocated_ticket(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role));

        $html = $this->get(route('event.view_guest', [
            'subdomain' => $role->subdomain, 'slug' => $event->slug,
        ]))->assertOk()->getContent();

        $this->assertStringContainsString('seating-picker-mount', $html, 'the mount point is missing');

        // ONE mount for the venue, not one per band. Two instances each posted only their own seats
        // while acquire() replaces the session's whole selection, so picking in a second band
        // silently released the first - and checkout then failed the books-balance guard.
        $this->assertSame(1, substr_count($html, 'seating-picker-mount'),
            'a second mount means a second instance, which is the two-band hold bug');
        $this->assertStringContainsString('pickerProps()', $html);
        $this->assertStringNotContainsString('pickerProps(ticket)', $html, 'the per-band signature is gone');
        $this->assertStringContainsString('seatingPicker:', $html, 'the shared props never reached the client');
        $this->assertStringContainsString('"is_allocated":true', $html);
        $this->assertStringContainsString('es-seats-changed', $html, 'the bridge back to the form is missing');

        // The strings resolved rather than shipping raw keys, and the endpoints are path-relative
        // so they stay same-origin on a custom domain.
        $this->assertStringContainsString(__('messages.seating_pick_your_seats'), $html);
        $this->assertStringNotContainsString('messages.seating_pick_your_seats', $html);
        $this->assertStringContainsString('\\/seating\\/hold', $html);

        // The quantity step is gone: choosing the seats IS choosing how many. Nothing should be
        // asking for a number, and best available is no longer offered to a guest.
        foreach (['seating_how_many', 'seating_choose_own', 'seating_best_available'] as $retired) {
            $this->assertStringNotContainsString($retired, $html, "{$retired} is still reaching the client");
        }
    }

    public function test_an_event_with_no_plan_renders_no_picker(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $request = Request::create('/', 'POST', [
            'name' => 'Plain Event',
            'starts_at' => now()->addMonths(6)->setTime(12, 0)->format('Y-m-d H:i:s'),
            'tickets_enabled' => 1, 'payment_method' => 'stripe', 'ticket_currency_code' => 'USD',
            'creator_role_id' => $role->id,
            'tickets' => [['type' => 'General', 'price' => 10, 'quantity' => 40]],
        ]);
        $request->setUserResolver(fn () => $role->user);
        $event = app(EventRepo::class)->saveEvent($role, $request)->fresh();
        $event->roles()->updateExistingPivot($role->id, ['is_accepted' => true]);

        $html = $this->get(route('event.view_guest', [
            'subdomain' => $role->subdomain, 'slug' => $event->slug,
        ]))->assertOk()->getContent();

        // The mount element's CLASS is part of the client-side template and is therefore always in
        // the source; what must differ is that no ticket claims to be allocated and the picker gets
        // no props at all.
        $this->assertStringContainsString('"is_allocated":false', $html);
        $this->assertStringNotContainsString('"is_allocated":true', $html);
        $this->assertStringContainsString('seatingPicker: null', $html);
        $this->assertStringNotContainsString('seating_choose_own', $html);
        $this->assertStringNotContainsString(__('messages.seating_choose_own'), $html);
    }

    // ------------------------------------------------ review finding 2

    public function test_state_will_not_materialize_a_map_for_an_event_that_cannot_sell(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role));

        // Ticketing switched off, so nobody can buy - and therefore nobody should be able to make
        // this date's snapshot exist. A crawler walking a recurring event's dates would otherwise
        // create a map per date through a plain GET.
        $event->tickets_enabled = false;
        $event->save();

        $this->assertNull($this->maps()->mapFor($event->fresh(), $event->saleEventDateFromStartsAt()));

        $this->getJson(route('seating.state', [
            'subdomain' => $role->subdomain,
            'event_id' => UrlUtils::encodeId($event->id),
            'date' => $event->saleEventDateFromStartsAt(),
        ]))->assertStatus(422);

        $this->assertSame(0, \App\Models\EventSeatingMap::where('event_id', $event->id)->count(),
            'a GET must not create rows');
    }

    public function test_state_still_materializes_for_a_sellable_date(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role));
        $date = $event->saleEventDateFromStartsAt();

        $this->assertNull($this->maps()->mapFor($event, $date), 'fixture sanity: nothing yet');

        // The lazy path is still the point - a real buyer opening the map brings it into existence.
        $this->getJson(route('seating.state', [
            'subdomain' => $role->subdomain,
            'event_id' => UrlUtils::encodeId($event->id),
            'date' => $date,
        ]))->assertOk()->assertJsonStructure(['version', 'levels']);

        $this->assertNotNull($this->maps()->mapFor($event->fresh(), $date));
    }

    public function test_the_release_endpoint_is_gone(): void
    {
        // acquire([]) already frees everything the token holds, so a separate unauthenticated
        // write route earned nothing. Holding zero seats is the release.
        $this->assertFalse(\Illuminate\Support\Facades\Route::has('seating.release'));

        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role));
        $map = $this->maps()->materialize($event);
        $seats = $this->seatsIn($map, 'Stalls', 'A');

        $args = ['subdomain' => $role->subdomain];
        $body = ['event_id' => UrlUtils::encodeId($event->id), 'date' => $event->saleEventDateFromStartsAt()];

        $this->postJson(route('seating.hold', $args), $body + ['seat_ids' => [$seats[0]->id]])->assertOk();
        $this->assertSame('held', $seats[0]->fresh()->status);

        $this->postJson(route('seating.hold', $args), $body + ['seat_ids' => []])->assertOk();
        $this->assertSame('available', $seats[0]->fresh()->status);
    }

    // ------------------------------------------------ review finding 6: the embed

    public function test_the_embed_renders_the_picker(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role));

        $html = $this->get(route('event.view_guest', [
            // ?tickets=true is what selects show-guest-ticket-embed; ?embed=true alone renders
            // the ordinary guest page in a frame.
            'subdomain' => $role->subdomain, 'slug' => $event->slug,
            'embed' => 'true', 'tickets' => 'true',
        ]))->assertOk()->getContent();

        $this->assertStringContainsString('seatingPicker:', $html);
        $this->assertStringContainsString('"is_allocated":true', $html);

        // The hold token lives in the session, which in a cross-site iframe depends on
        // SESSION_SAME_SITE. That is NOT a new constraint: event.checkout is already on
        // SecurityHeaders' embeddable list and posts a session CSRF token, so embedded checkout has
        // always had the same dependency. If one works in a deployment, so does the other.
        $this->assertStringContainsString('csrfToken', $html);
    }

    public function test_a_seat_id_from_another_event_is_ignored(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $plan = $this->makePlan($role);
        $mine = $this->seatedEvent($role, $plan);
        $other = $this->seatedEvent($role, $plan);

        $mineMap = $this->maps()->materialize($mine);
        $otherMap = $this->maps()->materialize($other);
        $foreign = SeatingSeat::where('event_seating_map_id', $otherMap->id)->first();

        $this->postJson(route('seating.hold', ['subdomain' => $role->subdomain]), [
            'event_id' => UrlUtils::encodeId($mine->id),
            'date' => $mine->saleEventDateFromStartsAt(),
            'seat_ids' => [$foreign->id],
        ])->assertOk()->assertJson(['held' => []]);

        $this->assertSame('available', $foreign->fresh()->status);
    }
}
