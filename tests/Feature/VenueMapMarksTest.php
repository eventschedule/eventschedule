<?php

namespace Tests\Feature;

use App\Models\PlaceLookup;
use App\Models\Role;
use App\Models\User;
use App\Models\VenueMapMark;
use App\Models\VenueMapSetting;
use App\Services\PlaceLookupService;
use App\Services\VenueMap;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * What a schedule's owner decides about a venue on their venue map (venue_map_marks): take it off
 * the map, put it back, move its pin, place one by hand where the address search found nothing.
 *
 * A mark is the owner's own fact about ONE map:
 *
 *   - only the people who may edit the schedule can set one, and only for a venue that map holds;
 *   - it never reaches another schedule's map, the venue itself, or the looked-up position, which
 *     "Use the looked-up position" goes back to;
 *   - a pin placed by hand is where the venue is, whatever the search said or did not say;
 *   - a venue taken off the map is on no public page and stays in the owner's list, to put back.
 */
class VenueMapMarksTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.map.geocoder_url' => 'https://lookup.test/search']);
        Http::preventStrayRequests();
        Cache::flush();
    }

    /** @return array{0: User, 1: Role, 2: Role, 3: Role} the owner, the curator, a found venue, a venue the search missed */
    private function map(): array
    {
        $owner = $this->createOwner();
        $curator = $this->createRole($owner, 'curator', ['country_code' => 'il']);
        VenueMap::saveSettings($curator, true, false);

        $found = $this->venue($curator, 'The Cellar', ['address1' => '1 Harbour St', 'city' => 'Binyamina']);
        $missed = $this->venue($curator, 'The Field', ['address1' => 'Behind the orchard', 'city' => 'Binyamina']);
        $third = $this->venue($curator, 'The Loft', ['address1' => '9 Mill Lane', 'city' => 'Binyamina']);

        $rows = VenueMap::venues($curator->fresh())->keyBy(fn (array $v) => $v['venue']->id);
        PlaceLookup::where('address_hash', $rows[$found->id]['hash'])->update(['status' => PlaceLookupService::FOUND, 'lat' => 32.5, 'lon' => 34.9, 'looked_up_at' => now()]);
        PlaceLookup::where('address_hash', $rows[$third->id]['hash'])->update(['status' => PlaceLookupService::FOUND, 'lat' => 32.52, 'lon' => 34.95, 'looked_up_at' => now()]);
        PlaceLookup::where('address_hash', $rows[$missed->id]['hash'])->update(['status' => PlaceLookupService::MISSING, 'looked_up_at' => now()]);
        VenueMapSetting::where('role_id', $curator->id)->update(['ready_at' => now()]);

        return [$owner, $curator->fresh(), $found, $missed];
    }

    private function venue(Role $curator, string $name, array $attrs = []): Role
    {
        $venue = $this->createRole($this->createOwner(), 'venue', $attrs + ['name' => $name, 'country_code' => 'il']);
        $event = $this->createEvent($curator, ['name' => 'Night at '.$name, 'creator_role_id' => $curator->id]);
        $event->roles()->attach($venue->id, ['is_accepted' => true]);

        return $venue;
    }

    private function mark(User $user, Role $curator, Role $venue, array $body)
    {
        return $this->actingAs($user)->putJson(route('role.venue_map.mark', ['subdomain' => $curator->subdomain, 'venue' => UrlUtils::encodeId($venue->id)]), $body);
    }

    private function row(Role $curator, Role $venue): array
    {
        return VenueMap::venues($curator->fresh(), null, false)->first(fn (array $v) => $v['venue']->id === $venue->id);
    }

    private function publicKeys(Role $curator): array
    {
        return array_column($this->getJson(route('role.venue_map', ['subdomain' => $curator->subdomain]))->assertOk()->json('venues'), 'key');
    }

    public function test_a_pin_placed_by_hand_is_where_the_venue_is(): void
    {
        [$owner, $curator, , $missed] = $this->map();

        $this->assertNull($this->row($curator, $missed)['lat'], 'the search found nothing');

        $this->mark($owner, $curator, $missed, ['lat' => 32.5123456789, 'lon' => 34.9123456789])
            ->assertOk()
            ->assertJsonPath('venue.state', VenueMap::PLACED)
            ->assertJsonPath('venue.by_hand', true)
            ->assertJsonPath('venue.lat', 32.5123457)
            ->assertJsonPath('placed', 3);

        $row = $this->row($curator, $missed);
        $this->assertSame([VenueMap::PLACED, 32.5123457, 34.9123457, true], [$row['state'], $row['lat'], $row['lon'], $row['by_hand']]);

        $sent = collect($this->getJson(route('role.venue_map', ['subdomain' => $curator->subdomain]))->json('venues'))->firstWhere('key', $missed->subdomain);
        $this->assertSame(32.5123457, $sent['lat'], 'and that is the pin a visitor sees');
        $this->assertFalse($sent['approx']);
        $this->assertNull($sent['why']);
    }

    public function test_a_moved_pin_beats_the_looked_up_one_and_can_be_gone_back_from(): void
    {
        [$owner, $curator, $found] = $this->map();

        $this->mark($owner, $curator, $found, ['lat' => 32.6, 'lon' => 34.8])->assertOk()->assertJsonPath('venue.found', true);
        $this->assertSame(32.6, $this->row($curator, $found)['lat']);
        $this->assertSame(32.5, (float) PlaceLookup::where('status', PlaceLookupService::FOUND)->orderBy('id')->first()->lat, 'the looked-up position is untouched');

        $this->actingAs($owner)->deleteJson(route('role.venue_map.unmark', ['subdomain' => $curator->subdomain, 'venue' => UrlUtils::encodeId($found->id)]))
            ->assertOk()
            ->assertJsonPath('venue.by_hand', false)
            ->assertJsonPath('venue.lat', 32.5);

        $this->assertSame(0, VenueMapMark::count(), 'a mark that says nothing is no mark');
    }

    public function test_a_venue_taken_off_the_map_is_on_no_public_page_and_can_be_put_back(): void
    {
        [$owner, $curator, $found] = $this->map();

        $this->assertContains($found->subdomain, $this->publicKeys($curator));
        $this->assertSame(3, VenueMap::band($curator)['total']);

        $this->mark($owner, $curator, $found, ['hidden' => true])->assertOk()->assertJsonPath('venue.hidden', true)->assertJsonPath('total', 2);

        $this->assertNotContains($found->subdomain, $this->publicKeys($curator));
        $this->assertNull(VenueMap::band($curator->fresh()), 'one pin left is not a map');

        $status = collect(VenueMap::status($curator->fresh())['venues']);
        $this->assertTrue($status->firstWhere('key', $found->subdomain)['hidden'], 'it stays in the owner\'s list');
        $this->assertSame($found->subdomain, $status->last()['key'], 'at the end of it');

        $this->mark($owner, $curator, $found, ['hidden' => false])->assertOk()->assertJsonPath('venue.hidden', false);
        $this->assertContains($found->subdomain, $this->publicKeys($curator->fresh()));
        $this->assertSame(0, VenueMapMark::count());
    }

    public function test_taking_a_venue_off_keeps_a_pin_that_was_placed_by_hand(): void
    {
        [$owner, $curator, , $missed] = $this->map();

        $this->mark($owner, $curator, $missed, ['lat' => 32.51, 'lon' => 34.91])->assertOk();
        $this->mark($owner, $curator, $missed, ['hidden' => true])->assertOk();
        $this->mark($owner, $curator, $missed, ['hidden' => false])->assertOk()->assertJsonPath('venue.lat', 32.51);
    }

    public function test_a_mark_is_about_one_schedules_map(): void
    {
        [$owner, $curator, $found] = $this->map();

        // The same venue on another curator's map.
        $other = $this->createRole($this->createOwner(), 'curator', ['country_code' => 'il']);
        VenueMap::saveSettings($other, true, false);
        $event = $this->createEvent($other, ['creator_role_id' => $other->id]);
        $event->roles()->attach($found->id, ['is_accepted' => true]);

        $this->mark($owner, $curator, $found, ['lat' => 32.6, 'lon' => 34.8])->assertOk();
        $this->mark($owner, $curator, $found, ['hidden' => true])->assertOk();

        $theirs = $this->row($other, $found);
        $this->assertSame(32.5, $theirs['lat'], 'their pin is where the search put it');
        $this->assertFalse($theirs['hidden']);
        $this->assertNull($found->fresh()->geo_lat, 'and nothing was written to the venue');
    }

    public function test_only_the_schedules_editors_can_set_a_mark(): void
    {
        [$owner, $curator, $found] = $this->map();

        $venue = UrlUtils::encodeId($found->id);
        $url = route('role.venue_map.mark', ['subdomain' => $curator->subdomain, 'venue' => $venue]);

        $this->putJson($url, ['hidden' => true])->assertUnauthorized();

        $stranger = $this->createOwner();
        $this->actingAs($stranger)->putJson($url, ['hidden' => true])->assertForbidden();
        $this->actingAs($stranger)->deleteJson($url)->assertForbidden();

        // The venue's own owner is not an editor of the curator's schedule.
        $this->actingAs($found->users()->first())->putJson($url, ['hidden' => true])->assertForbidden();

        $viewer = $this->createOwner();
        $curator->users()->attach($viewer->id, ['level' => 'viewer']);
        $this->actingAs($viewer)->putJson($url, ['hidden' => true])->assertForbidden();

        $this->assertSame(0, VenueMapMark::count());
        $this->actingAs($owner)->putJson($url, ['hidden' => true])->assertOk();
    }

    public function test_a_venue_this_map_does_not_hold_cannot_be_marked(): void
    {
        [$owner, $curator] = $this->map();

        $elsewhere = $this->createRole($this->createOwner(), 'venue', ['address1' => '5 Far St', 'city' => 'Haifa', 'country_code' => 'il']);

        $this->mark($owner, $curator, $elsewhere, ['lat' => 32.8, 'lon' => 35.0])->assertNotFound();
        $this->actingAs($owner)->putJson(route('role.venue_map.mark', ['subdomain' => $curator->subdomain, 'venue' => 'not-an-id']), ['hidden' => true])->assertNotFound();
        $this->assertSame(0, VenueMapMark::count());
    }

    public function test_a_position_that_is_not_a_place_is_refused(): void
    {
        [$owner, $curator, $found] = $this->map();

        foreach ([
            ['lat' => 91, 'lon' => 34.9],
            ['lat' => 32.5, 'lon' => 181],
            ['lat' => 'north', 'lon' => 34.9],
            ['lat' => 32.5],
            ['lon' => 34.9],
            ['lat' => 0, 'lon' => 0],
            ['hidden' => 'perhaps'],
            [],
            ['position' => '32.5,34.9'],
        ] as $body) {
            $this->mark($owner, $curator, $found, $body)->assertStatus(422, json_encode($body));
        }

        $this->assertSame(0, VenueMapMark::count());
    }

    public function test_a_map_does_not_wait_for_a_venue_its_owner_has_dealt_with(): void
    {
        [$owner, $curator, $found] = $this->map();

        // A new venue whose address nobody has asked about yet: the map would wait for it.
        $waiting = $this->venue($curator, 'The Barn', ['address1' => '2 Farm Road', 'city' => 'Binyamina']);
        VenueMapSetting::where('role_id', $curator->id)->update(['ready_at' => null]);
        $this->assertFalse(VenueMap::refreshReady($curator->fresh()));

        $this->mark($owner, $curator, $waiting, ['hidden' => true])->assertOk();

        $this->assertTrue(VenueMap::ready($curator->fresh()), 'taken off the map, it is not waited for');
    }

    public function test_the_owners_list_says_who_may_change_an_address(): void
    {
        [$owner, $curator, $found, $missed] = $this->map();

        // The owner also runs one of the venues.
        $found->users()->attach($owner->id, ['level' => 'admin']);

        $venues = collect($this->actingAs($owner)->getJson(route('role.venue_map.status', ['subdomain' => $curator->subdomain]))->assertOk()->json('venues'));

        $this->assertStringEndsWith('/edit#section-address', $venues->firstWhere('key', $found->subdomain)['edit_url']);
        $this->assertNull($venues->firstWhere('key', $missed->subdomain)['edit_url'], 'another person\'s venue');
        $this->assertSame('Behind the orchard, Binyamina', $venues->firstWhere('key', $missed->subdomain)['address']);
        $this->assertSame(UrlUtils::encodeId($missed->id), $venues->firstWhere('key', $missed->subdomain)['id'], 'the handle is encoded, never the raw id');
    }

    public function test_deleting_the_schedule_or_the_venue_takes_its_marks_with_it(): void
    {
        [$owner, $curator, $found, $missed] = $this->map();

        $this->mark($owner, $curator, $found, ['hidden' => true])->assertOk();
        $this->mark($owner, $curator, $missed, ['lat' => 32.51, 'lon' => 34.91])->assertOk();
        $this->assertSame(2, VenueMapMark::count());

        Role::where('id', $missed->id)->delete();
        $this->assertSame(1, VenueMapMark::count());

        Role::where('id', $curator->id)->delete();
        $this->assertSame(0, VenueMapMark::count());
    }

    public function test_a_merge_carries_the_owners_decision_to_the_venue_it_became(): void
    {
        $owner = $this->createOwner();
        $curator = $this->createRole($owner, 'curator', ['country_code' => 'il']);
        VenueMap::saveSettings($curator, true, false);

        // The same place twice: the owner's own venue, and a placeholder a calendar import made.
        $real = $this->createRole($owner, 'venue', ['name' => 'Ozen Bar', 'city' => 'Tel Aviv', 'country_code' => 'il']);
        $stub = new Role;
        $stub->forceFill(['subdomain' => 'stubozenbar', 'type' => 'venue', 'name' => 'Ozen Bar', 'address1' => 'Ozen Bar', 'city' => 'Tel Aviv', 'country_code' => 'il'])->save();
        $this->followRole($owner, $stub);
        $event = $this->createEvent($curator, ['creator_role_id' => $curator->id]);
        $event->roles()->attach($stub->id, ['is_accepted' => true]);

        // The pin was placed by hand on the placeholder.
        $this->mark($owner, $curator, $stub->fresh(), ['lat' => 32.07, 'lon' => 34.77])->assertOk();

        $this->actingAs($owner)->post(route('following.merge_venues_group'), [
            'target_id' => UrlUtils::encodeId($real->id),
            'source_ids' => [UrlUtils::encodeId($stub->id)],
        ])->assertRedirect();

        $this->assertDatabaseHas('roles', ['id' => $stub->id, 'is_deleted' => true]);
        $this->assertSame([$real->id], VenueMapMark::pluck('venue_id')->all(), 'the pin moved with the venue');

        $row = $this->row($curator, $real);
        $this->assertSame([32.07, 34.77, true], [$row['lat'], $row['lon'], $row['by_hand']]);
    }
}
