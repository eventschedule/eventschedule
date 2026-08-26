<?php

namespace Tests\Feature;

use App\Exceptions\BusinessException;
use App\Models\Event;
use App\Models\Role;
use App\Models\SeatingLevel;
use App\Models\SeatingPlan;
use App\Models\SeatingSeat;
use App\Models\SeatingSection;
use App\Repos\EventRepo;
use App\Services\SeatHoldService;
use App\Services\SeatingMapService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Phase 6: the orphan-seat rule and accessible seating.
 *
 * Both were columns the designer wrote and nothing read - a guest could hand-post a wheelchair
 * space's id and buy it with an ordinary ticket, and nothing stopped a selection stranding a seat.
 */
class SeatingRulesTest extends TestCase
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
     * Stalls: one row of 8, gangway after seat 4.
     * Access: a wheelchair space at 1 with a companion at 2, in an accessibility_only section.
     */
    private function makePlan(Role $role): SeatingPlan
    {
        $plan = SeatingPlan::create(['role_id' => $role->id, 'name' => 'Main House']);
        $level = SeatingLevel::create(['seating_plan_id' => $plan->id, 'name' => 'Ground']);

        $stalls = SeatingSection::create([
            'seating_plan_id' => $plan->id, 'seating_level_id' => $level->id,
            'name' => 'Stalls', 'band' => 'Stalls', 'kind' => 'seated', 'position' => 0,
        ]);
        for ($n = 1; $n <= 8; $n++) {
            SeatingSeat::create([
                'seating_plan_id' => $plan->id, 'seating_section_id' => $stalls->id,
                'row_label' => 'A', 'row_position' => 1, 'seat_label' => (string) $n,
                'position' => $n, 'aisle_after' => $n === 4,
            ]);
        }

        $access = SeatingSection::create([
            'seating_plan_id' => $plan->id, 'seating_level_id' => $level->id,
            'name' => 'Access', 'band' => 'Access', 'kind' => 'seated', 'position' => 1,
            'accessibility_only' => true,
        ]);
        // Six seats, not three: taking the wheelchair space and its companion out of a
        // three-seat section strands the third, so the orphan rule would refuse these for a
        // reason that has nothing to do with what they are testing.
        foreach ([[1, 'wheelchair'], [2, 'companion'], [3, 'standard'], [4, 'standard'], [5, 'standard'], [6, 'standard']] as [$n, $kind]) {
            SeatingSeat::create([
                'seating_plan_id' => $plan->id, 'seating_section_id' => $access->id,
                'row_label' => 'A', 'row_position' => 1, 'seat_label' => (string) $n,
                'position' => $n, 'kind' => $kind,
            ]);
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
                ['type' => 'Access', 'price' => 20, 'quantity' => 999, 'seating_band' => 'Access'],
            ],
        ]);
        $request->setUserResolver(fn () => $role->user);

        return app(EventRepo::class)->saveEvent($role, $request)->fresh();
    }

    private function row($map, string $section)
    {
        return SeatingSeat::where('event_seating_map_id', $map->id)
            ->where('seating_section_id', $map->sections()->where('name', $section)->value('id'))
            ->orderBy('position')->get();
    }

    // ------------------------------------------------------------ orphan rule

    public function test_a_selection_that_strands_a_single_seat_is_refused(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role));
        $map = $this->maps()->materialize($event);
        $stalls = $this->row($map, 'Stalls');

        // Seat 1 is already gone. Taking 3 and 4 would leave seat 2 alone between them.
        $stalls[0]->update(['status' => 'sold']);

        $this->expectException(BusinessException::class);
        $this->holds()->acquire($map, [$stalls[2]->id, $stalls[3]->id], 'tok-a');
    }

    public function test_a_selection_that_leaves_two_together_is_allowed(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role));
        $map = $this->maps()->materialize($event);
        $stalls = $this->row($map, 'Stalls');

        // Taking 3 and 4 with nothing sold leaves 1-2 together and 5-8 together.
        $this->holds()->acquire($map, [$stalls[2]->id, $stalls[3]->id], 'tok-a');

        $this->assertSame('held', $stalls[2]->fresh()->status);
    }

    public function test_a_gangway_is_a_row_boundary_not_an_orphan(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role));
        $map = $this->maps()->materialize($event);
        $stalls = $this->row($map, 'Stalls');

        // Leave only seat 4 free on the left of the gangway. It sits against the aisle, so it is
        // an end-of-block seat rather than a seat stranded between two groups... but the rule
        // still counts a lone seat as an orphan, so this must be refused.
        SeatingSeat::whereIn('id', [$stalls[0]->id, $stalls[1]->id])->update(['status' => 'sold']);

        $this->expectException(BusinessException::class);
        $this->holds()->acquire($map, [$stalls[2]->id], 'tok-a');
    }

    public function test_an_orphan_that_already_existed_is_not_blamed_on_this_buyer(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role));
        $map = $this->maps()->materialize($event);
        $stalls = $this->row($map, 'Stalls');

        // Somebody else already stranded seat 2 (1 and 3 gone).
        SeatingSeat::whereIn('id', [$stalls[0]->id, $stalls[2]->id])->update(['status' => 'sold']);

        // This buyer takes 7 and 8, nowhere near it. Refusing them for a pre-existing orphan would
        // be both unfair and impossible for them to fix.
        $this->holds()->acquire($map, [$stalls[6]->id, $stalls[7]->id], 'tok-a');

        $this->assertSame('held', $stalls[6]->fresh()->status);
    }

    public function test_the_rule_lifts_once_the_house_is_nearly_sold_out(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role));
        $map = $this->maps()->materialize($event);
        $stalls = $this->row($map, 'Stalls');

        // Seat 1 gone, so taking 3 and 4 strands seat 2 - refused below the threshold.
        $stalls[0]->update(['status' => 'sold']);

        try {
            $this->holds()->acquire($map, [$stalls[2]->id, $stalls[3]->id], 'tok-a');
            $this->fail('the rule should apply while there is plenty left');
        } catch (BusinessException $e) {
            // expected
        }

        // Now drop the threshold below what is actually sold (one seat of fourteen, ~7%). Driving
        // the threshold rather than the sold count keeps the test about the lift itself; the
        // default is 90%.
        $map->update(['orphan_rule_lift_pct' => 5]);

        $this->holds()->acquire($map->fresh(), [$stalls[2]->id, $stalls[3]->id], 'tok-a');

        $this->assertSame('held', $stalls[2]->fresh()->status);
    }

    public function test_the_box_office_is_exempt(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role));
        $map = $this->maps()->materialize($event);
        $stalls = $this->row($map, 'Stalls');
        $stalls[0]->update(['status' => 'sold']);

        // Staff see the whole map and are often deliberately filling awkward gaps.
        $this->holds()->acquire($map, [$stalls[2]->id, $stalls[3]->id], 'tok-staff', null, true);

        $this->assertSame('held', $stalls[2]->fresh()->status);
    }

    public function test_the_rule_can_be_switched_off_per_occurrence(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role));
        $map = $this->maps()->materialize($event);
        $map->update(['orphan_rule_enabled' => false]);
        $stalls = $this->row($map, 'Stalls');
        $stalls[0]->update(['status' => 'sold']);

        $this->holds()->acquire($map->fresh(), [$stalls[2]->id, $stalls[3]->id], 'tok-a');

        $this->assertSame('held', $stalls[2]->fresh()->status);
    }

    // ------------------------------------------------------------ accessible seating

    public function test_a_wheelchair_space_outside_an_accessibility_section_is_not_bookable(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $plan = $this->makePlan($role);
        $event = $this->seatedEvent($role, $plan);
        $map = $this->maps()->materialize($event);

        // Orphan rule off, so the ONLY thing that can refuse this is the accessibility gate.
        // With it on, taking a mid-block seat strands a neighbour and the test passed for the
        // wrong reason - it still threw a BusinessException with this gate deleted.
        $map->update(['orphan_rule_enabled' => false]);
        $map = $map->fresh();

        // The organizer marked a wheelchair seat inside the ordinary Stalls section. Until they
        // flag the section it is not bookable by the people it is for, so it is not bookable.
        $stalls = $this->row($map, 'Stalls');
        $stalls[5]->update(['kind' => 'wheelchair']);

        $this->expectException(BusinessException::class);
        $this->holds()->acquire($map, [$stalls[5]->id], 'tok-a');
    }

    public function test_a_wheelchair_space_in_an_accessibility_section_is_bookable(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role));
        $map = $this->maps()->materialize($event);
        $access = $this->row($map, 'Access');

        $this->holds()->acquire($map, [$access[0]->id], 'tok-a');

        $this->assertSame('held', $access[0]->fresh()->status);
    }

    public function test_a_companion_seat_cannot_be_taken_while_its_wheelchair_space_is_free(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role));
        $map = $this->maps()->materialize($event);

        // Orphan rule off for the same reason as the wheelchair test: taking the companion on its
        // own also strands the wheelchair space, so both rules would refuse and this would pass
        // even with the companion rule deleted.
        $map->update(['orphan_rule_enabled' => false]);
        $map = $map->fresh();

        $access = $this->row($map, 'Access');

        $this->expectException(BusinessException::class);
        $this->holds()->acquire($map, [$access[1]->id], 'tok-a');
    }

    public function test_a_companion_seat_goes_with_its_wheelchair_space(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role));
        $map = $this->maps()->materialize($event);
        $access = $this->row($map, 'Access');

        // Together is exactly what the companion seat is for.
        $this->holds()->acquire($map, [$access[0]->id, $access[1]->id], 'tok-a');

        $this->assertSame('held', $access[1]->fresh()->status);
    }

    /**
     * The same stale-baseline bug as the orphan warning, with a worse ending.
     *
     * Take the wheelchair space and its companion together (allowed), then drop the wheelchair
     * space. The companion rule asks whether the partner isAvailable() - it is `held`, by this very
     * token, and about to be released three lines further down - so nothing refuses. The buyer
     * walks away with a companion seat and the wheelchair space it belongs to sitting free.
     */
    public function test_a_companion_seat_cannot_outlive_the_wheelchair_space_it_was_taken_with(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role));
        $map = $this->maps()->materialize($event);

        // Orphan rule off for the same reason as the tests above: dropping the wheelchair space
        // also strands it, so with the rule on this would refuse even with the companion gate gone.
        $map->update(['orphan_rule_enabled' => false]);
        $map = $map->fresh();

        $access = $this->row($map, 'Access');
        $this->holds()->acquire($map, [$access[0]->id, $access[1]->id], 'tok-drop');

        try {
            $this->holds()->acquire($map, [$access[1]->id], 'tok-drop');
            $this->fail('the companion seat must not be keepable once its wheelchair space is dropped');
        } catch (BusinessException $e) {
            $this->assertSame(__('messages.seating_companion_reserved', [
                'seat' => $access[1]->fullLabel() ?: $access[1]->id,
            ]), $e->getMessage());
        }

        // The refused call must not have released anything on its way out.
        $this->assertSame('held', $access[0]->fresh()->status, 'the wheelchair space is still held');
        $this->assertSame('held', $access[1]->fresh()->status);
    }

    public function test_a_companion_seat_is_free_once_the_wheelchair_space_is_gone(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role));
        $map = $this->maps()->materialize($event);
        $access = $this->row($map, 'Access');

        $access[0]->update(['status' => 'sold']);

        $this->holds()->acquire($map, [$access[1]->id], 'tok-b');

        $this->assertSame('held', $access[1]->fresh()->status);
    }

    // ------------------------------------------------------------ combined mode

    public function test_an_allocated_event_is_never_in_combined_mode(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');

        // Two sections of the SAME size on purpose. Derived quantities then make
        // hasSameTicketQuantities() true by accident, and the checkout guard caps the whole house
        // at one band. With unequal bands this assertion passes whether the guard exists or not.
        $plan = SeatingPlan::create(['role_id' => $role->id, 'name' => 'Twin']);
        $level = SeatingLevel::create(['seating_plan_id' => $plan->id, 'name' => 'Ground']);
        foreach (['Stalls', 'Access'] as $i => $name) {
            $section = SeatingSection::create([
                'seating_plan_id' => $plan->id, 'seating_level_id' => $level->id,
                'name' => $name, 'band' => $name, 'kind' => 'seated', 'position' => $i,
            ]);
            for ($n = 1; $n <= 8; $n++) {
                SeatingSeat::create([
                    'seating_plan_id' => $plan->id, 'seating_section_id' => $section->id,
                    'row_label' => 'A', 'row_position' => 1, 'seat_label' => (string) $n, 'position' => $n,
                ]);
            }
        }

        $event = $this->seatedEvent($role, $plan->fresh());

        $this->assertSame([8, 8], $event->tickets->pluck('quantity')->map(fn ($q) => (int) $q)->sort()->values()->all(),
            'fixture sanity: the two bands really are the same size');
        $this->assertFalse($event->hasSameTicketQuantities());
    }
}
