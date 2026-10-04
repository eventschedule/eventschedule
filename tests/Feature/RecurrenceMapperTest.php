<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Utils\RecurrenceMapper;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\DataProvider;
use Sabre\VObject\Recur\RRuleIterator;
use Tests\TestCase;

/**
 * RecurrenceMapper turns a calendar's repeat rule into this app's repeating event, and promises
 * to answer null rather than something that is only nearly right.
 *
 * So the test for a mapped rule is not "does it produce these fields" but "do the two agree on
 * every date": the rule is expanded by the calendar library, the mapped fields are put on an
 * Event, and Event::matchesDate() is asked about every single day across the range. A rule that
 * maps to something the app reads differently fails on the first day they disagree.
 */
class RecurrenceMapperTest extends TestCase
{
    private function start(string $at): Carbon
    {
        return Carbon::parse($at, 'UTC');
    }

    /**
     * An Event carrying the mapped fields, set the way EventRepo::saveEvent() sets them from a
     * request (the block that reads schedule_type, recurring_frequency and days_of_week_N).
     */
    private function eventFor(array $mapped, Carbon $start): Event
    {
        $fields = $mapped['fields'];
        $frequency = $fields['recurring_frequency'];

        $days = '1111111';
        if (in_array($frequency, ['weekly', 'every_n_weeks'], true)) {
            $days = '';
            foreach (range(0, 6) as $index) {
                $days .= isset($fields['days_of_week_'.$index]) ? '1' : '0';
            }
        }

        $event = new Event;
        $event->starts_at = $start->format('Y-m-d H:i:s');
        $event->duration = 1;
        $event->recurring_frequency = $frequency;
        $event->recurring_interval = $frequency === 'every_n_weeks' ? max(2, (int) $fields['recurring_interval']) : null;
        $event->days_of_week = $days;
        $event->recurring_end_type = $fields['recurring_end_type'];
        $event->recurring_end_value = $fields['recurring_end_value'];
        $event->recurring_exclude_dates = $fields['recurring_exclude_dates'] ?: null;
        $event->recurring_include_dates = $fields['recurring_include_dates'] ?: null;

        return $event;
    }

    /** Every date the rule itself produces in the range, by the calendar library. */
    private function datesByTheRule(string $rule, Carbon $start, Carbon $end, array $excluded, array $included): array
    {
        $dates = $included;
        $iterator = new RRuleIterator($rule, $start->toDateTimeImmutable());
        while ($iterator->valid() && $iterator->current() <= $end) {
            $dates[] = $iterator->current()->format('Y-m-d');
            $iterator->next();
        }

        $dates = array_values(array_unique(array_diff($dates, $excluded)));
        sort($dates);

        return $dates;
    }

    /** Every date the app says the event falls on in the range, asked one day at a time. */
    private function datesByTheApp(Event $event, Carbon $start, Carbon $end): array
    {
        $dates = [];
        for ($day = $start->copy()->startOfDay(); $day->lte($end); $day->addDay()) {
            if ($event->matchesDate($day, 'UTC')) {
                $dates[] = $day->format('Y-m-d');
            }
        }

        return $dates;
    }

