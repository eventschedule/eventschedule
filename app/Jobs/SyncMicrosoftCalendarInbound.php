<?php

namespace App\Jobs;

use App\Models\Role;
use App\Services\MicrosoftCalendarService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

/**
 * Runs the inbound Graph delta sync for a role off the request thread.
 *
 * Microsoft Graph expects the change-notification endpoint to return a 2xx within a few
 * seconds and deprovisions subscriptions after sustained slow responses, so the webhook
 * controller dispatches this job and returns 202 immediately instead of syncing inline.
 */
class SyncMicrosoftCalendarInbound implements ShouldQueue
{
    use Queueable;

    public $deleteWhenMissingModels = true;

    public function __construct(protected Role $role) {}

    public function handle(MicrosoftCalendarService $microsoftCalendarService): void
    {
        // The subscription/calendar belong to the owner; sync with the owner's token.
        $user = $this->role->user;

        if (! $user || ! $user->microsoft_token) {
            return;
        }

        if (! $microsoftCalendarService->ensureValidToken($user)) {
            Log::error('Failed to refresh Microsoft token for inbound webhook sync', [
                'role_id' => $this->role->id,
                'user_id' => $user->id,
            ]);

            return;
        }

        try {
            $microsoftCalendarService->syncFromMicrosoftCalendar($user, $this->role, $this->role->getMicrosoftCalendarId());
        } catch (\Throwable $e) {
            Log::error('Failed to sync from Outlook Calendar via webhook job', [
                'role_id' => $this->role->id,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
