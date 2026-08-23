<?php

namespace Tests\Feature;

use App\Models\BackupJob;
use App\Models\Event;
use App\Models\EventSeatingMap;
use App\Models\Role;
use App\Models\Sale;
use App\Models\SeatingLevel;
use App\Models\SeatingPlan;
use App\Models\SeatingSeat;
use App\Models\SeatingSection;
use App\Repos\EventRepo;
use App\Services\BackupService;
use App\Services\BoxOfficeSeatingService;
use App\Services\SeatingMapService;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Backup and restore of the six seating tables.
 *
 * Before this the exporter simply dropped events.seating_plan_id, because a raw id would either
 * abort the whole restore on the foreign key or bind the event to another schedule's map. That kept
 * the restore safe and made it wrong: an allocated event came back with its tickets and its bands
 * but no plan, so a sold-out house restored as an empty one and every seat a buyer already held a
 * confirmation for went back on sale.
 */
class SeatingBackupTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private function svc(): BackupService
    {
        return app(BackupService::class);
    }

    /** Stalls: one row of 6. Circle: one row of 3. */
    private function makePlan(Role $role): SeatingPlan
    {
        $plan = SeatingPlan::create(['role_id' => $role->id, 'name' => 'Main House', 'description' => 'The big room']);
        $level = SeatingLevel::create(['seating_plan_id' => $plan->id, 'name' => 'Ground', 'position' => 0]);

        foreach ([['Stalls', 6, 0], ['Circle', 3, 1]] as [$name, $count, $pos]) {
            $section = SeatingSection::create([
                'seating_plan_id' => $plan->id, 'seating_level_id' => $level->id,
                'name' => $name, 'band' => $name, 'kind' => 'seated', 'position' => $pos,
                'color' => '#4E81FA', 'x' => $pos * 100, 'y' => 0,
            ]);
            for ($n = 1; $n <= $count; $n++) {
                SeatingSeat::create([
                    'seating_plan_id' => $plan->id, 'seating_section_id' => $section->id,
                    'row_label' => 'A', 'row_position' => 1, 'seat_label' => (string) $n,
                    'position' => $n, 'x' => $n * 26,
                    'kind' => ($name === 'Circle' && $n === 3) ? 'wheelchair' : 'seat',
                    'aisle_after' => $n === 3,
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
            'creator_role_id' => $role->id, 'seating_plan_id' => $plan->id,
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

    private function roundTrip(Role $role): Role
    {
        $owner = $role->user;
        $exportJob = BackupJob::create(['user_id' => $owner->id, 'type' => 'export', 'status' => 'processing']);
        $data = $this->svc()->exportSchedules([$role->fresh()], false, $exportJob)['json'];

        $importJob = BackupJob::create(['user_id' => $owner->id, 'type' => 'import', 'status' => 'processing']);
        $this->svc()->importSchedules($data, [0], $owner->id, $importJob);

        return Role::where('user_id', $owner->id)->where('id', '!=', $role->id)->latest('id')->firstOrFail();
    }

    private function restoredEvent(Role $newRole): Event
    {
        return $newRole->events()->latest('events.id')->firstOrFail();
    }

    public function test_the_plan_and_its_whole_structure_survive_a_round_trip(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $plan = $this->makePlan($role);
        $this->seatedEvent($role, $plan);

        $newRole = $this->roundTrip($role);
        $newPlan = SeatingPlan::where('role_id', $newRole->id)->firstOrFail();

        $this->assertNotSame($plan->id, $newPlan->id);
        $this->assertSame('Main House', $newPlan->name);
        $this->assertSame('The big room', $newPlan->description);

        $this->assertSame(1, SeatingLevel::where('seating_plan_id', $newPlan->id)->count());
        $sections = SeatingSection::where('seating_plan_id', $newPlan->id)->orderBy('position')->get();
        $this->assertSame(['Stalls', 'Circle'], $sections->pluck('name')->all());
        $this->assertSame(9, SeatingSeat::where('seating_plan_id', $newPlan->id)->count());

        // Per-seat detail, not just the count - the wheelchair space and the gangway are what the
        // rules run on, and a plan that loses them silently changes who can sit where.
        $wheelchair = SeatingSeat::where('seating_plan_id', $newPlan->id)->where('kind', 'wheelchair')->get();
        $this->assertCount(1, $wheelchair);
        $this->assertSame('3', $wheelchair->first()->seat_label);
        $this->assertSame(2, SeatingSeat::where('seating_plan_id', $newPlan->id)->where('aisle_after', true)->count());

        // Every section keeps the level it was on.
        foreach ($sections as $section) {
            $this->assertNotNull($section->seating_level_id);
        }
    }

    /**
     * A snapshot seat keeps its line back to the template seat it was copied from.
     *
     * source_seat_id is the only link between a date's seats and the plan they came from, so it is
     * what tells the designer that a seat it is about to move is already sold on some date. The
     * exporter can only write a ref, and the seats are written in one bulk insert that returns no
     * ids, so the mapping is rebuilt by reading them back - a wrong offset there binds every
     * snapshot seat to the wrong template row and nothing else in the restore would notice.
     */
    public function test_a_snapshot_seat_still_points_at_the_template_seat_it_came_from(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $plan = $this->makePlan($role);
        $event = $this->seatedEvent($role, $plan);

        $map = app(SeatingMapService::class)->materialize($event);
        // An untouched date is deliberately left out of the backup, so give this one a reason to
        // travel: one held seat is enough to make the snapshot worth keeping.
        $held = SeatingSeat::where('event_seating_map_id', $map->id)->orderBy('position')->firstOrFail();
        app(BoxOfficeSeatingService::class)->block($map, [$held->id], 'house', 'Held for the producer');

        $newRole = $this->roundTrip($role);
        $newPlan = SeatingPlan::where('role_id', $newRole->id)->firstOrFail();
        $map = EventSeatingMap::where('event_id', $this->restoredEvent($newRole)->id)->firstOrFail();

        $template = SeatingSeat::where('seating_plan_id', $newPlan->id)->get()->keyBy('id');
        $snapshot = SeatingSeat::where('event_seating_map_id', $map->id)->get();

        $this->assertCount(9, $snapshot);

        foreach ($snapshot as $seat) {
            $this->assertNotNull($seat->source_seat_id, "seat {$seat->seat_label} lost its template link");
            $source = $template->get($seat->source_seat_id);

            $this->assertNotNull($source, 'source_seat_id points outside the restored plan');
            // The same seat, not merely a seat: an off-by-one read-back would still resolve.
            $this->assertSame($source->seat_label, $seat->seat_label);
            $this->assertSame($source->kind, $seat->kind);
            $this->assertSame(
                $source->section->name,
                $seat->section->name,
                "seat {$seat->seat_label} was bound to a seat in another section"
            );
        }
    }

    public function test_the_restored_event_points_at_its_own_plan(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $plan = $this->makePlan($role);
        $this->seatedEvent($role, $plan);

        $newRole = $this->roundTrip($role);
        $newPlan = SeatingPlan::where('role_id', $newRole->id)->firstOrFail();
        $event = $this->restoredEvent($newRole);

        $this->assertSame($newPlan->id, (int) $event->seating_plan_id, 'not the original plan id');
        // ...and the bands still line up, so the tickets price real sections.
        $this->assertSame(['Stalls', 'Circle'], $event->tickets->pluck('seating_band')->all());
        $this->assertTrue($event->tickets->first()->isAllocated($event->saleEventDateFromStartsAt()));
    }

    public function test_a_sold_seat_comes_back_bound_to_its_restored_sale(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role));
        $map = app(SeatingMapService::class)->materialize($event);
        // All three from the SAME band (an order spanning two bands does not balance against a
        // single ticket line), and the whole block before the gangway, or the orphan rule refuses
        // the selection for leaving seat 3 on its own.
        $stalls = $map->sections()->where('name', 'Stalls')->value('id');
        $seats = SeatingSeat::where('event_seating_map_id', $map->id)
            ->where('seating_section_id', $stalls)->orderBy('position')->take(3)->get();

        $this->postJson(route('seating.hold', ['subdomain' => $role->subdomain]), [
            'event_id' => UrlUtils::encodeId($event->id),
            'date' => $event->saleEventDateFromStartsAt(),
            'seat_ids' => $seats->pluck('id')->all(),
        ])->assertOk();
        $this->post(route('event.checkout', ['subdomain' => $role->subdomain]), [
            'event_id' => UrlUtils::encodeId($event->id),
            'event_date' => $event->saleEventDateFromStartsAt(),
            'name' => 'Dana Rivers', 'email' => 'dana@gmail.com',
            'tickets' => [UrlUtils::encodeId($event->tickets->first()->id) => 3],
        ])->assertRedirect();

        $labels = Sale::latest('id')->firstOrFail()->saleTickets()->first()->seatLabels();
        $this->assertCount(3, $labels);

        $newRole = $this->roundTrip($role);
        $newEvent = $this->restoredEvent($newRole);
        $newMap = EventSeatingMap::where('event_id', $newEvent->id)->firstOrFail();

        $sold = SeatingSeat::where('event_seating_map_id', $newMap->id)->where('status', 'sold')->get();
        $this->assertCount(3, $sold, 'the sold seats must not go back on sale');

        $newSale = Sale::where('event_id', $newEvent->id)->firstOrFail();
        $this->assertSame('Dana Rivers', $newSale->name);
        foreach ($sold as $seat) {
            $this->assertSame($newSale->id, (int) $seat->sale_id);
            $this->assertNotNull($seat->sale_ticket_id, 'seats bind to the LINE, not just the sale');
        }

        // Same seats, by label, so the buyer's confirmation still matches the house.
        $this->assertSame($labels, $newSale->saleTickets()->first()->seatLabels());
    }

    public function test_the_snapshot_prices_its_sections_against_the_restored_tickets(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role));
        $map = app(SeatingMapService::class)->materialize($event);
        // A snapshot with nothing on it is skipped by the exporter on purpose (it is a copy of the
        // template and re-materializes on demand), so put a booking on it - which is the only
        // state in which its ticket_id has to survive a restore anyway.
        app(\App\Services\BoxOfficeSeatingService::class)->block($map, [
            SeatingSeat::where('event_seating_map_id', $map->id)->orderBy('position')->value('id'),
        ], 'house', 'Producer');

        $newRole = $this->roundTrip($role);
        $newEvent = $this->restoredEvent($newRole);
        $newMap = EventSeatingMap::where('event_id', $newEvent->id)->firstOrFail();

        $ticketIds = $newEvent->tickets->pluck('id')->all();
        $sections = SeatingSection::where('event_seating_map_id', $newMap->id)->get();

        $this->assertCount(2, $sections);
        foreach ($sections as $section) {
            $this->assertContains((int) $section->ticket_id, $ticketIds,
                'a section priced against the ORIGINAL ticket id would sell someone else a seat');
        }
    }

    public function test_a_staff_hold_survives_but_a_shoppers_cart_does_not(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role));
        $map = app(SeatingMapService::class)->materialize($event);
        $seats = SeatingSeat::where('event_seating_map_id', $map->id)->orderBy('position')->get();

        // A deliberate staff hold, with an internal note.
        app(BoxOfficeSeatingService::class)->block($map, [$seats[0]->id], 'house', 'Held for the producer');

        // ...and a live cart hold, which is a session artifact with a token nobody will ever hold again.
        $this->postJson(route('seating.hold', ['subdomain' => $role->subdomain]), [
            'event_id' => UrlUtils::encodeId($event->id),
            'date' => $event->saleEventDateFromStartsAt(),
            'seat_ids' => [$seats[1]->id],
        ])->assertOk();
        $this->assertSame('held', $seats[1]->fresh()->status);

        $newRole = $this->roundTrip($role);
        $newMap = EventSeatingMap::where('event_id', $this->restoredEvent($newRole)->id)->firstOrFail();
        $restored = SeatingSeat::where('event_seating_map_id', $newMap->id)->orderBy('position')->get();

        $this->assertSame('held', $restored[0]->status, 'a staff hold is a decision, not a session');
        $this->assertSame('house', $restored[0]->hold_kind);
        $this->assertSame('Held for the producer', $restored[0]->hold_note);
        $this->assertNull($restored[0]->hold_expires_at);

        $this->assertSame('available', $restored[1]->status, 'a dead cart must not hold a seat forever');
        $this->assertNull($restored[1]->hold_token);
    }

    public function test_the_export_carries_no_raw_seating_ids(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $plan = $this->makePlan($role);
        $event = $this->seatedEvent($role, $plan);
        app(SeatingMapService::class)->materialize($event);

        $job = BackupJob::create(['user_id' => $role->user->id, 'type' => 'export', 'status' => 'processing']);
        $data = $this->svc()->exportSchedules([$role->fresh()], false, $job)['json'];

        $eventData = $data['schedules'][0]['events'][0];
        $this->assertArrayNotHasKey('seating_plan_id', $eventData,
            'a raw plan id aborts the restore on the FK, or binds to a stranger plan on the same install');
        $this->assertSame($plan->id, $eventData['_seating_plan_ref_id']);

        // Hold tokens are session secrets and have no business in a file the operator downloads.
        $this->assertStringNotContainsString('hold_token', json_encode($data));
    }

    public function test_the_demo_data_seeds_a_usable_allocated_event(): void
    {
        // The demo is what a prospect clicks through before they buy Enterprise, so the seating
        // plan has to be real enough to open the picker, the console and the report on.
        $svc = app(\App\Services\DemoService::class);
        $user = $svc->getOrCreateDemoUser();
        $svc->populateDemoData($svc->getOrCreateDemoRole($user), false);

        $venue = Role::where('subdomain', 'demo-aztectheater')->firstOrFail();
        $plan = SeatingPlan::where('role_id', $venue->id)->firstOrFail();

        $this->assertSame('Aztec Auditorium', $plan->name);
        $this->assertSame(2, SeatingLevel::where('seating_plan_id', $plan->id)->count());

        // Stalls 6x12, Circle 3x10, plus a four-space accessible row.
        $this->assertSame(72 + 30 + 4, SeatingSeat::where('seating_plan_id', $plan->id)->count());
        $this->assertSame(2, SeatingSeat::where('seating_plan_id', $plan->id)->where('kind', 'wheelchair')->count());
        $this->assertSame(1, SeatingSection::where('seating_plan_id', $plan->id)->where('accessibility_only', true)->count());
        // A gangway in every row of both blocks, or a run could be offered straight across it.
        $this->assertSame(9, SeatingSeat::where('seating_plan_id', $plan->id)->where('aisle_after', true)->count());

        $event = Event::where('seating_plan_id', $plan->id)->firstOrFail();
        $this->assertSame(['Circle', 'Stalls'], $event->tickets->pluck('seating_band')->sort()->values()->all());
        $this->assertTrue($event->hasAllocatedSeating());

        // ...and the map materialises, which is what every seating surface calls first.
        $map = app(SeatingMapService::class)->materialize($event);
        $this->assertNotNull($map);
        $this->assertSame(106, SeatingSeat::where('event_seating_map_id', $map->id)->count());
    }

    public function test_untouched_dates_are_not_copied_into_the_backup(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role));
        $maps = app(SeatingMapService::class);

        // Three dates materialized, one of them sold on. A materialized map is a COPY of the
        // template, so exporting the untouched two is pure weight: a 1,200-seat house over 30
        // dates measured at 11.6 MB of JSON before this skip, and 0.4 MB after.
        $sold = $maps->materialize($event, $event->saleEventDateFromStartsAt());
        $maps->materialize($event, now()->addMonths(6)->addDays(7)->format('Y-m-d'));
        $maps->materialize($event, now()->addMonths(6)->addDays(14)->format('Y-m-d'));

        SeatingSeat::where('event_seating_map_id', $sold->id)
            ->orderBy('position')->limit(1)->update(['status' => 'sold']);

        $job = BackupJob::create(['user_id' => $role->user->id, 'type' => 'export', 'status' => 'processing']);
        $data = $this->svc()->exportSchedules([$role->fresh()], false, $job)['json'];
        $exported = $data['schedules'][0]['events'][0]['seating_maps'];

        $this->assertCount(1, $exported, 'only the date carrying something should travel');
        $this->assertSame($sold->event_date, $exported[0]['event_date']);

        // ...and the untouched dates come back anyway, because the plan reproduces them.
        $newRole = $this->roundTrip($role);
        $newEvent = $this->restoredEvent($newRole);
        $rebuilt = app(SeatingMapService::class)->materialize($newEvent, $newEvent->saleEventDateFromStartsAt());
        $this->assertSame(9, SeatingSeat::where('event_seating_map_id', $rebuilt->id)->count());
    }

    public function test_a_date_edited_on_its_own_still_travels(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->seatedEvent($role, $this->makePlan($role));
        $map = app(SeatingMapService::class)->materialize($event);

        // Nothing sold, but the front row came out for this date only. Skipping it would silently
        // restore the date to the template layout.
        SeatingSeat::where('event_seating_map_id', $map->id)->orderBy('position')->limit(2)->delete();

        $job = BackupJob::create(['user_id' => $role->user->id, 'type' => 'export', 'status' => 'processing']);
        $data = $this->svc()->exportSchedules([$role->fresh()], false, $job)['json'];

        $this->assertCount(1, $data['schedules'][0]['events'][0]['seating_maps']);

        $newRole = $this->roundTrip($role);
        $newMap = EventSeatingMap::where('event_id', $this->restoredEvent($newRole)->id)->firstOrFail();
        $this->assertSame(7, SeatingSeat::where('event_seating_map_id', $newMap->id)->count(),
            'the edited date must come back edited, not reset to the plan');
    }

    public function test_a_general_admission_schedule_is_unaffected(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->createEvent($role, ['creator_role_id' => $role->id, 'tickets_enabled' => true]);
        $this->createTicket($event, ['type' => 'General', 'quantity' => 10, 'price' => 10]);

        $newRole = $this->roundTrip($role);
        $newEvent = $this->restoredEvent($newRole);

        $this->assertNull($newEvent->seating_plan_id);
        $this->assertSame(0, SeatingPlan::where('role_id', $newRole->id)->count());
        $this->assertSame(0, EventSeatingMap::where('event_id', $newEvent->id)->count());
    }
}