    public static function expressibleRules(): array
    {
        return [
            // rule, first occurrence (UTC), expected frequency, excluded dates, included dates, days to compare
            'every week on the start day' => ['FREQ=WEEKLY', '2026-10-05 18:00', 'weekly', [], [], 400],
            'three days a week' => ['FREQ=WEEKLY;BYDAY=MO,WE,FR', '2026-10-05 18:00', 'weekly', [], [], 400],
            'every other Tuesday' => ['FREQ=WEEKLY;INTERVAL=2;BYDAY=TU', '2026-10-06 09:30', 'every_n_weeks', [], [], 400],
            'every other week, two days, no Sunday' => ['FREQ=WEEKLY;INTERVAL=2;BYDAY=MO,TH', '2026-10-05 18:00', 'every_n_weeks', [], [], 400],
            'every third week from a Thursday start' => ['FREQ=WEEKLY;INTERVAL=3;BYDAY=MO,TH', '2026-10-08 18:00', 'every_n_weeks', [], [], 400],
            'every other week with Sunday, weeks from Sunday' => ['FREQ=WEEKLY;INTERVAL=2;BYDAY=SU,WE;WKST=SU', '2026-10-04 11:00', 'every_n_weeks', [], [], 400],
            'every other Sunday' => ['FREQ=WEEKLY;INTERVAL=2;BYDAY=SU', '2026-10-04 11:00', 'every_n_weeks', [], [], 400],
            'every day' => ['FREQ=DAILY', '2026-10-05 07:00', 'daily', [], [], 120],
            'daily on weekdays is weekly' => ['FREQ=DAILY;BYDAY=MO,TU,WE,TH,FR', '2026-10-05 07:00', 'weekly', [], [], 200],
            'the 15th of each month' => ['FREQ=MONTHLY', '2026-10-15 19:00', 'monthly_date', [], [], 800],
            'the 31st, skipping short months' => ['FREQ=MONTHLY;BYMONTHDAY=31', '2026-10-31 19:00', 'monthly_date', [], [], 800],
            'the second Tuesday' => ['FREQ=MONTHLY;BYDAY=2TU', '2026-10-13 19:00', 'monthly_weekday', [], [], 800],
            'the second Tuesday, by set position' => ['FREQ=MONTHLY;BYDAY=TU;BYSETPOS=2', '2026-10-13 19:00', 'monthly_weekday', [], [], 800],
            'the fifth Friday, when there is one' => ['FREQ=MONTHLY;BYDAY=5FR', '2026-10-30 19:00', 'monthly_weekday', [], [], 800],
            'once a year' => ['FREQ=YEARLY', '2026-12-24 17:00', 'yearly', [], [], 1500],
            'a count becomes an end date' => ['FREQ=WEEKLY;COUNT=5', '2026-10-05 18:00', 'weekly', [], [], 200],
            'a count with a date left out' => ['FREQ=WEEKLY;COUNT=5', '2026-10-05 18:00', 'weekly', ['2026-10-19'], [], 200],
            'a count on a monthly rule' => ['FREQ=MONTHLY;BYDAY=2TU;COUNT=6', '2026-10-13 19:00', 'monthly_weekday', [], [], 400],
            'until, earlier in the day than the event' => ['FREQ=WEEKLY;UNTIL=20261130T000000Z', '2026-10-05 18:00', 'weekly', [], [], 200],
            'until, later in the day than the event' => ['FREQ=WEEKLY;UNTIL=20261130T200000Z', '2026-10-05 18:00', 'weekly', [], [], 200],
            'until a bare date, timed event' => ['FREQ=WEEKLY;UNTIL=20261130', '2026-10-05 18:00', 'weekly', [], [], 200],
            'until a bare date, all-day event' => ['FREQ=WEEKLY;UNTIL=20261130', '2026-10-05 00:00', 'weekly', [], [], 200],
            'dates left out and added' => ['FREQ=WEEKLY;BYDAY=MO', '2026-10-05 18:00', 'weekly', ['2026-10-12', '2026-11-02'], ['2026-10-14'], 200],
        ];
    }

    #[DataProvider('expressibleRules')]
    public function test_a_mapped_rule_falls_on_exactly_the_dates_the_rule_does(
        string $rule, string $startsAt, string $frequency, array $excluded, array $included, int $days
    ): void {
        $start = $this->start($startsAt);
        $end = $start->copy()->addDays($days);

        $mapped = RecurrenceMapper::fromRule($rule, $start, $excluded, $included);

        $this->assertNotNull($mapped, "{$rule} should be expressible");
        $this->assertSame($frequency, $mapped['frequency']);
        $this->assertSame('recurring', $mapped['fields']['schedule_type']);

        $this->assertSame(
            $this->datesByTheRule($rule, $start, $end, $excluded, $included),
            $this->datesByTheApp($this->eventFor($mapped, $start), $start, $end),
            "{$rule} from {$startsAt}"
        );
    }

