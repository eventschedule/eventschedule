<?php

namespace Tests\Support;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * QueryGuardTest's command: the same SELECT, more often than QueryGuard::REPEAT_LIMIT allows.
 */
final class QueryGuardCommand extends Command
{
    protected $signature = 'query-guard:repeat';

    protected $description = 'QueryGuardTest: ask the same query too often';

    public function handle(): int
    {
        foreach (range(1, QueryGuard::REPEAT_LIMIT + 2) as $n) {
            DB::table('events')->whereIntegerInRaw('id', [$n])->get();
        }

        return self::SUCCESS;
    }
}
