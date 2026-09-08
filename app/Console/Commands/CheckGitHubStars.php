<?php

namespace App\Console\Commands;

use App\Utils\GitHubUtils;
use Illuminate\Console\Command;

/**
 * Refresh the stored GitHub star count so no page render ever makes an outbound call for it.
 *
 * Scheduled daily in routes/console.php and in AppController::translateData(), which are the two
 * cron rails an install can be running. Sibling of app:check-version, which does the same for the
 * self-updater's "latest release" lookup.
 */
class CheckGitHubStars extends Command
{
    protected $signature = 'app:check-github-stars';

    protected $description = 'Refresh the cached GitHub star count shown on the star badges';

    public function handle(): int
    {
        // The INVERSE gate to app:check-version, on purpose. That command no-ops ON nexus because
        // eventschedule.com deploys from git and has nothing to self-update; this one runs ONLY on
        // nexus, because eventschedule.com is the only install that shows the badge to the public -
        // the whole marketing route block is inside `if (config('app.is_nexus'))` - and a selfhost
        // install should not be reaching out to github.com on a timer for a vanity count.
        if (! config('app.is_nexus')) {
            return self::SUCCESS;
        }

        $stars = GitHubUtils::refresh();

        if ($stars === null) {
            $this->error('Could not reach GitHub to refresh the star count.');

            // Logged as well as printed, because the printed line goes nowhere on the scheduler
            // rail: CallbackEvent::execute() only invokes the closure and never captures output, so
            // appendOutputTo() has nothing to write, and Artisan::call()'s buffer is discarded by
            // the closure. Nothing else records this either - ScheduledTaskRecorder reads a
            // non-throwing FAILURE as a success (deliberately, so a flaky third party does not
            // paint /admin/queue red) and the HTTP rail only catches throwables. Without this line
            // a refresh that broke permanently would show up as a frozen number and nothing else.
            \Log::warning('app:check-github-stars could not reach GitHub; the stored count is unchanged.');

            // The previously stored count is untouched, so the badge keeps showing it.
            return self::FAILURE;
        }

        $this->info('GitHub stars: '.$stars);

        return self::SUCCESS;
    }
}
