<?php

namespace Tests\Feature;

use App\Models\EventFeed;
use App\Services\Feeds\FeedFetcher;
use App\Services\Feeds\FeedKind;
use App\Services\Feeds\FeedReading;
use App\Services\Feeds\FeedText;
use App\Services\Feeds\FetchResult;
use App\Services\Feeds\Readers\CalendarReader;
use App\Services\Feeds\Readers\ItemsReader;
use App\Services\Feeds\Readers\PageReader;
use Carbon\Carbon;
use Tests\TestCase;

/**
 * The kinds of source a feed can be, each read into the same shape. A reader is handed what was
 * fetched and fetches nothing, so these are strings in and readings out.
 */
class FeedReadersTest extends TestCase
{
    private const ZONE = 'America/New_York';

    private function now(): Carbon
    {
        return Carbon::parse('2026-10-10 12:00', self::ZONE);
    }

    private function fetched(string $body, string $type, string $url = 'https://venue.example/whats-on'): FetchResult
    {
        return new FetchResult(FeedFetcher::OK, 200, $body, $type, $url);
    }

    private function calendar(string ...$events): string
    {
        return "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-//Test//EN\r\n"
            .implode('', array_map(fn ($event) => "BEGIN:VEVENT\r\n".str_replace("\n", "\r\n", trim($event))."\r\nEND:VEVENT\r\n", $events))
            ."END:VCALENDAR\r\n";
    }

    private function page(array ...$events): string
    {
        return '<!doctype html><html><head>'.implode('', array_map(
            fn ($event) => '<script type="application/ld+json">'.json_encode($event + ['@context' => 'https://schema.org', '@type' => 'Event']).'</script>',
            $events
        )).'</head><body>What\'s on</body></html>';
    }

    public function test_a_calendar_is_its_events_with_every_series_as_dates(): void
    {
        $reading = (new CalendarReader)->read($this->fetched($this->calendar(
            "UID:one\nSUMMARY:One night\nDTSTART:20261020T230000Z\nDTEND:20261021T010000Z",
            "UID:class\nSUMMARY:Class\nDTSTART;TZID=America/New_York:20261006T190000\nRRULE:FREQ=WEEKLY;COUNT=3",
            "UID:off\nSUMMARY:Called off\nDTSTART:20261022T230000Z\nSTATUS:CANCELLED",
            "UID:far\nSUMMARY:Postponed\nDTSTART;VALUE=DATE:20280101",
        ), 'text/calendar'), self::ZONE, false, $this->now());

        $this->assertInstanceOf(FeedReading::class, $reading);
        // In the order they happen; two at the same moment, by name.
        $this->assertSame(['class#20261013T230000Z', 'class#20261020T230000Z', 'one'], array_column($reading->items, 'id'));
        $this->assertSame(['off' => 'cancelled', 'far' => 'later'], $reading->seen);
        $this->assertTrue($reading->complete);
        $this->assertTrue($reading->listsEverything);
        $this->assertNull($reading->next);

        $one = $reading->items[2];
        $this->assertSame('One night', $one['row']['event_name']);
        $this->assertSame('2026-10-20 19:00', $one['row']['event_date_time']);
        $this->assertNull($one['detail_url']);
        $this->assertSame(64, strlen($one['list_hash']));
    }

    /** What a row says, not where it stands: a date of a series is numbered within it. */
    public function test_a_rows_hash_changes_when_the_event_does_and_not_when_its_neighbours_do(): void
    {
        $series = "UID:class\nSUMMARY:Class\nDTSTART;TZID=America/New_York:20261006T190000\nRRULE:FREQ=WEEKLY;COUNT=4";
        $read = fn (string $calendar, Carbon $now) => array_column(
            (new CalendarReader)->read($this->fetched($calendar, 'text/calendar'), self::ZONE, false, $now)->items,
            'list_hash',
            'id'
        );

        $before = $read($this->calendar($series), $this->now());
        // A week later the first date has passed, and every other is one place earlier in its series.
        $after = $read($this->calendar($series), $this->now()->addWeek());
        $this->assertSame($before['class#20261020T230000Z'], $after['class#20261020T230000Z']);

        $renamed = $read($this->calendar(str_replace('SUMMARY:Class', 'SUMMARY:Class (moved rooms)', $series)), $this->now());
        $this->assertNotSame($before['class#20261020T230000Z'], $renamed['class#20261020T230000Z']);
    }

