<?php

namespace Tests\Feature;

use App\Models\PlaceLookup;
use App\Models\Role;
use App\Services\PlaceLookupService;
use App\Services\VenueMap;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The venue map's address search (App\Services\PlaceLookupService, app:place-venues).
 *
 * The search service is free and has a policy, and each rule here is one of its clauses or a way
 * a venue would otherwise be pinned in the wrong place:
 *
 *   - what is sent is the venue's own address, never a machine translation, never a meeting link
 *     a calendar import stored as an address, never a bare name with no town to place it in;
 *   - one request per ADDRESS, however many venue rows share it and however often it is needed;
 *   - at most four a run on the timer, and one runner at a time;
 *   - a failure in transit is not a verdict on the address;
 *   - a town's centre is not a pin, unless the place is small enough for its centre to be near
 *     everything in it.
 */
class PlaceLookupTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private const SEARCH = 'https://lookup.test/search';

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.map.geocoder_url' => self::SEARCH]);
        Http::preventStrayRequests();
        Cache::flush();
    }

    private function venue(array $attrs = []): Role
    {
        return $this->createRole($this->createOwner(), 'venue', $attrs + ['address1' => '14 HaDekalim St', 'city' => 'Pardes Hanna', 'country_code' => 'il']);
    }

    private function pending(string $address = '14 HaDekalim St, Pardes Hanna', string $country = 'il'): PlaceLookup
    {
        return PlaceLookup::create(['address_hash' => sha1(mb_strtolower($address).'|'.$country), 'address' => $address, 'country_code' => $country]);
    }

    private function answer(array $item): array
    {
        return [array_merge(['lat' => '32.4762', 'lon' => '34.9741', 'place_rank' => 30, 'boundingbox' => ['32.4761', '32.4763', '34.9740', '34.9742']], $item)];
    }

    public function test_the_address_is_the_venues_own_words(): void
    {
        $address = PlaceLookupService::addressFor($this->venue(['state' => 'Haifa', 'postal_code' => '3700000']));

        $this->assertSame('14 HaDekalim St, Pardes Hanna, Haifa, 3700000', $address['address']);
        $this->assertSame('il', $address['country']);
        $this->assertTrue($address['street']);
        $this->assertNull($address['why']);
    }

    public function test_a_machine_translation_is_never_sent(): void
    {
        // Role::fullAddress() swaps in the _en copies while a translation is showing. Those are
        // machine translations of a Hebrew address: sent, they are a different query, and a worse one.
        $venue = $this->venue(['address1' => 'הדקלים 14', 'city' => 'פרדס חנה', 'address1_en' => '14 Palm Trees St', 'city_en' => 'Pardes Hanna', 'language_code' => 'he', 'translation_language_code' => 'en']);
        session(['translate' => true]);
        app()->setLocale('en');

        $this->assertSame('הדקלים 14, פרדס חנה', PlaceLookupService::addressFor($venue->fresh())['address']);
    }

    public function test_a_meeting_link_or_a_bare_name_is_never_sent(): void
    {
        // A calendar import stores whatever the location field held in address1.
        $link = PlaceLookupService::addressFor($this->venue(['address1' => 'https://zoom.us/j/123456', 'city' => null]));
        $this->assertNull($link['hash']);
        $this->assertSame('online', $link['why'], 'a link and nothing else is not a place');

        $www = PlaceLookupService::addressFor($this->venue(['address1' => 'www.meet.example/room', 'city' => 'Pardes Hanna']));
        $this->assertSame('Pardes Hanna', $www['address'], 'the link is dropped; the town alone can still be asked');
        $this->assertFalse($www['street']);

        $bare = PlaceLookupService::addressFor($this->venue(['address1' => 'The Old Winery', 'city' => null, 'postal_code' => null]));
        $this->assertNull($bare['hash'], 'a name with no town or postcode would be pinned somewhere confident and wrong');

        $nothing = PlaceLookupService::addressFor($this->venue(['address1' => null, 'city' => null]));
        $this->assertNull($nothing['hash']);
    }

    public function test_the_whole_world_is_never_searched(): void
    {
        $venue = $this->venue(['country_code' => null]);

        $this->assertSame('no_country', PlaceLookupService::addressFor($venue)['why']);

        $schedule = $this->createRole($this->createOwner(), 'curator', ['country_code' => 'IL']);
        $this->assertSame('il', PlaceLookupService::addressFor($venue, $schedule)['country'], 'the schedule\'s country stands in for a venue that has none');
    }

    public function test_one_request_for_an_address_two_venues_share(): void
    {
        $curator = $this->createRole($this->createOwner(), 'curator', ['country_code' => 'il']);
        VenueMap::saveSettings($curator, true, false);

        foreach (['Front Room', 'Back Room'] as $name) {
            $venue = $this->venue(['name' => $name]);
            $event = $this->createEvent($curator, ['name' => $name.' night', 'creator_role_id' => $curator->id]);
            $event->roles()->attach($venue->id, ['is_accepted' => true]);
        }

        VenueMap::venues($curator->fresh());
        $this->assertSame(1, PlaceLookup::count(), 'two venue rows at one address are one question');

        Http::fake([self::SEARCH.'*' => Http::response($this->answer([]))]);
        $result = PlaceLookupService::run(PlaceLookupService::TIMER_BATCH, 0);

        $this->assertSame(1, $result['asked']);
        Http::assertSentCount(1);
        Http::assertSent(fn (Request $request) => $request['q'] === '14 HaDekalim St, Pardes Hanna'
            && $request['countrycodes'] === 'il'
            && str_contains($request->header('User-Agent')[0], config('app.url')));

        $states = VenueMap::venues($curator->fresh())->pluck('state')->all();
        $this->assertSame([VenueMap::PLACED, VenueMap::PLACED], $states);

        // Needed again and again, asked never again.
        VenueMap::venues($curator->fresh());
        PlaceLookupService::run(PlaceLookupService::TIMER_BATCH, 0);
        Http::assertSentCount(1);
    }

    public function test_a_run_on_the_timer_asks_four_at_most_oldest_first(): void
    {
        foreach (range(1, 6) as $i) {
            $this->pending($i.' Some St, Hadera')->forceFill(['created_at' => now()->subMinutes(10 - $i)])->save();
        }

        Http::fake([self::SEARCH.'*' => Http::response($this->answer([]))]);

        $this->assertSame(4, PlaceLookupService::run(PlaceLookupService::TIMER_BATCH, 0)['asked']);
        Http::assertSentCount(4);
        $this->assertSame(['5 Some St, Hadera', '6 Some St, Hadera'], PlaceLookup::where('status', PlaceLookupService::PENDING)->orderBy('id')->pluck('address')->all());
    }

    public function test_a_street_is_a_pin_and_a_small_place_is_an_approximate_one(): void
    {
        $street = $this->pending('14 HaDekalim St, Pardes Hanna');
        $kibbutz = $this->pending('Maagan Michael');
        $town = $this->pending('Some Unknown St, Hadera');
        $nothing = $this->pending('Nowhere At All, Atlantis');

        Http::fake(function (Request $request) {
            return match ($request['q']) {
                '14 HaDekalim St, Pardes Hanna' => Http::response($this->answer(['place_rank' => 26])),
                // A village about 1.3 km across: its centre is near everything in it.
                'Maagan Michael' => Http::response($this->answer(['lat' => '32.5572', 'lon' => '34.9176', 'place_rank' => 19, 'boundingbox' => ['32.5510', '32.5630', '34.9110', '34.9240']])),
                // The street was not found and the service answered with the town, 7 km across.
                'Some Unknown St, Hadera' => Http::response($this->answer(['lat' => '32.4340', 'lon' => '34.9190', 'place_rank' => 16, 'boundingbox' => ['32.4000', '32.4700', '34.8800', '34.9600']])),
                default => Http::response([]),
            };
        });

        PlaceLookupService::run(10, 0);

        $this->assertSame(PlaceLookupService::FOUND, $street->fresh()->status);
        $this->assertSame(32.4762, $street->fresh()->lat);
        $this->assertSame(PlaceLookupService::APPROXIMATE, $kibbutz->fresh()->status);
        $this->assertSame(PlaceLookupService::MISSING, $town->fresh()->status, 'a town\'s centre would put the venue on the wrong side of town');
        $this->assertNull($town->fresh()->lat);
        $this->assertSame(PlaceLookupService::MISSING, $nothing->fresh()->status);
        $this->assertNotNull($nothing->fresh()->looked_up_at, 'a miss is an answer, and is not asked again tomorrow');
    }

    public function test_a_failure_in_transit_says_nothing_about_the_address(): void
    {
        foreach ([429, 403, 503] as $status) {
            PlaceLookup::query()->delete();
            Cache::flush();
            $first = $this->pending('1 First St, Hadera');
            $second = $this->pending('2 Second St, Hadera');

            Http::fake([self::SEARCH.'*' => Http::response('slow down', $status)]);
            $result = PlaceLookupService::run(4, 0);

            $this->assertTrue($result['failed'], "HTTP {$status}");
            $this->assertSame(1, $result['asked'], 'the run ends at the first failure');
            $this->assertSame(PlaceLookupService::PENDING, $first->fresh()->status, 'nothing is recorded against the address');
            $this->assertNull($first->fresh()->looked_up_at);
            $this->assertSame(PlaceLookupService::PENDING, $second->fresh()->status);

            // And nobody asks again straight away.
            $this->assertTrue(PlaceLookupService::run(4, 0)['paused']);
            $this->assertNotNull(Cache::get(PlaceLookupService::FAILING_SINCE_KEY));
        }
    }

    public function test_a_timeout_or_a_body_that_is_not_the_services_json_is_a_failure_too(): void
    {
        $row = $this->pending();

        Http::fake([self::SEARCH.'*' => fn () => throw new ConnectionException('timed out')]);
        $this->assertTrue(PlaceLookupService::run(1, 0)['failed']);
        $this->assertSame(PlaceLookupService::PENDING, $row->fresh()->status);
        $this->assertNotNull($row->fresh()->try_after, 'the address waits out the pause');

        Cache::flush();
        PlaceLookup::query()->update(['try_after' => null]);
        Http::swap(new \Illuminate\Http\Client\Factory);
        Http::preventStrayRequests();
        Http::fake([self::SEARCH.'*' => Http::response('<html>Service under maintenance</html>')]);
        $this->assertTrue(PlaceLookupService::run(1, 0)['failed']);

        Cache::flush();
        PlaceLookup::query()->update(['try_after' => null]);
        Http::swap(new \Illuminate\Http\Client\Factory);
        Http::preventStrayRequests();
        Http::fake([self::SEARCH.'*' => Http::response(['error' => 'Unable to geocode'])]);
        $this->assertTrue(PlaceLookupService::run(1, 0)['failed'], 'an object where the list should be is not an answer');
        $this->assertSame(PlaceLookupService::PENDING, $row->fresh()->status);
    }

    public function test_an_answer_clears_the_failing_mark(): void
    {
        Cache::put(PlaceLookupService::FAILING_SINCE_KEY, now()->subHours(2)->timestamp, now()->addDay());
        $this->pending();

        Http::fake([self::SEARCH.'*' => Http::response([])]);
        PlaceLookupService::run(1, 0);

        $this->assertNull(Cache::get(PlaceLookupService::FAILING_SINCE_KEY));
    }

    public function test_only_one_runner_asks_at_a_time(): void
    {
        $this->pending();
        Http::fake([self::SEARCH.'*' => Http::response($this->answer([]))]);

        $held = Cache::lock('place_lookups_lock', 60);
        $this->assertTrue($held->get());

        $result = PlaceLookupService::run(4, 0);

        $this->assertTrue($result['busy']);
        Http::assertNothingSent();

        $held->release();
        $this->assertSame(1, PlaceLookupService::run(4, 0)['asked']);
    }

    public function test_a_miss_is_asked_again_after_thirty_days_and_only_while_a_map_needs_it(): void
    {
        $curator = $this->createRole($this->createOwner(), 'curator', ['country_code' => 'il']);
        VenueMap::saveSettings($curator, true, false);
        $venue = $this->venue();
        $event = $this->createEvent($curator, ['creator_role_id' => $curator->id]);
        $event->roles()->attach($venue->id, ['is_accepted' => true]);

        $row = $this->pending();
        $row->update(['status' => PlaceLookupService::MISSING, 'looked_up_at' => now()->subDays(PlaceLookupService::RETRY_MISS_DAYS - 1)]);

        VenueMap::venues($curator->fresh());
        $this->assertSame(PlaceLookupService::MISSING, $row->fresh()->status, 'not yet');

        $row->update(['looked_up_at' => now()->subDays(PlaceLookupService::RETRY_MISS_DAYS + 1)]);
        $state = VenueMap::venues($curator->fresh())->first()['state'];

        $this->assertSame(PlaceLookupService::PENDING, $row->fresh()->status);
        $this->assertSame(VenueMap::WAITING, $state);
    }

    public function test_the_command_asks_and_then_marks_a_finished_map_ready(): void
    {
        $curator = $this->createRole($this->createOwner(), 'curator', ['country_code' => 'il']);
        VenueMap::saveSettings($curator, true, false);
        $venue = $this->venue();
        $event = $this->createEvent($curator, ['creator_role_id' => $curator->id]);
        $event->roles()->attach($venue->id, ['is_accepted' => true]);

        Http::fake([self::SEARCH.'*' => Http::response($this->answer([]))]);

        // First run: nobody has noted the address yet. The command's own look at the schedule
        // notes it, so a map switched on while the queue was down still gets its lookups.
        $this->artisan('app:place-venues')->assertSuccessful();
        $this->assertSame(1, PlaceLookup::where('status', PlaceLookupService::PENDING)->count());
        $this->assertFalse(VenueMap::ready($curator->fresh()));

        $this->artisan('app:place-venues')->assertSuccessful();

        $this->assertSame(PlaceLookupService::FOUND, PlaceLookup::first()->status);
        $this->assertTrue(VenueMap::ready($curator->fresh()), 'every venue has been asked once');
        Http::assertSentCount(1);
    }

    public function test_the_command_does_nothing_on_an_install_with_no_address_search(): void
    {
        config(['services.map.geocoder_url' => null]);
        $this->pending();

        $this->artisan('app:place-venues')->assertSuccessful();

        Http::assertNothingSent();
        $this->assertSame(PlaceLookupService::PENDING, PlaceLookup::first()->status);
    }
}
