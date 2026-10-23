<?php

namespace App\Jobs;

use App\Models\Event;
use App\Models\Role;
use App\Services\CalDAVService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SyncEventToCalDAV implements ShouldQueue
{
    use Queueable;

    public $deleteWhenMissingModels = true;

    protected $event;

    protected $role;

    protected $action; // 'create', 'update', 'delete', 'cancel' (a delete that leaves the owner's original)

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
     * Execute the job.
     */
    public function handle(CalDAVService $calDAVService): void
    {
        try {
            if (! $this->role->hasCalDAVSettings()) {
                Log::warning('Role does not have CalDAV configured', [
                    'role_id' => $this->role->id,
                    'event_id' => $this->event->id,
                ]);

                return;
            }

            switch ($this->action) {
                case 'create':
                    $this->createEvent($calDAVService);
                    break;
                case 'update':
                    $this->updateEvent($calDAVService);
                    break;
                case 'delete':
                case 'cancel':
                    $this->deleteEvent($calDAVService);
                    break;
            }
        } catch (\Exception $e) {
            Log::error('Failed to sync event to CalDAV', [
                'event_id' => $this->event->id,
                'role_id' => $this->role->id,
                'action' => $this->action,
                'error' => $e->getMessage(),
            ]);

            // Re-throw the exception to mark the job as failed
            throw $e;
        }
    }

    /**
     * Create event in CalDAV
     */
    private function createEvent(CalDAVService $calDAVService): void
    {
        // Already on that server: read in from it by the sync, or pushed there before. Publishing
        // a draft the sync brought in used to make a second copy beside the owner's own entry.
        if ($this->event->getCalDAVEventUidForRole($this->role->id)) {
            return;
        }

        $uid = $calDAVService->createEvent($this->event, $this->role);

        if ($uid) {
            $this->event->setCalDAVEventUidForRole($this->role->id, $uid);
        } else {
            Log::warning('Failed to create event in CalDAV (no UID returned)', [
                'event_id' => $this->event->id,
                'role_id' => $this->role->id,
                'event_name' => $this->event->name,
                'has_start_date' => ! empty($this->event->starts_at),
            ]);
        }
    }

    /**
     * Update event in CalDAV
     */
    private function updateEvent(CalDAVService $calDAVService): void
    {
        $uid = $this->event->getCalDAVEventUidForRole($this->role->id);

        if (! $uid) {
            // If no UID for this role, create a new event
            $this->createEvent($calDAVService);

            return;
        }

        $success = $calDAVService->updateEvent($this->event, $uid, $this->role);

        if (! $success) {
            Log::warning('Failed to update event in CalDAV', [
                'event_id' => $this->event->id,
                'role_id' => $this->role->id,
                'uid' => $uid,
            ]);
        }
    }

    /**
     * Delete event from CalDAV
     */
    private function deleteEvent(CalDAVService $calDAVService): void
    {
        $uid = $this->event->getCalDAVEventUidForRole($this->role->id);

        // The owner's own entry, which the sync read this event in from, belongs to their calendar:
        // hiding the event here (a draft) or cancelling it (which can be undone) leaves it, and
        // deleting the event takes it out only while the schedule pushes its changes to that server.
        $ownersOriginal = $this->event->cameFromCalendarSync(Event::IMPORT_CALDAV, $this->role)
            && ($this->event->is_draft || $this->action === 'cancel' || ! $this->role->syncsToCalDAV());

        if (! $uid || $ownersOriginal) {
            return;
        }

        $success = $calDAVService->deleteEvent($uid, $this->role);

        if ($success) {
            $this->event->setCalDAVEventUidForRole($this->role->id, null);
        } else {
            Log::warning('Failed to delete event from CalDAV', [
                'event_id' => $this->event->id,
                'role_id' => $this->role->id,
                'uid' => $uid,
            ]);
        }
    }
}
