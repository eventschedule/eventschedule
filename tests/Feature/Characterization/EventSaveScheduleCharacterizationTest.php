<?php

namespace Tests\Feature\Characterization;

use App\Repos\EventRepo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\Feature\Characterization\Concerns\SavesEventsOverHttp;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Characterizes the timezone-conversion and recurrence sections of
 * EventRepo::saveEvent() (REFACTOR_PLAN.md P11). The recurrence block reads
 * the GLOBAL request() helper (landmine L1) - over HTTP request() === $request,
 * so these pins hold for the form path the decomposition must preserve.
 */
class EventSaveScheduleCharacterizationTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;
    use SavesEventsOverHttp;

    public function test_wall_clock_is_stored_as_utc_anchored_to_schedule_timezone(): void
    {
        $owner = $this->createOwner();
        // Schedule in New York; the user's own timezone must NOT win.
        $role = $this->createRole($owner, 'talent', ['timezone' => 'America/New_York']);
        $owner->forceFill(['timezone' => 'Asia/Tokyo'])->save();

        // 2026-01-15 is EST (UTC-5); the August default payload is EDT (UTC-4).
        $this->postCreateEvent($owner, $role, [
            'starts_at' => '2026-01-15 20:00:00',
        ])->assertRedirect();

        $this->assertDatabaseHas('events', [
            'id' => $this->latestEvent()->id,
            'starts_at' => '2026-01-16 01:00:00',
            'timezone' => 'America/New_York',
        ]);
    }

    public function test_no_starts_at_stores_null_timezone(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');

        $this->postCreateEvent($owner, $role, [
            'starts_at' => '',
        ])->assertRedirect();

        $this->assertDatabaseHas('events', [
            'id' => $this->latestEvent()->id,
            'starts_at' => null,
            'timezone' => null,
        ]);
    }

    public function test_timezone_override_parameter_wins_over_schedule_timezone(): void
    {
        // The guest-submission path: the submitter's talent schedule is saved
        // onto, but the typed wall clock is the curator's local time.
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['timezone' => 'America/New_York']);

        $request = new Request($this->eventPayload([
            'starts_at' => '2026-01-15 20:00:00',
        ]));
        $request->setUserResolver(fn () => $owner);

        app(EventRepo::class)->saveEvent($role, $request, null, true, 'Europe/Berlin');

        // 20:00 Berlin (CET, UTC+1) -> 19:00 UTC, and the override is recorded.
        $this->assertDatabaseHas('events', [
            'id' => $this->latestEvent()->id,
            'starts_at' => '2026-01-15 19:00:00',
            'timezone' => 'Europe/Berlin',
        ]);
    }

    public function test_weekly_recurrence_persists_days_of_week_bitmask(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');

        $this->postCreateEvent($owner, $role, [
            'schedule_type' => 'recurring',
            'recurring_frequency' => 'weekly',
            'days_of_week_1' => '1', // Monday
            'days_of_week_3' => '1', // Wednesday
        ])->assertRedirect();

        $this->assertDatabaseHas('events', [
            'id' => $this->latestEvent()->id,
            'recurring_frequency' => 'weekly',
            'recurring_interval' => null,
            'days_of_week' => '0101000',
            'recurring_end_type' => 'never',
            'recurring_end_value' => null,
        ]);
    }

    public function test_every_n_weeks_clamps_interval_to_minimum_two(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');

        $this->postCreateEvent($owner, $role, [
            'schedule_type' => 'recurring',
            'recurring_frequency' => 'every_n_weeks',
            'recurring_interval' => '1',
            'days_of_week_5' => '1',
        ])->assertRedirect();

        $this->assertDatabaseHas('events', [
            'id' => $this->latestEvent()->id,
            'recurring_frequency' => 'every_n_weeks',
            'recurring_interval' => 2,
            'days_of_week' => '0000010',
        ]);
    }

    public function test_monthly_frequency_sets_all_days_for_query_compatibility(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');

        $this->postCreateEvent($owner, $role, [
            'schedule_type' => 'recurring',
            'recurring_frequency' => 'monthly_date',
        ])->assertRedirect();

        $this->assertDatabaseHas('events', [
            'id' => $this->latestEvent()->id,
            'recurring_frequency' => 'monthly_date',
            'days_of_week' => '1111111',
        ]);
    }

    public function test_recurring_end_on_date_rejects_malformed_dates(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');

        $this->postCreateEvent($owner, $role, [
            'schedule_type' => 'recurring',
            'recurring_frequency' => 'weekly',
            'days_of_week_0' => '1',
            'recurring_end_type' => 'on_date',
            'recurring_end_value' => '15/08/2026', // not YYYY-MM-DD
        ])->assertRedirect();

        $this->assertDatabaseHas('events', [
            'id' => $this->latestEvent()->id,
            'recurring_end_type' => 'on_date',
            'recurring_end_value' => null,
        ]);
    }

    public function test_recurring_end_after_events_normalizes_to_positive_integer_string(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');

        $this->postCreateEvent($owner, $role, [
            'schedule_type' => 'recurring',
            'recurring_frequency' => 'weekly',
            'days_of_week_0' => '1',
            'recurring_end_type' => 'after_events',
            'recurring_end_value' => '5.9',
        ])->assertRedirect();

        // Numeric values are truncated to int and stored as a string.
        $this->assertDatabaseHas('events', [
            'id' => $this->latestEvent()->id,
            'recurring_end_type' => 'after_events',
            'recurring_end_value' => '5',
        ]);
    }

    public function test_include_and_exclude_dates_are_filtered_deduped_and_sorted(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');

        $this->postCreateEvent($owner, $role, [
            'schedule_type' => 'recurring',
            'recurring_frequency' => 'weekly',
            'days_of_week_0' => '1',
            'recurring_include_dates' => ['2026-09-02', '2026-09-01', '2026-09-02', 'garbage'],
            'recurring_exclude_dates' => ['2026-08-20', 'not-a-date'],
        ])->assertRedirect();

        $event = $this->latestEvent();
        $this->assertSame(['2026-09-01', '2026-09-02'], $event->recurring_include_dates);
        $this->assertSame(['2026-08-20'], $event->recurring_exclude_dates);
    }

    public function test_switching_recurring_to_one_time_clears_all_recurrence_fields(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');
        $event = $this->createRecurringEvent($role, [
            'recurring_end_type' => 'after_events',
            'recurring_end_value' => '10',
            'recurring_include_dates' => ['2026-09-01'],
        ]);

        $this->putUpdateEvent($owner, $role, $event, [
            'schedule_type' => 'one_time',
        ])->assertRedirect();

        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'days_of_week' => null,
            'recurring_frequency' => null,
            'recurring_interval' => null,
            'recurring_end_type' => 'never',
            'recurring_end_value' => null,
            'recurring_include_dates' => null,
            'recurring_exclude_dates' => null,
        ]);
    }
}
