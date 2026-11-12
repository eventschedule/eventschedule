<?php

namespace App\Utils;

use App\Models\Event;
use App\Models\Role;
use App\Models\Sale;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Routing\Exceptions\UrlGenerationException;

/**
 * The calendar invitation a ticket buyer is mailed: one entry per person per occurrence, attached to
 * the ticket mail as a REQUEST, to the change mail as a REQUEST with the event's bumped SEQUENCE (so
 * the entry moves), and to the cancellation mail as a CANCEL (so it comes off their calendar).
 *
 * The UID is the event, the buyer's address and, for a series, the date. Never the sale: the change
 * and cancellation mails go out once per ADDRESS (EventChangeNotifier::eachRecipient()), so a UID per
 * sale would leave a second purchase's entry behind. And never the date of a one-off event: moving
 * one rewrites sales.event_date (Event's saving cascade), and the update would then be a new entry
 * beside the old one. The address is in the UID only as an HMAC, since a calendar can be shared.
 *
 * The entry's notes carry the ticket link, as the mail does. The online link does not ride in it:
 * the public feed never shows it either, and the ticket page is where a buyer finds it.
 */
class TicketInvite
{
    /** A series buyer holding more dates than this is sent this many, nearest first. */
    public const MAX_DATES = 10;

    /**
     * The invite for one sale, or null when the sale has nothing to put on a calendar: an
     * appointment (those have their own invite), a pass (it is for many dates, not one), an event
     * with no date, or an occurrence that is not one of the event's own.
     */
    public static function forSale(Sale $sale, Event $event, ?Role $role, string $method = 'REQUEST'): ?string
    {
        if (! self::applies($sale, $event, $method)) {
            return null;
        }

        $series = (bool) $event->days_of_week;
        $date = $series ? (string) $sale->event_date : null;
        $domain = parse_url(config('app.url'), PHP_URL_HOST) ?: 'eventschedule.com';

        $startAt = $series ? $event->occurrenceStartUtc($date) : $event->getStartDateTime();
        $duration = $event->duration > 0 ? $event->duration : 2;
        $endAt = $startAt->copy()->addMinutes(Event::durationHoursToMinutes($duration));

        // Named, and linked, in the language the guest bought in (Sale::guestLanguage()); its
        // notes are in the mail's own locale already.
        $lang = $sale->linkLanguage($role, $event);
        $ticketUrl = Sale::withLanguage(canonical_url(route('ticket.view', [
            'event_id' => UrlUtils::encodeId($sale->event_id),
            'secret' => $sale->secret,
        ], false)), $lang);
        $eventUrl = '';
        try {
            $eventUrl = Sale::withLanguage((string) ($event->getGuestUrl($role?->subdomain ?? false, $date, true) ?: ''), $lang);
        } catch (UrlGenerationException $e) {
            report($e);
        }

        $notes = __('messages.view_ticket').': '.$ticketUrl;
        if ($eventUrl !== '') {
            $notes .= "\n".__('messages.view_event').': '.$eventUrl;
        }

        $location = $event->venue ? trim((string) $event->venue->bestAddress()) : '';
        [$organizerEmail, $organizerName] = IcsUtils::organizer($role);
        $cancel = $method === 'CANCEL';

        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//Event Schedule//Tickets//EN',
            'CALSCALE:GREGORIAN',
            'METHOD:'.($cancel ? 'CANCEL' : 'REQUEST'),
            'BEGIN:VEVENT',
            'UID:'.self::uid($event, (string) $sale->email, $date, $domain),
            // EventRepo and EventLifecycleService bump this on a material change, a cancellation
            // and a restore, which is what lets the update replace the entry it was sent before.
            'SEQUENCE:'.((int) $event->ical_sequence),
            'DTSTAMP:'.now()->utc()->format('Ymd\THis\Z'),
            'DTSTART:'.$startAt->copy()->utc()->format('Ymd\THis\Z'),
            'DTEND:'.$endAt->copy()->utc()->format('Ymd\THis\Z'),
            'SUMMARY:'.IcsUtils::escape((string) $sale->eventAsRead($event)->name),
            'DESCRIPTION:'.IcsUtils::escape($notes),
        ];
        if ($location !== '') {
            $lines[] = 'LOCATION:'.IcsUtils::escape($location);
        }
        if ($eventUrl !== '') {
            $lines[] = 'URL:'.$eventUrl;
        }
        $lines[] = 'ORGANIZER;CN='.IcsUtils::escapeParam((string) $organizerName).':mailto:'.$organizerEmail;
        // RSVP=FALSE: the ticket is the answer, and a Yes or No pressed in a mail app would go to
        // the sender's address and change nothing here.
        $lines[] = 'ATTENDEE;CUTYPE=INDIVIDUAL;ROLE=REQ-PARTICIPANT;PARTSTAT=ACCEPTED;RSVP=FALSE;CN='
            .IcsUtils::escapeParam((string) ($sale->name ?: $sale->email)).':mailto:'.$sale->email;
        $lines[] = 'STATUS:'.($cancel ? 'CANCELLED' : 'CONFIRMED');
        $lines[] = 'END:VEVENT';
        $lines[] = 'END:VCALENDAR';

