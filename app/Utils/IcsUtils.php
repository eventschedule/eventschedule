<?php

namespace App\Utils;

use App\Models\Event;
use App\Models\Role;
use App\Models\Sale;

/**
 * Single-event iCalendar (.ics) invite for appointment emails. Ported from
 * FeedController::buildVevent with METHOD:PUBLISH so mail clients render an add-to-calendar action.
 */
class IcsUtils
{
    /**
     * @param  string  $method  `PUBLISH` (default) or `REQUEST`.
     *
     * `PUBLISH` is an "add this to your calendar" file: mail clients offer a button and each import
     * creates a NEW entry, so re-sending after a time change leaves the guest holding two.
     *
     * `REQUEST` is a real iTIP invitation. Combined with the stable UID, a bumped SEQUENCE and an
     * ORGANIZER/ATTENDEE pair - all three are required, SEQUENCE alone does nothing under PUBLISH - a
     * client CAN update the entry it already has instead of adding another. Used for the reschedule
     * mail; confirmation and reminder stay on PUBLISH so their long-standing behaviour is untouched.
     *
     * Two limits on that, stated here rather than left as an implied promise:
     *
     * 1. The MIME part is plain `text/calendar` with no `method=REQUEST` parameter, because Laravel's
     *    Attachment cannot express a parameterised content type. Outlook and Apple Mail decide
     *    invitation-versus-attachment on that parameter, not on the METHOD: line in the body, so they
     *    may still present the file as a download.
     * 2. The entry being updated was created by a PUBLISH invite that carried no ORGANIZER, and RFC 5546
     *    does not require a client to match an update REQUEST against an organizer-less item.
     *
     * So treat in-place update as best-effort. `update_your_calendar_note` is rendered in the mail for
     * exactly this reason and should stay there.
     */
    public static function buildInvite(Event $event, ?Role $role = null, ?Sale $sale = null, string $method = 'PUBLISH'): string
    {
        $domain = parse_url(config('app.url'), PHP_URL_HOST) ?: 'eventschedule.com';
        $uid = 'appointment-'.$event->id.'@'.$domain;

        $title = $event->name;
        $description = $event->description ? strip_tags($event->description) : '';
        $location = self::resolveLocation($event, $sale);
        $duration = $event->duration > 0 ? $event->duration : 2;

        $startAt = $event->getStartDateTime();
        $start = $startAt->format('Ymd\THis\Z');
        $end = $startAt->copy()->addMinutes(Event::durationHoursToMinutes($duration))->format('Ymd\THis\Z');

        $method = $method === 'REQUEST' ? 'REQUEST' : 'PUBLISH';
        // A booking awaiting the owner's approval is genuinely tentative - saying CONFIRMED would put a
        // solid entry on the guest's calendar for a time nobody has agreed to yet.
        $awaitingApproval = $event->appointment_type_id && $event->isAwaitingCreatorApproval();

        $ics = "BEGIN:VCALENDAR\r\n";
        $ics .= "VERSION:2.0\r\n";
        $ics .= "PRODID:-//Event Schedule//Appointments//EN\r\n";
        $ics .= 'METHOD:'.$method."\r\n";
        $ics .= "BEGIN:VEVENT\r\n";
        $ics .= 'UID:'.$uid."\r\n";
        $ics .= 'SEQUENCE:'.((int) $event->ical_sequence)."\r\n";
        $ics .= 'DTSTAMP:'.now()->format('Ymd\THis\Z')."\r\n";
        $ics .= 'DTSTART:'.$start."\r\n";
        $ics .= 'DTEND:'.$end."\r\n";
        $ics .= 'SUMMARY:'.self::escape($title)."\r\n";
        if ($description) {
            $ics .= 'DESCRIPTION:'.self::escape($description)."\r\n";
        }
        if ($location) {
            $ics .= 'LOCATION:'.self::escape($location)."\r\n";
        }

        if ($method === 'REQUEST') {
            // escapeParam, NOT escape: CN is a param-value (RFC 5545 3.1), where backslash escaping is
            // undefined. TEXT-escaping it left a colon untouched, and a parser splits a property at the
            // first unquoted colon - so a guest called "Dr: Smith" produced a CAL-ADDRESS of
            // "Smith:mailto:..." and the ATTENDEE no longer matched the recipient, which is the one
            // property iTIP needs to update an existing entry. A comma was just as bad: "Doe\, Jane"
            // parses as two param values and displays as "Doe\".
            [$organizerEmail, $organizerName] = self::organizer($role);
            $ics .= 'ORGANIZER;CN='.self::escapeParam($organizerName).':mailto:'.$organizerEmail."\r\n";

            if ($sale?->email) {
                $partstat = $awaitingApproval ? 'NEEDS-ACTION' : 'ACCEPTED';
                $ics .= 'ATTENDEE;CUTYPE=INDIVIDUAL;ROLE=REQ-PARTICIPANT;PARTSTAT='.$partstat
                    .';CN='.self::escapeParam((string) $sale->name).':mailto:'.$sale->email."\r\n";
            }
        }

        if ($event->is_cancelled) {
            $ics .= "STATUS:CANCELLED\r\n";
        } elseif ($awaitingApproval) {
            $ics .= "STATUS:TENTATIVE\r\n";
        }

        $ics .= "END:VEVENT\r\n";
        $ics .= "END:VCALENDAR\r\n";

        return self::fold($ics);
    }

