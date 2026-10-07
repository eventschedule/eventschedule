<?php

namespace Tests\Browser;

use App\Models\Event;
use App\Models\PlaceLookup;
use App\Models\Role;
use App\Models\User;
use App\Models\VenueMapMark;
use App\Models\VenueMapSetting;
use App\Services\PlaceLookupService;
use App\Services\VenueMap;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * The venue map on a schedule's guest page, driven the way a visitor and an owner drive it.
 *
 * What a rendered-HTML test cannot see: the band is drawn by a script, the map and the list
 * beside it are built in the browser, a venue and the map itself are steps in the browser's
 * history, and "See all events here" reaches into another app on the page (the list of events)
 * and sets its filter.
 *
 * The Dusk environment names an address search that is never asked (every position is written by
 * the test) and, for street images, one small picture of this app's own (.env.dusk.local): the
 * journeys can see WHEN a browser asks for streets, and no browser here reaches another site.
 */
class VenueMapJourneyTest extends DuskTestCase
{
    use DatabaseTruncation;

    private User $owner;

    private Role $curator;

    /** @var array<string, Role> */
    private array $venues = [];

    protected function setUp(): void
    {
        parent::setUp();

        if (! VenueMap::available()) {
            $this->markTestSkipped('MAP_GEOCODER_URL is not set in the Dusk environment.');
        }

        $this->owner = User::factory()->create(['email_verified_at' => now()]);
        $this->curator = $this->role($this->owner, 'curator', 'mapjourney', 'What is on');

        // Two towns: three venues a few streets apart, and one 25 km up the coast.
        foreach ([
            'cellar' => ['The Cellar', '1 Harbour St', 'Southport', 32.1660, 34.8100, 5],
            'loft' => ['The Loft', '9 Mill Lane', 'Southport', 32.1670, 34.8300, 1],
            'garden' => ['Garden Stage', '4 Park Row', 'Southport', 32.1830, 34.8650, 1],
            'barn' => ['The Old Barn', '2 Farm Road', 'Northfield', 32.4760, 34.9720, 2],
        ] as $key => [$name, $street, $town, $lat, $lon, $events]) {
            $venue = $this->role(User::factory()->create(['email_verified_at' => now()]), 'venue', 'map'.$key, $name);
            $venue->forceFill(['address1' => $street, 'city' => $town, 'country_code' => 'us'])->save();
            $this->venues[$key] = $venue->fresh();

            foreach (range(1, $events) as $n) {
                $this->event($name.' night '.$n, $venue, $n * 2);
            }
        }

        VenueMap::saveSettings($this->curator, true, false);

        foreach (VenueMap::venues($this->curator->fresh()) as $row) {
            $key = array_search($row['venue']->id, array_map(fn (Role $v) => $v->id, $this->venues), true);
            [$lat, $lon] = ['cellar' => [32.1660, 34.8100], 'loft' => [32.1670, 34.8300], 'garden' => [32.1830, 34.8650], 'barn' => [32.4760, 34.9720]][$key];
            PlaceLookup::where('address_hash', $row['hash'])->update(['status' => PlaceLookupService::FOUND, 'lat' => $lat, 'lon' => $lon, 'looked_up_at' => now()]);
        }

        VenueMapSetting::where('role_id', $this->curator->id)->update(['ready_at' => now()]);
    }

    private function role(User $user, string $type, string $subdomain, string $name): Role
    {
        $role = new Role;
        $role->subdomain = $subdomain;
        $role->user_id = $user->id;
        $role->type = $type;
        $role->name = $name;
        $role->email = $subdomain.'@gmail.com';
        $role->timezone = 'America/New_York';
        $role->country_code = 'us';
        $role->email_verified_at = now();
        $role->plan_type = 'enterprise';
        $role->plan_expires = now()->addYear()->format('Y-m-d');
        $role->save();
        $role->users()->attach($user->id, ['level' => 'owner']);

        return $role->fresh();
    }

