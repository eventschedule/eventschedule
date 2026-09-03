<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * What GET /up actually proves.
 *
 * Laravel's health route dispatches DiagnosingHealth and returns 200 unless a listener throws.
 * Nothing listened, so /up proved only that PHP booted and routing worked - it answered 200
 * throughout a total database outage, which is the one thing a health check exists to catch.
 *
 * Two dependencies, both hard: without either, every page on the site is already an error.
 *
 *  - The database. A `select 1` rather than getPdo(), because a lazily-resolved PDO can be
 *    handed back without a round trip actually having happened.
 *  - The cache STORE, exercised by writing a value and reading it back rather than by asking
 *    config what the driver is. That is what catches a store that resolves but does not work:
 *    an unwritable storage/framework/cache on the file driver, an unreachable Redis, or - the
 *    one this release can produce - CACHE_STORE present-but-EMPTY on the app spec, where
 *    CacheManager::resolve('') throws "Cache store [] is not defined" on the first cache read
 *    anywhere in the app. config('cache.default') is env('CACHE_STORE', 'file'), so the second
 *    argument never fires for a blank value and there is no fallback to land on.
 *
 * Deliberately NOT checked here:
 *
 *  - The scheduler. /up is a liveness signal, and DigitalOcean App Platform will recycle a
 *    container whose HTTP health check fails. A stalled scheduler must not take the web tier
 *    down with it - it is a genuine alert, but it belongs on /admin/queue, where
 *    AdminAlertService already surfaces it.
 *  - Whether the cache store is `database` rather than `file`. A round trip cannot tell them
 *    apart: the file driver works perfectly well inside one container, which is exactly why
 *    the multi-container hazard is silent. AdminAlertService's `scheduler_stalled` row is what
 *    catches that, because the two containers stop sharing a heartbeat.
 *
 * Nothing here leaks. ApplicationBuilder catches, report()s to Sentry and passes only a
 * boolean-ish $exception into health-up.blade.php, which renders "Application experiencing
 * problems" and no detail - so these messages are for the operator's error tracker, not the
 * public. Keep it that way: never put a credential, host or query into one.
 */
class VerifyApplicationHealth
{
    /**
     * Long enough to survive the read that follows it, short enough that a stuck row expires
     * before anyone can be confused by it. Nothing else reads this key.
     */
    private const PROBE_TTL_SECONDS = 10;

    private const PROBE_KEY = 'health:probe';

    public function handle(): void
    {
        $this->checkDatabase();
        $this->checkCacheStore();
    }

    private function checkDatabase(): void
    {
        DB::select('select 1');
    }

    private function checkCacheStore(): void
    {
        // Unique per call, so a value left behind by an earlier probe cannot make a broken
        // store look healthy.
        $expected = (string) bin2hex(random_bytes(8));

        Cache::put(self::PROBE_KEY, $expected, self::PROBE_TTL_SECONDS);

        if (Cache::get(self::PROBE_KEY) !== $expected) {
            throw new RuntimeException(
                'Cache store "'.config('cache.default').'" did not return the value just written to it.'
            );
        }
    }
}
