<?php

namespace App\Services\Feeds\Readers;

use App\Services\Feeds\FeedReading;
use App\Services\Feeds\FetchResult;

/**
 * One kind of source a feed can be. A reader is handed what was fetched and says what is in it;
 * it fetches nothing itself, so every address goes through the one guarded fetcher and every
 * reader can be tested with a string.
 */
interface FeedReader
{
    /**
     * Read the list.
     *
     * @param  string  $timezone  The feed's zone: the one an unzoned time is read in, and the
     *                            one the rows' wall-clock times are in.
     * @param  bool  $keepLocalClock  False for a venue schedule: see ImportedTime::place().
     * @return ?FeedReading Null when the document is not one this reader reads. That is a
     *                      failed read, never an empty feed.
     */
    public function read(FetchResult $fetched, string $timezone, bool $keepLocalClock, ?\DateTimeInterface $now = null): ?FeedReading;

    /**
     * Read one item's own page into its row, for a reader whose list gave `row: null`.
     *
     * @param  array{id: string, detail_url: ?string, hint: array}  $item
     * @return array|string The row, or why there is none: `not_event` (the item is something
     *                      else, and stays that), `no_date` (an event whose time could not be
     *                      read), `unreadable` (the page was not what was expected).
     */
    public function detail(array $item, FetchResult $fetched, string $timezone, bool $keepLocalClock, ?\DateTimeInterface $now = null): array|string;
}
