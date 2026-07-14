<?php

namespace App\Http\Controllers;

use App\Models\MicrosoftCalendarSync;
use App\Services\AuditService;
use App\Services\MicrosoftCalendarService;
use App\Utils\UrlUtils;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MicrosoftCalendarController extends Controller
{
    protected $microsoftCalendarService;

    public function __construct(MicrosoftCalendarService $microsoftCalendarService)
    {
        $this->microsoftCalendarService = $microsoftCalendarService;
    }

    /**
     * Redirect to Microsoft OAuth
     */
    public function redirect(): RedirectResponse
    {
        $user = Auth::user();

        // If the user has tokens but no refresh token, force a fresh consent
        if ($user->microsoft_token && ! $user->microsoft_refresh_token) {
            $authUrl = $this->microsoftCalendarService->getAuthUrlWithForce();
        } else {
            $authUrl = $this->microsoftCalendarService->getAuthUrl();
        }

        return redirect($authUrl);
    }

    /**
     * Handle Microsoft OAuth callback
     */
    public function callback(Request $request): RedirectResponse
    {
        try {
            $code = $request->get('code');

            if (! $code) {
                return redirect()->to(route('profile.edit').'#section-microsoft-calendar')
                    ->with('error', 'Outlook authorization failed. Please try again.');
            }

            $expectedState = session()->pull('microsoft_oauth_state');
            $providedState = (string) $request->get('state');

            if (! $expectedState || ! hash_equals($expectedState, $providedState)) {
                Log::warning('Microsoft OAuth state mismatch', ['user_id' => Auth::id()]);

                return redirect()->to(route('profile.edit').'#section-microsoft-calendar')
                    ->with('error', 'Outlook authorization failed. Please try again.');
            }

            $token = $this->microsoftCalendarService->getAccessToken($code);

            if (isset($token['error']) || empty($token['access_token'])) {
                Log::error('Microsoft OAuth error', ['error' => $token['error'] ?? 'no_access_token']);

                return redirect()->to(route('profile.edit').'#section-microsoft-calendar')
                    ->with('error', 'Outlook authorization failed: '.($token['error_description'] ?? 'Unknown error'));
            }

            // Store tokens in user record
            $user = Auth::user();
            $user->update([
                'microsoft_id' => ! empty($token['id_token']) ? $this->extractMicrosoftId($token['id_token']) : null,
                'microsoft_token' => $token['access_token'],
                'microsoft_refresh_token' => $token['refresh_token'] ?? null,
                'microsoft_token_expires_at' => now()->addSeconds($token['expires_in'] ?? 3600),
            ]);

            AuditService::log(AuditService::MICROSOFT_CALENDAR_CONNECT, $user->id, 'User', $user->id);

            return redirect()->to(route('profile.edit').'#section-microsoft-calendar')
                ->with('message', 'Outlook Calendar connected successfully!');

        } catch (\Exception $e) {
            Log::error('Microsoft Calendar OAuth callback error', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return redirect()->to(route('profile.edit').'#section-microsoft-calendar')
                ->with('error', 'Failed to connect Outlook Calendar. Please try again.');
        }
    }

    /**
     * Re-authorize Outlook Calendar (for users missing a refresh token)
     */
    public function reauthorize(): RedirectResponse
    {
        $user = Auth::user();

        if (! $user->microsoft_token) {
            return redirect()->to(route('profile.edit').'#section-microsoft-calendar')
                ->with('error', 'Outlook Calendar not connected. Please connect first.');
        }

        $authUrl = $this->microsoftCalendarService->getAuthUrlWithForce();

        return redirect($authUrl);
    }

    /**
     * Disconnect Outlook Calendar
     */
    public function disconnect(): RedirectResponse
    {
        $user = Auth::user();

        // Clean up any active subscriptions before disconnecting (owned roles only)
        try {
            $ownedRoles = $user->owner()->whereNotNull('microsoft_webhook_id')->get();

            foreach ($ownedRoles as $role) {
                if ($role->microsoft_webhook_id) {
                    if ($this->microsoftCalendarService->ensureValidToken($user)) {
                        $this->microsoftCalendarService->deleteSubscription($user, $role->microsoft_webhook_id);
                    }

                    // Clear subscription + delta state from role (dead without the account).
                    // forceFill: these are system-managed columns kept out of $fillable.
                    $role->forceFill([
                        'microsoft_webhook_id' => null,
                        'microsoft_webhook_expires_at' => null,
                        'microsoft_sync_token' => null,
                        'microsoft_last_sync_at' => null,
                    ])->save();
                }
            }

            // Clear sync direction on owned roles only
            $user->owner()->update(['microsoft_sync_direction' => null]);

            // Clear all Microsoft calendar sync records for this user
            MicrosoftCalendarSync::where('user_id', $user->id)->delete();

            // Clear per-schedule calendar selections for this user (all roles)
            DB::table('role_user')
                ->where('user_id', $user->id)
                ->whereNotNull('microsoft_calendar_id')
                ->update(['microsoft_calendar_id' => null]);
        } catch (\Exception $e) {
            Log::warning('Failed to clean up subscriptions during Outlook Calendar disconnect', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }

        $user->update([
            'microsoft_id' => null,
            'microsoft_token' => null,
            'microsoft_refresh_token' => null,
            'microsoft_token_expires_at' => null,
        ]);

        AuditService::log(AuditService::MICROSOFT_CALENDAR_DISCONNECT, $user->id, 'User', $user->id);

        return redirect()->to(route('profile.edit').'#section-microsoft-calendar')
            ->with('message', 'Outlook Calendar disconnected successfully.');
    }

    /**
     * Get the user's Outlook calendars
     */
    public function getCalendars(Request $request)
    {
        $user = Auth::user();

        if (! $user->microsoft_token) {
            return response()->json(['error' => 'Outlook Calendar not connected'], 400);
        }

        try {
            if (! $this->microsoftCalendarService->ensureValidToken($user)) {
                return response()->json(['error' => 'Outlook Calendar token invalid and refresh failed'], 401);
            }

            $calendars = $this->microsoftCalendarService->getCalendars($user);

            return response()->json(['calendars' => $calendars]);

        } catch (\Exception $e) {
            Log::error('Failed to get Outlook Calendars', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json(['error' => 'Failed to fetch calendars'], 500);
        }
    }

    /**
     * Sync a specific event to Outlook
     */
    public function syncEvent(Request $request, $subdomain, $eventId)
    {
        $user = Auth::user();

        if (! $user->microsoft_token) {
            return response()->json(['error' => 'Outlook Calendar not connected'], 400);
        }

        try {
            $event = \App\Models\Event::findOrFail(UrlUtils::decodeId($eventId));

            if (! $event->roles->contains(function ($role) use ($user) {
                return $role->users->contains($user);
            })) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            if (! $this->microsoftCalendarService->ensureValidToken($user)) {
                return response()->json(['error' => 'Outlook Calendar token invalid and refresh failed'], 401);
            }

            $subdomain = request()->subdomain;
            $role = \App\Models\Role::subdomain($subdomain)->first();

            if (! $role) {
                return response()->json(['error' => 'Role not found'], 404);
            }

            $sync = MicrosoftCalendarSync::where('user_id', $user->id)
                ->where('event_id', $event->id)
                ->where('role_id', $role->id)
                ->first();

            if ($sync?->microsoft_event_id) {
                $calendarId = $sync->microsoft_calendar_id ?: $role->getMicrosoftCalendarId();
                $microsoftEvent = $this->microsoftCalendarService->updateEvent($event, $sync->microsoft_event_id, $role, $calendarId);
            } else {
                $calendarId = $role->getMicrosoftCalendarId();
                $microsoftEvent = $this->microsoftCalendarService->createEvent($event, $role, $calendarId);
                if ($microsoftEvent && ! empty($microsoftEvent['id'])) {
                    MicrosoftCalendarSync::updateOrCreate(
                        ['user_id' => $user->id, 'event_id' => $event->id, 'role_id' => $role->id],
                        ['microsoft_event_id' => $microsoftEvent['id'], 'microsoft_calendar_id' => $calendarId]
                    );
                }
            }

            if ($microsoftEvent && ! empty($microsoftEvent['id'])) {
                return response()->json([
                    'message' => 'Event synced successfully',
                    'microsoft_event_id' => $microsoftEvent['id'],
                ]);
            }

            return response()->json(['error' => 'Failed to sync event'], 500);

        } catch (\Exception $e) {
            Log::error('Failed to sync event to Outlook Calendar', [
                'user_id' => $user->id,
                'event_id' => $eventId,
                'error' => $e->getMessage(),
            ]);

            return response()->json(['error' => 'Failed to sync event'], 500);
        }
    }

    /**
     * Remove an event from Outlook
     */
    public function unsyncEvent(Request $request, $subdomain, $eventId)
    {
        $user = Auth::user();

        if (! $user->microsoft_token) {
            return response()->json(['error' => 'Outlook Calendar not connected'], 400);
        }

        try {
            $event = \App\Models\Event::findOrFail(UrlUtils::decodeId($eventId));

            if (! $event->roles->contains(function ($role) use ($user) {
                return $role->users->contains($user);
            })) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $subdomain = request()->subdomain;
            $role = \App\Models\Role::subdomain($subdomain)->first();

            if (! $role) {
                return response()->json(['error' => 'Role not found'], 404);
            }

            $sync = MicrosoftCalendarSync::where('user_id', $user->id)
                ->where('event_id', $event->id)
                ->where('role_id', $role->id)
                ->first();

            if ($sync?->microsoft_event_id) {
                if (! $this->microsoftCalendarService->ensureValidToken($user)) {
                    return response()->json(['error' => 'Outlook Calendar token invalid and refresh failed'], 401);
                }

                $calendarId = $sync->microsoft_calendar_id ?: $role->getMicrosoftCalendarId();
                $this->microsoftCalendarService->deleteEvent($sync->microsoft_event_id, $calendarId, $role->id);
                $sync->delete();
            }

            return response()->json(['message' => 'Event removed from Outlook Calendar']);

        } catch (\Exception $e) {
            Log::error('Failed to remove event from Outlook Calendar', [
                'user_id' => $user->id,
                'event_id' => $eventId,
                'error' => $e->getMessage(),
            ]);

            return response()->json(['error' => 'Failed to remove event'], 500);
        }
    }

    /**
     * Unified sync method that handles 'to', 'from', and 'both' directions
     */
    public function sync(Request $request, $subdomain)
    {
        $user = Auth::user();

        if (! $user->microsoft_token) {
            return response()->json(['error' => 'Outlook Calendar not connected'], 400);
        }

        $syncDirection = null;

        try {
            $role = \App\Models\Role::subdomain($subdomain)->firstOrFail();

            if (! $role->users->contains($user)) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $syncDirection = $request->input('sync_direction', $role->microsoft_sync_direction);

            if (! in_array($syncDirection, ['to', 'from', 'both'])) {
                return response()->json(['error' => 'Invalid sync direction. Must be "to", "from", or "both"'], 400);
            }

            if ($request->has('sync_direction')) {
                $this->updateRoleSyncDirection($user, $syncDirection, $role);
            }

            if (! $this->microsoftCalendarService->ensureValidToken($user)) {
                return response()->json(['error' => 'Outlook Calendar token invalid and refresh failed'], 401);
            }

            $results = [];

            switch ($syncDirection) {
                case 'to':
                    $results['to'] = $this->microsoftCalendarService->syncUserEvents($user, $role);
                    break;

                case 'from':
                    $results['from'] = $this->microsoftCalendarService->syncFromMicrosoftCalendar($user, $role, $role->getMicrosoftCalendarId());
                    break;

                case 'both':
                    $results['to'] = $this->microsoftCalendarService->syncUserEvents($user, $role);
                    $results['from'] = $this->microsoftCalendarService->syncFromMicrosoftCalendar($user, $role, $role->getMicrosoftCalendarId());
                    break;
            }

            AuditService::log(AuditService::MICROSOFT_CALENDAR_SYNC, $user->id, 'Role', $role->id, null, null, $syncDirection);

            return response()->json([
                'message' => __('messages.events_synced'),
                'sync_direction' => $syncDirection,
                'results' => $results,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to sync events', [
                'user_id' => $user->id,
                'subdomain' => $subdomain,
                'sync_direction' => $syncDirection ?? 'unknown',
                'error' => $e->getMessage(),
            ]);

            return response()->json(['error' => 'Failed to sync events'], 500);
        }
    }

    /**
     * Update the role's sync direction and manage the Graph subscription
     */
    private function updateRoleSyncDirection($user, $syncDirection, $role = null)
    {
        try {
            if (! $role) {
                $role = $user->roles()->first();
                if (! $role) {
                    return;
                }
            }

            $role->update(['microsoft_sync_direction' => $syncDirection]);

            if ($syncDirection === 'from' || $syncDirection === 'both') {
                // Need a subscription for near-real-time inbound sync
                if (! $role->hasActiveMicrosoftWebhook()) {
                    if (! $this->microsoftCalendarService->ensureValidToken($user)) {
                        Log::warning('Outlook Calendar token invalid and refresh failed during sync direction update', [
                            'user_id' => $user->id,
                            'role_id' => $role->id,
                        ]);

                        return;
                    }

                    $webhook = $this->microsoftCalendarService->createSubscription(
                        $user,
                        $role->getMicrosoftCalendarId(),
                        route('microsoft.calendar.webhook.handle')
                    );

                    $role->forceFill([
                        'microsoft_webhook_id' => $webhook['id'],
                        'microsoft_webhook_expires_at' => $webhook['expiration'] ? Carbon::parse($webhook['expiration']) : null,
                    ])->save();
                }
            } else {
                // No subscription needed for 'to' or no sync
                if ($role->microsoft_webhook_id) {
                    if ($this->microsoftCalendarService->ensureValidToken($user)) {
                        $this->microsoftCalendarService->deleteSubscription($user, $role->microsoft_webhook_id);
                    }
                }

                $role->forceFill([
                    'microsoft_webhook_id' => null,
                    'microsoft_webhook_expires_at' => null,
                ])->save();
            }

        } catch (\Exception $e) {
            Log::error('Failed to update role Outlook sync direction', [
                'user_id' => $user->id,
                'role_id' => $role ? $role->id : null,
                'sync_direction' => $syncDirection,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Extract the Microsoft user id (oid/sub) from the id_token.
     * Microsoft id_tokens are base64url-encoded, so decode accordingly.
     */
    private function extractMicrosoftId(string $idToken): ?string
    {
        try {
            $parts = explode('.', $idToken);
            if (count($parts) !== 3) {
                return null;
            }

            $payload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);

            return $payload['sub'] ?? null;
        } catch (\Exception $e) {
            return null;
        }
    }
}
