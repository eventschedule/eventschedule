<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventSeatingMap;
use App\Models\Role;
use App\Models\SeatingLevel;
use App\Models\SeatingPlan;
use App\Models\SeatingSeat;
use App\Models\SeatingSection;
use App\Models\Ticket;
use App\Repos\EventRepo;
use App\Services\SeatingMapService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Attaching a seating plan to an event: which ticket prices which band, where a banded ticket's
 * quantity comes from, and what a guest is offered.
 *
 * The mapping lives on the TICKET (tickets.seating_band) rather than on the snapshot sections,
 * because pricing is set once on the event while sections are copied per occurrence - a recurring
 * event with 200 dates would otherwise need re-mapping 200 times.
 */
class SeatingEventTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private function service(): SeatingMapService
    {
        return app(SeatingMapService::class);
    }

    /** Stalls: 2 rows x 3 seats. Circle: 1 row x 2. Standing: capacity 50, no seat rows. */
    private function makePlan(Role $role): SeatingPlan
    {
        $plan = SeatingPlan::create(['role_id' => $role->id, 'name' => 'Main House']);
        $level = SeatingLevel::create(['seating_plan_id' => $plan->id, 'name' => 'Ground']);

        foreach ([['Stalls', 2, 3], ['Circle', 1, 2]] as [$name, $rows, $cols]) {
            $section = SeatingSection::create([
                'seating_plan_id' => $plan->id, 'seating_level_id' => $level->id,
                'name' => $name, 'band' => $name, 'kind' => 'seated',
            ]);
            for ($r = 0; $r < $rows; $r++) {
                for ($c = 1; $c <= $cols; $c++) {
                    SeatingSeat::create([
                        'seating_plan_id' => $plan->id, 'seating_section_id' => $section->id,
                        'row_label' => chr(65 + $r), 'row_position' => $r + 1,
                        'seat_label' => (string) $c, 'position' => $c,
                    ]);
                }
            }
        }

        SeatingSection::create([
            'seating_plan_id' => $plan->id, 'seating_level_id' => $level->id,
            'name' => 'Standing', 'band' => 'Standing', 'kind' => 'standing', 'capacity' => 50,
        ]);

        return $plan->fresh();
    }

    private function save(array $input, ?Event $event, Role $role): Event
    {
        $request = Request::create('/', 'POST', array_merge([
            'name' => 'Test Event',
            'starts_at' => now()->addMonths(6)->setTime(12, 0)->format('Y-m-d H:i:s'),
            'tickets_enabled' => 1,
            'payment_method' => 'stripe',
            'ticket_currency_code' => 'USD',
        ], $input));
        $request->setUserResolver(fn () => $role->user);

        return app(EventRepo::class)->saveEvent($role, $request, $event);
    }

    private function bandedTickets(SeatingPlan $plan): array
    {
        return [
            ['type' => 'Stalls', 'price' => 40, 'quantity' => 999, 'seating_band' => 'Stalls'],
            ['type' => 'Circle', 'price' => 25, 'quantity' => 999, 'seating_band' => 'Circle'],
            ['type' => 'Standing', 'price' => 15, 'quantity' => 999, 'seating_band' => 'Standing'],
        ];
    }

    public function test_a_banded_tickets_quantity_comes_from_the_plan_not_the_form(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $plan = $this->makePlan($role);

        $event = $this->save([
            'seating_plan_id' => $plan->id,
            'tickets' => $this->bandedTickets($plan),
        ], null, $role);

        $tickets = $event->fresh()->tickets->keyBy('type');

        // 999 was posted for all three; the plan is the authority.
        $this->assertSame(6, (int) $tickets['Stalls']->quantity, '2 rows x 3 seats');
        $this->assertSame(2, (int) $tickets['Circle']->quantity);
        $this->assertSame(50, (int) $tickets['Standing']->quantity, 'a standing band uses its capacity');
    }

    public function test_materializing_points_each_section_at_the_ticket_that_prices_it(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $plan = $this->makePlan($role);
        $event = $this->save([
            'seating_plan_id' => $plan->id,
            'tickets' => $this->bandedTickets($plan),
        ], null, $role);

        $map = $this->service()->materialize($event->fresh());
        $tickets = $event->fresh()->tickets->keyBy('type');

        $sections = $map->sections()->get()->keyBy('name');
        $this->assertSame($tickets['Stalls']->id, $sections['Stalls']->ticket_id);
        $this->assertSame($tickets['Circle']->id, $sections['Circle']->ticket_id);
        $this->assertSame($tickets['Standing']->id, $sections['Standing']->ticket_id);
    }

    public function test_an_allocated_ticket_offers_seats_remaining_not_quantity_minus_sold(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $plan = $this->makePlan($role);
        $event = $this->save([
            'seating_plan_id' => $plan->id,
            'tickets' => $this->bandedTickets($plan),
        ], null, $role)->fresh();

        $date = $event->saleEventDateFromStartsAt();
        $map = $this->service()->materialize($event, $date);
        $event = $event->fresh();
        $stalls = $event->tickets->firstWhere('type', 'Stalls');
        $stalls->setRelation('event', $event);

        $this->assertTrue($stalls->isAllocated());
        $this->assertSame(6, $stalls->toData($date)['quantity']);

        // Two seats go: one sold, one on a live cart hold.
        $seats = SeatingSeat::where('event_seating_map_id', $map->id)
            ->where('seating_section_id', $map->sections()->where('name', 'Stalls')->value('id'))
            ->orderBy('id')->get();
        $seats[0]->update(['status' => 'sold']);
        $seats[1]->update(['status' => 'held', 'hold_kind' => 'cart', 'hold_expires_at' => now()->addMinutes(5)]);

        $event = $event->fresh();
        $stalls = $event->tickets->firstWhere('type', 'Stalls');
        $stalls->setRelation('event', $event);
        $this->assertSame(4, $stalls->toData($date)['quantity']);

        // A lapsed hold is sellable again with no sweeper having run.
        $seats[1]->update(['hold_expires_at' => now()->subMinute()]);
        $event = $event->fresh();
        $stalls = $event->tickets->firstWhere('type', 'Stalls');
        $stalls->setRelation('event', $event);
        $this->assertSame(5, $stalls->toData($date)['quantity']);
    }

    public function test_a_standing_band_keeps_the_quantity_path(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $plan = $this->makePlan($role);
        $event = $this->save([
            'seating_plan_id' => $plan->id,
            'tickets' => $this->bandedTickets($plan),
        ], null, $role)->fresh();

        $standing = $event->tickets->firstWhere('type', 'Standing');
        $standing->setRelation('event', $event);

        // It has a band and a ticket so the map can price it, but no seat rows - treating it as
        // allocated would report zero seats available, i.e. permanently sold out.
        $this->assertFalse($standing->isAllocated());
        $this->assertSame('Standing', $standing->seating_band);
        $this->assertSame(config('app.max_tickets_per_order'), $standing->toData($event->saleEventDateFromStartsAt())['quantity']);
    }

    public function test_an_allocated_event_has_no_shared_house(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $plan = $this->makePlan($role);
        $event = $this->save([
            'seating_plan_id' => $plan->id,
            'tickets' => $this->bandedTickets($plan),
        ], null, $role)->fresh();

        // Each band owns its own seats. One pooled number would cap a large band at the smallest
        // one, and would let a band oversell so long as the total still fit.
        $this->assertNull($event->occurrenceSeatsRemaining($event->saleEventDateFromStartsAt()));
    }

    public function test_renaming_a_band_repoints_dates_that_were_already_snapshotted(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $plan = $this->makePlan($role);
        $event = $this->save([
            'seating_plan_id' => $plan->id,
            'tickets' => $this->bandedTickets($plan),
        ], null, $role)->fresh();

        $map = $this->service()->materialize($event);
        $stalls = $event->tickets->firstWhere('type', 'Stalls');
        $this->assertSame($stalls->id, $map->sections()->where('name', 'Stalls')->value('ticket_id'));

        // The organizer renames the band on the ticket so it no longer matches the section.
        $this->save([
            'seating_plan_id' => $plan->id,
            'tickets' => [
                ['id' => $stalls->id, 'type' => 'Stalls', 'price' => 40, 'quantity' => 6, 'seating_band' => 'Orchestra'],
            ],
        ], $event, $role);

        $this->assertNull(
            $map->fresh()->sections()->where('name', 'Stalls')->value('ticket_id'),
            'a section whose band no longer matches any ticket must stop selling at the old price'
        );
    }

    public function test_moving_a_one_time_event_carries_its_seat_map(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $plan = $this->makePlan($role);
        $event = $this->save([
            'seating_plan_id' => $plan->id,
            'tickets' => $this->bandedTickets($plan),
        ], null, $role)->fresh();

        $oldDate = $event->saleEventDateFromStartsAt();
        $map = $this->service()->materialize($event, $oldDate);

        // Block a seat so there is state worth carrying across.
        SeatingSeat::where('event_seating_map_id', $map->id)->limit(1)
            ->update(['status' => 'held', 'hold_kind' => 'house', 'hold_note' => 'Production hold']);

        $newStart = now()->addMonths(7)->setTime(12, 0)->format('Y-m-d H:i:s');
        $event = $this->save([
            'starts_at' => $newStart,
            'seating_plan_id' => $plan->id,
            'tickets' => $this->bandedTickets($plan),
        ], $event, $role)->fresh();

        $newDate = $event->saleEventDateFromStartsAt();
        $this->assertNotSame($oldDate, $newDate, 'fixture sanity: the event actually moved');

        $this->assertSame(1, EventSeatingMap::where('event_id', $event->id)->count(), 'no orphan left behind');
        $this->assertSame($map->id, EventSeatingMap::where('event_id', $event->id)->value('id'));
        $this->assertSame($newDate, $map->fresh()->event_date);
        $this->assertSame(1, SeatingSeat::where('event_seating_map_id', $map->id)
            ->where('hold_kind', 'house')->count(), 'the house hold moved with it');
    }

    public function test_a_non_enterprise_schedule_cannot_set_a_band(): void
    {
        config(['app.hosted' => true]);
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $plan = $this->makePlan($role);

        $role->plan_type = 'free';
        $role->plan_expires = now()->subYear()->format('Y-m-d');
        $role->save();
        $role = $role->fresh();

        $event = $this->save([
            'seating_plan_id' => $plan->id,
            'tickets' => [['type' => 'Stalls', 'price' => 40, 'quantity' => 20, 'seating_band' => 'Stalls']],
        ], null, $role)->fresh();

        $this->assertNull($event->seating_plan_id, 'the plan itself is Enterprise');
        $ticket = $event->tickets->first();
        $this->assertNull($ticket->seating_band);
        $this->assertSame(20, (int) $ticket->quantity, 'the posted quantity stands when no plan derives it');
    }

    /**
     * The Tickets tab has to RENDER the seating controls, not just accept them.
     *
     * A Blade directive named inside a JS comment compiles wherever it appears, so the first
     * version of this markup killed the whole event form with a parse error - caught only because
     * unrelated tests happen to load the page. This asserts the controls directly.
     */
    public function test_the_event_form_renders_the_plan_picker_and_band_select(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $plan = $this->makePlan($role);
        $event = $this->save([
            'seating_plan_id' => $plan->id,
            'tickets' => $this->bandedTickets($plan),
        ], null, $role)->fresh();

        $html = $this->actingAs($owner)
            ->get(route('event.edit', ['subdomain' => $role->subdomain, 'hash' => \App\Utils\UrlUtils::encodeId($event->id)]))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('name="seating_plan_id"', $html);
        $this->assertStringContainsString('Main House', $html);
        $this->assertStringContainsString('seatingBands', $html, 'the per-ticket band select is bound to the plan');
        $this->assertStringContainsString('seatingPlanOptions', $html);
        // The plan's bands reached the client so the select has something to offer.
        $this->assertStringContainsString('Stalls', $html);
    }

    public function test_a_schedule_with_no_plans_sees_none_of_it(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $event = $this->save(['tickets' => [['type' => 'General', 'price' => 10, 'quantity' => 50]]], null, $role)->fresh();

        $html = $this->actingAs($owner)
            ->get(route('event.edit', ['subdomain' => $role->subdomain, 'hash' => \App\Utils\UrlUtils::encodeId($event->id)]))
            ->assertOk()
            ->getContent();

        // An event with no seat map must look exactly as it did before this feature existed.
        $this->assertStringNotContainsString('name="seating_plan_id"', $html);
    }

    // ------------------------------------------------ review finding 1: backup

    public function test_a_backup_round_trip_preserves_the_band(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $plan = $this->makePlan($role);
        $this->save([
            'seating_plan_id' => $plan->id,
            'tickets' => $this->bandedTickets($plan),
        ], null, $role);

        $svc = app(\App\Services\BackupService::class);

        $exportJob = \App\Models\BackupJob::create(['user_id' => $owner->id, 'type' => 'export', 'status' => 'processing']);
        $data = $svc->exportSchedules([$role->fresh()], false, $exportJob)['json'];

        $exportedTickets = collect($data['schedules'][0]['events'][0]['tickets']);
        $this->assertContains('Stalls', $exportedTickets->pluck('seating_band')->all(),
            'exportTickets() is an explicit allowlist - an omitted column is silently dropped');

        $importJob = \App\Models\BackupJob::create(['user_id' => $owner->id, 'type' => 'import', 'status' => 'processing']);
        $svc->importSchedules($data, [0], $owner->id, $importJob);

        $restoredRole = Role::where('user_id', $owner->id)->where('id', '!=', $role->id)->latest('id')->firstOrFail();
        $restoredEvent = Event::whereHas('roles', fn ($q) => $q->where('roles.id', $restoredRole->id))->latest('id')->firstOrFail();

        $bands = $restoredEvent->tickets->pluck('seating_band')->filter()->values()->all();
        $this->assertContains('Stalls', $bands, 'without the band nothing maps and the event silently sells by quantity');
        $this->assertContains('Circle', $bands);
    }

    // ------------------------------------------------ review finding 2: sold out

    public function test_a_full_allocated_event_reports_sold_out(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $plan = $this->makePlan($role);
        $event = $this->save([
            'seating_plan_id' => $plan->id,
            // Seated bands only: a standing band never sells out at capacity 50 here.
            'tickets' => [
                ['type' => 'Stalls', 'price' => 40, 'quantity' => 999, 'seating_band' => 'Stalls'],
                ['type' => 'Circle', 'price' => 25, 'quantity' => 999, 'seating_band' => 'Circle'],
            ],
        ], null, $role)->fresh();

        $date = $event->saleEventDateFromStartsAt();
        $map = $this->service()->materialize($event, $date);

        $this->assertFalse($event->fresh()->allTicketsSoldOut($date), 'fixture sanity: nothing sold yet');

        // Everything but one seat.
        $ids = SeatingSeat::where('event_seating_map_id', $map->id)->orderBy('id')->pluck('id');
        SeatingSeat::whereIn('id', $ids->slice(0, $ids->count() - 1))->update(['status' => 'sold']);
        $this->assertFalse($event->fresh()->allTicketsSoldOut($date), 'one seat left is not sold out');

        SeatingSeat::whereIn('id', $ids)->update(['status' => 'sold']);

        // occurrenceSeatsRemaining() still returns null by design; sold-out must not read through it.
        $full = $event->fresh();
        $this->assertNull($full->occurrenceSeatsRemaining($date));
        $this->assertTrue($full->allTicketsSoldOut($date),
            'a null "no shared house" must not read as "unlimited, never sold out"');
    }

    public function test_the_waitlist_opens_once_an_allocated_event_is_full(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $plan = $this->makePlan($role);
        $event = $this->save([
            'seating_plan_id' => $plan->id,
            'tickets' => [['type' => 'Stalls', 'price' => 40, 'quantity' => 999, 'seating_band' => 'Stalls']],
        ], null, $role)->fresh();

        $date = $event->saleEventDateFromStartsAt();
        $map = $this->service()->materialize($event, $date);
        SeatingSeat::where('event_seating_map_id', $map->id)->update(['status' => 'sold']);

        // WaitlistController refuses a join unless this is true, so a permanently-false answer
        // locked guests out of the waitlist entirely on every allocated event.
        $this->assertTrue($event->fresh()->allTicketsSoldOut($date));
    }

    // ------------------------------------------------ review findings 3 and 4

    public function test_an_unsnapshotted_date_counts_seats_from_the_plan_not_the_stored_quantity(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $plan = $this->makePlan($role);
        $event = $this->save([
            'seating_plan_id' => $plan->id,
            'tickets' => $this->bandedTickets($plan),
        ], null, $role)->fresh();

        $date = $event->saleEventDateFromStartsAt();
        $this->assertNull($this->service()->mapFor($event, $date), 'fixture sanity: not snapshotted');

        // Force a divergence, the way a band removed from the plan leaves one behind: EventRepo
        // skips its derivation when the plan yields zero, so a stale quantity can survive.
        Ticket::where('event_id', $event->id)->where('type', 'Stalls')->update(['quantity' => 999]);

        $event = $event->fresh();
        $stalls = $event->tickets->firstWhere('type', 'Stalls');
        $stalls->setRelation('event', $event);

        $this->assertSame(6, $stalls->toData($date)['quantity'],
            'the plan is the authority, not the stored quantity');
    }

    public function test_a_band_with_no_seats_is_not_treated_as_allocated(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $plan = $this->makePlan($role);

        // A section drawn but never given rows - exactly what a half-built plan looks like.
        SeatingSection::create([
            'seating_plan_id' => $plan->id,
            'seating_level_id' => SeatingLevel::where('seating_plan_id', $plan->id)->value('id'),
            'name' => 'Gallery', 'band' => 'Gallery', 'kind' => 'seated',
        ]);

        $event = $this->save([
            'seating_plan_id' => $plan->id,
            'tickets' => [['type' => 'Gallery', 'price' => 10, 'quantity' => 30, 'seating_band' => 'Gallery']],
        ], null, $role)->fresh();

        $this->assertNotContains('Gallery', $event->seatedBands());

        $ticket = $event->tickets->first();
        $ticket->setRelation('event', $event);
        $this->assertFalse($ticket->isAllocated(),
            'an empty section would otherwise report the per-order cap for seats that do not exist');
    }

    public function test_the_band_survives_a_clone(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $plan = $this->makePlan($role);
        $event = $this->save([
            'seating_plan_id' => $plan->id,
            'tickets' => $this->bandedTickets($plan),
        ], null, $role)->fresh();

        $payload = EventRepo::buildClonePayload($event);

        $this->assertSame($plan->id, $payload['event']['seating_plan_id'] ?? $payload['seating_plan_id'] ?? null,
            'a clone references the same template');
        $bands = collect($payload['tickets'] ?? [])->pluck('seating_band')->filter()->values()->all();
        $this->assertContains('Stalls', $bands, 'Ticket::toClonePayload must carry the band');
    }
}
