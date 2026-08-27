<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Role;
use App\Models\SeatingDecoration;
use App\Models\SeatingPlan;
use App\Repos\EventRepo;
use App\Services\SeatingMapService;
use App\Services\SeatingStructureService;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Stages and text labels.
 *
 * A seat map had no way to say which end of the room the audience faces. Every competing product
 * draws the stage, because a grid of circles with no orientation cannot tell a buyer the front row
 * from the back - and an organizer who angles the side blocks toward a focal point was drawing a
 * room whose focal point was invisible to everyone.
 *
 * A decoration is never inventory and never interactive. What it has to do is reach all four
 * renderers and survive a snapshot and a backup, which is the copy-site class that has caught this
 * feature three times.
 */
class SeatingDecorationTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private function structure(): SeatingStructureService
    {
        return app(SeatingStructureService::class);
    }

    private function payload(array $decorations, array $sections = []): array
    {
        return ['levels' => [[
            'id' => -1, 'name' => 'Ground', 'width' => 1200, 'height' => 800,
            'decorations' => $decorations,
            'sections' => $sections ?: [[
                'id' => -2, 'name' => 'Stalls', 'color' => '#4E81FA', 'kind' => 'seated',
                'band' => 'Stalls', 'capacity' => null, 'accessibility_only' => false,
                'x' => 0, 'y' => 0, 'rotation' => 0, 'tables' => [],
                'seats' => [[
                    'id' => -10, 'table_id' => null, 'row_label' => 'A', 'row_position' => 1,
                    'seat_label' => '1', 'x' => 26, 'y' => 0, 'kind' => 'standard', 'aisle_after' => false,
                ]],
            ]],
        ]]];
    }

    private function stage(array $attrs = []): array
    {
        return array_merge([
            'id' => -50, 'kind' => 'stage', 'label' => 'STAGE',
            'x' => 40, 'y' => -80, 'width' => 320, 'height' => 40, 'rotation' => 0,
        ], $attrs);
    }

    private function save(Role $role, SeatingPlan $plan, array $body)
    {
        return $this->actingAs($role->user)->putJson(
            route('seating.save_structure', ['subdomain' => $role->subdomain, 'hash' => UrlUtils::encodeId($plan->id)]),
            $body
        );
    }

    private function seatedEvent(Role $role, SeatingPlan $plan): Event
    {
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

    public function test_the_designer_saves_a_stage_and_reads_it_back(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $plan = SeatingPlan::create(['role_id' => $role->id, 'name' => 'Main House']);

        $this->save($role, $plan, $this->payload([
            $this->stage(),
            ['id' => -51, 'kind' => 'text', 'label' => 'BAR', 'x' => 500, 'y' => 300, 'width' => 120, 'height' => 24, 'rotation' => 15],
        ]))->assertOk();

        $level = $this->structure()->toArray($plan->fresh())['levels'][0];

        $this->assertCount(2, $level['decorations']);
        $this->assertSame('stage', $level['decorations'][0]['kind']);
        $this->assertSame('STAGE', $level['decorations'][0]['label']);
        $this->assertSame(-80, $level['decorations'][0]['y']);
        $this->assertSame('text', $level['decorations'][1]['kind']);
        $this->assertSame(15, $level['decorations'][1]['rotation']);
        // Temporary client ids became real rows, like every other child of a level.
        $this->assertGreaterThan(0, $level['decorations'][0]['id']);
    }

    public function test_a_decoration_dropped_from_the_payload_is_removed(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $plan = SeatingPlan::create(['role_id' => $role->id, 'name' => 'Main House']);

        $this->save($role, $plan, $this->payload([$this->stage()]))->assertOk();
        $this->assertSame(1, SeatingDecoration::where('seating_plan_id', $plan->id)->count());

        $this->save($role, $plan, $this->payload([]))->assertOk();
        $this->assertSame(0, SeatingDecoration::where('seating_plan_id', $plan->id)->count());
    }

    /**
     * An EMPTY decorations list means "delete them"; an ABSENT one means "I was not editing those".
     *
     * The two used to be the same thing, so any client that posted a structure without the key -
     * an older build, a partial save, anything scripted against the endpoint - silently wiped every
     * stage marker and label on the plan.
     */
    public function test_a_payload_that_never_mentions_decorations_leaves_them_alone(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $plan = SeatingPlan::create(['role_id' => $role->id, 'name' => 'Main House']);

        $this->save($role, $plan, $this->payload([$this->stage()]))->assertOk();
        $this->assertSame(1, SeatingDecoration::where('seating_plan_id', $plan->id)->count());

        // Re-post the SAME level, or it is recreated and the decoration dies by FK cascade rather
        // than by the rule under test.
        $levelId = \App\Models\SeatingLevel::where('seating_plan_id', $plan->id)->value('id');
        $sectionId = \App\Models\SeatingSection::where('seating_plan_id', $plan->id)->value('id');

        $silent = $this->payload([]);
        $silent['levels'][0]['id'] = $levelId;
        $silent['levels'][0]['sections'][0]['id'] = $sectionId;
        unset($silent['levels'][0]['decorations']);

        $this->save($role, $plan, $silent)->assertOk();
        $this->assertSame(1, SeatingDecoration::where('seating_plan_id', $plan->id)->count(),
            'a payload that says nothing about decorations must not delete them');
    }

    public function test_an_unknown_kind_falls_back_to_a_stage_rather_than_being_stored(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $plan = SeatingPlan::create(['role_id' => $role->id, 'name' => 'Main House']);

        $this->save($role, $plan, $this->payload([$this->stage(['kind' => 'anything'])]))->assertOk();

        $this->assertSame('stage', SeatingDecoration::where('seating_plan_id', $plan->id)->first()->kind);
    }

    public function test_the_snapshot_copies_the_stage(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $plan = SeatingPlan::create(['role_id' => $role->id, 'name' => 'Main House']);
        $this->save($role, $plan, $this->payload([$this->stage()]))->assertOk();

        $event = $this->seatedEvent($role, $plan->fresh());
        $map = app(SeatingMapService::class)->materialize($event, $event->saleEventDateFromStartsAt());

        $copy = SeatingDecoration::where('event_seating_map_id', $map->id)->first();

        $this->assertNotNull($copy, 'a snapshot without the stage is a different room from the one drawn');
        $this->assertSame('STAGE', $copy->label);
        // The XOR the whole schema rests on.
        $this->assertNull($copy->seating_plan_id);
    }

    public function test_the_buyer_is_told_which_way_the_room_faces(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $plan = SeatingPlan::create(['role_id' => $role->id, 'name' => 'Main House']);
        $this->save($role, $plan, $this->payload([$this->stage()]))->assertOk();
        $event = $this->seatedEvent($role, $plan->fresh());

        $payload = $this->getJson(route('seating.state', [
            'subdomain' => $role->subdomain,
            'event_id' => UrlUtils::encodeId($event->id),
            'date' => $event->saleEventDateFromStartsAt(),
        ]))->assertOk()->json();

        $decorations = $payload['levels'][0]['decorations'];
        $this->assertCount(1, $decorations);
        $this->assertSame('STAGE', $decorations[0]['label']);
        $this->assertSame(320, $decorations[0]['width']);
    }

    public function test_the_box_office_and_the_printed_sheet_both_draw_it(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $plan = SeatingPlan::create(['role_id' => $role->id, 'name' => 'Main House']);
        $this->save($role, $plan, $this->payload([$this->stage()]))->assertOk();
        $event = $this->seatedEvent($role, $plan->fresh());

        $args = ['subdomain' => $role->subdomain, 'hash' => UrlUtils::encodeId($event->id)];

        $console = $this->actingAs($owner)->getJson(route('box_office.state', $args))->assertOk()->json();
        $this->assertSame('STAGE', $console['levels'][0]['decorations'][0]['label']);

        $report = $this->actingAs($owner)->get(route('box_office.report', $args))->assertOk();
        $level = $report->viewData('levels')[0];
        $this->assertSame('STAGE', $level['decorations'][0]['label']);

        // Framed in, not cropped out. The stage sits at y=-80 while the only seat is at y=0, so a
        // viewBox fitted to the seats alone starts below it and prints a sheet with no stage on it.
        [, $minY] = array_map('intval', explode(' ', $level['viewBox']));
        $this->assertLessThanOrEqual(-80, $minY, 'the sheet must frame the stage, not crop it');
    }

    public function test_a_backup_carries_the_stage_on_both_owners(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $plan = SeatingPlan::create(['role_id' => $role->id, 'name' => 'Main House']);
        $this->save($role, $plan, $this->payload([$this->stage()]))->assertOk();
        $event = $this->seatedEvent($role, $plan->fresh());
        app(SeatingMapService::class)->materialize($event, $event->saleEventDateFromStartsAt());

        $job = \App\Models\BackupJob::create(['user_id' => $owner->id, 'type' => 'export', 'status' => 'processing']);
        $backup = app(\App\Services\BackupService::class)->exportSchedules([$role->fresh()], false, $job)['json'];

        $planned = $backup['schedules'][0]['seating_plans'][0]['decorations'] ?? [];
        $this->assertCount(1, $planned, 'the template must carry its stage');
        $this->assertSame('STAGE', $planned[0]['label']);
        $this->assertArrayHasKey('_level_ref_id', $planned[0], 'the parent link must travel as a ref, never a raw id');
    }

    /**
     * A restore must not switch the single-seat rule back on for a room that turned it off.
     *
     * These three columns were absent from both halves of the backup, so a venue that had disabled
     * the rule got it back at full strength - and because materialize() seeds every occurrence from
     * the plan, every date created after the restore inherited the wrong setting too.
     */
    public function test_a_backup_carries_the_rooms_selling_rules(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $plan = SeatingPlan::create([
            'role_id' => $role->id, 'name' => 'Comedy Club',
            // A bar or a comedy club is exactly the room that turns this off.
            'orphan_rule_enabled' => false, 'orphan_rule_min_gap' => 2, 'orphan_rule_lift_pct' => 75,
        ]);
        $this->save($role, $plan, $this->payload([]))->assertOk();

        $svc = app(\App\Services\BackupService::class);
        $exportJob = \App\Models\BackupJob::create(['user_id' => $owner->id, 'type' => 'export', 'status' => 'processing']);
        $data = $svc->exportSchedules([$role->fresh()], false, $exportJob)['json'];

        $exported = $data['schedules'][0]['seating_plans'][0];
        $this->assertFalse($exported['orphan_rule_enabled']);
        $this->assertSame(2, $exported['orphan_rule_min_gap']);
        $this->assertSame(75, $exported['orphan_rule_lift_pct']);

        $importJob = \App\Models\BackupJob::create(['user_id' => $owner->id, 'type' => 'import', 'status' => 'processing']);
        $svc->importSchedules($data, [0], $owner->id, $importJob);

        $newRole = \App\Models\Role::where('user_id', $owner->id)->where('id', '!=', $role->id)->latest('id')->firstOrFail();
        $restored = SeatingPlan::where('role_id', $newRole->id)->firstOrFail();

        $this->assertFalse((bool) $restored->orphan_rule_enabled, 'a rule turned off must stay off');
        $this->assertSame(2, (int) $restored->orphan_rule_min_gap);
        $this->assertSame(75, (int) $restored->orphan_rule_lift_pct);
    }

    public function test_the_cap_names_decorations_rather_than_seats(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $plan = SeatingPlan::create(['role_id' => $role->id, 'name' => 'Main House']);

        $many = [];
        for ($i = 0; $i < SeatingStructureService::MAX_DECORATIONS + 1; $i++) {
            $many[] = $this->stage(['id' => -100 - $i, 'label' => 'S'.$i]);
        }

        $response = $this->save($role, $plan, $this->payload($many))->assertStatus(422);

        $this->assertStringContainsString(
            (string) SeatingStructureService::MAX_DECORATIONS,
            $response->json('error') ?? ''
        );
        $this->assertSame(0, SeatingDecoration::where('seating_plan_id', $plan->id)->count());
    }
}
