<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Role;
use App\Models\SeatingLevel;
use App\Models\SeatingPlan;
use App\Models\SeatingSeat;
use App\Models\SeatingSection;
use App\Repos\EventRepo;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The contract behind the buyer's "Find best seats" button.
 *
 * hold() has accepted ticket_id + quantity since the picker shipped, and nothing ever posted that
 * shape - so best available was reachable only from a pass booking or the attendee importer, while
 * /docs/allocated-seating and /features/allocated-seating both told buyers the control existed.
 *
 * This pins the endpoint the button depends on. The button itself is a Vue control and is verified
 * by driving the real page.
 */
class SeatingBestAvailableEndpointTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private function seatedEvent(Role $role): Event
    {
        $plan = SeatingPlan::create(['role_id' => $role->id, 'name' => 'House']);
        $level = SeatingLevel::create(['seating_plan_id' => $plan->id, 'name' => 'Ground']);
        $section = SeatingSection::create([
            'seating_plan_id' => $plan->id, 'seating_level_id' => $level->id,
            'name' => 'Stalls', 'band' => 'Stalls', 'kind' => 'seated', 'position' => 0,
        ]);
        foreach (range(1, 10) as $n) {
            SeatingSeat::create([
                'seating_plan_id' => $plan->id, 'seating_section_id' => $section->id,
                'row_label' => 'A', 'row_position' => 1, 'seat_label' => (string) $n,
                'position' => $n, 'x' => $n * 26, 'y' => 0,
            ]);
        }

        $request = Request::create('/', 'POST', [
            'name' => 'Show',
            'starts_at' => now()->addMonth()->setTime(19, 30)->format('Y-m-d H:i:s'),
            'tickets_enabled' => 1, 'payment_method' => 'cash', 'ticket_currency_code' => 'USD',
            'creator_role_id' => $role->id, 'seating_plan_id' => $plan->id,
            'tickets' => [['type' => 'Stalls', 'price' => 40, 'quantity' => 999, 'seating_band' => 'Stalls']],
        ]);
        $request->setUserResolver(fn () => $role->user);
        $event = app(EventRepo::class)->saveEvent($role, $request)->fresh();
        $event->roles()->updateExistingPivot($role->id, ['is_accepted' => true]);

        return $event->fresh();
    }

    /**
     * The picker shows one of two messages when a selection comes back short: somebody else took
     * the seats, or WE refused the surplus. It tells them apart by `capped` in this reply.
     *
     * The client half was verified in a browser against a stubbed reply, which proves nothing about
     * whether the server ever sets the field - and if it never does, every clamp falls through to
     * "Somebody else took your seats", which is the wrong-blame bug the split existed to fix.
     */
    public function test_the_hold_reply_says_when_our_own_limit_shortened_the_selection(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->seatedEvent($role);
        $ticket = $event->tickets->firstWhere('seating_band', 'Stalls');
        $ticket->update(['max_per_order' => 2]);

        $map = app(\App\Services\SeatingMapService::class)
            ->materialize($event, $event->saleEventDateFromStartsAt());
        $ids = SeatingSeat::where('event_seating_map_id', $map->id)
            ->orderBy('position')->take(5)->pluck('id')->all();

        $response = $this->postJson(route('seating.hold', ['subdomain' => $role->subdomain]), [
            'event_id' => UrlUtils::encodeId($event->id),
            'date' => $event->saleEventDateFromStartsAt(),
            'seat_ids' => $ids,
        ])->assertOk();

        $this->assertCount(2, $response->json('held'), 'the surplus is dropped, as it always was');
        $this->assertSame(2, $response->json('capped'), 'and the reply says WE are why');

        // A selection inside the limit is not flagged, or every buyer would be told about a cap
        // that never touched them.
        $within = $this->postJson(route('seating.hold', ['subdomain' => $role->subdomain]), [
            'event_id' => UrlUtils::encodeId($event->id),
            'date' => $event->saleEventDateFromStartsAt(),
            'seat_ids' => array_slice($ids, 0, 2),
        ])->assertOk();

        $this->assertCount(2, $within->json('held'));
        $this->assertNull($within->json('capped'));
    }

    public function test_a_party_size_holds_that_many_seats_together(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->seatedEvent($role);
        $ticket = $event->tickets->firstWhere('seating_band', 'Stalls');

        $response = $this->postJson(route('seating.hold', ['subdomain' => $role->subdomain]), [
            'event_id' => UrlUtils::encodeId($event->id),
            'date' => $event->saleEventDateFromStartsAt(),
            'ticket_id' => UrlUtils::encodeId($ticket->id),
            'quantity' => 3,
        ])->assertOk();

        $held = $response->json('held');
        $this->assertCount(3, $held);
        $this->assertNotNull($response->json('expires_at'));

        $seats = SeatingSeat::whereIn('id', $held)->orderBy('position')->get();
        $this->assertSame(['held', 'held', 'held'], $seats->pluck('status')->all());

        // Together, which is the entire promise: three consecutive positions in one row.
        $positions = $seats->pluck('position')->all();
        $this->assertSame(range($positions[0], $positions[0] + 2), $positions);
        $this->assertCount(1, $seats->pluck('row_position')->unique());
    }

    /**
     * The button must not recommend what the checkout then refuses.
     *
     * pick() scores blocks by distance from the centre of the row, and OrphanSeatRule refuses a
     * selection that strands a single seat. On a row of five, the most central block of four leaves
     * seat 1 alone - so the buyer pressed "Find best seats", got four seats, and was then blocked
     * from checking out by a rule they had not broken. This is the third rule pick() has had to
     * learn, after the accessible and whole-table ones, for exactly the same reason.
     */
    public function test_best_available_does_not_strand_a_seat_it_will_then_refuse(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');

        $plan = SeatingPlan::create(['role_id' => $role->id, 'name' => 'Row of five']);
        $level = SeatingLevel::create(['seating_plan_id' => $plan->id, 'name' => 'Ground']);
        $section = SeatingSection::create([
            'seating_plan_id' => $plan->id, 'seating_level_id' => $level->id,
            'name' => 'Stalls', 'band' => 'Stalls', 'kind' => 'seated', 'position' => 0,
        ]);
        // Two rows: one of five (where four strands a seat) and one of four (where it does not).
        foreach ([[1, 5], [2, 4]] as [$rowPos, $count]) {
            foreach (range(1, $count) as $n) {
                SeatingSeat::create([
                    'seating_plan_id' => $plan->id, 'seating_section_id' => $section->id,
                    'row_label' => chr(64 + $rowPos), 'row_position' => $rowPos,
                    'seat_label' => (string) $n, 'position' => $n, 'x' => $n * 26, 'y' => $rowPos * 30,
                ]);
            }
        }

        $request = Request::create('/', 'POST', [
            'name' => 'Show',
            'starts_at' => now()->addMonth()->setTime(19, 30)->format('Y-m-d H:i:s'),
            'tickets_enabled' => 1, 'payment_method' => 'cash', 'ticket_currency_code' => 'USD',
            'creator_role_id' => $role->id, 'seating_plan_id' => $plan->id,
            'tickets' => [['type' => 'Stalls', 'price' => 40, 'quantity' => 999, 'seating_band' => 'Stalls']],
        ]);
        $request->setUserResolver(fn () => $role->user);
        $event = app(EventRepo::class)->saveEvent($role, $request)->fresh();
        $event->roles()->updateExistingPivot($role->id, ['is_accepted' => true]);
        $ticket = $event->tickets->firstWhere('seating_band', 'Stalls');

        $held = $this->postJson(route('seating.hold', ['subdomain' => $role->subdomain]), [
            'event_id' => UrlUtils::encodeId($event->id),
            'date' => $event->saleEventDateFromStartsAt(),
            'ticket_id' => UrlUtils::encodeId($ticket->id),
            'quantity' => 4,
        ])->assertOk()->json();

        $this->assertCount(4, $held['held']);
        // Row 2 fills exactly; row 1 would leave one seat on its own. Row 1 is EARLIER, so without
        // the orphan term in the score it wins on the old section-then-row precedence.
        $rows = SeatingSeat::whereIn('id', $held['held'])->pluck('row_position')->unique()->all();
        $this->assertSame([2], $rows, 'best available strands a seat the checkout will refuse');

        // And the advisory agrees: nothing to warn about.
        $this->assertNull($held['warning']);
    }

    public function test_asking_for_more_than_is_left_holds_what_it_can_rather_than_failing(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->seatedEvent($role);
        $ticket = $event->tickets->firstWhere('seating_band', 'Stalls');

        $response = $this->postJson(route('seating.hold', ['subdomain' => $role->subdomain]), [
            'event_id' => UrlUtils::encodeId($event->id),
            'date' => $event->saleEventDateFromStartsAt(),
            'ticket_id' => UrlUtils::encodeId($ticket->id),
            'quantity' => 50,
        ])->assertOk();

        // The picker compares what came back against what was asked and says so when it is short;
        // a silent partial hold would read as a broken button.
        $this->assertLessThanOrEqual(10, count($response->json('held')));
    }
}