    private function event(string $name, Role $venue, int $days): Event
    {
        $event = new Event;
        $event->user_id = $this->owner->id;
        $event->creator_role_id = $this->curator->id;
        $event->name = $name;
        $event->slug = Str::slug($name).'-'.strtolower(Str::random(4));
        $event->starts_at = Carbon::now('America/New_York')->startOfDay()->addDays($days)->setTime(20, 0)->setTimezone('UTC')->format('Y-m-d H:i:s');
        $event->duration = 2;
        $event->save();
        $event->roles()->attach($this->curator->id, ['is_accepted' => true]);
        $event->roles()->attach($venue->id, ['is_accepted' => true]);

        return $event->fresh();
    }

    /** A real phone's width, which a resized desktop window does not give (Chrome stops near 500px). */
    private function metrics(Browser $browser, int $width, int $height): void
    {
        $browser->driver->executeCustomCommand('/session/:sessionId/goog/cdp/execute', 'POST', [
            'cmd' => 'Emulation.setDeviceMetricsOverride',
            'params' => ['width' => $width, 'height' => $height, 'deviceScaleFactor' => 1, 'mobile' => $width < 600],
        ]);
    }

    private function js(Browser $browser, string $expression): mixed
    {
        return $browser->script('return '.$expression.';')[0];
    }

    /** The schedule page in its list view, with the band drawn and the list loaded. */
    private function page(Browser $browser, string $query = 'layout=list'): void
    {
        $browser->visit('/mapjourney?visit='.uniqid().'&'.$query)
            ->waitFor('#gp-map', 15)
            ->waitUntil('window.calendarVueApp !== undefined && ! window.calendarVueApp.isLoadingEvents', 15);
    }

    /** How many requests for a street image this page has made. */
    private function streetRequests(Browser $browser): int
    {
        return (int) $this->js($browser, 'performance.getEntriesByType("resource").filter(function (e) { return e.name.indexOf("/vendor/leaflet/images/layers.png?z=") !== -1; }).length');
    }

    /**
     * Presses the button with these words INSIDE one element. Dusk's press() takes the first
     * button on the page with the words, and every venue in the owner's list has the same ones.
     */
    private function pressIn(Browser $browser, string $selector, string $words): void
    {
        $browser->script('var el = document.querySelector('.json_encode($selector).'); el.scrollIntoView({ block: "center" });'
            .'Array.prototype.filter.call(el.querySelectorAll("button"), function (b) { return b.textContent.trim() === '.json_encode($words).'; })[0].click();');
    }

    private function openMap(Browser $browser): void
    {
        $browser->click('#gp-map .gk-map-acts .gk-map-toggle')
            ->waitFor('.gk-map-leaflet .leaflet-marker-icon', 15)
            ->waitFor('.gk-map-item', 10)
            ->pause(500);
    }

