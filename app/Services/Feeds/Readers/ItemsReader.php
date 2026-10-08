<?php

namespace App\Services\Feeds\Readers;

use App\Services\Feeds\FeedDocument;
use App\Services\Feeds\FeedReading;
use App\Services\Feeds\FeedText;
use App\Services\Feeds\FetchResult;
use App\Utils\ImportAddress;
use App\Utils\JsonLdEventUtils;

/**
 * A feed of posts: RSS, Atom or JSON Feed.
 *
 * Such a feed says what was published and when it was POSTED. When the thing happens is on the
 * page each item links to, so the list gives ids and addresses and every row comes from
 * detail(), which reads that page's own event data. An item whose page says no date is not an
 * event this can add, and is counted.
 *
 * A feed of posts lists its newest few, so an event that is no longer in it has not gone
 * anywhere: listsEverything is false, and whether an event is gone can only be told from its
 * own page.
 */
class ItemsReader implements FeedReader
{
    public function read(FetchResult $fetched, string $timezone, bool $keepLocalClock, ?\DateTimeInterface $now = null): ?FeedReading
    {
        $document = FeedDocument::parse($fetched->body, $fetched->url);

        if (! $document) {
            return null;
        }

        $items = [];

        foreach ($document->items as $entry) {
            $items[] = [
                'id' => $entry['id'],
                'row' => null,
                'list_hash' => hash('sha256', json_encode([$entry['title'], $entry['link'], $entry['html'], $entry['updated'], $entry['image']], JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR)),
                'detail_url' => $entry['link'] !== '' ? $entry['link'] : null,
                'hint' => ['title' => $entry['title'], 'html' => $entry['html'], 'image' => $entry['image'], 'link' => $entry['link']],
            ];
        }

        return new FeedReading($items, [], ! $document->truncated, false, $document->next);
    }

    public function detail(array $item, FetchResult $fetched, string $timezone, bool $keepLocalClock, ?\DateTimeInterface $now = null): array|string
    {
        if (! ImportAddress::isHtml($fetched->contentType, $fetched->body)) {
            return 'unreadable';
        }

        $read = JsonLdEventUtils::read($fetched->body, $fetched->url, $timezone, $keepLocalClock, $now);

        if (! $read['rows']) {
            // The page has an event and it is over, further off than the window, or called
            // off. Otherwise it names no date at all.
            foreach (['cancelled', 'later', 'past'] as $reason) {
                if (in_array($reason, $read['seen'], true)) {
                    return $reason;
                }
            }

            return 'no_date';
        }

        $row = $this->choose($read['rows'], $item, $fetched->url);

        if (! $row) {
            return 'no_date';
        }

        $hint = $item['hint'];

        // What the list knows and the page did not repeat.
        if (($row['event_details'] ?? '') === '' && ($hint['html'] ?? '') !== '') {
            $row['event_details'] = FeedText::details($hint['html'], $fetched->url);
        }
        if (empty($row['image_url']) && ($hint['image'] ?? '') !== '') {
            $row['image_url'] = $hint['image'];
        }
        if (($row['registration_url'] ?? '') === '') {
            $row['registration_url'] = $hint['link'] ?? '';
        }

        $row['source_id'] = $item['id'];

        return $row;
    }

    /**
     * A page can mark up more than the post it is about (a sidebar of what else is on). The
     * post's event is the one that lives at the post's own address. Failing that, the one named
     * like the post. Failing that, the only event on the page, when it does not say it lives
     * somewhere else or its name and the post's overlap.
     *
     * Anything less sure is no event at all. The first on the page is the soonest thing the
     * SITE has on, and the daily re-read wrote its name, time and place over this post's event:
     * one event turned into another.
     *
     * @param  non-empty-list<array>  $rows
     */
    private function choose(array $rows, array $item, string $page): ?array
    {
        $addressOf = fn (array $row) => self::bare(explode('#', (string) ($row['source_id'] ?? ''))[0]);
        $here = array_filter([self::bare((string) ($item['detail_url'] ?? '')), self::bare($page)]);

        $own = array_values(array_filter($rows, fn (array $row) => in_array($addressOf($row), $here, true)));
        if (count($own) === 1) {
            return $own[0];
        }

        $title = mb_strtolower(trim((string) ($item['hint']['title'] ?? '')));
        $nameOf = fn (array $row) => mb_strtolower(trim((string) ($row['event_name'] ?? '')));

        $named = $title === '' ? [] : array_values(array_filter($own ?: $rows, fn (array $row) => $nameOf($row) === $title));
        if (count($named) === 1) {
            return $named[0];
        }

        if (count($rows) === 1) {
            $address = $addressOf($rows[0]);
            $name = $nameOf($rows[0]);
            $elsewhere = $address !== '' && ! str_starts_with($address, 'ld-') && ! in_array($address, $here, true);
            $overlap = $title !== '' && $name !== '' && (str_contains($title, $name) || str_contains($name, $title));

            if (! $elsewhere || $overlap) {
                return $rows[0];
            }
        }

        return null;
    }

    /** An address without what does not say where it is: scheme, www, query, fragment, a last slash. */
    private static function bare(string $url): string
    {
        $url = preg_replace('~^[a-z][a-z0-9+.-]*://(www\.)?~i', '', trim($url));
        $url = preg_replace('~[?#].*$~', '', (string) $url);

        return rtrim(mb_strtolower((string) $url), '/');
    }
}
