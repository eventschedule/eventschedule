<?php

namespace App\Console\Commands;

use App\Utils\RealtimeTracker;
use Illuminate\Console\Command;

/**
 * Deletes /admin/realtime page views about an hour after their last activity.
 *
 * This is what makes the privacy policy's "deleted about an hour after your last activity" true,
 * so it runs every five minutes on BOTH cron rails, ungated. RealtimeTracker::pruneIfDue() runs a
 * small slice of the same delete from the beacon and the admin page as a backstop while traffic
 * arrives, and AdminAlertService raises realtime_prune_stalled if rows ever outlive two hours.
 */
class PruneRealtimeHits extends Command
{
    protected $signature = 'realtime:prune';

    protected $description = 'Delete realtime page views older than the retention window';

    public function handle(): int
    {
        // A selfhost install can run the new scheduler entry before `migrate`; nothing to prune then.
        if (! RealtimeTracker::tableExists()) {
            return Command::SUCCESS;
        }

        // Bounded (500,000 rows) so a run fits well inside the scheduler entry's
        // withoutOverlapping(10); a bigger backlog finishes on the next run, five minutes later.
        $deleted = RealtimeTracker::prune(maxBatches: 100);

        $this->info("Pruned {$deleted} realtime page views.");

        return Command::SUCCESS;
    }
}
