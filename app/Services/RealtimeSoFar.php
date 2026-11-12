<?php

namespace App\Services;

use App\Utils\DayPace;
use App\Utils\RealtimeTracker;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * The "So far today, against before" card of /admin/dashboard: visits and sign-ups hour by hour
 * as running totals, today until now against yesterday and against a usual day of the same
 * weekday (the same weekday in each of the four weeks before), with today's total projected from
 * what is in so far. Brought over from Stackapella's Growth page (2026-11-12).
 *
 * The sources are the Trends card's (RealtimeTrends): visits from realtime_hourly, kept 62 days in
 * UTC hours, all of it exact, so nothing here is estimated; sign-ups from audit_logs by the
 * Activity rail's rules. Days are calendar days in the viewer's zone, as everywhere on the page. A
 * day the hourly counts had not begun is left out rather than read as a quiet one: yesterday when
 * counting began after its midnight, and each usual weekday likewise, so the usual band needs two
 * whole weekdays of counts and the projection a week.
 *
 * The page decides how each gap is said (fairVerdict in admin/realtime/_script) from what this
 * gives it: today's count, the count by the same time yesterday, and the usual day's mean by then
 * with how many days it is the mean of. Nothing here names anybody, so it is cached for a minute.
 */
class RealtimeSoFar
{
    public const METRICS = ['visits', 'signups'];

    /**
     * Today hour by hour, or the last 7 days, the last 4 weeks or this month day by day. No 12
     * weeks as Stackapella has: its comparison needs 24 weeks of visits, and realtime_hourly keeps
     * 62 days (RealtimeRollup::KEEP_DAYS).
     */
    public const PERIODS = ['today', '7d', '28d', 'month'];

    /** The same weekday this many weeks back makes a usual day. */
    private const USUAL_WEEKS = 4;

    /** A usual band needs at least this many of them. */
    private const USUAL_MIN = 2;

    private const CACHE_SECONDS = 60;

    private CarbonImmutable $now;

    private string $zone;

    public function __construct(?CarbonImmutable $now = null, ?string $timezone = null, public readonly string $period = 'today')
    {
        $this->zone = RealtimeTrends::viewerZone($timezone);
        $this->now = ($now ?? now()->toImmutable())->setTimezone($this->zone);
    }

    public static function period(?string $period): string
    {
        return in_array($period, self::PERIODS, true) ? $period : 'today';
    }

    public function cached(): array
    {
        $use24 = (bool) auth()->user()?->use_24_hour_time;
        $blocked = md5(implode(',', (new BlockedAccounts)->userIds()));

        // By the minute: the card's clock and its cut move with it.
        return Cache::remember('realtime_so_far:'.$this->period.':'.$this->now->format('Y-m-d H:i').':'.app()->getLocale().':'.(int) $use24.':'.$this->zone.':'.$blocked, self::CACHE_SECONDS, fn () => $this->build());
    }

