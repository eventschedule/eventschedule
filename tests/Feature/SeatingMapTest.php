<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventSeatingMap;
use App\Models\Role;
use App\Models\SeatingLevel;
use App\Models\SeatingPlan;
use App\Models\SeatingSeat;
use App\Models\SeatingSection;
use App\Models\SeatingTable;
use App\Services\SeatingMapService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Allocated seating keeps a reusable TEMPLATE and, per occurrence, a SNAPSHOT copied from
 * it. Everything else in the feature rests on that split holding: an organizer editing the
 * house plan in March must not move somebody who bought seat C14 in February, and "edit
 * this date only" must be nothing more exotic than editing the copy.
 *
 * These pin the split, the owner invariant that keeps one row from being reachable as both,
 * and the availability predicate that decides whether a seat can be sold.
 */
class SeatingMapTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private function service(): SeatingMapService
    {
        return app(SeatingMapService::class);
    }

    /**
     * Stalls (2 rows x 3), Circle (1 row x 2), Standing (capacity 50, no seat rows) and a
     * cabaret table of 2 - ten seats in total, across every section kind.
     */
    private function makePlan(Role $role): SeatingPlan
    {
        $plan = SeatingPlan::create(['role_id' => $role->id, 'name' => 'Main House']);

        $ground = SeatingLevel::create([
            'seating_plan_id' => $plan->id,
            'name' => 'Ground',
            'position' => 0,
        ]);
        $upper = SeatingLevel::create([
            'seating_plan_id' => $plan->id,
            'name' => 'Balcony',
            'position' => 1,
        ]);

        $stalls = SeatingSection::create([
            'seating_plan_id' => $plan->id,
            'seating_level_id' => $ground->id,
            'name' => 'Stalls',
            'band' => 'Stalls',
            'kind' => 'seated',
            'color' => '#4E81FA',
        ]);
        foreach (['A', 'B'] as $row) {
            foreach ([1, 2, 3] as $n) {
                SeatingSeat::create([
                    'seating_plan_id' => $plan->id,
                    'seating_section_id' => $stalls->id,
                    'row_label' => $row,
                    'seat_label' => (string) $n,
                    'position' => $n,
                    // A gangway after seat 2 of every row, so the orphan rule has a boundary
                    // to respect later.
                    'aisle_after' => $n === 2,
                ]);
            }
        }

        $circle = SeatingSection::create([
            'seating_plan_id' => $plan->id,
            'seating_level_id' => $upper->id,
            'name' => 'Circle',
            'band' => 'Circle',
            'kind' => 'seated',
        ]);
        foreach ([1, 2] as $n) {
            SeatingSeat::create([
                'seating_plan_id' => $plan->id,
                'seating_section_id' => $circle->id,
                'row_label' => 'A',
                'seat_label' => (string) $n,
                'position' => $n,
                'kind' => $n === 1 ? 'wheelchair' : 'companion',
            ]);
        }

        SeatingSection::create([
            'seating_plan_id' => $plan->id,
            'seating_level_id' => $ground->id,
            'name' => 'Standing',
            'band' => 'Standing',
            'kind' => 'standing',
            'capacity' => 50,
        ]);

        $cabaret = SeatingSection::create([
            'seating_plan_id' => $plan->id,
            'seating_level_id' => $ground->id,
            'name' => 'Cabaret',
            'band' => 'Cabaret',
            'kind' => 'table',
        ]);
        $table = SeatingTable::create([
            'seating_section_id' => $cabaret->id,
            'label' => 'Table 1',
            'shape' => 'round',
            'seat_count' => 2,
            'booking_mode' => 'either',
        ]);
        foreach ([1, 2] as $n) {
            SeatingSeat::create([
                'seating_plan_id' => $plan->id,
                'seating_section_id' => $cabaret->id,
                'seating_table_id' => $table->id,
                // Deliberately unnumbered: "any seat at this table".
                'seat_label' => null,
                'position' => $n,
            ]);
        }

        return $plan->fresh();
    }

    private function seatedEvent(Role $role, SeatingPlan $plan): Event
    {
        return $this->createEvent($role, [
            'tickets_enabled' => true,
            'seating_plan_id' => $plan->id,
        ]);
    }

    public function test_materialize_copies_the_whole_template_structure(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $plan = $this->makePlan($role);
        $event = $this->seatedEvent($role, $plan);
        $date = $event->saleEventDateFromStartsAt();

        $map = $this->service()->materialize($event, $date);

        $this->assertNotNull($map);
        $this->assertSame($date, $map->event_date);
        $this->assertNotNull($map->materialized_at, 'a half-built map must never be returned');

        $this->assertSame(2, $map->levels()->count());
        $this->assertSame(4, $map->sections()->count());
        $this->assertSame(10, $map->seats()->count());

        // Seats land on the COPIED sections, not the template's.
        $this->assertSame(0, SeatingSeat::where('event_seating_map_id', $map->id)
            ->whereIn('seating_section_id', $plan->sections()->pluck('id'))->count());

        // The table and its two unnumbered chairs came across, repointed at the copy.
        $cabaret = $map->sections()->where('name', 'Cabaret')->first();
        $this->assertSame(1, $cabaret->tables()->count());
        $copiedTable = $cabaret->tables()->first();
        $this->assertSame(2, $copiedTable->seats()->count());
        $this->assertNull($copiedTable->seats()->first()->seat_label);

        // Distinguishing detail carried, not defaulted.
        $stallsSeat = SeatingSeat::where('event_seating_map_id', $map->id)
            ->where('row_label', 'A')->where('seat_label', '2')->first();
        $this->assertTrue($stallsSeat->aisle_after);
        $this->assertSame('available', $stallsSeat->status);
        $this->assertNotNull($stallsSeat->source_seat_id);

        // Standing has capacity and deliberately no seat rows - it sells through the
        // ordinary quantity path.
        $standing = $map->sections()->where('name', 'Standing')->first();
        $this->assertSame(50, $standing->capacity);
        $this->assertSame(0, $standing->seats()->count());

        // The price band is only mapped when the plan is attached to tickets, never copied.
        $this->assertNull($standing->ticket_id);
        $this->assertSame('Standing', $standing->band);
    }

    public function test_materialize_is_idempotent(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $plan = $this->makePlan($role);
        $event = $this->seatedEvent($role, $plan);

        $first = $this->service()->materialize($event);
        $second = $this->service()->materialize($event);

        $this->assertSame($first->id, $second->id);
        $this->assertSame(1, EventSeatingMap::where('event_id', $event->id)->count());
        $this->assertSame(10, SeatingSeat::where('event_seating_map_id', $first->id)->count());
    }

    /**
     * The reason the snapshot exists at all. A/B this by deleting copyStructure()'s
     * remapping and pointing the map at the template's own rows: this fails immediately.
     */
    public function test_editing_the_template_afterwards_does_not_reach_a_materialized_date(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $plan = $this->makePlan($role);
        $event = $this->seatedEvent($role, $plan);
        $map = $this->service()->materialize($event);

        // Somebody buys seat A1 in the Stalls.
        $sold = SeatingSeat::where('event_seating_map_id', $map->id)
            ->where('row_label', 'A')->where('seat_label', '1')->first();
        $sold->update(['status' => 'sold']);

        // The organizer then rips a row out of the house plan and renames a section.
        $plan->seats()->where('row_label', 'B')->delete();
        $plan->sections()->where('name', 'Stalls')->update(['name' => 'Orchestra']);

        $map->refresh();
        $this->assertSame(10, $map->seats()->count(), 'the sold occurrence must keep every seat it had');
        $this->assertSame('sold', $sold->fresh()->status);
        $this->assertNotNull(
            $map->sections()->where('name', 'Stalls')->first(),
            'the snapshot keeps the name it was sold under'
        );
    }

    public function test_a_row_must_belong_to_exactly_one_owner(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $plan = $this->makePlan($role);
        $event = $this->seatedEvent($role, $plan);
        $map = $this->service()->materialize($event);

        $this->expectException(\LogicException::class);
        SeatingLevel::create([
            'seating_plan_id' => $plan->id,
            'event_seating_map_id' => $map->id,
            'name' => 'Reachable from both, which must never happen',
        ]);
    }

    public function test_a_row_with_no_owner_is_rejected(): void
    {
        $this->expectException(\LogicException::class);
        SeatingLevel::create(['name' => 'Orphan']);
    }

    /**
     * Hold expiry is evaluated at READ time, so a lapsed cart hold is sellable with no
     * sweeper having run - selfhost has no sub-minute cron to rely on. A staff block
     * carries no expiry at all and must never come back on its own.
     */
    public function test_a_lapsed_cart_hold_is_available_but_a_staff_block_is_not(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $plan = $this->makePlan($role);
        $event = $this->seatedEvent($role, $plan);
        $map = $this->service()->materialize($event);

        $seats = SeatingSeat::where('event_seating_map_id', $map->id)->orderBy('id')->get();

        $lapsed = $seats[0];
        $lapsed->update(['status' => 'held', 'hold_kind' => 'cart', 'hold_token' => 'abc', 'hold_expires_at' => now()->subMinute()]);

        $live = $seats[1];
        $live->update(['status' => 'held', 'hold_kind' => 'cart', 'hold_token' => 'abc', 'hold_expires_at' => now()->addMinutes(10)]);

        $house = $seats[2];
        $house->update(['status' => 'held', 'hold_kind' => 'house', 'hold_expires_at' => null, 'hold_note' => 'Production hold']);

        $seats[3]->update(['status' => 'sold']);

        $this->assertTrue($lapsed->fresh()->isAvailable());
        $this->assertFalse($live->fresh()->isAvailable());
        $this->assertFalse($house->fresh()->isAvailable());
        $this->assertTrue($house->fresh()->isBlocked());

        // The scope and the row-level twin must agree, or a map oversells.
        $availableIds = SeatingSeat::where('event_seating_map_id', $map->id)->available()->pluck('id')->all();
        $expected = $seats->filter(fn ($s) => $s->fresh()->isAvailable())->pluck('id')->all();
        sort($availableIds);
        sort($expected);
        $this->assertSame($expected, $availableIds);
        $this->assertSame(7, count($availableIds), '10 seats less one live hold, one house hold and one sold');
    }

    public function test_available_seat_count_narrows_to_one_price_band(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $plan = $this->makePlan($role);
        $event = $this->seatedEvent($role, $plan);
        $map = $this->service()->materialize($event);

        $stallsTicket = $this->createTicket($event, ['type' => 'Stalls', 'price' => 30, 'quantity' => 6]);
        $circleTicket = $this->createTicket($event, ['type' => 'Circle', 'price' => 20, 'quantity' => 2]);

        $map->sections()->where('name', 'Stalls')->update(['ticket_id' => $stallsTicket->id]);
        $map->sections()->where('name', 'Circle')->update(['ticket_id' => $circleTicket->id]);

        $this->assertSame(10, $this->service()->totalSeatCount($map));
        $this->assertSame(10, $this->service()->availableSeatCount($map));
        $this->assertSame(6, $this->service()->availableSeatCount($map, $stallsTicket->id));
        $this->assertSame(2, $this->service()->availableSeatCount($map, $circleTicket->id));

        // Scoped to Stalls on purpose: the Circle has a row A seat 1 of its own, and an
        // unscoped update would sell one out of each band.
        SeatingSeat::where('event_seating_map_id', $map->id)
            ->where('seating_section_id', $map->sections()->where('name', 'Stalls')->value('id'))
            ->where('row_label', 'A')->where('seat_label', '1')
            ->update(['status' => 'sold']);

        $this->assertSame(5, $this->service()->availableSeatCount($map, $stallsTicket->id));
        $this->assertSame(2, $this->service()->availableSeatCount($map, $circleTicket->id), 'the other band is untouched');
    }

    public function test_revert_to_template_refuses_while_a_seat_is_sold(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $plan = $this->makePlan($role);
        $event = $this->seatedEvent($role, $plan);
        $map = $this->service()->materialize($event);

        SeatingSeat::where('event_seating_map_id', $map->id)->limit(1)->update(['status' => 'sold']);

        $this->assertFalse($this->service()->revertToTemplate($map));
        $this->assertNotNull(EventSeatingMap::find($map->id));

        SeatingSeat::where('event_seating_map_id', $map->id)->update(['status' => 'available']);

        $this->assertTrue($this->service()->revertToTemplate($map));
        $this->assertNull(EventSeatingMap::find($map->id));
        $this->assertSame(0, SeatingSeat::where('event_seating_map_id', $map->id)->count(), 'the snapshot cascades away');
        $this->assertSame(10, $plan->seats()->count(), 'but the template is untouched');
    }

    /**
     * A one-time event reaches the guest page with no date in hand. If this resolved
     * differently from TicketController::assertLegTicketsAvailable() the map and the
     * oversell guard would key on different strings and the event would get two maps.
     */
    public function test_the_occurrence_date_resolves_the_way_checkout_does(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $plan = $this->makePlan($role);
        $event = $this->seatedEvent($role, $plan);

        $expected = $event->saleEventDateFromStartsAt();

        $fromNull = $this->service()->materialize($event, null);
        $fromExplicit = $this->service()->materialize($event, $expected);

        $this->assertSame($expected, $fromNull->event_date);
        $this->assertSame($fromNull->id, $fromExplicit->id);
        $this->assertSame(1, EventSeatingMap::where('event_id', $event->id)->count());
    }

    public function test_an_event_with_no_plan_materializes_nothing(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->createEvent($role, ['tickets_enabled' => true]);

        $this->assertFalse($event->hasAllocatedSeating());
        $this->assertNull($this->service()->materialize($event));
        $this->assertSame(0, EventSeatingMap::where('event_id', $event->id)->count());
    }

    public function test_bump_version_is_monotonic(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $plan = $this->makePlan($role);
        $event = $this->seatedEvent($role, $plan);
        $map = $this->service()->materialize($event);

        $first = $map->bumpVersion();
        $second = $map->bumpVersion();

        $this->assertGreaterThan($first, $second);
        $this->assertSame($second, (int) $map->fresh()->version);
        $this->assertSame($second, $map->version, 'the in-memory model tracks the claimed version');
    }

    public function test_seat_labels_read_for_a_ticket_or_a_scanner(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $plan = $this->makePlan($role);
        $event = $this->seatedEvent($role, $plan);
        $map = $this->service()->materialize($event);

        $seat = SeatingSeat::with('section')
            ->where('event_seating_map_id', $map->id)
            ->where('row_label', 'A')->where('seat_label', '3')->first();

        $this->assertSame('Row A, Seat 3', $seat->label());
        $this->assertSame('Stalls, Row A, Seat 3', $seat->fullLabel());

        $chair = SeatingSeat::with(['section', 'seatingTable'])
            ->where('event_seating_map_id', $map->id)
            ->whereNotNull('seating_table_id')->first();

        $this->assertSame('Table 1', $chair->label(), 'an unnumbered chair reads as just its table');
        $this->assertSame('Cabaret, Table 1', $chair->fullLabel());
    }
}
