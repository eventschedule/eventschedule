<?php

namespace App\Services;

use App\Utils\RealtimeTracker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * Counts realtime_hits into realtime_hourly before they are pruned, for the Trends card of
 * /admin/realtime (RealtimeTrends). See the migration that creates both tables.
 *
 * Called from RealtimeTracker::prune() itself, so every path that deletes rows (the scheduler's
 * realtime:prune on both cron rails, and pruneIfDue() on the request path when the cron has
 * stopped) counts them first. What it counts is what the live page counts as a page view: an
 * engaged row on the marketing site, a schedule page, the app or the sign-up pages, never an
 * admin's own and never a blocked account's (BlockedAccounts). An embedded calendar is not a page
 * of this site and is left out, as the live page leaves it out of its page views.
 *
 * Each page view is counted once, in the hour it started, at the run after it was engaged: the
 * cursor in realtime_rollup moves over engaged_at, and the read, the counts and the cursor are one
 * transaction holding that row's lock, so two runs at once (one on each rail) wait for each other.
 */
class RealtimeRollup
{
    /** The parts of the site a page view is counted under. */
    public const SURFACES = ['wp', 'gp', 'ap', 'auth'];

    /** Long enough for the 30-day range and the 30 days it is compared with. */
    public const KEEP_DAYS = 62;

    /**
     * A row engaged in the last few seconds may still be in a beacon's transaction that has not
     * committed: left for the next run rather than skipped for good.
     */
    private const SETTLE_SECONDS = 30;

    private static ?bool $tablesExist = null;

    /** @return int page views counted */
    public static function run(): int
    {
        if (! self::tablesExist()) {
            return 0;
        }

        $until = RealtimeTracker::ts(RealtimeTracker::now()->subSeconds(self::SETTLE_SECONDS));

        return DB::transaction(function () use ($until) {
            $through = self::lockCursor();

            if ($through >= $until) {
                return 0;
            }

            $hour = "DATE_FORMAT(realtime_hits.started_at, '%Y-%m-%d %H:00:00')";

            $counts = (new BlockedAccounts)->withoutTheirHits(DB::table('realtime_hits'))
                // last_seen_at is never earlier than engaged_at, and it is the column with an index.
                ->where('realtime_hits.last_seen_at', '>=', $through)
                ->where('realtime_hits.engaged_at', '>=', $through)
                ->where('realtime_hits.engaged_at', '<', $until)
                ->where('realtime_hits.is_admin', false)
                ->whereIn('realtime_hits.surface', self::SURFACES)
                ->groupBy(DB::raw($hour), 'realtime_hits.surface')
                ->get([
                    DB::raw($hour.' as hour'),
                    'realtime_hits.surface',
                    DB::raw('COUNT(*) as page_views'),
                    DB::raw('COALESCE(SUM(realtime_hits.is_entrance), 0) as visits'),
                ]);

            foreach ($counts as $count) {
                $key = ['hour' => $count->hour, 'surface' => $count->surface];

                DB::table('realtime_hourly')->insertOrIgnore($key + ['page_views' => 0, 'visits' => 0]);
                DB::table('realtime_hourly')->where($key)->increment('page_views', (int) $count->page_views, [
                    'visits' => DB::raw('visits + '.(int) $count->visits),
                ]);
            }

            DB::table('realtime_rollup')->where('id', 1)->update(['through_at' => $until]);

            DB::table('realtime_hourly')
                ->where('hour', '<', RealtimeTracker::ts(RealtimeTracker::now()->subDays(self::KEEP_DAYS)))
                ->delete();

            return (int) $counts->sum('page_views');
        });
    }

    /**
     * The cursor, locked for this transaction. The first run starts at the oldest engaged row
     * realtime_hits still holds, so the hour before the tables existed is counted too.
     */
    private static function lockCursor(): string
    {
        $through = DB::table('realtime_rollup')->where('id', 1)->lockForUpdate()->value('through_at');

        if ($through === null) {
            $oldest = DB::table('realtime_hits')->min('engaged_at');
            DB::table('realtime_rollup')->insertOrIgnore([
                'id' => 1,
                'through_at' => $oldest ?? RealtimeTracker::ts(RealtimeTracker::now()->subMinutes(RealtimeTracker::RETENTION_MINUTES)),
            ]);
            $through = DB::table('realtime_rollup')->where('id', 1)->lockForUpdate()->value('through_at');
        }

        return (string) $through;
    }

    /** Where the counts begin, for "counting since": null until anything has been counted. */
    public static function since(): ?string
    {
        if (! self::tablesExist()) {
            return null;
        }

        return DB::table('realtime_hourly')->min('hour');
    }

    /**
     * Whether both tables exist yet: a selfhost install can run this code before `migrate`.
     */
    public static function tablesExist(): bool
    {
        if (self::$tablesExist !== null) {
            return self::$tablesExist;
        }

        try {
            return self::$tablesExist = Schema::hasTable('realtime_hourly') && Schema::hasTable('realtime_rollup');
        } catch (Throwable) {
            return false;
        }
    }

    /** For tests that create or drop the tables. */
    public static function forget(): void
    {
        self::$tablesExist = null;
    }
}
