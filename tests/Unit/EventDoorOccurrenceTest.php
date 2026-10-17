<?php

namespace Tests\Unit;

use App\Models\Event;
use App\Models\Role;
use Carbon\Carbon;
use Tests\TestCase;

/**
 * Which night the door is on (Event::doorOccurrenceDates()), which the ticket scanner asks before it
 * checks in a ticket for another date of the same series. A ticket's own window opens a day early,
 * so without it tonight's door checked in tomorrow's ticket of a daily show.
 *
 * Built in memory: the answer depends on the event's fields and the schedule's timezone only.
 */
class EventDoorOccurrenceTest extends TestCase
{
    private const NY = 'America/New_York';

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    /** @param  array<string, mixed>  $fields */
    private function event(string $localStart, float $duration, array $fields = [], string $tz = self::NY): Event
    {
        $event = new Event;
        $event->starts_at = Carbon::parse($localStart, $tz)->setTimezone('UTC')->format('Y-m-d H:i:s');
        $event->duration = $duration;
        foreach ($fields as $key => $value) {
            $event->{$key} = $value;
        }
        $event->setRelation('creatorRole', new Role(['timezone' => $tz]));

        return $event;
    }

    private function daily(string $localStart, float $duration, array $fields = [], string $tz = self::NY): Event
    {
        return $this->event($localStart, $duration, array_merge([
            'days_of_week' => '1111111',
            'recurring_frequency' => 'daily',
        ], $fields), $tz);
    }

    private function at(string $local, string $tz = self::NY): Carbon
    {
        return Carbon::parse($local, $tz)->setTimezone('UTC');
    }

    public function test_a_one_off_event_never_names_a_door_date(): void
    {
        $show = $this->event('2026-07-15 20:00', 2);
        $festival = $this->event('2026-07-15 12:00', 72);

        $this->assertSame([], $show->doorOccurrenceDates($this->at('2026-07-15 21:00')));
        $this->assertSame([], $festival->doorOccurrenceDates($this->at('2026-07-16 15:00')));
    }

    public function test_a_daily_evening_show_is_on_tonight_until_it_ends(): void
    {
        $show = $this->daily('2026-07-01 20:00', 2);

        // Before tonight's start the door is on tonight, not on tomorrow (whose door is not open).
        $this->assertSame(['2026-07-15'], $show->doorOccurrenceDates($this->at('2026-07-15 14:00')));
        $this->assertSame(['2026-07-15'], $show->doorOccurrenceDates($this->at('2026-07-15 19:00')));
        // Running: tomorrow's door is open too, but tonight is in progress.
        $this->assertSame(['2026-07-15'], $show->doorOccurrenceDates($this->at('2026-07-15 21:00')));
        // Over: the next night's door is the one open.
        $this->assertSame(['2026-07-16'], $show->doorOccurrenceDates($this->at('2026-07-15 22:30')));
    }

    public function test_a_show_after_midnight_is_on_its_own_date_the_evening_before(): void
    {
        $late = $this->daily('2026-07-01 01:00', 2);

        $this->assertSame(['2026-07-16'], $late->doorOccurrenceDates($this->at('2026-07-15 23:30')));
        $this->assertSame(['2026-07-16'], $late->doorOccurrenceDates($this->at('2026-07-16 02:00')));
    }

    public function test_an_occurrence_in_progress_beats_the_nearer_start(): void
    {
        $market = $this->daily('2026-07-01 10:00', 14);

        // 23:00: tomorrow's start is 11 hours away and today's was 13 hours ago, but today runs.
        $this->assertSame(['2026-07-15'], $market->doorOccurrenceDates($this->at('2026-07-15 23:00')));
    }

    public function test_a_weekly_show_is_on_its_own_night(): void
    {
        // 2026-07-17 is a Friday; days_of_week runs from Sunday.
        $weekly = $this->event('2026-07-03 20:00', 3, ['days_of_week' => '0000011', 'recurring_frequency' => 'weekly']);

        $this->assertSame(['2026-07-17'], $weekly->doorOccurrenceDates($this->at('2026-07-17 21:00')));
        // Before Friday's show on Friday, the Friday is the one open (Saturday's opens at 20:00).
        $this->assertSame(['2026-07-17'], $weekly->doorOccurrenceDates($this->at('2026-07-17 12:00')));
        // Midweek no door is open at all.
        $this->assertSame([], $weekly->doorOccurrenceDates($this->at('2026-07-15 12:00')));
    }

    public function test_a_long_occurrence_is_found_days_after_its_date(): void
    {
        // A weekend market every Friday at 10:00 for 48 hours.
        $market = $this->event('2026-07-03 10:00', 48, ['days_of_week' => '0000010', 'recurring_frequency' => 'weekly']);

        $this->assertSame(['2026-07-17'], $market->doorOccurrenceDates($this->at('2026-07-18 12:00')));
    }

    public function test_an_excluded_night_is_not_the_door(): void
    {
        $show = $this->daily('2026-07-01 20:00', 2, ['recurring_exclude_dates' => ['2026-07-15']]);

        // Tonight is off, and tomorrow's door has not opened yet at 19:00.
        $this->assertSame([], $show->doorOccurrenceDates($this->at('2026-07-15 19:00')));
        $this->assertSame(['2026-07-16'], $show->doorOccurrenceDates($this->at('2026-07-15 21:00')));
    }

    public function test_dates_are_the_schedules_not_the_apps(): void
    {
        config(['app.timezone' => 'UTC']);
        // 20:00 in Tokyo is 11:00 UTC the same day; in Los Angeles it is 03:00 UTC the next day.
        $tokyo = $this->daily('2026-07-01 20:00', 2, [], 'Asia/Tokyo');
        $la = $this->daily('2026-07-01 20:00', 2, [], 'America/Los_Angeles');

        $this->assertSame(['2026-07-15'], $tokyo->doorOccurrenceDates($this->at('2026-07-15 21:00', 'Asia/Tokyo')));
        $this->assertSame(['2026-07-15'], $la->doorOccurrenceDates($this->at('2026-07-15 21:00', 'America/Los_Angeles')));
    }

    public function test_the_check_in_window_opens_a_day_early_and_closes_at_the_end(): void
    {
        $show = $this->daily('2026-07-01 20:00', 3);
        $window = $show->checkInWindow('2026-07-15');

        $this->assertSame('2026-07-16 00:00:00', $window['start']->format('Y-m-d H:i:s'));
        $this->assertSame('2026-07-15 00:00:00', $window['opens']->format('Y-m-d H:i:s'));
        $this->assertSame('2026-07-16 03:00:00', $window['end']->format('Y-m-d H:i:s'));

        // No length: two hours.
        $short = $this->daily('2026-07-01 20:00', 0);
        $this->assertSame('2026-07-16 02:00:00', $short->checkInWindow('2026-07-15')['end']->format('Y-m-d H:i:s'));
    }

    public function test_the_door_clock_adds_the_date_only_on_another_day(): void
    {
        $show = $this->daily('2026-07-01 20:00', 2);
        $now = $this->at('2026-07-15 21:30');

        $this->assertSame('9:05 PM', $show->doorClockLabel($this->at('2026-07-15 21:05'), $now));
        $this->assertStringContainsString('Jul 14, 2026', $show->doorClockLabel($this->at('2026-07-14 21:05'), $now));

        $show->creatorRole->use_24_hour_time = true;
        $this->assertSame('21:05', $show->doorClockLabel($this->at('2026-07-15 21:05'), $now));
    }
}
