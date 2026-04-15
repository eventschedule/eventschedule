<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AuditService;
use App\Services\GoogleCalendarService;
use App\Utils\UrlUtils;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class GoogleCalendarController extends Controller
{
    protected $googleCalendarService;

    public function __construct(GoogleCalendarService $googleCalendarService)
    {
        $this->googleCalendarService = $googleCalendarService;
    }

    /**
     * Safely calculate expires_in seconds from google_token_expires_at
     */
    private function calculateExpiresIn($expiresAt): int
    {
        if (! $expiresAt) {
            return 3600; // Default to 1 hour
        }

        if (is_string($expiresAt)) {
            $expiresAt = \Carbon\Carbon::parse($expiresAt);
        }

        return now()->diffInSeconds($expiresAt);
    }

    /**
     * Redirect to Google OAuth
     */
    public function redirect(): RedirectResponse
    {
        $user = Auth::user();

        // If user has tokens but no refresh token, force re-authorization
        if ($user->google_token && ! $user->google_refresh_token) {
            $authUrl = $this->googleCalendarService->getAuthUrlWithForce();
        } else {
            $authUrl = $this->googleCalendarService->getAuthUrl();
        }

        return redirect($authUrl);
    }

    /**
     * Handle Google OAuth callback
     */
    public function callback(Request $request): RedirectResponse
    {
        try {
            $code = $request->get('code');

            if (! $code) {
                return redirect()->to(route('profile.edit').'#section-google-calendar')
                    ->with('error', 'Google authorization failed. Please try again.');
            }

            $token = $this->googleCalendarService->getAccessToken($code);

            if (isset($token['error'])) {
                Log::error('Google OAuth error', ['error' => $token['error']]);

                return redirect()->to(route('profile.edit').'#section-google-calendar')
                    ->with('error', 'Google authorization failed: '.$token['error_description']);
            }

            // Store tokens in user record
            $user = Auth::user();
            $user->update([
                'google_id' => $token['id_token'] ? $this->extractGoogleId($token['id_token']) : null,
                'google_token' => $token['access_token'],
                'google_refresh_token' => $token['refresh_token'] ?? null,
                'google_token_expires_at' => now()->addSeconds($token['expires_in']),
            ]);

            AuditService::log(AuditService::GOOGLE_CALENDAR_CONNECT, $user->id, 'User', $user->id);

            return redirect()->to(route('profile.edit').'#section-google-calendar')
                ->with('message', 'Google Calendar connected successfully!');

        } catch (\Exception $e) {
            Log::error('Google Calendar OAuth callback error', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return redirect()->to(route('profile.edit').'#section-google-calendar')
                ->with('error', 'Failed to connect Google Calendar. Please try again.');
        }
    }

    /**
     * Re-authorize Google Calendar (for users missing refresh token)
     */
    public function reauthorize(): RedirectResponse
    {
        $user = Auth::user();

        if (! $user->google_token) {
            return redirect()->to(route('profile.edit').'#section-google-calendar')
                ->with('error', 'Google Calendar not connected. Please connect first.');
        }

        // Force re-authorization to get refresh token
        $authUrl = $this->googleCalendarService->getAuthUrlWithForce();

        return redirect($authUrl);
    }

    /**
     * Disconnect Google Calendar
     */
    public function disconnect(): RedirectResponse
    {
        $user = Auth::user();

        // Clean up any active webhooks before disconnecting (owned roles only)
        try {
            $ownedRoles = $user->owner()->whereNotNull('google_webhook_id')->get();

            foreach ($ownedRoles as $role) {
                if ($role->google_webhook_id && $role->google_webhook_resource_id) {
                    // Ensure user has valid token before deleting webhook
                    if ($this->googleCalendarService->ensureValidToken($user)) {
                        $this->googleCalendarService->deleteWebhook($role->google_webhook_id, $role->google_webhook_resource_id);
                    }

                    // Clear webhook data from role
                    $role->update([
                        'google_webhook_id' => null,
                        'google_webhook_resource_id' => null,
                        'google_webhook_expires_at' => null,
                    ]);
                }
            }
            // Clear sync direction on owned roles only
            $user->owner()->update(['sync_direction' => null]);

            // Clear all calendar sync records for this user
            \App\Models\CalendarSync::where('user_id', $user->id)->delete();

            // Clear calendar settings for this user (all roles)
            \DB::table('role_user')
                ->where('user_id', $user->id)
                ->whereNotNull('google_calendar_id')
                ->update(['google_calendar_id' => null]);
        } catch (\Exception $e) {
            Log::warning('Failed to clean up webhooks during Google Calendar disconnect', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }

        $user->update([
            'google_id' => null,
            'google_token' => null,
            'google_refresh_token' => null,
            'google_token_expires_at' => null,
        ]);

        AuditService::log(AuditService::GOOGLE_CALENDAR_DISCONNECT, $user->id, 'User', $user->id);

        return redirect()->to(route('profile.edit').'#section-google-calendar')
            ->with('message', 'Google Calendar disconnected successfully.');
    }

    /**
     * Get user's Google Calendars
     */
    public function getCalendars(Request $request)
    {
        $user = Auth::user();

        if (! $user->google_token) {
            return response()->json(['error' => 'Google Calendar not connected'], 400);
        }

        try {
            // Ensure user has valid token before getting calendars
            if (! $this->googleCalendarService->ensureValidToken($user)) {
                return response()->json(['error' => 'Google Calendar token invalid and refresh failed'], 401);
            }

            $calendars = $this->googleCalendarService->getCalendars();

            return response()->json(['calendars' => $calendars]);

        } catch (\Exception $e) {
            Log::error('Failed to get Google Calendars', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json(['error' => 'Failed to fetch calendars'], 500);
        }
    }

    /**
     * Sync a specific event to Google Calendar
     */
    public function syncEvent(Request $request, $subdomain, $eventId)
    {
        $user = Auth::user();

        if (! $user->google_token) {
            return response()->json(['error' => 'Google Calendar not connected'], 400);
        }

        try {
            $event = \App\Models\Event::findOrFail(UrlUtils::decodeId($eventId));

            // Check if user has permission to sync this event
            if (! $event->roles->contains(function ($role) use ($user) {
                return $role->users->contains($user);
            })) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            // Ensure user has valid token before syncing
            if (! $this->googleCalendarService->ensureValidToken($user)) {
                return response()->json(['error' => 'Google Calendar token invalid and refresh failed'], 401);
            }

            // Get the role from the subdomain in the request
            $subdomain = request()->subdomain;
            $role = \App\Models\Role::subdomain($subdomain)->first();

            if (! $role) {
                return response()->json(['error' => 'Role not found'], 404);
            }

            $sync = \App\Models\CalendarSync::where('user_id', $user->id)
                ->where('event_id', $event->id)
                ->where('role_id', $role->id)
                ->first();

            if ($sync?->google_event_id) {
                $googleEvent = $this->googleCalendarService->updateEvent($event, $sync->google_event_id, $role);
            } else {
                $googleEvent = $this->googleCalendarService->createEvent($event, $role);
                if ($googleEvent) {
                    \App\Models\CalendarSync::updateOrCreate(
                        ['user_id' => $user->id, 'event_id' => $event->id, 'role_id' => $role->id],
                        ['google_event_id' => $googleEvent->getId()]
                    );
                }
            }

            if ($googleEvent) {
                return response()->json([
                    'message' => 'Event synced successfully',
                    'google_event_id' => $googleEvent->getId(),
                ]);
            } else {
                return response()->json(['error' => 'Failed to sync event'], 500);
            }

        } catch (\Exception $e) {
            Log::error('Failed to sync event to Google Calendar', [
                'user_id' => $user->id,
                'event_id' => $eventId,
                'error' => $e->getMessage(),
            ]);

            return response()->json(['error' => 'Failed to sync event'], 500);
        }
    }

    /**
     * Remove event from Google Calendar
     */
    public function unsyncEvent(Request $request, $subdomain, $eventId)
    {
        $user = Auth::user();

        if (! $user->google_token) {
            return response()->json(['error' => 'Google Calendar not connected'], 400);
        }

        try {
            $event = \App\Models\Event::findOrFail(UrlUtils::decodeId($eventId));

            // Check if user has permission to unsync this event
            if (! $event->roles->contains(function ($role) use ($user) {
                return $role->users->contains($user);
            })) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            // Get the role from the subdomain in the request
            $subdomain = request()->subdomain;
            $role = \App\Models\Role::subdomain($subdomain)->first();

            if (! $role) {
                return response()->json(['error' => 'Role not found'], 404);
            }

            $sync = \App\Models\CalendarSync::where('user_id', $user->id)
                ->where('event_id', $event->id)
                ->where('role_id', $role->id)
                ->first();

            if ($sync?->google_event_id) {
                // Ensure user has valid token before deleting
                if (! $this->googleCalendarService->ensureValidToken($user)) {
                    return response()->json(['error' => 'Google Calendar token invalid and refresh failed'], 401);
                }

                $this->googleCalendarService->deleteEvent($sync->google_event_id, $role->getGoogleCalendarId(), $role->id);
                $sync->delete();
            }

            return response()->json(['message' => 'Event removed from Google Calendar']);

        } catch (\Exception $e) {
            Log::error('Failed to remove event from Google Calendar', [
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

        if (! $user->google_token) {
            return response()->json(['error' => 'Google Calendar not connected'], 400);
        }

        try {
            $role = \App\Models\Role::subdomain($subdomain)->firstOrFail();

            // Check if user has permission to sync this role
            if (! $role->users->contains($user)) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            // Get sync direction from request, default to role's current setting
            $syncDirection = $request->input('sync_direction', $role->sync_direction);

            // Validate sync direction
            if (! in_array($syncDirection, ['to', 'from', 'both'])) {
                return response()->json(['error' => 'Invalid sync direction. Must be "to", "from", or "both"'], 400);
            }

            // Update the role's sync_direction if provided
            if ($request->has('sync_direction')) {
                $this->updateRoleSyncDirection($user, $syncDirection, $role);
            }

            // Ensure user has valid token before syncing
            if (! $this->googleCalendarService->ensureValidToken($user)) {
                return response()->json(['error' => 'Google Calendar token invalid and refresh failed'], 401);
            }

            $results = [];

            // Handle different sync directions
            switch ($syncDirection) {
                case 'to':
                    $results['to'] = $this->googleCalendarService->syncUserEvents($user, $role);
                    break;

                case 'from':
                    $calendarId = $role->getGoogleCalendarId();
                    $results['from'] = $this->googleCalendarService->syncFromGoogleCalendar($user, $role, $calendarId);
                    break;

                case 'both':
                    // Sync to Google Calendar first
                    $results['to'] = $this->googleCalendarService->syncUserEvents($user, $role);

                    // Then sync from Google Calendar
                    $calendarId = $role->getGoogleCalendarId();
                    $results['from'] = $this->googleCalendarService->syncFromGoogleCalendar($user, $role, $calendarId);
                    break;
            }

            AuditService::log(AuditService::GOOGLE_CALENDAR_SYNC, $user->id, 'Role', $role->id, null, null, $syncDirection);

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
     * Enable or disable member calendar sync for the current user
     */
    public function memberSync(Request $request, $subdomain)
    {
        $user = Auth::user();

        if (! $user->google_token) {
            return response()->json(['error' => __('messages.google_calendar_not_connected')], 400);
        }

        try {
            $role = \App\Models\Role::subdomain($subdomain)->firstOrFail();

            // Must be a member but not the owner
            if ($role->user_id == $user->id) {
                return response()->json(['error' => __('messages.not_authorized')], 403);
            }

            $roleUser = \App\Models\RoleUser::where('user_id', $user->id)
                ->where('role_id', $role->id)
                ->first();

            if (! $roleUser) {
                return response()->json(['error' => __('messages.not_authorized')], 403);
            }

            $googleCalendarId = $request->input('google_calendar_id');

            if ($googleCalendarId) {
                // Enable sync
                $roleUser->update(['google_calendar_id' => $googleCalendarId]);

                AuditService::log(AuditService::GOOGLE_CALENDAR_MEMBER_SYNC, $user->id, 'Role', $role->id, null, null, 'enabled');

                return response()->json([
                    'message' => __('messages.member_sync_enabled'),
                ]);
            } else {
                // Disable sync - delete synced events from Google Calendar
                $oldCalendarId = $roleUser->google_calendar_id;

                if ($oldCalendarId) {
                    // Ensure valid token
                    if ($this->googleCalendarService->ensureValidToken($user)) {
                        $this->googleCalendarService->setAccessToken([
                            'access_token' => $user->google_token,
                            'refresh_token' => $user->google_refresh_token,
                            'expires_in' => $this->calculateExpiresIn($user->google_token_expires_at),
                        ]);

                        // Delete synced events from member's Google Calendar
                        $syncs = \App\Models\CalendarSync::where('user_id', $user->id)
                            ->where('role_id', $role->id)
                            ->whereNotNull('google_event_id')
                            ->get();

                        foreach ($syncs as $sync) {
                            try {
                                $this->googleCalendarService->deleteEvent($sync->google_event_id, $oldCalendarId, $role->id);
                            } catch (\Exception $e) {
                                Log::warning('Failed to delete member synced event from Google Calendar', [
                                    'user_id' => $user->id,
                                    'event_id' => $sync->event_id,
                                    'error' => $e->getMessage(),
                                ]);
                            }
                        }
                    }

                    // Clean up sync records
                    \App\Models\CalendarSync::where('user_id', $user->id)
                        ->where('role_id', $role->id)
                        ->delete();
                }

                $roleUser->update(['google_calendar_id' => null]);

                AuditService::log(AuditService::GOOGLE_CALENDAR_MEMBER_SYNC, $user->id, 'Role', $role->id, null, null, 'disabled');

                return response()->json([
                    'message' => __('messages.member_sync_disabled'),
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Failed to update member calendar sync', [
                'user_id' => $user->id,
                'subdomain' => $subdomain,
                'error' => $e->getMessage(),
            ]);

            return response()->json(['error' => __('messages.sync_error')], 500);
        }
    }

    /**
     * Update role's sync direction and handle webhook management
     */
    private function updateRoleSyncDirection($user, $syncDirection, $role = null)
    {
        try {
            // If no specific role provided, get the first role for this user
            if (! $role) {
                $role = $user->roles()->first();
                if (! $role) {
                    return;
                }
            }

            $oldSyncDirection = $role->sync_direction;

            // Update sync direction
            $role->update(['sync_direction' => $syncDirection]);

            // Handle webhook management based on sync direction
            if ($syncDirection === 'from' || $syncDirection === 'both') {
                // Need webhook for syncing from Google
                if (! $role->hasActiveWebhook()) {
                    // Ensure user has valid token before creating webhook
                    if (! $this->googleCalendarService->ensureValidToken($user)) {
                        Log::warning('Google Calendar token invalid and refresh failed during sync direction update', [
                            'user_id' => $user->id,
                            'role_id' => $role->id,
                        ]);

                        return;
                    }

                    $calendarId = $role->getGoogleCalendarId();
                    $webhookUrl = route('google.calendar.webhook.handle');

                    // Create webhook
                    $webhook = $this->googleCalendarService->createWebhook($calendarId, $webhookUrl);

                    $role->update([
                        'google_webhook_id' => $webhook['id'],
                        'google_webhook_resource_id' => $webhook['resourceId'],
                        'google_webhook_expires_at' => \Carbon\Carbon::createFromTimestamp($webhook['expiration'] / 1000),
                    ]);
                }
            } else {
                // No webhook needed for 'to' direction or no sync
                if ($role->google_webhook_id) {
                    // Delete existing webhook
                    if ($this->googleCalendarService->ensureValidToken($user)) {
                        $this->googleCalendarService->deleteWebhook($role->google_webhook_id, $role->google_webhook_resource_id);
                    }
                }

                $role->update([
                    'google_webhook_id' => null,
                    'google_webhook_resource_id' => null,
                    'google_webhook_expires_at' => null,
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Failed to update role sync direction', [
                'user_id' => $user->id,
                'role_id' => $role ? $role->id : null,
                'sync_direction' => $syncDirection,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Extract Google ID from ID token
     */
    private function extractGoogleId(string $idToken): ?string
    {
        try {
            $parts = explode('.', $idToken);
            if (count($parts) !== 3) {
                return null;
            }

            $payload = json_decode(base64_decode($parts[1]), true);

            return $payload['sub'] ?? null;
        } catch (\Exception $e) {
            return null;
        }
    }
}
