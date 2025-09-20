<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Services\GoogleCalendarService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class GoogleCalendarWebhookController extends Controller
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
     * Handle Google Calendar webhook notifications
     */
    public function handle(Request $request)
    {
        \Log::info('Google Calendar webhook received');

        try {
            // Verify the webhook token
            $token = $request->header('X-Goog-Channel-Token');

            if ($token !== env('GOOGLE_WEBHOOK_SECRET', 'default_secret')) {
                Log::warning('Invalid Google Calendar webhook token', [
                    'token' => $token,
                    'ip' => $request->ip(),
                ]);
                return response('Unauthorized', 401);
            }

            $channelId = $request->header('X-Goog-Channel-ID');
            $resourceId = $request->header('X-Goog-Resource-ID');
            $resourceState = $request->header('X-Goog-Resource-State');

            Log::info('Google Calendar webhook received', [
                'channel_id' => $channelId,
                'resource_id' => $resourceId,
                'resource_state' => $resourceState,
            ]);

            // Find the role with this webhook
            $role = Role::where('google_webhook_id', $channelId)
                       ->where('google_webhook_resource_id', $resourceId)
                       ->first();

            if (!$role) {
                Log::warning('No role found for webhook', [
                    'channel_id' => $channelId,
                    'resource_id' => $resourceId,
                ]);
                return response('Role not found', 404);
            }

            // Check if sync from Google is enabled
            if (!$role->syncsFromGoogle()) {
                Log::info('Sync from Google not enabled for role', [
                    'role_id' => $role->id,
                    'sync_direction' => $role->sync_direction,
                ]);
                return response('Sync from Google not enabled', 200);
            }

            // Get the user for this role
            $user = $role->users()->first();
            if (!$user || !$user->google_token) {
                Log::warning('No user with Google token found for role', [
                    'role_id' => $role->id,
                ]);
                return response('User not found or not connected', 404);
            }

            // Ensure user has valid Google token (with automatic refresh)
            if (!$this->googleCalendarService->ensureValidToken($user)) {
                Log::error('Failed to refresh Google token for webhook', [
                    'role_id' => $role->id,
                    'user_id' => $user->id,
                ]);
                return response('User token invalid and refresh failed', 401);
            }

            // Sync from Google Calendar to EventSchedule
            $this->syncFromGoogleCalendar($role, $user);

            return response('OK', 200);

        } catch (\Exception $e) {
            Log::error('Google Calendar webhook error', [
                'error' => $e->getMessage(),
                'request' => $request->all(),
            ]);

            return response('Internal Server Error', 500);
        }
    }

    /**
     * Sync events from Google Calendar to EventSchedule
     */
    private function syncFromGoogleCalendar(Role $role, $user)
    {
        try {
            // Ensure token is valid before proceeding
            if (!$this->googleCalendarService->ensureValidToken($user)) {
                Log::error('Token validation failed during webhook sync', [
                    'role_id' => $role->id,
                    'user_id' => $user->id,
                ]);
                return;
            }

            // Set the validated token
            $this->googleCalendarService->setAccessToken([
                'access_token' => $user->fresh()->google_token,
                'refresh_token' => $user->fresh()->google_refresh_token,
                'expires_in' => $this->calculateExpiresIn($user->fresh()->google_token_expires_at),
            ]);

            $calendarId = $role->getGoogleCalendarId();
            $results = $this->googleCalendarService->syncFromGoogleCalendar($user, $role, $calendarId);

            Log::info('Bidirectional sync completed from Google Calendar', [
                'role_id' => $role->id,
                'calendar_id' => $calendarId,
                'results' => $results,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to sync from Google Calendar via webhook', [
                'role_id' => $role->id,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle webhook verification (GET request)
     */
    public function verify(Request $request)
    {
        $challenge = $request->get('challenge');
        
        if ($challenge) {
            Log::info('Google Calendar webhook verification', [
                'challenge' => $challenge,
            ]);
            return response($challenge, 200);
        }

        return response('Bad Request', 400);
    }
}