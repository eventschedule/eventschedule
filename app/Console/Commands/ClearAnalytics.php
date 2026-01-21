<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AnalyticsDaily;
use App\Models\AnalyticsEventsDaily;
use App\Models\AnalyticsAppearancesDaily;
use App\Models\AnalyticsReferrersDaily;

class ClearAnalytics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:clear-analytics {--force : Skip confirmation prompt}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear all analytics data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (! $this->option('force')) {
            if (! $this->confirm('This will permanently delete all analytics data. Are you sure?')) {
                $this->info('Operation cancelled.');
                return 0;
            }
        }

        $this->info('Clearing analytics data...');

        $dailyCount = AnalyticsDaily::count();
        AnalyticsDaily::truncate();
        $this->line("Deleted {$dailyCount} schedule analytics records.");

        $eventsCount = AnalyticsEventsDaily::count();
        AnalyticsEventsDaily::truncate();
        $this->line("Deleted {$eventsCount} event analytics records.");

        $appearancesCount = AnalyticsAppearancesDaily::count();
        AnalyticsAppearancesDaily::truncate();
        $this->line("Deleted {$appearancesCount} appearance analytics records.");

        $referrersCount = AnalyticsReferrersDaily::count();
        AnalyticsReferrersDaily::truncate();
        $this->line("Deleted {$referrersCount} referrer analytics records.");

        $total = $dailyCount + $eventsCount + $appearancesCount + $referrersCount;
        $this->info("All analytics data cleared. Total records deleted: {$total}");

        return 0;
    }
}
