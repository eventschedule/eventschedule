<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * A password-protected event on the surfaces that show events to people who never unlocked it.
 *
 * A save keeps a password only on an unlisted (is_private) event now, and every one of these
 * surfaces already leaves unlisted events out. Rows from before that rule are LISTED with a
 * password, and those went straight through: ?graphic=1 serialized their name, flyer,
 * registration link and coupon unblanked, and the video carousel, the homepage wall, /browse,
 * /search, a placeholder's page and the logo wall showed them to anyone.
 *
 * Every locked fixture here is that legacy row: is_private false, a password set.
 */
class PasswordEventSharedSurfacesTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    /** What must not reach anybody who has not unlocked the event. None contains a slash, so a JSON-escaped URL cannot hide one. */
    private const LOCKED = ['Locked Tasting Night', 'lockedflyer77', 'lockedreg77', 'LOCKEDCOUPON77'];

    protected function setUp(): void
    {
        parent::setUp();

        // Mid-month, so the month grid ?graphic=1 reads holds both upcoming events whatever
        // today's date is.
        $this->travelTo(Carbon::parse('2026-10-14 12:00:00', 'UTC'));
    }

    private function lockedEvent(Role $role, array $attrs = []): Event
    {
        return $this->createEvent($role, $attrs + [
            'name' => 'Locked Tasting Night',
            'creator_role_id' => $role->id,
            'starts_at' => '2026-10-16 19:00:00',
            'flyer_image_url' => 'lockedflyer77.jpg',
            'registration_url' => 'https://tickets.example.org/lockedreg77',
            'coupon_code' => 'LOCKEDCOUPON77',
            'is_private' => false,
            'event_password' => 'hunter2',
        ]);
    }

    private function openEvent(Role $role, array $attrs = []): Event
    {
        return $this->createEvent($role, $attrs + [
            'name' => 'Open Tasting Night',
            'creator_role_id' => $role->id,
            'starts_at' => '2026-10-17 19:00:00',
            'flyer_image_url' => 'openflyer77.jpg',
        ]);
    }

    private function assertNothingLocked(string $html, string $surface): void
    {
        foreach (self::LOCKED as $needle) {
            $this->assertStringNotContainsString($needle, $html, "{$surface} shows the password-protected event's {$needle}");
        }
    }

    /**
     * Both of each builder's queries - the month and the past events - and for members too: the
     * page is made to be shared as an image, and the downloadable graphic never had these events.
     */
    public function test_the_graphic_view_leaves_a_password_event_out_for_everyone(): void
    {
        foreach (['venue', 'curator'] as $type) {
            $owner = $this->createOwner();
            $role = $this->createRole($owner, $type, ['name' => 'Harbour Wine Bar']);
            $this->lockedEvent($role);
            $this->lockedEvent($role, ['starts_at' => '2026-08-10 19:00:00']);
            $this->openEvent($role);
            $this->openEvent($role, ['name' => 'Open Summer Tasting', 'starts_at' => '2026-08-11 19:00:00']);

            $url = route('role.view_guest', ['subdomain' => $role->subdomain]);

            $html = $this->get($url.'?graphic=1')->assertOk()->getContent();
            $this->assertStringContainsString('Open Tasting Night', $html, "fixture: the {$type}'s month is in the graphic");
            $this->assertStringContainsString('Open Summer Tasting', $html, "fixture: the {$type}'s past events are in the graphic");
            $this->assertNothingLocked($html, "a {$type}'s ?graphic=1");

            $html = $this->get($url.'?embed=1&graphic=1')->assertOk()->getContent();
            $this->assertStringContainsString('openflyer77', $html, "fixture: the {$type}'s embedded graphic draws the open event");
            $this->assertNothingLocked($html, "a {$type}'s ?embed=1&graphic=1");

            $html = $this->actingAs($owner)->get($url.'?graphic=1')->assertOk()->getContent();
            $this->assertStringContainsString('Open Tasting Night', $html);
            $this->assertNothingLocked($html, "the {$type} owner's own ?graphic=1");

            auth()->logout();
        }
    }

    public function test_the_video_carousel_leaves_a_password_event_out(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue', ['name' => 'Harbour Wine Bar', 'hide_videos' => false]);
        $act = $this->createRole($this->createOwner(), 'talent', [
            'name' => 'The Cellar Trio',
            'youtube_links' => json_encode([['url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ']]),
        ]);

        foreach ([$this->lockedEvent($venue), $this->openEvent($venue)] as $event) {
            $event->roles()->attach($act->id, ['is_accepted' => true]);
        }

        $url = route('role.view_guest', ['subdomain' => $venue->subdomain]);

        foreach (['a visitor' => null, 'the owner' => $owner] as $who => $user) {
            if ($user) {
                $this->actingAs($user);
            }

            $html = $this->get($url)->assertOk()->getContent();
            $this->assertStringContainsString('gp-video-carousel', $html, 'fixture: the carousel renders');
            $this->assertStringContainsString('Open Tasting Night', $html);
            $this->assertNothingLocked($html, "the carousel {$who} sees");
        }
    }

    public function test_the_homepage_wall_browse_and_search_leave_a_password_event_out(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue', ['name' => 'Harbour Wine Bar']);
        $this->lockedEvent($role, ['short_description' => 'Grand cru flight']);
        $this->openEvent($role, ['short_description' => 'Grand cru flight']);

        $wall = $this->get('/')->assertOk();
        $this->assertSame(['Open Tasting Night'], $wall->viewData('discoverEvents')->pluck('name')->all());
        $this->assertNothingLocked($wall->getContent(), 'the homepage wall');

        $browse = $this->get('/browse')->assertOk();
        $this->assertSame(['Open Tasting Night'], $browse->viewData('events')->pluck('name')->all());
        $this->assertNothingLocked($browse->getContent(), '/browse');

        // The short description is matched too, so it cannot be probed for either.
        $search = $this->get('/search?q='.urlencode('Grand cru'))->assertOk();
        $this->assertSame(['Open Tasting Night'], $search->viewData('events')->pluck('name')->all());
        $this->assertNothingLocked($search->getContent(), '/search');
    }

    public function test_a_placeholders_page_leaves_a_password_event_out(): void
    {
        $venue = $this->createRole($this->createOwner(), 'venue', ['name' => 'Harbour Wine Bar']);

        $placeholder = new Role;
        $placeholder->subdomain = 'act'.strtolower(Str::random(10));
        $placeholder->type = 'talent';
        $placeholder->name = 'The Wandering Few';
        $placeholder->timezone = 'America/New_York';
        $placeholder->save();
        $this->assertTrue($placeholder->fresh()->isClaimable(), 'fixture: a placeholder with a page of its own');

        foreach ([$this->lockedEvent($venue), $this->openEvent($venue)] as $event) {
            $event->roles()->attach($placeholder->id, ['is_accepted' => true]);
        }

        $html = $this->get(route('role.view_guest', ['subdomain' => $placeholder->subdomain]))->assertOk()->getContent();
        $this->assertStringContainsString('Open Tasting Night', $html);
        $this->assertNothingLocked($html, "a placeholder's page");
    }

    public function test_the_logo_wall_leaves_out_a_venue_met_only_at_a_password_event(): void
    {
        $owner = $this->createOwner();
        $curator = $this->createRole($owner, 'curator', ['name' => 'Wall Curator', 'header_image' => 'logos']);
        $shown = $this->createRole($owner, 'venue', ['name' => 'Open Room', 'profile_image_url' => 'demo_wall_open_room.jpg']);
        $hidden = $this->createRole($owner, 'venue', ['name' => 'Locked Room', 'profile_image_url' => 'demo_wall_locked_room.jpg']);

        $this->openEvent($curator)->roles()->attach($shown->id, ['is_accepted' => true]);
        $this->lockedEvent($curator)->roles()->attach($hidden->id, ['is_accepted' => true]);

        $ids = $curator->fresh()->logoWallRoles()->pluck('id');
        $this->assertTrue($ids->contains($shown->id));
        $this->assertFalse($ids->contains($hidden->id), 'the wall advertises a booking only a password event shows');

        $html = $this->get(route('role.view_guest', ['subdomain' => $curator->subdomain]))->assertOk()->getContent();
        $this->assertStringContainsString('demo_wall_open_room.jpg', $html);
        $this->assertStringNotContainsString('demo_wall_locked_room.jpg', $html);
    }

    /**
     * Any signed-in account may search any schedule, and the query matches descriptions. The
     * response blanks a password event's description, but a match still names the event, so a
     * stranger could read the description back a word at a time. Its own people still find it.
     */
    public function test_searching_a_schedule_never_matches_a_password_event_for_a_stranger(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['name' => 'Harbour Wine Bar']);
        $this->lockedEvent($role, ['description' => 'The vintage is a Margaux.']);
        $this->openEvent($role, ['description' => 'The vintage is the house red.']);

        $search = fn (User $who) => collect(
            $this->actingAs($who)
                ->getJson(route('role.search_events', ['subdomain' => $role->subdomain]).'?q=vintage')
                ->assertOk()
                ->json()
        )->pluck('name')->sort()->values()->all();

        $this->assertSame(['Open Tasting Night'], $search($this->createOwner()));
        $this->assertSame(['Locked Tasting Night', 'Open Tasting Night'], $search($owner));
    }

    /**
     * The scope is the SQL half of isPasswordProtected(), and must pick exactly the rows that
     * calls unprotected. The column pads with spaces, so an `= ''` test would also have let a
     * password of only spaces through.
     */
    public function test_the_scope_keeps_exactly_the_rows_is_password_protected_calls_unprotected(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');

        foreach (['no password' => null, 'empty' => '', 'spaces' => '   ', 'set' => 'hunter2'] as $name => $password) {
            $event = $this->createEvent($role, ['name' => $name]);
            // Straight to the column, so no hook can tidy the value first.
            Event::whereKey($event->id)->update(['event_password' => $password]);
        }

        $unprotected = Event::all()->reject(fn ($e) => $e->isPasswordProtected())->pluck('name')->sort()->values()->all();
        $this->assertSame(['empty', 'no password'], $unprotected, 'fixture');

        $this->assertSame($unprotected, Event::notPasswordProtected()->pluck('name')->sort()->values()->all());
    }
}
