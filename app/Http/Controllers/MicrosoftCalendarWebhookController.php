<?php

namespace App\Http\Controllers;

use App\Jobs\SyncMicrosoftCalendarInbound;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class MicrosoftCalendarWebhookController extends Controller
{
    /**
     * Handle Microsoft Graph change notifications.
     */
    public function handle(Request $request)
    {
        // Subscription validation handshake: Graph POSTs ?validationToken=... on
        // subscription create/renew and expects it echoed back as text/plain.
        if ($request->filled('validationToken')) {
            return response($request->query('validationToken'), 200)
                ->header('Content-Type', 'text/plain');
        }

        try {
            $secret = config('services.microsoft.webhook_secret') ?? '';
            $notifications = $request->json('value', []);
            $validated = 0;

            foreach ($notifications as $notification) {
                $clientState = (string) ($notification['clientState'] ?? '');

                // clientState is the only authenticity check on inbound notifications.
                if (! $secret || ! hash_equals($secret, $clientState)) {
                    Log::warning('Invalid Microsoft webhook clientState', [
                        'subscription_id' => $notification['subscriptionId'] ?? null,
                        'ip' => $request->ip(),
                    ]);

                    continue;
                }

                $validated++;

                $subscriptionId = $notification['subscriptionId'] ?? null;
                $role = $subscriptionId ? Role::where('microsoft_webhook_id', $subscriptionId)->first() : null;

                if (! $role) {
                    Log::warning('No role found for Microsoft subscription', [
                        'subscription_id' => $subscriptionId,
                    ]);

                    continue;
                }

                if (! $role->syncsFromMicrosoft()) {
                    continue;
                }

                // Use the owner - the account whose token created the subscription and whose
                // calendar getMicrosoftCalendarId() reads (not an arbitrary member).
                $user = $role->user;
                if (! $user || ! $user->microsoft_token) {
                    Log::warning('No owner with Microsoft token found for role', [
                        'role_id' => $role->id,
                    ]);

                    continue;
                }

                // Debounce: Graph sends one notification per changed event. The deltaLink
                // returns every pending change, so a single sync per role per minute is
                // enough; the rest are caught by the next notification or the 15-min poll.
                // Cache::add is atomic (set-if-absent) so only the first concurrent
                // notification proceeds.
                $debounceKey = "microsoft-webhook-sync-{$role->id}";
                if (! Cache::add($debounceKey, true, 60)) {
                    continue;
                }

                // Sync off the request thread - Graph expects a fast 2xx and deprovisions
                // subscriptions after slow responses. Token refresh happens inside the job.
                try {
                    SyncMicrosoftCalendarInbound::dispatch($role);
                } catch (\Throwable $e) {
                    // Release the debounce key so this role isn't blocked for 60s on a queue blip.
                    Cache::forget($debounceKey);
                    Log::error('Failed to dispatch Outlook inbound sync job', [
                        'role_id' => $role->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            if ($validated === 0 && ! empty($notifications)) {
                return response('Unauthorized', 401);
            }

            return response('OK', 202);

        } catch (\Exception $e) {
            Log::error('Microsoft Calendar webhook error', [
                'error' => $e->getMessage(),
            ]);

            return response('Internal Server Error', 500);
        }
    }

    /**
     * Handle a GET validation request (echoes the validationToken).
     */
    public function verify(Request $request)
    {
        $validationToken = $request->query('validationToken');

        if ($validationToken) {
            return response($validationToken, 200)->header('Content-Type', 'text/plain');
        }

        return response('Bad Request', 400);
    }
}
