<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * cache.expiration and cache_locks.expiration: what app:prune-cache reads every hour, and what
     * DatabaseLock's lottery prune filters on. Without them each is a scan of a table that is
     * written on almost every request.
     *
     * failed_jobs.failed_at: app:retry-failed-jobs sorts the whole table on it every five minutes,
     * and /admin/queue sorts it again on every load.
     */
    private const INDEXES = [
        'cache' => 'expiration',
        'cache_locks' => 'expiration',
        'failed_jobs' => 'failed_at',
    ];

    public function up(): void
    {
        // `cache` is written on almost every request, and the ALTER needs a brief metadata lock at
        // its start and end. Behind an open cache transaction it would otherwise wait for MySQL's
        // default lock_wait_timeout - a year - while every cache query after it queued behind it,
        // freezing the site rather than failing the deploy. Ten seconds, then fail; a re-run of
        // `migrate --force` picks it up, because each index is guarded below.
        $previous = DB::selectOne('SELECT @@SESSION.lock_wait_timeout AS value')->value;
        DB::statement('SET SESSION lock_wait_timeout = 10');

        try {
            foreach (self::INDEXES as $table => $column) {
                // The column form matches an index of any name, so one a selfhost operator added by
                // hand is not duplicated.
                if (! Schema::hasTable($table) || Schema::hasIndex($table, [$column])) {
                    continue;
                }

                // Explicit, so MySQL refuses rather than quietly falling back to a build that
                // blocks writes for its whole duration.
                DB::statement("ALTER TABLE `{$table}` ADD INDEX `{$table}_{$column}_index` (`{$column}`), ALGORITHM=INPLACE, LOCK=NONE");
            }
        } finally {
            DB::statement('SET SESSION lock_wait_timeout = '.(int) $previous);
        }
    }

    public function down(): void
    {
        foreach (self::INDEXES as $table => $column) {
            if (Schema::hasTable($table) && Schema::hasIndex($table, "{$table}_{$column}_index")) {
                DB::statement("ALTER TABLE `{$table}` DROP INDEX `{$table}_{$column}_index`");
            }
        }
    }
};
