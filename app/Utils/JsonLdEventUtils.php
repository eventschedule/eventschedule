<?php

namespace App\Utils;

use Carbon\CarbonImmutable;

/**
 * Reads the events a web page describes about itself (schema.org Event data in JSON-LD, the
 * markup search engines read) into the rows the import preview shows.
 *
 * Most event platforms and site builders publish it, and it is exact: names, start times with
 * their offsets, venues with their addresses. Reading it needs no model and no guessing, so a
 * page that has it never costs an AI request.
 *
 * Pure: it is handed the page's HTML and the address it came from, and returns arrays in the flat
 * shape GeminiUtils::enrichParsedEvents() takes, plus is_all_day, local_time_zone, sort_at and
 * image_url as IcsImportUtils does.
 */
class JsonLdEventUtils
{
    /** schema.org types that are an event somebody could attend. */
    private const EVENT_TYPES = [
        'Event', 'MusicEvent', 'ComedyEvent', 'TheaterEvent', 'DanceEvent', 'Festival', 'SportsEvent',
        'FoodEvent', 'EducationEvent', 'BusinessEvent', 'SocialEvent', 'ExhibitionEvent',
        'VisualArtsEvent', 'LiteraryEvent', 'ScreeningEvent', 'ChildrensEvent', 'Hackathon',
        'CourseInstance', 'SaleEvent',
    ];

    /** The app's default category names (config app.event_categories), by event type. */
    private const CATEGORY_BY_TYPE = [
        'MusicEvent' => 'Concerts',
        'ComedyEvent' => 'Art & Culture',
        'TheaterEvent' => 'Art & Culture',
        'DanceEvent' => 'Art & Culture',
        'ExhibitionEvent' => 'Art & Culture',
        'VisualArtsEvent' => 'Art & Culture',
        'LiteraryEvent' => 'Art & Culture',
        'ScreeningEvent' => 'Art & Culture',
        'Festival' => 'Parties & Festivals',
        'SportsEvent' => 'Sports',
        'FoodEvent' => 'Food & Drink',
        'EducationEvent' => 'Education',
        'CourseInstance' => 'Education',
        'BusinessEvent' => 'Business Networking',
        'SocialEvent' => 'Community',
        'ChildrensEvent' => 'Community',
        'Hackathon' => 'Tech',
    ];

    /** How far ahead events are offered, as for a feed. */
    public const WINDOW_DAYS = 365;

    private const MAX_DEPTH = 6;

    private const MAX_PERFORMERS = 10;

    /** Longer than any opening script tag worth reading. */
    private const MAX_TAG = 2000;

    /**
     * The contents of a page's JSON-LD script blocks, found by walking the text once.
     *
     * Not one pattern over the whole page: "a script tag, then anything, then its end" is
     * retried from every opening tag when the end never comes, and a page made of opening tags
     * took most of a minute. The page is somebody else's, so that is theirs to choose.
     *
     * Nor a search to the end of the page for each tag's ">": with one ">" at the very end,
     * every opening tag before it was scanned to the end again, which is the same cost by
     * another road (13 seconds at the size a page is cut to). An opening tag is short, so its
     * end is looked for within the length one can have, and no further.
     *
     * @return list<string>
     */
    private static function blocks(string $html): array
    {
        $blocks = [];
        $offset = 0;

        $length = strlen($html);

        while (($open = stripos($html, '<script', $offset)) !== false) {
            $span = strcspn($html, '>', $open, self::MAX_TAG);
            $tagEnd = $open + $span;

            // No end within reach, or not a script tag at all ("<script-loader>", "<scripts>"):
            // carry on from just past it. A tag name ends at a space, a slash or the ">".
            if ($span >= self::MAX_TAG || $tagEnd >= $length || ! preg_match('#^<script[\s/>]#i', substr($html, $open, 8))) {
                $offset = $open + 7;

                continue;
            }

            $tag = substr($html, $open, $span + 1);
            $close = stripos($html, '</script', $tagEnd + 1);
            if ($close === false) {
                break;
            }

            // The type may carry a parameter: application/ld+json; charset=utf-8.
            if (preg_match('#\btype\s*=\s*(["\']?)application/ld\+json(?:\s*;[^"\'>]*)?\1#i', $tag)) {
                $blocks[] = substr($html, $tagEnd + 1, $close - $tagEnd - 1);
            }

            $offset = $close + 8;
        }

        return $blocks;
    }