    public function test_a_page_is_the_events_it_marks_up(): void
    {
        $reading = (new PageReader)->read($this->fetched($this->page(
            ['name' => 'Jazz Night', 'startDate' => '2026-10-20T20:00:00-04:00', '@id' => 'https://venue.example/e/jazz'],
            ['name' => 'Yesterday', 'startDate' => '2026-10-09T20:00:00-04:00', '@id' => 'https://venue.example/e/yesterday'],
            ['name' => 'Called off', 'startDate' => '2026-10-21T20:00:00-04:00', '@id' => 'https://venue.example/e/off', 'eventStatus' => 'https://schema.org/EventCancelled'],
        ), 'text/html; charset=utf-8'), self::ZONE, false, $this->now());

        $this->assertSame(['https://venue.example/e/jazz'], array_column($reading->items, 'id'));
        $this->assertSame(['https://venue.example/e/yesterday' => 'past', 'https://venue.example/e/off' => 'cancelled'], $reading->seen);
        $this->assertTrue($reading->listsEverything);
        $this->assertSame('2026-10-20 20:00', $reading->items[0]['row']['event_date_time']);
    }

    /**
     * A page whose events are all over still has events: it is read, and what it lists is
     * nothing. A page that marks up none is not a source, which a feed must hear as a failed
     * read and never as "everything is gone".
     */
    public function test_a_page_with_no_event_data_is_not_an_empty_feed(): void
    {
        $over = (new PageReader)->read($this->fetched($this->page(['name' => 'Yesterday', 'startDate' => '2026-10-09T20:00:00-04:00']), 'text/html'), self::ZONE, false, $this->now());
        $this->assertSame([], $over->items);
        $this->assertSame(['past'], array_values($over->seen));

        $this->assertNull((new PageReader)->read($this->fetched('<html><body><h1>Under construction</h1></body></html>', 'text/html'), self::ZONE, false, $this->now()));
        $this->assertNull((new PageReader)->read($this->fetched('{"not": "a page"}', 'application/json'), self::ZONE, false, $this->now()));
        $this->assertNull((new CalendarReader)->read($this->fetched('<html><body>Sign in</body></html>', 'text/html'), self::ZONE, false, $this->now()));
    }

    public function test_a_feed_of_posts_gives_ids_and_addresses_and_each_posts_page_gives_the_event(): void
    {
        $reader = new ItemsReader;
        $reading = $reader->read($this->fetched(
            '<?xml version="1.0"?><rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom"><channel><title>News</title>'
            .'<atom:link rel="next" href="https://venue.example/feed?page=2"/>'
            .'<item><guid>post-1</guid><title>Jazz Night</title><link>https://venue.example/posts/jazz</link><description>&lt;p&gt;From the &lt;a href="/menu"&gt;list&lt;/a&gt;.&lt;/p&gt;</description>'
            .'<enclosure url="https://venue.example/jazz.jpg" type="image/jpeg"/><pubDate>Sat, 03 Oct 2026 10:00:00 GMT</pubDate></item>'
            .'<item><guid>post-2</guid><title>No page of its own</title></item>'
            .'</channel></rss>',
            'application/rss+xml',
            'https://venue.example/feed'
        ), self::ZONE, false, $this->now());

        $this->assertSame(['post-1', 'post-2'], array_column($reading->items, 'id'));
        $this->assertSame([null, null], array_column($reading->items, 'row'));
        $this->assertSame(['https://venue.example/posts/jazz', null], array_column($reading->items, 'detail_url'));
        // A feed of posts lists its newest few: an event missing from it has not gone anywhere.
        $this->assertFalse($reading->listsEverything);
        $this->assertSame('https://venue.example/feed?page=2', $reading->next);

        $item = $reading->items[0];
        $post = fn (array ...$events) => $this->fetched($this->page(...$events), 'text/html', 'https://venue.example/posts/jazz');

        // The page says when. What it leaves out, the list fills in.
        $row = $reader->detail($item, $post(
            ['name' => 'Also on this week', 'startDate' => '2026-10-15T20:00:00-04:00'],
            ['name' => 'Jazz Night', 'startDate' => '2026-10-20T20:00:00-04:00'],
        ), self::ZONE, false, $this->now());

        $this->assertSame('Jazz Night', $row['event_name'], 'the event named like the post, not the first on the page');
        $this->assertSame('2026-10-20 20:00', $row['event_date_time']);
        $this->assertSame('post-1', $row['source_id']);
        $this->assertSame('From the [list](https://venue.example/menu).', $row['event_details']);
        $this->assertSame('https://venue.example/jazz.jpg', $row['image_url']);
        $this->assertSame('https://venue.example/posts/jazz', $row['registration_url']);

        // And what the page does say stands.
        $own = $reader->detail($item, $post(['name' => 'Jazz Night', 'startDate' => '2026-10-20T20:00:00-04:00', 'description' => 'From the page.', 'image' => 'https://venue.example/own.jpg', 'url' => 'https://tickets.example/jazz']), self::ZONE, false, $this->now());
        $this->assertSame(['From the page.', 'https://venue.example/own.jpg', 'https://tickets.example/jazz'], [$own['event_details'], $own['image_url'], $own['registration_url']]);

        // A post that is not an event, or one whose event is over or called off, says so.
        $this->assertSame('no_date', $reader->detail($item, $this->fetched('<html><body>Just news</body></html>', 'text/html'), self::ZONE, false, $this->now()));
        $this->assertSame('past', $reader->detail($item, $post(['name' => 'Jazz Night', 'startDate' => '2026-10-01T20:00:00-04:00']), self::ZONE, false, $this->now()));
        $this->assertSame('later', $reader->detail($item, $post(['name' => 'Jazz Night', 'startDate' => '2028-01-01T20:00:00-05:00']), self::ZONE, false, $this->now()));
        $this->assertSame('cancelled', $reader->detail($item, $post(['name' => 'Jazz Night', 'startDate' => '2026-10-20T20:00:00-04:00', 'eventStatus' => 'https://schema.org/EventCancelled']), self::ZONE, false, $this->now()));
        $this->assertSame('unreadable', $reader->detail($item, $this->fetched('%PDF-1.7', 'application/pdf'), self::ZONE, false, $this->now()));
    }

