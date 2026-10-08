<?php

namespace Tests\Feature;

use App\Services\Feeds\FeedFetcher;
use App\Services\Feeds\FetchResult;
use App\Services\Feeds\Readers\JoliooReader;
use Carbon\Carbon;
use Tests\TestCase;

/**
 * Jolioo, read from its "RSS Feed & Posting Schnittstelle" v1.0 (27.4.2023): the list is the
 * RSS the documentation shows, and a post is the JSON it shows, with the event fields its
 * attribute list names.
 *
 * The documentation's worked example is a news post, so an event's times here are in the
 * shapes such a field is written in. What is held is what must be true whichever of them the
 * live feed turns out to use: only events are taken, a time that cannot be read is counted
 * and not guessed, an unzoned time is the feed's own clock, and a post's page is only ever
 * asked on the feed's own host.
 */
class JoliooReaderTest extends TestCase
{
    private const ZONE = 'Europe/Vienna';

    private const FEED = 'https://app.jolioo.com/rss/ddcb37445354873e9c0685b87ee2c1afddf614409ae329fa1b2eaa16b5b26d2f';

    private function now(): Carbon
    {
        return Carbon::parse('2026-04-20 12:00', self::ZONE);
    }

    private function fetched(string $body, string $url = self::FEED, string $type = 'application/rss+xml'): FetchResult
    {
        return new FetchResult(FeedFetcher::OK, 200, $body, $type, $url);
    }

    /** An item exactly as the documentation prints one. */
    private function item(int $id, string $title, string $guidBase = self::FEED): string
    {
        return '<item><title>'.$title.'</title><link>'.self::FEED.'/item/'.$id.'.html</link>'
            .'<description>Jetzt ist sie da, kommt vorbei.</description><author>Retailer Shop, Demo</author>'
            .'<enclosure url="https://public.jolioo.com/generated/h1000w1000m1/static/files/22f/f832593e.jpeg" length="183387" type="image/jpeg" />'
            .'<guid>'.$guidBase.'/item/'.$id.'.xml</guid><pubDate>Wed, 26 Apr 2023 16:58:52 GMT</pubDate></item>';
    }

