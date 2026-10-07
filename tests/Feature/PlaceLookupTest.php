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

        $this->travel(61)->seconds();
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

    /**
     * A hosted search answers "no result" with a 404 and an error object where the reference
     * service answers 200 and an empty list. That is the service speaking about THIS address: it
     * is a miss, and it must not stop the address behind it.
     */
    public function test_a_refusal_of_one_address_is_a_miss_and_does_not_stop_the_next(): void
    {
        $bad = $this->pending('Nowhere At All, Atlantis');
        $bad->forceFill(['created_at' => now()->subMinutes(5)])->save();
        $good = $this->pending('14 HaDekalim St, Pardes Hanna');

        Http::fake(function (Request $request) {
            return $request['q'] === 'Nowhere At All, Atlantis'
                ? Http::response(['error' => 'Unable to geocode'], 404)
                : Http::response($this->answer([]));
        });

        $result = PlaceLookupService::run(4, 0);

        $this->assertFalse($result['failed'], 'a 404 for one address is not an outage');
        $this->assertSame(2, $result['asked']);
        $this->assertSame(PlaceLookupService::MISSING, $bad->fresh()->status);
        $this->assertNotNull($bad->fresh()->looked_up_at, 'so it is asked again in thirty days, not in ten minutes');
        $this->assertSame(PlaceLookupService::FOUND, $good->fresh()->status);
        $this->assertFalse(PlaceLookupService::run(4, 0)['paused']);

        foreach ([400, 422] as $status) {
            $row = $this->pending('Odd address '.$status.', Hadera');
            Http::swap(new \Illuminate\Http\Client\Factory);
            Http::preventStrayRequests();
            Http::fake([self::SEARCH.'*' => Http::response(['error' => 'Invalid query'], $status)]);

            $this->assertFalse(PlaceLookupService::run(4, 0)['failed'], "HTTP {$status}");
            $this->assertSame(PlaceLookupService::MISSING, $row->fresh()->status, "HTTP {$status}");
        }
    }

    public function test_a_wrong_search_address_in_the_setting_is_an_outage_not_a_thousand_misses(): void
    {
        // MAP_GEOCODER_URL with a wrong path answers every request with a 404 PAGE. Read as a
        // verdict on the address, every venue on the install would be "address not found" and
        // nothing would tell the operator. It is the service's own JSON that makes a refusal.
        $row = $this->pending();
        Http::fake([self::SEARCH.'*' => Http::response('<html><h1>Not Found</h1></html>', 404)]);

        $this->assertTrue(PlaceLookupService::run(4, 0)['failed']);
        $this->assertSame(PlaceLookupService::PENDING, $row->fresh()->status);
        $this->assertNotNull(Cache::get(PlaceLookupService::FAILING_SINCE_KEY), 'and the admin\'s list hears of it');

        // The same for the answers that are about US, whatever their body.
        foreach ([401, 403, 429] as $status) {
            Cache::flush();
            PlaceLookup::query()->update(['try_after' => null]);
            Http::swap(new \Illuminate\Http\Client\Factory);
            Http::preventStrayRequests();
            Http::fake([self::SEARCH.'*' => Http::response(['error' => 'Invalid key'], $status)]);

            $this->assertTrue(PlaceLookupService::run(4, 0)['failed'], "HTTP {$status}");
            $this->assertSame(PlaceLookupService::PENDING, $row->fresh()->status, "HTTP {$status}");
        }
    }

    /**
     * An address that keeps failing in transit (it always times out) goes to the back of the line,
     * and is given up on: before this, the oldest row was asked first again after every pause,
     * and one such row was every lookup on the install, for good.
     */
    public function test_an_address_that_keeps_failing_goes_to_the_back_and_is_given_up_on(): void
    {
        $stuck = $this->pending('1 Stuck St, Hadera');
        $stuck->forceFill(['created_at' => now()->subHour()])->save();
        $next = $this->pending('2 Fine St, Hadera');

        Http::fake(function (Request $request) {
            return $request['q'] === '1 Stuck St, Hadera' ? Http::response('gateway timeout', 504) : Http::response($this->answer([]));
        });

        $this->assertTrue(PlaceLookupService::run(4, 0)['failed']);
        $this->assertSame(1, $stuck->fresh()->attempts);

        // The pause lapses. The address that has never failed is asked first now.
        Cache::flush();
        PlaceLookup::query()->update(['try_after' => null]);
        $asked = [];
        Http::swap(new \Illuminate\Http\Client\Factory);
        Http::preventStrayRequests();
        Http::fake(function (Request $request) use (&$asked) {
            $asked[] = $request['q'];

            return $request['q'] === '1 Stuck St, Hadera' ? Http::response('gateway timeout', 504) : Http::response($this->answer([]));
        });
        PlaceLookupService::run(4, 0);

        $this->assertSame(['2 Fine St, Hadera', '1 Stuck St, Hadera'], $asked);
        $this->assertSame(PlaceLookupService::FOUND, $next->fresh()->status);

        // And after its last allowed failure it is a miss, so nothing waits on it any longer.
        foreach (range(3, PlaceLookupService::MAX_TRANSIT_FAILURES) as $attempt) {
            Cache::flush();
            PlaceLookup::query()->update(['try_after' => null]);
            PlaceLookupService::run(4, 0);
        }

        $this->assertSame(PlaceLookupService::MISSING, $stuck->fresh()->status);
        $this->assertSame(PlaceLookupService::MAX_TRANSIT_FAILURES, $stuck->fresh()->attempts);
    }

    public function test_a_key_in_the_search_address_is_sent_with_every_request(): void
    {
        // A hosted search wants its key in the query. The HTTP client REPLACES an address's own
        // query string with the parameters it is given, so the key has to be carried over.
        config(['services.map.geocoder_url' => 'https://hosted.test/v1/search?key=SECRET&dedupe=1']);
        $this->pending();

        Http::fake(['https://hosted.test/*' => Http::response($this->answer([]))]);
        PlaceLookupService::run(1, 0);

        Http::assertSent(fn (Request $request) => str_starts_with($request->url(), 'https://hosted.test/v1/search?')
            && $request['key'] === 'SECRET'
            && $request['dedupe'] === '1'
            && $request['q'] === '14 HaDekalim St, Pardes Hanna'
            && $request['format'] === 'jsonv2');
    }

    public function test_a_link_anywhere_in_an_address_is_never_sent(): void
    {
        // A calendar's location field holds whatever was typed, and a meeting link carries its
        // password: "Zoom: https://...?pwd=..." with a town beside it used to be sent whole.
        foreach ([
            'Zoom: https://zoom.us/j/123456?pwd=SECRET',
            'Online (www.meet.example/room)',
            'meet.google.com/abc-defg-hij',
            'Join at teams.microsoft.com/l/meetup-join/19%3a',
        ] as $street) {
            $address = PlaceLookupService::addressFor($this->venue(['address1' => $street, 'city' => 'Pardes Hanna']));

            $this->assertSame('Pardes Hanna', $address['address'], $street);
            $this->assertFalse($address['street'], $street);
        }

        // And what is not a link stays an address, dots and slashes included.
        foreach (['12 St. John\'s Rd', '4/6 Herzl St', 'Bldg. 3, Unit 2/B', 'P.O. Box 12'] as $street) {
            $this->assertStringStartsWith($street, PlaceLookupService::addressFor($this->venue(['address1' => $street]))['address'], $street);
        }
    }

    public function test_the_timer_asks_once_a_minute_however_many_rails_tick(): void
    {
        foreach (range(1, 10) as $i) {
            $this->pending($i.' Some St, Hadera');
        }

        Http::fake([self::SEARCH.'*' => Http::response($this->answer([]))]);

        // The scheduler and /translate_data in the same minute, or a cron that calls the second
        // more than once: four a minute is the service's limit for a script on a timer.
        $this->artisan('app:place-venues')->assertSuccessful();
        $this->artisan('app:place-venues')->assertSuccessful();
        Http::assertSentCount(PlaceLookupService::TIMER_BATCH);

        $this->travel(61)->seconds();
        $this->artisan('app:place-venues')->assertSuccessful();
        Http::assertSentCount(2 * PlaceLookupService::TIMER_BATCH);
    }

    public function test_a_run_with_a_budget_stops_when_its_time_is_up(): void
    {
        foreach (range(1, 5) as $i) {
            $this->pending($i.' Some St, Hadera');
        }

        Http::fake([self::SEARCH.'*' => Http::response($this->answer([]))]);

        // No time at all: one address is asked, since a run that asks nothing helps nobody.
        $this->assertSame(1, PlaceLookupService::run(25, 0, null, 0.0)['asked']);
        $this->assertSame(4, PlaceLookupService::run(25, 0, null, 30.0)['asked']);
    }

    public function test_an_address_no_map_needs_any_more_is_forgotten(): void
    {
        $curator = $this->createRole($this->createOwner(), 'curator', ['country_code' => 'il']);
        VenueMap::saveSettings($curator, true, false);
        $venue = $this->venue();
        $event = $this->createEvent($curator, ['creator_role_id' => $curator->id]);
        $event->roles()->attach($venue->id, ['is_accepted' => true]);

        VenueMap::venues($curator->fresh());
        $needed = PlaceLookup::first();
        $this->assertTrue($needed->needed_at->isToday(), 'a map that reads an address says it is still needed');

        $old = $this->pending('9 Gone St, Hadera');
        $old->forceFill(['status' => PlaceLookupService::FOUND, 'lat' => 32.4, 'lon' => 34.9, 'needed_at' => now()->subDays(PlaceLookupService::KEEP_DAYS + 1)])->save();
        $recent = $this->pending('8 Recent St, Hadera');
        $recent->forceFill(['needed_at' => now()->subDays(PlaceLookupService::KEEP_DAYS - 1)])->save();

        $this->assertSame(1, PlaceLookupService::prune());
        $this->assertNull($old->fresh());
        $this->assertNotNull($recent->fresh());
        $this->assertNotNull($needed->fresh());

        // Read again tomorrow: one UPDATE a day per address, not one per page view.
        $needed->forceFill(['needed_at' => now()->subDay()])->save();
        VenueMap::venues($curator->fresh());
        $this->assertTrue($needed->fresh()->needed_at->isToday());
    }

    public function test_the_queued_head_start_places_a_schedules_venues_and_marks_the_map_ready(): void
    {
        $curator = $this->createRole($this->createOwner(), 'curator', ['country_code' => 'il']);
        VenueMap::saveSettings($curator, true, false);
        foreach (['Front Room' => '1 First St', 'Back Room' => '2 Second St'] as $name => $street) {
            $venue = $this->venue(['name' => $name, 'address1' => $street]);
            $event = $this->createEvent($curator, ['name' => $name.' night', 'creator_role_id' => $curator->id]);
            $event->roles()->attach($venue->id, ['is_accepted' => true]);
        }
        // Another schedule's address, waiting too: the head start is for THIS schedule's.
        $other = $this->pending('9 Elsewhere St, Hadera');
        VenueMap::venues($curator->fresh());

        Http::fake([self::SEARCH.'*' => Http::response($this->answer([]))]);
        (new \App\Jobs\PlaceScheduleVenues($curator->id))->handle();

        Http::assertSentCount(2);
        $this->assertSame(PlaceLookupService::PENDING, $other->fresh()->status);
        $this->assertTrue(VenueMap::ready($curator->fresh()));

        // The job's own bounds: it runs inside the scheduler's process, in front of the mail.
        $job = new \App\Jobs\PlaceScheduleVenues($curator->id);
        $this->assertLessThanOrEqual(60, $job->timeout);
        $this->assertLessThanOrEqual(20.0, PlaceLookupService::BURST_SECONDS);
        $this->assertSame((string) $curator->id, $job->uniqueId());
    }

    public function test_the_head_start_does_nothing_for_a_map_that_was_switched_off_or_deleted(): void
    {
        $curator = $this->createRole($this->createOwner(), 'curator', ['country_code' => 'il']);
        VenueMap::saveSettings($curator, true, false);
        $venue = $this->venue();
        $event = $this->createEvent($curator, ['creator_role_id' => $curator->id]);
        $event->roles()->attach($venue->id, ['is_accepted' => true]);
        VenueMap::venues($curator->fresh());

        Http::fake([self::SEARCH.'*' => Http::response($this->answer([]))]);

        VenueMap::saveSettings($curator, false, false);
        (new \App\Jobs\PlaceScheduleVenues($curator->id))->handle();
        (new \App\Jobs\PlaceScheduleVenues(999999))->handle();

        Http::assertNothingSent();
    }

    public function test_deleting_an_account_forgets_its_venues_addresses(): void
    {
        // A house concert's venue is its host's home. The row is keyed by the address and linked
        // to nothing, so no cascade reaches it.
        $host = $this->createOwner();
        $home = $this->createRole($host, 'venue', ['address1' => '7 Olive Lane', 'city' => 'Pardes Hanna', 'country_code' => null]);
        $curator = $this->createRole($this->createOwner(), 'curator', ['country_code' => 'il']);
        VenueMap::saveSettings($curator, true, false);
        $event = $this->createEvent($curator, ['creator_role_id' => $curator->id]);
        $event->roles()->attach($home->id, ['is_accepted' => true]);
        $elsewhere = $this->pending('1 Other St, Hadera');

        VenueMap::venues($curator->fresh());
        $this->assertSame(1, PlaceLookup::where('address', '7 Olive Lane, Pardes Hanna')->count(), 'filed under the LISTING schedule\'s country, the venue having none');

        app(\App\Services\AccountDeletionService::class)->prepare($host);

        $this->assertSame(0, PlaceLookup::where('address', '7 Olive Lane, Pardes Hanna')->count());
        $this->assertNotNull($elsewhere->fresh(), 'and nobody else\'s');
    }
}
