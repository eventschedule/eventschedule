<?php

namespace App\Console\Commands;

use App\Services\RecurringRevenue;
use Illuminate\Console\Command;

/**
 * Today's MRR into mrr_snapshots (RecurringRevenue::recordSnapshot()), for the dashboard's chart.
 *
 * Hourly on BOTH cron rails, hosted only: billing exists only there. Each run overwrites the day's
 * row, so a day's figure is the last one counted on it, and a missed run costs nothing.
 */
class RecordMrrSnapshot extends Command
{
    protected $signature = 'app:record-mrr';

    protected $description = "Record today's MRR for the admin dashboard's chart";

    public function handle(): int
    {
        if (! config('app.hosted')) {
            $this->info('Skipping: billing is hosted only.');

            return self::SUCCESS;
        }

        RecurringRevenue::recordSnapshot();
        $this->info('MRR recorded.');

        return self::SUCCESS;
    }
}
