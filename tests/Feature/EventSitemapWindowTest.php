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

    /** Whether matchesDate() finds an occurrence from the grace cut-off onwards. */
    private function occursSinceTheCut(Event $event): bool
    {
        $timezone = $event->scheduleTimezone();
        $day = Carbon::now($timezone)->subDays(Event::SITEMAP_GRACE_DAYS)->startOfDay();

        for ($i = 0; $i < 400; $i++, $day->addDay()) {
            if ($event->matchesDate($day->format('Y-m-d'), $timezone)) {
                return true;
            }
        }

        return false;
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
