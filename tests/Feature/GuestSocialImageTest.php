<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The picture a guest page offers to WhatsApp, Facebook and X must be the owner's or none.
 *
 * Five sites in layouts/app-guest.blade.php used to fall back to /images/social/home.png - our
 * 1200x630 marketing card, which renders whatever the homepage hero currently says (at the time of
 * writing "Everything you have on. Booked solid.") - whenever the event
 * and the schedule had no image between them. None of the five was plan-gated, so a free
 * schedule's own link preview was an Event Schedule advert, on the surface a visitor sees BEFORE
 * they decide whether to tap. og:site_name already named the schedule, which made it worse: the
 * words were theirs and the picture was ours.
 *
 * Nothing is the correct fallback. A card with no image degrades to title and description, which
 * is the owner's text; an advert of ours does not degrade to anything they would have chosen.
 */
class GuestSocialImageTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    /**
     * The advert. Its presence on any guest surface is the bug.
     *
     * The DIRECTORY, not one filename. This used to be 'social/home.png', and the release that
     * re-encoded the social cards as JPEG changed layouts/app.blade.php's fallback to
     * social/home.jpg without changing this - so the guard silently stopped matching the thing it
     * guards against, and all ten assertions below would have passed with the advert back on
     * every guest page. Nothing under public/images/social is ever a schedule's own picture, so
     * the whole path is the right thing to forbid and it cannot be defanged by a re-encode again.
     */
    private const PLATFORM_AD = 'images/social/';

    private function role(array $attrs = []): Role
    {
        return $this->createRole($this->createOwner(), 'venue', $attrs);
    }

    /**
     * The og:image URL, or null if the page advertises none.
     *
     * Asserting on the bare filename instead is what these tests used to do, and it proved
     * nothing: createRole() defaults plan_type to 'enterprise', so every fixture role is Pro, and
     * app-guest.blade.php then emits <link rel="icon"> and <link rel="apple-touch-icon"> with the
     * same profile_image_url. A "the schedule's logo is the preview image" assertion was being
     * satisfied by the FAVICON, and passed with the og:image tag deleted outright.
     */
    private function ogImage(string $content): ?string
    {
        preg_match('/<meta property="og:image" content="([^"]*)"/', $content, $m);

        return $m[1] ?? null;
    }

    /** Same trap, same fix, for the Twitter card's image. */
    private function twitterImage(string $content): ?string
    {
        preg_match('/<meta name="twitter:image" content="([^"]*)"/', $content, $m);

        return $m[1] ?? null;
    }

    /**
     * The JSON-LD block describing the Event, as raw text.
     *
     * A guest page carries up to five ld+json blocks and two of them have an "image" key - the
     * Event's and the schedule's - so a page-wide assertion about "image" answers the wrong
     * question and breaks the day an unrelated block gains one. Callers must assert this is
     * non-empty first: an extraction that silently returns '' makes every assertStringNotContains
     * against it pass for free.
     */
    private function eventJsonLd(string $content): string
    {
        preg_match_all('#<script type="application/ld\+json"[^>]*>(.*?)</script>#s', $content, $m);

        foreach ($m[1] as $block) {
            // Decoded rather than string-matched: the layout encodes each block compactly
            // through SeoUtils::jsonLd(), so there is no '"@type": "Event"' spacing to match.
            if ((json_decode($block, true)['@type'] ?? null) === 'Event') {
                return $block;
            }
        }

        return '';
    }

    /**
     * FAILS before the change: emitted og:image and twitter:image pointing at PLATFORM_AD.
     */
    public function test_a_schedule_with_no_logo_advertises_no_image_at_all(): void
    {
        $role = $this->role(['profile_image_url' => null]);

        $content = $this->get('/'.$role->subdomain)->assertOk()->getContent();

        $this->assertStringNotContainsString(self::PLATFORM_AD, $content);
        $this->assertStringNotContainsString('property="og:image"', $content);
        $this->assertStringNotContainsString('name="twitter:image"', $content);
        $this->assertStringContainsString('<meta name="twitter:card" content="summary">', $content);
    }

    /** The positive half: a schedule that HAS a logo still gets a large card, of its own logo. */
    public function test_a_schedule_with_a_logo_advertises_its_own(): void
    {
        $role = $this->role(['profile_image_url' => 'profile_bluenote.png']);

        $content = $this->get('/'.$role->subdomain)->assertOk()->getContent();

        $this->assertStringNotContainsString(self::PLATFORM_AD, $content);
        $this->assertStringContainsString('profile_bluenote.png', (string) $this->ogImage($content));
        $this->assertStringContainsString('profile_bluenote.png', (string) $this->twitterImage($content));
        $this->assertStringContainsString('<meta name="twitter:card" content="summary_large_image">', $content);
    }

    /**
     * FAILS before the change. Event::getImageUrl() already cascades flyer -> schedule logo ->
     * venue logo, so a null here means the owner genuinely has no image anywhere - which is
     * exactly the case that used to be filled with ours.
     */
    public function test_an_event_with_no_image_anywhere_advertises_none(): void
    {
        $role = $this->role(['profile_image_url' => null]);
        $event = $this->createEvent($role, ['creator_role_id' => $role->id]);

        $content = $this->get($this->guestEventUrl($role, $event))->assertOk()->getContent();

        $this->assertStringNotContainsString(self::PLATFORM_AD, $content);
        $this->assertStringNotContainsString('property="og:image"', $content);
        $this->assertStringContainsString('<meta name="twitter:card" content="summary">', $content);
    }

    /** The cascade is untouched: no flyer still finds the schedule's logo before giving up. */
    public function test_an_event_with_no_flyer_still_falls_back_to_the_schedules_logo(): void
    {
        $role = $this->role(['profile_image_url' => 'profile_bluenote.png']);
        $event = $this->createEvent($role, ['creator_role_id' => $role->id]);

        $content = $this->get($this->guestEventUrl($role, $event))->assertOk()->getContent();

        $this->assertStringNotContainsString(self::PLATFORM_AD, $content);
        $this->assertStringContainsString('profile_bluenote.png', (string) $this->ogImage($content));
    }

    /**
     * FAILS before the change: the password branch was the only one that never even tried an
     * image of the owner's, so it served PLATFORM_AD unconditionally.
     *
     * It now serves the SCHEDULE's logo rather than Event::getImageUrl(), because the flyer of a
     * password-protected event is precisely what its owner chose not to publish. Removing our
     * advert must not replace it with a leak.
     */
    public function test_a_password_gated_event_shows_the_schedule_not_the_flyer(): void
    {
        $role = $this->role(['profile_image_url' => 'profile_bluenote.png']);
        $event = $this->createEvent($role, [
            'creator_role_id' => $role->id,
            'event_password' => 'letmein',
            'flyer_image_url' => 'secret_flyer.png',
        ]);

        $content = $this->get($this->guestEventUrl($role, $event))->assertOk()->getContent();

        $this->assertStringNotContainsString(self::PLATFORM_AD, $content);
        $this->assertStringNotContainsString('secret_flyer.png', $content);
        $this->assertStringContainsString('profile_bluenote.png', (string) $this->ogImage($content));
    }

    /** And with no schedule logo either, a gated event advertises nothing rather than us. */
    public function test_a_password_gated_event_with_no_schedule_logo_advertises_none(): void
    {
        $role = $this->role(['profile_image_url' => null]);
        $event = $this->createEvent($role, [
            'creator_role_id' => $role->id,
            'event_password' => 'letmein',
            'flyer_image_url' => 'secret_flyer.png',
        ]);

        $content = $this->get($this->guestEventUrl($role, $event))->assertOk()->getContent();

        $this->assertStringNotContainsString(self::PLATFORM_AD, $content);
        $this->assertStringNotContainsString('secret_flyer.png', $content);
        $this->assertStringNotContainsString('property="og:image"', $content);
    }

    /**
     * FAILS before the change. The JSON-LD block was already @if-guarded, so dropping the
     * fallback is enough - but the assertion matters: this one told Google, not just a chat app,
     * that somebody else's event looks like an Event Schedule advert.
     */
    public function test_the_event_json_ld_omits_its_image_rather_than_advertising_us(): void
    {
        $role = $this->role(['profile_image_url' => null]);
        $event = $this->createEvent($role, ['creator_role_id' => $role->id]);

        $content = $this->get($this->guestEventUrl($role, $event))->assertOk()->getContent();

        $eventBlock = $this->eventJsonLd($content);

        // Without this the assertion below passes on an empty string and proves nothing.
        $this->assertNotSame('', $eventBlock, 'No Event JSON-LD block was found to inspect.');
        $this->assertStringNotContainsString('"image"', $eventBlock);
    }

    /**
     * The scoping above has to survive a page that legitimately carries "image" elsewhere.
     *
     * A schedule with a logo emits it in its OWN Organization/Person block, and Event::getImageUrl()
     * cascades to that same logo - so both blocks carry an image and a page-wide assertion would
     * be meaningless here. What must hold is that each block describes its own subject.
     */
    public function test_the_json_ld_image_assertion_is_scoped_to_the_event_block(): void
    {
        $role = $this->role(['profile_image_url' => 'profile_bluenote.png']);
        $event = $this->createEvent($role, ['creator_role_id' => $role->id]);

        $content = $this->get($this->guestEventUrl($role, $event))->assertOk()->getContent();
        $eventBlock = $this->eventJsonLd($content);

        $this->assertNotSame('', $eventBlock);
        $this->assertStringNotContainsString(self::PLATFORM_AD, $eventBlock);
        $this->assertStringContainsString('profile_bluenote.png', $eventBlock);
    }

    /**
     * A wide header the owner uploaded is what a large card is shaped for, so it comes before
     * their square logo (Role::shareImage()).
     */
    public function test_a_schedule_prefers_its_uploaded_header_to_its_logo(): void
    {
        // header_image blank is the edit form's "custom" option: the upload is the header.
        $role = $this->role([
            'profile_image_url' => 'profile_bluenote.png',
            'header_image' => '',
            'header_image_url' => 'header_bluenote.png',
        ]);

        $content = $this->get('/'.$role->subdomain)->assertOk()->getContent();

        $this->assertStringContainsString('header_bluenote.png', (string) $this->ogImage($content));
        $this->assertStringContainsString('header_bluenote.png', (string) $this->twitterImage($content));
        $this->assertStringContainsString('<meta name="twitter:card" content="summary_large_image">', $content);
    }

    /**
     * A built-in header is stock art, not the owner's picture. With nothing uploaded the page
     * still advertises no image - even though an upload from before they picked the built-in one
     * is still stored.
     */
    public function test_a_built_in_header_or_background_is_never_the_preview(): void
    {
        $role = $this->role([
            'profile_image_url' => null,
            'header_image' => 'Arena',
            'header_image_url' => 'header_old_upload.png',
            'background' => 'image',
            'background_image' => 'Abstract_Sunrise',
            'background_image_url' => 'background_old_upload.png',
        ]);

        $content = $this->get('/'.$role->subdomain)->assertOk()->getContent();

        $this->assertStringNotContainsString('property="og:image"', $content);
        $this->assertStringNotContainsString('name="twitter:image"', $content);
        $this->assertStringContainsString('<meta name="twitter:card" content="summary">', $content);
    }

    /** With no header and no logo, the background the owner uploaded is still theirs. */
    public function test_a_schedule_falls_back_to_its_uploaded_background(): void
    {
        $role = $this->role([
            'profile_image_url' => null,
            'header_image' => 'none',
            'background' => 'image',
            'background_image' => null,
            'background_image_url' => 'background_bluenote.png',
        ]);

        $content = $this->get('/'.$role->subdomain)->assertOk()->getContent();

        $this->assertStringContainsString('background_bluenote.png', (string) $this->ogImage($content));
        $this->assertStringNotContainsString(self::PLATFORM_AD, $content);
    }

    /**
     * An event with no flyer, performer or venue picture falls back to the schedule that created
     * it - a curator's logo on a curated listing - before giving up (Event::shareImage()).
     */
    public function test_an_event_falls_back_to_the_logo_of_the_schedule_that_created_it(): void
    {
        $curator = $this->createCurator($this->createOwner(), ['profile_image_url' => 'profile_curator.png']);
        $venue = $this->role(['profile_image_url' => null]);
        $event = $this->createEvent($venue, ['creator_role_id' => $curator->id]);
        $event->roles()->attach($curator->id, ['is_accepted' => true]);

        $content = $this->get($this->guestEventUrl($venue, $event))->assertOk()->getContent();

        $this->assertStringContainsString('profile_curator.png', (string) $this->ogImage($content));
    }

    /**
     * og:image:width and og:image:height are emitted only when the file's real size is known -
     * today, a file this app serves itself - never guessed: scrapers crop to a declared size.
     */
    public function test_image_dimensions_are_declared_only_when_known(): void
    {
        $role = $this->role(['profile_image_url' => null]);

        // A bundled demo flyer: a real 800x600 file under public/.
        $known = $this->createEvent($role, ['creator_role_id' => $role->id, 'flyer_image_url' => 'demo_flyer_jazz.jpg']);
        $content = $this->get($this->guestEventUrl($role, $known))->assertOk()->getContent();

        $this->assertStringContainsString('demo_flyer_jazz.jpg', (string) $this->ogImage($content));
        $this->assertStringContainsString('<meta property="og:image:width" content="800">', $content);
        $this->assertStringContainsString('<meta property="og:image:height" content="600">', $content);

        // An upload with no readable file behind it: the image, and no size.
        $unknown = $this->createEvent($role, ['creator_role_id' => $role->id, 'flyer_image_url' => 'flyer_missing.png']);
        $content = $this->get($this->guestEventUrl($role, $unknown))->assertOk()->getContent();

        $this->assertStringContainsString('flyer_missing.png', (string) $this->ogImage($content));
        $this->assertStringNotContainsString('og:image:width', $content);
        $this->assertStringNotContainsString('og:image:height', $content);
    }

    /**
     * FAILS before the change: ticket/view.blade.php set no `meta` slot, so it fell through to
     * layouts/app.blade.php's default block - which names "Event Schedule" in og:title and
     * og:site_name and offers PLATFORM_AD as og:image.
     *
     * A ticket belongs to the schedule that sold it, and buyers forward these links. The page is
     * noindex either way, so it now keeps the robots tag and says nothing else.
     */
    public function test_a_ticket_page_carries_no_identity_of_ours(): void
    {
        $role = $this->role();
        $event = $this->createEvent($role, ['creator_role_id' => $role->id]);
        $ticket = $this->createTicket($event, ['price' => 0]);
        $sale = $this->createSale($event, $role, ['name' => 'Ticket Holder'], $ticket, 1);

        $content = $this->get(route('ticket.view', [
            'event_id' => UrlUtils::encodeId($event->id),
            'secret' => $sale->secret,
        ]))->assertOk()->getContent();

        $this->assertStringNotContainsString(self::PLATFORM_AD, $content);
        $this->assertStringNotContainsString('property="og:image"', $content);
        $this->assertStringNotContainsString('content="Event Schedule"', $content);
        $this->assertStringContainsString('name="robots" content="noindex, nofollow"', $content);
    }
}
