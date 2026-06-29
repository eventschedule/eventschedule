<?php

namespace App\Jobs;

use App\Models\Event;
use App\Services\EventChangeNotifier;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class NotifyEventCancelled implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 60;

    public function __construct(
        protected int $eventId,
        protected ?string $note = null,
    ) {}

    public function handle(): void
    {
        // Soft-cancel retains the row, so the event reloads normally.
        $event = Event::with(['roles', 'user', 'tickets'])->find($this->eventId);

        if (! $event) {
            return;
        }

        $role = $event->getRoleWithEmailSettings();

        if (! $role || ! $role->hasEmailSettings()) {
            return;
        }

        EventChangeNotifier::notifyCancellation($event, $role, $this->note);

        $event->forceFill(['attendees_notified_at' => now()])->saveQuietly();
    }
}
