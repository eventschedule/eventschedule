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
 * "Publish all" on a schedule's calendar review page: every event a calendar sync brought in as a
 * draft and that waits for nothing but a look. Not the ones held for meeting details or an email
 * thread, which are published one at a time. Off the request, in chunks: each publish pushes to
 * the connected calendars and fires a webhook, and a first read is hundreds of events.
 */
class PublishCalendarHolds implements ShouldQueue
{
    use Queueable;

    private const CHUNK = 50;

    public int $timeout = 600;

    public function __construct(public int $roleId, public ?int $userId) {}

    public function handle(EventLifecycleService $lifecycle): void
    {
        $role = Role::find($this->roleId);
        if (! $role || $role->is_deleted || ! CalendarArrival::tablesReady()) {
            return;
        }

        CalendarImportHold::where('role_id', $role->id)
            ->whereNull('decided_at')
            ->whereNull('was_visibility')
            ->where('reason', CalendarArrival::HOLD_REVIEW)
            ->with('event.roles')
            ->chunkById(self::CHUNK, function ($holds) use ($role, $lifecycle) {
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
            });
    }
}
