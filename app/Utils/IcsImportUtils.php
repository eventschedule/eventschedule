<?php

namespace App\Utils;

use Carbon\CarbonImmutable;
use Sabre\VObject\Component\VCalendar;
use Sabre\VObject\Component\VEvent;
use Sabre\VObject\Reader;
use Sabre\VObject\Recur\EventIterator;

/**
 * Reads a calendar feed (iCalendar, the text behind an .ics or webcal link) into the rows the
 * import preview shows.
 *
 * Pure: it is handed the feed's text and returns arrays. Fetching the feed, dropping what is
 * already on the schedule and capping how many are offered are the caller's job.
 *
 * Each row is in the flat shape GeminiUtils::enrichParsedEvents() takes (event_name,
 * event_date_time, venue_name ...), plus what only a feed knows:
 *
 *  - is_all_day: the entry had a date and no time.
 *  - local_time_zone: the zone the time was kept in, when that is not the schedule's own.
 *  - sort_at: when it next happens ("Y-m-d H:i"), for ordering and for showing.
 *  - recurrence: a repeating entry this app can express exactly (RecurrenceMapper). The row is
 *    then the whole series: event_date_time is its FIRST occurrence, as a repeating event's is.
 *  - series: a repeating entry it cannot. Its next dates arrive as separate rows that share
 *    series.id, so the preview can show and select them as one.
 */
class IcsImportUtils
{
    /** How far ahead entries are offered. */
    public const WINDOW_DAYS = 365;

    /** How many dates of a repeating entry are offered when it cannot be one repeating event. */
    public const SERIES_DATES = 12;

    /**
     * @param  bool  $keepLocalClock  False for a venue schedule: see ImportedTime::place().
     * @return array{rows: list<array>, skipped: array{past: int, cancelled: int, private: int, unreadable: int}}
     *
     * @throws \InvalidArgumentException when the text is not a calendar at all
     */
    public static function read(string $body, string $timezone, bool $keepLocalClock, ?\DateTimeInterface $now = null): array
    {
        try {
            $calendar = Reader::read($body, Reader::OPTION_FORGIVING | Reader::OPTION_IGNORE_INVALID_LINES);
        } catch (\Throwable $e) {
            throw new \InvalidArgumentException('not_a_calendar', 0, $e);
        }

        if (! $calendar instanceof VCalendar) {
            throw new \InvalidArgumentException('not_a_calendar');
        }

        $zone = new \DateTimeZone($timezone);
        $now = CarbonImmutable::instance($now ?? now())->setTimezone($zone);
        $from = $now->startOfDay();
        $to = $from->addDays(self::WINDOW_DAYS);

        // A repeating entry and its moved or edited dates share a UID.
        $entries = [];
        foreach ($calendar->select('VEVENT') as $index => $vevent) {
            if (! isset($vevent->UID) || trim((string) $vevent->UID) === '') {
                $vevent->UID = 'no-uid-'.$index;
            }
            $kind = isset($vevent->{'RECURRENCE-ID'}) ? 'overrides' : 'masters';
            $entries[(string) $vevent->UID][$kind][] = $vevent;
        }

        $rows = [];
        $skipped = ['past' => 0, 'cancelled' => 0, 'private' => 0, 'unreadable' => 0];

        foreach ($entries as $uid => $entry) {
            // Edited dates whose series is not in the feed are events in their own right.
            $standalone = isset($entry['masters']) ? [$entry['masters'][0]] : ($entry['overrides'] ?? []);

            foreach ($standalone as $vevent) {
                if ($reason = self::skipReason($vevent)) {
                    $skipped[$reason]++;

                    continue;
                }

                try {
                    $found = isset($vevent->RRULE) && isset($entry['masters'])
                        ? self::series($calendar, $vevent, (string) $uid, ! empty($entry['overrides']), $zone, $keepLocalClock, $from, $to)
                        : self::single($vevent, $zone, $keepLocalClock, $now, $to);
                } catch (\Throwable $e) {
                    // One malformed entry must not cost the person the rest of their calendar.
                    $skipped['unreadable']++;

                    continue;
                }

                if (! $found) {
                    $skipped['past']++;

                    continue;
                }

                array_push($rows, ...$found);
            }
        }

        usort($rows, fn ($a, $b) => [$a['sort_at'], $a['event_name']] <=> [$b['sort_at'], $b['event_name']]);

        return ['rows' => $rows, 'skipped' => $skipped];
    }

