<?php

namespace App\Mail;

use App\Models\Event;
use App\Models\Role;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EventChanged extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  array  $changes  ['date' => ['old_starts_at','old_duration','old_timezone'], 'location' => ['variant','old_venue','new_venue']]
     */
    public function __construct(
        protected Event $event,
        protected ?Role $role,
        protected array $changes,
        protected string $eventUrl,
        protected ?string $note = null,
        protected string $icalUrl = '',
        protected ?string $recipientName = null,
    ) {}

    public function envelope(): Envelope
    {
        $fromAddress = config('mail.from.address');
        $fromName = config('mail.from.name');

        if ($this->role && $this->role->hasEmailSettings()) {
            $emailSettings = $this->role->getEmailSettings();
            if (! empty($emailSettings['from_address'])) {
                $fromAddress = $emailSettings['from_address'];
            }
            if (! empty($emailSettings['from_name'])) {
                $fromName = $emailSettings['from_name'];
            }
        }

        return new Envelope(
            subject: __('messages.event_changed_subject', ['event' => $this->event->name]),
            from: new Address($fromAddress, $fromName),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.event_changed',
            text: 'emails.event_changed_text',
            with: [
                'event' => $this->event,
                'role' => $this->role,
                'note' => $this->note,
                'recipientName' => $this->recipientName,
                'eventUrl' => $this->eventUrl,
                'icalUrl' => $this->icalUrl,
                'display' => $this->buildDisplay(),
            ],
        );
    }

    /**
     * Build the human-facing change summary at render time (locale + 24h preference applied here so
     * month names localize and times use the schedule's format). Old values come from the snapshot in
     * $changes; new values from the live event.
     */
    protected function buildDisplay(): array
    {
        $event = $this->event;
        $use24 = (bool) ($this->role?->use_24_hour_time);
        $display = [];

        // Only render a date block when the event now has a start time. The "Previously" side is
        // optional: an event that had no date before (old_starts_at null) shows only the new time.
        if (isset($this->changes['date']) && ! empty($event->starts_at)) {
            $newTz = $event->getEffectiveTimezone();
            $newStart = $this->toTz($event->starts_at, $newTz);
            $newDuration = (float) $event->duration;

            $oldRaw = $this->changes['date']['old_starts_at'] ?? null;
            $hasOld = ! empty($oldRaw);
            $oldStart = $hasOld ? $this->toTz($oldRaw, $this->changes['date']['old_timezone'] ?: $newTz) : null;
            $oldDuration = (float) ($this->changes['date']['old_duration'] ?? 0);

            $display['date'] = [
                'old' => $hasOld ? $this->formatOccurrence($oldStart, $oldDuration, $use24) : null,
                'new' => $this->formatOccurrence($newStart, $newDuration, $use24),
                // The same values split into date and time, for the HTML part: each is isolated on
                // its own there, or an RTL line reorders "8:00 PM - 11:00 PM" around the month name.
                'old_parts' => $hasOld ? $this->occurrenceParts($oldStart, $oldDuration, $use24) : null,
                'new_parts' => $this->occurrenceParts($newStart, $newDuration, $use24),
                'old_tz' => $hasOld ? $oldStart->format('T') : '',
                'new_tz' => $newStart->format('T'),
                'delta' => $hasOld ? $this->formatDelta($oldStart, $oldDuration, $newStart, $newDuration) : null,
            ];
        }

        if (isset($this->changes['location'])) {
            $display['location'] = $this->changes['location'];
        }

        return $display;
    }

    protected function toTz(string $startsAt, string $tz): Carbon
    {
        $format = strlen($startsAt) === 10 ? 'Y-m-d' : 'Y-m-d H:i:s';
        $dt = Carbon::createFromFormat($format, $startsAt, 'UTC');

        return strlen($startsAt) === 10 ? $dt->startOfDay()->setTimezone($tz) : $dt->setTimezone($tz);
    }

    protected function formatOccurrence(Carbon $start, float $duration, bool $use24): string
    {
        $parts = $this->occurrenceParts($start, $duration, $use24);

        return $parts['time'] === null ? $parts['date'] : $parts['date'].', '.$parts['time'];
    }

    /**
     * ['date' => ..., 'time' => ...|null]. Multi-day (duration >= 24h) is a date range with no time;
     * single-day is the date plus a time range.
     */
    protected function occurrenceParts(Carbon $start, float $duration, bool $use24): array
    {
        $dateFmt = 'F j, Y';
        $timeFmt = $use24 ? 'H:i' : 'g:i A';

        if ($duration >= 24) {
            $end = $start->copy()->addMinutes(Event::durationHoursToMinutes($duration));

            return ['date' => $start->translatedFormat($dateFmt).' - '.$end->translatedFormat($dateFmt), 'time' => null];
        }

        $time = $start->translatedFormat($timeFmt);

        if ($duration > 0) {
            $end = $start->copy()->addMinutes(Event::durationHoursToMinutes($duration));
            $time .= ' - '.$end->translatedFormat($timeFmt);
        }

        return ['date' => $start->translatedFormat($dateFmt), 'time' => $time];
    }

    protected function formatDelta(Carbon $oldStart, float $oldDuration, Carbon $newStart, float $newDuration): ?string
    {
        $startDelta = $oldStart->copy()->startOfDay()->diffInDays($newStart->copy()->startOfDay(), false);

        if ($startDelta > 0) {
            return __('messages.event_changed_rescheduled_later', ['count' => $startDelta]);
        }
        if ($startDelta < 0) {
            return __('messages.event_changed_rescheduled_earlier', ['count' => abs($startDelta)]);
        }

        // Same start, only the end moved.
        if ($oldDuration != $newDuration) {
            $oldEnd = $oldStart->copy()->addMinutes(Event::durationHoursToMinutes($oldDuration));
            $newEnd = $newStart->copy()->addMinutes(Event::durationHoursToMinutes($newDuration));
            $endDelta = $oldEnd->startOfDay()->diffInDays($newEnd->startOfDay(), false);

            if ($endDelta > 0) {
                return __('messages.event_changed_ends_later', ['count' => $endDelta]);
            }
            if ($newDuration > $oldDuration) {
                return __('messages.event_changed_runs_longer');
            }
        }

        return null;
    }
}
