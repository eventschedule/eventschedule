<?php

namespace Tests\Feature;

use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * A schedule page's own node (Role::schemaNode()) and, on its home, the WebSite node.
 *
 * Production emitted every schedule as an Organization or a Person carrying inLanguage - which
 * schema.org defines on creative works and events, not on those - with no logo, no coordinates and
 * no events. A venue is now an EventVenue with its address, coordinates and logo, talent a Person,
 * a curator an Organization, each with the "{canonical}#schedule" @id every event page gives it,
 * listing its upcoming events.
 */
class GuestScheduleSchemaTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Role's saving hook geocodes a new address whenever a Google key is configured, which a
        // developer's .env may carry: the coordinates below are the fixture's own.
        config(['services.google.backend' => null]);
    }

    public function test_a_venue_is_an_event_venue_with_its_address_coordinates_logo_and_image(): void
    {
        $venue = $this->createVenueWithAddress($this->createOwner(), [
            'name' => 'The Blue Room',
            'geo_lat' => '39.7817',
            'geo_lon' => '-89.6501',
            'profile_image_url' => 'profile_bluenote.png',
            'description' => 'A **listening room** for jazz.',
        ]);

        [$node, $html] = $this->schedulePage('/'.$venue->subdomain, 'EventVenue');

        $canonical = $venue->getCanonicalUrl();
        $this->assertSame($canonical.'#schedule', $node['@id']);
        $this->assertSame($canonical, $node['url']);
        $this->assertSame($this->canonical($html), $node['url']);
        $this->assertSame('The Blue Room', $node['name']);
        $this->assertSame('A listening room for jazz.', $node['description']);
        $this->assertSame([
            '@type' => 'PostalAddress',
            'streetAddress' => '123 Main St',
            'addressLocality' => 'Springfield',
            'addressRegion' => 'IL',
            'postalCode' => '62701',
            'addressCountry' => 'US',
        ], $node['address']);
        $this->assertSame(['@type' => 'GeoCoordinates', 'latitude' => 39.7817, 'longitude' => -89.6501], $node['geo']);
        $this->assertSame('ImageObject', $node['logo']['@type']);
        $this->assertStringEndsWith('profile_bluenote.png', $node['logo']['url']);
        $this->assertStringEndsWith('profile_bluenote.png', $node['image']['url']);
        $this->assertArrayNotHasKey('inLanguage', $node);
        $this->assertArrayNotHasKey('telephone', $node);
    }

    /** The telephone the guest header shows: given, switched on, and verified. Nothing else. */
    public function test_a_venue_publishes_its_telephone_only_where_its_page_shows_it(): void
    {
        $owner = $this->createOwner();
        $phone = ['phone' => '+15555550100', 'show_phone' => true, 'phone_verified_at' => now()];

        $shown = $this->createVenueWithAddress($owner, $phone);
        [$node] = $this->schedulePage('/'.$shown->subdomain, 'EventVenue');
        $this->assertSame('+15555550100', $node['telephone']);

        $hidden = $this->createVenueWithAddress($owner, ['show_phone' => false] + $phone);
        [$node] = $this->schedulePage('/'.$hidden->subdomain, 'EventVenue');
        $this->assertArrayNotHasKey('telephone', $node);

        $unverified = $this->createVenueWithAddress($owner, ['phone_verified_at' => null] + $phone);
        [$node] = $this->schedulePage('/'.$unverified->subdomain, 'EventVenue');
        $this->assertArrayNotHasKey('telephone', $node);
    }

    public function test_talent_is_a_person_with_an_image_and_no_logo(): void
    {
        $talent = $this->createRole($this->createOwner(), 'talent', [
            'name' => 'Ada Quartet',
            'profile_image_url' => 'profile_bluenote.png',
            'address1' => '1 Practice Rd',
            'city' => 'Springfield',
        ]);

        [$node] = $this->schedulePage('/'.$talent->subdomain, 'Person');

        $this->assertSame('Ada Quartet', $node['name']);
        $this->assertStringEndsWith('profile_bluenote.png', $node['image']['url']);
        // schema.org gives a Person no logo, and an act's rehearsal room is not its address.
        $this->assertArrayNotHasKey('logo', $node);
        $this->assertArrayNotHasKey('address', $node);
        $this->assertArrayNotHasKey('inLanguage', $node);
    }

    public function test_a_curator_is_an_organization_with_a_logo_and_an_image(): void
    {
        $curator = $this->createCurator($this->createOwner(), [
            'name' => 'Night Listings',
            'profile_image_url' => 'profile_bluenote.png',
        ]);

        [$node] = $this->schedulePage('/'.$curator->subdomain, 'Organization');

        $this->assertSame($curator->getCanonicalUrl().'#schedule', $node['@id']);
        $this->assertStringEndsWith('profile_bluenote.png', $node['logo']['url']);
        $this->assertStringEndsWith('profile_bluenote.png', $node['image']['url']);
        $this->assertArrayNotHasKey('inLanguage', $node);
        $this->assertArrayNotHasKey('geo', $node);
    }

    /** sameAs links are withheld while nobody has verified a contact that is actually there. */
    public function test_same_as_is_withheld_for_an_unverified_schedule(): void
    {
        $links = ['social_links' => json_encode([['url' => 'https://instagram.com/bluenote']])];
        $owner = $this->createOwner();

        $verified = $this->createRole($owner, 'talent', $links);
        [$node] = $this->schedulePage('/'.$verified->subdomain, 'Person');
        $this->assertSame(['https://instagram.com/bluenote'], $node['sameAs']);

        // Claimed (a verification date and an owner) but the verified address is not there.
        $unverified = $this->createRole($owner, 'talent', $links + ['email' => '']);
        $this->assertFalse($unverified->hasVerifiedContact(), 'fixture: renders, but is unverified');
        [$node] = $this->schedulePage('/'.$unverified->subdomain, 'Person');
        $this->assertArrayNotHasKey('sameAs', $node);
    }

    /**
     * Up to ten upcoming events, each a compact Event at its UNDATED canonical - a series is one
     * page - dated by the occurrence the list computed, organized by a bare reference to the node
     * that lists it, and without the offers that would cost ticket queries per event.
     */
    public function test_a_venue_lists_its_next_ten_events_at_their_undated_urls(): void
    {
        $venue = $this->createVenueWithAddress($this->createOwner(), ['name' => 'The Blue Room']);

        $series = $this->createRecurringEvent($venue, [
            'name' => 'Daily Jam',
            'creator_role_id' => $venue->id,
            'starts_at' => now()->subWeeks(3)->setTime(23, 0)->format('Y-m-d H:i:s'),
            'tickets_enabled' => true,
        ]);
        $this->createTicket($series, ['type' => 'Door', 'price' => 10]);

        foreach (range(10, 21) as $days) {
            $event = $this->createEvent($venue, [
                'name' => 'Show in '.$days.' days',
                'creator_role_id' => $venue->id,
                'starts_at' => now()->addDays($days)->setTime(23, 0)->format('Y-m-d H:i:s'),
                'tickets_enabled' => true,
            ]);
            $this->createTicket($event, ['type' => 'General', 'price' => 20]);
        }

        $ticketQueries = 0;
        DB::listen(function ($query) use (&$ticketQueries) {
            if (preg_match('/\bfrom [`"]?tickets[`"]?/i', $query->sql)) {
                $ticketQueries++;
            }
        });

        [$node] = $this->schedulePage('/'.$venue->subdomain, 'EventVenue');

        $this->assertSame(0, $ticketQueries, 'the upcoming list must not load tickets');
        $this->assertArrayNotHasKey('performerIn', $node);
        $this->assertCount(Role::SCHEMA_UPCOMING_LIMIT, $node['event']);

        $first = $node['event'][0];
        $this->assertSame('Daily Jam', $first['name']);
        $this->assertSame($this->guestEventUrl($venue, $series), $first['url'], 'a series is listed at its undated URL');
        $this->assertStringStartsWith($series->fresh()->nextOccurrenceFrom().'T', $first['startDate']);

        foreach ($node['event'] as $entry) {
            $this->assertSame('Event', $entry['@type']);
            $this->assertArrayNotHasKey('@context', $entry);
            $this->assertSame('Place', $entry['location']['@type']);
            $this->assertSame(['@id' => $venue->getCanonicalUrl().'#schedule'], $entry['organizer']);
            $this->assertArrayNotHasKey('offers', $entry);
            $this->assertArrayNotHasKey('performer', $entry);
            $this->assertArrayNotHasKey('description', $entry);
        }

        $this->assertSame('Show in 10 days', $node['event'][1]['name']);
        $this->assertSame('Show in 18 days', $node['event'][9]['name']);
    }

    public function test_talent_lists_its_events_as_performer_in(): void
    {
        $owner = $this->createOwner();
        $talent = $this->createRole($owner, 'talent', ['name' => 'Ada Quartet']);
        $venue = $this->createVenueWithAddress($owner, ['name' => 'The Blue Room']);

        foreach (['First Set', 'Second Set'] as $index => $name) {
            $event = $this->createEvent($venue, [
                'name' => $name,
                'creator_role_id' => $venue->id,
                'starts_at' => now()->addDays(5 + $index)->setTime(23, 0)->format('Y-m-d H:i:s'),
            ]);
            $event->roles()->attach($talent->id, ['is_accepted' => true]);
        }

        [$node] = $this->schedulePage('/'.$talent->subdomain, 'Person');

        $this->assertArrayNotHasKey('event', $node);
        $this->assertSame(['First Set', 'Second Set'], array_column($node['performerIn'], 'name'));
        // Organized by the venue, which this page does not carry: the reference is spelled out.
        $this->assertSame('Organization', $node['performerIn'][0]['organizer']['@type']);
        $this->assertSame($venue->getCanonicalUrl().'#schedule', $node['performerIn'][0]['organizer']['@id']);
    }

    /** Google reads an event with no location as no event: those stay off the list. */
    public function test_an_upcoming_event_with_no_location_is_left_out(): void
    {
        $owner = $this->createOwner();
        $curator = $this->createCurator($owner, ['name' => 'Night Listings']);
        $venue = $this->createVenueWithAddress($owner, ['name' => 'The Blue Room']);

        $atVenue = $this->createEvent($curator, ['name' => 'At The Venue', 'starts_at' => now()->addDays(3)->format('Y-m-d H:i:s')]);
        $atVenue->roles()->attach($venue->id, ['is_accepted' => true]);
        $this->createEvent($curator, [
            'name' => 'On A Link',
            'event_url' => 'https://meet.google.com/abc-defg-hij',
            'starts_at' => now()->addDays(4)->format('Y-m-d H:i:s'),
        ]);
        $this->createEvent($curator, ['name' => 'Nowhere Yet', 'starts_at' => now()->addDays(5)->format('Y-m-d H:i:s')]);

        [$node, $html] = $this->schedulePage('/'.$curator->subdomain, 'Organization');

        $this->assertSame(['At The Venue', 'On A Link'], array_column($node['event'], 'name'));
        $this->assertSame('VirtualLocation', $node['event'][1]['location']['@type']);
        $this->assertStringNotContainsString('abc-defg-hij', $html);
    }

    /**
     * The WebSite node names the site after the schedule, so it belongs only where the schedule IS
     * the site: the home page, at the root of its own host, in its primary language.
     */
    public function test_the_website_node_is_only_on_a_home_at_the_root_of_its_host(): void
    {
        $owner = $this->createOwner();
        $direct = $this->createRole($owner, 'talent', [
            'name' => 'Direct Talent',
            'name_en' => 'Talento Directo',
            'language_code' => 'en',
            'translation_language_code' => 'es',
            'custom_domain' => 'https://website-node.test',
            'custom_domain_mode' => 'direct',
            'custom_domain_status' => 'active',
        ]);
        $this->createGroup($direct, ['name' => 'Workshops', 'slug' => 'workshops']);
        $event = $this->createEvent($direct, ['name' => 'Direct Show']);

        $home = $this->jsonLdBlocks($this->get('/'.$direct->subdomain)->assertOk()->getContent());
        $this->assertSame([
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => 'Direct Talent',
            'url' => 'https://website-node.test',
        ], $this->nodeOfType($home, 'WebSite'));

        $elsewhere = [
            'a sub-schedule' => '/'.$direct->subdomain.'/workshops',
            'an event page' => $this->guestEventUrl($direct, $event),
            // The ?lang= alternate is not the site's home URL, and its name is the other language's.
            'the second language' => '/'.$direct->subdomain.'?lang=es',
        ];

        foreach ($elsewhere as $label => $path) {
            $blocks = $this->jsonLdBlocks($this->get($path)->assertOk()->getContent());
            $this->assertNull($this->nodeOfType($blocks, 'WebSite'), "no WebSite node on $label");
        }

        // /{subdomain} under path routing is a section of somebody else's site.
        $path = $this->createRole($owner, 'talent', ['name' => 'Path Talent']);
        $this->assertNotSame('', (string) parse_url($path->getCanonicalUrl(), PHP_URL_PATH), 'fixture: a path-routed canonical');
        $blocks = $this->jsonLdBlocks($this->get('/'.$path->subdomain)->assertOk()->getContent());
        $this->assertNull($this->nodeOfType($blocks, 'WebSite'));
        $this->assertNotNull($this->nodeOfType($blocks, 'Person'));
    }

    /** On the ?lang= page the node's url is that page's canonical, and its name that language's. */
    public function test_the_second_language_page_names_its_own_url_and_language(): void
    {
        $talent = $this->createRole($this->createOwner(), 'talent', [
            'name' => 'Direct Talent',
            'name_en' => 'Talento Directo',
            'language_code' => 'en',
            'translation_language_code' => 'es',
        ]);

        [$node, $html] = $this->schedulePage('/'.$talent->subdomain.'?lang=es', 'Person');

        $this->assertSame($this->canonical($html), $node['url']);
        $this->assertStringEndsWith('?lang=es', $node['url']);
        $this->assertSame($talent->getCanonicalUrl().'#schedule', $node['@id'], 'one entity in every language');
        $this->assertSame('Talento Directo', $node['name']);
    }

    // ---------------------------------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------------------------------

    /**
     * The schedule page's node of $type and the page HTML.
     *
     * @return array{0: array<string, mixed>, 1: string}
     */
    private function schedulePage(string $path, string $type): array
    {
        $html = $this->get($path)->assertOk()->getContent();
        $node = $this->nodeOfType($this->jsonLdBlocks($html), $type);

        $this->assertNotNull($node, "the schedule page emitted no $type node");

        return [$node, $html];
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
