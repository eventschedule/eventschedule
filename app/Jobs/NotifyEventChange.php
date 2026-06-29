<?php

namespace App\Jobs;

use App\Models\Event;
use App\Services\EventChangeNotifier;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class NotifyEventChange implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 60;

    /**
     * @param  array  $changes  ['date' => [...], 'location' => [...]] captured at save time
     */
    public function __construct(
        protected int $eventId,
        protected array $changes,
        protected ?string $note = null,
    ) {}

    public function handle(): void
    {
        $event = Event::with(['roles', 'user', 'tickets'])->find($this->eventId);

        // Suppress while cancelled or as a draft: cancellation has its own notice, and a draft is not
        // yet public.
        if (! $event || $event->is_cancelled || $event->is_draft) {
            return;
        }

        $role = $event->getRoleWithEmailSettings();

        if (! $role || ! $role->hasEmailSettings()) {
            return;
        }

        EventChangeNotifier::notifyChange($event, $role, $this->changes, $this->note);

        $event->forceFill(['attendees_notified_at' => now()])->saveQuietly();
    }
}