    public function test_a_visitor_opens_the_map_and_reaches_a_venues_events(): void
    {
        $cellar = $this->venues['cellar']->subdomain;

        $this->browse(function (Browser $browser) use ($cellar) {
            $this->metrics($browser, 1280, 900);
            $this->page($browser);

            // The band, for a visitor who has not allowed cookies: the sentence that says who will
            // see their address stands beside Show map, and nothing has been asked for yet.
            $browser->assertSeeIn('#gp-map', 'Map')
                ->assertSeeIn('#gp-map .gk-map-sub-ask', 'which will see your IP address')
                ->assertSeeIn('#gp-map .gk-map-sub-ask', 'Open without streets')
                ->assertMissing('.gk-map-leaflet');
            $this->assertSame(0, $this->streetRequests($browser), 'no street image is asked for before the press');

            // Show map, beside that sentence, is the visitor's choice: the map opens WITH streets.
            $this->openMap($browser);
            $browser->waitFor('.gk-map-leaflet .leaflet-tile', 10);

            $this->assertSame('#gp-map', $this->js($browser, 'location.hash'), 'the open map is a step in the address');
            $this->assertSame(4, $this->js($browser, 'document.querySelectorAll(".gk-map-item").length'), 'every venue is in the list beside the map');
            $this->assertGreaterThan(0, $this->streetRequests($browser));
            $this->assertStringContainsString('gk-map-leaflet leaflet-container', $this->js($browser, 'document.querySelector(".gk-map-leaflet").className'), 'Leaflet keeps its own classes on its container');
            $browser->assertSeeIn('#gp-map .gk-map-sub', 'Southport')->assertMissing('.gk-map-askcard');
            // Three venues a few streets apart are one group at this distance, never three pins on top of each other.
            $this->assertGreaterThanOrEqual(1, $this->js($browser, 'document.querySelectorAll(".gk-cluster-dot").length'));

            // A venue: its panel, its place in the address, and its events as links.
            $browser->click('.gk-map-item[data-venue="'.$cellar.'"]')
                ->waitFor('.gk-map-venue', 10)
                ->pause(600)
                ->assertSeeIn('.gk-map-venue', 'The Cellar')
                ->assertSeeIn('.gk-map-venue', '1 Harbour St, Southport')
                ->assertSeeIn('.gk-map-venue', 'The Cellar night 1');

            $this->assertSame('#gp-map/'.$cellar, $this->js($browser, 'location.hash'));
            $this->assertSame(3, $this->js($browser, 'document.querySelectorAll(".gk-map-next li").length'), 'three of its five events');
            $this->assertSame(1, $this->js($browser, 'document.querySelectorAll(".gk-pin.is-on").length'), 'its pin is the chosen one');

            // See all events here: the list below is filtered through its OWN venue filter.
            $browser->assertSeeIn('.gk-map-cta', 'See all events here')
                ->click('.gk-map-cta button')
                ->pause(700);

            $this->assertSame($cellar, $this->js($browser, 'window.calendarVueApp.selectedVenue'));
            $this->assertSame(5, $this->js($browser, 'window.calendarVueApp.filteredEventsForView.length'), 'the venue\'s five events and no others');
            $this->assertSame('gp-events', $this->js($browser, 'document.activeElement.id'), 'focus is on the list, not on the chip that would undo it');
            $browser->assertSeeIn('.gk-map-cta', 'Clear filter');

            // And taken off again from the same place.
            $browser->click('.gk-map-cta button')->pause(300);
            $this->assertSame('', $this->js($browser, 'window.calendarVueApp.selectedVenue'));

            // Back: the venue, then the venues, then closed.
            $browser->back()->pause(600);
            $this->assertSame('#gp-map', $this->js($browser, 'location.hash'));
            $browser->assertMissing('.gk-map-venue')->assertVisible('.gk-map-item');

            $browser->back()->pause(600);
            $this->assertSame('', $this->js($browser, 'location.hash'));
            $this->assertFalse((bool) $this->js($browser, 'document.querySelector(".gk-map-body").offsetParent !== null'), 'the map is closed');
        });
    }

    public function test_the_map_opens_without_streets_and_asks_for_them_on_the_map(): void
    {
        $this->browse(function (Browser $browser) {
            $this->metrics($browser, 1280, 900);
            $this->page($browser);

            $browser->click('#gp-map .gk-map-plain')
                ->waitFor('.gk-map-leaflet .leaflet-marker-icon', 15)
                ->pause(500)
                ->assertVisible('.gk-map-askcard')
                ->assertSeeIn('.gk-map-askcard', 'which will see your IP address');

            $this->assertSame(0, $this->streetRequests($browser), 'pins on a plain ground: nothing was asked of anyone');
            $this->assertStringContainsString('Venue positions', $this->js($browser, 'document.querySelector(".leaflet-control-attribution").textContent'), 'the positions are credited all the same');

            $before = $this->js($browser, 'document.querySelector(".gk-pin, .gk-cluster-dot").getBoundingClientRect().left');
            $browser->click('.gk-map-askcard button')->waitFor('.gk-map-leaflet .leaflet-tile', 10)->pause(300);

            $this->assertGreaterThan(0, $this->streetRequests($browser));
            $browser->assertMissing('.gk-map-askcard');
            $this->assertSame($before, $this->js($browser, 'document.querySelector(".gk-pin, .gk-cluster-dot").getBoundingClientRect().left'), 'nothing moves when the streets arrive');
        });
    }

