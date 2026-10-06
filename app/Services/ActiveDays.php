<?php

namespace App\Services;

use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * Who used the app on which day: the one definition of an "active user".
 *
 * A person is active on a day if they loaded a page of the app while signed in that day
 * (App\Http\Middleware\RecordActiveDay). The 7-day figure is the distinct people over the last seven
 * days, today included; the chart is that figure for each day.
 *
 * It replaces a count of users.updated_at, which moved when calendar sync refreshed a token or an
 * onboarding email was stamped, and did not move when someone signed in and looked around.
 *
 * Days are app-timezone days, the same clock as the security log the first weeks were seeded from.
 *
 * Two tables (see the migration): user_active_days is the record and is personal data;
 * active_users_daily is each day's totals, written by snapshot() before the record is pruned.
 *
 * "Counted" against "estimate": a row seeded from the security log knows only about sign-ins and
 * event edits, so it runs low. A day's figure is exact once its whole 7-day window lies after the
 * first day the middleware recorded, which is itself a part day and so is not the start.
 */
class ActiveDays
{
    /** The chart's length. */
    public const SERIES_DAYS = 84;

    /** How far back a day's figures look. */
    private const WINDOW = 30;

    private static ?bool $tableExists = null;

    /**
     * Note that this person used the app today. At most one write per session per day, and never an
     * error: this runs on every page of the app.
     */
    public static function record(User $user, Request $request): void
    {
        try {
            if (DemoService::isDemoUser($user)) {
                return;
            }

            $today = now()->toDateString();
            $session = $request->hasSession() ? $request->session() : null;
            // The account is part of the mark: a session that changes hands without being
            // invalidated must not leave the second person unrecorded.
            $mark = $user->id.':'.$today;

            if ($session?->get('active_day') === $mark) {
                return;
            }

            // Before the write, so a write that fails is not attempted again on every request.
            $session?->put('active_day', $mark);

            if (! self::tableExists()) {
                return;
            }

            DB::table('user_active_days')->upsert(
                [['user_id' => $user->id, 'date' => $today, 'counted' => true]],
                ['user_id', 'date'],
                ['counted'],
            );
        } catch (Throwable $e) {
            self::reportOnce($e);
        }
    }

    /**
     * What the dashboard shows.
     *
     * `change` is measured between FINISHED days: the seven days to yesterday against the seven
     * before those. The record holds dates, not times, so today cannot be cut at the hour of the
     * look the way the sign-up windows are, and today-so-far against a whole week read as a fall
     * every morning. The headline figures themselves are live and include today.
     *
     * `change` is also null until both of those windows are exact: an exact week against an
     * estimated one would read as growth that did not happen. `exact_30d` says the same of the
     * 30-day figure, which takes thirty days of counting rather than seven.
     *
     * @return array{available: bool, active_7d: int, active_30d: int, organizers_7d: int,
     *     previous_7d: ?int, change: ?float, exact: bool, exact_30d: bool, exact_from: ?string,
     *     series: array<int, array{date: string, active: int, exact: bool}>}
     */
    public static function stats(?CarbonImmutable $now = null): array
    {
        $empty = ['available' => false, 'active_7d' => 0, 'active_30d' => 0, 'organizers_7d' => 0,
            'previous_7d' => null, 'change' => null, 'exact' => false, 'exact_30d' => false,
            'exact_from' => null, 'series' => []];

        if (! self::tableExists()) {
            return $empty;
        }

        $today = ($now ?? now()->toImmutable())->startOfDay();
        $exactFrom = self::exactFrom();

        self::snapshot($today, $exactFrom);

        $from = $today->subDays(self::SERIES_DAYS - 1);
        $stored = DB::table('active_users_daily')
            ->where('date', '>=', $from->toDateString())
            ->where('date', '<', $today->toDateString())
            ->orderBy('date')
            ->get()
            ->keyBy(fn ($row) => substr((string) $row->date, 0, 10));

        $live = self::compute($today, $today, $exactFrom)[$today->toDateString()];

        $series = [];
        foreach ($stored as $date => $row) {
            $series[] = ['date' => $date, 'active' => (int) $row->active_7d, 'exact' => (bool) $row->counted];
        }
        $series[] = ['date' => $today->toDateString(), 'active' => $live['active_7d'], 'exact' => $live['counted']];

        $latest = $stored->get($today->subDay()->toDateString());
        $previous = $stored->get($today->subDays(8)->toDateString());
        $change = null;

        if ($latest && $previous && $latest->counted && $previous->counted && (int) $previous->active_7d > 0) {
            $change = round((((int) $latest->active_7d - (int) $previous->active_7d) / (int) $previous->active_7d) * 100, 1);
        }

        return [
            'available' => true,
            'active_7d' => $live['active_7d'],
            'active_30d' => $live['active_30d'],
            'organizers_7d' => $live['organizers_7d'],
            'previous_7d' => $previous ? (int) $previous->active_7d : null,
            'change' => $change,
            'exact' => $live['counted'],
            // exactFrom() is the first recorded day plus 7. A 30-day window clears that part day
            // twenty-three days later.
            'exact_30d' => $exactFrom !== null && $today->gte($exactFrom->addDays(self::WINDOW - 7)),
            'exact_from' => $exactFrom?->toDateString(),
            'series' => $series,
        ];
    }

