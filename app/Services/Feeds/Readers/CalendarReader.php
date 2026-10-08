<?php

namespace App\Services\Feeds\Readers;

use App\Services\Feeds\FeedReading;
use App\Services\Feeds\FetchResult;
use App\Utils\IcsImportUtils;
use App\Utils\ImportAddress;

/**
 * A calendar: an .ics or webcal address, or a Google or Outlook share link (which
 * ImportAddress::knownFeedFor() has already turned into its feed).
 *
 * Read by IcsImportUtils, as the link import reads one, with one difference: a repeating entry
 * always arrives as its dates. A feed's window slides, so a listed series never runs out, and
 * each date can be moved, cancelled or gone on its own.
 */
class CalendarReader implements FeedReader
{
    public function read(FetchResult $fetched, string $timezone, bool $keepLocalClock, ?\DateTimeInterface $now = null): ?FeedReading
    {
        if (! ImportAddress::isCalendar($fetched->contentType, $fetched->body)) {
            return null;
        }

        try {
            return FeedReading::fromRows(IcsImportUtils::read($fetched->body, $timezone, $keepLocalClock, $now, true));
        } catch (\InvalidArgumentException $e) {
            return null;
        }
    }

    public function detail(array $item, FetchResult $fetched, string $timezone, bool $keepLocalClock, ?\DateTimeInterface $now = null): array|string
    {
        return 'unreadable';
    }
}