    public function test_a_sources_text_loses_its_pictures_and_keeps_only_web_links(): void
    {
        $this->assertSame(
            'Doors at **seven**. [Tickets](https://venue.example/tickets) and [more](https://other.example/x?a=1&b=2), not here or by mail.',
            FeedText::details(
                '<p>Doors at <b>seven</b>. <img src="https://tracker.example/pixel.gif" alt=""><a href="/tickets">Tickets</a> and <a class="x" href=\'https://other.example/x?a=1&amp;b=2\'>more</a>, '
                .'not <a href="javascript:alert(1)">here</a> or <a href="mailto:a@b.example">by mail</a>.</p><script>alert(1)</script><iframe src="https://x.example"></iframe>',
                'https://venue.example/posts/jazz'
            )
        );
        $this->assertSame('', FeedText::details('  ', 'https://venue.example/'));
        $this->assertSame('Plain & simple', FeedText::details('Plain &amp; simple', 'https://venue.example/'));
    }

    public function test_an_address_is_the_kind_its_answer_says_and_a_provider_is_known_by_where_it_is(): void
    {
        $rss = '<?xml version="1.0"?><rss version="2.0"><channel><title>News</title></channel></rss>';

        $this->assertSame(EventFeed::KIND_CALENDAR, FeedKind::detect('https://venue.example/cal', $this->fetched($this->calendar(), 'application/octet-stream')));
        $this->assertSame(EventFeed::KIND_ITEMS, FeedKind::detect('https://venue.example/feed', $this->fetched($rss, 'text/xml')));
        $this->assertSame(EventFeed::KIND_ITEMS, FeedKind::detect('https://venue.example/feed.json', $this->fetched('{"version": "https://jsonfeed.org/version/1.1", "items": []}', 'application/json')));
        $this->assertSame(EventFeed::KIND_PAGE, FeedKind::detect('https://venue.example/whats-on', $this->fetched($this->page(['name' => 'x', 'startDate' => '2026-10-20']), 'text/html')));
        $this->assertNull(FeedKind::detect('https://venue.example/file.pdf', $this->fetched('%PDF-1.7', 'application/pdf')));

        // The same RSS at a provider's address is the provider's.
        $this->assertSame(EventFeed::KIND_JOLIOO, FeedKind::detect('https://app.jolioo.com/rss/ddcb37445354873e', $this->fetched($rss, 'text/xml')));
        $this->assertSame(EventFeed::KIND_JOLIOO, FeedKind::detect('https://app.jolioo.com/rss/ddcb37445354873e/4', $this->fetched($rss, 'text/xml')));
        $this->assertSame(EventFeed::KIND_ITEMS, FeedKind::detect('https://app.jolioo.com.evil.example/rss/ddcb37445354873e', $this->fetched($rss, 'text/xml')));
        $this->assertSame(EventFeed::KIND_ITEMS, FeedKind::detect('https://app.jolioo.com/blog/feed', $this->fetched($rss, 'text/xml')));

        foreach (EventFeed::KINDS as $kind) {
            $this->assertNotNull(FeedKind::reader($kind));
        }
        $this->assertTrue(FeedKind::canSeeLeaving(EventFeed::KIND_CALENDAR));
        $this->assertFalse(FeedKind::canSeeLeaving(EventFeed::KIND_ITEMS));
    }
}
