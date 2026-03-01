<?php

namespace App\Jobs;

use App\Mail\WaitlistNotification;
use App\Models\Event;
use App\Models\TicketWaitlist;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;

class NotifyWaitlist implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 60;

    protected int $eventId;

    protected string $eventDate;

    public function __construct(int $eventId, string $eventDate)
    {
        $this->eventId = $eventId;
        $this->eventDate = $eventDate;
    }

    public function handle(): void
    {
        $event = Event::with('tickets', 'roles')->find($this->eventId);

        if (! $event) {
            return;
        }

        // Check if tickets are actually available
        if ($event->allTicketsSoldOut($this->eventDate)) {
            return;
        }

        // Find the oldest waiting entry and atomically mark as notified
        $entry = DB::transaction(function () {
            $entry = TicketWaitlist::where('event_id', $this->eventId)
                ->where('event_date', $this->eventDate)
                ->where('status', 'waiting')
                ->orderBy('created_at', 'asc')
                ->lockForUpdate()
                ->first();

            if (! $entry) {
                return null;
            }

            $entry->update([
                'status' => 'notified',
                'notified_at' => now(),
                'expires_at' => now()->addHours(24),
            ]);

            return $entry;
        });

        if (! $entry) {
            return;
        }

        $role = $event->getRoleWithEmailSettings();

        $mailable = new WaitlistNotification($entry, $event, $role);

        SendQueuedEmail::dispatch(
            $mailable,
            $entry->email,
            $role?->id,
            $entry->locale
        );
    }
}
