<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Cache\RateLimiter as CacheRateLimiter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The keyed API buckets in ApiAuthentication: 300 reads and 30 writes a minute per IP, each in a
 * fixed one-minute window that opens with the bucket's first counted request.
 *
 * The counter used to be a Cache::put() of count + 1 with a fresh one-minute expiry on every
 * request, so the expiry kept moving and a bucket emptied only after a full idle minute: a client
 * reading every 30 seconds was refused on its 300th request, two and a half hours in.
 */
class ApiRateLimitTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private const IP = '93.184.216.10';

    private const READ_KEY = 'api_rate_limit_read:'.self::IP;

    private const WRITE_KEY = 'api_rate_limit_write:'.self::IP;

    private string $key;

    protected function setUp(): void
    {
        parent::setUp();

        $owner = $this->createOwner();
        $this->createRole($owner);
        $this->key = $this->apiKey($owner);
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
        RateLimiter::increment(self::READ_KEY, 60, 299);

        $this->read()->assertOk();

        $refused = $this->read();
        $refused->assertStatus(429)->assertExactJson(['error' => 'Rate limit exceeded']);

        $retryAfter = (int) $refused->headers->get('Retry-After');
        $this->assertGreaterThanOrEqual(1, $retryAfter);
        $this->assertLessThanOrEqual(60, $retryAfter);
    }

    public function test_a_full_bucket_clears_when_its_minute_is_up(): void
    {
        RateLimiter::increment(self::READ_KEY, 60, 300);
        $this->read()->assertStatus(429);

        $this->travel(61)->seconds();

        $this->read()->assertOk();
        $this->assertSame(1, RateLimiter::attempts(self::READ_KEY));
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

        $this->assertSame(1, RateLimiter::attempts(self::READ_KEY));
    }

    public function test_a_full_write_bucket_leaves_reads_alone(): void
    {
        RateLimiter::increment(self::WRITE_KEY, 60, 30);

        $this->write()->assertStatus(429)->assertHeader('Retry-After');
        $this->read()->assertOk();

        $this->assertSame(30, RateLimiter::attempts(self::WRITE_KEY), 'a refused request is not counted');
        $this->assertSame(1, RateLimiter::attempts(self::READ_KEY));
    }

    public function test_a_full_read_bucket_leaves_writes_alone(): void
    {
        RateLimiter::increment(self::READ_KEY, 60, 300);

        // An empty body fails validation; what matters is that the read bucket did not refuse it.
        $this->assertNotSame(429, $this->write()->status());
        $this->assertSame(1, RateLimiter::attempts(self::WRITE_KEY));
    }

    public function test_a_request_that_fails_authentication_is_not_counted(): void
    {
        $this->read('testapikey_not_a_real_key')->assertStatus(401);

        $this->assertSame(0, RateLimiter::attempts(self::READ_KEY));
    }

    /**
     * Two requests can both read 299 before either one counts. The post-auth hit() is the atomic
     * increment, so the one that lands past the limit is refused there. The limiter below stands
     * in for such a request: its pre-auth check never sees the full bucket.
     */
    public function test_a_request_that_raced_past_the_check_is_refused_by_the_count(): void
    {
        RateLimiter::increment(self::READ_KEY, 60, 300);

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
