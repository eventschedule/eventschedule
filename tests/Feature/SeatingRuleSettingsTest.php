<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventSeatingMap;
use App\Models\Role;
use App\Models\SeatingLevel;
use App\Models\SeatingPlan;
use App\Models\SeatingSeat;
use App\Models\SeatingSection;
use App\Repos\EventRepo;
use App\Services\SeatHoldService;
use App\Services\SeatingMapService;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The single-seat rule's settings.
 *
 * They have been enforced on every guest selection since the feature shipped, with no writer and no
 * screen anywhere - the user guide documented the absence as a limitation: "The single-seat rule
 * has no setting. It is on for every allocated event." A room where single seats are normal, a bar
 * or a comedy club, had no way to sell one.
 */
class SeatingRuleSettingsTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    /** One row of five: taking the middle three strands seat 1 and seat 5. */
    private function plan(Role $role): SeatingPlan
    {
        $plan = SeatingPlan::create(['role_id' => $role->id, 'name' => 'Bar']);
        $level = SeatingLevel::create(['seating_plan_id' => $plan->id, 'name' => 'Floor']);
        $section = SeatingSection::create([
            'seating_plan_id' => $plan->id, 'seating_level_id' => $level->id,
            'name' => 'Stools', 'band' => 'Stools', 'kind' => 'seated', 'position' => 0,
        ]);
        foreach (range(1, 5) as $n) {
            SeatingSeat::create([
                'seating_plan_id' => $plan->id, 'seating_section_id' => $section->id,
                'row_label' => 'A', 'row_position' => 1, 'seat_label' => (string) $n,
                'position' => $n, 'x' => $n * 26,
            ]);
        }

        return $plan->fresh();
    }

    private function event(Role $role, SeatingPlan $plan): Event
    {
        $request = Request::create('/', 'POST', [
            'name' => 'Comedy Night',
            'starts_at' => now()->addMonth()->setTime(20, 0)->format('Y-m-d H:i:s'),
            'tickets_enabled' => 1, 'payment_method' => 'cash', 'ticket_currency_code' => 'USD',
            'creator_role_id' => $role->id, 'seating_plan_id' => $plan->id,
            'tickets' => [['type' => 'Stools', 'price' => 10, 'quantity' => 999, 'seating_band' => 'Stools']],
        ]);
        $request->setUserResolver(fn () => $role->user);
        $event = app(EventRepo::class)->saveEvent($role, $request)->fresh();
        $event->roles()->updateExistingPivot($role->id, ['is_accepted' => true]);

        return $event->fresh();
    }

    private function save(Role $role, SeatingPlan $plan, array $extra)
    {
        return $this->actingAs($role->user)->putJson(
            route('seating.save_structure', ['subdomain' => $role->subdomain, 'hash' => UrlUtils::encodeId($plan->id)]),
            array_merge(['levels' => app(\App\Services\SeatingStructureService::class)->toArray($plan)['levels']], $extra)
        );
    }

    public function test_the_designer_reads_and_writes_the_rule_settings(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $plan = $this->plan($role);

        // Defaults, as every existing plan has them.
        $payload = $this->actingAs($role->user)->getJson(route('seating.structure', [
            'subdomain' => $role->subdomain, 'hash' => UrlUtils::encodeId($plan->id),
        ]))->assertOk()->json();

        $this->assertTrue($payload['rules']['orphan_rule_enabled']);
        $this->assertSame(1, $payload['rules']['orphan_rule_min_gap']);
        $this->assertSame(90, $payload['rules']['orphan_rule_lift_pct']);

        $this->save($role, $plan, [
            'orphan_rule_enabled' => false,
            'orphan_rule_min_gap' => 2,
            'orphan_rule_lift_pct' => 50,
        ])->assertOk();

        $plan->refresh();
        $this->assertFalse((bool) $plan->orphan_rule_enabled);
        $this->assertSame(2, (int) $plan->orphan_rule_min_gap);
        $this->assertSame(50, (int) $plan->orphan_rule_lift_pct);
    }

    public function test_a_payload_without_the_settings_leaves_them_alone(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $plan = $this->plan($role);
        $plan->forceFill(['orphan_rule_enabled' => false])->save();

        // "Not posted = preserve", the same rule seating_plan_id and seating_band follow. An older
        // client must not silently reset a venue's decision to the column default.
        $this->save($role, $plan, [])->assertOk();

        $this->assertFalse((bool) $plan->fresh()->orphan_rule_enabled);
    }

    public function test_out_of_range_settings_are_clamped_rather_than_stored(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $plan = $this->plan($role);

        $this->save($role, $plan, [
            'orphan_rule_enabled' => true,
            'orphan_rule_min_gap' => 99,
            'orphan_rule_lift_pct' => 999,
        ])->assertOk();

        $plan->refresh();
        // Above 4 the rule refuses ordinary selections; the column is a tinyint either way.
        $this->assertSame(4, (int) $plan->orphan_rule_min_gap);
        $this->assertSame(100, (int) $plan->orphan_rule_lift_pct);
    }

    public function test_a_new_date_inherits_the_rooms_decision(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $plan = $this->plan($role);
        $plan->forceFill(['orphan_rule_enabled' => false, 'orphan_rule_min_gap' => 3])->save();

        $event = $this->event($role, $plan->fresh());
        $map = app(SeatingMapService::class)->materialize($event, $event->saleEventDateFromStartsAt());

        $this->assertFalse((bool) $map->orphan_rule_enabled, 'a date must not turn the rule back on');
        $this->assertSame(3, (int) $map->orphan_rule_min_gap);
    }

    public function test_turning_the_rule_off_lets_a_buyer_strand_a_seat(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $plan = $this->plan($role);
        $event = $this->event($role, $plan);
        $date = $event->saleEventDateFromStartsAt();

        $map = app(SeatingMapService::class)->materialize($event, $date);
        $middle = SeatingSeat::where('event_seating_map_id', $map->id)
            ->whereIn('position', [2, 3, 4])->pluck('id')->all();

        // On by default: seats 1 and 5 would be left alone, so the buyer is warned.
        $warned = app(SeatHoldService::class)->acquire($map, $middle, 'tok-on', null, false, true);
        $this->assertNotNull($warned['warning'], 'the rule must fire while it is on');

        // Let the first hold go first: acquire() only releases the calling TOKEN's own holds, so a
        // second token would find these seats taken and never reach the rule at all.
        app(SeatHoldService::class)->release($map, 'tok-on');

        // Off: the same selection is simply accepted, which is the whole point of the setting.
        EventSeatingMap::whereKey($map->id)->update(['orphan_rule_enabled' => false]);
        $quiet = app(SeatHoldService::class)->acquire($map->fresh(), $middle, 'tok-off', null, false, true);
        $this->assertNull($quiet['warning'], 'the rule must be silent once turned off');
    }
}
