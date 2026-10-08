<?php

namespace App\Services\Feeds\Readers;

use App\Services\Feeds\FeedDocument;
use App\Services\Feeds\FeedReading;
use App\Services\Feeds\FeedText;
use App\Services\Feeds\FeedTime;
use App\Services\Feeds\FetchResult;
use App\Utils\IcsImportUtils;
use App\Utils\ImportAddress;
use App\Utils\ImportedTime;
use Carbon\CarbonImmutable;

/**
 * Jolioo (app.jolioo.com), from its "RSS Feed & Posting Schnittstelle" v1.0 of 27.4.2023.
 *
 * The list is an RSS feed at /rss/<token>, in pages (/rss/<token>/2, /3 ...). It holds every
 * kind of post. Each item's guid ends /item/<id>.xml, and the same address ending .json answers
 * with the post itself: a `posting` whose `post_message_type` says what it is. Only `event` is
 * read. An event has `post_message_start`, `post_message_end` (not to be shown when
 * `post_message_end_hidden`), `post_message_location`, a title, an HTML body and pictures.
 *
 * PROVISIONAL. The documentation's one worked example is a news post. It says an event has a
 * start and an end and not how they are written, in which zone, or what a deleted post's
 * address answers. So a time is read in the shapes FeedTime knows, on the feed's own zone when
 * it names none, and one that cannot be read is counted and not guessed at. The first live
 * feed with a real event record will say which of this to firm up (plan, step 25).
 *
 * A post's page is only ever asked on the host the feed itself is on.
 */
class JoliooReader implements FeedReader
{
    private const HOSTS = ['app.jolioo.com'];

    /** How far ahead events are taken, as the other readers do. */
    private const WINDOW_DAYS = 365;

    /** Whether an address is a Jolioo feed, by where it is. */
    public static function reads(string $url): bool
    {
        return in_array(strtolower((string) parse_url($url, PHP_URL_HOST)), self::HOSTS, true)
            && (bool) preg_match('#^/rss/[A-Za-z0-9]+(/\d+)?/?$#', (string) parse_url($url, PHP_URL_PATH));
    }

    public function read(FetchResult $fetched, string $timezone, bool $keepLocalClock, ?\DateTimeInterface $now = null): ?FeedReading
    {
        $document = FeedDocument::parse($fetched->body, $fetched->url);

        if (! $document || $document->format !== FeedDocument::RSS) {
            return null;
        }

        $host = strtolower((string) parse_url($fetched->url, PHP_URL_HOST));
        $items = [];
        $seen = [];

        foreach ($document->items as $entry) {
            // The post's number, from the address the item is known by.
            if (! preg_match('#/item/(\d+)\.(?:xml|json|html)(?:$|[?\#])#', $entry['id'].' ', $m)
                && ! preg_match('#/item/(\d+)\.(?:xml|json|html)(?:$|[?\#])#', $entry['link'], $m)) {
                $seen[$entry['id']] = 'unreadable';

                continue;
            }

            $detail = preg_replace('#\.(?:xml|html)(?=$|[?\#])#', '.json', trim($entry['id']));
            // Only on the feed's own host: a guid that points anywhere else is not followed.
            if (strtolower((string) parse_url((string) $detail, PHP_URL_HOST)) !== $host) {
                $detail = null;
            }

            $items[] = [
                'id' => $m[1],
                'row' => null,
                'list_hash' => hash('sha256', json_encode([$entry['title'], $entry['html'], $entry['image'], $entry['published']], JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR)),
                'detail_url' => $detail,
                'hint' => ['title' => $entry['title'], 'html' => $entry['html'], 'image' => $entry['image'], 'link' => $entry['link']],
            ];
        }

        return new FeedReading($items, $seen, ! $document->truncated, true, $items ? $this->nextPage($fetched->url) : null);
    }

    /** /rss/<token> is page one; /rss/<token>/4 is page four. Null when the address is neither. */
    private function nextPage(string $url): ?string
    {
        $path = (string) parse_url($url, PHP_URL_PATH);

        if (! preg_match('#^(/rss/[A-Za-z0-9]+)(?:/(\d+))?/?$#', $path, $m)) {
            return null;
        }

        $origin = parse_url($url, PHP_URL_SCHEME).'://'.parse_url($url, PHP_URL_HOST);

        return $origin.$m[1].'/'.(max(1, (int) ($m[2] ?? 1)) + 1);
    }