    public function build(): array
    {
        $use24 = (bool) auth()->user()?->use_24_hour_time;
        $today = $this->now->startOfDay();
        // Today reads the four weeks before it; the longer periods as far back as the period
        // before them goes (8 weeks for 4 weeks, the start of last month), inside the 62 days
        // realtime_hourly keeps.
        $first = $this->period === 'today' ? $today->subDays(7 * self::USUAL_WEEKS)
            : $today->subDays(55)->min($today->startOfMonth()->subMonthNoOverflow());
        $series = fn (array $days, ?CarbonImmutable $since, CarbonImmutable $cut, int $past, bool $stale = false) => $this->period === 'today'
            ? $this->series($days, $since, $cut, $past, $use24)
            : $this->periodSeries($days, $since, $cut, $past, $use24, $stale);

        $since = RealtimeRollup::since();
        $sinceAt = $since ? CarbonImmutable::parse($since, 'UTC')->setTimezone($this->zone) : null;

        // Visits are counted a few minutes behind (RealtimeRollup runs with the prune, every five
        // minutes, and holds back the last half minute), so today's line and every count it is
        // set against are cut where the counting has reached, not at now: cut at now, today read
        // a few minutes short against complete days, and "behind" every morning.
        $through = RealtimeRollup::tablesExist() ? DB::table('realtime_rollup')->where('id', 1)->value('through_at') : null;
        // Never before today's midnight: just after it, or with the rollup stopped, the card says
        // "0 at 12:00 AM", not a time from another day.
        $visitsCut = $through ? CarbonImmutable::parse($through, 'UTC')->setTimezone($this->zone)->min($this->now)->max($this->now->startOfDay()) : $this->now;
        // Counted only to some time before today (the cron has stopped): yesterday is part of the
        // longer periods and is short, so they are not compared, or they would read as a drop.
        $stale = $through !== null && CarbonImmutable::parse($through, 'UTC')->lt($this->now->startOfDay());

        // A source's hours begin where its own clock's hours begin: realtime_hourly's are UTC
        // hours and audit_logs' the app's. In a zone half or three quarters of an hour off them, a
        // slot runs from :30 or :45, and the cut is counted from there.
        $offset = $this->now->utcOffset();
        $appOffset = $this->now->setTimezone(config('app.timezone'))->utcOffset();

        return [
            'date' => $today->format('Y-m-d'),
            'zone' => $this->zone,
            'period' => $this->period,
            'cut' => $this->clock($this->now, $use24),
            'weekday' => $this->now->translatedFormat('l'),
            'metrics' => [
                'visits' => $sinceAt ? $series($this->visitsByDay($first, $sinceAt), $sinceAt, $visitsCut, self::minutesPast($offset), $stale) : null,
                'signups' => $series($this->signupsByDay($first), null, $this->now, self::minutesPast($offset - $appOffset)),
            ],
        ];
    }

    /** How many minutes past a source's hour the viewer's hours fall: 0, or 30 or 45 in a part-hour zone. */
    private static function minutesPast(int $offsetMinutes): int
    {
        return (($offsetMinutes % 60) + 60) % 60;
    }

    /** A clock time, isolated so a right-to-left page keeps "4:59 PM" in its order. */
    private function clock(CarbonImmutable $at, bool $use24, bool $minutes = true): string
    {
        return "\u{2066}".$at->format($minutes ? ($use24 ? 'H:i' : 'g:i A') : ($use24 ? 'H:i' : 'g A'))."\u{2069}";
    }

