<?php

namespace App\Utils;

use Carbon\CarbonImmutable;
use Sabre\VObject\Component\VCalendar;
use Sabre\VObject\Component\VEvent;
use Sabre\VObject\Reader;
use Sabre\VObject\Recur\EventIterator;
use Sabre\VObject\Settings;
use Sabre\VObject\TimeZoneUtil;

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
     * How many dates the calendar library may step through for one series. It walks a series
     * from its first date and stops at 3,500 by default, which a daily class reaches in under
     * ten years: such an entry used to be dropped without a word. This is a daily entry for
     * over a century.
     */
    private const MAX_STEPS = 40000;

    /** Seconds one feed may take to read. What is left after that is counted as unreadable. */
    private const MAX_SECONDS = 10;

    /** Rules that are not about days. Nothing that repeats every hour is an event listing. */
    private const SUB_DAILY = ['SECONDLY', 'MINUTELY', 'HOURLY'];

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
        $starts = [];
        $families = [];
        $zones = [];
        foreach ($calendar->select('VEVENT') as $index => $vevent) {
            self::normalise($vevent, $calendar, $zones);

            if (! isset($vevent->UID) || trim((string) $vevent->UID) === '') {
                $vevent->UID = 'no-uid-'.$index;
            }
            $uid = (string) $vevent->UID;

            if (isset($vevent->{'RECURRENCE-ID'})) {
                $entries[$uid]['overrides'][] = $vevent;

                continue;
            }

            // Some feeds give every entry the same id. The same start and the same title is the
            // same entry said twice; anything else is another event, and gets an id of its own.
            // By start alone, two acts on two stages at eight o'clock were one event, and the
            // second was dropped without being counted.
            $start = (isset($vevent->DTSTART) ? $vevent->DTSTART->serialize() : '').'|'.trim((string) ($vevent->SUMMARY ?? ''));
            if (isset($starts[$uid][$start])) {
                continue;
            }
            $starts[$uid][$start] = true;
            $shared = $uid;
            if (count($starts[$uid]) > 1) {
                $uid .= '#'.count($starts[$uid]);
                $vevent->UID = $uid;
            }
            $families[$shared][] = $uid;

            $entries[$uid]['masters'][] = $vevent;
        }

        // A moved or cancelled date names the id it was written with, which several entries
        // may share. It belongs to one that repeats: left with whichever came first, a one-off,
        // the series kept the date as it was and lost the change. With two series under one id
        // there is no telling whose it is, and the first of them gets it. (An id made of digits
        // comes back from an array key as an integer, so the two are compared as text.)
        foreach ($families as $shared => $ids) {
            if (count($ids) < 2 || empty($entries[$shared]['overrides'])) {
                continue;
            }
            foreach ($ids as $id) {
                $master = $entries[$id]['masters'][0];
                if (isset($master->RRULE) || isset($master->RDATE)) {
                    if ((string) $id !== (string) $shared) {
                        $entries[$id]['overrides'] = $entries[$shared]['overrides'];
                        unset($entries[$shared]['overrides']);
                    }

                    break;
                }
            }
        }

        $rows = [];
        $skipped = ['past' => 0, 'cancelled' => 0, 'private' => 0, 'unreadable' => 0];
        $deadline = microtime(true) + self::MAX_SECONDS;
        $libraryLimit = Settings::$maxRecurrences;
        Settings::$maxRecurrences = self::MAX_STEPS;

        try {
            foreach ($entries as $uid => $entry) {
                // Edited dates whose series is not in the feed are events in their own right.
                $standalone = isset($entry['masters']) ? [$entry['masters'][0]] : ($entry['overrides'] ?? []);

                foreach ($standalone as $vevent) {
                    if ($reason = self::skipReason($vevent)) {
                        $skipped[$reason]++;

                        continue;
                    }

                    // A feed built to keep the reader busy must not hold the request.
                    if (microtime(true) > $deadline) {
                        $skipped['unreadable']++;

                        continue;
                    }

                    try {
                        $found = isset($entry['masters']) && (isset($vevent->RRULE) || isset($vevent->RDATE))
                            ? self::series($vevent, $entry['overrides'] ?? [], (string) $uid, $zone, $keepLocalClock, $from, $to)
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
        } finally {
            Settings::$maxRecurrences = $libraryLimit;
        }

        usort($rows, fn ($a, $b) => [$a['sort_at'], $a['event_name']] <=> [$b['sort_at'], $b['event_name']]);

        return ['rows' => $rows, 'skipped' => $skipped];
    }

    /**
     * Two things a feed gets wrong often enough to matter, put right before anything reads it.
     *
     * A zone nobody knows (Exchange writes "Customized Time Zone"): the library reads such a
     * time in the SERVER's zone, which put a 7 PM event at 3 PM. Without its zone the time is
     * read as it is written, on the schedule's own clock.
     *
     * A rule with no frequency: not a rule. The entry is what it would be without one.
     */
    private static function normalise(VEvent $vevent, VCalendar $calendar, array &$zones): void
    {
        foreach (['DTSTART', 'DTEND', 'RECURRENCE-ID', 'EXDATE', 'RDATE'] as $name) {
            foreach ($vevent->select($name) as $property) {
                if (! isset($property['TZID'])) {
                    continue;
                }

                $tzid = (string) $property['TZID'];
                if (! array_key_exists($tzid, $zones)) {
                    try {
                        TimeZoneUtil::getTimeZone($tzid, $calendar, true);
                        $zones[$tzid] = true;
                    } catch (\Throwable $e) {
                        $zones[$tzid] = false;
                    }
                }

                if (! $zones[$tzid]) {
                    unset($property['TZID']);
                }
            }
        }

        if (isset($vevent->RRULE)) {
            try {
                $frequency = $vevent->RRULE->getParts()['FREQ'] ?? null;
            } catch (\Throwable $e) {
                $frequency = null;
            }
            if (! $frequency) {
                unset($vevent->RRULE);
            }
        }
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

    /**
     * A repeating entry: one row when the app can repeat it itself, otherwise its next dates.
     *
     * @param  list<VEvent>  $overrides  Its moved or edited dates.
     */
    private static function series(VEvent $master, array $overrides, string $uid, \DateTimeZone $zone, bool $keepLocalClock, CarbonImmutable $from, CarbonImmutable $to): array
    {
        $hasRule = isset($master->RRULE);
        if ($hasRule && in_array(strtoupper((string) ($master->RRULE->getParts()['FREQ'] ?? '')), self::SUB_DAILY, true)) {
            throw new \DomainException('A rule that is not about days.');
        }

        $masterStart = $master->DTSTART->getDateTime($zone);
        $statesZone = self::statesZone($master);

        // Everything about a series is read on the series' own clock. A removed, added or moved
        // date is often written in UTC even when the series names a zone, and midnight UTC on
        // the 13th is the evening of the 12th in Chicago: taken on UTC's clock, the wrong date
        // was removed.
        $own = fn (\DateTimeInterface $at) => \DateTimeImmutable::createFromInterface($at)->setTimezone($masterStart->getTimezone());
        $place = fn (\DateTimeInterface $at) => ImportedTime::place($own($at), $statesZone, $zone->getName(), $keepLocalClock)[0];

        // Added dates (RDATE). The library follows them INSTEAD of the rule when an entry has
        // both, so they are taken off the entry and handled here.
        $added = [];
        foreach ($master->select('RDATE') as $property) {
            foreach ($property->getDateTimes($zone) as $at) {
                $added[] = $own($at);
            }
        }
        unset($master->RDATE);
        $addedAhead = array_values(array_filter($added, fn ($at) => $at >= $from && $at <= $to));
        usort($addedAhead, fn ($a, $b) => $a <=> $b);

        // Handed the entry itself: given the feed and an id, the library searches the whole feed
        // for every repeating entry, which made a long feed take minutes.
        $iterate = function () use ($master, $overrides, $zone, $from) {
            $iterator = new EventIterator(array_merge([$master], $overrides), null, $zone);
            $iterator->fastForward($from);

            return $iterator;
        };
        $iterator = $iterate();
        $ruleAhead = $iterator->valid() && $iterator->getDtStart() <= $to;

        if (! $ruleAhead && ! $addedAhead) {
            return [];
        }

        // An edited date cannot ride along on a repeating event, so such a series is listed.
        if ($hasRule && $ruleAhead && ! $overrides) {
            $placedStart = $place($masterStart);

            $excluded = [];
            foreach ($master->select('EXDATE') as $property) {
                foreach ($property->getDateTimes($zone) as $at) {
                    $excluded[] = $place($at)->format('Y-m-d');
                }
            }

            $included = [];
            $sameTime = true;
            foreach ($added as $at) {
                $placed = $place($at);
                $sameTime = $sameTime && $placed->format('H:i') === $placedStart->format('H:i');
                $included[] = $placed->format('Y-m-d');
            }

            $recurrence = $sameTime && ! self::daysMoved($master->RRULE->getParts(), $masterStart, $placedStart)
                ? RecurrenceMapper::fromRule($master->RRULE->getParts(), $placedStart, $excluded, $included)
                : null;

            if ($recurrence) {
                $next = $iterator->getDtStart();

                if (self::clockHolds($master, $masterStart, $statesZone, $zone, $keepLocalClock, $iterator, $to)) {
                    $row = self::row($master, $masterStart, self::endOf($master, $masterStart, $zone), $zone, $keepLocalClock);
                    $row['recurrence'] = $recurrence;
                    // Listed by when it next happens, not by a first date that may be years back.
                    if ($addedAhead && $addedAhead[0] < $next) {
                        $next = $addedAhead[0];
                    }
                    $row['sort_at'] = $place($next)->format('Y-m-d H:i');

                    return [$row];
                }

                // The check walked the iterator through the year. Start it again for the list.
                $iterator = $iterate();
            }
        }

        $rows = [];
        while ($iterator->valid() && count($rows) < self::SERIES_DATES && $iterator->getDtStart() <= $to) {
            $occurrence = $iterator->getEventObject();
            // A date the owner cancelled or hid on its own.
            if (! self::skipReason($occurrence)) {
                $start = $iterator->getDtStart();
                $end = $iterator->getDtEnd();
                // A moved date written in UTC, on a series that names its zone.
                if ($statesZone && ! self::statesZone($occurrence)) {
                    $start = $own($start);
                    $end = $end ? $own($end) : null;
                }
                $rows[] = self::row($occurrence, $start, $end, $zone, $keepLocalClock, $statesZone);
            }
            $iterator->next();
        }

        $more = $iterator->valid() && $iterator->getDtStart() <= $to;

        // The added dates, each as long as the entry itself.
        $masterEnd = self::endOf($master, $masterStart, $zone);
        $seconds = $masterEnd ? $masterEnd->getTimestamp() - $masterStart->getTimestamp() : 0;
        foreach ($addedAhead as $at) {
            $rows[] = self::row($master, $at, $seconds > 0 ? $at->modify('+'.$seconds.' seconds') : null, $zone, $keepLocalClock, $statesZone);
        }

        // In order, each moment once (a feed can add the date its rule already gives).
        usort($rows, fn ($a, $b) => $a['sort_at'] <=> $b['sort_at']);
        $unique = [];
        foreach ($rows as $row) {
            $unique[$row['sort_at']] ??= $row;
        }
        $rows = array_values($unique);
        if (count($rows) > self::SERIES_DATES) {
            $rows = array_slice($rows, 0, self::SERIES_DATES);
            $more = true;
        }

        // A "series" of one date is just an event.
        if (count($rows) > 1 || $more) {
            $id = substr(sha1($uid), 0, 12);
            foreach ($rows as $index => $row) {
                $rows[$index]['series'] = ['id' => $id, 'position' => $index + 1, 'count' => count($rows), 'more' => $more];
            }
        }

        return $rows;
    }

    /**
     * Whether putting a rule's start on the schedule's clock moved it to another calendar day
     * in a way the rule cannot follow. The time keeping still is clockHolds()'s question; this
     * one is about the day.
     *
     * A rule names its days on the clock it was written on. Monday to Friday at 08:00 in Tokyo,
     * written in UTC, is `DTSTART:...T230000Z` with `BYDAY=SU,MO,TU,WE,TH`: read onto Tokyo's
     * clock the start is a Monday, Monday is one of the days named, and it became one repeating
     * event on Sunday to Thursday. The rule mapper catches a start that is not one of the
     * rule's days; it cannot catch one that still is.
     *
     * So when the day moves:
     *  - a rule that names days or dates (BYDAY, BYMONTHDAY, BYMONTH, BYSETPOS) is listed by
     *    date. Rewriting the names onto the shifted days would be right for a plain weekly rule
     *    and wrong for "the second Monday", so none is rewritten;
     *  - a monthly rule is one only while both days are in the same month and up to the 28th:
     *    "the 1st" seen from the west is the last day of the month before, which is not a date;
     *  - a yearly rule is one unless both days are among 28 and 29 February and 1 March:
     *    across the leap day, which date one of them is depends on the year. (27 February
     *    seen as the 28th, or 1 March seen as the 2nd, is the same pair every year.)
     * A plain weekly, fortnightly or daily rule moves whole and is still one repeating event.
     */
    private static function daysMoved(array $parts, \DateTimeInterface $written, \DateTimeInterface $placed): bool
    {
        if ($written->format('Y-m-d') === $placed->format('Y-m-d')) {
            return false;
        }

        $parts = array_change_key_case($parts, CASE_UPPER);
        foreach (['BYDAY', 'BYMONTHDAY', 'BYMONTH', 'BYSETPOS'] as $named) {
            if (! empty($parts[$named])) {
                return true;
            }
        }

        $leapEdge = ['02-28', '02-29', '03-01'];

        return match (strtoupper((string) ($parts['FREQ'] ?? ''))) {
            'MONTHLY' => $written->format('Y-m') !== $placed->format('Y-m')
                || max((int) $written->format('j'), (int) $placed->format('j')) > 28,
            'YEARLY' => in_array($written->format('m-d'), $leapEdge, true) && in_array($placed->format('m-d'), $leapEdge, true),
            default => false,
        };
    }

    /**
     * Whether every date of a series falls at the same clock time on the schedule. One repeating
     * event has one clock time; a rule anchored in UTC, or in a zone whose clocks change on
     * other dates, drifts by an hour for part of the year (and across midnight, by a day), so
     * such a series is listed by date instead of being saved an hour wrong.
     *
     * Walks the iterator it is given through the window.
     */
    private static function clockHolds(VEvent $master, \DateTimeInterface $masterStart, bool $statesZone, \DateTimeZone $zone, bool $keepLocalClock, EventIterator $iterator, CarbonImmutable $to): bool
    {
        // No time to drift, its own clock is kept, or it is already on the schedule's clock
        // (a time with no zone at all was read in the schedule's).
        if (! $master->DTSTART->hasTime()
            || ($statesZone && $keepLocalClock)
            || $masterStart->getTimezone()->getName() === $zone->getName()) {
            return true;
        }

        $clock = \DateTimeImmutable::createFromInterface($masterStart)->setTimezone($zone)->format('H:i');

        for ($steps = 0; $steps < 400 && $iterator->valid() && $iterator->getDtStart() <= $to; $steps++) {
            if (\DateTimeImmutable::createFromInterface($iterator->getDtStart())->setTimezone($zone)->format('H:i') !== $clock) {
                return false;
            }
            $iterator->next();
        }

        return true;
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
