<?php

namespace App\Services\Feeds;

use App\Models\EventFeed;
use App\Services\Feeds\Readers\CalendarReader;
use App\Services\Feeds\Readers\FeedReader;
use App\Services\Feeds\Readers\ItemsReader;
use App\Services\Feeds\Readers\JoliooReader;
use App\Services\Feeds\Readers\PageReader;
use App\Utils\ImportAddress;

/**
 * Which kind of source an address is, and the reader for each kind.
 *
 * Decided once, when a feed is added, from where the address is and what it answered, and kept
 * on the feed: a source does not turn from a calendar into a page, and a feed whose address
 * starts answering something else has failed, not changed kind.
 */
final class FeedKind
{
    /**
     * A provider is known by its address. Everything else by what came back, most exact first:
     * a calendar says when each event is, a feed of posts has to be followed to its pages, and
     * a page is whatever is left that is HTML. Null: none of them.
     */
    public static function detect(string $url, FetchResult $fetched): ?string
    {
        return match (true) {
            JoliooReader::reads($url) => EventFeed::KIND_JOLIOO,
            ImportAddress::isCalendar($fetched->contentType, $fetched->body) => EventFeed::KIND_CALENDAR,
            FeedDocument::parse($fetched->body, $fetched->url) !== null => EventFeed::KIND_ITEMS,
            ImportAddress::isHtml($fetched->contentType, $fetched->body) => EventFeed::KIND_PAGE,
            default => null,
        };
    }

    public static function reader(string $kind): FeedReader
    {
        return match ($kind) {
            EventFeed::KIND_CALENDAR => new CalendarReader,
            EventFeed::KIND_PAGE => new PageReader,
            EventFeed::KIND_ITEMS => new ItemsReader,
            EventFeed::KIND_JOLIOO => new JoliooReader,
            default => throw new \InvalidArgumentException('Unknown feed kind.'),
        };
    }

    /** Whether a source of this kind lists all it has, so that absence from it means gone. */
    public static function canSeeLeaving(string $kind): bool
    {
        return in_array($kind, [EventFeed::KIND_CALENDAR, EventFeed::KIND_PAGE, EventFeed::KIND_JOLIOO], true);
    }
}