    /**
     * @param  string  $pageUrl  The address the HTML was finally served from, for relative links.
     * @param  bool  $keepLocalClock  False for a venue schedule: see ImportedTime::place().
     * @return array{rows: list<array>, skipped: array{past: int, cancelled: int, unreadable: int}, seen: array<string, string>, complete: bool}
     *
     * Each row carries `source_id`, the event's identity on the page for a reader that comes
     * back (a feed): its `@id`, else its own `url`, else its name and start. `seen` names every
     * event the page holds by that id with what became of it (listed, past, later, cancelled,
     * unreadable), so an event that is merely over, postponed past the window or called off is
     * not mistaken for one the page no longer lists. See identities().
     */
    public static function read(string $html, string $pageUrl, string $timezone, bool $keepLocalClock, ?\DateTimeInterface $now = null): array
    {
        $now = CarbonImmutable::instance($now ?? now())->setTimezone($timezone);
        $from = $now->startOfDay();
        $to = $from->addDays(self::WINDOW_DAYS);

        $nodes = [];
        foreach (self::blocks($html) as $block) {
            // Some pages wrap the JSON in a comment or CDATA; none of that is JSON.
            $block = trim(preg_replace('#^\s*(<!--|//\s*<!\[CDATA\[|<!\[CDATA\[)|(-->|//\s*\]\]>|\]\]>)\s*$#', '', trim($block)));
            $data = json_decode($block, true);
            if (is_array($data)) {
                self::collect($data, $nodes, 0);
            }
        }

        $rows = [];
        $once = [];
        $found = [];
        $skipped = ['past' => 0, 'cancelled' => 0, 'unreadable' => 0];

        foreach ($nodes as $node) {
            // Read before it is judged, so that an event has the same name-and-start whether it
            // is on, over or called off: that is what tells two events at one address apart.
            $row = self::row($node, $pageUrl, $timezone, $keepLocalClock);
            $key = $row === null ? null : mb_strtolower($row['event_name']).'|'.$row['event_date_time'];
            // An `@id` need not be an address (urn:uuid:...): it is kept as written then.
            $own = trim(self::text($node['@id'] ?? ''));
            $address = $own !== ''
                ? (self::absolute($own, $pageUrl) ?: $own)
                : self::absolute(self::text($node['url'] ?? ''), $pageUrl);

            if (str_contains((string) self::text($node['eventStatus'] ?? ''), 'EventCancelled')) {
                $skipped['cancelled']++;
                $found[] = ['address' => $address, 'key' => $key, 'reason' => 'cancelled', 'row' => null];

                continue;
            }

            if ($row === null) {
                $skipped['unreadable']++;
                $found[] = ['address' => $address, 'key' => null, 'reason' => 'unreadable', 'row' => null];

                continue;
            }

            // The same event marked up twice on one page (a list and a detail block) is one event.
            if (isset($once[$key])) {
                continue;
            }
            $once[$key] = true;

            $startsOn = substr($row['sort_at'], 0, 10);
            $endsAt = $row['ends_at'];
            unset($row['ends_at']);

            $upcoming = $startsOn >= $from->format('Y-m-d') || ($endsAt !== null && $endsAt >= $now->getTimestamp());
            if (! $upcoming || $startsOn > $to->format('Y-m-d')) {
                $skipped['past']++;
                $found[] = ['address' => $address, 'key' => $key, 'reason' => $upcoming ? 'later' : 'past', 'row' => null];

                continue;
            }

            $found[] = ['address' => $address, 'key' => $key, 'reason' => 'listed', 'row' => count($rows)];
            $rows[] = $row;
        }

        $seen = [];
        foreach (self::identities($found) as $index => $id) {
            if ($id === null) {
                continue;
            }

            // A cancelled copy of an event that is also listed does not take its id from it.
            if (($seen[$id] ?? null) !== 'listed') {
                $seen[$id] = $found[$index]['reason'];
            }

            if ($found[$index]['row'] !== null) {
                $rows[$found[$index]['row']]['source_id'] = $id;
            }
        }

        usort($rows, fn ($a, $b) => [$a['sort_at'], $a['event_name']] <=> [$b['sort_at'], $b['event_name']]);

        return ['rows' => $rows, 'skipped' => $skipped, 'seen' => $seen, 'complete' => true];
    }

