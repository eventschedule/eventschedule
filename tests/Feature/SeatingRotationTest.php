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
 * A rotated section has to reach every renderer, not just the one it was drawn in.
 *
 * SeatingPlanDesigner applied rotate() when drawing. SeatingPicker received `rotation` in its
 * payload and ignored it, the box office payload omitted the field outright, and the printed report
 * never looked - so an angled side block, the only reason the control exists, rendered straight to
 * the buyer, to staff at the door and on the front-of-house sheet.
 */
class SeatingRotationTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private const ANGLE = 90;

    private function seatedEvent(Role $role): Event
    {
        $plan = SeatingPlan::create(['role_id' => $role->id, 'name' => 'Angled House']);
        $level = SeatingLevel::create(['seating_plan_id' => $plan->id, 'name' => 'Ground']);
        $section = SeatingSection::create([
            'seating_plan_id' => $plan->id, 'seating_level_id' => $level->id,
            'name' => 'Side Block', 'band' => 'Stalls', 'kind' => 'seated', 'position' => 0,
            'x' => 100, 'y' => 200, 'rotation' => self::ANGLE,
        ]);
        for ($n = 1; $n <= 3; $n++) {
            SeatingSeat::create([
                'seating_plan_id' => $plan->id, 'seating_section_id' => $section->id,
                'row_label' => 'A', 'row_position' => 1, 'seat_label' => (string) $n,
                'position' => $n, 'x' => $n * 26, 'y' => 0,
            ]);
        }

        $request = Request::create('/', 'POST', [
            'name' => 'Angled Show',
            'starts_at' => now()->addMonth()->setTime(19, 30)->format('Y-m-d H:i:s'),
            'tickets_enabled' => 1, 'payment_method' => 'cash', 'ticket_currency_code' => 'USD',
            'creator_role_id' => $role->id,
            'seating_plan_id' => $plan->id,
            'tickets' => [['type' => 'Stalls', 'price' => 40, 'quantity' => 999, 'seating_band' => 'Stalls']],
        ]);
        $request->setUserResolver(fn () => $role->user);
        $event = app(EventRepo::class)->saveEvent($role, $request)->fresh();
        $event->roles()->updateExistingPivot($role->id, ['is_accepted' => true]);

        return $event->fresh();
    }

    /**
     * The contract the picker depends on, not the picker itself.
     *
     * This half always passed: the guest payload has always carried `rotation`. What was missing was
     * SeatingPicker.vue applying it, which no PHP test can see - that half is verified by screenshot.
     */
    public function test_the_rotation_survives_into_the_guest_payload(): void
    {
        $role = $this->createRole($this->createOwner());
        $event = $this->seatedEvent($role);

        $payload = $this->getJson(route('seating.state', [
            'subdomain' => $role->subdomain,
            'event_id' => UrlUtils::encodeId($event->id),
            'date' => $event->saleEventDateFromStartsAt(),
        ]))->assertOk()->json();

        $this->assertSame(self::ANGLE, $payload['levels'][0]['sections'][0]['rotation']);
    }

    public function test_the_box_office_payload_carries_the_rotation(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->seatedEvent($role);

        $payload = $this->actingAs($owner)->getJson(route('box_office.state', [
            'subdomain' => $role->subdomain,
            'hash' => UrlUtils::encodeId($event->id),
        ]))->assertOk()->json();

        $section = $payload['levels'][0]['sections'][0];
        $this->assertArrayHasKey('rotation', $section, 'the console cannot draw what it is not sent');
        $this->assertSame(self::ANGLE, $section['rotation']);
    }

    public function test_the_printed_report_places_seats_in_the_rotated_frame(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->seatedEvent($role);

        $response = $this->actingAs($owner)->get(route('box_office.report', [
            'subdomain' => $role->subdomain,
            'hash' => UrlUtils::encodeId($event->id),
        ]))->assertOk();

        $drawn = collect($response->viewData('levels'))->pluck('drawn')->flatten(1);
        $this->assertCount(3, $drawn);

        // At 90 degrees a row running along +x becomes a column running along +y. Unrotated, all
        // three seats would share a y and differ in x - which is exactly what used to print.
        $this->assertSame(1, $drawn->pluck('x')->map(fn ($v) => round($v, 6))->unique()->count(),
            'a quarter-turned row must occupy one column');
        $this->assertSame(3, $drawn->pluck('y')->map(fn ($v) => round($v, 6))->unique()->count(),
            'a quarter-turned row must spread down the page');
    }

    public function test_the_section_geometry_helper_matches_its_javascript_twin(): void
    {
        $section = new SeatingSection(['x' => 10, 'y' => 20, 'rotation' => 0]);
        $this->assertSame([35.0, 20.0], array_map('floatval', $section->canvasPoint(25, 0)));

        $section->rotation = 180;
        [$x, $y] = $section->canvasPoint(25, 0);
        $this->assertEqualsWithDelta(-15.0, $x, 0.0001);
        $this->assertEqualsWithDelta(20.0, $y, 0.0001);
    }
}
