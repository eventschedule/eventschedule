<?php

namespace App\Console\Commands;

use App\Services\GrowthExportService;
use Illuminate\Console\Command;

/**
 * CLI twin of /admin/growth/export. Same service, same payload - useful when the export
 * is wanted without a browser session, or when it runs long enough that a request would
 * time out. Deliberately not scheduled, so it belongs in neither
 * AppController::translateData() nor routes/console.php.
 */
class ExportGrowth extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:export-growth
                            {--days=30 : Size of the reporting window in days}
                            {--path= : Write to this file instead of stdout}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export aggregated growth and monetization data as JSON';

    public function handle(GrowthExportService $growth): int
    {
        $days = max(1, (int) $this->option('days'));
        $end = now();
        $start = $end->copy()->subDays($days)->startOfDay();
        $prevEnd = $start->copy()->subDay();
        $prevStart = $prevEnd->copy()->subDays($days)->startOfDay();

        $data = $growth->build($start, $end, $prevStart, $prevEnd);
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);

        $path = $this->option('path');
        if (! $path) {
            $this->line($json);

            return self::SUCCESS;
        }

        if (file_put_contents($path, $json) === false) {
            $this->error("Could not write to {$path}");

            return self::FAILURE;
        }

        $this->info(sprintf(
            'Wrote %s (%s KB): %d signup rows, %d schedule rows.',
            $path,
            number_format(strlen($json) / 1024, 1),
            count($data['signups']['rows']),
            count($data['schedules']['rows'])
        ));

        return self::SUCCESS;
    }
}
