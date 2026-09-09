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

        // getRoleWithEmailSettings() prefers a schedule with its own SMTP and otherwise falls back
        // to $venue ?: $firstRole, so this is non-null whenever the event is on any schedule.
        $role = $event->getRoleWithEmailSettings() ?: $event->creatorRole;

        if (! $role) {
            return;
        }

        // NO hasEmailSettings() bail here, and that is the point.
        //
        // It used to sit exactly here, and it is why the interest list never heard about a change
        // or a cancellation: notifyChange()/notifyCancellation() call notifyInterested() from
        // INSIDE, so returning here skipped both audiences rather than the one the gate is for.
        // Widening the two dispatch gates did not help - this was the wall behind them.
        //
        // The notifier applies the SMTP gate to the SALES half itself, which is where it belongs: a
        // buyer got their receipt from the schedule's own address, so a platform-branded follow-up
        // would be a surprise. Somebody who left an address on the schedule's public page asked US,
        // and was told they would hear if anything changed.
        //
        // Nothing is dispatched at all unless EventChangeNotifier::hasAnyoneToTell() found someone
        // mailable, so reaching this line means at least one message is going out and the stamp
        // below is honest.
        $queued = EventChangeNotifier::notifyChange($event, $role, $this->changes, $this->note);

        // Stamped on the RESULT, not unconditionally. attendees_notified_at drives the "recently
        // notified" caption in the editor, and the notifier can decline for reasons the dispatch
        // gate cannot see - the 50-recipient ceiling in canSendAudienceMail(), a schedule that lost
        // its subdomain, an event hidden since capture. Stamping regardless told an owner who had
        // notified nobody that they had.
        if ($queued > 0) {
            $event->forceFill(['attendees_notified_at' => now()])->saveQuietly();
        }
    }
}
