<?php

namespace App\Utils;

/**
 * Google Calendar's own shapes, made into what the import already reads.
 *
 * A Google calendar's entries are turned into calendar text (iCalendar) and handed to
 * IcsImportUtils, the reader a pasted calendar link goes through. That is deliberate: a repeat
 * rule, a moved date, an all-day entry, a time zone and the 12-month window then mean exactly
 * what they mean for a feed, and there is one place that decides them. No I/O here.
 */
class GoogleImportUtils
{
    /** Calendars Google adds to every account. Nobody means to publish these. */
    private const BUILT_IN_CALENDARS = [
        '#holiday@group.v.calendar.google.com',
        '#contacts@group.v.calendar.google.com',
        '#weeknum@group.v.calendar.google.com',
        '#weather@group.v.calendar.google.com',
    ];

    /**
     * Entry types that are not events: birthdays, working locations, out-of-office and focus
     * blocks, and entries Google made from mail (a flight, a hotel booking).
     */
    private const EVENT_TYPES = ['default'];

    /**
     * The calendars to offer, in the order to offer them: the person's own first, then ones
     * shared with them, and the account's main calendar last. The main one is where the dentist
     * and the school run live, so it is the last thing to suggest putting on a public schedule.
     *
     * @param  list<array{id: string, name: string, color: ?string, primary: bool, access: string}>  $calendars
     * @return list<array{id: string, name: string, color: ?string, primary: bool, read_only: bool}>
     */
    public static function calendarChoices(array $calendars): array
    {
        $choices = [];

        foreach ($calendars as $calendar) {
            $id = (string) ($calendar['id'] ?? '');
            if ($id === '' || self::isBuiltIn($id)) {
                continue;
            }

            $access = (string) ($calendar['access'] ?? '');
            $choices[] = [
                'id' => $id,
                'name' => trim((string) ($calendar['name'] ?? '')) ?: $id,
                'color' => self::color($calendar['color'] ?? null),
                'primary' => ! empty($calendar['primary']),
                'read_only' => ! in_array($access, ['owner', 'writer'], true),
                'rank' => ! empty($calendar['primary']) ? 2 : ($access === 'owner' ? 0 : 1),
            ];
        }

        usort($choices, fn ($a, $b) => [$a['rank'], mb_strtolower($a['name'])] <=> [$b['rank'], mb_strtolower($b['name'])]);

        return array_map(function (array $choice) {
            unset($choice['rank']);

            return $choice;
        }, $choices);
    }

    /** The address of the Google account: its main calendar is named for it. */
    public static function accountOf(array $calendars): ?string
    {
        foreach ($calendars as $calendar) {
            if (! empty($calendar['primary']) && str_contains((string) $calendar['id'], '@')) {
                return (string) $calendar['id'];
            }
        }

        return null;
    }

    private static function isBuiltIn(string $id): bool
    {
        foreach (self::BUILT_IN_CALENDARS as $suffix) {
            if (str_ends_with($id, $suffix)) {
                return true;
            }
        }

        return false;
    }

    /** A colour is echoed into a style attribute, so only a plain hex value is let through. */
    private static function color($value): ?string
    {
        return is_string($value) && preg_match('/^#[0-9a-fA-F]{6}$/', $value) ? strtolower($value) : null;
    }