    public function detail(array $item, FetchResult $fetched, string $timezone, bool $keepLocalClock, ?\DateTimeInterface $now = null): array|string
    {
        $data = json_decode(ltrim($fetched->body, "\xEF\xBB\xBF \t\r\n"), true);
        $posting = is_array($data) ? ($data['posting'] ?? null) : null;

        if (! is_array($posting)) {
            return 'unreadable';
        }

        if (strtolower(trim((string) ($posting['post_message_type'] ?? ''))) !== 'event') {
            return 'not_event';
        }

        $name = trim(preg_replace('/\s+/u', ' ', strip_tags((string) ($posting['post_message_title'] ?? ($item['hint']['title'] ?? '')))) ?? '');
        $start = FeedTime::parse($posting['post_message_start'] ?? null, $timezone);

        if ($name === '' || $start === null) {
            return 'no_date';
        }

        // An end that is not after the start gives no length: hoursBetween() answers null for it.
        $end = empty($posting['post_message_end_hidden']) ? FeedTime::parse($posting['post_message_end'] ?? null, $timezone) : null;

        if ($start['all_day']) {
            // An end DATE is the last day, inclusive.
            $days = $end ? max(1, (int) round(($end['at']->getTimestamp() - $start['at']->getTimestamp()) / 86400) + ($end['all_day'] ? 1 : 0)) : 1;
            $duration = ImportedTime::allDayDuration($days);
            $endsAt = $start['at']->getTimestamp() + (int) round($duration * 3600);
        } else {
            $duration = $end ? (ImportedTime::hoursBetween($start['at'], $end['at']) ?? '') : '';
            $endsAt = $end ? $end['at']->getTimestamp() : null;
        }

        // Over, or further off than is taken: said, so the item is not asked again every hour.
        $now = CarbonImmutable::instance($now ?? now())->setTimezone($timezone);
        $from = $now->startOfDay();
        $upcoming = $start['at'] >= $from || ($endsAt !== null && $endsAt >= $now->getTimestamp());
        if (! $upcoming) {
            return 'past';
        }
        if ($start['at'] > $from->addDays(self::WINDOW_DAYS)) {
            return 'later';
        }

        [$placed, $otherZone] = $start['all_day']
            ? [\Carbon\CarbonImmutable::instance($start['at']), null]
            : ImportedTime::place($start['at'], $start['states_zone'], $timezone, $keepLocalClock);
        $location = IcsImportUtils::location((string) ($posting['post_message_location'] ?? ''));
        $media = array_values(array_filter((array) ($posting['post_message_media'] ?? []), 'is_string'));
        $link = (string) ($item['hint']['link'] ?? '');

        return [
            'event_name' => $name,
            'event_details' => FeedText::details((string) ($posting['post_message_content'] ?? ($item['hint']['html'] ?? '')), $fetched->url),
            'event_date_time' => $placed->format('Y-m-d H:i'),
            'event_duration' => $duration,
            'venue_name' => $location['venue_name'],
            'event_address' => $location['event_address'],
            'registration_url' => ImportAddress::absolute($link, $fetched->url) ?: $location['url'],
            'link_from_location' => ! ImportAddress::absolute($link, $fetched->url) && ! empty($location['url']),
            'category_name' => '',
            'image_url' => ImportAddress::absolute($media[0] ?? (string) ($item['hint']['image'] ?? ''), $fetched->url) ?: null,
            'is_all_day' => $start['all_day'],
            'local_time_zone' => $otherZone,
            'sort_at' => $placed->format('Y-m-d H:i'),
            'recurrence' => null,
            'series' => null,
            'organizer_name' => trim((string) ($posting['team']['persondata_company'] ?? '')),
            // The source's own words for the time, shown beside ours when a feed is checked.
            'source_time' => is_scalar($posting['post_message_start'] ?? null) ? (string) $posting['post_message_start'] : '',
            'source_id' => $item['id'],
        ];
    }
}