    /**
     * One metric's lines and figures, from its 24 counts per local day.
     *
     * @param  array<string, list<int>>  $days  'Y-m-d' => 24 hourly counts, D0-28 to today
     * @param  CarbonImmutable|null  $since  the first hour counted, or null for a source that has always counted
     * @param  CarbonImmutable  $cut  how far today is counted: now, or less for a source that lags
     * @param  int  $past  minutes past the local hour each slot begins (see build())
     */
    private function series(array $days, ?CarbonImmutable $since, CarbonImmutable $cut, int $past, bool $use24): array
    {
        $today = $this->now->startOfDay();
        // The cut as minutes into the first slot of the day; slot h runs from h:$past.
        $minutes = $cut->lt($today) ? 0 : max(0, $cut->hour * 60 + $cut->minute - $past);
        $hour = min(23, intdiv($minutes, 60));
        $minute = $minutes - $hour * 60;
        // A day is used only if it was counted from its midnight.
        $whole = fn (CarbonImmutable $day) => $since === null || $since->lte($day);
        $counts = fn (CarbonImmutable $day) => $days[$day->format('Y-m-d')] ?? array_fill(0, 24, 0);

        // Each slot's start, read off a day with no clock change, so a 23-hour day never prints
        // one hour twice.
        $hours = [];
        for ($h = 0; $h < 24; $h++) {
            $hours[] = $this->clock(CarbonImmutable::create(2000, 1, 3, $h, $past, 0, 'UTC'), $use24, $past > 0);
        }

        $current = $counts($today);
        $soFar = (int) array_sum($current);

        $yesterday = $today->subDay();
        $previous = $whole($yesterday) ? $counts($yesterday) : null;

        $usualDays = [];
        for ($j = 1; $j <= self::USUAL_WEEKS; $j++) {
            $day = $today->subWeeks($j);
            if ($whole($day)) {
                $usualDays[] = $counts($day);
            }
        }

        $usual = null;
        if (count($usualDays) >= self::USUAL_MIN) {
            $runs = array_map(fn ($d) => DayPace::running($d), $usualDays);
            $column = fn (int $h) => array_map(fn ($run) => $run[$h], $runs);
            $byNow = array_map(fn ($d) => DayPace::byTime($d, $hour, $minute), $usualDays);
            $usual = [
                'low' => array_map(fn ($h) => min($column($h)), range(0, 23)),
                'high' => array_map(fn ($h) => max($column($h)), range(0, 23)),
                'mean' => array_map(fn ($h) => round(array_sum($column($h)) / count($runs), 1), range(0, 23)),
                'byNow' => round(array_sum($byNow) / count($byNow), 1),
                'lowByNow' => round(min($byNow), 1),
                'highByNow' => round(max($byNow), 1),
                'available' => count($usualDays),
            ];
        }

        // The usual day's shape, from every whole day counted in the four weeks before today.
        $weights = array_fill(0, 24, 0);
        $counted = 0;
        for ($d = 1; $d <= 7 * self::USUAL_WEEKS; $d++) {
            $day = $today->subDays($d);
            if ($whole($day)) {
                $counted++;
                foreach ($counts($day) as $h => $n) {
                    $weights[$h] += $n;
                }
            }
        }
        $weeks = intdiv($counted, 7);
        $share = DayPace::shape($weights, $weeks, $minutes);
        $projection = DayPace::project($soFar, $share, $counted >= 7);

        $path = null;
        if ($projection['shown']) {
            // From what is in now to each end, along a usual day's shape: at the cut every line
            // starts from today's count, and the rest of the day is shared out by the shape.
            $along = function (int $h, int $end) use ($weights, $weeks, $share, $soFar) {
                $part = $share >= 1 ? 1.0 : (DayPace::shape($weights, $weeks, ($h + 1) * 60) - $share) / (1 - $share);

                return round($soFar + ($end - $soFar) * max(0.0, min(1.0, $part)), 1);
            };
            $path = [];
            foreach (['value' => 'mean', 'low' => 'low', 'high' => 'high'] as $from => $to) {
                $path[$to] = array_map(fn ($h) => $along($h, $projection[$from]), range($hour, 23));
            }
        }
        $showsFrom = $projection['hidden'] === 'early' ? DayPace::reaches($weights, $weeks, DayPace::PROJECT_SHARE) : null;

        return [
            'cut' => ['slot' => $hour, 'fraction' => round($minute / 60, 3), 'label' => $this->clock($cut, $use24)],
            'hours' => $hours,
            // The x-axis: the day's start, every six hours, and the start of tomorrow.
            'ticks' => [$hours[0], $hours[6], $hours[12], $hours[18], $hours[0]],
            'now' => array_map(fn ($h) => $h <= $hour ? $current[$h] : null, range(0, 23)),
            'soFar' => $soFar,
            'previous' => $previous,
            'previousTotal' => $previous === null ? null : (int) array_sum($previous),
            'previousByNow' => $previous === null ? null : round(DayPace::byTime($previous, $hour, $minute), 1),
            'usual' => $usual,
            'projection' => $projection + [
                'path' => $path,
                'showsFrom' => $showsFrom === null ? null : $hours[$showsFrom],
            ],
        ];
    }

