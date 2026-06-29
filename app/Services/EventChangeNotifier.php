<?php

namespace App\Services;

use App\Jobs\SendQueuedEmail;
use App\Mail\EventCancelled;
use App\Mail\EventChanged;
use App\Models\Event;
use App\Models\Role;

/**
 * Sends change / cancellation notifications to an event's registered attendees (paid sales, including
 * free RSVPs). Shared by the queued jobs and reusable by any future caller (e.g. the API). Email is the
 * channel that requires the schedule's own SMTP settings; push mirrors it additively.
 */
class EventChangeNotifier
{
    public static function notifyChange(Event $event, ?Role $role, array $changes, ?string $note = null): void
    {
        if (! $role || ! $role->hasEmailSettings()) {
            return;
        }

        $locale = $role->language_code ?: app()->getLocale();

        self::eachRecipient($event, function ($sale) use ($event, $role, $changes, $note, $locale) {
            $eventUrl = $event->getGuestUrl(false, $sale->event_date, true);
            $icalUrl = $event->getAppleCalendarUrl($sale->event_date);

            SendQueuedEmail::dispatch(
                new EventChanged($event, $role, $changes, $eventUrl, $note, $icalUrl, $sale->name),
                $sale->email,
                $role->id,
                $locale
            );

            // Push body is intentionally generic: never the join link or the organizer note.
            OneSignalService::pushToGuestEmail($sale->email, $locale, [
                'title_key' => 'messages.push_event_changed_title',
                'body_key' => 'messages.push_event_changed_body',
                'body_params' => ['event' => $event->name],
                'url' => $eventUrl,
                'options' => ['icon' => $role->profile_image_url],
            ], $role);
        });
    }

    public static function notifyCancellation(Event $event, ?Role $role, ?string $note = null): void
    {
        if (! $role || ! $role->hasEmailSettings()) {
            return;
        }

        $locale = $role->language_code ?: app()->getLocale();

        self::eachRecipient($event, function ($sale) use ($event, $role, $note, $locale) {
            $eventUrl = $event->getGuestUrl(false, $sale->event_date, true);

            SendQueuedEmail::dispatch(
                new EventCancelled($event, $role, $eventUrl, $note, $sale->name),
                $sale->email,
                $role->id,
                $locale
            );

            OneSignalService::pushToGuestEmail($sale->email, $locale, [
                'title_key' => 'messages.push_event_cancelled_title',
                'body_key' => 'messages.push_event_cancelled_body',
                'body_params' => ['event' => $event->name],
                'url' => $eventUrl,
                'options' => ['icon' => $role->profile_image_url],
            ], $role);
        });
    }

    /** Distinct count of attendees that would be notified (drives the confirm dialog count). */
    public static function recipientCount(Event $event): int
    {
        return self::baseQuery($event)->distinct()->count('email');
    }

    public static function hasRecipients(Event $event): bool
    {
        return self::baseQuery($event)->exists();
    }

    /** Iterate paid attendees once per distinct (lowercased) email, scale-safe via chunking. */
    protected static function eachRecipient(Event $event, callable $callback): void
    {
        $seen = [];

        self::baseQuery($event)->orderBy('id')->chunkById(200, function ($sales) use (&$seen, $callback) {
            foreach ($sales as $sale) {
                $key = strtolower(trim((string) $sale->email));
                if ($key === '' || isset($seen[$key])) {
                    continue;
                }
                $seen[$key] = true;
                $callback($sale);
            }
        });
    }

    protected static function baseQuery(Event $event)
    {
        $isRecurring = (bool) $event->days_of_week;

        return $event->sales()
            ->where('status', 'paid')
            ->excludeTestEmails()
            ->when($isRecurring, fn ($q) => $q->whereDate('event_date', '>=', now()->toDateString()));
    }
}
