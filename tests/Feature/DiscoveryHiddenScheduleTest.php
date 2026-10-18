<?php

namespace Tests\Feature;

use App\Models\DiscoveryHiddenSchedule;
use App\Models\Event;
use App\Models\Role;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * "Hide schedule": a platform admin's pill beside "Hide event" on the homepage rail and /browse,
 * which takes every event FROM a schedule off the homepage, /browse, /search and the global
 * sitemap at once, and the schedule off /search's schedule results.
 *
 * "From" is Event::constrainFromSchedule(): the events it owns, and the ones it is on as a talent
 * or a venue and has not turned down. Never a curator's listing, because a curator lists anybody's
 * event without asking, and a hidden curator would otherwise take other people's events with it.
 */
class DiscoveryHiddenScheduleTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private function schedule(string $name, string $type = 'venue', array $attrs = []): Role
    {
        return $this->createRole($this->createOwner(), $type, ['name' => $name] + $attrs);
    }

    /** An event owned by $role, with a flyer so every surface can show it. */
    private function event(Role $role, string $name, array $attrs = []): Event
    {
        return $this->createEvent($role, $attrs + [
            'name' => $name,
            'creator_role_id' => $role->id,
            'flyer_image_url' => strtolower(preg_replace('/\W+/', '-', $name)).'.png',
        ]);
    }

    private function admin()
    {
        return $this->withSession(['admin_password_confirmed_at' => now()->timestamp])
            ->actingAs($this->createOwner(true));
    }

    private function hide(Role $role, bool $hide = true, string $anchor = 'discover')
    {
        return $this->admin()->post(route('marketing.discovery.schedule', UrlUtils::encodeId($role->id)), [
            'hide' => $hide ? 1 : 0,
            'anchor' => $anchor,
        ]);
    }

    /** Back to a signed-out visitor: actingAs() outlives the request it was set for. */
    private function asGuest(): static
    {
        Auth::forgetGuards();
        $this->flushSession();

        return $this;
    }

    /** A global sitemap page, built afresh: its sections are cached like the real one's. */
    private function sitemap(string $kind): string
    {
        Cache::flush();

        return $this->get('/sitemap-'.$kind.'-1.xml')->assertOk()->streamedContent();
    }

    private function names($response, string $key): array
    {
        return $response->assertOk()->viewData($key)->map(fn ($row) => $row->name)->sort()->values()->all();
    }

    private function everywhere(string $q = 'Gala'): array
    {
        $search = $this->get('/search?q='.$q);

        return [
            'home' => $this->names($this->get('/'), 'discoverEvents'),
            'browse' => $this->names($this->get('/browse'), 'events'),
            'search' => $this->names($search, 'events'),
            'schedules' => $this->names($search, 'schedules'),
        ];
    }

    public function test_hiding_a_schedule_takes_its_events_off_every_discovery_surface_and_unhiding_brings_them_back(): void
    {
        $venue = $this->schedule('Gala Hall');
        $this->event($venue, 'Gala Night');

        // An act's event at the venue: owned by the act, on the venue's calendar.
        $act = $this->schedule('The Gala Act', 'talent');
        $atVenue = $this->event($act, 'Gala Act Live');
        $atVenue->roles()->attach($venue->id, ['is_accepted' => true]);

        $this->event($this->schedule('Elsewhere Gala Room'), 'Gala Elsewhere');

        $before = $this->everywhere();
        $this->assertContains('Gala Night', $before['home']);
        $this->assertContains('Gala Act Live', $before['browse']);
        $this->assertContains('Gala Hall', $before['schedules']);
        // An act's event at a venue is addressed under the act's subdomain, then the venue's.
        $this->assertStringContainsString('/'.$act->subdomain.'/', $this->sitemap('events'));
        $this->assertStringContainsString($venue->subdomain, $this->sitemap('schedules'));

        $this->hide($venue)
            ->assertRedirect(route('marketing.index').'#discover')
            ->assertSessionHas('discovery_notice', fn ($notice) => str_starts_with($notice['text'], '2 upcoming events from Gala Hall are now hidden')
                && $notice['undo']['hide'] === false);

        $after = $this->everywhere();
        $this->assertSame(['Gala Elsewhere'], $after['home']);
        $this->assertSame(['Gala Elsewhere'], $after['browse']);
        $this->assertSame(['Gala Elsewhere'], $after['search']);
        $this->assertNotContains('Gala Hall', $after['schedules']);
        $this->assertContains('The Gala Act', $after['schedules'], 'Only the hidden schedule leaves the schedule results');

        $this->assertStringNotContainsString('/'.$act->subdomain.'/', $this->sitemap('events'));
        $this->assertStringNotContainsString($venue->subdomain, $this->sitemap('schedules'));

        $this->hide($venue, false)->assertSessionHas('discovery_notice', fn ($notice) => $notice['undo']['hide'] === true);

        $this->assertSame($before, $this->asGuest()->everywhere());
        $this->assertStringContainsString('/'.$act->subdomain.'/', $this->sitemap('events'));
    }

    public function test_a_curators_listing_and_a_declined_request_stay_on_but_a_pending_one_goes(): void
    {
        $curator = $this->schedule('Gala Picks', 'curator');
        $this->event($curator, 'Gala Curated Own');

        // Listed by the curator from another schedule: not the curator's to hide.
        $listed = $this->event($this->schedule('Gala Room'), 'Gala Listed');
        $listed->roles()->attach($curator->id, ['is_accepted' => true]);

        // A venue that turned an act's request down is not where that event is from.
        $venue = $this->schedule('Gala Venue');
        $declined = $this->event($this->schedule('Gala Band', 'talent'), 'Gala Declined');
        $declined->roles()->attach($venue->id, ['is_accepted' => false]);

        // Still waiting on the venue: the card can already credit it, so it goes with it.
        $pending = $this->event($this->schedule('Gala Duo', 'talent'), 'Gala Pending');
        $pending->roles()->attach($venue->id, ['is_accepted' => null]);

        $this->hide($curator);
        $this->hide($venue);

        $this->assertSame(['Gala Declined', 'Gala Listed'], $this->everywhere()['search']);
    }

    public function test_an_event_from_before_creator_role_id_goes_with_the_schedule_it_was_first_put_on(): void
    {
        $curator = $this->schedule('Gala Early', 'curator');
        $room = $this->schedule('Gala Old Room');

        $first = $this->event($curator, 'Gala First On', ['creator_role_id' => null]);
        $first->roles()->attach($room->id, ['is_accepted' => true]);

        $second = $this->event($room, 'Gala Second On', ['creator_role_id' => null]);
        $second->roles()->attach($curator->id, ['is_accepted' => true]);

        $this->hide($curator);

        $this->assertSame(['Gala Second On'], $this->everywhere()['search']);
    }

    public function test_the_card_rule_agrees_with_the_query(): void
    {
        $curator = $this->schedule('Gala Agree Picks', 'curator');
        $venue = $this->schedule('Gala Agree Venue');
        $act = $this->schedule('Gala Agree Act', 'talent');

        $events = [
            $this->event($venue, 'Owned'),
            tap($this->event($act, 'Hosted'), fn ($e) => $e->roles()->attach($venue->id, ['is_accepted' => true])),
            tap($this->event($act, 'Asked'), fn ($e) => $e->roles()->attach($venue->id, ['is_accepted' => null])),
            tap($this->event($act, 'Declined'), fn ($e) => $e->roles()->attach($venue->id, ['is_accepted' => false])),
            tap($this->event($venue, 'Listed'), fn ($e) => $e->roles()->attach($curator->id, ['is_accepted' => true])),
            tap($this->event($curator, 'Old Row', ['creator_role_id' => null]), fn ($e) => $e->roles()->attach($act->id, ['is_accepted' => true])),
        ];

        foreach ($events as $event) {
            $event = Event::with('roles')->find($event->id);

            foreach ([$curator, $venue, $act] as $role) {
                $this->assertSame(
                    Event::constrainFromSchedule(Event::query()->whereKey($event->id), $role->id)->exists(),
                    $event->isFromSchedule($role),
                    "{$event->name} / {$role->name}"
                );
            }
        }
    }

    public function test_the_card_that_was_pressed_is_gone_and_a_curators_listing_has_no_pill(): void
    {
        $venue = $this->schedule('Pressed Venue');
        $this->event($venue, 'Pressed Night');

        // A placeholder venue nobody has claimed, listed by a curator: the card credits the
        // curator, whose hide would not take the card away, so it gets no schedule pill.
        $placeholder = $this->schedule('Unclaimed Room', 'venue', ['email_verified_at' => null]);
        $curator = $this->schedule('Listing Curator', 'curator');
        $listed = $this->event($placeholder, 'Listed Night');
        $listed->roles()->attach($curator->id, ['is_accepted' => true]);

        $home = $this->admin()->get('/')->assertOk();
        $pill = route('marketing.discovery.schedule', UrlUtils::encodeId($venue->id));
        $home->assertSee($pill, false)->assertSee('Hide schedule')->assertSee('Hide event');
        $home->assertDontSee(route('marketing.discovery.schedule', UrlUtils::encodeId($curator->id)), false);

        $this->hide($venue);

        $this->assertNotContains('Pressed Night', $this->names($this->get('/'), 'discoverEvents'));
    }

    public function test_only_a_confirmed_admin_may_hide_and_a_refusal_says_how_to_confirm(): void
    {
        $venue = $this->schedule('Guarded Venue');
        $url = route('marketing.discovery.schedule', UrlUtils::encodeId($venue->id));

        $this->actingAs($this->createOwner())->post($url, ['hide' => 1])->assertForbidden();

        $this->actingAs($this->createOwner(true))
            ->post($url, ['hide' => 1, 'anchor' => 'discover'])
            ->assertRedirect(route('marketing.index').'#discover')
            ->assertSessionHas('url.intended', route('marketing.index').'#discover')
            ->assertSessionHas('discovery_notice', fn ($notice) => $notice['confirm'] === route('admin.password.confirm.show'));

        $this->assertSame(0, DiscoveryHiddenSchedule::count());
    }

    public function test_hide_says_which_state_it_wants_so_a_double_press_cannot_undo_it(): void
    {
        $venue = $this->schedule('Twice Venue');

        $this->hide($venue);
        $this->hide($venue);
        $this->assertSame(1, DiscoveryHiddenSchedule::where('role_id', $venue->id)->count());

        $this->hide($venue, false, 'hidden-schedules')->assertRedirect(route('marketing.browse').'#hidden-schedules');
        $this->hide($venue, false);
        $this->assertSame(0, DiscoveryHiddenSchedule::count());
    }

    public function test_the_admin_has_the_hidden_schedules_list_and_a_guest_sees_none_of_it(): void
    {
        $venue = $this->schedule('Listed Hidden Venue');
        $this->event($venue, 'Some Night');
        $this->hide($venue);

        $this->admin()->get('/browse')->assertOk()
            ->assertSee('id="hidden-schedules"', false)
            ->assertSee('Listed Hidden Venue')
            ->assertSee(route('marketing.discovery.schedule', UrlUtils::encodeId($venue->id)), false);

        $this->event($this->schedule('Visible Venue'), 'Visible Night');

        $this->asGuest()->get('/browse')->assertOk()->assertDontSee('id="hidden-schedules"', false)->assertDontSee('Hide schedule');
        $this->get('/')->assertOk()->assertDontSee('Hide schedule');
    }

    public function test_a_cached_homepage_does_not_keep_a_hidden_schedules_events(): void
    {
        config(['marketing.wall_cache_seconds' => 600]);

        $venue = $this->schedule('Cached Venue');
        $this->event($venue, 'Cached Night');

        $this->assertContains('Cached Night', $this->names($this->get('/'), 'discoverEvents'));

        $this->hide($venue);

        $this->assertNotContains('Cached Night', $this->names($this->get('/'), 'discoverEvents'));
    }
}