    public function test_a_link_to_a_venue_opens_the_map_on_it(): void
    {
        $barn = $this->venues['barn']->subdomain;

        $this->browse(function (Browser $browser) use ($barn) {
            $this->metrics($browser, 1280, 900);
            // Straight to a venue on the map, as a link to one lands.
            $browser->visit('/mapjourney?visit='.uniqid().'&layout=list#gp-map/'.$barn)
                ->waitFor('.gk-map-venue', 15)
                ->assertSeeIn('.gk-map-venue', 'The Old Barn')
                ->assertSeeIn('.gk-map-venue', 'The Old Barn night 2')
                // A link is not a press: the map is open, and the streets are still asked about.
                ->assertVisible('.gk-map-askcard')
                // Its two events are both in the panel, so there is nothing more to see below.
                ->assertMissing('.gk-map-cta');

            $this->assertSame('#gp-map/'.$barn, $this->js($browser, 'location.hash'));
            // Nearby: the two closest venues with something on, each with how far it is.
            $this->assertSame(2, $this->js($browser, 'document.querySelectorAll(".gk-map-venue .gk-map-list-flush .gk-map-item").length'));
            $this->assertMatchesRegularExpression('/^\d+(\.\d)? mi$/', trim($this->js($browser, 'document.querySelector(".gk-map-venue .gk-map-list-flush small span").textContent')), 'miles, where the road signs are');
        });
    }

    public function test_today_leaves_the_venues_with_nothing_on_as_quiet_marks(): void
    {
        $this->browse(function (Browser $browser) {
            $this->metrics($browser, 1280, 900);
            $this->page($browser);
            $this->openMap($browser);

            // Nothing in the fixture is today: every venue leaves the list and is a quiet mark.
            $browser->click('.gk-map-when button:nth-child(2)')->pause(700);

            $this->assertSame(0, $this->js($browser, 'document.querySelectorAll(".gk-map-item").length'));
            $this->assertGreaterThanOrEqual(1, $this->js($browser, 'document.querySelectorAll(".gk-pin-quiet").length'));
            $this->assertSame(0, $this->js($browser, 'document.querySelectorAll(".gk-pin, .gk-cluster-dot").length'));
            $browser->assertVisible('.gk-map-empty');

            $browser->click('.gk-map-when button:nth-child(1)')->pause(700);
            $this->assertSame(4, $this->js($browser, 'document.querySelectorAll(".gk-map-item").length'));
        });
    }

    public function test_on_a_phone_the_map_is_a_full_window_sheet(): void
    {
        $this->browse(function (Browser $browser) {
            $this->metrics($browser, 390, 800);
            $this->page($browser);

            $browser->click('#gp-map .gk-map-acts .gk-map-toggle')
                ->waitFor('#gp-map-sheet .leaflet-marker-icon', 15)
                ->pause(500);

            $this->assertTrue((bool) $this->js($browser, 'getComputedStyle(document.getElementById("gp-map-sheet")).display !== "none"'));
            $this->assertSame('hidden', $this->js($browser, 'document.body.style.overflow'), 'the page behind does not scroll');
            $this->assertLessThanOrEqual(390, $this->js($browser, 'document.documentElement.scrollWidth'), 'nothing is wider than the phone');
            $this->assertSame(4, $this->js($browser, 'document.querySelectorAll("#gp-map-sheet .gk-map-item").length'));

            // Close is Back: the sheet goes, the page scrolls again, and focus returns to the band.
            $browser->click('#gp-map-sheet .gk-map-sheetbar button')->pause(700);

            $this->assertTrue((bool) $this->js($browser, 'getComputedStyle(document.getElementById("gp-map-sheet")).display === "none"'));
            $this->assertSame('', $this->js($browser, 'document.body.style.overflow'));
            $this->assertSame('', $this->js($browser, 'location.hash'));

            $this->metrics($browser, 1280, 900);
        });
    }

