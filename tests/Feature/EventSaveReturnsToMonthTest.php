<?php

namespace Tests\Feature;

use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Characterization\Concerns\SavesEventsOverHttp;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Saving an event comes back to the calendar month its Edit link was pressed on.
 *
 * A series is edited from whichever of its months the owner is looking at, and the save used to
 * send them to the month it began in. The calendar's editUrl() puts the month on the link, the
 * form posts it back as return_month, and EventController::update() returns there while the event
 * as saved still falls in that month, and to the event's own month otherwise.
 */
class EventSaveReturnsToMonthTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;
    use SavesEventsOverHttp;

    /** Every Saturday from 2026-08-15, the payload's default start. */
    private function weekly(array $overrides = []): array
    {
        return array_merge([
            'schedule_type' => 'recurring',
            'recurring_frequency' => 'weekly',
            'days_of_week_6' => '1',
        ], $overrides);
    }

    public function test_saving_a_series_returns_to_the_month_it_was_edited_from(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');
        $this->postCreateEvent($owner, $role, $this->weekly())->assertRedirect();
        $event = $this->latestEvent();

        $this->putUpdateEvent($owner, $role, $event, $this->weekly(['name' => 'Renamed', 'return_month' => '2026-11']))
            ->assertRedirect($this->adminCalendarUrl($role, 11, 2026));
    }

    public function test_without_a_month_the_save_goes_to_the_events_own(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');
        $this->postCreateEvent($owner, $role, $this->weekly())->assertRedirect();
        $event = $this->latestEvent();

        $this->putUpdateEvent($owner, $role, $event, $this->weekly(['name' => 'Renamed']))
            ->assertRedirect($this->adminCalendarUrl($role, 8, 2026));
    }

    public function test_a_series_that_no_longer_falls_in_that_month_goes_to_its_own(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');
        $this->postCreateEvent($owner, $role, $this->weekly())->assertRedirect();
        $event = $this->latestEvent();

        // Ended in September, so November has nothing of it left to show.
        $this->putUpdateEvent($owner, $role, $event, $this->weekly([
            'recurring_end_type' => 'on_date',
            'recurring_end_value' => '2026-09-30',
            'return_month' => '2026-11',
        ]))->assertRedirect($this->adminCalendarUrl($role, 8, 2026));
    }

    public function test_an_event_moved_to_another_month_is_followed_there(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');
        $this->postCreateEvent($owner, $role)->assertRedirect();
        $event = $this->latestEvent();

        $this->putUpdateEvent($owner, $role, $event, [
            'starts_at' => '2026-12-05 20:00:00',
            'return_month' => '2026-08',
        ])->assertRedirect($this->adminCalendarUrl($role, 12, 2026));
    }

    public function test_a_month_that_is_not_one_is_ignored(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');
        $this->postCreateEvent($owner, $role, $this->weekly())->assertRedirect();
        $event = $this->latestEvent();

        foreach (['2026-13', '26-11', '2026-11-01', ['2026-11']] as $value) {
            $this->putUpdateEvent($owner, $role, $event, $this->weekly(['return_month' => $value]))
                ->assertRedirect($this->adminCalendarUrl($role, 8, 2026));
        }
    }

    public function test_the_edit_form_carries_the_month_of_its_link(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');
        $this->postCreateEvent($owner, $role, $this->weekly())->assertRedirect();
        $hash = UrlUtils::encodeId($this->latestEvent()->id);

        $this->actingAs($owner)
            ->get(route('event.edit', ['subdomain' => $role->subdomain, 'hash' => $hash, 'year' => 2026, 'month' => 11]))
            ->assertOk()
            ->assertSee('name="return_month" value="2026-11"', false);

        $this->actingAs($owner)
            ->get(route('event.edit', ['subdomain' => $role->subdomain, 'hash' => $hash, 'year' => 2026, 'month' => 13]))
            ->assertOk()
            ->assertDontSee('name="return_month"', false);

        // The dashboard's link goes through the schedule-less address, which passes it on.
        $this->actingAs($owner)
            ->get(route('event.edit_admin', ['hash' => $hash, 'year' => 2026, 'month' => 11]))
            ->assertRedirect(route('event.edit', ['subdomain' => $role->subdomain, 'hash' => $hash, 'year' => 2026, 'month' => 11]));
    }

    public function test_every_edit_link_in_the_calendar_carries_the_month(): void
    {
        foreach (['calendar', 'guest-row', 'mobile-event-card', 'month-script'] as $partial) {
            $source = file_get_contents(resource_path("views/role/partials/{$partial}.blade.php"));

            $this->assertDoesNotMatchRegularExpression('/[:\s]href[=:]\s*"?\s*(event|e)\.edit_url/', $source, "{$partial} links to edit_url without the month on screen; use editUrl()");
            $this->assertDoesNotMatchRegularExpression('/\?\s*e\.edit_url\b/', $source, "{$partial} links to edit_url without the month on screen; use editUrl()");
        }
    }
}