    private static function skipReason(VEvent $vevent): ?string
    {
        if (! isset($vevent->DTSTART)) {
            return 'unreadable';
        }

        if (isset($vevent->STATUS) && strtoupper((string) $vevent->STATUS) === 'CANCELLED') {
            return 'cancelled';
        }

        // The owner marked it not for publishing, and a schedule is public.
        if (isset($vevent->CLASS) && in_array(strtoupper((string) $vevent->CLASS), ['PRIVATE', 'CONFIDENTIAL'], true)) {
            return 'private';
        }

        return null;
    }

    /** An entry that happens once. Empty when it is over, or further ahead than the window. */
    private static function single(VEvent $vevent, \DateTimeZone $zone, bool $keepLocalClock, CarbonImmutable $now, CarbonImmutable $to): array
    {
        $start = $vevent->DTSTART->getDateTime($zone);
        $end = self::endOf($vevent, $start, $zone);

        // Still to come, or still going on.
        $upcoming = $start >= $now->startOfDay() || ($end && $end >= $now);
        if (! $upcoming || $start > $to) {
            return [];
        }

        return [self::row($vevent, $start, $end, $zone, $keepLocalClock)];
    }

    /** A repeating entry: one row when the app can repeat it itself, otherwise its next dates. */
    private static function series(VCalendar $calendar, VEvent $master, string $uid, bool $hasOverrides, \DateTimeZone $zone, bool $keepLocalClock, CarbonImmutable $from, CarbonImmutable $to): array
    {
        $iterator = new EventIterator($calendar, $uid, $zone);
        $iterator->fastForward($from);

        if (! $iterator->valid() || $iterator->getDtStart() > $to) {
            return [];
        }

        $masterStart = $master->DTSTART->getDateTime($zone);
        $statesZone = self::statesZone($master);

        // An edited date cannot ride along on a repeating event, so such a series is listed.
        if (! $hasOverrides) {
            [$placedStart] = ImportedTime::place($masterStart, $statesZone, $zone->getName(), $keepLocalClock);
            $place = fn (\DateTimeInterface $at) => ImportedTime::place($at, $statesZone, $zone->getName(), $keepLocalClock)[0];

            $excluded = [];
            foreach ($master->select('EXDATE') as $property) {
                foreach ($property->getDateTimes($zone) as $at) {
                    $excluded[] = $place($at)->format('Y-m-d');
                }
            }

            $included = [];
            $sameTime = true;
            foreach ($master->select('RDATE') as $property) {
                foreach ($property->getDateTimes($zone) as $at) {
                    $placed = $place($at);
                    $sameTime = $sameTime && $placed->format('H:i') === $placedStart->format('H:i');
                    $included[] = $placed->format('Y-m-d');
                }
            }

            $recurrence = $sameTime
                ? RecurrenceMapper::fromRule($master->RRULE->getParts(), $placedStart, $excluded, $included)
                : null;

            if ($recurrence) {
                $row = self::row($master, $masterStart, self::endOf($master, $masterStart, $zone), $zone, $keepLocalClock);
                $row['recurrence'] = $recurrence;
                // Listed by when it next happens, not by a first date that may be years back.
                $row['sort_at'] = $place($iterator->getDtStart())->format('Y-m-d H:i');

                return [$row];
            }
        }

        $rows = [];
        while ($iterator->valid() && count($rows) < self::SERIES_DATES && $iterator->getDtStart() <= $to) {
            $occurrence = $iterator->getEventObject();
            // A date the owner cancelled or hid on its own.
            if (! self::skipReason($occurrence)) {
                $rows[] = self::row($occurrence, $iterator->getDtStart(), $iterator->getDtEnd(), $zone, $keepLocalClock, $statesZone);
            }
            $iterator->next();
        }

        $more = $iterator->valid() && $iterator->getDtStart() <= $to;

        // A "series" of one date is just an event.
        if (count($rows) > 1 || $more) {
            $id = substr(sha1($uid), 0, 12);
            foreach ($rows as $index => $row) {
                $rows[$index]['series'] = ['id' => $id, 'position' => $index + 1, 'count' => count($rows), 'more' => $more];
            }
        }

        return $rows;
    }

