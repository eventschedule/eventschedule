<?php

namespace App\Console\Commands;

use App\Services\AppUpdateService;
use Codedge\Updater\UpdaterManager;
use Illuminate\Console\Command;

class UpdateApp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update';

    /**
     * The command description.
     *
     * @var string
     */
    protected $description = 'Download and install the latest Event Schedule release, then run migrations';

    /**
     * Execute the console command.
     *
     * Gated on is_nexus, not hosted, to match AppController::update(). A self-hosted SaaS runs
     * with IS_HOSTED=true and still updates itself; refusing there left the one install type
     * whose web UI is admin-only with no command line fallback either.
     */
    public function handle(UpdaterManager $updater, AppUpdateService $appUpdate): int
    {
        if (config('app.is_nexus')) {
            $this->error('Not authorized');

            return self::FAILURE;
        }

        $this->info('Updating app, this can take a few minutes...');

        $result = $appUpdate->performUpdate($updater);

        if ($result['status'] === 'up_to_date') {
            $this->info('No updates available');

            return self::SUCCESS;
        }

        if ($result['status'] === 'error') {
            $this->error('Update failed: '.($result['detail'] ?? $result['message']));

            return self::FAILURE;
        }

        $this->info($result['message']);

        return self::SUCCESS;
    }
}
