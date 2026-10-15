<?php

namespace App\Services;

use App\Models\User;
use App\Utils\RealtimeTracker;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * The Trends card of /admin/realtime: the last 24 hours, 7 days or 30 days, against the same span
 * before it, cut at the same clock time.
 *
 * Two sources, each in its own clock:
 *  - page views and visits by part of the site come from realtime_hourly (RealtimeRollup), hours
 *    in UTC. It holds counts only and begins on the day it shipped, so until a range is covered the
 *    card says since when it is counting, and a comparison it cannot make is left out rather than
 *    made against nothing. "Visitors" is not one of them: a person seen in two hours cannot be told
 *    from two people without keeping who they were, which realtime_hits is not allowed to.
 *  - sign-ups, new schedules, new events and orders come from audit_logs, written in the app's
 *    timezone and kept 90 days, by the rules of RealtimeActivity::classify() (kind()): the count a
 *    tile gives is the count the Activity rail would give over the same hours. Admins, the demo
 *    account and blocked accounts are left out, with what happened on a blocked account's
 *    schedules and events (BlockedAccounts), as in the rail.
 *
 * The 24-hour range is the current hour and the 23 before it, by the clock, because an hour is the
 * finest grain realtime_hourly has. The day ranges are calendar days in the app's timezone, today
 * included.
 *
 * Nothing here names anybody, so the answer is cached for a minute: unlike the live payload
 * (RealtimeDashboard's class docblock), it leaves nothing on disk that the hour must take away.
 */
class RealtimeTrends
{
    public const RANGES = ['24h', '7d', '30d'];

    public const METRICS = ['views', 'visits', 'signup', 'schedule', 'events', 'order'];

    private const ACTIVITY = ['signup', 'schedule', 'events', 'order'];

    private const CACHE_SECONDS = 60;

    private CarbonImmutable $now;

    public function __construct(public readonly string $range = '24h', ?CarbonImmutable $now = null)
    {
        $this->now = ($now ?? now()->toImmutable())->setTimezone(config('app.timezone'));
    }

    public static function range(?string $range): string
    {
        return in_array($range, self::RANGES, true) ? $range : '24h';
    }

    public function cached(): array
    {
        $use24 = (bool) auth()->user()?->use_24_hour_time;

        return Cache::remember('realtime_trends:'.$this->range.':'.app()->getLocale().':'.(int) $use24, self::CACHE_SECONDS, fn () => $this->build());
    }

    public function build(): array
    {
        [$unit, $count] = match ($this->range) {
            '7d' => ['day', 7],
            '30d' => ['day', 30],
            default => ['hour', 24],
        };
        // Hours by the hour, days by the calendar: a day is 23 or 25 hours when the clocks change.
        $back = fn (CarbonImmutable $at, int $n) => $unit === 'hour' ? $at->subHours($n) : $at->subDays($n);
        $start = $back($unit === 'hour' ? $this->now->startOfHour() : $this->now->startOfDay(), $count - 1);
        $previousStart = $back($start, $count);
        $previousEnd = $back($this->now, $count);

        $use24 = (bool) auth()->user()?->use_24_hour_time;
        $buckets = [];
        for ($i = 0, $at = $start; $i < $count; $i++, $at = $unit === 'hour' ? $at->addHour() : $at->addDay()) {
            $buckets[$this->key($at, $unit)] = [
                'label' => $unit === 'hour' ? $at->format($use24 ? 'H:00' : 'g A') : $at->translatedFormat('M j'),
                'title' => $unit === 'hour' ? $at->translatedFormat('D').' '.$at->format($use24 ? 'H:00' : 'g A') : $at->translatedFormat('D, M j'),
                'current' => $i === $count - 1,
                'start' => $at,
                'views' => null,
                'visits' => null,
            ] + array_fill_keys(self::ACTIVITY, 0);
        }

        $totals = array_fill_keys(self::METRICS, 0);
        $previous = array_fill_keys(self::METRICS, 0);

        $since = RealtimeRollup::since();
        $sinceAt = $since ? CarbonImmutable::parse($since, 'UTC')->setTimezone(config('app.timezone')) : null;
        $surfaces = config('app.is_nexus') ? RealtimeRollup::SURFACES : array_values(array_diff(RealtimeRollup::SURFACES, ['wp']));

        $this->addTraffic($buckets, $totals, $previous, $start, $previousStart, $previousEnd, $sinceAt, $unit, $surfaces);
        $this->addActivity($buckets, $totals, $previous, $start, $previousStart, $previousEnd, $unit);

        // Traffic the rollup had not begun counting: an empty bar is not a quiet hour.
        if (! $sinceAt) {
            $totals['views'] = $totals['visits'] = null;
        }
        if (! $sinceAt || $sinceAt->gt($previousStart)) {
            $previous['views'] = $previous['visits'] = null;
        }

        $change = [];
        foreach (self::METRICS as $metric) {
            $before = $previous[$metric];
            $change[$metric] = $before && $totals[$metric] !== null
                ? round((($totals[$metric] - $before) / $before) * 100)
                : null;
        }

        return [
            'range' => $this->range,
            'unit' => $unit,
            'surfaces' => $surfaces,
            'buckets' => array_values(array_map(function ($bucket) {
                unset($bucket['start']);

                return $bucket;
            }, $buckets)),
            'totals' => $totals,
            'previous' => $previous,
            'change' => $change,
            // Said on the card while the range reaches back past the first hour counted.
            'since' => $sinceAt && $sinceAt->gt($start)
                ? $sinceAt->translatedFormat('M j').', '.$sinceAt->format($use24 ? 'H:00' : 'g A')
                : null,
            'collecting' => ! $sinceAt,
        ];
    }

    private function key(CarbonImmutable $at, string $unit): string
    {
        return $unit === 'hour' ? $at->format('Y-m-d H') : $at->format('Y-m-d');
    }

    private function addTraffic(array &$buckets, array &$totals, array &$previous, CarbonImmutable $start, CarbonImmutable $previousStart, CarbonImmutable $previousEnd, ?CarbonImmutable $since, string $unit, array $surfaces): void
    {
        if (! $since) {
            return;
        }

        // A bar from the first hour counted on is a count, even a zero; one before it is unknown.
        foreach ($buckets as &$bucket) {
            $end = $unit === 'hour' ? $bucket['start']->addHour() : $bucket['start']->addDay();
            if ($end->gt($since)) {
                $bucket['views'] = array_fill_keys($surfaces, 0);
                $bucket['visits'] = array_fill_keys($surfaces, 0);
            }
        }
        unset($bucket);

        $rows = DB::table('realtime_hourly')
            ->where('hour', '>=', RealtimeTracker::ts($previousStart))
            ->where('hour', '<=', RealtimeTracker::ts($this->now))
            ->whereIn('surface', $surfaces)
            ->get(['hour', 'surface', 'page_views', 'visits']);

        foreach ($rows as $row) {
            $hour = CarbonImmutable::parse($row->hour, 'UTC')->setTimezone(config('app.timezone'));

            if ($hour->gte($start)) {
                $key = $this->key($hour, $unit);
                if (isset($buckets[$key]) && $buckets[$key]['views'] !== null) {
                    $buckets[$key]['views'][$row->surface] += (int) $row->page_views;
                    $buckets[$key]['visits'][$row->surface] += (int) $row->visits;
                }
                $totals['views'] += (int) $row->page_views;
                $totals['visits'] += (int) $row->visits;
            } elseif ($hour->lt($previousEnd)) {
                $previous['views'] += (int) $row->page_views;
                $previous['visits'] += (int) $row->visits;
            }
        }
    }

    private function addActivity(array &$buckets, array &$totals, array &$previous, CarbonImmutable $start, CarbonImmutable $previousStart, CarbonImmutable $previousEnd, string $unit): void
    {
        $current = DB::query()
            ->fromSub($this->classified($start, $this->now, true), 'classified')
            ->whereNotNull('kind')
            ->groupBy('hour', 'kind')
            ->get(['hour', 'kind', DB::raw('COUNT(*) as total')]);

        foreach ($current as $row) {
            $key = $unit === 'hour' ? $row->hour : substr((string) $row->hour, 0, 10);
            if (isset($buckets[$key])) {
                $buckets[$key][$row->kind] += (int) $row->total;
            }
            $totals[$row->kind] += (int) $row->total;
        }

        $before = DB::query()
            ->fromSub($this->classified($previousStart, $previousEnd), 'classified')
            ->whereNotNull('kind')
            ->groupBy('kind')
            ->get(['kind', DB::raw('COUNT(*) as total')]);

        foreach ($before as $row) {
            $previous[$row->kind] += (int) $row->total;
        }
    }

    /**
     * Each audit row of the window with its hour (in the app's timezone, as written) and its kind,
     * or a null kind for a row the Activity rail would not count. $toNow takes in the current second.
     */
    private function classified(CarbonImmutable $from, CarbonImmutable $to, bool $toNow = false): \Illuminate\Database\Query\Builder
    {
        $blocked = new BlockedAccounts;
        $excluded = User::where('email', DemoService::DEMO_EMAIL)->orWhere('is_admin', true)->pluck('id')
            ->merge($blocked->userIds())->unique()->values()->all();

        $query = DB::table('audit_logs')
            ->whereIn('action', [
                AuditService::AUTH_REGISTER, AuditService::API_REGISTER, AuditService::AUTH_GOOGLE_LOGIN,
                AuditService::AUTH_FACEBOOK_LOGIN, AuditService::SCHEDULE_CREATE, AuditService::EVENT_CREATE,
                AuditService::SALE_PAID,
            ])
            ->where('created_at', '>=', $from->format('Y-m-d H:i:s'))
            // Up to and including this second for the current span; the span before ends where
            // the same clock time fell then.
            ->where('created_at', $toNow ? '<=' : '<', $to->format('Y-m-d H:i:s'))
            // A sale may have no user (a guest's checkout); everything else must have one.
            ->where(fn ($q) => $q->whereNotNull('user_id')->orWhere('action', AuditService::SALE_PAID))
            ->when($excluded, fn ($q) => $q->where(fn ($inner) => $inner->whereNull('user_id')->orWhereNotIn('user_id', $excluded)))
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m-%d %H') as hour, ".self::kind().' as kind', self::kindBindings());

        if ($blocked->any()) {
            // What happened on a blocked account's schedules and events, whoever did it.
            $query->where(fn ($q) => $q->where('action', '!=', AuditService::SCHEDULE_CREATE)->orWhereNotIn('model_id', $blocked->scheduleIds() ?: [0]))
                ->where(fn ($q) => $q->where('action', '!=', AuditService::EVENT_CREATE)->orWhereNotIn('model_id', $blocked->eventIds()))
                ->where(fn ($q) => $q->where('action', '!=', AuditService::SALE_PAID)->orWhereNull('model_id')
                    ->orWhereNotIn('model_id', DB::table('sales')->select('sales.id')->whereIn('sales.event_id', $blocked->eventIds())));
        }

        return $query;
    }

    /**
     * RealtimeActivity::classify() in SQL, for the four kinds a tile counts. A Google or Facebook
     * sign-up is logged as that provider's login with metadata 'new_account'; a scheduler import
     * carries the console's 'Symfony' user agent; a sale counts once it is paid, not on a mismatch.
     */
    private static function kind(): string
    {
        return "CASE
            WHEN action IN (?, ?) AND model_type = 'User' THEN 'signup'
            WHEN action IN (?, ?) AND metadata = 'new_account' THEN 'signup'
            WHEN action = ? THEN 'schedule'
            WHEN action = ? AND (user_agent IS NULL OR user_agent <> 'Symfony') THEN 'events'
            WHEN action = ? AND JSON_UNQUOTE(JSON_EXTRACT(new_values, '$.status')) = 'paid' THEN 'order'
        END";
    }

    private static function kindBindings(): array
    {
        return [
            AuditService::AUTH_REGISTER, AuditService::API_REGISTER,
            AuditService::AUTH_GOOGLE_LOGIN, AuditService::AUTH_FACEBOOK_LOGIN,
            AuditService::SCHEDULE_CREATE,
            AuditService::EVENT_CREATE,
            AuditService::SALE_PAID,
        ];
    }
}