    public static function inexpressibleRules(): array
    {
        return [
            'the last Friday' => ['FREQ=MONTHLY;BYDAY=-1FR', '2026-10-30 19:00'],
            'the second Tuesday, starting on the third' => ['FREQ=MONTHLY;BYDAY=2TU', '2026-10-20 19:00'],
            'every Tuesday of the month, said monthly' => ['FREQ=MONTHLY;BYDAY=TU', '2026-10-13 19:00'],
            'every other month' => ['FREQ=MONTHLY;INTERVAL=2', '2026-10-15 19:00'],
            'the 1st and the 15th' => ['FREQ=MONTHLY;BYMONTHDAY=1,15', '2026-10-15 19:00'],
            'a day of the month that is not the start' => ['FREQ=MONTHLY;BYMONTHDAY=20', '2026-10-15 19:00'],
            'every three days' => ['FREQ=DAILY;INTERVAL=3', '2026-10-05 07:00'],
            'every hour' => ['FREQ=HOURLY', '2026-10-05 07:00'],
            'twice a day' => ['FREQ=DAILY;BYHOUR=9,17', '2026-10-05 09:00'],
            'the first Monday of September' => ['FREQ=YEARLY;BYMONTH=9;BYDAY=1MO', '2026-09-07 10:00'],
            'every other year' => ['FREQ=YEARLY;INTERVAL=2', '2026-12-24 17:00'],
            // Rule weeks run Monday to Sunday and the app's run Sunday to Saturday, so these two
            // days pair up differently once a week is skipped.
            'every other week with Sunday, weeks from Monday' => ['FREQ=WEEKLY;INTERVAL=2;BYDAY=SU,WE', '2026-10-04 11:00'],
            'every other week, weeks from Wednesday' => ['FREQ=WEEKLY;INTERVAL=2;BYDAY=MO,TH;WKST=WE', '2026-10-05 18:00'],
            'a weekly rule that starts on a day it does not name' => ['FREQ=WEEKLY;BYDAY=TU', '2026-10-05 18:00'],
            'a week number' => ['FREQ=YEARLY;BYWEEKNO=20', '2026-05-11 10:00'],
            'a count too long to walk' => ['FREQ=DAILY;COUNT=5000', '2026-10-05 07:00'],
            'nothing at all' => ['', '2026-10-05 07:00'],
        ];
    }

    #[DataProvider('inexpressibleRules')]
    public function test_a_rule_the_app_cannot_express_exactly_is_refused(string $rule, string $startsAt): void
    {
        $this->assertNull(RecurrenceMapper::fromRule($rule, $this->start($startsAt)));
    }

    public function test_the_fields_are_the_ones_the_event_form_posts(): void
    {
        $mapped = RecurrenceMapper::fromRule(
            'RRULE:FREQ=WEEKLY;INTERVAL=2;BYDAY=MO,TH;UNTIL=20261231T235959Z',
            $this->start('2026-10-05 18:00'),
            ['2026-10-19', 'not a date', '2026-10-19'],
        );

        $this->assertSame([1, 4], $mapped['days']);
        $this->assertSame(2, $mapped['interval']);
        $this->assertSame('2026-12-31', $mapped['until']);
        $this->assertSame([
            'schedule_type' => 'recurring',
            'recurring_frequency' => 'every_n_weeks',
            'recurring_interval' => 2,
            'recurring_end_type' => 'on_date',
            'recurring_end_value' => '2026-12-31',
            'recurring_exclude_dates' => ['2026-10-19'],
            'recurring_include_dates' => [],
            'days_of_week_1' => 1,
            'days_of_week_4' => 1,
        ], $mapped['fields']);
    }

    public function test_the_rule_is_read_as_parts_as_well_as_text(): void
    {
        $fromParts = RecurrenceMapper::fromRule(['FREQ' => 'WEEKLY', 'BYDAY' => ['MO', 'WE']], $this->start('2026-10-05 18:00'));
        $fromOne = RecurrenceMapper::fromRule(['freq' => 'weekly', 'byday' => 'MO'], $this->start('2026-10-05 18:00'));

        $this->assertSame([1, 3], $fromParts['days']);
        $this->assertSame([1], $fromOne['days']);
        $this->assertNull($fromOne['until']);
        $this->assertSame('never', $fromOne['fields']['recurring_end_type']);
    }

    public function test_an_end_is_a_date_in_the_events_own_zone(): void
    {
        // 20:00 in Los Angeles. UNTIL is in UTC: 03:30 on the 1st is still 19:30 on the 30th
        // there, half an hour before the event, so the 30th is already out.
        $start = Carbon::parse('2026-11-02 20:00', 'America/Los_Angeles');

        $before = RecurrenceMapper::fromRule('FREQ=WEEKLY;UNTIL=20261201T033000Z', $start);
        $after = RecurrenceMapper::fromRule('FREQ=WEEKLY;UNTIL=20261201T043000Z', $start);

        $this->assertSame('2026-11-29', $before['until']);
        $this->assertSame('2026-11-30', $after['until']);
    }
}
