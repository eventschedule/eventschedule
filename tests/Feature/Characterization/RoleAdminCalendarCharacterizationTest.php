<?php

namespace Tests\Feature\Characterization;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Route-level characterization of RoleController::adminCalendarEvents(), the Ajax endpoint
 * behind the AP schedule tab. The AP renders a month grid on desktop but a flat four-month
 * agenda on mobile with no month navigation, so the query deliberately carries no upper
 * date bound and is capped by row count instead. Pins that window and the member gate.
 */
class RoleAdminCalendarCharacterizationTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    public function test_admin_calendar_events_includes_future_month_events_for_curator(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'curator');
        $this->createEvent($role, [
            'name' => 'Curator Next Quarter Event',
            'starts_at' => now()->addMonthsNoOverflow(2)->setTime(12, 0)->format('Y-m-d H:i:s'),
        ]);

        $response = $this->actingAs($owner)
            ->get(route('role.admin_calendar_events', ['subdomain' => $role->subdomain]));

        $response->assertOk();
        $response->assertJsonStructure(['events', 'eventsMap', 'pastEvents', 'hasMorePastEvents', 'filterMeta']);
        $this->assertStringContainsString('Curator Next Quarter Event', $response->getContent());
    }

    public function test_admin_calendar_events_includes_future_month_events_for_non_curator(): void
    {
        // The non-curator branch builds the same window with a different query shape
        // (whereHas instead of a whereIn subquery), so it needs its own pin.
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $this->createEvent($role, [
            'name' => 'Venue Next Quarter Event',
            'starts_at' => now()->addMonthsNoOverflow(2)->setTime(12, 0)->format('Y-m-d H:i:s'),
        ]);

        $response = $this->actingAs($owner)
            ->get(route('role.admin_calendar_events', ['subdomain' => $role->subdomain]));

        $response->assertOk();
        $this->assertStringContainsString('Venue Next Quarter Event', $response->getContent());
    }

    public function test_admin_calendar_events_excludes_unaccepted_events(): void
    {
        // is_accepted = true stays a hard filter: pending submissions belong to the
        // Requests tab, not the schedule tab.
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'curator');
        $event = $this->createEvent($role, [
            'name' => 'Pending Submission Event',
            'starts_at' => now()->addMonthsNoOverflow(2)->setTime(12, 0)->format('Y-m-d H:i:s'),
        ]);
        $event->roles()->updateExistingPivot($role->id, ['is_accepted' => null]);

        $response = $this->actingAs($owner)
            ->get(route('role.admin_calendar_events', ['subdomain' => $role->subdomain]));

        $response->assertOk();
        $this->assertStringNotContainsString('Pending Submission Event', $response->getContent());
    }

    public function test_admin_calendar_events_rejects_non_member(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'curator');
        $stranger = $this->createOwner();

        $this->actingAs($stranger)
            ->get(route('role.admin_calendar_events', ['subdomain' => $role->subdomain]))
            ->assertForbidden();
    }
}
