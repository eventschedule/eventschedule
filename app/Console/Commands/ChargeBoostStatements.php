<?php

namespace App\Console\Commands;

use App\Services\BoostStatementService;
use Illuminate\Console\Command;

/**
 * Daily: bill each boost partner the platform's fee on last month's actual Meta spend once that
 * month has closed in its ad account's timezone, and charge or retry anything unpaid. Running
 * daily (rather than on the 1st) is what lets the month close in every timezone and lets a
 * declined card be retried; BoostStatementService keeps each step idempotent.
 */
class ChargeBoostStatements extends Command
{
    protected $signature = 'boost:charge-statements';

    protected $description = 'Bill boost partners their monthly fee on actual ad spend';

    public function handle(BoostStatementService $service): int
    {
        $service->run();

        return self::SUCCESS;
    }
}
