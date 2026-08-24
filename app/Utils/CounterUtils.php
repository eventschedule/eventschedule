<?php

namespace App\Utils;

use Illuminate\Database\DetectsConcurrencyErrors;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Best-effort writes for the daily counter tables (analytics_*_daily, usage_daily,
 * marketing_daily_stats, federation_clicks_daily).
 *
 * Those tables are all written with a single INSERT ... ON DUPLICATE KEY UPDATE from the
 * guest page render path. That statement is race-safe against duplicate keys (1062) but
 * NOT against deadlocks (1213): a hot (role_id, date) row, plus the role_id foreign key
 * that makes every insert also take a shared lock on the same roles row, is enough for
 * InnoDB to pick a victim under concurrency. One guest request fires six of these.
 *
 * Before this helper a deadlock on a fire-and-forget view counter propagated all the way
 * out of AnalyticsDaily::incrementView() and 500'd a public schedule page. A counter must
 * never break the page it is counting, so outside a transaction we retry a few times and
 * then give up quietly, reporting to Sentry.
 *
 * That same foreign key has a second failure mode, which is why 1452 is singled out below:
 * nothing in this app soft-deletes, so the parent roles/events row can stop existing
 * outright between the page's SELECT and the counter's INSERT.
 */
final class CounterUtils
{
    use DetectsConcurrencyErrors;

    /**
     * Run a counter write, retrying deadlocks and swallowing what is left.
     *
     * Only for statements whose loss is acceptable - a failure is reported, not thrown.
     */
    public static function statement(string $sql, array $bindings = [], int $attempts = 3): void
    {
        (new self)->run($sql, $bindings, $attempts);
    }

    private function run(string $sql, array $bindings, int $attempts): void
    {
        // Inside a transaction there is nothing to retry and nothing safe to swallow: MySQL
        // rolls back the WHOLE transaction when it picks a deadlock victim, so retrying just
        // this statement would run against a dead transaction, and swallowing would let the
        // caller carry on believing its earlier writes survived.
        // AnalyticsEventsDaily::incrementSale() runs inside the checkout transaction, where
        // that would mark a sale paid on top of a rolled-back row. Let it bubble instead.
        if (DB::transactionLevel() > 0) {
            DB::statement($sql, $bindings);

            return;
        }

        for ($attempt = 1; ; $attempt++) {
            try {
                DB::statement($sql, $bindings);

                return;
            } catch (Throwable $e) {
                if ($attempt < $attempts && $this->causedByConcurrencyError($e)) {
                    // Jittered backoff so two colliding requests don't retry in lockstep.
                    usleep(random_int(20, 60) * 1000 * $attempt);

                    continue;
                }

                // 1452: the parent roles/events row was deleted between the page's SELECT
                // and this INSERT. Not worth retrying and not worth reporting - the row is
                // permanently gone, and ON DELETE CASCADE has already taken whatever
                // counters landed before it went. The hourly demo reset
                // (DemoService::resetDemoData) hard-deletes and recreates every demo-%
                // schedule and its events, so a guest mid-render on a demo page loses this
                // race roughly once an hour; a user deleting an event under a live visitor
                // loses it the same way on any schedule. Sentry EVENTSCHEDULE-PHP-46.
                // Every other failure still reports - see the 42S02 case in
                // AnalyticsCounterResilienceTest, which pins that boundary.
                if ($e instanceof QueryException && ($e->errorInfo[1] ?? null) === 1452) {
                    return;
                }

                report($e);

                return;
            }
        }
    }
}
