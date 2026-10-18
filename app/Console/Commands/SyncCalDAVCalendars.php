<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Services\CalDAVService;
use App\Services\CalendarArrival;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SyncCalDAVCalendars extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'caldav:sync {--role= : Sync only a specific role ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync events from CalDAV calendars';

    /**
     * Execute the console command.
     */
    public function handle(CalDAVService $calDAVService): int
    {
        // Prevent concurrent execution using a cache lock (15 minute timeout)
        $lock = Cache::lock('caldav-sync-command', 900);

        if (! $lock->get()) {
            $this->warn('Another CalDAV sync is already running, skipping');

            return Command::SUCCESS;
        }

        try {
            return $this->executeSync($calDAVService);
        } finally {
            $lock->release();
        }
    }

    /**
     * Execute the actual sync logic
     */
    protected function executeSync(CalDAVService $calDAVService): int
    {
        $roleId = $this->option('role');

        if ($roleId) {
            $roles = Role::where('id', $roleId)
                ->whereNotNull('caldav_settings')
                ->whereIn('caldav_sync_direction', ['from', 'both'])
                ->get();
        } else {
            // Get all roles with CalDAV sync from calendar enabled
            $roles = Role::whereNotNull('caldav_settings')
                ->whereIn('caldav_sync_direction', ['from', 'both'])
                ->where('is_deleted', false)
                ->get();
        }

        $this->info("Found {$roles->count()} roles with CalDAV sync enabled");

        $totalCreated = 0;
        $totalUpdated = 0;
        $totalErrors = 0;

        $this->rereadsStarted = 0;
        $this->runStarted = microtime(true);

        foreach ($roles as $role) {
            $this->info("Syncing CalDAV for role: {$role->subdomain} (ID: {$role->id})");

            try {
                // The one full read each sync owes its calendar, a few a run (CalendarArrival),
                // reads even a calendar that has not changed.
                $calDAVService->rereadAllowed = $this->mayReread($role, CalendarArrival::CALDAV);

                // Check if there are changes before syncing
                if (! $calDAVService->rereadAllowed && ! $calDAVService->hasChanges($role)) {
                    $this->info('  No changes detected, skipping');

                    continue;
                }

                $results = $calDAVService->syncFromCalDAV($role);

                $totalCreated += $results['created'];
                $totalUpdated += $results['updated'];
                $totalErrors += $results['errors'];

                $this->info("  Created: {$results['created']}, Updated: {$results['updated']}, Errors: {$results['errors']}");
            } catch (\Exception $e) {
                $this->error("  Error syncing: {$e->getMessage()}");
                Log::error('CalDAV sync command failed for role', [
                    'role_id' => $role->id,
                    'error' => $e->getMessage(),
                ]);
                $totalErrors++;
            }
        }

        $calDAVService->rereadAllowed = false;

        $this->info('');
        $this->info("Sync complete. Total - Created: {$totalCreated}, Updated: {$totalUpdated}, Errors: {$totalErrors}");

        return $totalErrors > 0 ? Command::FAILURE : Command::SUCCESS;
    }

    private int $rereadsStarted = 0;

    private float $runStarted = 0;

    /**
     * Whether this schedule gets its one full read in this run: while it still owes one, at most
     * CalendarArrival::REREADS_PER_RUN a run, and none started once the run has gone on for
     * REREAD_BUDGET_SECONDS, so the release that brings this in does not run either cron rail over.
     */
    private function mayReread(Role $role, string $provider): bool
    {
        if ($this->rereadsStarted >= CalendarArrival::REREADS_PER_RUN
            || microtime(true) - $this->runStarted >= CalendarArrival::rereadBudgetSeconds()
            || ! CalendarArrival::needsReread($role, $provider)
            || ! CalendarArrival::claimReread()) {
            return false;
        }

        $this->rereadsStarted++;

        return true;
    }
}