    /**
     * The id each event keeps from one read of the page to the next.
     *
     * Its own address (`@id`, else `url`) when it is the only event that has it. Many pages
     * give every event in a list the address of the list, and some give none, so:
     *
     *  - several different events at one address are each that address plus a mark made of the
     *    name and start, all of them, so that none depends on which comes first on the page;
     *  - an event with no address is known by its name and start alone.
     *
     * An event nothing can be said about (no address, and no name or start to read) has no id.
     *
     * @param  list<array{address: string, key: ?string, reason: string, row: ?int}>  $found
     * @return list<?string>
     */
    private static function identities(array $found): array
    {
        $shared = [];
        foreach ($found as $entry) {
            if ($entry['address'] !== '') {
                $shared[$entry['address']][$entry['key'] ?? ''] = true;
            }
        }

        return array_map(function (array $entry) use ($shared) {
            $mark = $entry['key'] === null ? null : substr(sha1($entry['key']), 0, 12);

            if ($entry['address'] === '') {
                return $mark === null ? null : 'ld-'.$mark;
            }

            return count($shared[$entry['address']]) > 1 && $mark !== null
                ? $entry['address'].'#'.$mark
                : $entry['address'];
        }, $found);
    }

    /** Walk a decoded block and gather every event node in it, wherever it is nested. */
    private static function collect(array $data, array &$nodes, int $depth): void
    {
        if ($depth > self::MAX_DEPTH) {
            return;
        }

        // A list: of nodes, or of list items wrapping them.
        if (array_is_list($data)) {
            foreach ($data as $item) {
                if (is_array($item)) {
                    self::collect($item, $nodes, $depth + 1);
                }
            }

            return;
        }

        // A type is a word or a list of words. Anything else in its place is not a type.
        $types = array_map('strval', array_filter((array) ($data['@type'] ?? []), 'is_scalar'));

        if (array_intersect($types, self::EVENT_TYPES)) {
            $nodes[] = $data;
        }

        // Where events hang off other things: a graph, a list, a page, a series, a venue's or an
        // organizer's own listing. Not superEvent: that leads up to the festival, not down.
        foreach (['@graph', 'itemListElement', 'item', 'mainEntity', 'mainEntityOfPage', 'subEvent', 'subEvents', 'event', 'events'] as $key) {
            if (isset($data[$key]) && is_array($data[$key])) {
                self::collect($data[$key], $nodes, $depth + 1);
            }
        }
    }

