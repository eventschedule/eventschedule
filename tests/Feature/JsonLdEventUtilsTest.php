<?php

namespace Tests\Feature;

use App\Utils\JsonLdEventUtils;
use Carbon\Carbon;
use Tests\TestCase;

/**
 * JsonLdEventUtils reads the event data a web page publishes about itself. Pure, so every case is
 * a page written out here and read back, as of noon on 10 October 2026 in New York.
 */
class JsonLdEventUtilsTest extends TestCase
{
    private const ZONE = 'America/New_York';

    private const PAGE = 'https://venue.example/whats-on/index.html';

    private function page(array|string ...$blocks): string
    {
        $scripts = array_map(
            fn ($block) => '<script type="application/ld+json">'.(is_string($block) ? $block : json_encode($block)).'</script>',
            $blocks
        );

        return '<html><head><title>What\'s on</title>'.implode("\n", $scripts).'</head><body><h1>Events</h1></body></html>';
    }

    private function event(array $overrides = []): array
    {
        return array_merge([
            '@context' => 'https://schema.org',
            '@type' => 'Event',
            'name' => 'Jazz Night',
            'startDate' => '2026-10-20T20:00:00-04:00',
        ], $overrides);
    }

    private function read(string $html, bool $keepLocalClock = false): array
    {
        return JsonLdEventUtils::read($html, self::PAGE, self::ZONE, $keepLocalClock, Carbon::parse('2026-10-10 12:00', self::ZONE));
    }

    private function rows(string $html, bool $keepLocalClock = false): array
    {
        return $this->read($html, $keepLocalClock)['rows'];
    }

    public function test_events_are_found_wherever_the_page_nests_them(): void
    {
        $rows = $this->rows($this->page(
            $this->event(['name' => 'On its own']),
            [$this->event(['name' => 'In a list', 'startDate' => '2026-10-21T20:00:00-04:00'])],
            ['@context' => 'https://schema.org', '@graph' => [
                ['@type' => 'WebPage', 'name' => 'What\'s on'],
                $this->event(['name' => 'In a graph', 'startDate' => '2026-10-22T20:00:00-04:00']),
            ]],
            ['@type' => 'ItemList', 'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'item' => $this->event(['name' => 'In an item list', 'startDate' => '2026-10-23T20:00:00-04:00'])],
            ]],
            ['@type' => 'EventSeries', 'name' => 'Season', 'subEvent' => [
                $this->event(['name' => 'In a series', 'startDate' => '2026-10-24T20:00:00-04:00']),
            ]],
            ['@type' => 'MusicVenue', 'name' => 'The Hall', 'event' => $this->event(['name' => 'On the venue', 'startDate' => '2026-10-25T20:00:00-04:00'])],
        ));

