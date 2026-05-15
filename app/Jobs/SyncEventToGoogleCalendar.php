<?php

namespace App\Jobs;

use App\Models\CalendarSync;
use App\Models\Event;
use App\Models\Role;
use App\Models\User;
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

    protected $user; // Optional: specific user (defaults to role owner)

    protected $calendarId; // Optional: specific calendar (defaults to owner's calendar)

    public $deleteWhenMissingModels = true;

    /**
     * Create a new job instance.
     */
    public function __construct(Event $event, Role $role, string $action = 'create', ?User $user = null, ?string $calendarId = null)
    {
        $this->event = $event;
        $this->role = $role;
        $this->action = $action;
        $this->user = $user;
        $this->calendarId = $calendarId;
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
     * Get the user whose credentials should be used for this sync
     */
    private function getSyncUser(): User
    {
        return $this->user ?? $this->role->user;
    }

    /**
     * Get the calendar ID to sync with
     */
    private function getSyncCalendarId(): string
    {
        return $this->calendarId ?? $this->role->getGoogleCalendarId();
    }

    /**
     * Execute the job.
     */
    public function handle(GoogleCalendarService $googleCalendarService): void
    {
        try {
            $user = $this->getSyncUser();

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
                'user_id' => $this->getSyncUser()?->id,
                'action' => $this->action,
                'error' => $e->getMessage(),
            ]);

            // Re-throw the exception to mark the job as failed
            throw $e;
        }
    }

    /**
     * Get the existing CalendarSync row for this user/event/role
     */
    private function getCalendarSync(): ?CalendarSync
    {
        return CalendarSync::where('user_id', $this->getSyncUser()->id)
            ->where('event_id', $this->event->id)
            ->where('role_id', $this->role->id)
            ->first();
    }

    /**
     * Get the stored Google event ID for this sync
     */
    private function getGoogleEventId(): ?string
    {
        return $this->getCalendarSync()?->google_event_id;
    }

    /**
     * Store the Google event ID and the calendar it lives on for this sync
     */
    private function setGoogleEventId(?string $googleEventId, string $calendarId): void
    {
        CalendarSync::updateOrCreate(
            [
                'user_id' => $this->getSyncUser()->id,
                'event_id' => $this->event->id,
                'role_id' => $this->role->id,
            ],
            [
                'google_event_id' => $googleEventId,
                'google_calendar_id' => $calendarId,
            ]
        );
    }

    /**
     * Create event in Google Calendar
     */
    private function createEvent(GoogleCalendarService $googleCalendarService): void
    {
        $calendarId = $this->getSyncCalendarId();
        $googleEvent = $googleCalendarService->createEvent($this->event, $this->role, $calendarId);

        if ($googleEvent) {
            $this->setGoogleEventId($googleEvent->getId(), $calendarId);
        }
    }

    /**
     * Update event in Google Calendar — operates on the calendar where the
     * event was originally created, falling back to the current pivot only
     * for legacy rows where the calendar wasn't recorded.
     */
    private function updateEvent(GoogleCalendarService $googleCalendarService): void
    {
        $sync = $this->getCalendarSync();
        $googleEventId = $sync?->google_event_id;

        if (! $googleEventId) {
            $this->createEvent($googleCalendarService);

            return;
        }

        $calendarId = $sync->google_calendar_id ?: $this->getSyncCalendarId();

        $googleCalendarService->updateEvent(
            $this->event,
            $googleEventId,
            $this->role,
            $calendarId
        );
    }

    /**
     * Delete event from Google Calendar — operates on the calendar where the
     * event was originally created.
     */
    private function deleteEvent(GoogleCalendarService $googleCalendarService): void
    {
        $sync = $this->getCalendarSync();
        $googleEventId = $sync?->google_event_id;

        if (! $googleEventId) {
            return;
        }

        $calendarId = $sync->google_calendar_id ?: $this->getSyncCalendarId();

        $success = $googleCalendarService->deleteEvent($googleEventId, $calendarId, $this->role->id);

        if ($success) {
            $sync->delete();
        }
    }
}
