<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\GoogleCalendarService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

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
        if (!$expiresAt) {
            return 3600; // Default to 1 hour
        }
        
        if (is_string($expiresAt)) {
            $expiresAt = \Carbon\Carbon::parse($expiresAt);
        }
        
        return $expiresAt->diffInSeconds(now());
    }

    /**
     * Redirect to Google OAuth
     */
    public function redirect(): RedirectResponse
    {
        $user = Auth::user();
        
        // If user has tokens but no refresh token, force re-authorization
        if ($user->google_token && !$user->google_refresh_token) {
            Log::info('User has access token but no refresh token, forcing re-authorization', [
                'user_id' => $user->id,
            ]);
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
            
            if (!$code) {
                return redirect()->route('profile.edit')
                    ->with('error', 'Google authorization failed. Please try again.');
            }

            $token = $this->googleCalendarService->getAccessToken($code);
                        
            if (isset($token['error'])) {
                Log::error('Google OAuth error', ['error' => $token['error']]);
                return redirect()->route('profile.edit')
                    ->with('error', 'Google authorization failed: ' . $token['error_description']);
            }

            // Store tokens in user record
            $user = Auth::user();
            $user->update([
                'google_id' => $token['id_token'] ? $this->extractGoogleId($token['id_token']) : null,
                'google_token' => $token['access_token'],
                'google_refresh_token' => $token['refresh_token'] ?? null,
                'google_token_expires_at' => now()->addSeconds($token['expires_in']),
            ]);
            
            return redirect()->route('profile.edit')
                ->with('message', 'Google Calendar connected successfully!');

        } catch (\Exception $e) {
            Log::error('Google Calendar OAuth callback error', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return redirect()->route('profile.edit')
                ->with('error', 'Failed to connect Google Calendar. Please try again.');
        }
    }

    /**
     * Re-authorize Google Calendar (for users missing refresh token)
     */
    public function reauthorize(): RedirectResponse
    {
        $user = Auth::user();
        
        if (!$user->google_token) {
            return redirect()->route('profile.edit')
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
        
        $user->update([
            'google_id' => null,
            'google_token' => null,
            'google_refresh_token' => null,
            'google_token_expires_at' => null,
        ]);

        return redirect()->route('profile.edit')
            ->with('message', 'Google Calendar disconnected successfully.');
    }

    /**
     * Get user's Google Calendars
     */
    public function getCalendars(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->google_token) {
            return response()->json(['error' => 'Google Calendar not connected'], 400);
        }

        try {
            // Ensure user has valid token before getting calendars
            if (!$this->googleCalendarService->ensureValidToken($user)) {
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
     * Update role's Google Calendar selection
     */
    public function updateRoleCalendar(Request $request, $subdomain)
    {
        $user = Auth::user();
        
        if (!$user->google_token) {
            return response()->json(['error' => 'Google Calendar not connected'], 400);
        }

        $request->validate([
            'calendar_id' => 'required|string',
        ]);

        try {
            $role = \App\Models\Role::subdomain($subdomain)->firstOrFail();
            
            // Check if user has permission to update this role
            if (!$role->users->contains($user)) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            // Ensure user has valid token before validating calendar
            if (!$this->googleCalendarService->ensureValidToken($user)) {
                return response()->json(['error' => 'Google Calendar token invalid and refresh failed'], 401);
            }

            // Validate that the selected calendar exists and user has access
            $calendars = $this->googleCalendarService->getCalendars();
            $calendarExists = collect($calendars)->contains('id', $request->calendar_id);
            
            if (!$calendarExists) {
                return response()->json(['error' => 'Selected calendar not found or access denied'], 400);
            }

            $role->update([
                'google_calendar_id' => $request->calendar_id,
            ]);

            return response()->json([
                'message' => 'Google Calendar updated successfully',
                'calendar_id' => $request->calendar_id,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to update role Google Calendar', [
                'user_id' => $user->id,
                'subdomain' => $subdomain,
                'error' => $e->getMessage(),
            ]);

            return response()->json(['error' => 'Failed to update calendar'], 500);
        }
    }

    /**
     * Sync all events to Google Calendar for a specific role
     */
    public function syncEvents(Request $request, $subdomain)
    {
        $user = Auth::user();
        
        if (!$user->google_token) {
            return response()->json(['error' => 'Google Calendar not connected'], 400);
        }

        try {
            $role = \App\Models\Role::subdomain($subdomain)->firstOrFail();
            
            // Check if user has permission to sync this role
            if (!$role->users->contains($user)) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            // Ensure user has valid token before syncing
            if (!$this->googleCalendarService->ensureValidToken($user)) {
                return response()->json(['error' => 'Google Calendar token invalid and refresh failed'], 401);
            }

            // If sync_direction is provided, update the role's sync_direction
            if ($request->has('sync_direction')) {
                $this->updateRoleSyncDirection($user, $request->sync_direction, $role);
            }

            $results = $this->googleCalendarService->syncUserEvents($user, $role);
            
            return response()->json([
                'message' => 'Events synced successfully',
                'results' => $results,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to sync events to Google Calendar', [
                'user_id' => $user->id,
                'role_id' => $role->id ?? null,
                'error' => $e->getMessage(),
            ]);

            return response()->json(['error' => 'Failed to sync events'], 500);
        }
    }

    /**
     * Sync all events to Google Calendar for the authenticated user across all their roles
     */
    public function syncAllEvents(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->google_token) {
            return response()->json(['error' => 'Google Calendar not connected'], 400);
        }

        try {
            // Ensure user has valid token before syncing
            if (!$this->googleCalendarService->ensureValidToken($user)) {
                return response()->json(['error' => 'Google Calendar token invalid and refresh failed'], 401);
            }

            $results = $this->googleCalendarService->syncAllUserEvents($user);
            
            return response()->json([
                'message' => 'Events synced successfully',
                'results' => $results,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to sync all events to Google Calendar', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json(['error' => 'Failed to sync events'], 500);
        }
    }

    /**
     * Sync a specific event to Google Calendar
     */
    public function syncEvent(Request $request, $eventId)
    {
        $user = Auth::user();
        
        if (!$user->google_token) {
            return response()->json(['error' => 'Google Calendar not connected'], 400);
        }

        try {
            $event = \App\Models\Event::findOrFail($eventId);
            
            // Check if user has permission to sync this event
            if (!$event->roles->contains(function ($role) use ($user) {
                return $role->users->contains($user);
            })) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            // Ensure user has valid token before syncing
            if (!$this->googleCalendarService->ensureValidToken($user)) {
                return response()->json(['error' => 'Google Calendar token invalid and refresh failed'], 401);
            }

            if ($event->google_event_id) {
                $googleEvent = $this->googleCalendarService->updateEvent($event, $event->google_event_id);
            } else {
                $googleEvent = $this->googleCalendarService->createEvent($event);
                if ($googleEvent) {
                    $event->update(['google_event_id' => $googleEvent->getId()]);
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
    public function unsyncEvent(Request $request, $eventId)
    {
        $user = Auth::user();
        
        if (!$user->google_token) {
            return response()->json(['error' => 'Google Calendar not connected'], 400);
        }

        try {
            $event = \App\Models\Event::findOrFail($eventId);
            
            // Check if user has permission to unsync this event
            if (!$event->roles->contains(function ($role) use ($user) {
                return $role->users->contains($user);
            })) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            if ($event->google_event_id) {
                // Ensure user has valid token before deleting
                if (!$this->googleCalendarService->ensureValidToken($user)) {
                    return response()->json(['error' => 'Google Calendar token invalid and refresh failed'], 401);
                }

                $this->googleCalendarService->deleteEvent($event->google_event_id);
                $event->update(['google_event_id' => null]);
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
     * Sync from Google Calendar to EventSchedule
     */
    public function syncFromGoogleCalendar(Request $request, $subdomain)
    {
        $user = Auth::user();
        
        if (!$user->google_token) {
            return response()->json(['error' => 'Google Calendar not connected'], 400);
        }

        try {
            $role = \App\Models\Role::subdomain($subdomain)->firstOrFail();
            
            // Check if user has permission to sync this role
            if (!$role->users->contains($user)) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            // If sync_direction is provided, update the role's sync_direction
            if ($request->has('sync_direction')) {
                $this->updateRoleSyncDirection($user, $request->sync_direction, $role);
            }

            // Ensure user has valid token before syncing
            if (!$this->googleCalendarService->ensureValidToken($user)) {
                return response()->json(['error' => 'Google Calendar token invalid and refresh failed'], 401);
            }

            $calendarId = $role->getGoogleCalendarId();
            $results = $this->googleCalendarService->syncFromGoogleCalendar($user, $role, $calendarId);

            return response()->json([
                'message' => 'Events synced from Google Calendar successfully',
                'results' => $results,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to sync from Google Calendar', [
                'user_id' => $user->id,
                'subdomain' => $subdomain,
                'error' => $e->getMessage(),
            ]);

            return response()->json(['error' => 'Failed to sync from Google Calendar'], 500);
        }
    }

    /**
     * Update role's sync direction and handle webhook management
     */
    private function updateRoleSyncDirection($user, $syncDirection, $role = null)
    {
        try {
            // If no specific role provided, get the first role for this user
            if (!$role) {
                $role = $user->roles()->first();
                if (!$role) {
                    return;
                }
            }

            $oldSyncDirection = $role->sync_direction;

            // Update sync direction
            $role->update(['sync_direction' => $syncDirection]);

            // Handle webhook management based on sync direction
            if ($syncDirection === 'from' || $syncDirection === 'both') {
                // Need webhook for syncing from Google
                if (!$role->hasActiveWebhook()) {
                    // Ensure user has valid token before creating webhook
                    if (!$this->googleCalendarService->ensureValidToken($user)) {
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
                        $this->googleCalendarService->deleteWebhook($role->google_webhook_id);
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
