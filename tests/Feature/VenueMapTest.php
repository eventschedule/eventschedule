<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Group;
use App\Models\PlaceLookup;
use App\Models\Role;
use App\Services\PlaceLookupService;
use App\Services\VenueMap;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The venue map on a schedule's guest page: which venues are on it and what each surface is told
 * (App\Services\VenueMap, VenueMapController).
 *
 * The rules a green suite would otherwise let slip:
 *
 *   - Only a venue of a PUBLIC event is ever named: not a draft's, an unlisted event's, a
 *     cancelled one's or a password-protected one's, and not a venue that turned the event down.
 *   - A pin never comes from roles.geo_lat / geo_lon. Those are Google's, and Google's terms allow
 *     them on a Google map only; the map's positions come from place_lookups alone.
 *   - A venue whose address has not been asked yet is not shown to the public at all, so nobody is
 *     told "not on the map" about a venue that is only in the queue.
 *   - Nothing is asked of the address search inside a request: every test here runs with
 *     Http::preventStrayRequests() and no fake.
 */
class VenueMapTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.map.geocoder_url' => 'https://lookup.test/search', 'services.map.tile_url' => 'https://tiles.test/{z}/{x}/{y}.png']);
        Http::preventStrayRequests();
    }

    private function curator(array $attrs = []): Role
    {
        $curator = $this->createRole($this->createOwner(), 'curator', $attrs + ['country_code' => 'il']);
        VenueMap::saveSettings($curator, true, false);

        return $curator->fresh();
    }

    /** A venue with an address, and one public event of $curator's held there. */
    private function venueWithEvent(Role $curator, string $name, array $venueAttrs = [], array $eventAttrs = [], array $pivot = ['is_accepted' => true]): array
    {
        $venue = $this->createRole($this->createOwner(), 'venue', $venueAttrs + [
            'name' => $name,
            'address1' => '1 '.$name.' St',
            'city' => 'Binyamina',
            'country_code' => 'il',
        ]);

        $event = $this->createEvent($curator, $eventAttrs + ['name' => 'Night at '.$name, 'creator_role_id' => $curator->id]);
        $event->roles()->attach($venue->id, $pivot);

        return [$venue, $event->fresh()];
    }

    private function place(Role $venue, Role $curator, ?float $lat = 32.52, ?float $lon = 34.94, string $status = PlaceLookupService::FOUND): PlaceLookup
    {
        $address = PlaceLookupService::addressFor($venue->fresh(), $curator);

        return PlaceLookup::updateOrCreate(['address_hash' => $address['hash']], [
            'address' => $address['address'],
            'country_code' => $address['country'],
            'status' => $status,
            'lat' => $status === PlaceLookupService::MISSING ? null : $lat,
            'lon' => $status === PlaceLookupService::MISSING ? null : $lon,
            'looked_up_at' => now(),
        ]);
    }

    private function ready(Role $curator): Role
    {
        \App\Models\VenueMapSetting::where('role_id', $curator->id)->update(['ready_at' => now()]);

        return $curator->fresh();
    }

    private function names(Role $curator, ?Group $group = null): array
    {
        return VenueMap::venues($curator, $group)->map(fn (array $v) => $v['venue']->name)->sort()->values()->all();
    }

    private function endpoint(Role $curator, array $query = []): array
    {
        return $this->getJson('/'.$curator->subdomain.'/api/venue-map'.($query ? '?'.http_build_query($query) : ''))->assertOk()->json('venues');
    }

    public function test_the_feature_exists_only_where_an_address_search_is_configured(): void
    {
        $curator = $this->curator();
        $this->assertTrue(VenueMap::enabledFor($curator));
        $this->assertFalse(VenueMap::offeredTo($this->createRole($this->createOwner(), 'venue')), 'a venue schedule has one venue: itself');

        config(['services.map.geocoder_url' => null]);

        $this->assertFalse(VenueMap::available());
        $this->assertFalse(VenueMap::enabledFor($curator));
    }

    public function test_only_venues_of_public_events_are_on_the_map(): void
    {
        $curator = $this->curator();

        $this->venueWithEvent($curator, 'Listed');
        $this->venueWithEvent($curator, 'Draft', [], ['is_draft' => true]);
        $this->venueWithEvent($curator, 'Unlisted', [], ['is_private' => true]);
        $this->venueWithEvent($curator, 'Cancelled', [], ['is_cancelled' => true]);
        $this->venueWithEvent($curator, 'Locked', [], ['event_password' => 'open sesame']);
        $this->venueWithEvent($curator, 'Declined', [], [], ['is_accepted' => false]);
        [$gone] = $this->venueWithEvent($curator, 'Deleted');
        DB::table('roles')->where('id', $gone->id)->update(['is_deleted' => true]);

        // An event the curator has not accepted is not the curator's to show.
        [, $pending] = $this->venueWithEvent($curator, 'Pending');
        $pending->roles()->updateExistingPivot($curator->id, ['is_accepted' => null]);

        $this->assertSame(['Listed'], $this->names($curator));
    }

    public function test_a_claimed_venue_must_have_said_yes_and_a_placeholder_must_not_have_said_no(): void
    {
        $curator = $this->curator();

        $this->venueWithEvent($curator, 'Claimed And Silent', [], [], ['is_accepted' => null]);
        [$placeholder] = $this->venueWithEvent($curator, 'Placeholder', [], [], ['is_accepted' => null]);
        DB::table('roles')->where('id', $placeholder->id)->update(['user_id' => null]);

        $this->assertSame(['Placeholder'], $this->names($curator));
    }

    public function test_a_venue_stays_through_the_grace_period_after_its_last_event(): void
    {
        $curator = $this->curator();
        $past = fn (int $days) => ['starts_at' => Carbon::now('UTC')->subDays($days)->setTime(12, 0)->format('Y-m-d H:i:s')];

        $this->venueWithEvent($curator, 'Coming');
        $this->venueWithEvent($curator, 'Recent', [], $past(VenueMap::GRACE_DAYS - 1));
        $this->venueWithEvent($curator, 'Long Ago', [], $past(VenueMap::GRACE_DAYS + 1));

        $venues = VenueMap::venues($curator)->keyBy(fn (array $v) => $v['venue']->name);

        $this->assertSame(['Coming', 'Recent'], $venues->keys()->sort()->values()->all());
        $this->assertTrue($venues['Coming']['upcoming']);
        $this->assertFalse($venues['Recent']['upcoming'], 'kept on the map, and known to have nothing coming');
    }

    public function test_an_online_events_meeting_link_is_not_a_venue_on_anyones_list(): void
    {
        // A calendar import stores whatever the location field held as the venue's address.
        $curator = $this->curator();
        $this->venueWithEvent($curator, 'Weekly call', ['address1' => 'https://zoom.us/j/123456789', 'city' => null]);
        [$real] = $this->venueWithEvent($curator, 'The Cellar');

        $this->assertSame([$real->id], VenueMap::venues($curator)->pluck('venue.id')->all());
        $this->assertSame(['The Cellar'], array_column(VenueMap::status($curator)['venues'], 'name'), 'nor in the owner\'s list, where there is nothing to fix');
    }

    public function test_a_pin_never_comes_from_googles_coordinates(): void
    {
        $curator = $this->curator();
        [$venue] = $this->venueWithEvent($curator, 'Geocoded By Google');
        DB::table('roles')->where('id', $venue->id)->update(['geo_lat' => '32.5200000', 'geo_lon' => '34.9400000', 'geo_address' => 'whatever Google resolved']);

        $row = VenueMap::venues($curator)->first();

        $this->assertSame(VenueMap::WAITING, $row['state'], 'with no answer from the map\'s own search, the venue is waiting');
        $this->assertNull($row['lat']);
        $this->assertNull($row['lon']);

        $this->place($venue, $curator, null, null, PlaceLookupService::MISSING);

        $row = VenueMap::venues($curator)->first();
        $this->assertSame(VenueMap::NOT_FOUND, $row['state']);
        $this->assertNull($row['lat'], 'Google\'s coordinates must not stand in for a miss');
    }

    public function test_an_address_is_noted_once_and_nothing_is_asked_in_the_request(): void
    {
        $curator = $this->curator();
        $this->venueWithEvent($curator, 'First');
        $this->venueWithEvent($curator, 'Second');

        VenueMap::venues($curator, null, false);
        $this->assertSame(0, PlaceLookup::count(), 'a read that was told not to register writes nothing');

        VenueMap::venues($curator);
        VenueMap::venues($curator);

        $this->assertSame(2, PlaceLookup::count());
        $this->assertSame(2, PlaceLookup::where('status', PlaceLookupService::PENDING)->count());
        Http::assertNothingSent();
    }

    public function test_there_is_no_band_until_the_first_pass_is_done_and_two_venues_have_a_pin(): void
    {
        $curator = $this->curator();
        [$a] = $this->venueWithEvent($curator, 'Alpha');
        [$b] = $this->venueWithEvent($curator, 'Beta');
        $this->venueWithEvent($curator, 'Gamma', ['address1' => null, 'city' => null]);

        $this->place($a, $curator);
        $this->assertNull(VenueMap::band($curator), 'not ready: the other venues have not been asked');

        $curator = $this->ready($curator);
        $this->assertNull(VenueMap::band($curator), 'one pin is what an event page already shows');

        $this->place($b, $curator, 32.47, 34.97);
        $band = VenueMap::band($curator);

        $this->assertNotNull($band);
        $this->assertSame(3, $band['total'], 'the venue with no address is on the list, without a pin');
        $this->assertSame(['Binyamina'], $band['towns']);

        VenueMap::saveSettings($curator, false, false);
        $this->assertNull(VenueMap::band($curator->fresh()));
    }

    public function test_the_endpoint_answers_an_empty_list_for_anything_that_is_not_a_map(): void
    {
        $curator = $this->curator();
        [$a] = $this->venueWithEvent($curator, 'Alpha');
        $this->place($a, $curator);

        $this->assertSame([], $this->endpoint($curator), 'not ready yet');

        $curator = $this->ready($curator);
        $this->assertCount(1, $this->endpoint($curator));

        VenueMap::saveSettings($curator, false, false);
        $this->assertSame([], $this->endpoint($curator));

        $this->getJson('/no-such-schedule-here/api/venue-map')->assertOk()->assertExactJson(['venues' => []]);

        config(['services.map.geocoder_url' => null]);
        VenueMap::saveSettings($curator, true, false);
        $this->assertSame([], $this->endpoint($curator), 'an install with no address search has no venue map');
    }

    public function test_the_endpoint_sends_what_the_map_draws_and_leaves_out_a_waiting_venue(): void
    {
        $curator = $this->ready($this->curator());
        [$placed, $event] = $this->venueWithEvent($curator, 'Olive Press');
        [$village] = $this->venueWithEvent($curator, 'Kibbutz Hall', ['address1' => null, 'city' => 'Maagan Michael']);
        [$lost] = $this->venueWithEvent($curator, 'Nowhere Bar');
        $this->venueWithEvent($curator, 'Still Waiting');
        [$unclaimed] = $this->venueWithEvent($curator, 'Unclaimed Cafe', [], [], ['is_accepted' => null]);
        DB::table('roles')->where('id', $unclaimed->id)->update(['user_id' => null, 'email_verified_at' => null]);

        $this->place($placed, $curator, 32.4762, 34.9741);
        $this->place($village, $curator, 32.5572, 34.9176, PlaceLookupService::APPROXIMATE);
        $this->place($lost, $curator, null, null, PlaceLookupService::MISSING);
        $this->place($unclaimed->fresh(), $curator, 32.51, 34.95);

        $venues = collect($this->endpoint($curator))->keyBy('name');

        $this->assertSame(['Kibbutz Hall', 'Nowhere Bar', 'Olive Press', 'Unclaimed Cafe'], $venues->keys()->sort()->values()->all(),
            'a venue whose address has not been asked is not shown to the public');

        $olive = $venues['Olive Press'];
        $this->assertSame($placed->subdomain, $olive['key'], 'the key is the list\'s own venue filter key');
        $this->assertSame(32.4762, $olive['lat']);
        $this->assertFalse($olive['approx']);
        $this->assertNull($olive['why']);
        $this->assertNotEmpty($olive['url'], 'a claimed venue links to its own page');
        $this->assertStringContainsString(urlencode('1 Olive Press St, Binyamina'), $olive['directions']);
        $this->assertSame('Night at Olive Press', $olive['next']['name']);
        $this->assertCount(1, $olive['events']);
        $this->assertStringContainsString($event->slug, $olive['events'][0]['url']);
        $this->assertFalse($olive['more'], 'the panel lists everything this venue has');

        $this->assertTrue($venues['Kibbutz Hall']['approx']);
        $this->assertNull($venues['Kibbutz Hall']['directions'], 'no street address, so no directions to one');

        $this->assertNull($venues['Nowhere Bar']['lat']);
        $this->assertSame('not_found', $venues['Nowhere Bar']['why']);

        $this->assertNull($venues['Unclaimed Cafe']['url'], 'an unclaimed venue has no page to link to');
        Http::assertNothingSent();
    }

    public function test_a_name_is_sent_as_data_and_the_page_host_would_carry_it_unharmed(): void
    {
        $curator = $this->ready($this->curator());
        [$venue] = $this->venueWithEvent($curator, 'Curly {{ 7 * 7 }} <b>Bar</b>');
        $this->place($venue, $curator);

        $sent = $this->endpoint($curator)[0]['name'];

        $this->assertSame('Curly {{ 7 * 7 }} <b>Bar</b>', $sent, 'the component prints it as text; nothing here may pre-escape or evaluate it');
    }

    public function test_more_says_when_the_list_holds_more_than_the_panel(): void
    {
        $curator = $this->ready($this->curator());
        [$venue] = $this->venueWithEvent($curator, 'Busy Room');
        $this->place($venue, $curator);

        foreach (range(1, 3) as $i) {
            $extra = $this->createEvent($curator, ['name' => 'Extra '.$i, 'creator_role_id' => $curator->id, 'starts_at' => Carbon::now('UTC')->addDays(10 + $i)->setTime(12, 0)->format('Y-m-d H:i:s')]);
            $extra->roles()->attach($venue->id, ['is_accepted' => true]);
        }

        $busy = $this->endpoint($curator)[0];

        $this->assertCount(3, $busy['events']);
        $this->assertTrue($busy['more']);
        $this->assertSame('Night at Busy Room', $busy['next']['name'], 'soonest first');
    }

    public function test_today_is_the_schedules_day_not_the_servers(): void
    {
        // 23:30 UTC on the 7th is 02:30 on the 8th in Jerusalem. An event at 21:00 Jerusalem time
        // on the 8th is "today" there, and would be "tomorrow" by the UTC calendar.
        Carbon::setTestNow(Carbon::parse('2026-10-07 23:30:00', 'UTC'));

        $curator = $this->ready($this->curator(['timezone' => 'Asia/Jerusalem']));
        [$tonight] = $this->venueWithEvent($curator, 'Tonight', [], ['starts_at' => '2026-10-08 18:00:00']);
        [$later] = $this->venueWithEvent($curator, 'Next Week', [], ['starts_at' => '2026-10-13 18:00:00']);
        [$far] = $this->venueWithEvent($curator, 'Next Month', [], ['starts_at' => '2026-11-20 18:00:00']);
        foreach ([$tonight, $later, $far] as $venue) {
            $this->place($venue, $curator);
        }

        $soon = collect($this->endpoint($curator))->pluck('soon', 'name');

        $this->assertSame('today', $soon['Tonight']);
        $this->assertSame('week', $soon['Next Week']);
        $this->assertNull($soon['Next Month']);

        Carbon::setTestNow();
    }

    public function test_a_sub_schedule_narrows_the_map_to_its_own_venues(): void
    {
        $curator = $this->ready($this->curator());
        $jazz = $this->createGroup($curator, ['name' => 'Jazz', 'slug' => 'jazz']);

        [$in, $event] = $this->venueWithEvent($curator, 'Jazz Cellar');
        $event->roles()->updateExistingPivot($curator->id, ['group_id' => $jazz->id]);
        [$out] = $this->venueWithEvent($curator, 'Rock Barn');
        $this->place($in, $curator);
        $this->place($out, $curator, 32.47, 34.97);

        $this->assertCount(2, $this->endpoint($curator));
        $this->assertSame(['Jazz Cellar'], array_column($this->endpoint($curator, ['schedule' => 'jazz']), 'name'));
    }

    public function test_the_owners_status_lists_every_venue_and_is_for_editors_only(): void
    {
        $curator = $this->curator();
        [$a] = $this->venueWithEvent($curator, 'Alpha');
        $this->venueWithEvent($curator, 'Beta');
        $this->venueWithEvent($curator, 'No Address', ['address1' => null, 'city' => null]);
        $this->place($a, $curator);

        $url = route('role.venue_map.status', ['subdomain' => $curator->subdomain]);

        $this->actingAs($this->createOwner())->getJson($url)->assertForbidden();

        $status = $this->actingAs($curator->user)->getJson($url)->assertOk()->json();

        $this->assertFalse($status['ready']);
        $this->assertSame(3, $status['total']);
        $this->assertSame(2, $status['asked'], 'one placed, one that has no address to ask about; one waiting');
        $this->assertSame(1, $status['placed']);
        $this->assertSame(['No Address', 'Beta', 'Alpha'], array_column($status['venues'], 'name'), 'problems first');
        $this->assertSame([VenueMap::NO_ADDRESS, VenueMap::WAITING, VenueMap::PLACED], array_column($status['venues'], 'state'));
    }

    public function test_ready_is_set_once_nothing_is_waiting(): void
    {
        $curator = $this->curator();
        [$a] = $this->venueWithEvent($curator, 'Alpha');

        $this->assertFalse(VenueMap::refreshReady($curator), 'Alpha has not been asked');
        $this->assertFalse(VenueMap::ready($curator->fresh()));

        $this->place($a, $curator, null, null, PlaceLookupService::MISSING);

        $this->assertTrue(VenueMap::refreshReady($curator), 'a miss is an answer too');
        $this->assertTrue(VenueMap::ready($curator->fresh()));
    }
}
