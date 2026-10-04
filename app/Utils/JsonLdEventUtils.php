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

    /**
     * @param  string  $pageUrl  The address the HTML was finally served from, for relative links.
     * @param  bool  $keepLocalClock  False for a venue schedule: see ImportedTime::place().
     * @return array{rows: list<array>, skipped: array{past: int, cancelled: int, unreadable: int}}
     */
    public static function read(string $html, string $pageUrl, string $timezone, bool $keepLocalClock, ?\DateTimeInterface $now = null): array
    {
        $now = CarbonImmutable::instance($now ?? now())->setTimezone($timezone);
        $from = $now->startOfDay();
        $to = $from->addDays(self::WINDOW_DAYS);

        $nodes = [];
        if (preg_match_all('#<script\b[^>]*type\s*=\s*(["\']?)application/ld\+json\1[^>]*>(.*?)</script>#is', $html, $blocks)) {
            foreach ($blocks[2] as $block) {
                // Some pages wrap the JSON in a comment or CDATA; none of that is JSON.
                $block = trim(preg_replace('#^\s*(<!--|//\s*<!\[CDATA\[|<!\[CDATA\[)|(-->|//\s*\]\]>|\]\]>)\s*$#', '', trim($block)));
                $data = json_decode($block, true);
                if (is_array($data)) {
                    self::collect($data, $nodes, 0);
                }
            }
        }

        $rows = [];
        $seen = [];
        $skipped = ['past' => 0, 'cancelled' => 0, 'unreadable' => 0];

        foreach ($nodes as $node) {
            if (str_contains((string) self::text($node['eventStatus'] ?? ''), 'EventCancelled')) {
                $skipped['cancelled']++;

                continue;
            }

            $row = self::row($node, $pageUrl, $timezone, $keepLocalClock);
            if ($row === null) {
                $skipped['unreadable']++;

                continue;
            }

            // The same event marked up twice on one page (a list and a detail block) is one event.
            $key = mb_strtolower($row['event_name']).'|'.$row['event_date_time'];
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;

            $startsOn = substr($row['sort_at'], 0, 10);
            $endsAt = $row['ends_at'];
            unset($row['ends_at']);

            $upcoming = $startsOn >= $from->format('Y-m-d') || ($endsAt !== null && $endsAt >= $now->getTimestamp());
            if (! $upcoming || $startsOn > $to->format('Y-m-d')) {
                $skipped['past']++;

                continue;
            }

            $rows[] = $row;
        }

        usort($rows, fn ($a, $b) => [$a['sort_at'], $a['event_name']] <=> [$b['sort_at'], $b['event_name']]);

        return ['rows' => $rows, 'skipped' => $skipped];
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

        $types = array_map('strval', (array) ($data['@type'] ?? []));

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

        [$placed, $otherZone] = ImportedTime::place($start['at'], $start['states_zone'], $timezone, $keepLocalClock);

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
        $link = trim($link);
        if ($link === '') {
            return '';
        }

        if (preg_match('#^https?://#i', $link)) {
            return $link;
        }

        $base = parse_url($pageUrl);
        if (empty($base['scheme']) || empty($base['host']) || preg_match('#^[a-z][a-z0-9+.-]*:#i', $link)) {
            return '';
        }

        $origin = $base['scheme'].'://'.$base['host'].(isset($base['port']) ? ':'.$base['port'] : '');

        if (str_starts_with($link, '//')) {
            return $base['scheme'].':'.$link;
        }

        if (str_starts_with($link, '/')) {
            return $origin.$link;
        }

        $directory = preg_replace('#/[^/]*$#', '/', $base['path'] ?? '/');

        return $origin.$directory.$link;
    }
}