    /**
     * One metric day by day over the last 7 days, the last 4 weeks or this month, against the
     * period before it cut at the same clock time: whole days and one day cut at the cut on each
     * side. The 7 days are also set against a usual week, the mean of the four windows before.
     *
     * @param  array<string, list<int>>  $days  'Y-m-d' => 24 hourly counts
     */
    private function periodSeries(array $days, ?CarbonImmutable $since, CarbonImmutable $cut, int $past, bool $use24, bool $stale = false): array
    {
        $today = $this->now->startOfDay();
        $minutes = $cut->lt($today) ? 0 : max(0, $cut->hour * 60 + $cut->minute - $past);
        $hour = min(23, intdiv($minutes, 60));
        $minute = $minutes - $hour * 60;
        $whole = fn (CarbonImmutable $day) => $since === null || $since->lte($day);
        $hours = fn (CarbonImmutable $day) => $days[$day->format('Y-m-d')] ?? array_fill(0, 24, 0);
        $total = fn (CarbonImmutable $day) => (int) array_sum($hours($day));
        // Whole days from $from up to the cut on $cutDay, or null when counting had not begun.
        $window = function (CarbonImmutable $from, CarbonImmutable $cutDay) use ($whole, $total, $hours, $hour, $minute) {
            if (! $whole($from)) {
                return null;
            }
            $sum = 0.0;
            for ($day = $from; $day->lt($cutDay); $day = $day->addDay()) {
                $sum += $total($day);
            }

            return $sum + DayPace::byTime($hours($cutDay), $hour, $minute);
        };

        // The usual day's shape, for today's share and the projections.
        $weights = array_fill(0, 24, 0);
        $counted = 0;
        for ($d = 1; $d <= 28; $d++) {
            $day = $today->subDays($d);
            if ($whole($day)) {
                $counted++;
                foreach ($hours($day) as $h => $n) {
                    $weights[$h] += $n;
                }
            }
        }
        $share = DayPace::shape($weights, intdiv($counted, 7), $minutes);

        $lastMonth = $today->startOfMonth()->subMonthNoOverflow();
        if ($this->period === 'month') {
            $first = $today->startOfMonth();
            $count = $today->daysInMonth;
            $previousOf = fn (CarbonImmutable $day) => $day->day <= $lastMonth->daysInMonth ? $lastMonth->addDays($day->day - 1) : null;
        } else {
            $span = $this->period === '28d' ? 28 : 7;
            $first = $today->subDays($span - 1);
            $count = $span;
            $previousOf = fn (CarbonImmutable $day) => $day->subDays($span);
        }

        $slots = [];
        $now = [];
        $previous = [];
        $todayIndex = 0;
        for ($i = 0; $i < $count; $i++) {
            $day = $first->addDays($i);
            if ($day->eq($today)) {
                $todayIndex = $i;
            }
            $slots[] = [
                'date' => $day->format('Y-m-d'),
                'label' => $this->period === '7d' ? $day->translatedFormat('D') : $day->translatedFormat('M j'),
                'title' => $day->translatedFormat('D, M j'),
            ];
            // A day before counting began is unknown, not a quiet day; today is shown as it stands.
            $now[] = $day->lte($today) && ($whole($day) || $day->eq($today)) ? $total($day) : null;
            $before = $previousOf($day);
            $previous[] = $before && $whole($before) ? $total($before) : null;
        }

        $soFar = (int) array_sum(array_filter($now, fn ($n) => $n !== null));
        $todayCount = $total($today);

        // The period before, cut at the same time, and for 7 days a usual week as well.
        $compare = ['previous' => null, 'usual' => null];
        if ($this->period === 'month') {
            $n = $today->day;
            $length = $lastMonth->daysInMonth;
            if ($n <= $length) {
                $expected = $window($lastMonth, $lastMonth->addDays($n - 1));
                $compare['previous'] = $expected === null ? null : ['expected' => round($expected, 1), 'scaled' => false];
            } elseif ($whole($lastMonth)) {
                // Past last month's last day: all of it, at its daily rate, for as many days.
                $all = 0;
                for ($day = $lastMonth; $day->lt($today->startOfMonth()); $day = $day->addDay()) {
                    $all += $total($day);
                }
                $compare['previous'] = ['expected' => round($all * (($n - 1) + $share) / $length, 1), 'scaled' => true];
            }
            $previousTotal = null;
            if ($whole($lastMonth)) {
                $previousTotal = 0;
                for ($day = $lastMonth; $day->lt($today->startOfMonth()); $day = $day->addDay()) {
                    $previousTotal += $total($day);
                }
            }
        } else {
            $expected = $window($first->subDays($count), $today->subDays($count));
            $compare['previous'] = $expected === null ? null : ['expected' => round($expected, 1), 'scaled' => false];
            $previousTotal = in_array(null, $previous, true) ? null : (int) array_sum($previous);

            if ($this->period === '7d') {
                $windows = [];
                for ($j = 1; $j <= self::USUAL_WEEKS; $j++) {
                    $value = $window($first->subWeeks($j), $today->subWeeks($j));
                    if ($value !== null) {
                        $windows[] = $value;
                    }
                }
                if (count($windows) >= self::USUAL_MIN) {
                    $compare['usual'] = [
                        'expected' => round(array_sum($windows) / count($windows), 1),
                        'low' => (int) round(min($windows)),
                        'high' => (int) round(max($windows)),
                        'available' => count($windows),
                    ];
                }
            }
        }

        // Today projected to midnight, and the period with it; the month by its daily rate.
        $history = $counted >= 7;
        $day = DayPace::project($todayCount, $share, $history);
        if (! $whole($first)) {
            // Counting began inside the period: its total so far is short, so is not projected.
            $projection = ['shown' => false, 'hidden' => 'noHistory', 'value' => null, 'low' => null, 'high' => null];
        } elseif ($this->period === 'month') {
            $projection = DayPace::projectMonth($soFar, ($today->day - 1) + $share, $today->daysInMonth, $history);
        } else {
            $before = $soFar - $todayCount;
            $projection = $day['shown']
                ? ['shown' => true, 'hidden' => null, 'value' => $before + $day['value'], 'low' => $before + $day['low'], 'high' => $before + $day['high']]
                : $day;
        }

        if ($stale) {
            $compare = ['previous' => null, 'usual' => null];
        }

        return [
            'kind' => 'days',
            'cut' => ['label' => $this->clock($cut, $use24)],
            'slots' => $slots,
            'todayIndex' => $todayIndex,
            'now' => $now,
            'previous' => $previous,
            'previousTotal' => $previousTotal,
            'soFar' => $soFar,
            'todayCount' => $todayCount,
            'compare' => $compare,
            'projection' => $projection + [
                'day' => $day['shown'] ? ['value' => $day['value'], 'low' => $day['low'], 'high' => $day['high']] : null,
            ],
        ];
    }

