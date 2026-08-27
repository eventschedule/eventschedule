<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * An event falls on a calendar day because of WHERE it happens, not who is looking at it.
 *
 * A selfhoster reported a 7:30pm Europe/London show rendering as "Sunday Oct 25, 00:00" on the
 * admin calendar and the guest page while the edit form said "Sat Oct 24, 19:30". Their stored
 * starts_at was correct and the signed-OUT guest page was correct: the calendar payload resolved
 * its timezone from the signed-in viewer's account (Asia/Kolkata, UTC+5:30), which is exactly
 * 4.5 hours past the 18:30 UTC instant, so the occurrence landed on the next day at midnight.
 *
 * These assert on the JSON endpoints rather than the pages: the calendar Vue app ships an empty
 * array and fetches its events from these, so a page assertion pins nothing.
 */
class CalendarTimezoneTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    /** The reporter's schedule: a London venue. */
    private Role $role;

    /** The owner, whose account timezone is 4.5 hours ahead of the schedule's. */
    private User $owner;

    private Event $event;

    protected function setUp(): void
    {
        parent::setUp();

        // The event date is absolute, so freeze "now" before it - otherwise this passes until
        // 2026-10-24 and then silently starts testing a past event.
        Carbon::setTestNow(Carbon::parse('2026-08-27 12:00:00', 'UTC'));

        $this->owner = $this->createOwner();
        $this->owner->timezone = 'Asia/Kolkata';
        $this->owner->save();

        $this->role = $this->createRole($this->owner, 'talent', ['timezone' => 'Europe/London']);

        // 2026-10-24 19:30 Europe/London (BST) == 18:30 UTC, running 2.5 hours.
        $this->event = $this->createEvent($this->role, [
            'name' => 'Durham Jazz Fest',
            'starts_at' => '2026-10-24 18:30:00',
            'duration' => 2.5,
            // Without this scheduleTimezone() falls back to the app timezone and the bug that
            // this test exists for cannot reproduce.
            'creator_role_id' => $this->role->id,
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    /** @return array<string, mixed> the payload row for the event under test */
    private function eventRow(array $payload): array
    {
        $rows = array_values(array_filter(
            $payload['events'] ?? [],
            fn ($e) => ($e['name'] ?? null) === 'Durham Jazz Fest'
        ));

        $this->assertCount(1, $rows, 'the calendar payload must contain the event');

        return $rows[0];
    }

    private function assertRendersTheVenuesDay(array $payload, string $context): void
    {
        $row = $this->eventRow($payload);

        $this->assertSame('2026-10-24 18:30:00', $row['starts_at'], "the stored instant must not move: {$context}");
        $this->assertSame('2026-10-24 19:30:00', $row['local_starts_at'], "local_starts_at drives the printed time: {$context}");
        $this->assertSame('2026-10-24', $row['local_date'], "local_date: {$context}");
        $this->assertSame('2026-10-24', $row['start_date'], "start_date: {$context}");
        $this->assertSame('2026-10-24', $row['occurrenceDate'], "occurrenceDate drives the day header: {$context}");

        $this->assertSame(
            ['2026-10-24'],
            array_keys((array) ($payload['eventsMap'] ?? [])),
            "buildEventsMap places the day cell: {$context}"
        );
    }

    private function guestPayload(): array
    {
        return $this->getJson(route('role.calendar_events', [
            'subdomain' => $this->role->subdomain,
            'year' => 2026,
            'month' => 10,
        ]))->assertOk()->json();
    }

    public function test_guest_calendar_renders_the_schedules_day_for_a_signed_out_visitor(): void
    {
        $this->assertRendersTheVenuesDay($this->guestPayload(), 'signed out');
    }

    public function test_guest_calendar_does_not_shift_for_a_viewer_east_of_the_schedule(): void
    {
        $this->actingAs($this->owner);

        $this->assertRendersTheVenuesDay($this->guestPayload(), 'signed in, Asia/Kolkata');
    }

    /**
     * The strongest form of the guarantee, and the comparison that settled the live report: the
     * schedule's own timezone decides the answer, so signing in must not change a single field.
     */
    public function test_signing_in_changes_nothing_in_the_guest_calendar_payload(): void
    {
        $anonymous = $this->guestPayload();

        $this->actingAs($this->owner);
        $authenticated = $this->guestPayload();

        foreach (['local_starts_at', 'local_date', 'utc_date', 'start_date', 'occurrenceDate'] as $key) {
            $this->assertSame(
                $this->eventRow($anonymous)[$key],
                $this->eventRow($authenticated)[$key],
                "{$key} must not depend on who is looking"
            );
        }

        $this->assertSame(
            array_keys((array) $anonymous['eventsMap']),
            array_keys((array) $authenticated['eventsMap']),
            'the day cell must not depend on who is looking'
        );
    }

    public function test_admin_calendar_renders_the_schedules_day_for_a_viewer_east_of_the_schedule(): void
    {
        $payload = $this->actingAs($this->owner)
            ->getJson(route('role.admin_calendar_events', [
                'subdomain' => $this->role->subdomain,
                'year' => 2026,
                'month' => 10,
            ]))->assertOk()->json();

        $this->assertRendersTheVenuesDay($payload, 'admin calendar, Asia/Kolkata');
    }
}
