<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\SeatingLevel;
use App\Models\SeatingPlan;
use App\Models\SeatingSeat;
use App\Models\SeatingSection;
use App\Services\SeatingStructureService;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The designer posts a whole structure back at once, so the save path is the only thing standing
 * between an editor and every seat map on the install. These pin the two rules that matter: an id
 * you do not own is never adopted, and a sold seat cannot be deleted.
 */
class SeatingDesignerTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private function structureService(): SeatingStructureService
    {
        return app(SeatingStructureService::class);
    }

    private function payload(array $sections): array
    {
        return ['levels' => [[
            'id' => -1, 'name' => 'Ground', 'width' => 1200, 'height' => 800,
            'sections' => $sections,
        ]]];
    }

    private function section(array $attrs = [], array $seats = []): array
    {
        return array_merge([
            'id' => -2, 'name' => 'Stalls', 'color' => '#4E81FA', 'kind' => 'seated',
            'band' => 'Stalls', 'capacity' => null, 'accessibility_only' => false,
            'x' => 0, 'y' => 0, 'rotation' => 0, 'tables' => [], 'seats' => $seats,
        ], $attrs);
    }

    private function seat(int $id, string $row, int $pos, array $attrs = []): array
    {
        return array_merge([
            'id' => $id, 'table_id' => null, 'row_label' => $row, 'row_position' => 1,
            'seat_label' => (string) $pos, 'x' => $pos * 26, 'y' => 0,
            'kind' => 'standard', 'aisle_after' => false,
        ], $attrs);
    }

    public function test_the_designer_saves_a_structure_and_reads_it_back(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $plan = SeatingPlan::create(['role_id' => $role->id, 'name' => 'Main House']);

        $this->actingAs($owner)->putJson(
            route('seating.save_structure', ['subdomain' => $role->subdomain, 'hash' => UrlUtils::encodeId($plan->id)]),
            $this->payload([$this->section([], [$this->seat(-10, 'A', 1), $this->seat(-11, 'A', 2, ['aisle_after' => true])])])
        )->assertOk();

        $structure = $this->structureService()->toArray($plan->fresh());

        $this->assertCount(1, $structure['levels']);
        $this->assertSame('Ground', $structure['levels'][0]['name']);
        $section = $structure['levels'][0]['sections'][0];
        $this->assertSame('Stalls', $section['name']);
        $this->assertCount(2, $section['seats']);
        $this->assertTrue($section['seats'][1]['aisle_after']);
        // Temporary client ids became real rows.
        $this->assertGreaterThan(0, $section['seats'][0]['id']);
    }

    public function test_a_second_save_updates_rather_than_duplicating(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $plan = SeatingPlan::create(['role_id' => $role->id, 'name' => 'Main House']);
        $url = route('seating.save_structure', ['subdomain' => $role->subdomain, 'hash' => UrlUtils::encodeId($plan->id)]);

        $first = $this->actingAs($owner)->putJson($url,
            $this->payload([$this->section([], [$this->seat(-10, 'A', 1)])]))->json();

        // Post the structure straight back, exactly as the designer does after a save.
        $this->actingAs($owner)->putJson($url, ['levels' => $first['levels']])->assertOk();

        $this->assertSame(1, SeatingSeat::where('seating_plan_id', $plan->id)->count());
        $this->assertSame(1, SeatingSection::where('seating_plan_id', $plan->id)->where('is_deleted', false)->count());
        $this->assertSame(1, SeatingLevel::where('seating_plan_id', $plan->id)->count());
    }

    public function test_an_id_from_another_schedules_plan_is_never_adopted(): void
    {
        $mine = $this->createRole($this->createOwner(), 'venue');
        $theirs = $this->createRole($this->createOwner(), 'venue');

        $theirPlan = SeatingPlan::create(['role_id' => $theirs->id, 'name' => 'Their House']);
        $theirLevel = SeatingLevel::create(['seating_plan_id' => $theirPlan->id, 'name' => 'Theirs']);
        $theirSection = SeatingSection::create([
            'seating_plan_id' => $theirPlan->id, 'seating_level_id' => $theirLevel->id, 'name' => 'Their Stalls',
        ]);

        $myPlan = SeatingPlan::create(['role_id' => $mine->id, 'name' => 'My House']);

        // Hand-edited payload naming their level and section by real id.
        $this->actingAs($mine->user)->putJson(
            route('seating.save_structure', ['subdomain' => $mine->subdomain, 'hash' => UrlUtils::encodeId($myPlan->id)]),
            ['levels' => [[
                'id' => $theirLevel->id, 'name' => 'Hijacked', 'width' => 1200, 'height' => 800,
                'sections' => [$this->section(['id' => $theirSection->id, 'name' => 'Hijacked'])],
            ]]]
        )->assertOk();

        $this->assertSame('Theirs', $theirLevel->fresh()->name, 'their level must be untouched');
        $this->assertSame('Their Stalls', $theirSection->fresh()->name);
        $this->assertSame($theirPlan->id, $theirSection->fresh()->seating_plan_id);

        // Mine got its own brand-new rows instead.
        $this->assertSame(1, SeatingLevel::where('seating_plan_id', $myPlan->id)->count());
        $this->assertSame('Hijacked', SeatingLevel::where('seating_plan_id', $myPlan->id)->value('name'));
    }

    public function test_a_sold_seat_cannot_be_removed(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $plan = SeatingPlan::create(['role_id' => $role->id, 'name' => 'Main House']);
        $url = route('seating.save_structure', ['subdomain' => $role->subdomain, 'hash' => UrlUtils::encodeId($plan->id)]);

        $saved = $this->actingAs($owner)->putJson($url,
            $this->payload([$this->section([], [$this->seat(-10, 'A', 1), $this->seat(-11, 'A', 2)])]))->json();

        $seats = $saved['levels'][0]['sections'][0]['seats'];
        SeatingSeat::where('id', $seats[0]['id'])->update(['status' => 'sold']);

        // Post the structure back with the sold seat dropped.
        $levels = $saved['levels'];
        $levels[0]['sections'][0]['seats'] = [$seats[1]];

        $this->actingAs($owner)->putJson($url, ['levels' => $levels])
            ->assertStatus(422)
            ->assertJsonStructure(['error']);

        $this->assertSame(2, SeatingSeat::where('seating_plan_id', $plan->id)->count(), 'nothing is written on refusal');
        $this->assertSame('sold', SeatingSeat::find($seats[0]['id'])->status);
    }

    public function test_removing_a_section_that_holds_a_sold_seat_is_refused_too(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $plan = SeatingPlan::create(['role_id' => $role->id, 'name' => 'Main House']);
        $url = route('seating.save_structure', ['subdomain' => $role->subdomain, 'hash' => UrlUtils::encodeId($plan->id)]);

        $saved = $this->actingAs($owner)->putJson($url,
            $this->payload([$this->section([], [$this->seat(-10, 'A', 1)])]))->json();

        SeatingSeat::where('seating_plan_id', $plan->id)->update(['status' => 'sold']);

        $levels = $saved['levels'];
        $levels[0]['sections'] = [];

        $this->actingAs($owner)->putJson($url, ['levels' => $levels])->assertStatus(422);
        $this->assertSame(1, SeatingSection::where('seating_plan_id', $plan->id)->where('is_deleted', false)->count());
    }

    public function test_an_oversized_plan_is_rejected_before_anything_is_written(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $plan = SeatingPlan::create(['role_id' => $role->id, 'name' => 'Main House']);

        $seats = [];
        for ($i = 0; $i < SeatingStructureService::MAX_SEATS + 1; $i++) {
            $seats[] = $this->seat(-1000 - $i, 'A', $i);
        }

        $this->actingAs($owner)->putJson(
            route('seating.save_structure', ['subdomain' => $role->subdomain, 'hash' => UrlUtils::encodeId($plan->id)]),
            $this->payload([$this->section([], $seats)])
        )->assertStatus(422);

        $this->assertSame(0, SeatingSeat::where('seating_plan_id', $plan->id)->count());
    }

    public function test_duplicating_a_plan_copies_its_whole_structure(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $plan = SeatingPlan::create(['role_id' => $role->id, 'name' => 'Main House']);

        $this->actingAs($owner)->putJson(
            route('seating.save_structure', ['subdomain' => $role->subdomain, 'hash' => UrlUtils::encodeId($plan->id)]),
            $this->payload([$this->section([], [$this->seat(-10, 'A', 1), $this->seat(-11, 'A', 2)])])
        )->assertOk();

        $this->actingAs($owner)->post(
            route('seating.duplicate', ['subdomain' => $role->subdomain, 'hash' => UrlUtils::encodeId($plan->id)])
        )->assertRedirect();

        $copy = SeatingPlan::where('role_id', $role->id)->where('id', '!=', $plan->id)->firstOrFail();

        $this->assertSame('Copy of Main House', $copy->name);
        $this->assertSame(2, $copy->seatCount());
        $this->assertSame(2, $plan->fresh()->seatCount(), 'the original is untouched');
        $this->assertSame(0, SeatingSeat::where('seating_plan_id', $copy->id)
            ->whereIn('seating_section_id', SeatingSection::where('seating_plan_id', $plan->id)->select('id'))
            ->count(), 'the copy points at its own sections');
    }

    public function test_a_non_enterprise_schedule_is_refused_every_write(): void
    {
        // Only app.hosted, deliberately: clearing app.is_testing as well makes
        // RedirectToAppSubdomain bounce every request to the app.* host, and the 302 looks
        // exactly like a passing plan gate. Role::isEnterprise() never reads is_testing.
        config(['app.hosted' => true]);
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', [
            'plan_type' => 'free',
            'plan_expires' => now()->subYear()->format('Y-m-d'),
        ]);
        $plan = SeatingPlan::create(['role_id' => $role->id, 'name' => 'Main House']);
        $hash = UrlUtils::encodeId($plan->id);

        $this->actingAs($owner)->post(route('seating.store', ['subdomain' => $role->subdomain]), ['name' => 'X'])->assertStatus(403);
        $this->actingAs($owner)->get(route('seating.design', ['subdomain' => $role->subdomain, 'hash' => $hash]))->assertStatus(403);
        $this->actingAs($owner)->putJson(
            route('seating.save_structure', ['subdomain' => $role->subdomain, 'hash' => $hash]),
            $this->payload([$this->section()])
        )->assertStatus(403);
    }

    /**
     * The designer page has to actually RENDER, not merely authorise.
     *
     * Every other test here either got a 403 or posted JSON, so none of them ever compiled the
     * view - and it shipped with a `use` statement inside an @php block that sits in a component
     * slot, which Blade turns into a closure body. That is a PHP syntax error, so the page 500'd
     * for everyone while the suite stayed green.
     */
    public function test_the_designer_page_renders_and_hands_the_component_its_props(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $plan = SeatingPlan::create(['role_id' => $role->id, 'name' => 'Grand Theatre']);

        $html = $this->actingAs($owner)
            ->get(route('seating.design', ['subdomain' => $role->subdomain, 'hash' => UrlUtils::encodeId($plan->id)]))
            ->assertOk()
            ->assertSee('id="seating-designer"', false)
            ->assertSee('Grand Theatre')
            ->getContent();

        // The mount point carries its props, and they point at this plan's own endpoints.
        preg_match('/data-props="([^"]*)"/', $html, $m);
        $this->assertNotEmpty($m, 'the component gets no props');
        $props = json_decode(html_entity_decode($m[1], ENT_QUOTES), true);

        $this->assertSame('Grand Theatre', $props['planName']);
        $this->assertStringContainsString('/structure', $props['structureUrl']);
        $this->assertNotEmpty($props['csrfToken']);
        // Path-relative, so the designer's fetch stays same-origin on a custom domain.
        $this->assertStringStartsWith('/', $props['structureUrl']);
        // A handful of spot checks that the string map resolved rather than shipping raw keys.
        $this->assertSame(__('messages.seating_generate_rows'), $props['strings']['generateRows']);
        $this->assertStringNotContainsString('messages.', implode(' ', $props['strings']));
    }

    // ------------------------------------------------ review finding 1: name handling

    public function test_an_over_long_name_is_clamped_rather_than_500ing(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $long = str_repeat('a', 400);

        // store
        $this->actingAs($owner)
            ->post(route('seating.store', ['subdomain' => $role->subdomain]), ['name' => $long])
            ->assertRedirect();

        $plan = SeatingPlan::where('role_id', $role->id)->firstOrFail();
        $this->assertSame(255, mb_strlen($plan->name), 'seating_plans.name is varchar(255) and MySQL runs strict');

        $hash = UrlUtils::encodeId($plan->id);

        // update
        $this->actingAs($owner)
            ->put(route('seating.update', ['subdomain' => $role->subdomain, 'hash' => $hash]), ['name' => $long])
            ->assertRedirect();
        $this->assertSame(255, mb_strlen($plan->fresh()->name));

        // saveStructure carries the name too
        $this->actingAs($owner)->putJson(
            route('seating.save_structure', ['subdomain' => $role->subdomain, 'hash' => $hash]),
            ['name' => $long, 'levels' => []]
        )->assertOk();
        $this->assertSame(255, mb_strlen($plan->fresh()->name));

        // duplicate prepends "Copy of ", which GROWS an already-maximal name
        $this->actingAs($owner)
            ->post(route('seating.duplicate', ['subdomain' => $role->subdomain, 'hash' => $hash]))
            ->assertRedirect();
        $copy = SeatingPlan::where('role_id', $role->id)->where('id', '!=', $plan->id)->firstOrFail();
        $this->assertSame(255, mb_strlen($copy->name));
    }

    public function test_an_array_name_does_not_throw(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');

        // ?name[]=x makes the input an ARRAY, which used to TypeError inside trim() before any
        // validation ran - the same shape as the ?lang[]= incident.
        $this->actingAs($owner)
            ->post(route('seating.store', ['subdomain' => $role->subdomain]), ['name' => ['a', 'b']])
            ->assertRedirect();

        $plan = SeatingPlan::where('role_id', $role->id)->firstOrFail();
        $this->assertSame(__('messages.seating_untitled_plan'), $plan->name);
    }

    // ------------------------------------------------ review finding 4: seat position

    public function test_a_seats_own_number_survives_a_save(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $plan = SeatingPlan::create(['role_id' => $role->id, 'name' => 'Main House']);

        // Two rows of three. Each seat's position is its index WITHIN its row, exactly as the
        // designer's row builder emits it.
        $seats = [];
        $id = -10;
        foreach ([['A', 1], ['B', 2]] as [$row, $rp]) {
            foreach ([1, 2, 3] as $n) {
                $seats[] = $this->seat($id--, $row, $n, ['row_position' => $rp, 'position' => $n]);
            }
        }

        $this->actingAs($owner)->putJson(
            route('seating.save_structure', ['subdomain' => $role->subdomain, 'hash' => UrlUtils::encodeId($plan->id)]),
            $this->payload([$this->section([], $seats)])
        )->assertOk();

        $stored = SeatingSeat::where('seating_plan_id', $plan->id)
            ->orderBy('row_position')->orderBy('position')->get();

        $this->assertSame([1, 2, 3, 1, 2, 3], $stored->pluck('position')->all(),
            'a flat array index would give 0..5 and lose the seat number within the row');
        $this->assertSame(['A', 'A', 'A', 'B', 'B', 'B'], $stored->pluck('row_label')->all());
    }

    // ------------------------------------------------ review finding 5: limits in save()

    public function test_duplicating_cannot_bypass_the_size_limit(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $plan = SeatingPlan::create(['role_id' => $role->id, 'name' => 'Main House']);

        // Built past the cap directly, the way a legacy row or a future caller could be.
        $level = SeatingLevel::create(['seating_plan_id' => $plan->id, 'name' => 'Ground']);
        $section = SeatingSection::create([
            'seating_plan_id' => $plan->id, 'seating_level_id' => $level->id, 'name' => 'Stalls',
        ]);
        $rows = [];
        $now = now();
        for ($i = 0; $i <= SeatingStructureService::MAX_SEATS; $i++) {
            $rows[] = [
                'seating_plan_id' => $plan->id, 'seating_section_id' => $section->id,
                'row_label' => 'A', 'row_position' => 1, 'seat_label' => (string) $i,
                'position' => $i, 'status' => 'available', 'created_at' => $now, 'updated_at' => $now,
            ];
        }
        foreach (array_chunk($rows, 1000) as $chunk) {
            SeatingSeat::insert($chunk);
        }

        // duplicate() calls save() directly, so the check has to live inside save() to catch it.
        $this->expectException(\App\Exceptions\BusinessException::class);
        $this->structureService()->save(
            SeatingPlan::create(['role_id' => $role->id, 'name' => 'Copy']),
            $this->structureService()->toArray($plan->fresh())
        );
    }

    // ------------------------------------------------ review finding 3: viewers

    public function test_a_viewer_is_not_shown_actions_that_would_403(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        SeatingPlan::create(['role_id' => $role->id, 'name' => 'Grand Theatre']);

        $viewer = $this->createOwner();
        $role->users()->attach($viewer->id, ['level' => 'viewer']);

        $this->actingAs($viewer)
            ->get(route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'seating']))
            ->assertOk()
            ->assertSee('Grand Theatre')
            ->assertDontSee(__('messages.seating_new_plan'))
            ->assertDontSee(__('messages.seating_duplicate'))
            ->assertDontSee(__('messages.seating_open_designer'));

        // And the endpoints themselves still refuse.
        $this->actingAs($viewer)
            ->post(route('seating.store', ['subdomain' => $role->subdomain]), ['name' => 'X'])
            ->assertStatus(403);
    }

    public function test_deleting_a_plan_is_soft_and_hides_it(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $plan = SeatingPlan::create(['role_id' => $role->id, 'name' => 'Grand Theatre']);

        $this->actingAs($owner)
            ->delete(route('seating.destroy', ['subdomain' => $role->subdomain, 'hash' => UrlUtils::encodeId($plan->id)]))
            ->assertRedirect();

        // Soft only: an occurrence that already snapshotted this template keeps seating_plan_id as
        // provenance, and the plan report reads the name back through it.
        $this->assertTrue((bool) $plan->fresh()->is_deleted);
        $this->assertNotNull(SeatingPlan::find($plan->id));

        $this->actingAs($owner)
            ->get(route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'seating']))
            ->assertOk()
            ->assertDontSee('Grand Theatre');

        // And it is no longer reachable by hash.
        $this->actingAs($owner)
            ->get(route('seating.design', ['subdomain' => $role->subdomain, 'hash' => UrlUtils::encodeId($plan->id)]))
            ->assertStatus(404);
    }

    public function test_a_stranger_cannot_reach_the_designer(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $plan = SeatingPlan::create(['role_id' => $role->id, 'name' => 'Main House']);
        $stranger = $this->createOwner();

        $this->actingAs($stranger)->get(
            route('seating.design', ['subdomain' => $role->subdomain, 'hash' => UrlUtils::encodeId($plan->id)])
        )->assertStatus(403);
    }

    public function test_the_seating_tab_renders_the_plan_list(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        SeatingPlan::create(['role_id' => $role->id, 'name' => 'Grand Theatre']);

        $this->actingAs($owner)
            ->get(route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'seating']))
            ->assertOk()
            ->assertSee('Grand Theatre');
    }

    public function test_the_tab_shows_the_upgrade_pitch_below_enterprise(): void
    {
        // Only app.hosted, deliberately: clearing app.is_testing as well makes
        // RedirectToAppSubdomain bounce every request to the app.* host, and the 302 looks
        // exactly like a passing plan gate. Role::isEnterprise() never reads is_testing.
        config(['app.hosted' => true]);
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', [
            'plan_type' => 'free',
            'plan_expires' => now()->subYear()->format('Y-m-d'),
        ]);

        $this->actingAs($owner)
            ->get(route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'seating']))
            ->assertOk()
            ->assertSee(__('messages.seating_gate_designer'))
            ->assertDontSee(__('messages.seating_new_plan'));
    }
}
