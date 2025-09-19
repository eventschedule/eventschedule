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
                ->with('success', 'Google Calendar connected successfully!');

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
            ->with('success', 'Google Calendar disconnected successfully.');
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

            $this->googleCalendarService->setAccessToken([
                'access_token' => $user->fresh()->google_token,
                'refresh_token' => $user->fresh()->google_refresh_token,
                'expires_in' => $this->calculateExpiresIn($user->fresh()->google_token_expires_at),
            ]);

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
            $this->googleCalendarService->setAccessToken([
                'access_token' => $user->fresh()->google_token,
                'refresh_token' => $user->fresh()->google_refresh_token,
                'expires_in' => $this->calculateExpiresIn($user->fresh()->google_token_expires_at),
            ]);

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
     * Sync all events to Google Calendar
     */
    public function syncEvents(Request $request)
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

            $results = $this->googleCalendarService->syncUserEvents($user);
            
            return response()->json([
                'message' => 'Events synced successfully',
                'results' => $results,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to sync events to Google Calendar', [
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

            $this->googleCalendarService->setAccessToken([
                'access_token' => $user->fresh()->google_token,
                'refresh_token' => $user->fresh()->google_refresh_token,
                'expires_in' => $this->calculateExpiresIn($user->fresh()->google_token_expires_at),
            ]);

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

                $this->googleCalendarService->setAccessToken([
                    'access_token' => $user->fresh()->google_token,
                    'refresh_token' => $user->fresh()->google_refresh_token,
                    'expires_in' => $user->fresh()->google_token_expires_at ? 
                        $user->fresh()->google_token_expires_at->diffInSeconds(now()) : 3600,
                ]);

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
     * Update sync direction for a role
     */
    public function updateSyncDirection(Request $request, $subdomain)
    {
        $user = Auth::user();
        
        if (!$user->google_token) {
            return response()->json(['error' => 'Google Calendar not connected'], 400);
        }

        $request->validate([
            'sync_direction' => 'required|in:to,from,both',
        ]);

        try {
            $role = \App\Models\Role::subdomain($subdomain)->firstOrFail();
            
            // Check if user has permission to update this role
            if (!$role->users->contains($user)) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $oldSyncDirection = $role->sync_direction;
            $newSyncDirection = $request->sync_direction;

            // Handle webhook management based on sync direction
            if ($newSyncDirection === 'from' || $newSyncDirection === 'both') {
                // Need webhook for syncing from Google
                if (!$role->hasActiveWebhook()) {
                    // Ensure user has valid token before creating webhook
                    if (!$this->googleCalendarService->ensureValidToken($user)) {
                        return response()->json(['error' => 'Google Calendar token invalid and refresh failed'], 401);
                    }

                    $this->googleCalendarService->setAccessToken([
                        'access_token' => $user->fresh()->google_token,
                        'refresh_token' => $user->fresh()->google_refresh_token,
                        'expires_in' => $user->fresh()->google_token_expires_at ? 
                            $user->fresh()->google_token_expires_at->diffInSeconds(now()) : 3600,
                    ]);

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
                        $this->googleCalendarService->setAccessToken([
                            'access_token' => $user->fresh()->google_token,
                            'refresh_token' => $user->fresh()->google_refresh_token,
                            'expires_in' => $user->fresh()->google_token_expires_at ? 
                                $user->fresh()->google_token_expires_at->diffInSeconds(now()) : 3600,
                        ]);

                        $this->googleCalendarService->deleteWebhook($role->google_webhook_id);
                    }
                }

                $role->update([
                    'google_webhook_id' => null,
                    'google_webhook_resource_id' => null,
                    'google_webhook_expires_at' => null,
                ]);
            }

            // Update sync direction
            $role->update([
                'sync_direction' => $newSyncDirection,
            ]);

            $message = match($newSyncDirection) {
                'to' => 'Sync to Google Calendar enabled',
                'from' => 'Sync from Google Calendar enabled',
                'both' => 'Bidirectional sync enabled',
                default => 'Sync disabled'
            };

            return response()->json([
                'message' => $message,
                'sync_direction' => $newSyncDirection,
                'sync_direction_label' => $role->getSyncDirectionLabel(),
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to update sync direction', [
                'user_id' => $user->id,
                'subdomain' => $subdomain,
                'sync_direction' => $request->sync_direction,
                'error' => $e->getMessage(),
            ]);

            return response()->json(['error' => 'Failed to update sync direction'], 500);
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

            // Ensure user has valid token before syncing
            if (!$this->googleCalendarService->ensureValidToken($user)) {
                return response()->json(['error' => 'Google Calendar token invalid and refresh failed'], 401);
            }

            $this->googleCalendarService->setAccessToken([
                'access_token' => $user->fresh()->google_token,
                'refresh_token' => $user->fresh()->google_refresh_token,
                'expires_in' => $this->calculateExpiresIn($user->fresh()->google_token_expires_at),
            ]);

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
