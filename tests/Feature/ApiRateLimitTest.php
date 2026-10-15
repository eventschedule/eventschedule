<?php

namespace Tests\Feature;

use App\Http\Middleware\ApiAuthentication;
use App\Models\User;
use Illuminate\Cache\RateLimiter as CacheRateLimiter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The keyed API buckets in ApiAuthentication: 300 reads and 30 writes a minute per ACCOUNT, each in
 * a fixed one-minute window that opens with the bucket's first counted request, and a bucket per
 * address for requests that prove no key.
 *
 * The counter used to be a Cache::put() of count + 1 with a fresh one-minute expiry on every
 * request, so the expiry kept moving and a bucket emptied only after a full idle minute: a client
 * reading every 30 seconds was refused on its 300th request, two and a half hours in.
 *
 * The buckets were per address until 2026-10, which put every caller behind one shared address (a
 * voice-agent platform, a company's NAT) on a single budget.
 */
class ApiRateLimitTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private const IP = '93.184.216.10';

    private const UNPROVEN_KEY = ApiAuthentication::UNPROVEN_PREFIX.self::IP;

    private string $key;

    private User $owner;

    private string $readKey;

    private string $writeKey;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = $this->createOwner();
        $this->createRole($this->owner);
        $this->key = $this->apiKey($this->owner);
        $this->readKey = ApiAuthentication::bucket($this->owner->id, false);
        $this->writeKey = ApiAuthentication::bucket($this->owner->id, true);
    }

    private function apiKey(User $user): string
    {
        $raw = 'testapikey_'.Str::random(24);
        $user->api_key = substr(hash('sha256', $raw), 0, 8);
        $user->api_key_hash = Hash::make($raw);
        $user->save();

        return $raw;
    }

    private function read(?string $key = null)
    {
        return $this->withServerVariables(['REMOTE_ADDR' => self::IP])
            ->withHeaders(['X-API-Key' => $key ?? $this->key])
            ->getJson('/api/schedules');
    }

    private function write()
    {
        return $this->withServerVariables(['REMOTE_ADDR' => self::IP])
            ->withHeaders(['X-API-Key' => $this->key])
            ->postJson('/api/schedules', []);
    }

    public function test_the_300th_read_passes_and_the_301st_is_refused_with_retry_after(): void
    {
        RateLimiter::increment($this->readKey, 60, 299);

        $this->read()->assertOk();

        $refused = $this->read();
        $refused->assertStatus(429)->assertExactJson(['error' => 'Rate limit exceeded']);

        $retryAfter = (int) $refused->headers->get('Retry-After');
        $this->assertGreaterThanOrEqual(1, $retryAfter);
        $this->assertLessThanOrEqual(60, $retryAfter);
    }

    public function test_a_full_bucket_clears_when_its_minute_is_up(): void
    {
        RateLimiter::increment($this->readKey, 60, 300);
        $this->read()->assertStatus(429);

        $this->travel(61)->seconds();

        $this->read()->assertOk();
        $this->assertSame(1, RateLimiter::attempts($this->readKey));
    }

    /**
     * 61 seconds after the first request its window has closed, so the third request opens a new
     * one. The old counter re-armed its expiry on every request and would read 3 here.
     */
    public function test_the_window_is_fixed_from_the_first_request(): void
    {
        $this->read()->assertOk();
        $this->travel(40)->seconds();
        $this->read()->assertOk();
        $this->travel(21)->seconds();
        $this->read()->assertOk();

        $this->assertSame(1, RateLimiter::attempts($this->readKey));
    }

    public function test_a_full_write_bucket_leaves_reads_alone(): void
    {
        RateLimiter::increment($this->writeKey, 60, 30);

        $this->write()->assertStatus(429)->assertHeader('Retry-After');
        $this->read()->assertOk();

        $this->assertSame(30, RateLimiter::attempts($this->writeKey), 'a refused request is not counted');
        $this->assertSame(1, RateLimiter::attempts($this->readKey));
    }

    public function test_a_full_read_bucket_leaves_writes_alone(): void
    {
        RateLimiter::increment($this->readKey, 60, 300);

        // An empty body fails validation; what matters is that the read bucket did not refuse it.
        $this->assertNotSame(429, $this->write()->status());
        $this->assertSame(1, RateLimiter::attempts($this->writeKey));
    }

    public function test_a_request_that_fails_authentication_is_counted_against_the_address_only(): void
    {
        $this->read('testapikey_not_a_real_key')->assertStatus(401);

        $this->assertSame(0, RateLimiter::attempts($this->readKey));
        $this->assertSame(1, RateLimiter::attempts(self::UNPROVEN_KEY));
    }

    public function test_two_accounts_behind_one_address_have_a_budget_each(): void
    {
        $other = $this->createOwner();
        $otherKey = $this->apiKey($other);

        RateLimiter::increment($this->readKey, 60, 300);

        $this->read()->assertStatus(429);
        $this->read($otherKey)->assertOk();
        $this->assertSame(1, RateLimiter::attempts(ApiAuthentication::bucket($other->id, false)));
    }

    /**
     * An address that keeps sending wrong keys is refused before any key is checked (each check
     * is a bcrypt comparison), but a key already proven from it is let past: a caller must not be
     * refused for somebody else's broken client on the same shared address.
     */
    public function test_an_address_sending_wrong_keys_is_refused_but_a_proven_key_is_not(): void
    {
        $this->read()->assertOk(); // proves the key

        RateLimiter::increment(self::UNPROVEN_KEY, 60, ApiAuthentication::UNPROVEN_LIMIT);

        $this->read('testapikey_not_a_real_key')->assertStatus(429);
        $this->withServerVariables(['REMOTE_ADDR' => self::IP])->getJson('/api/schedules')->assertStatus(429);
        $this->read()->assertOk();
    }

    /**
     * Behind Cloudflare the socket's peer is an edge address that thousands of visitors share, so
     * wrong keys are counted against the visitor's own address (Blocklist::address()): one
     * client's broken key must not fill the bucket for everybody that edge serves.
     */
    public function test_wrong_keys_are_counted_against_the_visitors_own_address(): void
    {
        config(['app.hosted' => true]);

        $this->withServerVariables(['REMOTE_ADDR' => self::IP])
            ->withHeaders(['X-API-Key' => 'testapikey_not_a_real_key', 'CF-Connecting-IP' => '203.0.113.7'])
            ->getJson('/api/schedules')
            ->assertStatus(401);

        $this->assertSame(1, RateLimiter::attempts(ApiAuthentication::UNPROVEN_PREFIX.'203.0.113.7'));
        $this->assertSame(0, RateLimiter::attempts(self::UNPROVEN_KEY), 'not the shared edge address');
    }

    /**
     * A proven key is remembered for a day after each use, and forgotten the moment it is
     * disabled, so an old key gets no way past the per-address bucket.
     */
    public function test_a_disabled_key_is_forgotten_as_proven(): void
    {
        $this->read()->assertOk();
        $proven = ApiAuthentication::KEY_OWNER_PREFIX.hash('sha256', $this->key);
        $this->assertSame($this->owner->id, (int) Cache::get($proven));

        $this->actingAs($this->owner)
            ->patch(route('api-settings.update'), ['enable_api' => '0'])
            ->assertRedirect();
        $this->assertNull($this->owner->fresh()->api_key_hash);
        $this->assertNull(Cache::get($proven));

        auth()->logout();
        RateLimiter::increment(self::UNPROVEN_KEY, 60, ApiAuthentication::UNPROVEN_LIMIT);
        $this->read()->assertStatus(429);
    }

    public function test_a_proven_key_is_remembered_for_a_day_after_its_last_use(): void
    {
        $this->read()->assertOk();
        $proven = ApiAuthentication::KEY_OWNER_PREFIX.hash('sha256', $this->key);

        $this->travel(20)->hours();
        $this->read()->assertOk();
        $this->travel(20)->hours();

        $this->assertSame($this->owner->id, (int) Cache::get($proven), 'each use refreshes the day');
    }

    /**
     * Two requests can both read 299 before either one counts. The post-auth hit() is the atomic
     * increment, so the one that lands past the limit is refused there. The limiter below stands
     * in for such a request: its pre-auth check never sees the full bucket.
     */
    public function test_a_request_that_raced_past_the_check_is_refused_by_the_count(): void
    {
        RateLimiter::increment($this->readKey, 60, 300);

        RateLimiter::swap(new class(app('cache')->driver()) extends CacheRateLimiter
        {
            public function tooManyAttempts($key, $maxAttempts)
            {
                return false;
            }
        });

        $this->read()->assertStatus(429)->assertHeader('Retry-After');
    }
}