    private static function row(array $node, string $pageUrl, string $timezone, bool $keepLocalClock): ?array
    {
        $name = self::plain(self::text($node['name'] ?? ''));
        $start = self::moment(self::text($node['startDate'] ?? ''), $timezone);

        if ($name === '' || $start === null) {
            return null;
        }

        // A whole day is a date as written: it is on no clock, so it is not moved to one.
        [$placed, $otherZone] = $start['all_day']
            ? [\Carbon\CarbonImmutable::instance($start['at']), null]
            : ImportedTime::place($start['at'], $start['states_zone'], $timezone, $keepLocalClock);

        $end = self::moment(self::text($node['endDate'] ?? ''), $timezone);
        $duration = '';
        $endsAt = null;

        if ($start['all_day']) {
            $days = 1;
            if ($end) {
                // An end DATE is the last day, inclusive, in this vocabulary.
                $days = max(1, (int) round(($end['at']->getTimestamp() - $start['at']->getTimestamp()) / 86400) + ($end['all_day'] ? 1 : 0));
            }
            $duration = ImportedTime::allDayDuration($days);
            $endsAt = $start['at']->getTimestamp() + (int) round($duration * 3600);
        } elseif ($end && ($hours = ImportedTime::hoursBetween($start['at'], $end['at']))) {
            $duration = $hours;
            $endsAt = $end['at']->getTimestamp();
        } elseif (($iso = self::text($node['duration'] ?? '')) !== '') {
            try {
                $interval = new \DateInterval($iso);
                $hours = round(($interval->d * 86400 + $interval->h * 3600 + $interval->i * 60 + $interval->s) / 3600, 3);
                if ($hours > 0) {
                    $duration = $hours;
                    $endsAt = $start['at']->getTimestamp() + (int) round($hours * 3600);
                }
            } catch (\Throwable $e) {
                // Not a duration; leave it out.
            }
        }

        $location = self::location($node['location'] ?? null);
        $offer = self::offer($node['offers'] ?? null);
        $ownUrl = self::text($node['url'] ?? '');

        $registration = self::absolute($offer['url'] ?: $location['url'] ?: $ownUrl, $pageUrl);

        $types = array_map('strval', (array) ($node['@type'] ?? []));
        $category = '';
        foreach ($types as $type) {
            if (isset(self::CATEGORY_BY_TYPE[$type])) {
                $category = self::CATEGORY_BY_TYPE[$type];

                break;
            }
        }

        $performers = [];
        foreach (self::listOf($node['performer'] ?? $node['performers'] ?? null) as $performer) {
            $performerName = self::plain(is_array($performer) ? self::text($performer['name'] ?? '') : self::text($performer));
            if ($performerName !== '' && count($performers) < self::MAX_PERFORMERS) {
                $performers[] = [
                    'name' => $performerName,
                    'name_en' => '',
                    'email' => '',
                    'website' => is_array($performer) ? self::absolute(self::text($performer['url'] ?? $performer['sameAs'] ?? ''), $pageUrl) : '',
                ];
            }
        }

        $row = [
            'event_name' => $name,
            'event_details' => self::details(self::text($node['description'] ?? '')),
            'event_date_time' => $placed->format('Y-m-d H:i'),
            'event_duration' => $duration,
            'venue_name' => $location['venue_name'],
            'event_address' => $location['event_address'],
            'event_city' => $location['event_city'],
            'event_state' => $location['event_state'],
            'event_postal_code' => $location['event_postal_code'],
            'event_country_code' => $location['event_country_code'],
            'registration_url' => $registration,
            'ticket_price' => $offer['price'],
            'ticket_currency' => $offer['currency'],
            'category_name' => $category,
            'image_url' => self::absolute(self::image($node['image'] ?? null), $pageUrl) ?: null,
            'is_all_day' => $start['all_day'],
            'local_time_zone' => $otherZone,
            'sort_at' => $placed->format('Y-m-d H:i'),
            'recurrence' => null,
            'series' => null,
            'ends_at' => $endsAt,
        ];

        if ($performers) {
            $row['performers'] = $performers;
        }

        return $row;
    }

    /**
     * An ISO 8601 date or date-time as an instant.
     *
     * @return ?array{at: CarbonImmutable, all_day: bool, states_zone: bool}
     */
    private static function moment(string $value, string $timezone): ?array
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        try {
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
                return ['at' => CarbonImmutable::parse($value.' 00:00', $timezone), 'all_day' => true, 'states_zone' => false];
            }

            if (! preg_match('/^\d{4}-\d{2}-\d{2}[T ]\d{2}:\d{2}/', $value)) {
                return null;
            }

            // An offset says where the clock is; "Z" only says when. With neither, it is the
            // clock time the page shows, read in the schedule's zone.
            $hasOffset = (bool) preg_match('/[+-]\d{2}:?\d{2}$/', $value);

