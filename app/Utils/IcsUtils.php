<?php

namespace App\Utils;

use App\Models\Event;
use App\Models\Role;

/**
 * Single-event iCalendar (.ics) invite for appointment emails. Ported from
 * FeedController::buildVevent with METHOD:PUBLISH so mail clients render an add-to-calendar action.
 */
class IcsUtils
{
    public static function buildInvite(Event $event, ?Role $role = null): string
    {
        $domain = parse_url(config('app.url'), PHP_URL_HOST) ?: 'eventschedule.com';
        $uid = 'appointment-'.$event->id.'@'.$domain;

        $title = $event->name;
        $description = $event->description ? strip_tags($event->description) : '';
        $location = $event->event_url ?: '';
        $duration = $event->duration > 0 ? $event->duration : 2;

        $startAt = $event->getStartDateTime();
        $start = $startAt->format('Ymd\THis\Z');
        $end = $startAt->copy()->addMinutes(Event::durationHoursToMinutes($duration))->format('Ymd\THis\Z');

        $ics = "BEGIN:VCALENDAR\r\n";
        $ics .= "VERSION:2.0\r\n";
        $ics .= "PRODID:-//Event Schedule//Appointments//EN\r\n";
        $ics .= "METHOD:PUBLISH\r\n";
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
        if ($event->is_cancelled) {
            $ics .= "STATUS:CANCELLED\r\n";
        }
        $ics .= "END:VEVENT\r\n";
        $ics .= "END:VCALENDAR\r\n";

        return $ics;
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