    /**
     * Write the totals of every finished day that has none yet. Called before the record is pruned
     * (app:prune-personal-data) and before the dashboard reads, so an install with no cron still
     * fills in. With nothing missing it costs one query.
     */
    public static function snapshot(?CarbonImmutable $today = null, CarbonImmutable|false|null $exactFrom = false): int
    {
        if (! self::tableExists()) {
            return 0;
        }

        $today = ($today ?? now()->toImmutable())->startOfDay();
        $exactFrom = $exactFrom === false ? self::exactFrom() : $exactFrom;
        $yesterday = $today->subDay();

        $last = DB::table('active_users_daily')->max('date');
        $first = self::firstUsableDay();

        if ($first === null) {
            return 0;
        }

        $from = $last !== null ? CarbonImmutable::parse(substr((string) $last, 0, 10))->addDay() : $first;
        $from = $from->max($first);

        if ($from->gt($yesterday)) {
            return 0;
        }

        $rows = [];
        foreach (self::compute($from, $yesterday, $exactFrom) as $date => $day) {
            $rows[] = ['date' => $date, 'active_7d' => $day['active_7d'], 'active_30d' => $day['active_30d'],
                'organizers_7d' => $day['organizers_7d'], 'counted' => $day['counted']];
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('active_users_daily')->upsert($chunk, ['date'], ['active_7d', 'active_30d', 'organizers_7d', 'counted']);
        }

        return count($rows);
    }

    /**
     * Delete the record past its retention: seeded rows after $estimateDays (the life of the log
     * they came from), the rest after $days. The totals are written first, by the caller.
     */
    public static function prune(int $days, int $estimateDays): int
    {
        if (! self::tableExists()) {
            return 0;
        }

        $deleted = 0;

        foreach ([[$days, null], [$estimateDays, false]] as [$age, $counted]) {
            do {
                $query = DB::table('user_active_days')->where('date', '<', now()->subDays($age)->toDateString());
                if ($counted !== null) {
                    $query->where('counted', $counted);
                }

                $batch = $query->limit(1000)->delete();
                $deleted += $batch;
            } while ($batch === 1000);
        }

        return $deleted;
    }

    /** A selfhost install can run this code before `migrate`. */
    public static function tableExists(): bool
    {
        if (self::$tableExists === null) {
            try {
                self::$tableExists = Schema::hasTable('user_active_days') && Schema::hasTable('active_users_daily');
            } catch (Throwable) {
                return false;
            }
        }

        return self::$tableExists;
    }

    /** Tests share a process. */
    public static function flush(): void
    {
        self::$tableExists = null;
    }

    /**
     * The first day whose figures are exact, or null while nothing has been recorded. The first
     * recorded day is the release day, a part day, so the count starts the day after; a 7-day
     * figure is exact six days after that.
     */
    public static function exactFrom(): ?CarbonImmutable
    {
        $first = DB::table('user_active_days')->where('counted', true)->min('date');

        return $first === null ? null : CarbonImmutable::parse(substr((string) $first, 0, 10))->addDays(7);
    }

    /**
     * The totals of each day from $from to $to, from one read of the record. Verified accounts
     * only, and never the demo account.
     *
     * @return array<string, array{active_7d: int, active_30d: int, organizers_7d: int, counted: bool}>
     */
    private static function compute(CarbonImmutable $from, CarbonImmutable $to, ?CarbonImmutable $exactFrom): array
    {
        $rows = DB::table('user_active_days as d')
            ->join('users as u', 'u.id', '=', 'd.user_id')
            ->where('d.date', '>=', $from->subDays(self::WINDOW - 1)->toDateString())
            ->where('d.date', '<=', $to->toDateString())
            ->whereNotNull('u.email_verified_at')
            ->where('u.email', '!=', DemoService::DEMO_EMAIL)
            ->get(['d.user_id', 'd.date', 'u.signup_intent']);

        /** @var Collection<string, Collection> $byDay */
        $byDay = $rows->groupBy(fn ($row) => substr((string) $row->date, 0, 10));
        $organizers = $rows->filter(fn ($row) => $row->signup_intent === null || $row->signup_intent === 'organizer')
            ->pluck('user_id')->flip()->all();

        $out = [];
        for ($day = $from; $day->lte($to); $day = $day->addDay()) {
            $week = [];
            $month = [];

            for ($back = 0; $back < self::WINDOW; $back++) {
                foreach ($byDay->get($day->subDays($back)->toDateString(), []) as $row) {
                    $month[$row->user_id] = true;
                    if ($back < 7) {
                        $week[$row->user_id] = true;
                    }
                }
            }

            $out[$day->toDateString()] = [
                'active_7d' => count($week),
                'active_30d' => count($month),
                'organizers_7d' => count(array_intersect_key($week, $organizers)),
                'counted' => $exactFrom !== null && $day->gte($exactFrom),
            ];
        }

        return $out;
    }

    /**
     * The first day a 7-day figure can be drawn for: a week after the oldest row, whose own day is
     * cut short by the security log's 90-day edge.
     */
    private static function firstUsableDay(): ?CarbonImmutable
    {
        $oldest = DB::table('user_active_days')->min('date');

        return $oldest === null ? null : CarbonImmutable::parse(substr((string) $oldest, 0, 10))->addDays(7);
    }

    private static function reportOnce(Throwable $e): void
    {
        try {
            if (Cache::add('active_days_error_reported', 1, 3600)) {
                report($e);
            }
        } catch (Throwable) {
            // Nothing left to do: recording a day is best-effort.
        }
    }
}
