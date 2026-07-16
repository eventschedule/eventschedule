<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Services\GoogleCalendarService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SyncGoogleCalendars extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'google:sync {--role= : Sync only a specific role ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync events from Google Calendars (incremental; backstop for missed webhooks and selfhost)';

    /**
     * Execute the console command.
     */
    public function handle(GoogleCalendarService $googleCalendarService): int
    {
        // Prevent concurrent execution using a cache lock (15 minute timeout)
        $lock = Cache::lock('google-sync-command', 900);

        if (! $lock->get()) {
            $this->warn('Another Google sync is already running, skipping');

            return Command::SUCCESS;
        }

        try {
            return $this->executeSync($googleCalendarService);
        } finally {
            $lock->release();
        }
    }

    /**
     * Execute the actual sync logic
     */
    protected function executeSync(GoogleCalendarService $googleCalendarService): int
    {
        $roleId = $this->option('role');

        if ($roleId) {
            $roles = Role::where('id', $roleId)
                ->whereIn('sync_direction', ['from', 'both'])
                ->get();
        } else {
            $roles = Role::whereIn('sync_direction', ['from', 'both'])
                ->where('is_deleted', false)
                ->get();
        }

        $this->info("Found {$roles->count()} roles with Google Calendar sync enabled");

        $totalCreated = 0;
        $totalUpdated = 0;
        $totalDeleted = 0;
        $totalErrors = 0;

        foreach ($roles as $role) {
            $this->info("Syncing Google Calendar for role: {$role->subdomain} (ID: {$role->id})");

            try {
                // The webhook/calendar belong to the owner; sync with the owner's token.
                $user = $role->user;

                if (! $user || ! $user->google_token) {
                    $this->warn('  No owner with Google token found, skipping');

                    continue;
                }

                if (! $googleCalendarService->ensureValidToken($user)) {
                    $this->warn('  Failed to refresh Google token, skipping');
                    $totalErrors++;

                    continue;
                }

                // The persisted sync token makes an empty poll one cheap request, which is itself
                // the change check - no separate hasChanges() call is needed.
                $results = $googleCalendarService->syncFromGoogleCalendar($user, $role, $role->getGoogleCalendarId());

                $totalCreated += $results['created'];
                $totalUpdated += $results['updated'];
                $totalDeleted += $results['deleted'] ?? 0;
                $totalErrors += $results['errors'];

                $this->info("  Created: {$results['created']}, Updated: {$results['updated']}, Deleted: ".($results['deleted'] ?? 0).", Errors: {$results['errors']}");
            } catch (\Exception $e) {
                $this->error("  Error syncing: {$e->getMessage()}");
                Log::error('Google sync command failed for role', [
                    'role_id' => $role->id,
                    'error' => $e->getMessage(),
                ]);
                $totalErrors++;
            }
        }

        $this->info('');
        $this->info("Sync complete. Total - Created: {$totalCreated}, Updated: {$totalUpdated}, Deleted: {$totalDeleted}, Errors: {$totalErrors}");

        return $totalErrors > 0 ? Command::FAILURE : Command::SUCCESS;
    }
}
