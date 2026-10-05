<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Services\GeocodingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * What a schedule's address costs at Google's Geocoding API, which bills per request and bills
 * "found nothing" like a hit.
 *
 * Role's saving hook used to write geo_address only when Google resolved the address, so a
 * schedule Google could not place was sent back on EVERY save - including the no-op saves a
 * calendar sync makes every fifteen minutes, since `saving` fires before Eloquent's dirty check.
 * And nothing was shared between rows: the hourly demo reset geocoded sixteen schedules in three
 * cities, every hour. Each test here counts requests, because the bug was never a wrong answer.
 *
 * The key is set per test: phpunit.xml pins BACKEND_GOOGLE_KEY empty so that no other fixture can
 * reach Google at all (TestEnvironmentTest holds that).
 */
class GeocodingCostTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private const UNFINDABLE = 'Backstage, behind the blue door';

    /** Flipped by a test to stand for Google's index catching up with a real address. */
    private bool $googleHasLearned = false;

    /** Flipped by a test to stand for an outage: every request fails in transit. */
    private bool $googleIsDown = false;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.google.backend' => 'test-key']);
        Cache::flush();
    }

    /** Google as a fake: nothing for the UNFINDABLE street, a hit for everything else. */
    private function fakeGoogle(): void
    {
        Http::fake(['maps.googleapis.com/*' => function (Request $request) {
            if ($this->googleIsDown) {
                return Http::response([], 500);
            }

            return str_contains($request->data()['address'] ?? '', self::UNFINDABLE) && ! $this->googleHasLearned
                ? Http::response(['status' => 'ZERO_RESULTS', 'results' => []])
                : Http::response($this->hit());
        }]);
    }

    private function hit(): array
    {
        return ['status' => 'OK', 'results' => [[
            'formatted_address' => '123 Main St, Springfield, IL 62701, USA',
            'place_id' => 'ChIJ-fixture',
            'geometry' => ['location' => ['lat' => 39.7817, 'lng' => -89.6501]],
            'address_components' => [
                ['long_name' => '123', 'short_name' => '123', 'types' => ['street_number']],
                ['long_name' => 'Main Street', 'short_name' => 'Main St', 'types' => ['route']],
                ['long_name' => 'Springfield', 'short_name' => 'Springfield', 'types' => ['locality', 'political']],
                ['long_name' => 'Illinois', 'short_name' => 'IL', 'types' => ['administrative_area_level_1', 'political']],
                ['long_name' => 'United States', 'short_name' => 'US', 'types' => ['country', 'political']],
                ['long_name' => '62701', 'short_name' => '62701', 'types' => ['postal_code']],
            ],
        ]]];
    }

    private function requests(): int
    {
        return count(Http::recorded());
    }

    /** Rows in the shared cache table, as opposed to this process's own memory of an answer. */
    private function sharedCacheRows(): int
    {
        return DB::table('cache')->where('key', 'like', '%geocode:v1:%')->count();
    }

    public function test_an_address_google_cannot_find_is_asked_about_once_a_month_not_once_a_save(): void
    {
        $this->fakeGoogle();

        $venue = $this->createVenueWithAddress($this->createOwner(), ['address1' => self::UNFINDABLE]);
        $this->assertSame(1, $this->requests(), 'fixture: creating the venue must geocode it');

        // What the calendar syncs used to do every fifteen minutes, and what any unrelated save
        // still does: a save that changes no address.
        for ($i = 0; $i < 3; $i++) {
            $venue = $venue->fresh();
            $venue->google_sync_token = 'cursor-'.$i;
            $venue->save();
        }
        $venue->fresh()->save();

        $this->assertSame(1, $this->requests(), 'a schedule Google cannot place was geocoded again on a later save');

        $venue->refresh();
        $this->assertSame($venue->fullAddressRaw(), $venue->geo_address, 'the miss must be recorded against the address');
        $this->assertNull($venue->geo_lat);
        $this->assertNull($venue->geo_lon);

        // Once the held answer has expired it is asked again - once, however many saves follow.
        $this->travel(31)->days();
        $venue->fresh()->save();
        $venue->fresh()->save();

        $this->assertSame(2, $this->requests(), 'an expired miss must cost one request, not one per save');
    }

    /**
     * A real address Google does not know yet (a new development, a gap in its index) must not be
     * stuck without a map until somebody retypes it.
     */
    public function test_an_address_google_learns_later_resolves_without_being_retyped(): void
    {
        $this->fakeGoogle();

        $venue = $this->createVenueWithAddress($this->createOwner(), ['address1' => self::UNFINDABLE]);
        $this->assertNull($venue->geo_lat, 'fixture: the address must start out unresolved');

        $this->googleHasLearned = true;
        $venue->fresh()->save();
        $this->assertNull($venue->fresh()->geo_lat, 'inside the month the held answer still stands');

        $this->travel(31)->days();
        $venue->fresh()->save();

        $venue->refresh();
        $this->assertSame(2, $this->requests());
        $this->assertEqualsWithDelta(39.7817, (float) $venue->geo_lat, 0.00001);
        $this->assertSame('ChIJ-fixture', $venue->google_place_id);
    }

    /**
     * geo_address is a varchar(255) on a strict connection, and the composed address is longer
     * than the columns it is built from: address1 alone may be 255, before the city and country.
     * A calendar import stores a Teams or Zoom join link as the venue's address1, clamped to
     * exactly that - the very address Google answers ZERO_RESULTS for - so writing the miss back
     * as the raw string threw MySQL 1406 inside the import and lost the event.
     */
    public function test_a_long_address_google_cannot_find_still_saves_and_is_asked_about_once(): void
    {
        $this->fakeGoogle();

        $joinLink = str_pad(self::UNFINDABLE.' https://teams.example/l/meetup-join/', 255, 'x');
        $venue = $this->createRole($this->createOwner(), 'venue', ['address1' => $joinLink, 'country_code' => 'us']);

        $this->assertGreaterThan(255, mb_strlen($venue->fullAddressRaw()), 'fixture: the composed address must overflow the column');
        $this->assertSame(1, $this->requests());
        $this->assertNotNull($venue->geo_address, 'the miss must still be recorded');
        $this->assertLessThanOrEqual(255, mb_strlen($venue->geo_address));

        $venue->fresh()->save();
        $this->assertSame(1, $this->requests(), 'the long address was not recognised as the one already asked about');

        // The part that changed is past what the column can hold, and is still a different place.
        $venue = $venue->fresh();
        $venue->city = 'Shelbyville';
        $venue->save();
        $this->assertSame(2, $this->requests(), 'two long addresses that differ only late were read as one');
    }

    /**
     * The other two varchar(255) columns the hook writes, on a hit. Google sets no maximum length
     * on a place ID, and a truncated one is not a shorter ID but an invalid one - so it is dropped,
     * and the guest map falls back to the address.
     */
    public function test_a_long_answer_from_google_still_saves(): void
    {
        Http::fake(['maps.googleapis.com/*' => Http::response(['status' => 'OK', 'results' => [[
            'formatted_address' => str_repeat('Long Building Name, ', 15).'Springfield',
            'place_id' => str_repeat('E', 300),
            'geometry' => ['location' => ['lat' => 39.7817, 'lng' => -89.6501]],
        ]]])]);

        $venue = $this->createVenueWithAddress($this->createOwner());

        $this->assertEqualsWithDelta(39.7817, (float) $venue->geo_lat, 0.00001);
        $this->assertStringStartsWith('Long Building Name', $venue->formatted_address);
        $this->assertLessThanOrEqual(255, mb_strlen($venue->formatted_address));
        $this->assertNull($venue->google_place_id);
    }

    public function test_moving_to_an_address_google_cannot_find_drops_the_old_coordinates(): void
    {
        $this->fakeGoogle();

        $venue = $this->createVenueWithAddress($this->createOwner());
        $this->assertEqualsWithDelta(39.7817, (float) $venue->fresh()->geo_lat, 0.00001, 'fixture: the first address must resolve');

        $venue->address1 = self::UNFINDABLE;
        $venue->save();
        $venue->refresh();

        // Left in place they would be published as the new address's: on the map, in the JSON-LD,
        // on a wallet pass.
        $this->assertNull($venue->geo_lat);
        $this->assertNull($venue->geo_lon);
        $this->assertNull($venue->formatted_address);
        $this->assertNull($venue->google_place_id);
        $this->assertSame($venue->fullAddressRaw(), $venue->geo_address);
    }

    /** The composed address, literally: every part in order, then the country. */
    public function test_the_address_sent_to_google_is_every_part_in_order_then_the_country(): void
    {
        $this->fakeGoogle();

        $this->createVenueWithAddress($this->createOwner(), ['address2' => 'Suite 4']);

        Http::assertSent(fn (Request $request) => $request->data()['address'] === '123 Main St, Suite 4, Springfield, IL, 62701, us');
    }

    public function test_two_schedules_at_one_address_cost_one_request(): void
    {
        $this->fakeGoogle();

        $first = $this->createVenueWithAddress($this->createOwner());
        // Case and spacing are not a different place.
        $second = $this->createVenueWithAddress($this->createOwner(), ['address1' => '123  MAIN st']);

        $this->assertSame(1, $this->requests());
        $this->assertEqualsWithDelta(39.7817, (float) $second->fresh()->geo_lat, 0.00001, 'the cached answer must still reach the second row');
        $this->assertSame('ChIJ-fixture', $second->fresh()->google_place_id);
        $this->assertNotSame($first->fresh()->geo_address, $second->fresh()->geo_address, 'each row records its own spelling');
    }

    public static function failuresInTransit(): array
    {
        return [
            'a 500' => [500, []],
            'quota exhausted' => [200, ['status' => 'OVER_QUERY_LIMIT', 'results' => []]],
            'a rejected key' => [200, ['status' => 'REQUEST_DENIED', 'results' => []]],
            'a body that is not Google\'s' => [200, ['unexpected' => true]],
        ];
    }

    #[DataProvider('failuresInTransit')]
    public function test_a_failed_request_is_not_a_verdict_on_the_address(int $httpStatus, array $body): void
    {
        Http::fake(['maps.googleapis.com/*' => Http::response($body, $httpStatus)]);

        $venue = $this->createVenueWithAddress($this->createOwner());
        $this->assertSame(1, $this->requests());
        $this->assertNull($venue->fresh()->geo_address, 'a failure in transit must not be recorded as "Google cannot find this"');

        // For a few minutes the failure is held, so a save loop cannot hammer it...
        $venue->fresh()->save();
        $this->assertSame(1, $this->requests(), 'the failure was retried at once');

        // ...and then the row, still unresolved, asks again: an owner who saves a second time
        // must get a real retry, not the same held failure for the rest of the hour.
        $this->travel(6)->minutes();
        $venue->fresh()->save();
        $this->assertSame(2, $this->requests(), 'a transient failure must be retried once its hold expires');
    }

    public function test_an_address_google_rejects_with_a_400_is_a_miss_like_any_other(): void
    {
        Http::fake(['maps.googleapis.com/*' => Http::response(['status' => 'INVALID_REQUEST', 'results' => []], 400)]);

        $venue = $this->createVenueWithAddress($this->createOwner());
        $venue->fresh()->save();

        $this->assertSame(1, $this->requests());
        $this->assertSame($venue->fullAddressRaw(), $venue->fresh()->geo_address);
    }

    public function test_changing_the_address_asks_again_and_clearing_it_clears_the_result(): void
    {
        $this->fakeGoogle();

        $venue = $this->createVenueWithAddress($this->createOwner());
        $venue->address1 = '9 Harbour Rd';
        $venue->save();

        $this->assertSame(2, $this->requests(), 'a new address is a new question');
        $this->assertSame($venue->fullAddressRaw(), $venue->fresh()->geo_address);

        foreach (['address1', 'address2', 'city', 'state', 'postal_code'] as $column) {
            $venue->{$column} = null;
        }
        $venue->save();
        $venue->refresh();

        $this->assertSame(2, $this->requests());
        foreach (['geo_address', 'geo_lat', 'geo_lon', 'formatted_address', 'google_place_id'] as $column) {
            $this->assertNull($venue->{$column}, "{$column} outlived the address it described");
        }
    }

    /**
     * The geocode half of "the saving hook derives nothing from a column it cannot see". The
     * rendered description and banner are the other half, in RoleSaveHookTest.
     */
    public function test_a_narrowed_hydrate_neither_geocodes_nor_wipes_the_stored_geocode(): void
    {
        $this->fakeGoogle();

        $venue = $this->createVenueWithAddress($this->createOwner());
        $stored = $venue->fresh()->only(['geo_address', 'geo_lat', 'geo_lon', 'formatted_address', 'google_place_id']);
        $this->assertNotNull($stored['geo_lat'], 'fixture: the venue must be geocoded');
        Cache::flush();

        // Without the address columns the composed address reads as empty: "the address was removed".
        $withoutAddress = Role::query()->select(['id', 'name', 'geo_address'])->findOrFail($venue->id);
        $withoutAddress->name = 'Renamed';
        $withoutAddress->save();

        // With only some of them it reads as a different, partial address: "it moved".
        $partialAddress = Role::query()->select(['id', 'name', 'address1'])->findOrFail($venue->id);
        $partialAddress->name = 'Renamed Again';
        $partialAddress->save();

        // With the address but not the coordinates it reads as resolved-to-nothing: "ask again".
        $withoutCoordinates = Role::query()
            ->select(['id', 'name', 'address1', 'address2', 'city', 'state', 'postal_code', 'country_code', 'geo_address'])
            ->findOrFail($venue->id);
        $withoutCoordinates->name = 'Renamed Once More';
        $withoutCoordinates->save();

        $this->assertSame(1, $this->requests(), 'a narrowed select was read as a changed address');
        $this->assertSame($stored, $venue->fresh()->only(array_keys($stored)), 'a narrowed select wiped or replaced the stored geocode');
    }

    /**
     * The other shape with missing attribute keys: a schedule this instance created and never
     * re-read. There a key it lacks was never assigned and IS null, so it must not be mistaken
     * for a narrowed select - or giving the schedule its address would never geocode it.
     */
    public function test_a_schedule_created_in_this_request_is_geocoded_when_it_is_given_an_address(): void
    {
        $this->fakeGoogle();

        $venue = new Role;
        $venue->subdomain = 'created-then-addressed';
        $venue->name = 'Created Then Addressed';
        $venue->type = 'venue';
        $venue->timezone = 'America/New_York';
        $venue->save();
        $this->assertSame(0, $this->requests(), 'fixture: no address yet');

        $venue->address1 = '123 Main St';
        $venue->city = 'Springfield';
        $venue->save();

        $this->assertSame(1, $this->requests(), 'an un-refreshed instance was read as a narrowed select');
        $this->assertNotNull($venue->fresh()->geo_lat);

        $venue->address1 = null;
        $venue->city = null;
        $venue->save();

        $this->assertNull($venue->fresh()->geo_lat, 'clearing the address on the same instance must clear its geocode');
    }

    /**
     * On hosted the shared cache is a table on the connection the save itself runs on. Written
     * inside the caller's transaction, the cache row stays locked until that commits and a cache
     * failure aborts the save - so the answer is held in this process and reaches the shared
     * store only after the commit.
     */
    public function test_the_shared_cache_is_written_after_the_callers_transaction_not_inside_it(): void
    {
        config(['cache.default' => 'database']);
        $this->fakeGoogle();
        $owner = $this->createOwner();

        DB::transaction(function () use ($owner) {
            $this->createVenueWithAddress($owner);
            $this->createVenueWithAddress($owner);

            $this->assertSame(1, $this->requests(), 'a second schedule at the same address, in the same transaction, paid again');
            $this->assertSame(0, $this->sharedCacheRows(), 'the cache row was written inside the transaction');
        });

        $this->assertSame(1, $this->sharedCacheRows(), 'the answer never reached the shared cache');
    }

    public function test_a_rolled_back_save_writes_nothing_to_the_shared_cache_and_is_not_paid_for_twice(): void
    {
        config(['cache.default' => 'database']);
        $this->fakeGoogle();
        $owner = $this->createOwner();

        try {
            DB::transaction(function () use ($owner) {
                $this->createVenueWithAddress($owner);

                throw new \RuntimeException('rolled back');
            });
        } catch (\RuntimeException) {
            // The import, or whatever the save was part of, failed after the geocode.
        }

        $this->assertSame(0, $this->sharedCacheRows());

        // The retry in the same process (a transaction retried after a deadlock, say) still knows.
        $this->createVenueWithAddress($owner);
        $this->assertSame(1, $this->requests());
    }

    public function test_without_a_key_nothing_is_sent_and_nothing_is_remembered(): void
    {
        $this->fakeGoogle();
        config(['services.google.backend' => null]);

        $venue = $this->createVenueWithAddress($this->createOwner());

        $this->assertSame(0, $this->requests());
        $this->assertNull($venue->fresh()->geo_address);
        $this->assertSame(GeocodingService::NOT_CONFIGURED, GeocodingService::lookup('123 Main St')['status']);

        // Configuring the key later must take effect on the next save, not after a cache TTL.
        config(['services.google.backend' => 'test-key']);
        $venue->fresh()->save();

        $this->assertSame(1, $this->requests());
        $this->assertNotNull($venue->fresh()->geo_lat);
    }

    /**
     * The Validate button asks again about an address held as "not found". If THAT request fails
     * in transit, the held answer must survive it: replaced by the short-lived failure, the
     * thirty-day entry that bounds the saving hook's re-ask is gone, and every schedule at the
     * address is billed again a few minutes later.
     */
    public function test_a_failed_fresh_lookup_does_not_throw_away_a_held_answer(): void
    {
        $this->fakeGoogle();
        $address = self::UNFINDABLE.', Springfield, us';

        $this->assertSame('ZERO_RESULTS', GeocodingService::lookup($address)['status']);

        $this->googleIsDown = true;
        $this->assertSame(GeocodingService::UNAVAILABLE, GeocodingService::lookup($address, fresh: true)['status']);
        $this->assertSame(2, $this->requests());

        // Past the hold a failure gets, with Google back: the miss is still what is remembered.
        $this->googleIsDown = false;
        $this->travel(6)->minutes();

        $this->assertSame('ZERO_RESULTS', GeocodingService::lookup($address)['status']);
        $this->assertSame(2, $this->requests(), 'a failure in transit replaced the answer it could not improve on');
    }

    /** A cache store that is down: every read and every write throws. */
    private function takeTheSharedCacheDown(\Throwable $failure): void
    {
        Cache::extend('down', fn () => Cache::repository(new class($failure) extends \Illuminate\Cache\ArrayStore
        {
            public function __construct(private \Throwable $failure)
            {
                parent::__construct();
            }

            public function get($key)
            {
                throw $this->failure;
            }

            public function put($key, $value, $seconds)
            {
                throw $this->failure;
            }
        }));

        config(['cache.stores.down' => ['driver' => 'down'], 'cache.default' => 'down']);
    }

    /**
     * Geocoding is best effort and the cache is its bookkeeping. When the cache cannot be read
     * the save must still go through - and Google must not be asked, because with nowhere to
     * remember the answer every save would ask again, which is the bill this class exists to stop.
     */
    public function test_a_cache_that_cannot_be_read_neither_fails_the_lookup_nor_spends(): void
    {
        $this->fakeGoogle();
        Exceptions::fake();
        $this->takeTheSharedCacheDown(new \RuntimeException('cache is down'));

        $this->assertSame(GeocodingService::UNAVAILABLE, GeocodingService::lookup('123 Main St, Springfield, us')['status']);
        $this->assertSame(0, $this->requests());
        Exceptions::assertReported(\RuntimeException::class);
    }

    /**
     * The exception to that. On the database store a failed read is a failed statement on the
     * caller's own connection, and inside a transaction it may be a deadlock that has already
     * rolled the transaction back. Carrying on as if nothing happened would be worse than failing.
     */
    public function test_a_database_cache_failure_inside_a_transaction_is_not_swallowed(): void
    {
        $this->fakeGoogle();
        $this->takeTheSharedCacheDown(new \Illuminate\Database\QueryException(
            'mysql', 'select * from `cache`', [], new \RuntimeException('Deadlock found when trying to get lock')
        ));

        // RefreshDatabase runs each test inside one, which is the transaction this is "inside".
        // (A DB::transaction() here would rewrap the deadlock as a DeadlockException.)
        $this->assertGreaterThan(0, DB::transactionLevel());
        $this->expectException(\Illuminate\Database\QueryException::class);

        GeocodingService::lookup('123 Main St, Springfield, us');
    }

    public function test_the_validate_button_reads_the_same_cache(): void
    {
        $this->fakeGoogle();

        $owner = $this->createOwner();
        $form = ['address1' => '123 Main St', 'city' => 'Springfield', 'state' => 'IL', 'postal_code' => '62701', 'country_code' => 'us'];

        $first = $this->actingAs($owner)->postJson(route('validate_address'), $form)->assertOk();
        $second = $this->actingAs($owner)->postJson(route('validate_address'), $form)->assertOk();

        $this->assertSame(1, $this->requests(), 'pressing Validate twice on one address paid twice');
        $this->assertSame($first->json(), $second->json());
        $first->assertJsonPath('data.address1', '123 Main Street')
            ->assertJsonPath('data.city', 'Springfield')
            ->assertJsonPath('data.state', 'Illinois')
            ->assertJsonPath('data.postal_code', '62701')
            ->assertJsonPath('data.country', 'US')
            ->assertJsonPath('data.lat', 39.7817)
            ->assertJsonPath('data.lng', -89.6501);
    }

    public function test_the_validate_button_asks_again_about_an_address_google_could_not_find(): void
    {
        $this->fakeGoogle();

        $owner = $this->createOwner();
        $form = ['address1' => self::UNFINDABLE, 'city' => 'Springfield', 'country_code' => 'us'];

        $this->actingAs($owner)->postJson(route('validate_address'), $form)->assertStatus(400);

        // Someone is waiting on this one, so a held "no" is not repeated back: the owner may have
        // pressed it again precisely because the address should exist by now.
        $this->googleHasLearned = true;
        $this->actingAs($owner)->postJson(route('validate_address'), $form)->assertOk();

        $this->assertSame(2, $this->requests());
    }

    public function test_the_validate_button_retries_a_failure_instead_of_serving_the_held_one(): void
    {
        Http::fake(['maps.googleapis.com/*' => Http::response([], 500)]);

        $owner = $this->createOwner();
        $form = ['address1' => '123 Main St', 'city' => 'Springfield', 'country_code' => 'us'];

        $this->actingAs($owner)->postJson(route('validate_address'), $form)->assertStatus(500);
        $this->actingAs($owner)->postJson(route('validate_address'), $form)->assertStatus(500);

        $this->assertSame(2, $this->requests());
    }

    /**
     * A rejected key or an exhausted quota is the install's problem. Reported as a 400 it told the
     * owner that a correct address was wrong.
     */
    #[DataProvider('accountLevelFailures')]
    public function test_the_validate_button_does_not_blame_the_address_for_an_account_failure(string $status): void
    {
        Http::fake(['maps.googleapis.com/*' => Http::response(['status' => $status, 'results' => []])]);

        $this->actingAs($this->createOwner())
            ->postJson(route('validate_address'), ['address1' => '123 Main St', 'city' => 'Springfield', 'country_code' => 'us'])
            ->assertStatus(500)
            ->assertJsonPath('error', 'Failed to validate address');
    }

    public static function accountLevelFailures(): array
    {
        return [['REQUEST_DENIED'], ['OVER_QUERY_LIMIT'], ['OVER_DAILY_LIMIT']];
    }
}
