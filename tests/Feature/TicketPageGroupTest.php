<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Role;
use App\Models\Sale;
use App\Models\SaleTicket;
use App\Models\SeatingLevel;
use App\Models\SeatingPlan;
use App\Models\SeatingSeat;
use App\Models\SeatingSection;
use App\Models\Ticket;
use App\Repos\EventRepo;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The ticket page for a GROUP PRIMARY - the buyer of a per-attendee order.
 *
 * `Sale::isPrimarySale()` is `group_id && group_id === id`, which TicketController sets on the
 * primary of every individual_tickets purchase. The page then rebuilds its ticket list as stdClass
 * totals aggregated across the group, and the seat-label line added with allocated seating called
 * seatLabels() on those totals - a method only SaleTicket has. Every per-attendee buyer's ticket
 * page was a 500, whether or not the event had a seating plan, and nothing rendered this path.
 */
class TicketPageGroupTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    /** A primary sale plus one guest row, exactly as individual_tickets writes them. */
    private function group(Event $event, Role $role, Ticket $ticket): array
    {
        $primary = $this->createSale($event, $role, ['name' => 'Buyer One'], $ticket);
        $primary->group_id = $primary->id;
        $primary->save();

        $guest = $this->createSale($event, $role, ['name' => 'Guest Two'], $ticket);
        $guest->group_id = $primary->id;
        $guest->save();

        return [$primary->fresh(), $guest->fresh()];
    }

    private function page(Event $event, Sale $sale)
    {
        return $this->get(route('ticket.view', [
            'event_id' => UrlUtils::encodeId($event->id),
            'secret' => $sale->secret,
        ]));
    }

    public function test_the_ticket_page_renders_for_a_group_primary_with_no_seating(): void
    {
        $role = $this->createRole($this->createOwner());
        $event = $this->createEvent($role, ['tickets_enabled' => true, 'payment_method' => 'cash']);
        $event->roles()->updateExistingPivot($role->id, ['is_accepted' => true]);
        $ticket = $this->createTicket($event, ['type' => 'General', 'price' => 10, 'quantity' => 50]);

        [$primary] = $this->group($event, $role, $ticket);

        $this->page($event, $primary)->assertOk()->assertSee('General', false);
    }

    public function test_a_group_primary_sees_the_seats_of_everyone_in_the_order(): void
    {
        $role = $this->createRole($this->createOwner());

        $plan = SeatingPlan::create(['role_id' => $role->id, 'name' => 'Small House']);
        $level = SeatingLevel::create(['seating_plan_id' => $plan->id, 'name' => 'Ground']);
        $section = SeatingSection::create([
            'seating_plan_id' => $plan->id, 'seating_level_id' => $level->id,
            'name' => 'Stalls', 'band' => 'Stalls', 'kind' => 'seated', 'position' => 0,
        ]);
        for ($n = 1; $n <= 4; $n++) {
            SeatingSeat::create([
                'seating_plan_id' => $plan->id, 'seating_section_id' => $section->id,
                'row_label' => 'A', 'row_position' => 1, 'seat_label' => (string) $n,
                'position' => $n, 'x' => $n * 26,
            ]);
        }

        $request = Request::create('/', 'POST', [
            'name' => 'Seated Show',
            'starts_at' => now()->addMonths(6)->setTime(19, 30)->format('Y-m-d H:i:s'),
            'tickets_enabled' => 1, 'payment_method' => 'cash', 'ticket_currency_code' => 'USD',
            'creator_role_id' => $role->id,
            'seating_plan_id' => $plan->id,
            'tickets' => [
                ['type' => 'Stalls', 'price' => 40, 'quantity' => 999, 'seating_band' => 'Stalls'],
            ],
        ]);
        $request->setUserResolver(fn () => $role->user);
        $event = app(EventRepo::class)->saveEvent($role, $request)->fresh();
        $event->roles()->updateExistingPivot($role->id, ['is_accepted' => true]);

        $ticket = $event->tickets->firstWhere('seating_band', 'Stalls');
        [$primary, $guest] = $this->group($event, $role, $ticket);

        $date = $event->saleEventDateFromStartsAt();
        $map = app(\App\Services\SeatingMapService::class)->materialize($event, $date);

        // One seat to the buyer's own line, one to the guest's. Only the aggregated branch can show
        // both, which is the whole reason that branch exists.
        $seats = SeatingSeat::where('event_seating_map_id', $map->id)->orderBy('position')->take(2)->get();
        foreach ([[$seats[0], $primary], [$seats[1], $guest]] as [$seat, $sale]) {
            $line = SaleTicket::where('sale_id', $sale->id)->first();
            $seat->forceFill([
                'status' => 'sold', 'sale_id' => $sale->id, 'sale_ticket_id' => $line->id,
            ])->save();
        }

        $response = $this->page($event, $primary)->assertOk();

        foreach ($seats as $seat) {
            $response->assertSee($seat->fresh()->fullLabel(), false);
        }
    }
}
