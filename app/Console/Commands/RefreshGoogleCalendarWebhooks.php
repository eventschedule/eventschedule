<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Services\GoogleCalendarService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RefreshGoogleCalendarWebhooks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'google:refresh-webhooks 
                            {--force : Force refresh all webhooks, even if not expired}
                            {--role= : Refresh webhooks for a specific role ID or subdomain}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh Google Calendar webhooks to prevent expiration';

    protected $googleCalendarService;

    public function __construct(GoogleCalendarService $googleCalendarService)
    {
        parent::__construct();
        $this->googleCalendarService = $googleCalendarService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting Google Calendar webhook refresh...');

        $force = $this->option('force');
        $roleFilter = $this->option('role');

        // Get roles that need webhook refresh
        $query = Role::whereNotNull('google_webhook_id')
            ->whereNotNull('google_webhook_resource_id')
            ->whereNotNull('google_webhook_expires_at');

        // If not forcing, only get webhooks that expire within the next 3 days
        if (! $force) {
            $query->where('google_webhook_expires_at', '<=', now()->addDays(3));
        }

        // Filter by specific role if provided
        if ($roleFilter) {
            if (is_numeric($roleFilter)) {
                $query->where('id', $roleFilter);
            } else {
                $query->where('subdomain', $roleFilter);
            }
        }

        $roles = $query->get();

        if ($roles->isEmpty()) {
            $this->info('No webhooks need refreshing.');

            return 0;
        }

        $this->info("Found {$roles->count()} webhook(s) to refresh.");

        $refreshed = 0;
        $errors = 0;

        foreach ($roles as $role) {
            $this->info("Processing role: {$role->subdomain} (ID: {$role->id})");

            try {
                // Get the user for this role
                $user = $role->users()->first();

                if (! $user || ! $user->google_token) {
                    $this->warn("  - No user with Google token found for role {$role->subdomain}");
                    $errors++;

                    continue;
                }

                // Ensure user has valid token
                if (! $this->googleCalendarService->ensureValidToken($user)) {
                    $this->warn("  - Failed to refresh Google token for role {$role->subdomain}");
                    $errors++;

                    continue;
                }

                // Delete the old webhook
                $this->info("  - Deleting old webhook: {$role->google_webhook_id}");
                $this->googleCalendarService->deleteWebhook(
                    $role->google_webhook_id,
                    $role->google_webhook_resource_id
                );

                // Create a new webhook
                $calendarId = $role->getGoogleCalendarId();
                $webhookUrl = route('google.calendar.webhook.handle');

                $this->info("  - Creating new webhook for calendar: {$calendarId}");
                $webhook = $this->googleCalendarService->createWebhook($calendarId, $webhookUrl);

                // Update the role with new webhook data
                $role->update([
                    'google_webhook_id' => $webhook['id'],
                    'google_webhook_resource_id' => $webhook['resourceId'],
                    'google_webhook_expires_at' => \Carbon\Carbon::createFromTimestamp($webhook['expiration'] / 1000),
                ]);

                $this->info("  - ✅ Webhook refreshed successfully. New expiration: {$role->fresh()->google_webhook_expires_at}");
                $refreshed++;

            } catch (\Exception $e) {
                $this->error("  - ❌ Failed to refresh webhook for role {$role->subdomain}: {$e->getMessage()}");
                Log::error('Failed to refresh Google Calendar webhook', [
                    'role_id' => $role->id,
                    'role_subdomain' => $role->subdomain,
                    'error' => $e->getMessage(),
                ]);
                $errors++;
            }
        }

        $this->info("\nWebhook refresh completed:");
        $this->info("  - Refreshed: {$refreshed}");
        $this->info("  - Errors: {$errors}");

        if ($errors > 0) {
            return 1;
        }

        return 0;
    }
}
