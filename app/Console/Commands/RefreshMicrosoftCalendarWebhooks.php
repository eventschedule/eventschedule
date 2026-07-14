<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Services\MicrosoftCalendarService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RefreshMicrosoftCalendarWebhooks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'microsoft:refresh-webhooks
                            {--force : Force refresh all subscriptions, even if not expiring soon}
                            {--role= : Refresh the subscription for a specific role ID or subdomain}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Renew Microsoft Graph calendar subscriptions to prevent expiration';

    protected $microsoftCalendarService;

    public function __construct(MicrosoftCalendarService $microsoftCalendarService)
    {
        parent::__construct();
        $this->microsoftCalendarService = $microsoftCalendarService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting Outlook Calendar subscription refresh...');

        $force = $this->option('force');
        $roleFilter = $this->option('role');

        $query = Role::whereNotNull('microsoft_webhook_id')
            ->whereNotNull('microsoft_webhook_expires_at');

        // Graph subscriptions live ~2.5 days; renew any expiring within the next day.
        if (! $force) {
            $query->where('microsoft_webhook_expires_at', '<=', now()->addDay());
        }

        if ($roleFilter) {
            if (is_numeric($roleFilter)) {
                $query->where('id', $roleFilter);
            } else {
                $query->where('subdomain', $roleFilter);
            }
        }

        $roles = $query->get();

        if ($roles->isEmpty()) {
            $this->info('No subscriptions need refreshing.');

            return 0;
        }

        $this->info("Found {$roles->count()} subscription(s) to refresh.");

        $refreshed = 0;
        $errors = 0;

        foreach ($roles as $role) {
            $this->info("Processing role: {$role->subdomain} (ID: {$role->id})");

            try {
                // The subscription belongs to the owner; renew with the owner's token.
                $user = $role->user;

                if (! $user || ! $user->microsoft_token) {
                    $this->warn("  - No owner with Outlook token found for role {$role->subdomain}");
                    $errors++;

                    continue;
                }

                if (! $this->microsoftCalendarService->ensureValidToken($user)) {
                    $this->warn("  - Failed to refresh Outlook token for role {$role->subdomain}");
                    $errors++;

                    continue;
                }

                // Try to extend the existing subscription; recreate it if Graph 404s.
                $result = $this->microsoftCalendarService->renewSubscription($user, $role->microsoft_webhook_id);

                if (! $result) {
                    $this->info('  - Subscription expired or missing, creating a new one');
                    $result = $this->microsoftCalendarService->createSubscription(
                        $user,
                        $role->getMicrosoftCalendarId(),
                        route('microsoft.calendar.webhook.handle')
                    );
                }

                $role->forceFill([
                    'microsoft_webhook_id' => $result['id'] ?? $role->microsoft_webhook_id,
                    'microsoft_webhook_expires_at' => ! empty($result['expiration']) ? \Carbon\Carbon::parse($result['expiration']) : $role->microsoft_webhook_expires_at,
                ])->save();

                $this->info("  - Subscription refreshed. New expiration: {$role->fresh()->microsoft_webhook_expires_at}");
                $refreshed++;

            } catch (\Exception $e) {
                $this->error("  - Failed to refresh subscription for role {$role->subdomain}: {$e->getMessage()}");
                Log::error('Failed to refresh Outlook Calendar subscription', [
                    'role_id' => $role->id,
                    'role_subdomain' => $role->subdomain,
                    'error' => $e->getMessage(),
                ]);
                $errors++;
            }
        }

        $this->info("\nSubscription refresh completed:");
        $this->info("  - Refreshed: {$refreshed}");
        $this->info("  - Errors: {$errors}");

        return $errors > 0 ? 1 : 0;
    }
}
