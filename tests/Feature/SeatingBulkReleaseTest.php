<?php

namespace Tests\Feature;

use App\Exceptions\BusinessException;
use App\Models\Event;
use App\Models\Role;
use App\Models\SaleTicket;
use App\Models\SeatingLevel;
use App\Models\SeatingPlan;
use App\Models\SeatingSeat;
use App\Models\SeatingSection;
use App\Repos\EventRepo;
use App\Services\BoxOfficeSeatingService;
use App\Services\SeatingMapService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Releasing a party in one action.
 *
 * releaseSeat() takes one seat, so refunding a party of six was six click-and-confirm cycles at the
 * counter while Hold back and Book have always been bulk.
 *
 * The bulk version is deliberately NOT a loop over the single one: that would take a tickets lock,
 * then the map lock (bumpVersion is an UPDATE and holds an X lock to commit), then another tickets
 * lock - the exact {event_seating_maps, tickets} inversion the single-seat version documents.
 */
class SeatingBulkReleaseTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private function boxOffice(): BoxOfficeSeatingService
    {
        return app(BoxOfficeSeatingService::class);
    }

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
                'position' => $n, 'x' => $n * 26,
            ]);
        }

        $request = Request::create('/', 'POST', [
            'name' => 'Show',
            'starts_at' => now()->addMonth()->setTime(19, 30)->format('Y-m-d H:i:s'),
            'tickets_enabled' => 1, 'payment_method' => 'cash', 'ticket_currency_code' => 'USD',
            'creator_role_id' => $role->id, 'seating_plan_id' => $plan->id,
            'tickets' => [['type' => 'Stalls', 'price' => 20, 'quantity' => 999, 'seating_band' => 'Stalls']],
        ]);
        $request->setUserResolver(fn () => $role->user);
        $event = app(EventRepo::class)->saveEvent($role, $request)->fresh();
        $event->roles()->updateExistingPivot($role->id, ['is_accepted' => true]);

        return $event->fresh();
    }

    /**
     * Sell $count seats at the counter, which is the shape a party arrives in.
     *
     * Through the route rather than the service: bookSeats() reads a $buyer array the controller
     * assembles - subdomain, phone, amount and all - so calling it directly means reproducing that
     * contract in the fixture and getting it wrong when it changes.
     */
    private function sellParty(Role $role, Event $event, int $count): array
    {
        $date = $event->saleEventDateFromStartsAt();
        $map = app(SeatingMapService::class)->materialize($event, $date);
        $seats = SeatingSeat::where('event_seating_map_id', $map->id)
            ->orderBy('position')->take($count)->pluck('id')->all();

        $this->actingAs($role->user)->postJson(route('box_office.book', [
            'subdomain' => $role->subdomain, 'hash' => \App\Utils\UrlUtils::encodeId($event->id),
        ]), [
            'seat_ids' => $seats,
            'name' => 'Party of '.$count,
            'email' => 'party@gmail.com',
            'status' => 'paid',
        ])->assertOk();

        return [$map->fresh(), $seats];
    }

    public function test_a_party_is_released_in_one_action(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->seatedEvent($role);
        [$map, $seats] = $this->sellParty($role, $event, 6);

        $ticket = $event->tickets->firstWhere('seating_band', 'Stalls');
        $date = $event->saleEventDateFromStartsAt();
        $this->assertSame(6, $ticket->fresh()->soldCountFor($date));

        $released = $this->boxOffice()->releaseSeats($map, $seats);

        $this->assertSame(6, $released);
        $this->assertSame(0, SeatingSeat::whereIn('id', $seats)->where('status', 'sold')->count());
        $this->assertSame(0, SeatingSeat::whereIn('id', $seats)->whereNotNull('sale_id')->count());

        // The quantity counter moves by the same number, in one step rather than six.
        $this->assertSame(0, $ticket->fresh()->soldCountFor($date), 'the sold counter must follow the seats');

        // And the line it came from is emptied, not left claiming six.
        $this->assertSame(0, (int) SaleTicket::whereIn('id',
            SeatingSeat::whereIn('id', $seats)->pluck('sale_ticket_id')->filter())->sum('quantity'));
    }

    public function test_a_selection_holding_an_unsold_seat_is_refused_whole(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->seatedEvent($role);
        [$map, $seats] = $this->sellParty($role, $event, 3);

        $free = SeatingSeat::where('event_seating_map_id', $map->id)
            ->where('status', 'available')->first();

        try {
            $this->boxOffice()->releaseSeats($map, array_merge($seats, [$free->id]));
            $this->fail('a mixed selection must be refused');
        } catch (BusinessException $e) {
            $this->assertStringContainsString($free->seat_label, $e->getMessage(), 'the refusal must name the seat');
        }

        // All or nothing: "six seats" is what the staff member believes they acted on.
        $this->assertSame(3, SeatingSeat::whereIn('id', $seats)->where('status', 'sold')->count());
    }

    /**
     * The reason this is not a loop, pinned the only way a single-threaded test can pin it.
     *
     * Looping releaseSeat() takes tickets, then the map (bumpVersion is an UPDATE holding an X lock
     * to commit), then tickets AGAIN - acquiring a tickets lock while already holding the map lock,
     * which is the {event_seating_maps, tickets} inversion releaseSeat() documents avoiding. A
     * deadlock cannot be provoked from one thread, so this asserts the ORDER and the COUNT: every
     * tickets lock comes before the single map write.
     *
     * Mirrors test_booking_locks_the_tickets_before_it_touches_the_map_row in SeatingBoxOfficeTest.
     */
    public function test_the_bulk_release_locks_every_ticket_before_it_touches_the_map_row(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->seatedEvent($role);
        [$map, $seats] = $this->sellParty($role, $event, 4);

        // Flush as well as enable: the log is not cleared by enableQueryLog(), so without this it
        // still holds the booking above - and its map bump made the "once, not once per seat"
        // assertion fail against work this test never performed.
        \Illuminate\Support\Facades\DB::enableQueryLog();
        \Illuminate\Support\Facades\DB::flushQueryLog();
        $this->boxOffice()->releaseSeats($map, $seats);
        $sql = array_column(\Illuminate\Support\Facades\DB::getQueryLog(), 'query');
        \Illuminate\Support\Facades\DB::disableQueryLog();

        $ticketLocks = [];
        $mapWrites = [];

        foreach ($sql as $i => $query) {
            $normalized = strtolower($query);

            if (str_contains($normalized, 'from `tickets`') && str_contains($normalized, 'for update')) {
                $ticketLocks[] = $i;
            }

            if (str_contains($normalized, 'update `event_seating_maps`') && str_contains($normalized, '`version`')) {
                $mapWrites[] = $i;
            }
        }

        $this->assertNotEmpty($ticketLocks, 'the release must take a row lock on tickets');
        $this->assertCount(1, $mapWrites, 'the batch must bump the map version once, not once per seat');
        $this->assertLessThan(
            $mapWrites[0],
            max($ticketLocks),
            'every tickets lock must come BEFORE event_seating_maps - see the note on releaseSeats()',
        );
    }

    /**
     * The printed sheet listed every seat in the house, sold or not.
     *
     * A 2,000-seat room on a 40%-sold night printed 2,000 lines to find 800, and there was no
     * by-name order at all - front of house could not look up a walk-up customer on paper without
     * re-sorting the CSV in a spreadsheet.
     */
    public function test_the_report_can_narrow_to_the_seats_that_are_actually_taken(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->seatedEvent($role);
        [$map] = $this->sellParty($role, $event, 3);

        $args = ['subdomain' => $role->subdomain, 'hash' => \App\Utils\UrlUtils::encodeId($event->id)];

        $all = $this->actingAs($role->user)->get(route('box_office.report', $args))->assertOk();
        $this->assertCount(10, $all->viewData('rows'), 'the default sheet lists the whole house');

        $taken = $this->actingAs($role->user)->get(route('box_office.report', $args + ['view' => 'taken']))->assertOk();
        $rows = $taken->viewData('rows');
        $this->assertCount(3, $rows, 'the front-of-house sheet lists only what is taken');
        $this->assertSame([], array_values(array_filter($rows, fn ($r) => $r['state'] === 'available')));

        // The map is NOT narrowed - a plan with holes in it is not a plan.
        $this->assertCount(10, collect($taken->viewData('levels'))->pluck('drawn')->flatten(1));
    }

    public function test_the_report_can_be_ordered_by_name_for_the_door(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->seatedEvent($role);
        $date = $event->saleEventDateFromStartsAt();
        $map = app(SeatingMapService::class)->materialize($event, $date);
        $args = ['subdomain' => $role->subdomain, 'hash' => \App\Utils\UrlUtils::encodeId($event->id)];

        // Two buyers, deliberately booked out of alphabetical order.
        foreach ([['Zed Zebra', 1], ['Abe Abbott', 2]] as [$name, $position]) {
            $seat = SeatingSeat::where('event_seating_map_id', $map->id)->where('position', $position)->first();
            $this->actingAs($role->user)->postJson(route('box_office.book', $args), [
                'seat_ids' => [$seat->id], 'name' => $name, 'email' => 'x@gmail.com', 'status' => 'paid',
            ])->assertOk();
        }

        $rows = $this->actingAs($role->user)
            ->get(route('box_office.report', $args + ['view' => 'names']))->assertOk()->viewData('rows');

        $this->assertSame(['Abe Abbott', 'Zed Zebra'], array_column($rows, 'name'));
    }

    public function test_an_unknown_view_falls_back_to_the_whole_house(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->seatedEvent($role);
        $this->sellParty($role, $event, 2);

        $rows = $this->actingAs($role->user)->get(route('box_office.report', [
            'subdomain' => $role->subdomain,
            'hash' => \App\Utils\UrlUtils::encodeId($event->id),
            'view' => 'anything',
        ]))->assertOk()->viewData('rows');

        $this->assertCount(10, $rows);
    }

    /**
     * Every seat mutation leaves a record, visible to the schedule owner.
     *
     * Neither the controller nor BoxOfficeSeatingService wrote an audit row for block, unblock,
     * release, exchange or a counter booking - so "who released whose seat" had no answer, which is
     * the first question after a refund dispute.
     */
    public function test_seat_mutations_are_recorded_against_the_event(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->seatedEvent($role);
        [$map, $seats] = $this->sellParty($role, $event, 2);
        $args = ['subdomain' => $role->subdomain, 'hash' => \App\Utils\UrlUtils::encodeId($event->id)];

        $free = SeatingSeat::where('event_seating_map_id', $map->id)->where('status', 'available')->first();

        $this->actingAs($role->user)->postJson(route('box_office.block', $args), [
            'seat_ids' => [$free->id], 'kind' => 'house',
        ])->assertOk();

        $this->actingAs($role->user)->postJson(route('box_office.release_seat', $args), [
            'seat_ids' => $seats,
        ])->assertOk();

        $actions = \App\Models\AuditLog::pluck('action')->all();
        $this->assertContains('sale.seat_blocked', $actions);
        $this->assertContains('sale.seat_released', $actions);
        $this->assertContains('sale.seat_booked', $actions, 'the counter booking in the fixture must be recorded too');

        $release = \App\Models\AuditLog::where('action', 'sale.seat_released')->firstOrFail();

        $this->assertSame($role->user->id, $release->user_id, 'the record must name who did it');
        $this->assertSame($event->id, (int) $release->model_id);

        // The event_id suffix is load-bearing: RoleController::auditLog() only surfaces `sale.%`
        // rows to a schedule owner when the metadata ends with it. Without it these are written and
        // then invisible to the one person who needs them.
        $this->assertStringEndsWith('event_id:'.$event->id, $release->metadata);

        // The seats, and WHOSE they were. Seat labels belong to the seat and survive a release;
        // sale_id does not, so recording the buyer is only possible before it runs - which is what
        // makes the ordering in the controller load-bearing rather than incidental.
        $this->assertStringContainsString(SeatingSeat::find($seats[0])->fullLabel(), $release->metadata);
        $this->assertStringContainsString('Party of 2', $release->metadata, 'the record must name whose seats these were');
    }

    /**
     * How full the house is, and how the run is selling.
     *
     * OrphanSeatRule::soldPercent() has computed the first number on every guest selection since the
     * feature shipped and rendered it to nobody, and there was no cross-date view at all - the docs
     * name that as a limitation. A producer comparing nights had to open thirty consoles.
     */
    public function test_the_sheet_reports_how_full_the_house_is(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->seatedEvent($role);
        $this->sellParty($role, $event, 4);

        $response = $this->actingAs($role->user)->get(route('box_office.report', [
            'subdomain' => $role->subdomain, 'hash' => \App\Utils\UrlUtils::encodeId($event->id),
        ]))->assertOk();

        $occupancy = $response->viewData('occupancy');

        // Four of ten.
        $this->assertSame(40, $occupancy['percent']);
        $this->assertSame(4, $occupancy['sold']);
        $this->assertSame(10, $occupancy['total']);

        // Section by section, which is what the header actually draws. This is accumulated by
        // reportData()'s own pass now rather than re-derived per section, so it needs pinning.
        $this->assertCount(1, $occupancy['sections']);
        $this->assertSame('Stalls', $occupancy['sections'][0]['name']);
        $this->assertSame(4, $occupancy['sections'][0]['sold']);
        $this->assertSame(10, $occupancy['sections'][0]['total']);
        $this->assertSame(40, $occupancy['sections'][0]['percent']);
    }

    public function test_the_csv_carries_the_same_rows_as_the_screen(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->seatedEvent($role);
        [$map, $seats] = $this->sellParty($role, $event, 4);
        SeatingSeat::whereIn('id', [$seats[0]])->update(['checked_in_at' => now()]);

        $args = ['subdomain' => $role->subdomain, 'hash' => \App\Utils\UrlUtils::encodeId($event->id)];

        $all = $this->actingAs($role->user)->get(route('box_office.report_csv', $args))->streamedContent();
        $taken = $this->actingAs($role->user)
            ->get(route('box_office.report_csv', $args + ['view' => 'taken']))->streamedContent();

        // 10 seats in the house, 4 of them sold. Plus a header row each.
        $this->assertSame(11, substr_count(trim($all), "\n") + 1, 'the whole house');
        $this->assertSame(5, substr_count(trim($taken), "\n") + 1, 'only what is actually taken');

        // And the two columns the printed sheet gained.
        $header = strtok($taken, "\n");
        $this->assertStringContainsString(__('messages.ticket'), $header);
        $this->assertStringContainsString(__('messages.seating_arrived'), $header);
        $this->assertStringContainsString(__('messages.yes'), $taken, 'the arrived seat is marked');
    }

    public function test_the_run_view_lists_every_night_and_is_absent_for_a_one_off(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->seatedEvent($role);

        // A one-time event is not a run: one row comparing itself to itself is noise.
        $args = ['subdomain' => $role->subdomain, 'hash' => \App\Utils\UrlUtils::encodeId($event->id)];
        $this->assertSame([], $this->actingAs($role->user)->get(route('box_office.report', $args))->assertOk()->viewData('run'));

        $event->days_of_week = '1111111';
        $event->recurring_frequency = 'daily';
        $event->save();

        $run = $this->actingAs($role->user)->get(route('box_office.report', $args))->assertOk()->viewData('run');

        $this->assertGreaterThan(1, count($run));
        $this->assertCount(1, array_filter($run, fn ($n) => $n['current']), 'exactly one night is the one being shown');

        // A night nobody has opened has no snapshot, which is 0% rather than missing - the template
        // still says how big the room is.
        $untouched = collect($run)->firstWhere('current', false);
        $this->assertSame(0, $untouched['percent']);
        $this->assertSame(10, $untouched['total'], 'the room size comes from the template when there is no map yet');

        // Every assertion above holds if `sold` is hardcoded to 0, and `sold` is the one figure in
        // the run query that is a raw aggregate with a binding. So sell a party on TONIGHT and read
        // it back: the run view is the screen that says which dates are soft.
        [$map, $seats] = $this->sellParty($role, $event, 4);
        $tonight = $event->saleEventDateFromStartsAt();

        $run = $this->actingAs($role->user)->get(route('box_office.report', $args))->assertOk()->viewData('run');
        $sold = collect($run)->firstWhere('date', $tonight);

        $this->assertNotNull($sold, 'the night that was sold must be in the run');
        $this->assertSame(4, $sold['sold'], 'the run summary must count the seats actually sold');
        $this->assertSame(40, $sold['percent']);
        $this->assertCount(4, $seats);
        $this->assertNotNull($map);
    }

    public function test_one_seat_still_goes_through_the_single_path(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->seatedEvent($role);
        [$map, $seats] = $this->sellParty($role, $event, 2);

        $this->assertSame(1, $this->boxOffice()->releaseSeats($map, [$seats[0]]));

        $this->assertSame('available', SeatingSeat::find($seats[0])->status);
        $this->assertSame('sold', SeatingSeat::find($seats[1])->status, 'only the named seat is released');
    }
}
