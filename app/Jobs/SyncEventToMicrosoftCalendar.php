<?php

namespace App\Jobs;

use App\Models\Event;
use App\Models\MicrosoftCalendarSync;
use App\Models\Role;
use App\Models\User;
use App\Services\MicrosoftCalendarService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SyncEventToMicrosoftCalendar implements ShouldQueue
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
     * Get the user whose credentials should be used for this sync
     */
    private function getSyncUser(): User
    {
        return $this->user ?? $this->role->user;
    }

    /**
     * Get the calendar ID to sync with (null = the user's default calendar)
     */
    private function getSyncCalendarId(): ?string
    {
        return $this->calendarId ?? $this->role->getMicrosoftCalendarId();
    }

    /**
     * Execute the job.
     */
    public function handle(MicrosoftCalendarService $microsoftCalendarService): void
    {
        try {
            $user = $this->getSyncUser();

            if (! $user->microsoft_token) {
                Log::warning('User does not have Outlook Calendar connected', [
                    'user_id' => $user->id,
                    'event_id' => $this->event->id,
                ]);

                return;
            }

            // Ensure user has a valid token before syncing (also binds the service to this user)
            if (! $microsoftCalendarService->ensureValidToken($user)) {
                Log::error('Failed to refresh Microsoft token for event sync', [
                    'user_id' => $user->id,
                    'event_id' => $this->event->id,
                ]);

                return;
            }

            switch ($this->action) {
                case 'create':
                    $this->createEvent($microsoftCalendarService);
                    break;
                case 'update':
                    $this->updateEvent($microsoftCalendarService);
                    break;
                case 'delete':
                    $this->deleteEvent($microsoftCalendarService);
                    break;
            }
        } catch (\Exception $e) {
            Log::error('Failed to sync event to Microsoft Calendar', [
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
     * Get the existing MicrosoftCalendarSync row for this user/event/role
     */
    private function getCalendarSync(): ?MicrosoftCalendarSync
    {
        return MicrosoftCalendarSync::where('user_id', $this->getSyncUser()->id)
            ->where('event_id', $this->event->id)
            ->where('role_id', $this->role->id)
            ->first();
    }

    /**
     * Store the Microsoft event ID and the calendar it lives on for this sync
     */
    private function setMicrosoftEventId(?string $microsoftEventId, ?string $calendarId): void
    {
        MicrosoftCalendarSync::updateOrCreate(
            [
                'user_id' => $this->getSyncUser()->id,
                'event_id' => $this->event->id,
                'role_id' => $this->role->id,
            ],
            [
                'microsoft_event_id' => $microsoftEventId,
                'microsoft_calendar_id' => $calendarId,
            ]
        );
    }

    /**
     * Create event in Outlook
     */
    private function createEvent(MicrosoftCalendarService $microsoftCalendarService): void
    {
        $calendarId = $this->getSyncCalendarId();
        $microsoftEvent = $microsoftCalendarService->createEvent($this->event, $this->role, $calendarId);

        if ($microsoftEvent && ! empty($microsoftEvent['id'])) {
            $this->setMicrosoftEventId($microsoftEvent['id'], $calendarId);
        }
    }

    /**
     * Update event in Outlook — operates on the calendar where the event was
     * originally created, falling back to the current pivot for legacy rows.
     */
    private function updateEvent(MicrosoftCalendarService $microsoftCalendarService): void
    {
        $sync = $this->getCalendarSync();
        $microsoftEventId = $sync?->microsoft_event_id;

        if (! $microsoftEventId) {
            $this->createEvent($microsoftCalendarService);

            return;
        }

        $calendarId = $sync->microsoft_calendar_id ?: $this->getSyncCalendarId();

        $microsoftCalendarService->updateEvent(
            $this->event,
            $microsoftEventId,
            $this->role,
            $calendarId
        );
    }

    /**
     * Delete event from Outlook — operates on the calendar where the event was
     * originally created.
     */
    private function deleteEvent(MicrosoftCalendarService $microsoftCalendarService): void
    {
        $sync = $this->getCalendarSync();
        $microsoftEventId = $sync?->microsoft_event_id;

        if (! $microsoftEventId) {
            return;
        }

        $calendarId = $sync->microsoft_calendar_id ?: $this->getSyncCalendarId();

        $success = $microsoftCalendarService->deleteEvent($microsoftEventId, $calendarId, $this->role->id);

        if ($success) {
            $sync->delete();
        }
    }
}
