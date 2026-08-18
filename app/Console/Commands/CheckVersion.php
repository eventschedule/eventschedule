<?php

namespace App\Console\Commands;

use App\Services\AppUpdateService;
use Codedge\Updater\UpdaterManager;
use Illuminate\Console\Command;

/**
 * Refresh the cached "latest released version" so the admin panel can show an update badge
 * without any page render making an outbound call to GitHub.
 *
 * Scheduled daily in routes/console.php and in AppController::translateData(), which are the
 * two cron rails a selfhost can be running.
 */
class CheckVersion extends Command
{
    protected $signature = 'app:check-version';

    protected $description = 'Check GitHub for the latest Event Schedule release and cache the result';

    public function handle(UpdaterManager $updater, AppUpdateService $appUpdate): int
    {
        // eventschedule.com deploys from git, so there is nothing here to check against.
        if (config('app.is_nexus')) {
            return self::SUCCESS;
        }

        $available = $appUpdate->versionAvailable($updater, true);

        if ($available === null) {
            $this->error('Could not reach GitHub to check for a new version.');

            return self::FAILURE;
        }

        $this->info('Installed: '.$appUpdate->versionInstalled().', latest: '.$available);

        if ($appUpdate->isUpdateAvailable()) {
            $this->info('An update is available. Run app:update or use System > App Update in the admin panel.');
        }

        return self::SUCCESS;
    }
}
