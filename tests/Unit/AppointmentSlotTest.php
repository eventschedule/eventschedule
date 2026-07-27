<?php

namespace Tests\Unit;

use App\Services\AppointmentService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Slot-engine coverage for AppointmentService. All fixtures live in America/New_York so the
 * asserted UTC instants pin the timezone math (EDT = UTC-4, EST = UTC-5) and the DST transition.
 * Uses the DB (busyIntervals queries events), so RefreshDatabase + the schedule-builder trait.
 */
class AppointmentSlotTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private function service(): AppointmentService
    {
        return app(AppointmentService::class);
    }

    private function role(): \App\Models\Role
    {
        return $this->createRole($this->createOwner(), 'talent', ['timezone' => 'America/New_York']);
    }

    /** Every weekday open for the given window. */
    private function allDays(string $start = '09:00', string $end = '17:00'): array
    {
        return array_fill_keys(['0', '1', '2', '3', '4', '5', '6'], [['start' => $start, 'end' => $end]]);
    }

    public function test_weekly_windows_produce_exact_utc_instants(): void
    {
        $type = $this->createAppointmentType($this->role(), ['weekly_windows' => $this->allDays()]);
        $now = Carbon::parse('2026-09-07 00:00:00', 'America/New_York');

        $result = $this->service()->availableSlots($type, '2026-09-07', 1, $now);
        $slots = $result['days']['2026-09-07'];

        $this->assertSame('America/New_York', $result['schedule_timezone']);
        $this->assertCount(16, $slots); // 09:00..16:30 every 30 min
        $this->assertSame('2026-09-07T13:00:00Z', $slots[0]['utc']); // 09:00 EDT
        $this->assertSame('09:00', $slots[0]['label']);
        $this->assertSame('2026-09-07T20:30:00Z', $slots[15]['utc']); // 16:30 EDT
    }

    public function test_min_notice_clips_early_slots(): void
    {
        $type = $this->createAppointmentType($this->role(), ['weekly_windows' => $this->allDays(), 'min_notice_hours' => 12]);
        $now = Carbon::parse('2026-09-07 00:00:00', 'America/New_York');

        $result = $this->service()->availableSlots($type, '2026-09-07', 1, $now);

        // earliest = 12:00 NY, so the first offered slot is noon.
        $this->assertSame('12:00', $result['days']['2026-09-07'][0]['label']);
    }

    public function test_max_advance_window_is_empty_with_null_next(): void
    {
        $type = $this->createAppointmentType($this->role(), ['weekly_windows' => $this->allDays(), 'max_advance_days' => 1]);
        $now = Carbon::parse('2026-09-07 00:00:00', 'America/New_York');

        $result = $this->service()->availableSlots($type, '2026-09-20', 3, $now);

        $this->assertEmpty($result['days']);
        $this->assertNull($result['next_available_date']);
    }

    public function test_date_override_closes_a_day(): void
    {
        $type = $this->createAppointmentType($this->role(), [
            'weekly_windows' => $this->allDays(),
            'date_overrides' => ['2026-09-07' => []],
        ]);
        $now = Carbon::parse('2026-09-06 00:00:00', 'America/New_York');

        $result = $this->service()->availableSlots($type, '2026-09-07', 1, $now);

        $this->assertArrayNotHasKey('2026-09-07', $result['days']);
    }

    public function test_date_override_replaces_weekly_windows(): void
    {
        $type = $this->createAppointmentType($this->role(), [
            'weekly_windows' => $this->allDays(),
            'date_overrides' => ['2026-09-07' => [['start' => '10:00', 'end' => '12:00']]],
        ]);
        $now = Carbon::parse('2026-09-06 00:00:00', 'America/New_York');

        $slots = $this->service()->availableSlots($type, '2026-09-07', 1, $now)['days']['2026-09-07'];

        $this->assertCount(4, $slots); // 10:00, 10:30, 11:00, 11:30
        $this->assertSame('10:00', $slots[0]['label']);
        $this->assertSame('11:30', $slots[3]['label']);
    }

    public function test_busy_events_block_by_acceptance_state(): void
    {
        $role = $this->role();
        $type = $this->createAppointmentType($role, ['weekly_windows' => $this->allDays()]);

        // Accepted 10:00-11:00 EDT (14:00-15:00 UTC) - blocks.
        $this->createEvent($role, ['starts_at' => '2026-09-07 14:00:00', 'duration' => 1, 'is_accepted' => true]);
        // Pending (is_accepted null) 14:00-15:00 EDT - holds the slot.
        $this->createEvent($role, ['starts_at' => '2026-09-07 18:00:00', 'duration' => 1, 'is_accepted' => null]);
        // Declined 15:00-16:00 EDT - does NOT block.
        $this->createEvent($role, ['starts_at' => '2026-09-07 19:00:00', 'duration' => 1, 'is_accepted' => false]);
        // Cancelled 16:00 EDT - does NOT block.
        $this->createEvent($role, ['starts_at' => '2026-09-07 20:00:00', 'duration' => 1, 'is_accepted' => true, 'is_cancelled' => true]);

        $now = Carbon::parse('2026-09-07 00:00:00', 'America/New_York');
        $labels = array_column($this->service()->availableSlots($type, '2026-09-07', 1, $now)['days']['2026-09-07'], 'label');

        $this->assertNotContains('10:00', $labels);
        $this->assertNotContains('10:30', $labels);
        $this->assertNotContains('14:00', $labels);
        $this->assertContains('15:00', $labels);
        $this->assertContains('16:00', $labels);
    }

    /**
     * A booking being rescheduled must stop blocking itself. Without the exclusion, its own slot and
     * every neighbouring slot that overlaps the time it holds are hidden - so the guest cannot even
     * pick the adjacent half hour.
     */
    public function test_exclude_event_id_frees_the_bookings_own_slot_and_its_neighbours(): void
    {
        $role = $this->role();
        $type = $this->createAppointmentType($role, ['weekly_windows' => $this->allDays(), 'slot_interval_minutes' => 15]);

        // The booking under move: 10:00-10:30 EDT (14:00 UTC), and an unrelated one at 13:00 EDT.
        $mine = $this->createEvent($role, ['starts_at' => '2026-09-07 14:00:00', 'duration' => 0.5, 'is_accepted' => true]);
        $other = $this->createEvent($role, ['starts_at' => '2026-09-07 17:00:00', 'duration' => 0.5, 'is_accepted' => true]);

        $now = Carbon::parse('2026-09-07 00:00:00', 'America/New_York');
        $labels = fn ($exclude) => array_column(
            $this->service()->availableSlots($type, '2026-09-07', 1, $now, true, $exclude)['days']['2026-09-07'],
            'label'
        );

        // Without the exclusion its own block and the overlapping neighbours are gone.
        $before = $labels(null);
        $this->assertNotContains('10:00', $before);
        $this->assertNotContains('10:15', $before);

        // With it, they are all offered again - including the slot it currently holds, which is a
        // legitimate "keep this time" no-op.
        $after = $labels($mine->id);
        $this->assertContains('10:00', $after);
        $this->assertContains('10:15', $after);

        // Excluding one booking must not free anyone else's.
        $this->assertNotContains('13:00', $after);
        // ...and excluding the other one frees 13:00 while 10:00 goes back to being blocked.
        $otherExcluded = $labels($other->id);
        $this->assertContains('13:00', $otherExcluded);
        $this->assertNotContains('10:00', $otherExcluded);
    }

    public function test_exclude_event_id_flows_through_is_slot_available(): void
    {
        $role = $this->role();
        $type = $this->createAppointmentType($role, ['weekly_windows' => $this->allDays()]);
        $mine = $this->createEvent($role, ['starts_at' => '2026-09-07 14:00:00', 'duration' => 0.5, 'is_accepted' => true]);

        $now = Carbon::parse('2026-09-07 00:00:00', 'America/New_York');

        $this->assertFalse($this->service()->isSlotAvailable($type, '2026-09-07T14:00:00Z', $now));
        $this->assertTrue($this->service()->isSlotAvailable($type, '2026-09-07T14:00:00Z', $now, $mine->id));
    }

    /**
     * The exclusion must reach nextAvailableDate(), a third computeDays() caller. Without it the
     * "next available" link skips the very day the guest is trying to move within.
     */
    public function test_exclude_event_id_reaches_the_next_available_lookahead(): void
    {
        $role = $this->role();
        $type = $this->createAppointmentType($role, [
            // One 30-minute slot per day, so a single booking fills a day completely.
            'weekly_windows' => $this->allDays('09:00', '09:30'),
            // The queried day is closed, which forces the lookahead to run in both branches.
            'date_overrides' => ['2026-09-08' => []],
        ]);
        // Fills 2026-09-09's only slot: 09:00 EDT = 13:00 UTC.
        $mine = $this->createEvent($role, ['starts_at' => '2026-09-09 13:00:00', 'duration' => 0.5, 'is_accepted' => true]);

        $now = Carbon::parse('2026-09-07 08:00:00', 'America/New_York');

        $withoutExclusion = $this->service()->availableSlots($type, '2026-09-08', 1, $now);
        $withExclusion = $this->service()->availableSlots($type, '2026-09-08', 1, $now, true, $mine->id);

        // Both had to fall through to the lookahead.
        $this->assertEmpty($withoutExclusion['days']);
        $this->assertEmpty($withExclusion['days']);

        // The booking hides its own day from the lookahead; excluding it brings that day back.
        $this->assertSame('2026-09-10', $withoutExclusion['next_available_date']);
        $this->assertSame('2026-09-09', $withExclusion['next_available_date']);
    }

    /**
     * Owner mode drops min-notice and the booking window, which exist to constrain guests. Buffers
     * and busy intervals must survive it or the owner could double-book.
     */
    public function test_owner_mode_ignores_min_notice_and_booking_window_but_not_busy_time(): void
    {
        $role = $this->role();
        $type = $this->createAppointmentType($role, [
            'weekly_windows' => $this->allDays(),
            'min_notice_hours' => 48,
            'max_advance_days' => 1,
        ]);
        $this->createEvent($role, ['starts_at' => '2026-09-07 14:00:00', 'duration' => 1, 'is_accepted' => true]);

        $now = Carbon::parse('2026-09-07 09:00:00', 'America/New_York');

        // Guest path: 48h notice wipes today out entirely.
        $guest = $this->service()->availableSlots($type, '2026-09-07', 1, $now)['days']['2026-09-07'] ?? [];
        $this->assertEmpty($guest);

        // Owner path: today is offered.
        $owner = $this->service()->availableSlots($type, '2026-09-07', 1, $now, true, null, true)['days']['2026-09-07'] ?? [];
        $this->assertNotEmpty($owner);
        $ownerLabels = array_column($owner, 'label');
        $this->assertContains('12:00', $ownerLabels);
        // ...but the 10:00-11:00 booking still blocks.
        $this->assertNotContains('10:00', $ownerLabels);
        $this->assertNotContains('10:30', $ownerLabels);

        // And max_advance_days = 1 no longer caps the owner.
        $farOut = $this->service()->availableSlots($type, '2026-10-15', 1, $now, true, null, true)['days'] ?? [];
        $this->assertNotEmpty($farOut);
        $guestFarOut = $this->service()->availableSlots($type, '2026-10-15', 1, $now)['days'] ?? [];
        $this->assertEmpty($guestFarOut);
    }

    /**
     * Owner mode drops min-notice to zero, not below it. $earliest is the only past-slot floor
     * computeDays() has, so relaxing it to startOfDay() offered this morning's elapsed slots - and a
     * booking moved into the past can no longer be moved OR cancelled by anyone.
     */
    public function test_owner_mode_never_offers_an_elapsed_slot(): void
    {
        $type = $this->createAppointmentType($this->role(), [
            'weekly_windows' => $this->allDays(),
            'min_notice_hours' => 48,
        ]);

        // Mid-window, so there are elapsed slots to leak and open ones to keep.
        $now = Carbon::parse('2026-09-07 13:20:00', 'America/New_York');

        $labels = array_column(
            $this->service()->availableSlots($type, '2026-09-07', 1, $now, true, null, true)['days']['2026-09-07'] ?? [],
            'label'
        );

        $this->assertNotEmpty($labels, 'owner mode still ignores the 48h notice');
        foreach (['09:00', '10:00', '12:00', '13:00'] as $elapsed) {
            $this->assertNotContains($elapsed, $labels, "$elapsed already passed and must not be offered");
        }
        $this->assertContains('13:30', $labels, 'the next slot after now is still offered');
    }

    public function test_recurring_event_blocks_matching_days(): void
    {
        $role = $this->role();
        $type = $this->createAppointmentType($role, ['weekly_windows' => $this->allDays()]);

        // Weekly all-days event at 12:00 EDT (16:00 UTC), 1h, starting before the test date.
        $this->createRecurringEvent($role, ['starts_at' => '2026-09-01 16:00:00', 'duration' => 1, 'is_accepted' => true]);

        $now = Carbon::parse('2026-09-07 00:00:00', 'America/New_York');
        $labels = array_column($this->service()->availableSlots($type, '2026-09-07', 1, $now)['days']['2026-09-07'], 'label');

        $this->assertNotContains('12:00', $labels);
        $this->assertNotContains('12:30', $labels);
        $this->assertContains('13:00', $labels); // block ends 13:00; back-to-back allowed
    }

    public function test_back_to_back_allowed_without_buffers(): void
    {
        $role = $this->role();
        $type = $this->createAppointmentType($role, ['weekly_windows' => $this->allDays()]);
        // Busy 12:00-12:30 EDT (16:00-16:30 UTC).
        $this->createEvent($role, ['starts_at' => '2026-09-07 16:00:00', 'duration' => 0.5, 'is_accepted' => true]);

        $now = Carbon::parse('2026-09-07 00:00:00', 'America/New_York');
        $labels = array_column($this->service()->availableSlots($type, '2026-09-07', 1, $now)['days']['2026-09-07'], 'label');

        $this->assertNotContains('12:00', $labels); // overlaps the busy block
        $this->assertContains('11:30', $labels);    // ends exactly at 12:00 (half-open)
        $this->assertContains('12:30', $labels);    // starts exactly at 12:30 (half-open)
    }

    public function test_buffers_extend_the_block_both_directions(): void
    {
        $role = $this->role();
        $type = $this->createAppointmentType($role, [
            'weekly_windows' => $this->allDays(),
            'buffer_before_minutes' => 15,
            'buffer_after_minutes' => 15,
        ]);
        // Busy 12:00-12:30 EDT.
        $this->createEvent($role, ['starts_at' => '2026-09-07 16:00:00', 'duration' => 0.5, 'is_accepted' => true]);

        $now = Carbon::parse('2026-09-07 00:00:00', 'America/New_York');
        $labels = array_column($this->service()->availableSlots($type, '2026-09-07', 1, $now)['days']['2026-09-07'], 'label');

        // 15-min candidate buffers push the collision out to the neighbours.
        $this->assertNotContains('11:30', $labels);
        $this->assertNotContains('12:00', $labels);
        $this->assertNotContains('12:30', $labels);
        $this->assertContains('11:00', $labels);
        $this->assertContains('13:00', $labels);
    }

    public function test_dst_spring_forward_window_yields_no_slots(): void
    {
        // 2026-03-08 is the US spring-forward Sunday: 02:00-03:00 local does not exist.
        $type = $this->createAppointmentType($this->role(), [
            'weekly_windows' => ['0' => [['start' => '02:00', 'end' => '03:00']], '1' => [], '2' => [], '3' => [], '4' => [], '5' => [], '6' => []],
        ]);
        $now = Carbon::parse('2026-03-08 00:00:00', 'America/New_York');

        $result = $this->service()->availableSlots($type, '2026-03-08', 1, $now);

        $this->assertArrayNotHasKey('2026-03-08', $result['days']);
    }

    public function test_dst_offset_shifts_across_the_transition(): void
    {
        $type = $this->createAppointmentType($this->role(), ['weekly_windows' => $this->allDays('09:00', '09:30')]);
        $now = Carbon::parse('2026-03-01 00:00:00', 'America/New_York');

        // Day before the transition: EST (UTC-5) -> 09:00 = 14:00Z.
        $before = $this->service()->availableSlots($type, '2026-03-07', 1, $now);
        $this->assertSame('2026-03-07T14:00:00Z', $before['days']['2026-03-07'][0]['utc']);

        // Day after: EDT (UTC-4) -> 09:00 = 13:00Z.
        $after = $this->service()->availableSlots($type, '2026-03-09', 1, $now);
        $this->assertSame('2026-03-09T13:00:00Z', $after['days']['2026-03-09'][0]['utc']);
    }

    public function test_null_duration_busy_event_blocks_two_hours(): void
    {
        $role = $this->role();
        $type = $this->createAppointmentType($role, ['weekly_windows' => $this->allDays()]);
        // Zero-duration event at 12:00 EDT (16:00 UTC) blocks the default 2h -> 12:00-14:00.
        $this->createEvent($role, ['starts_at' => '2026-09-07 16:00:00', 'duration' => 0, 'is_accepted' => true]);

        $now = Carbon::parse('2026-09-07 00:00:00', 'America/New_York');
        $labels = array_column($this->service()->availableSlots($type, '2026-09-07', 1, $now)['days']['2026-09-07'], 'label');

        $this->assertNotContains('12:00', $labels);
        $this->assertNotContains('13:30', $labels); // still inside the 2h block
        $this->assertContains('14:00', $labels);    // block ends 14:00
    }

    public function test_is_slot_available_requires_exact_membership(): void
    {
        $type = $this->createAppointmentType($this->role(), ['weekly_windows' => $this->allDays()]);
        $now = Carbon::parse('2026-09-07 00:00:00', 'America/New_York');
        $service = $this->service();

        $this->assertTrue($service->isSlotAvailable($type, '2026-09-07T13:00:00Z', $now)); // 09:00 EDT
        $this->assertFalse($service->isSlotAvailable($type, '2026-09-07T13:15:00Z', $now)); // off-grid
        $this->assertFalse($service->isSlotAvailable($type, '2026-09-07T02:00:00Z', $now)); // outside window
    }
}
