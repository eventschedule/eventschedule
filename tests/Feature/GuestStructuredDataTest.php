<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The guest portal's JSON-LD: the Event or schedule node, and the BreadcrumbList.
 *
 * These blocks used to be stitched together by hand in app-guest.blade.php, with Blade
 * conditionals placing the commas and some values still interpolated with {{ }}, which
 * HTML-escapes but does not JSON-escape. They are now PHP arrays encoded once by SeoUtils::jsonLd(), and this test pins
 * the two things that rewrite is for: owner-controlled text (quotes, a literal </script>,
 * non-ASCII) always yields a block that parses and carries the exact value, and the URLs in it
 * agree with the page they sit on.
 */
class GuestStructuredDataTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private const HOSTILE_SCHEDULE = 'Café "Q" </script><b>x';

    private const HOSTILE_EVENT = 'Show "Ev" </script><i>ü ☃';

    public function test_hostile_event_and_schedule_names_produce_parseable_json_ld_with_exact_values(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createVenueWithAddress($owner, [
            'name' => self::HOSTILE_SCHEDULE,
            'description' => 'About "us" ünï',
        ]);
        $talent = $this->createRole($owner, 'talent', ['name' => 'Talent "T" ☃']);
        $event = $this->createEvent($venue, [
            'name' => self::HOSTILE_EVENT,
            'description' => 'Hello "there" ünï',
        ]);
        $event->roles()->attach($talent->id, ['is_accepted' => true]);

        $html = $this->get($this->guestEventUrl($venue, $event))->assertOk()->getContent();
        $blocks = $this->jsonLdBlocks($html);

        $node = $this->nodeOfType($blocks, 'Event');
        $this->assertNotNull($node, 'the event page emitted no Event node');
        $this->assertSame(self::HOSTILE_EVENT, $node['name']);
        $this->assertSame('Hello "there" ünï', $node['description']);
        $this->assertSame(self::HOSTILE_SCHEDULE, $node['location']['name']);
        $this->assertSame('Talent "T" ☃', $node['performer']['name']);
        $this->assertSame('en', $node['inLanguage']);
        // No tickets, RSVP or registration link: the page states no price, so neither does this.
        $this->assertArrayNotHasKey('isAccessibleForFree', $node);

        $breadcrumb = $this->nodeOfType($blocks, 'BreadcrumbList');
        $this->assertSame(self::HOSTILE_SCHEDULE, $breadcrumb['itemListElement'][1]['name']);
        $this->assertSame(self::HOSTILE_EVENT, $breadcrumb['itemListElement'][2]['name']);

        $html = $this->get('/'.$venue->subdomain)->assertOk()->getContent();
        $node = $this->nodeOfType($this->jsonLdBlocks($html), 'EventVenue');
        $this->assertNotNull($node, 'the venue page emitted no EventVenue node');
        $this->assertSame(self::HOSTILE_SCHEDULE, $node['name']);
        $this->assertSame('About "us" ünï', $node['description']);
        // schema.org defines inLanguage on creative works and events, not on a place.
        $this->assertArrayNotHasKey('inLanguage', $node);
    }

    public function test_breadcrumb_items_are_absolute_urls_numbered_from_one(): void
    {
        $owner = $this->createOwner();
        $plain = $this->createRole($owner, 'talent', ['name' => 'Plain Talent']);
        $direct = $this->createRole($owner, 'talent', [
            'name' => 'Direct Talent',
            'custom_domain' => 'https://jsonld-direct.test',
            'custom_domain_mode' => 'direct',
            'custom_domain_status' => 'active',
        ]);
        $plainEvent = $this->createEvent($plain, ['name' => 'Plain Event']);
        $directEvent = $this->createEvent($direct, ['name' => 'Direct Event']);

        $cases = [
            'schedule page' => ['/'.$plain->subdomain, 2],
            'event page' => [$this->guestEventUrl($plain, $plainEvent), 3],
            // On its own domain the trail roots at the schedule, not at the marketing site.
            'custom-domain event page' => [$this->guestEventUrl($direct, $directEvent), 2],
        ];

        foreach ($cases as $label => [$path, $count]) {
            $html = $this->get($path)->assertOk()->getContent();
            $breadcrumb = $this->nodeOfType($this->jsonLdBlocks($html), 'BreadcrumbList');

            $this->assertNotNull($breadcrumb, "$label: no BreadcrumbList");
            $this->assertCount($count, $breadcrumb['itemListElement'], $label);

            foreach ($breadcrumb['itemListElement'] as $index => $item) {
                $this->assertSame($index + 1, $item['position'], "$label: crumb $index");
                $this->assertNotFalse(filter_var($item['item'], FILTER_VALIDATE_URL), "$label: crumb $index item is not a URL");
                $this->assertStringStartsWith('https://', $item['item'], $label);
            }
        }

        // A schedule page on its own domain would be a one-crumb trail, so it gets none.
        $html = $this->get('/'.$direct->subdomain)->assertOk()->getContent();
        $this->assertNull($this->nodeOfType($this->jsonLdBlocks($html), 'BreadcrumbList'));
    }

    public function test_offer_url_is_the_page_canonical_on_a_custom_domain(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', [
            'name' => 'Offer Talent',
            'custom_domain' => 'https://jsonld-offer.test',
            'custom_domain_mode' => 'direct',
            'custom_domain_status' => 'active',
        ]);
        $event = $this->createEvent($role, ['name' => 'Offer Event', 'rsvp_enabled' => true]);

        $html = $this->get($this->guestEventUrl($role, $event))->assertOk()->getContent();
        $node = $this->nodeOfType($this->jsonLdBlocks($html), 'Event');
        $canonical = $this->canonical($html);

        $this->assertStringStartsWith('https://jsonld-offer.test/', $canonical);
        $this->assertSame($canonical, $node['url']);
        // RSVP, so a single free offer at the event's own URL, opening the registration form.
        $this->assertSame($canonical.'?rsvp=true', $node['offers']['url']);

        // With no tickets, no RSVP and no registration link the page offers nothing, and the node
        // used to invent a price-0, in-stock Offer for it all the same.
        $plain = $this->createEvent($role, ['name' => 'Plain Event']);
        $node = $this->nodeOfType($this->jsonLdBlocks($this->get($this->guestEventUrl($role, $plain))->assertOk()->getContent()), 'Event');
        $this->assertArrayNotHasKey('offers', $node);
        $this->assertArrayNotHasKey('isAccessibleForFree', $node);
    }

    public function test_organizer_and_performer_urls_use_the_custom_domain(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', [
            'name' => 'Domain Talent',
            'custom_domain' => 'https://jsonld-people.test',
            'custom_domain_mode' => 'direct',
            'custom_domain_status' => 'active',
        ]);
        $event = $this->createEvent($role, ['name' => 'Domain Event']);
        $event->creator_role_id = $role->id;
        $event->save();

        $html = $this->get($this->guestEventUrl($role, $event))->assertOk()->getContent();
        $node = $this->nodeOfType($this->jsonLdBlocks($html), 'Event');

        // The schedule serves on its own domain, so a subdomain URL here would name a host
        // other than the one the crawler is reading.
        $this->assertStringStartsWith('https://jsonld-people.test', $node['organizer']['url']);
        $this->assertStringStartsWith('https://jsonld-people.test', $node['performer']['url']);
    }

    /**
     * A dated occurrence canonicalizes to the series, so its url and offers name the series too -
     * while the dates stay the occurrence the page is about.
     */
    public function test_a_recurring_occurrence_points_its_url_and_offers_at_the_series(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['name' => 'Weekly Talent']);
        $event = $this->createRecurringEvent($role, [
            'name' => 'Weekly Event',
            'starts_at' => now()->addDays(1)->setTime(12, 0)->format('Y-m-d H:i:s'),
            'tickets_enabled' => true,
        ]);
        $this->createTicket($event, ['price' => 20]);
        $date = now()->addDays(15)->format('Y-m-d');

        $html = $this->get($this->guestEventUrl($role, $event, $date))->assertOk()->getContent();
        $node = $this->nodeOfType($this->jsonLdBlocks($html), 'Event');
        $canonical = $this->canonical($html);

        $this->assertSame($this->guestEventUrl($role, $event), $canonical, 'the canonical is the undated series URL');
        $this->assertSame($canonical, $node['url']);
        $this->assertSame($canonical.'?tickets=true', $node['offers']['url']);
        // Noon UTC is the morning of the same calendar day in the schedule's New York.
        $this->assertStringStartsWith($date.'T', $node['startDate'], 'startDate is the occurrence this page shows');
    }

    public function test_ticket_offers_point_at_the_canonical_with_the_ticket_flag(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', [
            'name' => 'Ticket Talent',
            'custom_domain' => 'https://jsonld-tickets.test',
            'custom_domain_mode' => 'direct',
            'custom_domain_status' => 'active',
        ]);
        $event = $this->createEvent($role, ['name' => 'Ticket Event', 'tickets_enabled' => true]);
        $this->createTicket($event, ['price' => 20]);
        $this->createTicket($event, ['price' => 0]);

        $html = $this->get($this->guestEventUrl($role, $event))->assertOk()->getContent();
        $node = $this->nodeOfType($this->jsonLdBlocks($html), 'Event');
        $canonical = $this->canonical($html);

        $this->assertCount(2, $node['offers']);
        foreach ($node['offers'] as $offer) {
            $this->assertSame($canonical.'?tickets=true', $offer['url']);
        }
    }

    private function canonical(string $html): string
    {
        $this->assertSame(1, preg_match('~<link rel="canonical" href="([^"]+)">~', $html, $m), 'no canonical link');

        return html_entity_decode($m[1]);
    }

    /**
     * Every block, decoded. Asserts each one parses and carries no raw "<": JSON_HEX_TAG escapes
     * it, and a raw one is how a "</script>" in owner text would close the element early.
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
