<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Services\MicrosoftCalendarService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SyncMicrosoftCalendars extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'microsoft:sync {--role= : Sync only a specific role ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync events from Outlook / Microsoft 365 calendars';

    /**
     * Execute the console command.
     */
    public function handle(MicrosoftCalendarService $microsoftCalendarService): int
    {
        // Prevent concurrent execution using a cache lock (15 minute timeout)
        $lock = Cache::lock('microsoft-sync-command', 900);

        if (! $lock->get()) {
            $this->warn('Another Outlook sync is already running, skipping');

            return Command::SUCCESS;
        }

        try {
            return $this->executeSync($microsoftCalendarService);
        } finally {
            $lock->release();
        }
    }

    /**
     * Execute the actual sync logic
     */
    protected function executeSync(MicrosoftCalendarService $microsoftCalendarService): int
    {
        $roleId = $this->option('role');

        if ($roleId) {
            $roles = Role::where('id', $roleId)
                ->whereIn('microsoft_sync_direction', ['from', 'both'])
                ->get();
        } else {
            $roles = Role::whereIn('microsoft_sync_direction', ['from', 'both'])
                ->where('is_deleted', false)
                ->get();
        }

        $this->info("Found {$roles->count()} roles with Outlook sync enabled");

        $totalCreated = 0;
        $totalUpdated = 0;
        $totalErrors = 0;

        foreach ($roles as $role) {
            $this->info("Syncing Outlook for role: {$role->subdomain} (ID: {$role->id})");

            try {
                // The subscription/calendar belong to the owner; sync with the owner's token.
                $user = $role->user;

                if (! $user || ! $user->microsoft_token) {
                    $this->warn('  No owner with Outlook token found, skipping');

                    continue;
                }

                if (! $microsoftCalendarService->ensureValidToken($user)) {
                    $this->warn('  Failed to refresh Outlook token, skipping');
                    $totalErrors++;

                    continue;
                }

                // The persisted deltaLink makes an empty poll one cheap request, which is
                // itself the change check - no separate hasChanges() call is needed.
                $results = $microsoftCalendarService->syncFromMicrosoftCalendar($user, $role, $role->getMicrosoftCalendarId());

                $totalCreated += $results['created'];
                $totalUpdated += $results['updated'];
                $totalErrors += $results['errors'];

                $this->info("  Created: {$results['created']}, Updated: {$results['updated']}, Errors: {$results['errors']}");
            } catch (\Exception $e) {
                $this->error("  Error syncing: {$e->getMessage()}");
                Log::error('Outlook sync command failed for role', [
                    'role_id' => $role->id,
                    'error' => $e->getMessage(),
                ]);
                $totalErrors++;
            }
        }

        $this->info('');
        $this->info("Sync complete. Total - Created: {$totalCreated}, Updated: {$totalUpdated}, Errors: {$totalErrors}");

        return $totalErrors > 0 ? Command::FAILURE : Command::SUCCESS;
    }
}
