<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventPart;
use App\Models\Role;
use App\Models\User;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The gp-* section ids on the guest portal.
 *
 * These ids are a published interface: /docs/schedule-styling#hiding-sections tells Pro owners to
 * write `#gp-talent-list { display: none }` against them, so a panel that loses its id, or gains
 * one nobody documented, breaks or hides a feature people were told they could rely on. The two
 * drift tests below are the point of this file - the render test only proves the ids reach the
 * page at all.
 *
 * Mutually exclusive branches deliberately SHARE an id (the five event status bars, the two map
 * branches, the banner and compact headers, the RSVP and ticket price rows, the RSVP and ticket
 * forms), exactly as #event-form-section and #schedule-header did before the gp- rename. Only one
 * of each ever renders, so the duplicate ids in source are not duplicates in the DOM.
 */
class GuestSectionIdTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    /** Blade files that may declare a gp-* id. Keep in step with the docs table. */
    private const GUEST_VIEWS = [
        'resources/views/role/show-guest.blade.php',
        'resources/views/event/show-guest.blade.php',
        'resources/views/event/partials/interest-capture.blade.php',
        'resources/views/partials/subscribe-panel.blade.php',
        'resources/views/components/sponsor-grid.blade.php',
        'resources/views/components/stay22-map.blade.php',
        'resources/views/role/partials/guest-banner.blade.php',
        'resources/views/role/partials/headers/banner.blade.php',
        'resources/views/role/partials/headers/compact.blade.php',
        'resources/views/role/partials/headers/below-bar.blade.php',
        'resources/views/layouts/app-guest.blade.php',
    ];

    private const DOCS_VIEW = 'resources/views/marketing/docs/schedule-styling.blade.php';

    /** Suffixes of an id that names a part of a panel, never the panel itself. */
    private const NOT_A_PANEL = ['gp-subscribe-heading', 'gp-event-interest-error'];

    /** @return list<string> */
    private function idsInMarkup(): array
    {
        $found = [];

        foreach (self::GUEST_VIEWS as $view) {
            preg_match_all('/id="(gp-[a-z0-9-]+)"/', file_get_contents(base_path($view)), $m);
            $found = array_merge($found, $m[1]);
        }

        $found = array_values(array_unique(array_diff($found, self::NOT_A_PANEL)));
        sort($found);

        return $found;
    }

    /** @return list<string> */
    private function idsInDocs(): array
    {
        $docs = file_get_contents(base_path(self::DOCS_VIEW));

        $start = strpos($docs, 'id="hiding-sections"');
        $this->assertNotFalse($start, 'the docs page has lost its #hiding-sections section');

        $table = substr($docs, $start, strpos($docs, '<!-- Live Preview -->') - $start);
        preg_match_all('/<code class="doc-inline-code">#(gp-[a-z0-9-]+)<\/code>/', $table, $m);

        $found = array_values(array_unique($m[1]));
        sort($found);

        return $found;
    }

    public function test_every_section_id_in_the_guest_views_is_documented(): void
    {
        $this->assertSame(
            [],
            array_values(array_diff($this->idsInMarkup(), $this->idsInDocs())),
            'a gp-* id renders on a guest page but is missing from the table in '
                .'/docs/schedule-styling#hiding-sections. Owners cannot target what is not listed.'
        );
    }

    public function test_every_documented_section_id_still_exists_in_the_markup(): void
    {
        $this->assertSame(
            [],
            array_values(array_diff($this->idsInDocs(), $this->idsInMarkup())),
            'the docs list a gp-* id that no guest view renders any more. A rule written against '
                .'it silently does nothing, which reads to an owner as custom CSS being broken.'
        );
    }

    public function test_the_schedule_page_carries_its_section_ids(): void
    {
        [$owner, $role] = $this->schedule();
        $this->createEvent($role, ['creator_role_id' => $role->id]);

        $html = $this->get(route('role.view_guest', ['subdomain' => $role->subdomain]))
            ->assertOk()
            ->getContent();

        foreach ([
            'gp-announcement',
            'gp-header',
            'gp-header-image',
            'gp-profile-image',
            'gp-header-body-mobile',
            'gp-header-body-desktop',
            'gp-sponsors',
            'gp-events',
            'gp-calendar',
            'gp-subscribe',
            'gp-videos',
        ] as $id) {
            $this->assertStringContainsString('id="'.$id.'"', $html, $id.' is missing from the schedule page');
        }
    }

    public function test_the_event_page_carries_its_section_ids(): void
    {
        [$owner, $role] = $this->schedule();
        $event = $this->event($role, $owner);

        $html = $this->get($this->eventUrl($role, $event))->assertOk()->getContent();

        foreach ([
            'gp-announcement',
            'gp-event-details',
            'gp-back-link',
            'gp-event-title',
            'gp-event-short-description',
            'gp-event-date',
            'gp-event-location',
            'gp-event-price',
            'gp-event-cta',
            'gp-event-share',
            'gp-mobile-cta',
            'gp-event-interest',
            'gp-talent',
            'gp-talent-list',
            'gp-venue',
            'gp-venue-map',
            'gp-flyer',
            'gp-about',
            'gp-agenda-image',
            'gp-agenda',
            'gp-upcoming-events',
            'gp-sponsors',
            'gp-subscribe',
        ] as $id) {
            $this->assertStringContainsString('id="'.$id.'"', $html, $id.' is missing from the event page');
        }
    }

    public function test_hiding_a_section_is_what_the_documented_rule_does(): void
    {
        // The whole feature, end to end: the id is on the page and the owner's rule reaches it in
        // the same document. Pro, because layouts/app-guest.blade.php gates the sheet on isPro().
        [$owner, $role] = $this->schedule();
        $role->forceFill(['custom_css' => '#gp-talent-list { display: none; }'])->save();

        $event = $this->event($role, $owner);
        $html = $this->get($this->eventUrl($role, $event))->assertOk()->getContent();

        $this->assertStringContainsString('id="gp-talent-list"', $html);
        $this->assertStringContainsString('#gp-talent-list { display: none; }', $html);
    }

    public function test_a_schedule_below_pro_gets_the_id_but_not_the_rule(): void
    {
        [$owner, $role] = $this->schedule(['plan_type' => 'free', 'plan_expires' => null]);
        $role->forceFill(['custom_css' => '#gp-talent-list { display: none; }'])->save();

        $event = $this->event($role, $owner);
        $html = $this->get($this->eventUrl($role, $event))->assertOk()->getContent();

        $this->assertStringContainsString('id="gp-talent-list"', $html, 'the ids are not plan-gated');
        $this->assertStringNotContainsString('#gp-talent-list { display: none; }', $html, 'the sheet is');
    }

    /** @return array{0: User, 1: Role} */
    private function schedule(array $attrs = []): array
    {
        $owner = $this->createOwner();

        // Curator, not venue: Event::venue() takes the first venue-type role attached, so a
        // venue-type SCHEDULE would answer as the event's venue and the real venue card, with the
        // address the map needs, would never render.
        $role = $this->createRole($owner, 'curator', $attrs + [
            'name' => 'The Old Market',
            'description' => 'A room with a stage in it.',
            'profile_image_url' => 'https://images.test/profile.jpg',
            'header_image_url' => 'https://images.test/header.jpg',
            'banner_enabled' => true,
            'banner_on_event_pages' => true,
            // Off by default; on here so #gp-event-interest renders with the rest.
            'show_event_interest' => true,
            'banner_message' => 'Doors at seven.',
            'youtube_links' => json_encode([['url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ']]),
            'sponsor_logos' => json_encode([['name' => 'A Sponsor', 'logo' => 'demo_sponsor.png', 'url' => 'https://sponsor.test']]),
        ]);

        return [$owner, $role];
    }

    private function event(Role $role, User $owner): Event
    {
        $event = $this->createEvent($role, [
            'name' => 'Tuesday Session',
            'creator_role_id' => $role->id,
            'short_description' => 'Standards and a rhythm section.',
            'description' => 'Four sets, no interval.',
            'flyer_image_url' => 'https://images.test/flyer.jpg',
            'agenda_image_url' => 'https://images.test/agenda.jpg',
            'registration_url' => 'https://tickets.test/buy',
            'ticket_price' => 0,
        ]);

        // A venue that is not the creator, so the venue card and its map both render. The map is
        // gated on a Maps key as well as an address, and formatted_address is what the card reads.
        config(['services.google.maps' => 'test-maps-key']);

        $venue = $this->createVenueWithAddress($owner, [
            'name' => 'Market Hall',
            'formatted_address' => '123 Main St, Springfield, IL 62701, USA',
        ]);
        $event->roles()->attach($venue->id, ['is_accepted' => true]);

        // One act with something to show (a card) and one bare name (the compact list). The bare
        // one must be UNCLAIMED as well as empty: isClaimed() alone earns a card of its own.
        $event->roles()->attach($this->act($owner, 'Headliner', 'A band with a bio.')->id, ['is_accepted' => true]);
        $event->roles()->attach($this->bareAct('Support Act')->id, ['is_accepted' => true]);

        EventPart::create([
            'event_id' => $event->id,
            'name' => 'First set',
            'start_time' => '20:00',
            'sort_order' => 0,
        ]);

        return $event->fresh();
    }

    private function act(User $owner, string $name, ?string $description = null): Role
    {
        return $this->createRole($owner, 'talent', array_filter([
            'name' => $name,
            'description' => $description,
        ]));
    }

    /** A name somebody typed on the bill: no owner, no verified contact, nothing to show. */
    private function bareAct(string $name): Role
    {
        $role = new Role;
        $role->subdomain = 'act'.strtolower(Str::random(10));
        $role->type = 'talent';
        $role->name = $name;
        $role->timezone = 'America/New_York';
        $role->plan_type = 'free';
        $role->save();

        return $role->fresh();
    }

    private function eventUrl(Role $role, Event $event): string
    {
        return route('role.view_guest', ['subdomain' => $role->subdomain])
            .'/'.$event->slug.'?id='.UrlUtils::encodeId($event->id);
    }
}