    /**
     * Calendar text for a list of Google entries (GoogleCalendarService::listUpcomingEvents()).
     *
     * A date removed from a repeating entry arrives from Google as a cancelled entry of its own.
     * Left like that it would read as "this series has edited dates", which costs the series its
     * repeat rule; it is written as an excluded date on the series instead. A date that was
     * moved or edited stays an entry of its own, as it is in a feed.
     *
     * @param  list<array>  $events
     */
    public static function toCalendarText(array $events, ?string $calendarTimezone = null): string
    {
        $calendarTimezone = self::zone($calendarTimezone) ?: 'UTC';

        // Dates removed from a series, by the series they were removed from.
        $removed = [];
        foreach ($events as $event) {
            if (! empty($event['recurringEventId']) && ($event['status'] ?? null) === 'cancelled' && ! empty($event['originalStartTime'])) {
                $removed[$event['recurringEventId']][] = $event['originalStartTime'];
            }
        }

        $lines = ['BEGIN:VCALENDAR', 'VERSION:2.0', 'PRODID:-//Event Schedule//Google import//EN'];

        foreach ($events as $event) {
            $instanceOf = $event['recurringEventId'] ?? null;
            $cancelled = ($event['status'] ?? null) === 'cancelled';

            if ($instanceOf && $cancelled) {
                continue;
            }
            if (! in_array($event['eventType'] ?? 'default', self::EVENT_TYPES, true)) {
                continue;
            }

            $start = self::moment('DTSTART', $event['start'] ?? null, $calendarTimezone);
            if ($start === null || empty($event['id'])) {
                continue;
            }

            $lines[] = 'BEGIN:VEVENT';
            $lines[] = 'UID:'.self::text((string) ($instanceOf ?: $event['id']));
            $lines[] = $start;
            if ($end = self::moment('DTEND', $event['end'] ?? null, $calendarTimezone)) {
                $lines[] = $end;
            }
            if ($instanceOf && ($original = self::moment('RECURRENCE-ID', $event['originalStartTime'] ?? null, $calendarTimezone))) {
                $lines[] = $original;
            }
            $lines[] = 'SUMMARY:'.self::text((string) ($event['summary'] ?? ''));
            if (! empty($event['description'])) {
                $lines[] = 'DESCRIPTION:'.self::text((string) $event['description']);
            }
            if (! empty($event['location'])) {
                $lines[] = 'LOCATION:'.self::text((string) $event['location']);
            }
            if ($cancelled) {
                $lines[] = 'STATUS:CANCELLED';
            }
            if (in_array($event['visibility'] ?? null, ['private', 'confidential'], true)) {
                $lines[] = 'CLASS:PRIVATE';
            }

            if (! $instanceOf) {
                foreach ((array) ($event['recurrence'] ?? []) as $rule) {
                    // Google hands these over as finished calendar lines. Only the four kinds
                    // that describe a repeat are taken, and never one that spans lines.
                    $rule = trim((string) $rule);
                    if (preg_match('/^(RRULE|EXRULE|RDATE|EXDATE)[;:][^\r\n]+$/', $rule)) {
                        $lines[] = $rule;
                    }
                }
                foreach ($removed[$event['id']] ?? [] as $gone) {
                    if ($excluded = self::moment('EXDATE', $gone, $calendarTimezone)) {
                        $lines[] = $excluded;
                    }
                }
            }

            $lines[] = 'END:VEVENT';
        }

        $lines[] = 'END:VCALENDAR';

        return implode("\r\n", $lines)."\r\n";
    }

    /**
     * One date or time as a calendar line. A timed entry is written in its own zone (Google
     * names one on repeating entries, and the calendar's zone stands in otherwise), so "6 PM
     * every Monday" stays 6 PM across a clock change.
     */
    private static function moment(string $name, ?array $moment, string $calendarTimezone): ?string
    {
        if (! $moment) {
            return null;
        }

        if (! empty($moment['date']) && preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', (string) $moment['date'], $parts)) {
            return $name.';VALUE=DATE:'.$parts[1].$parts[2].$parts[3];
        }

        if (empty($moment['dateTime'])) {
            return null;
        }

        try {
            $zone = self::zone($moment['timeZone'] ?? null) ?: $calendarTimezone;
            $at = (new \DateTimeImmutable((string) $moment['dateTime']))->setTimezone(new \DateTimeZone($zone));
        } catch (\Throwable $e) {
            return null;
        }

        return $zone === 'UTC'
            ? $name.':'.$at->format('Ymd\THis\Z')
            : $name.';TZID='.$zone.':'.$at->format('Ymd\THis');
    }

    /** A zone name PHP knows, or null. Goes into a calendar line, so nothing else is let through. */
    private static function zone($name): ?string
    {
        if (! is_string($name) || $name === '') {
            return null;
        }

        try {
            return (new \DateTimeZone($name))->getName();
        } catch (\Throwable $e) {
            return null;
        }
    }

    /** A value escaped as calendar text: one line, with its commas and semicolons kept literal. */
    private static function text(string $value): string
    {
        return str_replace(
            ['\\', ';', ',', "\r\n", "\n", "\r"],
            ['\\\\', '\;', '\\,', '\\n', '\\n', ''],
            $value
        );
    }
}