            return [
                'at' => CarbonImmutable::parse($value, $timezone),
                'all_day' => false,
                'states_zone' => $hasOffset,
            ];
        } catch (\Throwable $e) {
            return null;
        }
    }

    /** @return array{venue_name: string, event_address: string, event_city: string, event_state: string, event_postal_code: string, event_country_code: string, url: string} */
    private static function location(mixed $location): array
    {
        $result = ['venue_name' => '', 'event_address' => '', 'event_city' => '', 'event_state' => '',
            'event_postal_code' => '', 'event_country_code' => '', 'url' => ''];

        if (is_string($location)) {
            $result['venue_name'] = self::plain($location);

            return $result;
        }

        if (! is_array($location)) {
            return $result;
        }

        // Several places: an in-person one beats the stream link.
        if (array_is_list($location)) {
            $places = array_filter($location, 'is_array');
            usort($places, fn ($a, $b) => (int) in_array('VirtualLocation', (array) ($a['@type'] ?? []), true)
                <=> (int) in_array('VirtualLocation', (array) ($b['@type'] ?? []), true));

            return $places ? self::location(reset($places)) : $result;
        }

        if (in_array('VirtualLocation', (array) ($location['@type'] ?? []), true)) {
            $result['url'] = self::text($location['url'] ?? '');

            return $result;
        }

        $result['venue_name'] = self::plain(self::text($location['name'] ?? ''));

        $address = $location['address'] ?? null;
        if (is_string($address)) {
            $result['event_address'] = self::plain($address);
        } elseif (is_array($address)) {
            $result['event_address'] = self::plain(self::text($address['streetAddress'] ?? ''));
            $result['event_city'] = self::plain(self::text($address['addressLocality'] ?? ''));
            $result['event_state'] = self::plain(self::text($address['addressRegion'] ?? ''));
            $result['event_postal_code'] = self::plain(self::text($address['postalCode'] ?? ''));

            $country = $address['addressCountry'] ?? '';
            $country = is_array($country) ? self::text($country['identifier'] ?? $country['name'] ?? '') : self::text($country);
            // A code, not a name: "United States" would be stored as written.
            if (preg_match('/^[A-Za-z]{2,3}$/', trim($country))) {
                $result['event_country_code'] = strtolower(trim($country));
            }
        }

        return $result;
    }

    /** @return array{url: string, price: string, currency: string} */
    private static function offer(mixed $offers): array
    {
        $result = ['url' => '', 'price' => '', 'currency' => ''];

        foreach (self::listOf($offers) as $offer) {
            if (! is_array($offer)) {
                continue;
            }

            $result['url'] = $result['url'] ?: self::text($offer['url'] ?? '');

            $price = self::text($offer['price'] ?? $offer['lowPrice'] ?? '');
            if ($result['price'] === '' && is_numeric($price) && (float) $price > 0) {
                $result['price'] = $price;
                $result['currency'] = self::text($offer['priceCurrency'] ?? '');
            }
        }

        return $result;
    }

    private static function image(mixed $image): string
    {
        foreach (self::listOf($image) as $candidate) {
            $url = is_array($candidate) ? self::text($candidate['url'] ?? $candidate['contentUrl'] ?? '') : self::text($candidate);
            if ($url !== '') {
                return $url;
            }
        }

        return '';
    }

    /** One value or several, as a list either way. */
    private static function listOf(mixed $value): array
    {
        if ($value === null || $value === '') {
            return [];
        }

        return is_array($value) && array_is_list($value) ? $value : [$value];
    }

    /** A property's value as text. Markup gives a string, a number, or (for a few) a wrapper. */
    private static function text(mixed $value): string
    {
        if (is_array($value)) {
            $value = $value['@value'] ?? $value['name'] ?? (array_is_list($value) ? ($value[0] ?? '') : '');
        }

        return is_scalar($value) ? trim((string) $value) : '';
    }

    /** Names and addresses arrive with entities and the odd tag in them. */
    private static function plain(string $value): string
    {
        return trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($value), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
    }

    private static function details(string $description): string
    {
        $description = trim(html_entity_decode($description, ENT_QUOTES | ENT_HTML5, 'UTF-8'));

        if ($description !== '' && preg_match('/<(p|br|a|b|i|strong|em|ul|ol|li|div|span|h[1-6])\b[^>]*>/i', $description)) {
            return trim(MarkdownUtils::convertHtmlToMarkdown($description));
        }

        return $description;
    }

    /** A link as the page wrote it, made whole against the page's own address. Web links only. */
    private static function absolute(string $link, string $pageUrl): string
    {
        return ImportAddress::absolute($link, $pageUrl);
    }
}
