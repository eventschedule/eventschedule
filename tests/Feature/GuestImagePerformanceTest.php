<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The guest pages' images, measured against what the lab found: a schedule home's mobile LCP was
 * 11.2 s, its largest paint the owner's uploaded BACKGROUND served as a 1.1MB original through
 * CSS, found late and fetched at default priority. The event flyer was loading="lazy" with no
 * srcset, the high-priority image on the event page was one a phone renders below it, and the
 * calendar cards requested originals - with header images that were dead URLs.
 *
 * Every derivative here is recorded by hand (recordImageVariants()), exactly as the generation
 * job and the backfill write them; ImageVariantsTest covers making them.
 */
class GuestImagePerformanceTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Role's saving hook geocodes a new address whenever a Google key is configured, which a
        // developer's .env may carry.
        config(['services.google.backend' => null]);

        // The created hooks queue a generation job per upload; on the `sync` queue they would run
        // against fixture files that do not exist and record a `missing` skip.
        Queue::fake();
    }

    private function page(string $path): string
    {
        return $this->get($path)->assertOk()->getContent();
    }

    /** The one <img> tag whose markup contains $needle (typically its src). */
    private function imgTag(string $html, string $needle): string
    {
        preg_match_all('/<img\b[^>]*>/s', $html, $m);

        $tags = array_values(array_filter($m[0], fn (string $tag) => str_contains($tag, $needle)));
        $this->assertCount(1, $tags, "Expected exactly one <img> containing {$needle}");

        return $tags[0];
    }

    private function highPriorityCount(string $html): int
    {
        return substr_count($html, 'fetchpriority="high"');
    }

    private function scheduleWithUploadedBackground(array $attrs = []): Role
    {
        return $this->createRole($this->createOwner(), 'venue', $attrs + [
            'name' => 'Blue Room',
            'profile_image_url' => 'profile_abc.png',
            'background' => 'image',
            'background_image' => null,
            'background_image_url' => 'background_abc.png',
        ]);
    }

    private function recordBackgroundVariants(Role $role): void
    {
        $role->recordImageVariants(
            ['w960' => 'background_abc_w960.webp', 'w1920' => 'background_abc_w1920.webp', 'src' => ['w' => 3000, 'h' => 4000]],
            'background'
        );
    }

    // ------------------------------------------------------------ schedule page

    public function test_a_schedule_with_an_uploaded_background_preloads_its_phone_derivative(): void
    {
        $role = $this->scheduleWithUploadedBackground();
        $this->recordBackgroundVariants($role);

        $html = $this->page('/'.$role->subdomain);
        $phone = url('/storage/background_abc_w960.webp');

        $this->assertStringContainsString(
            '<link rel="preload" as="image" href="'.$phone.'" media="(max-width: 767px)" fetchpriority="high">',
            $html
        );
        // The banner a phone paints is that same file, so the preload is not a second download...
        $this->assertStringContainsString("background-image: url('".$phone."');", $html);
        // ...and the desktop background is the 1920 derivative. Neither is the original.
        $this->assertStringContainsString('url("'.url('/storage/background_abc_w1920.webp').'")', $html);
        $this->assertStringNotContainsString("url('".url('/storage/background_abc.png')."')", $html);
        $this->assertStringNotContainsString('url("'.url('/storage/background_abc.png').'")', $html);
    }

    public function test_until_the_derivatives_exist_the_original_is_what_is_preloaded(): void
    {
        $role = $this->scheduleWithUploadedBackground();

        $html = $this->page('/'.$role->subdomain);
        $original = url('/storage/background_abc.png');

        $this->assertStringContainsString('<link rel="preload" as="image" href="'.$original.'"', $html);
        $this->assertStringContainsString("background-image: url('".$original."');", $html);
    }

    public function test_a_built_in_background_preloads_its_bundled_webp(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue', [
            'name' => 'Blue Room',
            'background' => 'image',
            'background_image' => 'Abstract_Sunrise',
        ]);

        $html = $this->page('/'.$role->subdomain);

        $this->assertStringContainsString(
            '<link rel="preload" as="image" href="'.asset('images/backgrounds/Abstract_Sunrise.webp').'" media="(max-width: 767px)" fetchpriority="high">',
            $html
        );
    }

    /**
     * The preload names the banner role/show-guest paints, so only that page carries it: an event
     * page paints its background differently, and an embed or a graphic paints no banner at all.
     */
    public function test_only_the_schedule_page_preloads_the_banner(): void
    {
        $role = $this->scheduleWithUploadedBackground();
        $this->recordBackgroundVariants($role);
        $event = $this->createEvent($role, ['name' => 'Autumn Session', 'creator_role_id' => $role->id]);

        $this->assertStringContainsString('rel="preload" as="image"', $this->page('/'.$role->subdomain), 'fixture: the schedule page does');

        foreach ([
            'the event page' => $this->guestEventUrl($role, $event),
            'the embed' => '/'.$role->subdomain.'?embed=true',
            'the graphic' => '/'.$role->subdomain.'?graphic=1',
        ] as $label => $path) {
            $this->assertStringNotContainsString('rel="preload" as="image"', $this->page($path), $label);
        }

        $solid = $this->createRole($this->createOwner(), 'venue', ['name' => 'Plain Room', 'background' => 'solid', 'background_color' => '#223344']);
        $this->assertStringNotContainsString('rel="preload" as="image"', $this->page('/'.$solid->subdomain), 'no image background');
    }

    /** An event page paints its background at every width: the phone's derivative, then md up. */
    public function test_an_event_page_paints_the_phone_derivative_and_the_desktop_one_from_md(): void
    {
        $role = $this->scheduleWithUploadedBackground();
        $this->recordBackgroundVariants($role);
        $event = $this->createEvent($role, ['name' => 'Autumn Session', 'creator_role_id' => $role->id]);

        $html = $this->page($this->guestEventUrl($role, $event));

        $this->assertMatchesRegularExpression(
            '#url\("'.preg_quote(url('/storage/background_abc_w960.webp'), '#').'"\);\s*@media \(min-width: 768px\) \{\s*background-image:\s*url\("'.preg_quote(url('/storage/background_abc_w1920.webp'), '#').'"\);#',
            $html
        );
    }

    public function test_an_uploaded_header_offers_its_derivatives_its_shape_and_the_page_priority(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue', [
            'name' => 'Blue Room',
            'profile_image_url' => 'profile_abc.png',
            'header_image' => '',
            'header_image_url' => 'header_abc.png',
        ]);
        $role->recordImageVariants(
            ['w960' => 'header_abc_w960.webp', 'w1920' => 'header_abc_w1920.webp', 'src' => ['w' => 3000, 'h' => 1500]],
            'header'
        );
        $role->recordImageVariants(['w480' => 'profile_abc_w480.webp', 'w960' => 'profile_abc_w960.webp']);

        $html = $this->page('/'.$role->subdomain);
        $header = $this->imgTag($html, 'header_abc_w960.webp');

        $this->assertStringContainsString('src="'.url('/storage/header_abc_w960.webp').'"', $header);
        $this->assertStringContainsString(
            'srcset="'.url('/storage/header_abc_w960.webp').' 960w, '.url('/storage/header_abc_w1920.webp').' 1920w"',
            $header
        );
        $this->assertStringContainsString('sizes="(min-width: 1536px) 1496px, calc(100vw - 40px)"', $header);
        $this->assertStringContainsString('width="3000" height="1500"', $header);
        $this->assertStringContainsString('fetchpriority="high"', $header, 'With no image background the header is the LCP candidate');

        // The profile photo: its 480 derivative in a box of a known size.
        $avatar = $this->imgTag($html, 'profile_abc_w480.webp');
        $this->assertStringContainsString('width="120" height="120"', $avatar);
        $this->assertStringNotContainsString('src="'.url('/storage/profile_abc.png').'"', $html);
    }

    public function test_before_its_derivatives_exist_an_uploaded_header_is_its_plain_original(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue', [
            'name' => 'Blue Room',
            'header_image' => '',
            'header_image_url' => 'header_abc.png',
        ]);

        $header = $this->imgTag($this->page('/'.$role->subdomain), url('/storage/header_abc.png'));

        $this->assertStringNotContainsString('srcset=', $header);
        $this->assertStringNotContainsString('width=', $header, 'No size is declared that the file might not have');
    }

    public function test_a_built_in_header_declares_its_size(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue', ['name' => 'Blue Room', 'header_image' => 'Arena']);

        $header = $this->imgTag($this->page('/'.$role->subdomain), 'images/headers/Arena.png');

        $this->assertStringContainsString('width="1536" height="768"', $header);
        $this->assertStringContainsString('fetchpriority="high"', $header);
    }

    public function test_the_compact_header_avatar_is_the_small_derivative(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue', [
            'name' => 'Blue Room',
            'header_style' => 'compact',
            'profile_image_url' => 'profile_abc.png',
        ]);
        $role->recordImageVariants(['w480' => 'profile_abc_w480.webp', 'w960' => 'profile_abc_w960.webp']);

        $avatar = $this->imgTag($this->page('/'.$role->subdomain), 'profile_abc_w480.webp');

        $this->assertStringContainsString('width="40" height="40"', $avatar);
    }

    // --------------------------------------------------------------- event page

    public function test_the_flyer_is_eager_with_its_derivatives_and_its_shape(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue', ['name' => 'Blue Room', 'profile_image_url' => 'profile_abc.png']);
        $event = $this->createEvent($role, ['name' => 'Autumn Session', 'creator_role_id' => $role->id, 'flyer_image_url' => 'flyer_abc.png']);
        $event->recordImageVariants(['w480' => 'flyer_abc_w480.webp', 'w960' => 'flyer_abc_w960.webp', 'src' => ['w' => 1600, 'h' => 2133]]);

        $html = $this->page($this->guestEventUrl($role, $event));

        $this->assertSame(1, preg_match('#<div id="gp-flyer"[^>]*>(.*?)</div>#s', $html, $m), 'The #gp-flyer hook is still there');
        $section = $m[1];

        // A real link to the full-size original: what a click opens without JavaScript.
        $this->assertStringContainsString('<a href="'.url('/storage/flyer_abc.png').'" data-flyer-open', $section);

        $flyer = $this->imgTag($section, 'flyer_abc_w960.webp');
        $this->assertStringContainsString('src="'.url('/storage/flyer_abc_w960.webp').'"', $flyer);
        $this->assertStringContainsString(
            'srcset="'.url('/storage/flyer_abc_w480.webp').' 480w, '.url('/storage/flyer_abc_w960.webp').' 960w, '.url('/storage/flyer_abc.png').' 1600w"',
            $flyer
        );
        $this->assertStringContainsString('sizes="(min-width: 1024px) 564px, (min-width: 640px) calc(100vw - 40px), 100vw"', $flyer);
        $this->assertStringContainsString('width="1600" height="2133"', $flyer);
        $this->assertStringContainsString('fetchpriority="high"', $flyer);
        $this->assertStringNotContainsString('loading="lazy"', $flyer);

        // The Alpine lightbox is gone; the Vue app replacing it is mounted after the page.
        $this->assertStringNotContainsString('flyerOpen', $html);
        $this->assertStringContainsString('<div id="flyer-lightbox-app"></div>', $html);
        $this->assertStringContainsString("closest('[data-flyer-open]')", $html);
    }

    public function test_a_flyer_without_derivatives_is_still_eager_and_linked(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue', ['name' => 'Blue Room']);
        $event = $this->createEvent($role, ['name' => 'Autumn Session', 'creator_role_id' => $role->id, 'flyer_image_url' => 'flyer_abc.png']);

        $flyer = $this->imgTag($this->page($this->guestEventUrl($role, $event)), 'flyer_abc.png');

        $this->assertStringContainsString('src="'.url('/storage/flyer_abc.png').'"', $flyer);
        $this->assertStringNotContainsString('srcset=', $flyer);
        $this->assertStringContainsString('fetchpriority="high"', $flyer);
        $this->assertStringNotContainsString('loading="lazy"', $flyer);
    }

    /**
     * With no flyer, the square hero is the picture a phone sees: its 960 derivative and a srcset,
     * and the page's one high-priority image. With a flyer it steps aside.
     */
    public function test_the_hero_fallback_is_a_derivative_and_high_priority_only_without_a_flyer(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue', ['name' => 'Blue Room', 'profile_image_url' => 'profile_abc.png']);
        $role->recordImageVariants(['w480' => 'profile_abc_w480.webp', 'w960' => 'profile_abc_w960.webp']);

        $plain = $this->createEvent($role, ['name' => 'Autumn Session', 'creator_role_id' => $role->id]);
        $html = $this->page($this->guestEventUrl($role, $plain));

        $this->assertSame(1, preg_match('#<div id="gp-event-hero-image"[^>]*>\s*(<img\b[^>]*>)#s', $html, $m));
        $hero = $m[1];
        $this->assertStringContainsString('src="'.url('/storage/profile_abc_w960.webp').'"', $hero);
        $this->assertStringContainsString(url('/storage/profile_abc_w480.webp').' 480w', $hero);
        $this->assertStringContainsString('fetchpriority="high"', $hero);

        $withFlyer = $this->createEvent($role, ['name' => 'Winter Session', 'creator_role_id' => $role->id, 'flyer_image_url' => 'flyer_abc.png']);
        $html = $this->page($this->guestEventUrl($role, $withFlyer));

        $this->assertSame(1, preg_match('#<div id="gp-event-hero-image"[^>]*>\s*(<img\b[^>]*>)#s', $html, $m), 'fixture: the hero still renders');
        $this->assertStringNotContainsString('fetchpriority', $m[1]);
    }

    /**
     * A browser gives "high" meaning only by comparison, so a page gets one: the image that is
     * actually its largest paint.
     */
    public function test_every_page_asks_for_exactly_one_high_priority_image(): void
    {
        $owner = $this->createOwner();

        $headerOnly = $this->createRole($owner, 'venue', ['name' => 'Header Room', 'header_image' => 'Arena', 'profile_image_url' => 'profile_abc.png']);
        $this->assertSame(1, $this->highPriorityCount($this->page('/'.$headerOnly->subdomain)), 'schedule: the header');

        $both = $this->scheduleWithUploadedBackground(['header_image' => '', 'header_image_url' => 'header_abc.png']);
        $html = $this->page('/'.$both->subdomain);
        $this->assertSame(1, $this->highPriorityCount($html), 'schedule with an image background: the preload');
        $this->assertStringNotContainsString('fetchpriority', $this->imgTag($html, 'header_abc.png'));

        $event = $this->createEvent($headerOnly, ['name' => 'Autumn Session', 'creator_role_id' => $headerOnly->id, 'flyer_image_url' => 'flyer_abc.png']);
        $html = $this->page($this->guestEventUrl($headerOnly, $event));
        $this->assertStringContainsString('id="gp-event-hero-image"', $html, 'fixture: a hero AND a flyer on one page');
        $this->assertSame(1, $this->highPriorityCount($html), 'event with a flyer: the flyer');

        $plain = $this->createEvent($headerOnly, ['name' => 'Winter Session', 'creator_role_id' => $headerOnly->id]);
        $this->assertSame(1, $this->highPriorityCount($this->page($this->guestEventUrl($headerOnly, $plain))), 'event without one: the hero');
    }

    public function test_performer_and_venue_cards_use_derivatives_and_working_headers(): void
    {
        $owner = $this->createOwner();
        $curator = $this->createCurator($owner, ['name' => 'Night Guide']);
        $talent = $this->createRole($owner, 'talent', [
            'name' => 'The Quartet',
            'profile_image_url' => 'profile_talent.png',
            'header_image' => '',
            'header_image_url' => 'header_talent.png',
        ]);
        $talent->recordImageVariants(['w480' => 'profile_talent_w480.webp', 'w960' => 'profile_talent_w960.webp']);
        $talent->recordImageVariants(['w960' => 'header_talent_w960.webp', 'w1920' => 'header_talent_w1920.webp', 'src' => ['w' => 2000, 'h' => 800]], 'header');
        $venue = $this->createRole($owner, 'venue', [
            'name' => 'Blue Room',
            'profile_image_url' => 'profile_venue.png',
            'header_image' => 'none',
            'header_image_url' => 'header_stale.png',
        ]);
        $venue->recordImageVariants(['w480' => 'profile_venue_w480.webp', 'w960' => 'profile_venue_w960.webp']);

        // An act with a photo and no header gets the full-width square instead.
        $solo = $this->createRole($owner, 'talent', ['name' => 'Solo Act', 'profile_image_url' => 'profile_solo.png']);
        $solo->recordImageVariants(['w480' => 'profile_solo_w480.webp', 'w960' => 'profile_solo_w960.webp']);

        $event = $this->createEvent($curator, ['name' => 'Autumn Session', 'creator_role_id' => $curator->id]);
        $event->roles()->attach($talent->id, ['is_accepted' => true]);
        $event->roles()->attach($solo->id, ['is_accepted' => true]);
        $event->roles()->attach($venue->id, ['is_accepted' => true]);

        $html = $this->page($this->guestEventUrl($curator, $event));

        $square = $this->imgTag($html, 'profile_solo_w960.webp');
        $this->assertStringContainsString('srcset="'.url('/storage/profile_solo_w480.webp').' 480w, '.url('/storage/profile_solo_w960.webp').' 960w"', $square);
        $this->assertStringContainsString('sizes="(min-width: 1024px) 340px, (min-width: 640px) calc(100vw - 80px), calc(100vw - 40px)"', $square);

        $talentHeader = $this->imgTag($html, 'header_talent_w960.webp');
        $this->assertStringContainsString('width="2000" height="800"', $talentHeader);
        $this->assertStringContainsString('profile_talent_w480.webp', $html, 'The 90px avatar over the header');
        $this->assertStringContainsString('profile_venue_w480.webp', $html, 'The 44px venue badge');
        $this->assertStringNotContainsString('src="'.url('/storage/profile_talent.png').'"', $html);
        $this->assertStringNotContainsString('src="'.url('/storage/profile_venue.png').'"', $html);
        // "None" is the venue's choice: an upload left behind it is not its header.
        $this->assertStringNotContainsString('header_stale.png', $html);
    }

    // ------------------------------------------------------------ card payloads

    public function test_the_card_payload_carries_card_sizes_and_a_header_that_exists(): void
    {
        $owner = $this->createOwner();
        $curator = $this->createCurator($owner, ['name' => 'Night Guide']);
        $venue = $this->createRole($owner, 'venue', ['name' => 'Blue Room', 'header_image' => 'Arena', 'profile_image_url' => 'profile_venue.png']);
        $venue->recordImageVariants(['w480' => 'profile_venue_w480.webp', 'w960' => 'profile_venue_w960.webp']);
        $talent = $this->createRole($owner, 'talent', ['name' => 'The Quartet', 'header_image' => '', 'header_image_url' => 'header_talent.png', 'profile_image_url' => 'profile_talent.png']);
        $talent->recordImageVariants(['w960' => 'header_talent_w960.webp', 'w1920' => 'header_talent_w1920.webp'], 'header');

        $event = $this->createEvent($curator, ['name' => 'Autumn Session', 'creator_role_id' => $curator->id, 'flyer_image_url' => 'flyer_abc.png']);
        $event->roles()->attach($venue->id, ['is_accepted' => true]);
        $event->roles()->attach($talent->id, ['is_accepted' => true]);
        $event->recordImageVariants(['w480' => 'flyer_abc_w480.webp', 'w960' => 'flyer_abc_w960.webp', 'src' => ['w' => 1600, 'h' => 2133]]);

        $row = collect($this->getJson(route('role.calendar_events', [
            'subdomain' => $curator->subdomain,
            'year' => now()->addDays(7)->year,
            'month' => now()->addDays(7)->month,
        ]))->assertOk()->json('events'))->firstWhere('id', UrlUtils::encodeId($event->id));

        $this->assertNotNull($row, 'fixture: the event is in the month payload');
        $this->assertSame(url('/storage/flyer_abc.png'), $row['image_url'], 'The full-size fallback stays');
        $this->assertSame(url('/storage/flyer_abc_w480.webp'), $row['image_thumb_url']);
        $this->assertSame(url('/storage/flyer_abc_w480.webp').' 480w, '.url('/storage/flyer_abc_w960.webp').' 960w', $row['image_srcset']);
        $this->assertSame(url('/storage/flyer_abc.png'), $row['flyer_url']);
        $this->assertSame(url('/storage/flyer_abc_w480.webp'), $row['flyer_thumb_url']);
        $this->assertSame($row['image_srcset'], $row['flyer_srcset']);
        $this->assertSame(1600, $row['flyer_width']);
        $this->assertSame(2133, $row['flyer_height']);
        $this->assertSame(url('/storage/profile_venue_w480.webp'), $row['venue_profile_image']);
        // A built-in header is its bundled WebP - it used to be /storage/Arena, which 404s.
        $this->assertSame(asset('images/headers/Arena.webp'), $row['venue_header_image']);
        $this->assertSame(url('/storage/header_talent_w960.webp'), $row['talent'][0]['header_image'], 'An uploaded header works too');
    }

    public function test_a_password_protected_card_carries_no_image_at_all(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['name' => 'Blue Room', 'header_image' => 'Arena', 'profile_image_url' => 'profile_venue.png']);
        $event = $this->createEvent($role, [
            'name' => 'Private Session',
            'creator_role_id' => $role->id,
            'flyer_image_url' => 'flyer_secret.png',
            'event_password' => 'letmein',
        ]);
        $event->recordImageVariants(['w480' => 'flyer_secret_w480.webp', 'w960' => 'flyer_secret_w960.webp', 'src' => ['w' => 1600, 'h' => 2133]]);

        $response = $this->getJson(route('role.calendar_events', [
            'subdomain' => $role->subdomain,
            'year' => now()->addDays(7)->year,
            'month' => now()->addDays(7)->month,
        ]))->assertOk();

        $row = collect($response->json('events'))->firstWhere('id', UrlUtils::encodeId($event->id));
        $this->assertNotNull($row, 'fixture: the event is in the month payload');

        foreach (array_keys($event->fresh()->cardImageFields()) as $key) {
            $this->assertArrayHasKey($key, $row);
            $this->assertNull($row[$key], "{$key} must not leak from a password-protected event");
        }
        $this->assertStringNotContainsString('flyer_secret', $response->getContent());
    }

    public function test_the_past_events_payload_carries_the_same_image_fields(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['name' => 'Blue Room']);
        $event = $this->createEvent($role, [
            'name' => 'Last Winter Session',
            'creator_role_id' => $role->id,
            'starts_at' => now()->subMonths(2)->format('Y-m-d H:i:s'),
            'flyer_image_url' => 'flyer_past.png',
        ]);
        $event->recordImageVariants(['w480' => 'flyer_past_w480.webp', 'w960' => 'flyer_past_w960.webp']);

        $row = collect($this->getJson(route('role.list_past_events', [
            'subdomain' => $role->subdomain,
            'before' => now()->format('Y-m-d'),
        ]))->assertOk()->json('events'))->firstWhere('id', UrlUtils::encodeId($event->id));

        $this->assertNotNull($row, 'fixture: the event is in the past payload');
        // flyer_url is new here: past cards could never render their flyer column before.
        $this->assertSame(url('/storage/flyer_past.png'), $row['flyer_url']);
        $this->assertSame(url('/storage/flyer_past_w480.webp'), $row['image_thumb_url']);
        $this->assertSame(url('/storage/flyer_past_w480.webp'), $row['flyer_thumb_url']);
    }

    // -------------------------------------------------------- the Role helpers

    public function test_header_and_background_urls_follow_what_the_owner_selected(): void
    {
        $owner = $this->createOwner();

        $none = $this->createRole($owner, 'venue', ['header_image' => 'none', 'header_image_url' => 'header_stale.png']);
        $logos = $this->createRole($owner, 'venue', ['header_image' => 'logos', 'header_image_url' => 'header_stale.png']);
        $builtIn = $this->createRole($owner, 'venue', ['header_image' => 'Arena', 'header_image_url' => 'header_stale.png']);
        $custom = $this->createRole($owner, 'venue', ['header_image' => '', 'header_image_url' => 'header_abc.png']);
        $custom->recordImageVariants(['w960' => 'header_abc_w960.webp', 'w1920' => null, 'skipped' => 'write_failed'], 'header');

        $this->assertNull($none->headerImageUrl(960));
        $this->assertNull($logos->headerImageUrl(960));
        $this->assertSame(asset('images/headers/Arena.webp'), $builtIn->headerImageUrl(960));
        $this->assertSame([1536, 768], $builtIn->headerImageDimensions());
        $this->assertSame(url('/storage/header_abc_w960.webp'), $custom->headerImageUrl(960));
        $this->assertSame(url('/storage/header_abc.png'), $custom->headerImageUrl(1920), 'A width with no derivative is the original');
        $this->assertSame(url('/storage/header_abc.png'), $custom->headerImageUrl());

        $solid = $this->createRole($owner, 'venue', ['background' => 'solid', 'background_image_url' => 'background_stale.png']);
        $bundled = $this->createRole($owner, 'venue', ['background' => 'image', 'background_image' => 'Abstract_Sunrise']);

        $this->assertNull($solid->backgroundImageUrl(960), 'Only an image background is an image');
        $this->assertSame(asset('images/backgrounds/Abstract_Sunrise.webp'), $bundled->backgroundImageUrl(1920));
    }
}