        $this->assertSame(
            ['On its own', 'In a list', 'In a graph', 'In an item list', 'In a series', 'On the venue'],
            array_column($rows, 'event_name')
        );
    }

    public function test_an_event_type_is_recognised_and_gives_a_category(): void
    {
        $rows = $this->rows($this->page(
            $this->event(['@type' => 'MusicEvent', 'name' => 'A']),
            $this->event(['@type' => ['Event', 'ComedyEvent'], 'name' => 'B', 'startDate' => '2026-10-21T20:00:00-04:00']),
            $this->event(['@type' => 'Festival', 'name' => 'C', 'startDate' => '2026-10-22T20:00:00-04:00']),
            // Not something to attend.
            $this->event(['@type' => 'BroadcastEvent', 'name' => 'D', 'startDate' => '2026-10-23T20:00:00-04:00']),
            ['@type' => 'Organization', 'name' => 'E'],
        ));

        $this->assertSame(['A', 'B', 'C'], array_column($rows, 'event_name'));
        $this->assertSame(['Concerts', 'Art & Culture', 'Parties & Festivals'], array_column($rows, 'category_name'));
    }

    public function test_when_it_is_and_how_long(): void
    {
        $rows = $this->rows($this->page(
            // An offset in another zone, on a venue's schedule: converted to the schedule's.
            $this->event(['name' => 'Offset', 'startDate' => '2026-10-20T20:00:00-07:00', 'endDate' => '2026-10-20T22:30:00-07:00']),
            $this->event(['name' => 'UTC', 'startDate' => '2026-10-22T00:00:00Z', 'duration' => 'PT90M']),
            $this->event(['name' => 'No zone', 'startDate' => '2026-10-23T19:30']),
            $this->event(['name' => 'One day', 'startDate' => '2026-10-24']),
            $this->event(['name' => 'Three days', 'startDate' => '2026-10-26', 'endDate' => '2026-10-28']),
        ));

        $this->assertSame('2026-10-20 23:00', $rows[0]['event_date_time']);
        $this->assertSame(2.5, $rows[0]['event_duration']);
        $this->assertSame('2026-10-21 20:00', $rows[1]['event_date_time']);
        $this->assertSame(1.5, $rows[1]['event_duration']);
        $this->assertSame('2026-10-23 19:30', $rows[2]['event_date_time']);
        $this->assertSame('', $rows[2]['event_duration']);
        $this->assertSame('2026-10-24 00:00', $rows[3]['event_date_time']);
        $this->assertTrue($rows[3]['is_all_day']);
        $this->assertSame(23.983, $rows[3]['event_duration']);
        // The end date is the last day.
        $this->assertSame(71.983, $rows[4]['event_duration']);
    }

    public function test_a_touring_schedule_keeps_the_clock_time_the_page_gives(): void
    {
        $html = $this->page(
            $this->event(['name' => 'Los Angeles', 'startDate' => '2026-10-20T20:00:00-07:00']),
            $this->event(['name' => 'UTC', 'startDate' => '2026-10-22T00:00:00Z']),
        );

        [$la, $utc] = $this->rows($html, keepLocalClock: true);

        $this->assertSame('2026-10-20 20:00', $la['event_date_time']);
        $this->assertSame('-07:00', $la['local_time_zone']);
        $this->assertSame('2026-10-21 20:00', $utc['event_date_time']);
        $this->assertNull($utc['local_time_zone']);
    }

    public function test_where_it_is_in_each_shape_a_page_uses(): void
    {
        $rows = $this->rows($this->page(
            $this->event(['name' => 'A', 'location' => [
                '@type' => 'Place', 'name' => 'The Blue Room &amp; Bar',
                'address' => ['@type' => 'PostalAddress', 'streetAddress' => '12 Main St', 'addressLocality' => 'Austin',
                    'addressRegion' => 'TX', 'postalCode' => '78701', 'addressCountry' => 'US'],
            ]]),
            $this->event(['name' => 'B', 'startDate' => '2026-10-21T20:00:00-04:00', 'location' => [
                '@type' => 'Place', 'name' => 'Town Hall', 'address' => '1 Square, Springfield',
            ]]),
            $this->event(['name' => 'C', 'startDate' => '2026-10-22T20:00:00-04:00', 'location' => 'Somewhere Nice']),
            $this->event(['name' => 'D', 'startDate' => '2026-10-23T20:00:00-04:00', 'location' => [
                '@type' => 'VirtualLocation', 'url' => 'https://stream.example/live',
            ]]),
            $this->event(['name' => 'E', 'startDate' => '2026-10-24T20:00:00-04:00', 'location' => [
                ['@type' => 'VirtualLocation', 'url' => 'https://stream.example/live'],
                ['@type' => 'Place', 'name' => 'In Person Too', 'address' => ['addressCountry' => ['@type' => 'Country', 'name' => 'United States']]],
            ]]),
        ));

        $this->assertSame('The Blue Room & Bar', $rows[0]['venue_name']);
        $this->assertSame('12 Main St', $rows[0]['event_address']);
        $this->assertSame('Austin', $rows[0]['event_city']);
        $this->assertSame('TX', $rows[0]['event_state']);
        $this->assertSame('78701', $rows[0]['event_postal_code']);
        $this->assertSame('us', $rows[0]['event_country_code']);

        $this->assertSame('Town Hall', $rows[1]['venue_name']);
        $this->assertSame('1 Square, Springfield', $rows[1]['event_address']);

        $this->assertSame('Somewhere Nice', $rows[2]['venue_name']);

        $this->assertSame('', $rows[3]['venue_name']);
        $this->assertSame('https://stream.example/live', $rows[3]['registration_url']);

        $this->assertSame('In Person Too', $rows[4]['venue_name']);
        // A country written out is not a code, and is not stored as one.
        $this->assertSame('', $rows[4]['event_country_code']);
    }

    public function test_links_price_picture_and_performers(): void
    {
        $rows = $this->rows($this->page(
            $this->event([
                'name' => 'A',
                'url' => '/whats-on/a',
                'offers' => ['@type' => 'Offer', 'url' => 'tickets/a', 'price' => '25.00', 'priceCurrency' => 'USD'],
                'image' => ['@type' => 'ImageObject', 'url' => '//cdn.example/a.jpg'],
                'performer' => [
                    ['@type' => 'MusicGroup', 'name' => 'The Band', 'sameAs' => 'https://band.example'],
                    ['@type' => 'Person', 'name' => 'A Guest'],
                ],
                'description' => '<p>Doors at <strong>seven</strong>.</p>',
            ]),
            $this->event([
                'name' => 'B', 'startDate' => '2026-10-21T20:00:00-04:00',
                'url' => 'https://venue.example/b',
                'offers' => [
                    ['@type' => 'Offer', 'price' => 0],
                    ['@type' => 'AggregateOffer', 'lowPrice' => 10, 'priceCurrency' => 'EUR'],
                ],
                'image' => ['https://venue.example/b-1.jpg', 'https://venue.example/b-2.jpg'],
                'performer' => 'Solo Act',
                'description' => 'Plain &amp; simple',
            ]),
            $this->event(['name' => 'C', 'startDate' => '2026-10-22T20:00:00-04:00', 'url' => 'javascript:alert(1)']),
        ));

        // The ticket link wins over the event's own page; both are made whole against the page.
        $this->assertSame('https://venue.example/whats-on/tickets/a', $rows[0]['registration_url']);
        $this->assertSame('25.00', $rows[0]['ticket_price']);
        $this->assertSame('USD', $rows[0]['ticket_currency']);
        $this->assertSame('https://cdn.example/a.jpg', $rows[0]['image_url']);
        $this->assertSame(['The Band', 'A Guest'], array_column($rows[0]['performers'], 'name'));
        $this->assertSame('https://band.example', $rows[0]['performers'][0]['website']);
        $this->assertSame('Doors at **seven**.', $rows[0]['event_details']);

        $this->assertSame('https://venue.example/b', $rows[1]['registration_url']);
        // Free is not a price; the first real one is used.
        $this->assertSame('10', $rows[1]['ticket_price']);
        $this->assertSame('EUR', $rows[1]['ticket_currency']);
        $this->assertSame('https://venue.example/b-1.jpg', $rows[1]['image_url']);
        $this->assertSame(['Solo Act'], array_column($rows[1]['performers'], 'name'));
        $this->assertSame('Plain & simple', $rows[1]['event_details']);

        // Only a web link is a link.
        $this->assertSame('', $rows[2]['registration_url']);
        $this->assertArrayNotHasKey('performers', $rows[2]);
        $this->assertNull($rows[2]['image_url']);
    }

    public function test_what_is_over_cancelled_or_unreadable_is_left_out_and_counted(): void
    {
        $result = $this->read($this->page(
            $this->event(['name' => 'Yesterday', 'startDate' => '2026-10-09T20:00:00-04:00']),
            $this->event(['name' => 'This morning', 'startDate' => '2026-10-10T09:00:00-04:00']),
            $this->event(['name' => 'Still on', 'startDate' => '2026-10-08', 'endDate' => '2026-10-11']),
            $this->event(['name' => 'Too far', 'startDate' => '2028-01-01T20:00:00-05:00']),
            $this->event(['name' => 'Called off', 'eventStatus' => 'https://schema.org/EventCancelled']),
            $this->event(['name' => 'No date', 'startDate' => '']),
            $this->event(['name' => '', 'startDate' => '2026-10-20T20:00:00-04:00']),
            $this->event(['name' => 'Odd date', 'startDate' => 'next Friday']),
            // The same event marked up twice is one event.
            $this->event(['name' => 'Twice', 'startDate' => '2026-10-25T20:00:00-04:00']),
            $this->event(['name' => 'twice', 'startDate' => '2026-10-25T20:00:00-04:00']),
        ));

        $this->assertSame(['Still on', 'This morning', 'Twice'], array_column($result['rows'], 'event_name'));
        $this->assertSame(['past' => 2, 'cancelled' => 1, 'unreadable' => 3], $result['skipped']);
    }

    public function test_a_broken_block_does_not_cost_the_good_ones(): void
    {
        $rows = $this->rows($this->page(
            '{ this is not json',
            "<!--\n".json_encode($this->event(['name' => 'In a comment']))."\n-->",
            $this->event(['name' => 'Fine', 'startDate' => '2026-10-21T20:00:00-04:00']),
        ));

        $this->assertSame(['In a comment', 'Fine'], array_column($rows, 'event_name'));
    }

    public function test_a_page_without_event_data_has_no_rows(): void
    {
        $result = $this->read('<html><body><h1>Events</h1><p>Jazz Night, 20 October</p></body></html>');

        $this->assertSame([], $result['rows']);
        $this->assertSame(['past' => 0, 'cancelled' => 0, 'unreadable' => 0], $result['skipped']);
    }
}
