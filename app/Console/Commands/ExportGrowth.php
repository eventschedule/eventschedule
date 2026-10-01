<?php

namespace App\Console\Commands;

use App\Services\GrowthExportService;
use App\Utils\AdminDateRange;
use Illuminate\Console\Command;

/**
 * Server-side twin of GET /api/internal/growth: same service, same window, same payload. For when
 * the payload is wanted from a shell on the server itself, or a build runs longer than a request
 * may. On a dev machine, `app:pull-growth` is the way in. Deliberately not scheduled, so it belongs
 * in neither AppController::translateData() nor routes/console.php.
 */
class ExportGrowth extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:export-growth
                            {--range=last_30_days : last_7_days, last_30_days, last_90_days or all_time - the funnel window, as on /admin/growth}
                            {--path= : Write to this file instead of stdout}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export aggregated growth and monetization data as JSON';

    public function handle(GrowthExportService $growth): int
    {
        // Refused rather than passed through: AdminDateRange quietly reads anything it does not
        // recognise as all time, so a typo would build the heaviest window under the asked-for name.
        $range = (string) $this->option('range');
        if (! in_array($range, AdminDateRange::RANGES, true)) {
            $this->error('--range must be one of: '.implode(', ', AdminDateRange::RANGES));

            return self::FAILURE;
        }

        // AdminDateRange, so a range here is the exact window the endpoint and the page use. This
        // command used to do its own arithmetic, a day short of theirs.
        $dates = AdminDateRange::for($range);

        $data = $growth->build($dates['start'], $dates['end'], $dates['previous_start'], $dates['previous_end']);
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION | JSON_THROW_ON_ERROR);

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
