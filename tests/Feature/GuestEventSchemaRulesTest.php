<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventVideo;
use App\Models\Role;
use App\Utils\PlatformCurrency;
use App\Utils\SeoUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The guest event page's Event node (Event::schemaNode()) says what the page shows and offers, and
 * nothing it does not.
 *
 * A crawl of production's event pages found an invented price-0 offer on 90% of them (1,057 of
 * those beside isAccessibleForFree: false), addresses of only a country, events with no venue
 * "located" at their organizer, online events that were never a VirtualLocation, a quarter
 * described as "{name} - Event", ticket offers named after a column that does not exist, and paid
 * types a free plan cannot sell published as offers. These pin each branch of the rules that
 * replaced them, and the one that must never move: events.event_url is the private join link and
 * appears nowhere in the page.
 */
class GuestEventSchemaRulesTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private const OFFLINE = 'https://schema.org/OfflineEventAttendanceMode';

    private const ONLINE = 'https://schema.org/OnlineEventAttendanceMode';

    private const MIXED = 'https://schema.org/MixedEventAttendanceMode';

    private const IN_STOCK = 'https://schema.org/InStock';

    private const SOLD_OUT = 'https://schema.org/SoldOut';

    protected function setUp(): void
    {
        parent::setUp();

        // Role's saving hook geocodes a new address whenever a Google key is configured, which a
        // developer's .env may carry: the coordinates and addresses below are the fixture's own.
        config(['services.google.backend' => null]);
    }

    // ---------------------------------------------------------------------------------------
    // location
    // ---------------------------------------------------------------------------------------

    public function test_a_venue_event_is_in_person_at_a_place_with_its_full_address(): void
    {
        $venue = $this->venue();
        $event = $this->createEvent($venue, ['creator_role_id' => $venue->id]);

        [$node, $html] = $this->eventPage($venue, $event);

        $this->assertSame(self::OFFLINE, $node['eventAttendanceMode']);
        $this->assertSame([
            '@type' => 'Place',
            '@id' => $venue->getCanonicalUrl().'#schedule',
            'name' => 'The Blue Room',
            'url' => $venue->getCanonicalUrl(),
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => '123 Main St',
                'addressLocality' => 'Springfield',
                'addressRegion' => 'IL',
                'postalCode' => '62701',
                'addressCountry' => 'US',
            ],
            'geo' => ['@type' => 'GeoCoordinates', 'latitude' => 39.7817, 'longitude' => -89.6501],
        ], $node['location']);

        // The page's canonical, and no @id: the series page and each dated page of a recurring
        // event describe different occurrences, so one id would name two start dates.
        $this->assertSame($this->canonical($html), $node['url']);
        $this->assertArrayNotHasKey('@id', $node);
    }

    public function test_an_online_event_is_a_virtual_location_at_its_own_page_never_its_join_link(): void
    {
        $talent = $this->createRole($this->createOwner(), 'talent', ['name' => 'Stream Talent']);
        $event = $this->createEvent($talent, [
            'creator_role_id' => $talent->id,
            'event_url' => 'https://zoom.us/j/5550001234?pwd=TopSecretJoinCode',
        ]);

        [$node, $html] = $this->eventPage($talent, $event);

        $this->assertSame(self::ONLINE, $node['eventAttendanceMode']);
        $this->assertSame(['@type' => 'VirtualLocation', 'url' => $this->canonical($html)], $node['location']);

        // The join link is private: the page shows its domain and nothing more, anywhere.
        $this->assertStringNotContainsString('5550001234', $html);
        $this->assertStringNotContainsString('TopSecretJoinCode', $html);

        // Stored without its scheme, the link had no host for parse_url(), so the WHOLE link came
        // back as its "domain" and the page printed it. The web form only checks it is a string.
        $bare = $this->createEvent($talent, [
            'creator_role_id' => $talent->id,
            'event_url' => 'zoom.us/j/5550009876?pwd=AnotherSecretCode',
        ]);

        [$node, $html] = $this->eventPage($talent, $bare);

        $this->assertSame(['@type' => 'VirtualLocation', 'url' => $this->canonical($html)], $node['location']);
        $this->assertStringContainsString('zoom.us', $html);
        $this->assertStringNotContainsString('5550009876', $html);
        $this->assertStringNotContainsString('AnotherSecretCode', $html);
    }

    public function test_a_hybrid_event_is_both_its_place_and_its_page(): void
    {
        $venue = $this->venue();
        $event = $this->createEvent($venue, [
            'creator_role_id' => $venue->id,
            'event_url' => 'https://meet.google.com/kfr-hxbz-qde',
        ]);

        [$node, $html] = $this->eventPage($venue, $event);

        $this->assertSame(self::MIXED, $node['eventAttendanceMode']);
        $this->assertCount(2, $node['location']);
        $this->assertSame('Place', $node['location'][0]['@type']);
        $this->assertSame('The Blue Room', $node['location'][0]['name']);
        $this->assertSame(['@type' => 'VirtualLocation', 'url' => $this->canonical($html)], $node['location'][1]);
        $this->assertStringNotContainsString('kfr-hxbz-qde', $html);
    }

    /**
     * No venue and no link: no location and no attendance mode. The old fallback located the event
     * at its ORGANIZER - a comedy show "at" the promoter's schedule, in the promoter's country.
     */
    public function test_an_event_with_no_venue_and_no_link_has_no_location(): void
    {
        $talent = $this->createRole($this->createOwner(), 'talent', [
            'name' => 'MG Stand-up shows',
            'city' => 'Austin',
            'country_code' => 'us',
        ]);
        $event = $this->createEvent($talent, ['creator_role_id' => $talent->id]);

        [$node, $html] = $this->eventPage($talent, $event);

        $this->assertArrayNotHasKey('location', $node);
        $this->assertArrayNotHasKey('eventAttendanceMode', $node);
        $this->assertStringNotContainsString('"Place"', $this->eventBlock($html));
        // Still organized by the act, who has a page of their own.
        $this->assertSame('Person', $node['organizer']['@type']);
    }

    /** Never an empty PostalAddress, and never a country on its own: neither locates anything. */
    public function test_a_place_falls_back_to_the_formatted_address_and_never_publishes_a_bare_country(): void
    {
        $owner = $this->createOwner();

        $formatted = $this->createRole($owner, 'venue', [
            'name' => 'Harbour Hall',
            'formatted_address' => '9 Harbour Rd, Cork, Ireland',
            'country_code' => 'ie',
        ]);
        $event = $this->createEvent($formatted, ['creator_role_id' => $formatted->id]);
        [$node] = $this->eventPage($formatted, $event);

        $this->assertSame([
            '@type' => 'PostalAddress',
            'streetAddress' => '9 Harbour Rd, Cork, Ireland',
            'addressCountry' => 'IE',
        ], $node['location']['address']);

        $countryOnly = $this->createRole($owner, 'venue', ['name' => 'Somewhere Hall', 'country_code' => 'us']);
        $event = $this->createEvent($countryOnly, ['creator_role_id' => $countryOnly->id]);
        [$node, $html] = $this->eventPage($countryOnly, $event);

        $this->assertSame('Somewhere Hall', $node['location']['name']);
        $this->assertArrayNotHasKey('address', $node['location']);
        $this->assertStringNotContainsString('PostalAddress', $this->eventBlock($html));

        // A venue a sync made from an address alone has no name: its short address names it.
        $nameless = $this->createRole($owner, 'venue', ['name' => '', 'address1' => '5 Elm St', 'city' => 'Salem']);
        $this->assertSame('5 Elm St, Salem', $nameless->schemaPlace('en')['name']);
    }

    // ---------------------------------------------------------------------------------------
    // offers
    // ---------------------------------------------------------------------------------------

    public function test_a_cancelled_event_offers_nothing(): void
    {
        $venue = $this->venue();

        $ticketed = $this->createEvent($venue, ['tickets_enabled' => true, 'is_cancelled' => true]);
        $this->createTicket($ticketed, ['type' => 'General', 'price' => 20]);

        [$node] = $this->eventPage($venue, $ticketed);
        $this->assertSame('https://schema.org/EventCancelled', $node['eventStatus']);
        $this->assertArrayNotHasKey('offers', $node);

        // A registration link lives on somebody else's site, so nothing but the cancellation
        // itself closes it here.
        $registration = $this->createEvent($venue, [
            'registration_url' => 'https://tickets.example.org/e/46',
            'ticket_price' => 15,
            'is_cancelled' => true,
        ]);

        [$node] = $this->eventPage($venue, $registration);
        $this->assertSame('https://schema.org/EventCancelled', $node['eventStatus']);
        $this->assertArrayNotHasKey('offers', $node);
    }

    public function test_an_rsvp_is_one_free_offer_at_the_registration_form(): void
    {
        $venue = $this->venue();
        $event = $this->createEvent($venue, ['rsvp_enabled' => true, 'rsvp_limit' => 50]);

        [$node, $html] = $this->eventPage($venue, $event);

        $offer = $node['offers'];
        $this->assertSame('Offer', $offer['@type']);
        $this->assertEquals(0, $offer['price']);
        $this->assertSame($this->canonical($html).'?rsvp=true', $offer['url']);
        $this->assertSame(self::IN_STOCK, $offer['availability']);
        $this->assertSame($event->fresh()->published_at->toIso8601String(), $offer['validFrom']);
        $this->assertTrue($node['isAccessibleForFree']);
    }

    public function test_a_full_rsvp_is_sold_out_and_a_finished_one_offers_nothing(): void
    {
        $venue = $this->venue();

        $full = $this->createEvent($venue, ['rsvp_enabled' => true, 'rsvp_limit' => 2]);
        Event::whereKey($full->id)->update(['rsvp_sold' => json_encode([$full->saleEventDateFromStartsAt() => 2])]);

        [$node] = $this->eventPage($venue, $full);
        $this->assertSame(self::SOLD_OUT, $node['offers']['availability']);
        $this->assertTrue($node['isAccessibleForFree']);

        $finished = $this->createEvent($venue, [
            'rsvp_enabled' => true,
            'starts_at' => now()->subDays(3)->setTime(12, 0)->format('Y-m-d H:i:s'),
        ]);

        [$node] = $this->eventPage($venue, $finished);
        $this->assertArrayNotHasKey('offers', $node);
        $this->assertTrue($node['isAccessibleForFree'], 'registration is free whether or not it is still open');
    }

    public function test_a_selling_event_offers_each_type_by_its_name_with_no_inventory(): void
    {
        $venue = $this->venue();
        $event = $this->createEvent($venue, ['tickets_enabled' => true, 'ticket_currency_code' => 'EUR']);
        $this->createTicket($event, ['type' => 'General Admission', 'price' => 25.5, 'quantity' => 100]);
        $vip = $this->createTicket($event, [
            'type' => 'VIP',
            'price' => 60,
            'quantity' => 10,
            'sales_end_at' => now()->addDays(5)->startOfHour(),
        ]);

        [$node, $html] = $this->eventPage($venue, $event);
        $offers = collect($this->offers($node))->keyBy('name');

        // The offer is named after tickets.type; there is no tickets.name column.
        $this->assertSame(['VIP', 'General Admission'], $offers->keys()->all());
        $this->assertEquals(60, $offers['VIP']['price']);
        $this->assertEquals(25.5, $offers['General Admission']['price']);

        foreach ($offers as $offer) {
            $this->assertSame('EUR', $offer['priceCurrency']);
            $this->assertSame($this->canonical($html).'?tickets=true', $offer['url']);
            $this->assertSame(self::IN_STOCK, $offer['availability']);
            // The TOTAL quantity used to be published here as if it were what remained.
            $this->assertArrayNotHasKey('inventoryLevel', $offer);
        }

        $this->assertSame($event->fresh()->published_at->toIso8601String(), $offers['General Admission']['validFrom']);
        $this->assertSame($vip->sales_end_at->toIso8601String(), $offers['VIP']['validThrough']);
        $this->assertArrayNotHasKey('validThrough', $offers['General Admission']);
        $this->assertFalse($node['isAccessibleForFree']);
    }

    /** SoldOut exactly where the ticket form prints "Sold out": per type, or the whole house. */
    public function test_a_sold_out_type_is_sold_out_and_a_full_house_sells_out_every_type(): void
    {
        $venue = $this->venue();

        $event = $this->createEvent($venue, ['tickets_enabled' => true]);
        $date = $event->saleEventDateFromStartsAt();
        $this->createTicket($event, ['type' => 'Early Bird', 'price' => 15, 'quantity' => 2, 'sold' => json_encode([$date => 2])]);
        $this->createTicket($event, ['type' => 'Standard', 'price' => 25, 'quantity' => 50]);

        [$node] = $this->eventPage($venue, $event);
        $offers = collect($this->offers($node))->keyBy('name');
        $this->assertSame(self::SOLD_OUT, $offers['Early Bird']['availability']);
        $this->assertSame(self::IN_STOCK, $offers['Standard']['availability']);

        // Combined mode: both types draw on one house of 10, and it is full.
        $house = $this->createEvent($venue, ['tickets_enabled' => true, 'total_tickets_mode' => 'combined']);
        $date = $house->saleEventDateFromStartsAt();
        $this->createTicket($house, ['type' => 'Stalls', 'price' => 30, 'quantity' => 10, 'sold' => json_encode([$date => 10])]);
        $this->createTicket($house, ['type' => 'Circle', 'price' => 20, 'quantity' => 10]);

        [$node] = $this->eventPage($venue, $house);
        foreach ($this->offers($node) as $offer) {
            $this->assertSame(self::SOLD_OUT, $offer['availability'], $offer['name'].' draws on a full house');
        }
    }

    /**
     * Kept from the rule this replaced: an event its PLAN stops selling publishes no offers at all,
     * never SoldOut, which would claim a sell-out that never happened.
     */
    public function test_an_event_the_plan_cannot_sell_publishes_no_offers_and_never_sold_out(): void
    {
        $role = $this->createFreeRole(null, 'venue');
        $this->assertFalse($role->fresh()->isPro(), 'fixture: a genuinely free schedule');
        $event = $this->createEvent($role, ['tickets_enabled' => true]);
        $this->createTicket($event, ['type' => 'General', 'price' => 20]);

        [$node, $html] = $this->eventPage($role, $event);

        $this->assertArrayNotHasKey('offers', $node);
        $this->assertArrayNotHasKey('isAccessibleForFree', $node);
        $this->assertStringNotContainsString('SoldOut', $this->eventBlock($html));
    }

    /**
     * A free tier keeps a free schedule's event selling (canOfferTickets()), which used to carry
     * every PAID type into the offers with it, though the form refuses to sell them.
     */
    public function test_a_free_plan_publishes_its_free_tier_and_never_its_unsellable_paid_types(): void
    {
        $role = $this->createFreeRole(null, 'venue');
        $event = $this->createEvent($role, ['tickets_enabled' => true]);
        $this->createTicket($event, ['type' => 'Community', 'price' => 0]);
        $this->createTicket($event, ['type' => 'Supporter', 'price' => 30]);

        [$node, $html] = $this->eventPage($role, $event);
        $offers = $this->offers($node);

        $this->assertCount(1, $offers);
        $this->assertSame('Community', $offers[0]['name']);
        $this->assertEquals(0, $offers[0]['price']);
        $this->assertTrue($node['isAccessibleForFree']);
        $this->assertStringNotContainsString('Supporter', $this->eventBlock($html));
    }

    public function test_tickets_not_on_sale_yet_are_offered_from_the_day_they_go_on_sale(): void
    {
        $venue = $this->venue();
        $event = $this->createEvent($venue, [
            'tickets_enabled' => true,
            'starts_at' => now()->addDays(20)->setTime(12, 0)->format('Y-m-d H:i:s'),
        ]);
        $ticket = $this->createTicket($event, [
            'type' => 'General',
            'price' => 20,
            'sales_start_at' => now()->addDays(3)->startOfHour(),
        ]);

        [$node] = $this->eventPage($venue, $event);

        $this->assertSame($ticket->sales_start_at->toIso8601String(), $node['offers']['validFrom']);
        $this->assertSame(self::IN_STOCK, $node['offers']['availability']);
        $this->assertFalse($node['isAccessibleForFree']);
    }

    public function test_a_type_whose_sales_have_ended_is_not_offered(): void
    {
        $venue = $this->venue();

        $ended = $this->createEvent($venue, ['tickets_enabled' => true]);
        $this->createTicket($ended, ['type' => 'General', 'price' => 20, 'sales_end_at' => now()->subDay()]);

        [$node] = $this->eventPage($venue, $ended);
        $this->assertArrayNotHasKey('offers', $node);

        $mixed = $this->createEvent($venue, ['tickets_enabled' => true]);
        $this->createTicket($mixed, ['type' => 'Early Bird', 'price' => 15, 'sales_end_at' => now()->subDay()]);
        $this->createTicket($mixed, ['type' => 'Standard', 'price' => 25]);

        [$node] = $this->eventPage($venue, $mixed);
        $this->assertSame(['Standard'], array_column($this->offers($node), 'name'));
    }

    /** A multi-event pass is not a ticket to THIS event, unless the event sells nothing else. */
    public function test_passes_are_offered_only_when_the_event_sells_nothing_else(): void
    {
        $venue = $this->venue();

        $mixed = $this->createEvent($venue, ['tickets_enabled' => true]);
        $this->createTicket($mixed, ['type' => 'Season Pass', 'price' => 99, 'is_pass' => true, 'pass_usage_type' => 'unlimited']);
        $this->createTicket($mixed, ['type' => 'Single Night', 'price' => 20]);

        [$node] = $this->eventPage($venue, $mixed);
        $this->assertSame(['Single Night'], array_column($this->offers($node), 'name'));

        $passOnly = $this->createEvent($venue, ['tickets_enabled' => true]);
        $this->createTicket($passOnly, ['type' => 'Season Pass', 'price' => 99, 'is_pass' => true, 'pass_usage_type' => 'unlimited']);

        [$node] = $this->eventPage($venue, $passOnly);
        $this->assertSame(['Season Pass'], array_column($this->offers($node), 'name'));
    }

    /** The page's price badge: a registration link somewhere else, at a stated price. */
    public function test_an_external_registration_with_a_price_is_one_offer_at_its_link(): void
    {
        $venue = $this->venue();

        $paid = $this->createEvent($venue, [
            'registration_url' => 'https://tickets.example.org/e/42',
            'ticket_price' => 15,
            'ticket_currency_code' => 'GBP',
        ]);

        [$node] = $this->eventPage($venue, $paid);
        $this->assertSame('https://tickets.example.org/e/42', $node['offers']['url']);
        $this->assertEquals(15, $node['offers']['price']);
        $this->assertSame('GBP', $node['offers']['priceCurrency']);
        $this->assertSame(self::IN_STOCK, $node['offers']['availability']);
        $this->assertFalse($node['isAccessibleForFree']);

        $free = $this->createEvent($venue, ['registration_url' => 'https://tickets.example.org/e/43', 'ticket_price' => 0]);
        [$node] = $this->eventPage($venue, $free);
        $this->assertEquals(0, $node['offers']['price']);
        $this->assertTrue($node['isAccessibleForFree']);

        // Not an http(s) link: the offer stays on the event's own page.
        $bare = $this->createEvent($venue, ['registration_url' => 'tickets.example.org/e/44', 'ticket_price' => 10]);
        [$node, $html] = $this->eventPage($venue, $bare);
        $this->assertSame($this->canonical($html), $node['offers']['url']);

        // Over: nothing left to buy, though the price is still what it was.
        $past = $this->createEvent($venue, [
            'registration_url' => 'https://tickets.example.org/e/45',
            'ticket_price' => 15,
            'starts_at' => now()->subDays(3)->setTime(12, 0)->format('Y-m-d H:i:s'),
        ]);
        [$node] = $this->eventPage($venue, $past);
        $this->assertArrayNotHasKey('offers', $node);
        $this->assertFalse($node['isAccessibleForFree']);
    }

    /**
     * No tickets, no RSVP and no priced registration: the page offers nothing and states no price,
     * so the node does neither. This is where the invented price-0 in-stock Offer used to go.
     */
    public function test_an_event_with_nothing_to_sell_or_register_offers_nothing(): void
    {
        $venue = $this->venue();
        $event = $this->createEvent($venue, ['registration_url' => 'https://example.org/info']);

        [$node, $html] = $this->eventPage($venue, $event);

        $this->assertArrayNotHasKey('offers', $node);
        $this->assertArrayNotHasKey('isAccessibleForFree', $node);
        $this->assertStringNotContainsString('"Offer"', $this->eventBlock($html));
    }

    /** CLAUDE.md: never a hardcoded currency next to a price. The old ticket fallback was 'USD'. */
    public function test_prices_without_an_event_currency_follow_the_platform_never_usd(): void
    {
        config(['app.platform_currency' => 'ZAR']);
        PlatformCurrency::flush();

        // events.ticket_currency_code is NOT NULL with a USD default, so "no currency" is the empty
        // string an import or an API write can leave there.
        $venue = $this->venue();
        $rsvp = $this->createEvent($venue, ['rsvp_enabled' => true, 'ticket_currency_code' => '']);
        $ticketed = $this->createEvent($venue, ['tickets_enabled' => true, 'ticket_currency_code' => '']);
        $this->createTicket($ticketed, ['type' => 'General', 'price' => 100]);

        foreach ([$rsvp, $ticketed] as $event) {
            [$node, $html] = $this->eventPage($venue, $event);

            $this->assertNotEmpty($this->offers($node));
            foreach ($this->offers($node) as $offer) {
                $this->assertSame('ZAR', $offer['priceCurrency']);
            }
            foreach ($this->jsonLdBlocks($html) as $block) {
                $this->assertStringNotContainsString('USD', json_encode($block));
            }
        }
    }

    // ---------------------------------------------------------------------------------------
    // The rest of the node
    // ---------------------------------------------------------------------------------------

    public function test_the_description_is_the_owners_text_or_nothing_never_a_placeholder(): void
    {
        $venue = $this->venue();

        $bare = $this->createEvent($venue, ['name' => 'Bare Night']);
        [$node, $html] = $this->eventPage($venue, $bare);
        $this->assertArrayNotHasKey('description', $node);
        $this->assertStringNotContainsString('Bare Night - '.__('messages.event'), $html);

        // Plain text, decoded once: an import can store an entity in a plain column.
        $short = $this->createEvent($venue, ['name' => 'Short Night', 'short_description' => 'Two sets &amp; a jam']);
        [$node] = $this->eventPage($venue, $short);
        $this->assertSame('Two sets & a jam', $node['description']);

        // The long description wins, block-aware: a line break is a boundary, not glue.
        $long = $this->createEvent($venue, [
            'name' => 'Long Night',
            'short_description' => 'The short one',
            'description' => "Doors at seven\nMusic at eight\n\nBring a friend",
        ]);
        [$node] = $this->eventPage($venue, $long);
        $this->assertSame(SeoUtils::plainText($long->fresh()->description_html), $node['description']);
        $this->assertStringContainsString('Doors at seven', $node['description']);
        $this->assertStringNotContainsString('sevenMusic', $node['description']);
        $this->assertStringNotContainsString('<', $node['description']);

        $novel = $this->createEvent($venue, ['name' => 'Novel Night', 'description' => str_repeat('A long evening of words. ', 400)]);
        [$node] = $this->eventPage($venue, $novel);
        $this->assertLessThanOrEqual(Event::SCHEMA_DESCRIPTION_MAX, mb_strlen($node['description']));
    }

    /** A VideoObject needs a description: the event's, as the same plain text, else its name. */
    public function test_a_video_object_describes_its_event_in_plain_text(): void
    {
        $venue = $this->venue();
        $described = $this->createEvent($venue, ['name' => 'Long Night', 'description' => "Doors at seven\nMusic at eight"]);
        $bare = $this->createEvent($venue, ['name' => 'Bare Night']);

        foreach ([$described, $bare] as $event) {
            EventVideo::create([
                'event_id' => $event->id,
                'user_id' => $venue->user_id,
                'youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                'is_approved' => true,
            ]);
        }

        [$node, $html] = $this->eventPage($venue, $described);
        $video = $this->nodeOfType($this->jsonLdBlocks($html), 'VideoObject');
        $this->assertSame($node['description'], $video['description']);
        $this->assertStringNotContainsString('sevenMusic', $video['description']);

        [, $html] = $this->eventPage($venue, $bare);
        $this->assertSame('Bare Night', $this->nodeOfType($this->jsonLdBlocks($html), 'VideoObject')['description']);
    }

    /**
     * The first CLAIMED of the creator, the venue and the performers organizes the event, typed and
     * with the @id its own page gives it. With none claimed there is no organizer: the old last
     * resort was an Organization named after the event itself.
     */
    public function test_the_organizer_is_the_first_claimed_schedule_and_absent_when_none_is(): void
    {
        $owner = $this->createOwner();
        $curator = $this->createCurator($owner, ['name' => 'Night Listings']);
        $venue = $this->venue();

        // Created by the curator: the curator organizes it, as an Organization.
        $event = $this->createEvent($venue, ['creator_role_id' => $curator->id]);
        $event->roles()->attach($curator->id, ['is_accepted' => true]);

        [$node] = $this->eventPage($venue, $event);
        $this->assertSame([
            '@type' => 'Organization',
            '@id' => $curator->getCanonicalUrl().'#schedule',
            'name' => 'Night Listings',
            'url' => $curator->getCanonicalUrl(),
        ], $node['organizer']);

        // Created by a placeholder nobody has claimed: the claimed venue is next in line.
        $placeholderVenue = $this->unclaimed($owner, 'venue', ['name' => 'Unclaimed Hall', 'address1' => '1 Side St', 'city' => 'Leeds']);
        $placeholderAct = $this->unclaimed($owner, 'talent', ['name' => 'Unclaimed Act']);

        $listed = $this->createEvent($curator, ['creator_role_id' => $placeholderVenue->id]);
        $listed->roles()->attach($placeholderVenue->id, ['is_accepted' => true]);
        $listed->roles()->attach($placeholderAct->id, ['is_accepted' => true]);

        [$node] = $this->eventPage($curator, $listed);
        $this->assertArrayNotHasKey('organizer', $node, 'the listing curator neither created nor hosts it');
        // Both still named, just not linked: neither has a page of its own.
        $this->assertSame('Unclaimed Hall', $node['location']['name']);
        $this->assertArrayNotHasKey('url', $node['location']);
        $this->assertArrayNotHasKey('@id', $node['location']);
        $this->assertSame(['@type' => 'Person', 'name' => 'Unclaimed Act'], $node['performer']);
    }

    public function test_an_end_date_only_when_the_event_has_a_duration(): void
    {
        $venue = $this->venue();

        $open = $this->createEvent($venue, ['duration' => 0]);
        [$node] = $this->eventPage($venue, $open);
        // getEndDateTime() assumes two hours for these, a guess the page never shows.
        $this->assertArrayNotHasKey('endDate', $node);

        $timed = $this->createEvent($venue, ['duration' => 3]);
        [$node] = $this->eventPage($venue, $timed);
        $this->assertSame(
            \Carbon\Carbon::parse($node['startDate'])->addHours(3)->toIso8601String(),
            $node['endDate']
        );
    }

    /**
     * A date-only starts_at is already the schedule's calendar day. Read as midnight UTC and then
     * converted, it landed on the previous day everywhere west of Greenwich.
     *
     * starts_at is a DATETIME column, so a bare date lives only in memory (an import or an API
     * payload mid-request): the node is built from one directly.
     */
    public function test_a_date_only_start_keeps_its_calendar_date_west_of_utc(): void
    {
        $venue = $this->venue(['timezone' => 'America/Los_Angeles']);
        $event = $this->createEvent($venue, ['creator_role_id' => $venue->id, 'duration' => 0]);
        $event->starts_at = '2026-10-15';

        $node = $event->schemaNode(null, $venue, 'en');
        $this->assertSame('2026-10-15', $node['startDate']);
        $this->assertArrayNotHasKey('endDate', $node);

        // Two whole days end on the second of them.
        $event->duration = 48;
        $this->assertSame('2026-10-16', $event->schemaNode(null, $venue, 'en')['endDate']);

        // A recurring occurrence is its own day.
        $event->days_of_week = '1111111';
        $event->recurring_frequency = 'weekly';
        $this->assertSame('2026-10-22', $event->getSchemaStartDate('2026-10-22'));
    }

    // ---------------------------------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------------------------------

    /** A claimed venue with a full address and coordinates. */
    private function venue(array $attrs = []): Role
    {
        return $this->createVenueWithAddress($this->createOwner(), $attrs + [
            'name' => 'The Blue Room',
            'geo_lat' => '39.7817',
            'geo_lon' => '-89.6501',
        ]);
    }

    /** A placeholder schedule: no owner and nothing verified, like the ones the app invents. */
    private function unclaimed($owner, string $type, array $attrs): Role
    {
        $role = $this->createRole($owner, $type, $attrs + ['email_verified_at' => null]);
        Role::whereKey($role->id)->update(['user_id' => null]);

        return $role->fresh();
    }

    /**
     * The event page's Event node and HTML.
     *
     * @return array{0: array<string, mixed>, 1: string}
     */
    private function eventPage(Role $host, Event $event, ?string $date = null): array
    {
        $html = $this->get($this->guestEventUrl($host, $event, $date))->assertOk()->getContent();
        $node = $this->nodeOfType($this->jsonLdBlocks($html), 'Event');

        $this->assertNotNull($node, 'the event page emitted no Event node');

        return [$node, $html];
    }

    /** The Event block's raw JSON, for asserting a string never appears in it. */
    private function eventBlock(string $html): string
    {
        $block = json_encode($this->nodeOfType($this->jsonLdBlocks($html), 'Event'));
        $this->assertNotSame('null', $block);

        return $block;
    }

    /** @return array<int, array<string, mixed>> the node's offers as a list, one or several */
    private function offers(array $node): array
    {
        if (! isset($node['offers'])) {
            return [];
        }

        return array_is_list($node['offers']) ? $node['offers'] : [$node['offers']];
    }

    private function canonical(string $html): string
    {
        $this->assertSame(1, preg_match('~<link rel="canonical" href="([^"]+)">~', $html, $m), 'no canonical link');

        return html_entity_decode($m[1]);
    }

    /**
     * Every JSON-LD block, decoded. Each must parse and carry no raw "<" (JSON_HEX_TAG).
     *
     * @return array<int, array<string, mixed>>
     */
    private function jsonLdBlocks(string $html): array
    {
        preg_match_all('~<script type="application/ld\+json"[^>]*>(.*?)</script>~s', $html, $matches);
        $this->assertNotEmpty($matches[1], 'the page emitted no JSON-LD');

        return array_map(function (string $raw) {
            $this->assertStringNotContainsString('<', $raw, 'a JSON-LD block carries a raw "<"');
            $decoded = json_decode($raw, true);
            $this->assertIsArray($decoded, 'a JSON-LD block does not parse: '.$raw);

            return $decoded;
        }, $matches[1]);
    }

    /**
     * @param  array<int, array<string, mixed>>  $blocks
     * @return array<string, mixed>|null
     */
    private function nodeOfType(array $blocks, string $type): ?array
    {
        foreach ($blocks as $block) {
            if (($block['@type'] ?? null) === $type) {
                return $block;
            }
        }

        return null;
    }
}
