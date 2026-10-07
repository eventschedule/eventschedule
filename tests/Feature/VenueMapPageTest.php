<?php

namespace Tests\Feature;

use App\Models\PlaceLookup;
use App\Models\Role;
use App\Models\VenueMapSetting;
use App\Services\AdminAlertService;
use App\Services\PlaceLookupService;
use App\Services\VenueMap;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The venue map where people meet it: the band on a schedule's guest page, the row on the
 * schedule form, the save, the notice after it, and the words that must stay true about it.
 *
 * VenueMapTest holds which venues are on a map and PlaceLookupTest how their positions are found.
 * Here:
 *
 *   - the band is on the page only when there is a map to open, and never in an embed or in the
 *     picture ?graphic=1 renders;
 *   - its host is EMPTY and everything a venue's owner typed rides beside it as JSON: text inside
 *     a Vue mount is compiled as a template;
 *   - the map changes nothing in the list of events below it. "See all events here" sets the
 *     list's own venue filter, so the names it reads there must still exist;
 *   - the two switches are not columns of the schedule and are stored only by a save that
 *     carries them;
 *   - nothing is looked up inside the save;
 *   - the privacy policy names a map service only on an install that uses one.
 */
class VenueMapPageTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.map.geocoder_url' => 'https://lookup.test/search', 'services.map.tile_url' => 'https://tiles.test/{z}/{x}/{y}.png']);
        Http::preventStrayRequests();
        Cache::flush();
    }

    /** A curator with a map switched on and $count venues, each with a position. */
    private function curatorWithMap(int $count = 2, array $venueAttrs = []): Role
    {
        $curator = $this->createRole($this->createOwner(), 'curator', ['country_code' => 'il']);
        VenueMap::saveSettings($curator, true, false);

        foreach (range(1, $count) as $i) {
            $venue = $this->createRole($this->createOwner(), 'venue', $venueAttrs + ['name' => 'Venue '.$i, 'address1' => $i.' Main St', 'city' => 'Binyamina', 'country_code' => 'il']);
            $event = $this->createEvent($curator, ['name' => 'Night '.$i, 'creator_role_id' => $curator->id]);
            $event->roles()->attach($venue->id, ['is_accepted' => true]);
        }

        foreach (VenueMap::venues($curator->fresh()) as $i => $row) {
            PlaceLookup::where('address_hash', $row['hash'])->update(['status' => PlaceLookupService::FOUND, 'lat' => 32.5 + $i / 100, 'lon' => 34.9, 'looked_up_at' => now()]);
        }

        VenueMapSetting::where('role_id', $curator->id)->update(['ready_at' => now()]);

        return $curator->fresh();
    }

    private function page(Role $role, array $query = []): string
    {
        return $this->get(route('role.view_guest', ['subdomain' => $role->subdomain] + $query))->assertOk()->getContent();
    }

    private function props(string $html): array
    {
        $this->assertSame(1, preg_match('/<script type="application\/json" id="es-venue-map-json"[^>]*>(.*?)<\/script>/s', $html, $m), 'the map\'s props are on the page');

        return json_decode($m[1], true, 512, JSON_THROW_ON_ERROR);
    }

    public function test_the_band_is_on_the_page_between_the_sponsors_and_the_events(): void
    {
        $html = $this->page($this->curatorWithMap());

        $this->assertSame(1, substr_count($html, 'id="es-venue-map-host"'));
        $this->assertLessThan(strpos($html, 'id="gp-events"'), strpos($html, 'id="es-venue-map-host"'), 'above the events');

        $props = $this->props($html);
        $this->assertSame(2, $props['band']['total']);
        $this->assertSame(['Binyamina'], $props['band']['towns']);
        $this->assertStringEndsWith('/api/venue-map', $props['url']);
        $this->assertSame('https://tiles.test/{z}/{x}/{y}.png', $props['tiles']['url']);
        $this->assertStringContainsString('tiles.test', $props['t']['streets_note'], 'the sentence names the service the visitor\'s browser would reach');
    }

    public function test_no_band_without_a_map_to_open(): void
    {
        $curator = $this->curatorWithMap();

        // Switched off.
        VenueMap::saveSettings($curator, false, false);
        $this->assertStringNotContainsString('es-venue-map-host', $this->page($curator->fresh()));

        // On, and not through its first pass.
        VenueMap::saveSettings($curator, true, false);
        VenueMapSetting::where('role_id', $curator->id)->update(['ready_at' => null]);
        $this->assertStringNotContainsString('es-venue-map-host', $this->page($curator->fresh()));

        // Ready, on an install whose operator has named no address search.
        VenueMapSetting::where('role_id', $curator->id)->update(['ready_at' => now()]);
        $this->assertStringContainsString('es-venue-map-host', $this->page($curator->fresh()));
        config(['services.map.geocoder_url' => null]);
        $this->assertStringNotContainsString('es-venue-map-host', $this->page($curator->fresh()));
    }

    public function test_one_pin_is_not_a_map(): void
    {
        $this->assertStringNotContainsString('es-venue-map-host', $this->page($this->curatorWithMap(1)));
    }

    public function test_an_embed_and_a_graphic_are_the_list_alone(): void
    {
        $curator = $this->curatorWithMap();

        $this->assertStringNotContainsString('es-venue-map-host', $this->page($curator, ['embed' => 'true']));
        $this->assertStringNotContainsString('es-venue-map-host', $this->page($curator, ['graphic' => '1']));
    }

    public function test_the_host_is_empty_and_a_name_rides_beside_it_as_data(): void
    {
        $curator = $this->curatorWithMap(2, ['city' => '{{ constructor.constructor("alert(1)")() }}</script><b>']);
        $html = $this->page($curator);

        $this->assertMatchesRegularExpression('/<div id="es-venue-map-host" class="gk-map-host" data-venue-map><\/div>/', $html, 'nothing is printed inside the mount');
        $this->assertSame(['{{ constructor.constructor("alert(1)")() }}</script><b>'], $this->props($html)['band']['towns'], 'the town arrives whole');
        $this->assertStringNotContainsString('</script><b>', $html, 'and cannot end the block it is printed in');
    }

    public function test_without_street_images_nothing_names_a_tile_service(): void
    {
        // Named here, not read from the developer's .env, where an operator's own credit may be set.
        config(['services.map.tile_url' => null, 'services.map.attribution' => '© OpenStreetMap contributors']);

        $props = $this->props($this->page($this->curatorWithMap()));

        $this->assertNull($props['tiles'], 'pins on a plain ground');
        $this->assertSame('© OpenStreetMap contributors', $props['credit'], 'the positions are still credited');
    }

    /**
     * The map sets the list's own filter from outside and changes nothing in the list. These are
     * the names it reads there (VenueMap.vue, list() and seeAll()): if one is renamed, the button
     * is silently never offered, and this is what says so.
     */
    public function test_the_list_still_has_what_the_map_reads(): void
    {
        $calendar = file_get_contents(resource_path('views/role/partials/calendar.blade.php'));

        foreach ([
            "selectedVenue: ''," => 'the venue filter the Venue select is bound to',
            'eventsForFilters() {' => 'the events the list is holding',
            'passesFilters(event, except = {}) {' => 'the list\'s own filter test',
            'event.venue_subdomain !== this.selectedVenue' => 'a venue is filtered by its subdomain, which is the map\'s key',
            "phoneDay: ''," => 'the day pressed in the phone\'s month',
            'isLoadingEvents:' => 'whether the list has loaded',
            'window.calendarVueApp = calendarAppInstance;' => 'where the list\'s app is reachable',
        ] as $needle => $what) {
            $this->assertStringContainsString($needle, $calendar, $what);
        }

        $component = file_get_contents(resource_path('js/components/VenueMap.vue'));
        $this->assertStringNotContainsString('showVenue', $calendar.$component, 'nothing was added to the list for the map');

        // The venue filter is the only one the map sets. The visitor's category, Free, Online,
        // search and custom fields are theirs: the first version cleared four of them to avoid an
        // empty list, and could still land on one.
        foreach (['selectedCategory', 'showFreeOnly', 'showOnlineOnly', 'clearSearch', 'selectedCustomFields', 'selectedGroup', 'clearFilters'] as $theirs) {
            $this->assertStringNotContainsString($theirs, $component, "the map leaves {$theirs} alone");
        }

        // And it travels through history only over the one entry the full-window map pushed: the
        // list has entries of its own, and a map that went back through several took the list's
        // month and filters back with it.
        $this->assertSame(1, substr_count($component, 'history.pushState('), 'one place pushes an entry');
        $this->assertSame(1, substr_count($component, 'history.back()'), 'one place travels, by one');
        $this->assertStringNotContainsString('history.go(', $component);
    }

    public function test_the_host_keeps_no_room_for_a_band_that_will_not_come(): void
    {
        $html = $this->page($this->curatorWithMap());

        $this->assertMatchesRegularExpression('/<noscript><style[^>]*>\.gk-map-host \{ min-height: 0; \}<\/style><\/noscript>/', $html, 'no scripts, no band, no gap');
        $this->assertStringContainsString("classList.add('is-mounted')", file_get_contents(resource_path('js/app.js')), 'a chunk that fails to load gives the room back');
        $this->assertStringContainsString("host.classList.add('is-mounted');\n\n        return;", file_get_contents(resource_path('js/venue-map-boot.js')), 'and so does a boot that finds nothing to mount');
    }

    public function test_the_form_row_is_offered_where_a_map_can_exist(): void
    {
        $owner = $this->createOwner();
        $curator = $this->createRole($owner, 'curator', ['country_code' => 'il']);
        $venue = $this->createRole($owner, 'venue');

        $form = fn (Role $role) => $this->actingAs($owner)->get(route('role.edit', ['subdomain' => $role->subdomain]))->assertOk()->getContent();

        $html = $form($curator);
        $this->assertMatchesRegularExpression('/data-row-group="engagement" data-tab="map"\s+aria-expanded="false" aria-controls="engagement-tab-map"/', $html);
        $this->assertMatchesRegularExpression('/<div id="engagement-tab-map" class="event-subrow-body[^"]*"[^>]* hidden>/', $html, 'the pane starts closed');
        $this->assertStringContainsString('data-summary="engagement:map"', $html);
        $this->assertMatchesRegularExpression('/<input type="checkbox"[^>]*name="show_venues_map"/', $html);
        $this->assertStringContainsString('lookup.test', $html, 'the switch says where the addresses go');

        $this->assertStringNotContainsString('id="engagement-tab-map"', $form($venue), 'a venue schedule is one place');

        config(['services.map.geocoder_url' => null]);
        $this->assertStringNotContainsString('id="engagement-tab-map"', $form($curator), 'no row on an install with no address search');
    }

    public function test_the_owners_list_of_venues_is_an_empty_host_with_its_data_beside_it(): void
    {
        $curator = $this->curatorWithMap(2, ['name' => '{{ constructor.constructor("alert(1)")() }}</script><i>']);
        $owner = $curator->users()->first();

        $html = $this->actingAs($owner)->get(route('role.edit', ['subdomain' => $curator->subdomain]))->assertOk()->getContent();

        $this->assertStringContainsString('<div id="es-venue-map-editor" data-venue-map-editor></div>', $html, 'nothing is printed inside the mount');
        $this->assertSame(1, preg_match('/<script type="application\/json" id="es-venue-map-editor-json"[^>]*>(.*?)<\/script>/s', $html, $m));

        $props = json_decode($m[1], true, 512, JSON_THROW_ON_ERROR);
        $this->assertCount(2, $props['venues']);
        $this->assertSame('{{ constructor.constructor("alert(1)")() }}</script><i>', $props['venues'][0]['name'], 'a venue\'s name arrives whole, as data');
        $this->assertStringNotContainsString('</script><i>', $html, 'and cannot end the block it is printed in');
        $this->assertStringContainsString('/venue-map/marks/__VENUE__', $props['markUrl']);
        $this->assertSame(['id', 'key', 'name', 'address', 'state', 'why', 'hidden', 'by_hand', 'lat', 'lon', 'found', 'claimed', 'edit_url'], array_keys($props['venues'][0]));
        $this->assertStringContainsString('tiles.test', $props['t']['streets_note'], 'the list says whose streets a pin is moved on');

        // A map that is switched off has asked about nothing, and lists nothing.
        VenueMap::saveSettings($curator, false, false);
        $this->assertStringNotContainsString('id="es-venue-map-editor"', $this->actingAs($owner)->get(route('role.edit', ['subdomain' => $curator->subdomain]))->assertOk()->getContent());
    }

    public function test_the_row_has_a_help_link_into_the_guide(): void
    {
        $anchor = \App\Utils\HelpUtils::class;
        $source = file_get_contents(app_path('Utils/HelpUtils.php'));
        $this->assertStringContainsString("'engagement-tab-map' => '/docs/creating-schedules#engagement-venue-map'", $source);
        $this->assertStringContainsString('id="engagement-venue-map"', file_get_contents(resource_path('views/marketing/docs/creating-schedules.blade.php')));
        $this->assertTrue(class_exists($anchor));
    }

    public function test_a_save_stores_the_switches_and_looks_nothing_up(): void
    {
        Queue::fake();
        $owner = $this->createOwner();
        $curator = $this->createRole($owner, 'curator', ['country_code' => 'il']);
        $venue = $this->createRole($this->createOwner(), 'venue', ['address1' => '1 Main St', 'city' => 'Binyamina', 'country_code' => 'il']);
        $event = $this->createEvent($curator, ['creator_role_id' => $curator->id]);
        $event->roles()->attach($venue->id, ['is_accepted' => true]);

        $save = fn (array $more) => $this->actingAs($owner)->from(route('role.edit', ['subdomain' => $curator->subdomain]))
            ->put(route('role.update', ['subdomain' => $curator->subdomain]), [
                'name' => $curator->name, 'email' => $curator->email, 'timezone' => $curator->timezone, 'new_subdomain' => $curator->subdomain,
            ] + $more)
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('role.view_admin', ['subdomain' => $curator->subdomain, 'tab' => 'schedule']));

        // A save from somewhere that does not carry the switch leaves the map as it was.
        $save([])->assertSessionMissing('venue_map_saved');
        $this->assertFalse(VenueMap::enabledFor($curator->fresh()));

        $save(['show_venues_map' => '1', 'venues_map_open' => '1'])->assertSessionHas('venue_map_saved');

        $curator = $curator->fresh();
        $this->assertTrue(VenueMap::enabledFor($curator));
        $this->assertTrue(VenueMap::startsOpen($curator));
        $this->assertFalse(VenueMap::ready($curator), 'its venue has not been asked about');
        $this->assertSame(1, PlaceLookup::where('status', PlaceLookupService::PENDING)->count(), 'the address is noted for the runner');
        Http::assertNothingSent();

        // A later save that does not carry the switch (a save from the API, or from a form that
        // never drew the row) is not "off": absent is not unchecked.
        $save([]);
        $this->assertTrue(VenueMap::enabledFor($curator->fresh()));
        $this->assertTrue(VenueMap::startsOpen($curator->fresh()));

        // The same for "Open on arrival" alone: its switch is not drawn where nobody can allow
        // cookies, and a save from that form used to clear it.
        $save(['show_venues_map' => '1']);
        $this->assertTrue(VenueMap::startsOpen($curator->fresh()), 'absent is not off');
        $save(['show_venues_map' => '1', 'venues_map_open' => '0']);
        $this->assertFalse(VenueMap::startsOpen($curator->fresh()));
        $save(['show_venues_map' => '1', 'venues_map_open' => '1']);

        // Switched off: both go, and nothing about the first pass is forgotten.
        $save(['show_venues_map' => '0', 'venues_map_open' => '1']);
        $this->assertFalse(VenueMap::enabledFor($curator->fresh()));
        $this->assertFalse(VenueMap::startsOpen($curator->fresh()), 'a map that is off does not start open');
    }

    public function test_the_switches_are_not_columns_a_post_can_fill(): void
    {
        $this->assertNotContains('show_venues_map', (new Role)->getFillable());
        $this->assertNotContains('venues_map_open', (new Role)->getFillable());
        $this->assertFalse(\Illuminate\Support\Facades\Schema::hasColumn('roles', 'show_venues_map'), 'roles is at MySQL\'s row-size limit: the map has a table of its own');
    }

    public function test_a_venue_schedule_cannot_switch_a_map_on_by_posting_the_field(): void
    {
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue', ['address1' => '1 Main St', 'city' => 'Binyamina']);

        $this->actingAs($owner)->from(route('role.edit', ['subdomain' => $venue->subdomain]))
            ->put(route('role.update', ['subdomain' => $venue->subdomain]), [
                'name' => $venue->name, 'email' => $venue->email, 'timezone' => $venue->timezone, 'new_subdomain' => $venue->subdomain, 'address1' => '1 Main St', 'show_venues_map' => '1',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('role.view_admin', ['subdomain' => $venue->subdomain, 'tab' => 'schedule']));

        $this->assertFalse(VenueMap::enabledFor($venue->fresh()));
        $this->assertSame(0, VenueMapSetting::count());
    }

    public function test_the_notice_after_a_save_says_how_far_the_map_is(): void
    {
        $curator = $this->curatorWithMap();
        $owner = $curator->users()->first();
        VenueMapSetting::where('role_id', $curator->id)->update(['ready_at' => null]);

        $html = $this->actingAs($owner)->withSession(['venue_map_saved' => true])
            ->get(route('role.view_admin', ['subdomain' => $curator->subdomain, 'tab' => 'schedule']))->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/id="venue-map-notice"[^>]*data-ready="0"\s+data-total="2" data-asked="2" data-placed="2"/s', $html);
        $this->assertStringContainsString(route('role.venue_map.status', ['subdomain' => $curator->subdomain]), $html);
        $this->assertStringContainsString('#engagement-tab-map', $html, 'Review venues leads to the row');

        // Without the flash there is no card: it is said once, after the save.
        $this->flushSession();
        $again = $this->actingAs($owner)->get(route('role.view_admin', ['subdomain' => $curator->subdomain, 'tab' => 'schedule']))->assertOk()->getContent();
        $this->assertStringNotContainsString('venue-map-notice', $again);
    }

    public function test_the_privacy_policy_names_a_map_service_only_where_one_is_used(): void
    {
        $this->pinAppUrl('https://eventschedule.test');

        config(['services.map.geocoder_url' => 'https://nominatim.openstreetmap.org/search', 'services.map.tile_url' => 'https://tile.openstreetmap.org/{z}/{x}/{y}.png']);
        $both = $this->get('/privacy')->assertOk()->getContent();
        $this->assertSame(1, substr_count($both, '>OpenStreetMap<'), 'one row when both services are the same provider');
        $this->assertStringContainsString('Our servers send it the address of a venue', $both);
        $this->assertStringContainsString('so it sees your IP address', $both);
        // The clause on what marketing consent loads, and the list of what the browser keeps: the
        // provider table alone was updated at first, and the release note said nothing was left.
        $this->assertStringContainsString('the street images of a schedule&#039;s map of venues, which your browser fetches from OpenStreetMap', $both);
        $this->assertStringContainsString('map of venues that you hid', $both);

        config(['services.map.tile_url' => null]);
        $lookupOnly = $this->get('/privacy')->assertOk()->getContent();
        $this->assertStringContainsString('Address search for the map of venues', $lookupOnly);
        $this->assertStringNotContainsString('so it sees your IP address, and only if you allow marketing cookies or press the button that shows the map', $lookupOnly);

        $this->assertStringNotContainsString('which your browser fetches from', $lookupOnly, 'no street images, no such clause');

        config(['services.map.geocoder_url' => null]);
        $this->assertStringNotContainsString('OpenStreetMap', $this->get('/privacy')->assertOk()->getContent(), 'an install without the map says nothing about it');
    }

    public function test_an_hour_of_failing_lookups_is_on_the_admins_list(): void
    {
        $type = 'venue_map_lookups_failing';
        $alert = fn () => AdminAlertService::items()->firstWhere('type', $type);

        Cache::put(PlaceLookupService::FAILING_SINCE_KEY, now()->subMinutes(20)->timestamp, now()->addDay());
        AdminAlertService::flush();
        $this->assertNull($alert(), 'one bad quarter of an hour is not a stall');

        Cache::put(PlaceLookupService::FAILING_SINCE_KEY, now()->subHours(2)->timestamp, now()->addDay());
        AdminAlertService::flush();
        $this->assertNotNull($alert());
        $this->assertSame('amber', $alert()['color']);

        config(['services.map.geocoder_url' => null]);
        AdminAlertService::flush();
        $this->assertNull($alert(), 'nothing to alert about where the map does not exist');
    }
}