    /**
     * A param-value per RFC 5545 3.1: quoted when it contains a character that would otherwise end the
     * value or the parameter, with any embedded DQUOTE stripped since the grammar provides no escape for
     * one inside a quoted string.
     */
    private static function escapeParam(string $text): string
    {
        $clean = str_replace(['"', "\r", "\n"], ['', ' ', ' '], $text);

        return preg_match('/[,;:]/', $clean) ? '"'.$clean.'"' : $clean;
    }

    /**
     * Fold every content line to the 75-octet limit RFC 5545 3.1 says lines MUST respect.
     *
     * Needed because the ATTENDEE prefix alone is 73 octets before the name, so every REQUEST invite
     * exceeded the limit and strict parsers reject them.
     *
     * Folds on CHARACTER boundaries, not octet boundaries. Splitting mid-sequence is spec-legal (the
     * receiver unfolds before parsing) but measured to leave a line that is not valid UTF-8 on its own in
     * 39 of 120 Hebrew/Arabic/CJK names, which breaks line-oriented tooling for no benefit.
     */
    private static function fold(string $ics): string
    {
        $out = [];

        foreach (explode("\r\n", $ics) as $line) {
            if (strlen($line) <= 75) {
                $out[] = $line;

                continue;
            }

            // A continuation line costs one leading space, so its own budget is one octet smaller.
            $current = '';
            $limit = 75;
            foreach (preg_split('//u', $line, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $char) {
                if (strlen($current) + strlen($char) > $limit) {
                    $out[] = $current;
                    $current = ' ';
                    $limit = 75;
                }
                $current .= $char;
            }
            $out[] = $current;
        }

        return implode("\r\n", $out);
    }

    /**
     * The ORGANIZER address, resolved exactly the way the appointment mailables resolve their From
     * header (see AppointmentConfirmed::envelope()) - an organizer that does not match the sender is
     * the fastest way to have an invitation treated as spoofed.
     *
     * @return array{0:string, 1:string}
     */
    private static function organizer(?Role $role): array
    {
        $email = config('mail.from.address');
        $name = config('mail.from.name');

        if ($role && $role->hasEmailSettings()) {
            $settings = $role->getEmailSettings();
            if (! empty($settings['from_address'])) {
                $email = $settings['from_address'];
            }
            if (! empty($settings['from_name'])) {
                $name = $settings['from_name'];
            }
        }

        return [$email, $name ?: ($role->name ?? 'Event Schedule')];
    }

    /**
     * "Add to Google Calendar" link for a booking.
     *
     * Event::getGoogleCalendarUrl() is not usable here: it builds its title from getTitle(), which
     * appends the venue and the date, and takes its location from the venue - always empty for a
     * booking. Building it next to buildInvite() keeps the link and the mailed .ics in step.
     */
    public static function googleUrl(Event $event, ?Sale $sale = null): string
    {
        [$start, $end] = self::window($event);

        return 'https://calendar.google.com/calendar/render?'.http_build_query([
            'action' => 'TEMPLATE',
            'text' => self::title($event),
            'dates' => $start->format('Ymd\THis\Z').'/'.$end->format('Ymd\THis\Z'),
            'details' => $event->description ? strip_tags($event->description) : '',
            'location' => self::resolveLocation($event, $sale),
        ]);
    }

    /** "Add to Outlook" link (works for both outlook.com and Microsoft 365 accounts). */
    public static function outlookUrl(Event $event, ?Sale $sale = null): string
    {
        [$start, $end] = self::window($event);

        return 'https://outlook.live.com/calendar/0/deeplink/compose?'.http_build_query([
            'path' => '/calendar/action/compose',
            'rru' => 'addevent',
            'subject' => self::title($event),
            'startdt' => $start->format('Y-m-d\TH:i:s\Z'),
            'enddt' => $end->format('Y-m-d\TH:i:s\Z'),
            'body' => $event->description ? strip_tags($event->description) : '',
            'location' => self::resolveLocation($event, $sale),
        ]);
    }

    /** The appointment type's name reads better in a calendar than "Type - Guest Name". */
    private static function title(Event $event): string
    {
        return $event->appointmentType?->name ?: $event->name;
    }

    /** @return array{0: \Carbon\Carbon, 1: \Carbon\Carbon} UTC start/end of the booking. */
    private static function window(Event $event): array
    {
        $duration = $event->duration > 0 ? $event->duration : 2;
        $start = $event->getStartDateTime();

        return [$start, $start->copy()->addMinutes(Event::durationHoursToMinutes($duration))];
    }

    /**
     * LOCATION for the invite. Only online types put their URL on the event itself, so an
     * in-person or phone booking has to read the address/number off the appointment type -
     * otherwise the calendar entry the guest saves has no location at all. Mirrors the branch
     * in emails/appointment_confirmed.blade.php.
     */
    private static function resolveLocation(Event $event, ?Sale $sale = null): string
    {
        if ($event->event_url) {
            return $event->event_url;
        }

        $type = $event->appointmentType;
        if (! $type) {
            return '';
        }

        return match ($type->location_type) {
            'in_person' => (string) $type->location_address,
            'phone' => (string) ($type->location_phone ?: $sale?->phone),
            default => '',
        };
    }

    private static function escape(string $text): string
    {
        return str_replace(
            ['\\', ';', ',', "\r\n", "\r", "\n"],
            ['\\\\', '\\;', '\\,', '\\n', '\\n', '\\n'],
            $text
        );
    }
}