        return IcsUtils::fold(implode("\r\n", $lines)."\r\n");
    }

    /**
     * Every invite one person is owed for this event: one per date they hold (a one-off event has
     * one), each built from their own sale so the notes carry their own ticket link. For the change
     * and cancellation mails, which are sent once per address.
     *
     * @return array<int, Attachment>
     */
    public static function attachmentsFor(Event $event, ?Role $role, string $email, string $method): array
    {
        if ($email === '' || $event->appointment_type_id || empty($event->starts_at)) {
            return [];
        }

        $series = (bool) $event->days_of_week;

        $sales = Sale::query()
            ->where('event_id', $event->id)
            ->where('email', $email)
            ->where('status', 'paid')
            ->when($series, fn ($q) => $q->where('event_date', '>=', $event->scheduleToday())->orderBy('event_date'))
            ->orderBy('id')
            ->with('saleTickets.ticket')
            ->get();

        $byDate = [];
        foreach ($sales as $sale) {
            $key = $series ? (string) $sale->event_date : '';
            if (isset($byDate[$key]) || ! self::applies($sale, $event, $method)) {
                continue;
            }
            $byDate[$key] = $sale;
            if (count($byDate) >= self::MAX_DATES) {
                break;
            }
        }

        $attachments = [];
        foreach ($byDate as $key => $sale) {
            $ics = self::forSale($sale, $event, $role, $method);
            if ($ics !== null) {
                $attachments[] = IcsUtils::attachment($ics, $method, $key === '' ? 'event.ics' : 'event-'.$key.'.ics');
            }
        }

        return $attachments;
    }

    public static function uid(Event $event, string $email, ?string $date, ?string $domain = null): string
    {
        $domain ??= parse_url(config('app.url'), PHP_URL_HOST) ?: 'eventschedule.com';
        $who = substr(hash_hmac('sha256', strtolower(trim($email)), (string) config('app.key')), 0, 16);

        return 'ticket-'.$event->id.($date ? '-'.$date : '').'-'.$who.'@'.$domain;
    }

    private static function applies(Sale $sale, Event $event, string $method): bool
    {
        if ($event->appointment_type_id || empty($event->starts_at) || ! $sale->email || $sale->isPass()) {
            return false;
        }

        // A REQUEST puts the event on a calendar, so only for a ticket that stands, to an event
        // that is on. A CANCEL is sent for a cancelled event, whose sales stay paid.
        if ($method !== 'CANCEL' && ($sale->status !== 'paid' || $event->is_cancelled)) {
            return false;
        }

        if ($event->days_of_week) {
            $date = (string) $sale->event_date;

            return Event::isOccurrenceDate($date) && $event->matchesDate($date, $event->scheduleTimezone());
        }

        return true;
    }
}
