<?php

namespace Tests\Unit;

use App\Repos\EventRepo;
use PHPUnit\Framework\TestCase;

/**
 * Covers the pure material-change detection that drives attendee notifications (issue #94).
 * No DB access: detectMaterialChanges() is a pure function of the old/new value arrays.
 */
class EventChangeDetectionTest extends TestCase
{
    private function base(array $overrides = []): array
    {
        return array_merge([
            'starts_at' => '2026-08-01 19:00:00',
            'duration' => 2.0,
            'timezone' => 'America/Los_Angeles',
            'days_of_week' => null,
            'event_url' => null,
            'venue_id' => 10,
            'venue_name' => 'The Fillmore',
        ], $overrides);
    }

    public function test_no_change_returns_empty(): void
    {
        $this->assertSame([], EventRepo::detectMaterialChanges($this->base(), $this->base()));
    }

    public function test_date_change_on_non_recurring_event(): void
    {
        $changes = EventRepo::detectMaterialChanges(
            $this->base(),
            $this->base(['starts_at' => '2026-08-08 19:00:00'])
        );

        $this->assertArrayHasKey('date', $changes);
        $this->assertSame('2026-08-01 19:00:00', $changes['date']['old_starts_at']);
    }

    public function test_duration_only_change_is_detected(): void
    {
        $changes = EventRepo::detectMaterialChanges($this->base(), $this->base(['duration' => 4.0]));
        $this->assertArrayHasKey('date', $changes);
    }

    public function test_date_change_on_recurring_event_is_suppressed(): void
    {
        $rec = $this->base(['days_of_week' => '1010100']);
        $changes = EventRepo::detectMaterialChanges($rec, array_merge($rec, ['starts_at' => '2026-08-08 19:00:00']));
        $this->assertArrayNotHasKey('date', $changes);
    }

    public function test_venue_change_variant(): void
    {
        $changes = EventRepo::detectMaterialChanges(
            $this->base(),
            $this->base(['venue_id' => 20, 'venue_name' => 'The Chapel'])
        );

        $this->assertSame('venue', $changes['location']['variant']);
        $this->assertSame('The Fillmore', $changes['location']['old_venue']);
        $this->assertSame('The Chapel', $changes['location']['new_venue']);
    }

    public function test_moved_online_variant(): void
    {
        $changes = EventRepo::detectMaterialChanges(
            $this->base(),
            $this->base(['venue_id' => null, 'venue_name' => null, 'event_url' => 'https://zoom.us/x'])
        );

        $this->assertSame('moved_online', $changes['location']['variant']);
    }

    public function test_moved_in_person_variant(): void
    {
        $online = $this->base(['venue_id' => null, 'venue_name' => null, 'event_url' => 'https://zoom.us/a']);
        $changes = EventRepo::detectMaterialChanges(
            $online,
            array_merge($online, ['event_url' => null, 'venue_id' => 30, 'venue_name' => 'Town Hall'])
        );

        $this->assertSame('moved_in_person', $changes['location']['variant']);
    }

    public function test_online_link_update_variant(): void
    {
        $online = $this->base(['venue_id' => null, 'venue_name' => null, 'event_url' => 'https://zoom.us/a']);
        $changes = EventRepo::detectMaterialChanges($online, array_merge($online, ['event_url' => 'https://zoom.us/b']));

        $this->assertSame('online_updated', $changes['location']['variant']);
    }

    public function test_online_link_unchanged_apart_from_trailing_slash(): void
    {
        $online = $this->base(['venue_id' => null, 'venue_name' => null, 'event_url' => 'https://zoom.us/a']);
        $changes = EventRepo::detectMaterialChanges($online, array_merge($online, ['event_url' => 'https://zoom.us/a/']));

        $this->assertSame([], $changes);
    }
}
