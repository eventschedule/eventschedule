<?php

namespace App\Jobs;

use App\Models\CalendarImportHold;
use App\Models\Role;
use App\Services\CalendarArrival;
use App\Services\EventLifecycleService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

/**
 * One chunk of "Publish all" on a schedule's calendar review page (CalendarReviewController):
 * events a calendar sync brought in as drafts that wait for nothing but a look. Not the ones held
 * for meeting details or an email thread, which are published one at a time. A job per chunk, so
 * no one job holds the queue for long: each publish pushes to the connected calendars and fires a
 * webhook, and the queue is drained between jobs.
 */
class PublishCalendarHolds implements ShouldQueue
{
    use Queueable;

    public const CHUNK = 50;

    /** @param  list<int>  $eventIds */
    public function __construct(public int $roleId, public ?int $userId, public array $eventIds) {}

    public function handle(EventLifecycleService $lifecycle): void
    {
        $role = Role::find($this->roleId);
        if (! $role || $role->is_deleted || ! CalendarArrival::tablesReady()) {
            return;
        }

        $holds = CalendarImportHold::where('role_id', $role->id)
            ->whereIn('event_id', $this->eventIds)
            ->whereNull('decided_at')
            ->where('was_public', false)
            ->where('reason', CalendarArrival::HOLD_REVIEW)
            ->with('event.roles')
            ->get();

        foreach ($holds as $hold) {
            $event = $hold->event;
            if (! $event || (int) $event->creator_role_id !== (int) $role->id || ! $event->is_draft) {
                continue;
            }

            try {
                $lifecycle->publish($event, $this->userId, $role);
            } catch (\Throwable $e) {
                Log::warning('Publish all: an event could not be published', ['event_id' => $event->id, 'error' => $e->getMessage()]);
            }
        }
    }
}