    private static function row(VEvent $vevent, \DateTimeInterface $start, ?\DateTimeInterface $end, \DateTimeZone $zone, bool $keepLocalClock, ?bool $statesZone = null): array
    {
        $allDay = ! $vevent->DTSTART->hasTime();
        [$placed, $otherZone] = ImportedTime::place($start, $statesZone ?? self::statesZone($vevent), $zone->getName(), $keepLocalClock);

        if ($allDay) {
            $days = $end ? max(1, (int) round(($end->getTimestamp() - $start->getTimestamp()) / 86400)) : 1;
            $duration = ImportedTime::allDayDuration($days);
        } else {
            $duration = $end ? ImportedTime::hoursBetween($start, $end) : null;
        }

        $url = isset($vevent->URL) ? trim((string) $vevent->URL) : '';
        $location = self::location(isset($vevent->LOCATION) ? (string) $vevent->LOCATION : '');

        return [
            'event_name' => isset($vevent->SUMMARY) ? trim((string) $vevent->SUMMARY) : '',
            'event_details' => self::details(isset($vevent->DESCRIPTION) ? (string) $vevent->DESCRIPTION : ''),
            'event_date_time' => $placed->format('Y-m-d H:i'),
            'event_duration' => $duration ?? '',
            'venue_name' => $location['venue_name'],
            'event_address' => $location['event_address'],
            'registration_url' => self::isWebUrl($url) ? $url : $location['url'],
            'category_name' => self::category($vevent),
            'image_url' => self::image($vevent),
            'is_all_day' => $allDay,
            'local_time_zone' => $otherZone,
            'sort_at' => $placed->format('Y-m-d H:i'),
            'recurrence' => null,
            'series' => null,
            'source_uid' => (string) $vevent->UID,
        ];
    }

    /** Whether the entry's start names a zone of its own, rather than UTC or none at all. */
    private static function statesZone(VEvent $vevent): bool
    {
        $start = $vevent->DTSTART;

        return $start->hasTime() && ! $start->isFloating() && isset($start['TZID']);
    }

    private static function endOf(VEvent $vevent, \DateTimeInterface $start, \DateTimeZone $zone): ?\DateTimeInterface
    {
        if (isset($vevent->DTEND)) {
            return $vevent->DTEND->getDateTime($zone);
        }

        if (isset($vevent->DURATION)) {
            return \DateTimeImmutable::createFromInterface($start)->add($vevent->DURATION->getDateInterval());
        }

        return null;
    }

    /**
     * A feed has one line for where: "The Blue Room, 12 Main St, Austin" or just an address, or a
     * link for something online. A leading part with no digit in it is taken as the venue's name.
     *
     * @return array{venue_name: string, event_address: string, url: string}
     */
    private static function location(string $location): array
    {
        $location = trim(preg_replace('/\s+/', ' ', $location));
        $result = ['venue_name' => '', 'event_address' => '', 'url' => ''];

        if ($location === '') {
            return $result;
        }

        if (self::isWebUrl($location)) {
            $result['url'] = $location;

            return $result;
        }

        [$first, $rest] = array_pad(array_map('trim', explode(',', $location, 2)), 2, '');

        if (preg_match('/\d/', $first)) {
            $result['event_address'] = $location;
        } else {
            $result['venue_name'] = $first;
            $result['event_address'] = $rest;
        }

        return $result;
    }

    /** Feeds carry plain text, except the ones (Google's among them) that put HTML in it. */
    private static function details(string $description): string
    {
        $description = trim($description);

        if ($description !== '' && preg_match('/<(p|br|a|b|i|strong|em|ul|ol|li|div|span|h[1-6])\b[^>]*>/i', $description)) {
            return trim(MarkdownUtils::convertHtmlToMarkdown($description));
        }

        return $description;
    }

    private static function category(VEvent $vevent): string
    {
        if (! isset($vevent->CATEGORIES)) {
            return '';
        }

        return trim((string) ($vevent->CATEGORIES->getParts()[0] ?? ''));
    }

    private static function image(VEvent $vevent): ?string
    {
        foreach (['IMAGE', 'ATTACH'] as $name) {
            foreach ($vevent->select($name) as $property) {
                $url = trim((string) $property);
                $type = strtolower((string) ($property['FMTTYPE'] ?? ''));

                if (self::isWebUrl($url) && (str_starts_with($type, 'image/')
                    || preg_match('/\.(jpe?g|png|gif|webp)(\?|$)/i', $url))) {
                    return $url;
                }
            }
        }

        return null;
    }

    private static function isWebUrl(string $value): bool
    {
        return (bool) preg_match('#^https?://\S+$#i', $value);
    }
}