    public function test_an_owner_switches_the_map_on_and_is_told_how_far_it_is(): void
    {
        VenueMapSetting::query()->delete();
        PlaceLookup::query()->update(['status' => PlaceLookupService::PENDING, 'lat' => null, 'lon' => null, 'looked_up_at' => null]);

        $this->browse(function (Browser $browser) {
            $this->metrics($browser, 1280, 900);
            $browser->script('window._skipUnsavedWarning = true;');
            $browser->loginAs($this->owner)
                ->visit('/mapjourney/edit?visit='.uniqid())
                ->waitFor('#edit-form', 15)
                ->waitUntil('window.FormKit !== undefined && window.FormSaveBar !== undefined && window.FormKit.isArmed()', 15)
                ->pause(100);

            $browser->script('document.querySelector(\'a[data-section="section-engagement"]\').click();');
            $browser->waitUntil('document.getElementById("section-engagement").style.display === "block"', 10);

            $this->assertSame('Disabled', trim($this->js($browser, 'document.querySelector(\'[data-summary="engagement:map"]\').textContent')));

            $browser->script('document.querySelector(\'button.engagement-tab[data-tab="map"]\').scrollIntoView({ block: "center" });');
            $browser->pause(150)->click('button.engagement-tab[data-tab="map"]')->pause(200);
            $this->assertTrue((bool) $this->js($browser, '! document.getElementById("engagement-tab-map").hidden'));

            // The switch is a styled checkbox: its label is what a person presses.
            $browser->script('document.querySelector(\'label[for="show_venues_map"]\').click();');
            $browser->pause(200);
            $this->assertSame('Enabled', trim($this->js($browser, 'document.querySelector(\'[data-summary="engagement:map"]\').textContent')));

            $browser->script('window._skipUnsavedWarning = true; document.getElementById("edit-form").requestSubmit();');
            $browser->waitForLocation('/mapjourney/schedule', 30)
                ->waitFor('#venue-map-notice', 10)
                ->assertSeeIn('#venue-map-notice', 'Your venue map is being prepared')
                ->assertSeeIn('#venue-map-notice', '0 / 4');
        });

        $this->assertTrue(VenueMap::enabledFor($this->curator->fresh()));
        $this->assertFalse(VenueMap::ready($this->curator->fresh()), 'nothing has been looked up yet');
        $this->assertSame(4, PlaceLookup::where('status', PlaceLookupService::PENDING)->count());

        // The lookups finish (here, by hand): the card that is still open says so by itself.
        PlaceLookup::query()->update(['status' => PlaceLookupService::FOUND, 'lat' => 32.2, 'lon' => 34.8, 'looked_up_at' => now()]);
        VenueMap::refreshReady($this->curator->fresh());

        $this->browse(function (Browser $browser) {
            $browser->waitForTextIn('#venue-map-notice', 'Your venue map is on your page', 12)
                ->assertVisible('#venue-map-notice [data-map-notice-view]');
        });
    }

