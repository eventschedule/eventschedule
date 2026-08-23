<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Role;
use App\Models\SeatingLevel;
use App\Models\SeatingPlan;
use App\Models\SeatingSeat;
use App\Models\SeatingSection;
use App\Models\SeatingTable;
use App\Repos\EventRepo;
use App\Services\BestAvailableService;
use App\Services\SeatingMapService;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * A table's Booking setting has to mean what it says.
 *
 * `booking_mode` was stored, snapshotted, shipped to the browser and drawn in the designer - and
 * read by nothing, so "Whole table only" sold exactly like single seats. A venue selling a
 * fundraising dinner got the opposite of what it asked for: one guest taking one chair at a table
 * of eight.
 */
class SeatingTableBookingTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    /** Two tables of 4 in one section, each with the given booking mode. */
    private function makePlan(Role $role, string $mode): SeatingPlan
    {
        $plan = SeatingPlan::create(['role_id' => $role->id, 'name' => 'Dinner']);
        $level = SeatingLevel::create(['seating_plan_id' => $plan->id, 'name' => 'Floor']);
        $section = SeatingSection::create([
            'seating_plan_id' => $plan->id, 'seating_level_id' => $level->id,
            'name' => 'Floor', 'band' => 'Floor', 'kind' => 'table',
        ]);

        foreach ([1, 2] as $t) {
            $table = SeatingTable::create([
                'seating_section_id' => $section->id, 'label' => (string) $t,
                'shape' => 'round', 'seat_count' => 4, 'booking_mode' => $mode,
                'x' => $t * 200, 'y' => 0,
            ]);
            for ($n = 1; $n <= 4; $n++) {
                SeatingSeat::create([
                    'seating_plan_id' => $plan->id, 'seating_section_id' => $section->id,
                    'seating_table_id' => $table->id, 'seat_label' => (string) $n,
                    'row_label' => (string) $t, 'row_position' => $t, 'position' => $n,
                    'x' => $n * 20, 'y' => 0,
                ]);
            }
        }

        return $plan->fresh();
    }

    private function seatedEvent(Role $role, SeatingPlan $plan): Event
    {
        $request = Request::create('/', 'POST', [
            'name' => 'Gala Dinner',
            'starts_at' => now()->addMonths(6)->setTime(19, 30)->format('Y-m-d H:i:s'),
            'tickets_enabled' => 1, 'payment_method' => 'cash', 'ticket_currency_code' => 'USD',
            'creator_role_id' => $role->id, 'seating_plan_id' => $plan->id,
            'tickets' => [['type' => 'Floor', 'price' => 100, 'quantity' => 999, 'seating_band' => 'Floor']],
        ]);
        $request->setUserResolver(fn () => $role->user);
        $event = app(EventRepo::class)->saveEvent($role, $request)->fresh();
        $event->roles()->updateExistingPivot($role->id, ['is_accepted' => true]);

        return $event->fresh();
    }

    private function tableSeats($map, string $label)
    {
        $tableId = SeatingTable::whereIn('seating_section_id',
            SeatingSection::where('event_seating_map_id', $map->id)->pluck('id'))
            ->where('label', $label)->value('id');

        return SeatingSeat::where('seating_table_id', $tableId)->orderBy('position')->get();
    }

    public function test_a_size_limit_names_the_limit_it_hit(): void
    {
        $svc = app(\App\Services\SeatingStructureService::class);

        // Thirteen levels, well inside the seat cap. Reporting "up to 6000 seats" here sends the
        // organizer deleting rows that were never the problem.
        $levels = [];
        for ($i = 0; $i < 13; $i++) {
            $levels[] = ['name' => "L{$i}", 'sections' => []];
        }

        try {
            $svc->assertWithinLimits(['levels' => $levels]);
            $this->fail('thirteen levels should be refused');
        } catch (\App\Exceptions\BusinessException $e) {
            $this->assertStringContainsString('levels', $e->getMessage());
            $this->assertStringNotContainsString('6000', $e->getMessage());
        }

        // ...and the seat cap still reports seats.
        $seats = array_fill(0, 6001, ['row_label' => 'A', 'seat_label' => '1']);
        try {
            $svc->assertWithinLimits(['levels' => [['name' => 'L', 'sections' => [['name' => 'S', 'seats' => $seats]]]]]);
            $this->fail('6001 seats should be refused');
        } catch (\App\Exceptions\BusinessException $e) {
            $this->assertStringContainsString('6000', $e->getMessage());
        }
    }

    public function test_a_whole_table_refuses_a_partial_take(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role, 'whole'));
        $map = app(SeatingMapService::class)->materialize($event);
        $seats = $this->tableSeats($map, '1');

        $response = $this->postJson(route('seating.hold', ['subdomain' => $role->subdomain]), [
            'event_id' => UrlUtils::encodeId($event->id),
            'date' => $event->saleEventDateFromStartsAt(),
            'seat_ids' => $seats->take(2)->pluck('id')->all(),
        ]);

        $response->assertStatus(409);
        $this->assertSame('available', $seats->first()->fresh()->status,
            'two chairs of a whole-only table of four were held');
    }

    public function test_a_whole_table_taken_entirely_is_allowed(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role, 'whole'));
        $map = app(SeatingMapService::class)->materialize($event);
        $seats = $this->tableSeats($map, '1');

        $this->postJson(route('seating.hold', ['subdomain' => $role->subdomain]), [
            'event_id' => UrlUtils::encodeId($event->id),
            'date' => $event->saleEventDateFromStartsAt(),
            'seat_ids' => $seats->pluck('id')->all(),
        ])->assertOk();

        $this->assertSame(4, SeatingSeat::whereIn('id', $seats->pluck('id'))->where('status', 'held')->count());
    }

    public function test_single_seat_and_either_modes_are_unchanged(): void
    {
        foreach (['seat', 'either'] as $mode) {
            $role = $this->createRole($this->createOwner(), 'venue');
            $event = $this->seatedEvent($role, $this->makePlan($role, $mode));
            $map = app(SeatingMapService::class)->materialize($event);
            $seats = $this->tableSeats($map, '1');

            $this->postJson(route('seating.hold', ['subdomain' => $role->subdomain]), [
                'event_id' => UrlUtils::encodeId($event->id),
                'date' => $event->saleEventDateFromStartsAt(),
                'seat_ids' => [$seats->first()->id],
            ])->assertOk("mode {$mode} should still allow one chair");
        }
    }

    public function test_best_available_never_hands_back_part_of_a_whole_table(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role, 'whole'));
        $map = app(SeatingMapService::class)->materialize($event);
        $ticket = $event->tickets->first();

        // A party of two, in a room made entirely of whole-only tables of four.
        $picked = app(BestAvailableService::class)->pick($map, $ticket, 2);
        $this->assertSame([], $picked, 'best-available offered two chairs of a whole-only table');

        // A party of four fills one exactly, so it is offerable - and it is a WHOLE table.
        $four = app(BestAvailableService::class)->pick($map, $ticket, 4);
        $this->assertCount(4, $four);
        $this->assertSame(1, SeatingSeat::whereIn('id', $four)->distinct()->count('seating_table_id'),
            'the four seats should all be at the same table');
    }

    public function test_a_part_sold_whole_table_is_offered_to_nobody(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role, 'whole'));
        $map = app(SeatingMapService::class)->materialize($event);
        $ticket = $event->tickets->first();

        // Staff seated one guest at table 1, so it can no longer be sold whole.
        $this->tableSeats($map, '1')->first()->forceFill(['status' => 'sold'])->save();

        $four = app(BestAvailableService::class)->pick($map, $ticket, 4);
        $this->assertCount(4, $four);
        $tableLabels = SeatingTable::whereIn('id', SeatingSeat::whereIn('id', $four)->pluck('seating_table_id'))
            ->pluck('label')->unique()->values()->all();
        $this->assertSame(['2'], $tableLabels, 'the part-sold table must not be offered');
    }

    public function test_the_box_office_may_still_seat_one_guest_at_a_whole_table(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role, 'whole'));
        $map = app(SeatingMapService::class)->materialize($event);
        $seats = $this->tableSeats($map, '1');

        // Staff can see the whole room; seating one person at a reserved table is their job, and
        // it is the same exemption the accessibility and orphan rules already make.
        app(\App\Services\BoxOfficeSeatingService::class)->bookSeats($map, [$seats->first()->id], [
            'subdomain' => $role->subdomain, 'name' => 'Late Guest',
            'email' => null, 'phone' => null, 'status' => 'paid', 'amount' => null,
        ]);

        $this->assertSame('sold', $seats->first()->fresh()->status);
    }
}
