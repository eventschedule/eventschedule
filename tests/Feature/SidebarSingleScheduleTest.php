<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * A user with exactly one schedule gets it as a regular sidebar item next to the Dashboard,
 * with the same icon treatment as Following, Sales and the rest - not a "Venue Schedules"
 * section heading over a one-row list with a letter badge. From two schedules on, the
 * per-type sections come back.
 */
class SidebarSingleScheduleTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    public function test_a_single_schedule_renders_as_a_regular_nav_item(): void
    {
        $owner = $this->createOwner();
        $this->createRole($owner, 'venue', ['name' => 'Only Venue']);

        $content = $this->actingAs($owner)->get('/dashboard')->assertOk()->getContent();

        $this->assertStringContainsString('Only Venue', $content);
        $this->assertStringNotContainsString(__('messages.venue_schedules'), $content);
        $this->assertStringNotContainsString('class="schedule-badge', $content);
    }

    public function test_a_single_schedule_counts_across_all_three_types(): void
    {
        $owner = $this->createOwner();
        $this->createRole($owner, 'venue', ['name' => 'First Venue']);
        $this->createRole($owner, 'talent', ['name' => 'First Talent']);

        $content = $this->actingAs($owner)->get('/dashboard')->assertOk()->getContent();

        $this->assertStringContainsString(__('messages.venue_schedules'), $content);
        $this->assertStringContainsString(__('messages.talent_schedules'), $content);
        $this->assertStringContainsString('class="schedule-badge', $content);
    }
}
