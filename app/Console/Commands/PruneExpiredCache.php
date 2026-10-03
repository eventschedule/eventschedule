<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\DetectsConcurrencyErrors;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

/**
 * Deletes expired rows from the database cache store.
 *
 * Laravel's DatabaseStore removes an expired row only when the same key is read again, and many of
 * this app's keys never are: PageView's per-visitor daily counters and visit dedupe, the promo and
 * social-click counters, and every throttle key with its `:timer` twin. On CACHE_STORE=database
 * those pile up in MySQL for good, a fresh set per visitor per day, and because `cache` is keyed on
 * a random string the inserts land all over the table, so all of it stays hot in the buffer pool.
 * That is what turned up when the hosted 1 GB MySQL started alerting on memory in October 2026.
 *
 * Hourly on BOTH cron rails, ungated: a selfhost install on the database store leaks the same way,
 * and every other store is skipped here.
 */
class PruneExpiredCache extends Command
{
    use DetectsConcurrencyErrors;

    private const BATCH = 1000;

    // A default rather than an argument, because CronRailSyncTest requires both rails to pass the
    // same arguments, and on the HTTP rail this runs inside translateData()'s hourly request, where
    // every second it takes is a second closer to the timeout for the commands after it.
    protected $signature = 'app:prune-cache {--max-seconds=20 : Start no new batch after this many seconds}';

    protected $description = 'Delete expired rows from the database cache and cache lock tables';

    public function handle(): int
    {
        $name = config('cache.default');
        $config = config("cache.stores.{$name}", []);

        if (($config['driver'] ?? null) !== 'database') {
            $this->info("Skipping: the [{$name}] cache store does not use the database.");

            return self::SUCCESS;
        }

        // Resolved exactly as CacheManager::createDatabaseDriver() does, so these are the tables the
        // store really writes to.
        $connection = $config['connection'] ?? null;
        $lockConnection = $config['lock_connection'] ?? $connection;

        // One clock for the whole run, and the one DatabaseStore compares against (Carbon's, so a
        // test's travelTo moves it too).
        $now = now()->getTimestamp();
        $deadline = microtime(true) + max(1, (int) $this->option('max-seconds'));

        $rows = $this->prune($connection, $config['table'] ?? 'cache', $now, $deadline);
        $locks = $this->prune($lockConnection, $config['lock_table'] ?? 'cache_locks', $now, $deadline);

        $this->info("Pruned {$rows} expired cache rows and {$locks} expired locks.");

        return self::SUCCESS;
    }

    /**
     * Read a batch of expired keys, then delete those keys - never one ranged DELETE.
     *
     * A `DELETE ... WHERE expiration <= ? LIMIT n` locks the expiration index before the primary key,
     * while a web request that reads an expired key deletes it by primary key first
     * (DatabaseStore::forgetManyIfExpired), so the two take their locks in opposite orders and can
     * deadlock - and InnoDB picks the smaller transaction, which is the visitor's. Deleting by key
     * takes the same order the store does.
     *
     * The delete repeats the expiration test on purpose: a key rewritten between the read and the
     * delete has a fresh expiration and must survive, or a Cache::add() dedupe or a td_* tier marker
     * written in that gap would be wiped.
     */
    private function prune(?string $connection, string $table, int $now, float $deadline): int
    {
        $deleted = 0;

        do {
            $keys = DB::connection($connection)->table($table)
                ->where('expiration', '<=', $now)
                ->orderBy('expiration')
                ->limit(self::BATCH)
                ->pluck('key');

            if ($keys->isEmpty()) {
                break;
            }

            try {
                $deleted += DB::connection($connection)->table($table)
                    ->whereIn('key', $keys->all())
                    ->where('expiration', '<=', $now)
                    ->delete();
            } catch (QueryException $e) {
                // A deadlock or lock wait against live traffic loses this batch, not the run: the
                // same keys come back on the next read, until the deadline.
                if (! $this->causedByConcurrencyError($e)) {
                    throw $e;
                }
            }
        } while ($keys->count() === self::BATCH && microtime(true) < $deadline);

        return $deleted;
    }
}