    private function list(string ...$items): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?><rss version="2.0"><channel><title>Retailer Demo Website Widget</title>'.implode('', $items).'</channel></rss>';
    }

    /** A posting as the documentation's JSON prints one, with the event fields it lists. */
    private function posting(array $overrides = []): string
    {
        return json_encode(['posting' => $overrides + [
            'post_message_id' => 4686933,
            'post_message_type' => 'event',
            'post_message_process_time' => '2026-04-01 16:58:52',
            'post_message_media' => [
                'https://public.jolioo.com/generated/h1000w1000m1/static/files/22f/f832593e.jpeg',
                'https://public.jolioo.com/generated/h1000w1000m1/static/files/379/cb56726f.jpg',
            ],
            'team' => ['team_id' => 3, 'persondata_company' => 'Demo', 'persondata_picture' => 'https://public.jolioo.com/logo.png'],
            'post_message_title' => 'Frühlingskonzert',
            'post_message_content' => '<p>Kommt vorbei! <img src="https://public.jolioo.com/x.jpg"> <a href="/mehr">Mehr</a></p>',
            'post_message_location' => 'Stadtsaal Voitsberg, Conrad-von-Hötzendorf-Straße 25, 8570 Voitsberg',
            'post_message_start' => '2026-05-09 19:30:00',
            'post_message_end' => '2026-05-09 22:00:00',
            'post_message_end_hidden' => false,
            'post_message_duration_formated' => '19:30 - 22:00',
        ]]);
    }

    private function detail(array $overrides = [], ?array $item = null): array|string
    {
        return (new JoliooReader)->detail(
            $item ?? ['id' => '4686933', 'detail_url' => self::FEED.'/item/4686933.json', 'hint' => [
                'title' => 'From the list', 'html' => 'From the list', 'image' => 'https://public.jolioo.com/list.jpeg', 'link' => self::FEED.'/item/4686933.html',
            ]],
            $this->fetched($this->posting($overrides), self::FEED.'/item/4686933.json', 'application/json'),
            self::ZONE,
            true,
            $this->now()
        );
    }

    public function test_the_list_gives_each_posts_number_and_where_its_json_is(): void
    {
        $reading = (new JoliooReader)->read($this->fetched($this->list(
            $this->item(4686933, 'Neue Kollektion ist da!'),
            $this->item(4686940, 'Frühlingskonzert'),
            // A guid that points somewhere else is known by its number and never followed.
            $this->item(4686941, 'Elsewhere', 'https://evil.example/rss/abc'),
            '<item><title>No address at all</title><guid>something-else</guid></item>',
        )), self::ZONE, true, $this->now());

        $this->assertSame(['4686933', '4686940', '4686941'], array_column($reading->items, 'id'));
        $this->assertSame([
            self::FEED.'/item/4686933.json',
            self::FEED.'/item/4686940.json',
            null,
        ], array_column($reading->items, 'detail_url'));
        $this->assertSame([null, null, null], array_column($reading->items, 'row'));
        $this->assertSame(['something-else' => 'unreadable'], $reading->seen);
        $this->assertSame('Neue Kollektion ist da!', $reading->items[0]['hint']['title']);
        $this->assertSame(self::FEED.'/item/4686933.html', $reading->items[0]['hint']['link']);
        $this->assertTrue($reading->listsEverything);
    }

    /** /rss/<token> is page one, /rss/<token>/4 is page four, and a page with nothing on it is the last. */
    public function test_the_pages_are_walked_until_one_is_empty(): void
    {
        $reader = new JoliooReader;
        $page = fn (string $url, string ...$items) => $reader->read($this->fetched($this->list(...$items), $url), self::ZONE, true, $this->now())->next;

        $this->assertSame(self::FEED.'/2', $page(self::FEED, $this->item(1, 'a')));
        $this->assertSame(self::FEED.'/5', $page(self::FEED.'/4', $this->item(1, 'a')));
        $this->assertNull($page(self::FEED.'/5'));

        $this->assertTrue(JoliooReader::reads(self::FEED));
        $this->assertTrue(JoliooReader::reads(self::FEED.'/4'));
        $this->assertFalse(JoliooReader::reads('https://app.jolioo.com/'));
        $this->assertFalse(JoliooReader::reads(self::FEED.'/item/4686933.json'));
        $this->assertFalse(JoliooReader::reads('https://app.jolioo.com.evil.example/rss/abc'));
        $this->assertNull($reader->read($this->fetched('<html><body>Login</body></html>', self::FEED, 'text/html'), self::ZONE, true, $this->now()));
    }

    public function test_an_event_post_becomes_a_row(): void
    {
        $row = $this->detail();

        $this->assertSame('Frühlingskonzert', $row['event_name']);
        $this->assertSame('2026-05-09 19:30', $row['event_date_time']);
        $this->assertSame(2.5, $row['event_duration']);
        $this->assertFalse($row['is_all_day']);
        $this->assertSame('Stadtsaal Voitsberg', $row['venue_name']);
        $this->assertSame('Conrad-von-Hötzendorf-Straße 25, 8570 Voitsberg', $row['event_address']);
        // The picture is the post's first; the one in its text is dropped, and its link is made whole.
        $this->assertSame('https://public.jolioo.com/generated/h1000w1000m1/static/files/22f/f832593e.jpeg', $row['image_url']);
        $this->assertSame('Kommt vorbei! [Mehr](https://app.jolioo.com/mehr)', preg_replace('/\s+/', ' ', $row['event_details']));
        $this->assertSame(self::FEED.'/item/4686933.html', $row['registration_url']);
        $this->assertSame('Demo', $row['organizer_name']);
        $this->assertSame('2026-05-09 19:30:00', $row['source_time']);
        $this->assertSame('4686933', $row['source_id']);
        $this->assertNull($row['local_time_zone']);
    }

    public function test_only_events_are_taken(): void
    {
        foreach (['newsinfo', 'coupon', 'deal', '', null] as $type) {
            $this->assertSame('not_event', $this->detail(['post_message_type' => $type]), var_export($type, true));
        }

        $this->assertIsArray($this->detail(['post_message_type' => ' Event ']));
    }

    /**
     * The documentation does not say how a start is written. Each of these is one way such a
     * field is, and each has to come out as the same evening in Voitsberg.
     */
    public function test_the_start_is_read_however_it_is_written_and_on_the_feeds_clock_when_it_names_none(): void
    {
        foreach ([
            '2026-05-09 19:30:00', '2026-05-09 19:30', '2026-05-09T19:30:00', '09.05.2026 19:30',
            '2026-05-09T19:30:00+02:00', '2026-05-09T17:30:00Z', 1778347800,
        ] as $written) {
            $row = $this->detail(['post_message_start' => $written, 'post_message_end' => null]);

            $this->assertSame('2026-05-09 19:30', $row['event_date_time'], var_export($written, true));
            $this->assertSame('', $row['event_duration'], 'no end, no length');
        }

        foreach (['', null, 'Sa, 9. Mai, 19:30 Uhr', '05/09/2026 19:30'] as $unreadable) {
            $this->assertSame('no_date', $this->detail(['post_message_start' => $unreadable]), var_export($unreadable, true));
        }
        $this->assertSame('no_date', $this->detail(['post_message_title' => '  ', 'post_message_start' => '2026-05-09 19:30:00'], ['id' => '1', 'detail_url' => null, 'hint' => []]));
    }

    public function test_an_end_that_is_hidden_or_not_after_the_start_gives_no_length(): void
    {
        $this->assertSame('', $this->detail(['post_message_end_hidden' => true])['event_duration']);
        $this->assertSame('', $this->detail(['post_message_end' => '2026-05-09 19:30:00'])['event_duration']);
        $this->assertSame('', $this->detail(['post_message_end' => '2026-05-09 18:00:00'])['event_duration']);
        $this->assertSame(26.5, $this->detail(['post_message_end' => '2026-05-10 22:00:00'])['event_duration']);
    }

    public function test_a_day_with_no_time_is_a_whole_day_and_two_days_are_two(): void
    {
        $one = $this->detail(['post_message_start' => '2026-05-09', 'post_message_end' => null]);
        $this->assertTrue($one['is_all_day']);
        $this->assertSame('2026-05-09 00:00', $one['event_date_time']);
        $this->assertSame(23.983, $one['event_duration']);

        $two = $this->detail(['post_message_start' => '09.05.2026', 'post_message_end' => '10.05.2026']);
        $this->assertSame(47.983, $two['event_duration']);
    }

    /** Said, so that a post which will never be an event to add is not asked again every hour. */
    public function test_an_event_that_is_over_or_too_far_off_says_which(): void
    {
        $this->assertSame('past', $this->detail(['post_message_start' => '2026-04-19 19:30:00', 'post_message_end' => '2026-04-19 22:00:00']));
        $this->assertSame('later', $this->detail(['post_message_start' => '2027-06-01 19:30:00', 'post_message_end' => null]));
        // Earlier today, and one that began yesterday and is still on.
        $this->assertIsArray($this->detail(['post_message_start' => '2026-04-20 09:00:00', 'post_message_end' => '2026-04-20 10:00:00']));
        $this->assertIsArray($this->detail(['post_message_start' => '2026-04-19 20:00:00', 'post_message_end' => '2026-04-20 20:00:00']));
    }

    public function test_what_is_not_a_posting_is_unreadable_and_what_the_post_lacks_the_list_fills_in(): void
    {
        $reader = new JoliooReader;
        $item = ['id' => '1', 'detail_url' => null, 'hint' => ['title' => 'From the list', 'html' => '<p>List text</p>', 'image' => 'https://public.jolioo.com/list.jpeg', 'link' => '']];

        foreach (['', 'not json', '[]', '{"posting": "no"}', '<data><posting/></data>'] as $body) {
            $this->assertSame('unreadable', $reader->detail($item, $this->fetched($body, self::FEED.'/item/1.json', 'application/json'), self::ZONE, true, $this->now()), $body);
        }

        $bare = $reader->detail($item, $this->fetched(json_encode(['posting' => [
            'post_message_type' => 'event', 'post_message_start' => '2026-05-09 19:30:00',
        ]]), self::FEED.'/item/1.json', 'application/json'), self::ZONE, true, $this->now());

        $this->assertSame('From the list', $bare['event_name']);
        $this->assertSame('List text', $bare['event_details']);
        $this->assertSame('https://public.jolioo.com/list.jpeg', $bare['image_url']);
        $this->assertSame('', $bare['venue_name']);
        $this->assertSame('', $bare['registration_url']);
        $this->assertSame('', $bare['organizer_name']);
    }
}
