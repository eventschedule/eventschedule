<?php

namespace Tests\Feature;

use App\Models\BackupJob;
use App\Models\Event;
use App\Models\EventSeatingMap;
use App\Models\Role;
use App\Models\SeatingLevel;
use App\Models\SeatingPlan;
use App\Models\SeatingSeat;
use App\Models\SeatingSection;
use App\Models\SeatingTable;
use App\Repos\EventRepo;
use App\Services\BackupService;
use App\Services\SeatingMapService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Regressions for the eight defects the Phase 1 review found. Three of them were invisible to
 * the twelve tests Phase 1 shipped with, which is exactly how they survived it.
 *
 * The two that mattered most were both consequences of one decision: events.seating_plan_id is
 * fillable, so that EventRepo::buildClonePayload() carries it on clone. Everything else that
 * walks getFillable() picked it up too - BackupService on both sides, and the event form's
 * fill($request->all()).
 */
class SeatingReviewFixesTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private function service(): SeatingMapService
    {
        return app(SeatingMapService::class);
    }

    /** A minimal template: one level, Stalls with row A seats 1-2, and a table of 2. */
    private function makePlan(Role $role, string $name = 'Main House'): SeatingPlan
    {
        $plan = SeatingPlan::create(['role_id' => $role->id, 'name' => $name]);

        $level = SeatingLevel::create(['seating_plan_id' => $plan->id, 'name' => 'Ground']);

        $stalls = SeatingSection::create([
            'seating_plan_id' => $plan->id,
            'seating_level_id' => $level->id,
            'name' => 'Stalls',
            'kind' => 'seated',
        ]);
        foreach ([1, 2] as $n) {
            SeatingSeat::create([
                'seating_plan_id' => $plan->id,
                'seating_section_id' => $stalls->id,
                'row_label' => 'A',
                'row_position' => 1,
                'seat_label' => (string) $n,
                'position' => $n,
            ]);
        }

        $cabaret = SeatingSection::create([
            'seating_plan_id' => $plan->id,
            'seating_level_id' => $level->id,
            'name' => 'Cabaret',
            'kind' => 'table',
        ]);
        $table = SeatingTable::create([
            'seating_section_id' => $cabaret->id,
            'label' => 'Table 1',
            'seat_count' => 2,
        ]);
        foreach ([1, 2] as $n) {
            SeatingSeat::create([
                'seating_plan_id' => $plan->id,
                'seating_section_id' => $cabaret->id,
                'seating_table_id' => $table->id,
                'seat_label' => null,
                'position' => $n,
            ]);
        }

        return $plan->fresh();
    }

    /** Feature tests bypass plan gates, so a real denial needs hosted plus a free, expired schedule. */
    private function makeFree(Role $role): Role
    {
        config(['app.hosted' => true, 'app.is_testing' => false]);
        $role->plan_type = 'free';
        $role->plan_expires = now()->subYear()->format('Y-m-d');
        $role->save();

        return $role->fresh();
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

    // ---------------------------------------------------------------- finding 2

    public function test_a_non_enterprise_schedule_cannot_attach_a_seating_plan(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $plan = $this->makePlan($role);
        $role = $this->makeFree($role);

        $event = $this->save(['seating_plan_id' => $plan->id], null, $role);

        $this->assertNull($event->fresh()->seating_plan_id, 'allocated seating is Enterprise');
    }

    public function test_a_schedule_cannot_attach_another_schedules_seating_plan(): void
    {
        $mine = $this->createRole($this->createOwner(), 'venue');
        $theirs = $this->createRole($this->createOwner(), 'venue');
        $theirPlan = $this->makePlan($theirs, 'Their House');

        // Both Enterprise, so the plan gate is satisfied and only tenancy is under test.
        $event = $this->save(['seating_plan_id' => $theirPlan->id], null, $mine);

        $this->assertNull(
            $event->fresh()->seating_plan_id,
            'an organizer must not be able to render another venue seat map on their own event'
        );
    }

    public function test_a_lapsed_enterprise_schedule_keeps_the_plan_it_already_configured(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $plan = $this->makePlan($role);

        $event = $this->save(['seating_plan_id' => $plan->id], null, $role);
        $this->assertSame($plan->id, $event->fresh()->seating_plan_id, 'fixture sanity: Enterprise could set it');

        $role = $this->makeFree($role);
        $event = $this->save(['seating_plan_id' => $plan->id], $event->fresh(), $role);

        $this->assertSame(
            $plan->id,
            $event->fresh()->seating_plan_id,
            'clamping an unchanged value would silently unseat an event that has already sold'
        );
    }

    public function test_detaching_a_plan_is_always_allowed(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $plan = $this->makePlan($role);
        $event = $this->save(['seating_plan_id' => $plan->id], null, $role);

        $role = $this->makeFree($role);
        $event = $this->save(['seating_plan_id' => ''], $event->fresh(), $role);

        $this->assertNull($event->fresh()->seating_plan_id);
    }

    // ---------------------------------------------------------------- finding 1

    public function test_backup_never_writes_a_raw_plan_id_on_restore(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $plan = $this->makePlan($role);
        $event = $this->createEvent($role, ['tickets_enabled' => true, 'seating_plan_id' => $plan->id]);

        $svc = app(BackupService::class);

        $exportJob = BackupJob::create(['user_id' => $owner->id, 'type' => 'export', 'status' => 'processing']);
        $data = $svc->exportSchedules([$role->fresh()], false, $exportJob)['json'];

        $exported = $data['schedules'][0]['events'][0];
        // The RAW id must never travel. Unfixed, importEvent() writes it straight back and the
        // foreign key aborts the whole restore on another install - not just the seating part -
        // while on the same install it silently points the event at another schedule's map.
        $this->assertArrayNotHasKey('seating_plan_id', $exported);
        $this->assertSame($plan->id, $exported['_seating_plan_ref_id']);

        // Strip the plan from the file, leaving the reference behind. This is the shape a partial
        // or hand-edited backup has, and the id it names is not one this install can honour.
        $data['schedules'][0]['seating_plans'] = [];

        $importJob = BackupJob::create(['user_id' => $owner->id, 'type' => 'import', 'status' => 'processing']);
        $svc->importSchedules($data, [0], $owner->id, $importJob);

        $restored = Event::where('id', '!=', $event->id)->latest('id')->firstOrFail();
        $this->assertNull($restored->seating_plan_id, 'an unresolvable reference must become null');
    }

    public function test_a_full_backup_restores_the_plan_and_relinks_the_event(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $plan = $this->makePlan($role);
        $event = $this->createEvent($role, ['tickets_enabled' => true, 'seating_plan_id' => $plan->id]);

        $svc = app(BackupService::class);
        $exportJob = BackupJob::create(['user_id' => $owner->id, 'type' => 'export', 'status' => 'processing']);
        $data = $svc->exportSchedules([$role->fresh()], false, $exportJob)['json'];

        $importJob = BackupJob::create(['user_id' => $owner->id, 'type' => 'import', 'status' => 'processing']);
        $svc->importSchedules($data, [0], $owner->id, $importJob);

        $restored = Event::where('id', '!=', $event->id)->latest('id')->firstOrFail();
        $newPlan = \App\Models\SeatingPlan::where('id', '!=', $plan->id)->latest('id')->firstOrFail();

        $this->assertSame($newPlan->id, (int) $restored->seating_plan_id);
        $this->assertNotSame($plan->id, (int) $restored->seating_plan_id);
    }

    // ---------------------------------------------------------------- finding 3

    public function test_removing_a_section_stops_its_seats_counting_as_available(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $plan = $this->makePlan($role);
        $event = $this->createEvent($role, ['tickets_enabled' => true, 'seating_plan_id' => $plan->id]);
        $map = $this->service()->materialize($event);

        $this->assertSame(4, $this->service()->totalSeatCount($map), 'fixture sanity: 2 stalls + 2 chairs');
        $this->assertSame(4, $this->service()->availableSeatCount($map));

        // The box office drops the cabaret from this one date.
        $map->sections()->where('name', 'Cabaret')->update(['is_deleted' => true]);

        $this->assertSame(2, $this->service()->availableSeatCount($map), 'a removed section must stop selling');
        $this->assertSame(2, $this->service()->totalSeatCount($map));

        // The template agrees with its own snapshot, which is what copyStructure() already assumed.
        $plan->sections()->where('name', 'Cabaret')->update(['is_deleted' => true]);
        $this->assertSame(2, $plan->fresh()->seatCount());
    }

    // ---------------------------------------------------------------- finding 4

    public function test_a_table_seat_labels_correctly_without_eager_loading(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $plan = $this->makePlan($role);
        $event = $this->createEvent($role, ['tickets_enabled' => true, 'seating_plan_id' => $plan->id]);
        $map = $this->service()->materialize($event);

        // Fetched cold, exactly as a confirmation email or the scanner would.
        $id = SeatingSeat::where('event_seating_map_id', $map->id)->whereNotNull('seating_table_id')->value('id');
        $seat = SeatingSeat::find($id);

        $this->assertFalse($seat->relationLoaded('seatingTable'), 'fixture sanity: the relation is cold');
        $this->assertSame('Table 1', $seat->label(), 'a table seat has no row_label, so it used to render empty');
        $this->assertSame('Cabaret, Table 1', $seat->fullLabel());
    }

    // ---------------------------------------------------------------- finding 7

    public function test_rows_sort_naturally_past_twenty_six(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $plan = SeatingPlan::create(['role_id' => $role->id, 'name' => 'Big House']);
        $level = SeatingLevel::create(['seating_plan_id' => $plan->id, 'name' => 'Ground']);
        $section = SeatingSection::create([
            'seating_plan_id' => $plan->id,
            'seating_level_id' => $level->id,
            'name' => 'Stalls',
        ]);

        // Row B is the second row; AA is the twenty-seventh. Lexicographically AA sorts first.
        foreach ([['B', 2], ['AA', 27]] as [$label, $pos]) {
            SeatingSeat::create([
                'seating_plan_id' => $plan->id,
                'seating_section_id' => $section->id,
                'row_label' => $label,
                'row_position' => $pos,
                'seat_label' => '1',
                'position' => 1,
            ]);
        }

        $this->assertSame(
            ['B', 'AA'],
            $section->fresh()->seats->pluck('row_label')->all(),
            'row order feeds the orphan rule idea of which seats are adjacent'
        );
    }

    // ---------------------------------------------------------------- finding 5

    public function test_every_bump_hands_out_a_distinct_version(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $plan = $this->makePlan($role);
        $event = $this->createEvent($role, ['tickets_enabled' => true, 'seating_plan_id' => $plan->id]);
        $map = $this->service()->materialize($event);

        // A second handle on the same row, standing in for a second request. Two callers must
        // never come away with the same number or a client polling "> N" loses a whole batch.
        $other = EventSeatingMap::findOrFail($map->id);

        $claimed = [$map->bumpVersion(), $other->bumpVersion(), $map->bumpVersion(), DB::transaction(fn () => $other->bumpVersion())];

        $this->assertSame($claimed, array_unique($claimed), 'versions must be unique');
        $this->assertSame($claimed, [$claimed[0], $claimed[0] + 1, $claimed[0] + 2, $claimed[0] + 3]);
        $this->assertSame(end($claimed), (int) $map->fresh()->version);
    }

    // ---------------------------------------------------------------- finding 6

    public function test_revert_refuses_while_a_guest_is_holding_seats(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $plan = $this->makePlan($role);
        $event = $this->createEvent($role, ['tickets_enabled' => true, 'seating_plan_id' => $plan->id]);
        $map = $this->service()->materialize($event);

        $seat = SeatingSeat::where('event_seating_map_id', $map->id)->first();
        $seat->update([
            'status' => 'held',
            'hold_kind' => 'cart',
            'hold_token' => 'abc',
            'hold_expires_at' => now()->addMinutes(8),
        ]);

        $this->assertFalse($this->service()->revertToTemplate($map), 'a guest mid-checkout would lose their seats');

        // A staff block is not a reason to refuse: discarding per-date changes is the point.
        $seat->update(['status' => 'held', 'hold_kind' => 'house', 'hold_token' => null, 'hold_expires_at' => null]);
        $this->assertTrue($this->service()->revertToTemplate($map));
    }
}
