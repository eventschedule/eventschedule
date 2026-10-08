<?php

namespace App\Services\Feeds;

/**
 * What one read of a feed's list found, in the shape every kind of feed comes out as.
 *
 * items: what is there to become events, each
 *   id          the source's own id for it, the same on every read
 *   row         the event as the import's flat row (event_name, event_date_time ...), in the
 *               feed's zone, or null when the list alone does not say when it is and the item's
 *               own page has to be read (detail_url)
 *   list_hash   a hash of what the list says about it, to know cheaply that nothing changed
 *   detail_url  the item's own page, when it has one
 *   hint        what the list knows that its page may not repeat (title, body, picture)
 *
 * seen: every other entry the source holds, by id, with why it is not an item: past, later,
 * cancelled, private, unreadable. An entry that is in neither is not in the source.
 *
 * complete: false when the read stopped early (out of time, a page that could not be had), so
 * that absence means nothing this time.
 *
 * listsEverything: whether this kind of source lists all it has. A calendar does. A feed of
 * posts lists its newest few, so an event missing from it has not gone anywhere.
 *
 * next: the next page's address, when the list is in pages.
 *
 * skipped: the reader's counts of what it left out, for the check before a feed is added.
 */
final class FeedReading
{
    /**
     * @param  list<array{id: string, row: ?array, list_hash: string, detail_url: ?string, hint: array}>  $items
     * @param  array<string, string>  $seen
     * @param  array<string, int>  $skipped
     */
    public function __construct(
        public readonly array $items,
        public readonly array $seen = [],
        public readonly bool $complete = true,
        public readonly bool $listsEverything = true,
        public readonly ?string $next = null,
        public readonly array $skipped = [],
    ) {}

    /**
     * A row's hash: what it says, not where it stands. A date of a series is numbered within
     * its series, and that number moves each time an earlier date passes.
     */
    public static function hashOf(array $row): string
    {
        unset($row['series'], $row['sort_at'], $row['source_id'], $row['source_uid']);
        ksort($row);

        return hash('sha256', json_encode($row, JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR));
    }

    /**
     * Readers that are handed whole rows (a calendar, a page's own event data) wrap them the
     * same way.
     *
     * @param  array{rows: list<array>, skipped: array<string, int>, seen: array<string, string>, complete: bool}  $read
     */
    public static function fromRows(array $read): self
    {
        $items = [];

        foreach ($read['rows'] as $row) {
            $items[] = [
                'id' => (string) $row['source_id'],
                'row' => $row,
                'list_hash' => self::hashOf($row),
                'detail_url' => null,
                'hint' => [],
            ];
        }

        return new self(
            $items,
            array_filter($read['seen'], fn (string $reason) => $reason !== 'listed'),
            $read['complete'],
            true,
            null,
            $read['skipped'],
        );
    }
}
