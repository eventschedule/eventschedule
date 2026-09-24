<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Role;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The sitemaps list a one-off event until 30 days after it ends, and a recurring series while it
 * still runs (Event::constrainSitemapWindow()). 86% of the event URLs they submitted were past, 966
 * of them by more than a month.
 *
 * Sitemap hygiene only: nothing here makes a page noindex. And the SQL has to answer the
 * recurring half without JSON functions, so beside the exact expectations every series is also
 * checked against matchesDate() itself - the window may keep a series that has ended, but never
 * drops one that still occurs.
 */
class EventSitemapWindowTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private Role $role;

    protected function setUp(): void
    {
        parent::setUp();

        $this->role = $this->createRole($this->createOwner(), 'venue', ['timezone' => 'America/New_York']);
    }

    /** Noon UTC $days from today, so the UTC and New York calendar days agree. */
    private function day(int $days): Carbon
    {
        return Carbon::now('UTC')->addDays($days)->setTime(12, 0);
    }

    private function oneOff(int $startsInDays, float $durationHours = 2): Event
    {
        return $this->createEvent($this->role, [
            'starts_at' => $this->day($startsInDays)->format('Y-m-d H:i:s'),
            'duration' => $durationHours,
            'creator_role_id' => $this->role->id,
        ]);
    }

    /** A weekly series on the weekday it starts on, unless $attrs says otherwise. */
    private function series(int $startsInDays, array $attrs = []): Event
    {
        $start = $this->day($startsInDays);
        $days = str_repeat('0', 7);
        $days[$start->dayOfWeek] = '1';

        return $this->createRecurringEvent($this->role, array_merge([
            'starts_at' => $start->format('Y-m-d H:i:s'),
            'days_of_week' => $days,
            'recurring_frequency' => 'weekly',
            'creator_role_id' => $this->role->id,
        ], $attrs));
    }

    /** The ids in the window, now. */
    private function inWindow(): array
    {
        return Event::query()->inSitemapWindow()->pluck('id')->all();
    }

    /**
     * Whether matchesDate() finds an occurrence from the grace cut-off onwards. 1,500 days, because
     * a yearly series from Feb 29 next occurs four years on.
     */
    private function occursSinceTheCut(Event $event): bool
    {
        $timezone = $event->scheduleTimezone();
        $day = Carbon::now($timezone)->subDays(Event::SITEMAP_GRACE_DAYS)->startOfDay();

        for ($i = 0; $i < 1500; $i++, $day->addDay()) {
            if ($event->matchesDate($day->format('Y-m-d'), $timezone)) {
                return true;
            }
        }

        return false;
    }

    /**
     * A monthly_weekday series whose first date is a local 29th to 31st, so its nth weekday is the
     * 5th: started about ten months back at $localTime New York time, with enough occurrences that
     * the last of them is still to come. $monthEnd starts it on the last day of its month, so an
     * evening start is already the next month's 1st in UTC.
     */
    private function fifthWeekdaySeries(string $localTime, bool $monthEnd): Event
    {
        $timezone = $this->role->timezone;
        $start = Carbon::now($timezone)->subDays(300)->startOfDay();

        while ($start->day < 29 || ($monthEnd && $start->day !== $start->daysInMonth)) {
            $start->subDay();
        }

        [$hour, $minute] = array_map('intval', explode(':', $localTime));
        $start->setTime($hour, $minute);

        $event = $this->createRecurringEvent($this->role, [
            'starts_at' => $start->copy()->utc()->format('Y-m-d H:i:s'),
            'days_of_week' => '1111111',
            'recurring_frequency' => 'monthly_weekday',
            'creator_role_id' => $this->role->id,
        ]);

        // Every occurrence through the next 120 days. A 5th weekday is never more than 119 days
        // after the one before it, so the last one counted is still ahead.
        $count = 0;
        $until = Carbon::now($timezone)->addDays(120)->format('Y-m-d');

        for ($day = $start->copy()->startOfDay(); $day->format('Y-m-d') <= $until; $day->addDay()) {
            $count += $event->matchesDate($day->format('Y-m-d'), $timezone) ? 1 : 0;
        }

        $event->recurring_end_type = 'after_events';
        $event->recurring_end_value = (string) $count;
        $event->save();

        return $event->fresh();
    }

    public function test_a_one_off_event_stays_until_thirty_days_after_it_ends(): void
    {
        $expected = [
            'next week' => [$this->oneOff(7), true],
            'twenty days ago' => [$this->oneOff(-20), true],
            'forty days ago' => [$this->oneOff(-40), false],
            // Started 31 days ago but ran three days, so it ended 28 days ago.
            'multi-day, ended recently' => [$this->oneOff(-31, 72), true],
            'multi-day, ended long ago' => [$this->oneOff(-60, 48), false],
        ];

        $in = $this->inWindow();

        foreach ($expected as $label => [$event, $listed]) {
            $this->assertSame($listed, in_array($event->id, $in, true), $label);
        }
    }

    public function test_a_series_stays_while_it_runs(): void
    {
        $expected = [
            'never ends' => [$this->series(-400), true],
            'ended on a date two months ago' => [$this->series(-200, [
                'recurring_end_type' => 'on_date',
                'recurring_end_value' => $this->day(-60)->format('Y-m-d'),
            ]), false],
            'ended on a date ten days ago' => [$this->series(-200, [
                'recurring_end_type' => 'on_date',
                'recurring_end_value' => $this->day(-10)->format('Y-m-d'),
            ]), true],
            'ends on a date next month' => [$this->series(-200, [
                'recurring_end_type' => 'on_date',
                'recurring_end_value' => $this->day(30)->format('Y-m-d'),
            ]), true],
            'five occurrences, long done' => [$this->series(-200, [
                'recurring_end_type' => 'after_events',
                'recurring_end_value' => '5',
            ]), false],
            'thirty occurrences, still going' => [$this->series(-200, [
                'recurring_end_type' => 'after_events',
                'recurring_end_value' => '30',
            ]), true],
            // Nine weekly occurrences from 95 days ago end 39 days ago: out.
            'nine occurrences' => [$this->series(-95, [
                'recurring_end_type' => 'after_events',
                'recurring_end_value' => '9',
            ]), false],
            // Two of them excluded push the ninth to 25 days ago: in. countOccurrences() skips an
            // excluded date, so the window has to count them too.
            'nine occurrences, two excluded' => [$this->series(-95, [
                'recurring_end_type' => 'after_events',
                'recurring_end_value' => '9',
                'recurring_exclude_dates' => [$this->day(-88)->format('Y-m-d'), $this->day(-81)->format('Y-m-d')],
            ]), true],
            'every weekday unchecked' => [$this->series(-100, ['days_of_week' => '0000000']), false],
            'every weekday unchecked, with an include date' => [$this->series(-100, [
                'days_of_week' => '0000000',
                'recurring_include_dates' => [$this->day(14)->format('Y-m-d')],
            ]), true],
            // A daily series ignores days_of_week (matchesFrequency() never reads it).
            'daily, no weekdays stored' => [$this->series(-100, [
                'days_of_week' => '0000000',
                'recurring_frequency' => 'daily',
            ]), true],
        ];

        $in = $this->inWindow();

        foreach ($expected as $label => [$event, $listed]) {
            $event = $event->fresh();

            $this->assertSame($listed, $this->occursSinceTheCut($event), $label.': the fixture is not what it claims');
            $this->assertSame($listed, in_array($event->id, $in, true), $label);
        }
    }

    /**
     * The event form stores any huge count as 9223372036854775807 (saveEvent() keeps
     * (string)(int), which saturates), and a restored backup keeps whatever text it carried. The
     * bound's CAST AS UNSIGNED overflowed on a huge or negative count, with ERROR 1690.
     */
    public function test_a_malformed_after_events_count_neither_errors_nor_drops_a_running_series(): void
    {
        $counts = ['9223372036854775807', '99999999999999999999', '-1', 'abc', '1e5'];

        $events = collect($counts)->mapWithKeys(fn (string $count) => [$count => $this->series(-200, [
            'recurring_end_type' => 'after_events',
            'recurring_end_value' => $count,
        ])]);

        $in = $this->inWindow();

        foreach ($events as $count => $event) {
            $this->assertSame($this->occursSinceTheCut($event->fresh()), in_array($event->id, $in, true), "after_events '{$count}'");
        }

        // The huge ones and the exponent are running series, which is what makes this a test.
        $this->assertContains($events['9223372036854775807']->id, $in);
        $this->assertContains($events['1e5']->id, $in);
    }

    /**
     * countOccurrences() counts only the months that have a monthly_weekday series' nth weekday. A
     * 5th weekday (a local 29th to 31st) can be 119 days after the one before, so a 35-day period
     * dropped such a series while it was still running. The evening one starts on a New York
     * month end, which in UTC is already the next month's 1st.
     */
    public function test_a_fifth_weekday_series_stays_listed_while_it_runs(): void
    {
        $expected = [
            'fifth weekday, at midday' => $this->fifthWeekdaySeries('12:00', false),
            'fifth weekday, a month-end evening' => $this->fifthWeekdaySeries('21:00', true),
        ];

        $in = $this->inWindow();

        foreach ($expected as $label => $event) {
            $this->assertTrue($this->occursSinceTheCut($event), $label.': the fixture is not what it claims');
            $this->assertContains($event->id, $in, $label);
        }
    }

    /**
     * Regression pins, not mutation-tested: these pass with the old SQL and the new alike, and are
     * here so a later "tightening" of the periods cannot slip through. countOccurrences() counts
     * addMonth() and addYear() steps, which are at most 31 and 366 days, so those periods bound a
     * series on the 31st, which skips the short months, and one on Feb 29, which occurs every
     * fourth year.
     *
     * Each is checked at the one moment the bound is tight: the last day the grace period still
     * covers the series' last date. Any earlier and the bound has room to spare, so a period one
     * day too short passes unnoticed.
     */
    public function test_series_on_the_31st_and_on_feb_29_stay_listed_until_their_last_date_ages_out(): void
    {
        $timezone = $this->role->timezone;

        $cases = [
            // The addMonth() steps from Jan 31 run Mar 3, Apr 3 and on, so the seventh step is
            // Aug 3 and the last 31st it admits is Aug 31: 212 days on, against a bound of 217.
            'monthly on the 31st, after 7' => ['2026-01-31', 'monthly_date', 7, '2026-08-31'],
            // "after 4" from one Feb 29 ends on the next: 1,461 days on, against a bound of 1,464.
            'yearly on Feb 29, after 4' => ['2024-02-29', 'yearly', 4, '2028-02-29'],
        ];

        foreach ($cases as $label => [$start, $frequency, $count, $last]) {
            $event = $this->createRecurringEvent($this->role, [
                'starts_at' => Carbon::parse($start.' 12:00', $timezone)->utc()->format('Y-m-d H:i:s'),
                'days_of_week' => '1111111',
                'recurring_frequency' => $frequency,
                'recurring_end_type' => 'after_events',
                'recurring_end_value' => (string) $count,
                'creator_role_id' => $this->role->id,
            ]);

            $this->travelTo(Carbon::parse($last.' 12:00', $timezone)->addDays(Event::SITEMAP_GRACE_DAYS));
            $this->assertTrue($this->occursSinceTheCut($event), $label.': the fixture is not what it claims');
            $this->assertContains($event->id, $this->inWindow(), $label);

            // A day later it has aged out, which is what makes the moment above the edge.
            $this->travel(1)->days();
            $this->assertFalse($this->occursSinceTheCut($event), "{$label}: {$last} is not its last date");

            $this->travelBack();
        }
    }

    /**
     * The same overflow, where a visitor meets it: the schedule page's upcoming list runs the
     * window, and the error 500'd the page for everyone.
     */
    public function test_the_schedule_page_survives_a_huge_after_events_count(): void
    {
        $this->series(-14, [
            'name' => 'Endless Jam',
            'recurring_end_type' => 'after_events',
            'recurring_end_value' => '9223372036854775807',
        ]);

        $html = $this->get('/'.$this->role->subdomain)->assertOk()->getContent();

        // Listed, not merely survived: it is weekly, well inside the 60 days the list looks ahead.
        $this->assertSame(1, preg_match('#<noscript v-pre>(.*?)</noscript>#s', $html, $noscript));
        $this->assertStringContainsString('Endless Jam', $noscript[1]);
    }

    /** Both sitemaps apply it, and a schedule whose only event has aged out is not submitted. */
    public function test_the_sitemaps_apply_the_window(): void
    {
        $fresh = $this->oneOff(-5);
        $stale = $this->oneOff(-45);

        $quiet = $this->createRole($this->createOwner(), 'venue');
        $this->createEvent($quiet, ['starts_at' => $this->day(-45)->format('Y-m-d H:i:s'), 'creator_role_id' => $quiet->id]);

        foreach (['/sitemap-events-1.xml', '/'.$this->role->subdomain.'/sitemap.xml'] as $path) {
            $xml = $this->get($path)->assertOk()->streamedContent();

            $this->assertStringContainsString('/'.$fresh->slug.'/', $xml, $path);
            $this->assertStringNotContainsString('/'.$stale->slug.'/', $xml, $path);
        }

        $schedules = $this->get('/sitemap-schedules-1.xml')->assertOk()->streamedContent();

        $this->assertStringContainsString($this->role->getCanonicalUrl().'<', $schedules);
        $this->assertStringNotContainsString($quiet->getCanonicalUrl().'<', $schedules);

        // Hygiene, not a verdict: the aged-out event's own page is still indexable.
        $html = $this->get($this->guestEventUrl($this->role, $stale))->assertOk()->getContent();
        $this->assertMatchesRegularExpression('/<meta name="robots" content="index, follow\b[^"]*">/', $html);
    }
}
