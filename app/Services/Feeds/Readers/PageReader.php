<?php

namespace App\Services\Feeds\Readers;

use App\Services\Feeds\FeedReading;
use App\Services\Feeds\FetchResult;
use App\Utils\ImportAddress;
use App\Utils\JsonLdEventUtils;

/**
 * A web page that publishes event data about itself (schema.org JSON-LD), read by
 * JsonLdEventUtils as the link import reads one. Never by the model: a feed is read every hour
 * with nobody there to look the result over.
 *
 * A page that marks up no event at all is not a page this reads, which is different from one
 * whose events are all over.
 */
class PageReader implements FeedReader
{
    public function read(FetchResult $fetched, string $timezone, bool $keepLocalClock, ?\DateTimeInterface $now = null): ?FeedReading
    {
        if (! ImportAddress::isHtml($fetched->contentType, $fetched->body)) {
            return null;
        }

        $read = JsonLdEventUtils::read($fetched->body, $fetched->url, $timezone, $keepLocalClock, $now);

        if (! $read['rows'] && ! $read['seen'] && ! array_sum($read['skipped'])) {
            return null;
        }

        return FeedReading::fromRows($read);
    }

    public function detail(array $item, FetchResult $fetched, string $timezone, bool $keepLocalClock, ?\DateTimeInterface $now = null): array|string
    {
        return 'unreadable';
    }
}
