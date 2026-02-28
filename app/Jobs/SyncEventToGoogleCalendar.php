<?php

namespace App\Jobs;

use App\Models\Event;
use App\Models\Role;
use App\Services\GoogleCalendarService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SyncEventToGoogleCalendar implements ShouldQueue
{
    use Queueable;

    protected $event;

    protected $role;

    protected $action; // 'create', 'update', 'delete'

    public $deleteWhenMissingModels = true;

    /**
     * Create a new job instance.
     */
    public function __construct(Event $event, Role $role, string $action = 'create')
    {
        $this->event = $event;
        $this->role = $role;
        $this->action = $action;
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
     * Execute the job.
     */
    public function handle(GoogleCalendarService $googleCalendarService): void
    {
        try {
            $user = $this->role->user;

            if (! $user->google_token) {
                Log::warning('User does not have Google Calendar connected', [
                    'user_id' => $user->id,
                    'event_id' => $this->event->id,
                ]);

                return;
            }

            // Ensure user has valid token before syncing
            if (! $googleCalendarService->ensureValidToken($user)) {
                Log::error('Failed to refresh Google token for event sync', [
                    'user_id' => $user->id,
                    'event_id' => $this->event->id,
                ]);

                return;
            }

            // Set access token with fresh data
            $googleCalendarService->setAccessToken([
                'access_token' => $user->google_token,
                'refresh_token' => $user->google_refresh_token,
                'expires_in' => $this->calculateExpiresIn($user->google_token_expires_at),
            ]);

            switch ($this->action) {
                case 'create':
                    $this->createEvent($googleCalendarService);
                    break;
                case 'update':
                    $this->updateEvent($googleCalendarService);
                    break;
                case 'delete':
                    $this->deleteEvent($googleCalendarService);
                    break;
            }

        } catch (\Exception $e) {
            Log::error('Failed to sync event to Google Calendar', [
                'event_id' => $this->event->id,
                'user_id' => $this->role->user->id ?? null,
                'action' => $this->action,
                'error' => $e->getMessage(),
            ]);

            // Re-throw the exception to mark the job as failed
            throw $e;
        }
    }

    /**
     * Create event in Google Calendar
     */
    private function createEvent(GoogleCalendarService $googleCalendarService): void
    {
        $calendarId = $this->role->getGoogleCalendarId();
        $googleEvent = $googleCalendarService->createEvent($this->event, $this->role);

        if ($googleEvent) {
            $this->event->setGoogleEventIdForRole($this->role->id, $googleEvent->getId());

            Log::info('Event created in Google Calendar', [
                'event_id' => $this->event->id,
                'google_event_id' => $googleEvent->getId(),
                'calendar_id' => $this->role->getGoogleCalendarId(),
            ]);
        }
    }

    /**
     * Update event in Google Calendar
     */
    private function updateEvent(GoogleCalendarService $googleCalendarService): void
    {
        $googleEventId = $this->event->getGoogleEventIdForRole($this->role->id);

        if (! $googleEventId) {
            // If no Google event ID for this role, create a new event
            $this->createEvent($googleCalendarService);

            return;
        }

        $googleEvent = $googleCalendarService->updateEvent(
            $this->event,
            $googleEventId,
            $this->role
        );

        if ($googleEvent) {
            Log::info('Event updated in Google Calendar', [
                'event_id' => $this->event->id,
                'google_event_id' => $googleEventId,
            ]);
        }
    }

    /**
     * Delete event from Google Calendar
     */
    private function deleteEvent(GoogleCalendarService $googleCalendarService): void
    {
        $googleEventId = $this->event->getGoogleEventIdForRole($this->role->id);

        if (! $googleEventId) {
            return;
        }

        $success = $googleCalendarService->deleteEvent($googleEventId, $this->role->getGoogleCalendarId());

        if ($success) {
            $this->event->setGoogleEventIdForRole($this->role->id, null);

            Log::info('Event deleted from Google Calendar', [
                'event_id' => $this->event->id,
                'google_event_id' => $googleEventId,
            ]);
        }
    }
}