    public function test_an_owner_takes_a_venue_off_the_map_and_moves_a_pin(): void
    {
        $loft = $this->venues['loft'];
        $cellar = $this->venues['cellar'];
        $row = fn (Role $venue) => '#es-venue-map-editor li[data-venue="'.$venue->subdomain.'"]';

        $this->browse(function (Browser $browser) use ($loft, $row) {
            $this->metrics($browser, 1280, 900);
            $browser->script('window._skipUnsavedWarning = true;');
            $browser->loginAs($this->owner)
                ->visit('/mapjourney/edit?visit='.uniqid())
                ->waitFor('#edit-form', 15)
                ->waitUntil('window.FormKit !== undefined && window.FormSaveBar !== undefined && window.FormKit.isArmed()', 15)
                ->pause(100);

            $browser->script('document.querySelector(\'a[data-section="section-engagement"]\').click();');
            $browser->waitUntil('document.getElementById("section-engagement").style.display === "block"', 10);
            $browser->script('document.querySelector(\'button.engagement-tab[data-tab="map"]\').scrollIntoView({ block: "center" });');
            $browser->pause(150)->click('button.engagement-tab[data-tab="map"]')
                ->waitFor($row($loft), 10);

            $this->assertSame(4, $this->js($browser, 'document.querySelectorAll("#es-venue-map-editor li").length'));
            $browser->assertSeeIn($row($loft), 'On the map')->assertSeeIn($row($loft), '9 Mill Lane, Southport');

            // Take off the map: saved at once, and no part of the form's own Save.
            $this->pressIn($browser, $row($loft), 'Take off the map');
            $browser->waitForTextIn($row($loft), 'Off the map', 10)->assertSeeIn($row($loft), 'Saved');
        });

        $this->assertTrue(VenueMapMark::where('role_id', $this->curator->id)->where('venue_id', $loft->id)->value('hidden'));
        $this->assertNotContains($loft->subdomain, array_column(VenueMap::payload($this->curator->fresh(), null, 'en'), 'key'), 'it is gone from the public map');

        $this->browse(function (Browser $browser) use ($loft, $cellar, $row) {
            $this->assertSame('No unsaved changes', trim(preg_replace('/\s+/', ' ', $this->js($browser, 'document.querySelector("#form-save-bar .event-save-status").innerText'))), 'the form itself has nothing to save');

            // The row stays where it was pressed (it is at the end of the list on the next visit),
            // so the way back is under the same finger.
            $this->pressIn($browser, $row($loft), 'Put back on the map');
            $browser->waitUntil('document.querySelector('.json_encode($row($loft)).').getAttribute("data-venue-state") === "placed"', 10);

            // Move a pin: the dialog, a click on the map, Save position.
            $this->pressIn($browser, $row($cellar), 'Move pin');
            $browser->waitFor('#es-venue-pin-dialog .leaflet-marker-icon', 15)
                ->assertSeeIn('#es-venue-pin-dialog', 'Where is The Cellar?')
                ->pause(400);

            [$x, $y] = $browser->script('var r = document.querySelector(\'#es-venue-pin-dialog .leaflet-container\').getBoundingClientRect(); return [Math.round(r.left + 70), Math.round(r.top + 70)];')[0];
            $browser->clickAtPoint($x, $y)->pause(300);

            $browser->press('Save position')->waitUntilMissing('#es-venue-pin-dialog', 10)
                ->waitForTextIn($row($cellar), 'Placed by hand', 10);
        });

        $mark = VenueMapMark::where('role_id', $this->curator->id)->where('venue_id', $cellar->id)->first();
        $this->assertNotNull($mark, 'the pin placed by hand is the owner\'s own mark');
        $this->assertNotEqualsWithDelta(32.1660, $mark->lat, 0.00001, 'and it is not where the search had put it');
        $this->assertEqualsWithDelta(32.1660, $mark->lat, 0.02, 'it is a click away from it');
        $this->assertSame(0, VenueMapMark::where('venue_id', $loft->id)->count(), 'the venue that was put back carries no mark');

        // And back to what the search found.
        $this->browse(function (Browser $browser) use ($cellar, $row) {
            $this->pressIn($browser, $row($cellar), 'Move pin');
            $browser->waitFor('#es-venue-pin-dialog .leaflet-marker-icon', 15)
                ->press('Use the looked-up position')
                ->waitUntilMissing('#es-venue-pin-dialog', 10)
                ->waitForTextIn($row($cellar), 'On the map', 10);
        });

        $this->assertSame(0, VenueMapMark::count());
    }
}