    /**
     * Visits by local day and hour, from realtime_hourly's UTC hours, over the parts of the site
     * the Trends card counts, a blocked account's pages left out.
     *
     * @return array<string, list<int>>
     */
    private function visitsByDay(CarbonImmutable $first, CarbonImmutable $since): array
    {
        $surfaces = config('app.is_nexus') ? RealtimeRollup::SURFACES : array_values(array_diff(RealtimeRollup::SURFACES, ['wp']));

        $query = DB::table('realtime_hourly');
        if (RealtimeRollup::breakdownsExist()) {
            (new BlockedAccounts)->withoutTheirCounts($query, 'realtime_hourly');
        }

        $rows = $query
            ->where('hour', '>=', RealtimeTracker::ts($first->max($since)))
            ->where('hour', '<=', RealtimeTracker::ts($this->now))
            ->whereIn('surface', $surfaces)
            ->groupBy('hour')
            ->get(['hour', DB::raw('SUM(visits) as total')]);

        $days = [];
        foreach ($rows as $row) {
            $this->add($days, CarbonImmutable::parse($row->hour, 'UTC'), (int) $row->total);
        }

        return $days;
    }

    /**
     * Sign-ups by local day and hour, by the Activity rail's rules (RealtimeTrends::classified()).
     * audit_logs is written in the app's zone, so each hour is read in it first.
     *
     * @return array<string, list<int>>
     */
    private function signupsByDay(CarbonImmutable $first): array
    {
        $rows = DB::query()
            ->fromSub(RealtimeTrends::classified($first, $this->now, true), 'classified')
            ->where('kind', 'signup')
            ->groupBy('hour')
            ->get(['hour', DB::raw('COUNT(*) as total')]);

        $days = [];
        foreach ($rows as $row) {
            $this->add($days, CarbonImmutable::createFromFormat('Y-m-d H', (string) $row->hour, config('app.timezone'))->startOfHour(), (int) $row->total);
        }

        return $days;
    }

    /** @param  array<string, list<int>>  $days */
    private function add(array &$days, CarbonImmutable $hour, int $count): void
    {
        $local = $hour->setTimezone($this->zone);
        $key = $local->format('Y-m-d');
        $days[$key] ??= array_fill(0, 24, 0);
        $days[$key][$local->hour] += $count;
    }
}
