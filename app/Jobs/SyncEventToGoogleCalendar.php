<?php

namespace App\Jobs;

use App\Models\Event;
use App\Models\User;
use App\Services\GoogleCalendarService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SyncEventToGoogleCalendar implements ShouldQueue
{
    use Queueable;

    protected $event;
    protected $user;
    protected $action; // 'create', 'update', 'delete'
    protected $calendarId;

    /**
     * Create a new job instance.
     */
    public function __construct(Event $event, User $user, string $action = 'create', string $calendarId = 'primary')
    {
        $this->event = $event;
        $this->user = $user;
        $this->action = $action;
        $this->calendarId = $calendarId;
    }

    /**
     * Execute the job.
     */
    public function handle(GoogleCalendarService $googleCalendarService): void
    {
        try {
            if (!$this->user->google_token) {
                Log::warning('User does not have Google Calendar connected', [
                    'user_id' => $this->user->id,
                    'event_id' => $this->event->id,
                ]);
                return;
            }

            // Ensure user has valid token before syncing
            if (!$googleCalendarService->ensureValidToken($this->user)) {
                Log::error('Failed to refresh Google token for event sync', [
                    'user_id' => $this->user->id,
                    'event_id' => $this->event->id,
                ]);
                return;
            }

            // Set access token with fresh data
            $googleCalendarService->setAccessToken([
                'access_token' => $this->user->fresh()->google_token,
                'refresh_token' => $this->user->fresh()->google_refresh_token,
                'expires_in' => $this->user->fresh()->google_token_expires_at ? 
                    $this->user->fresh()->google_token_expires_at->diffInSeconds(now()) : 3600,
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
                'user_id' => $this->user->id,
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
        $googleEvent = $googleCalendarService->createEvent($this->event, $this->calendarId);
        
        if ($googleEvent) {
            $this->event->update([
                'google_event_id' => $googleEvent->getId(),
            ]);
            
            Log::info('Event created in Google Calendar', [
                'event_id' => $this->event->id,
                'google_event_id' => $googleEvent->getId(),
                'calendar_id' => $this->calendarId,
            ]);
        }
    }

    /**
     * Update event in Google Calendar
     */
    private function updateEvent(GoogleCalendarService $googleCalendarService): void
    {
        if (!$this->event->google_event_id) {
            // If no Google event ID, create a new event
            $this->createEvent($googleCalendarService);
            return;
        }

        $googleEvent = $googleCalendarService->updateEvent(
            $this->event, 
            $this->event->google_event_id,
            $this->calendarId
        );
        
        if ($googleEvent) {
            Log::info('Event updated in Google Calendar', [
                'event_id' => $this->event->id,
                'google_event_id' => $this->event->google_event_id,
            ]);
        }
    }

    /**
     * Delete event from Google Calendar
     */
    private function deleteEvent(GoogleCalendarService $googleCalendarService): void
    {
        if (!$this->event->google_event_id) {
            return;
        }

        $success = $googleCalendarService->deleteEvent($this->event->google_event_id, $this->calendarId);
        
        if ($success) {
            $this->event->update([
                'google_event_id' => null,
            ]);
            
            Log::info('Event deleted from Google Calendar', [
                'event_id' => $this->event->id,
                'google_event_id' => $this->event->google_event_id,
            ]);
        }
    }
}
