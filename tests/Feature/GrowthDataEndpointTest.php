<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\User;
use App\Services\AuditService;
use App\Services\GrowthExportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * GET /api/internal/growth - the token-secured pull that replaced the /admin/growth download.
 *
 * Every test sets its own token. phpunit.xml pins GROWTH_DATA_TOKEN empty, because the operator's
 * real one sits in the .env this suite loads.
 */
class GrowthDataEndpointTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private const TOKEN = '9c1e5b0f3a7d42e8b6c0f19a2d4e7b3c5a8f0e1d2c3b4a59687766554433f2a1';

    protected function setUp(): void
    {
        parent::setUp();

        // Hosted, like the only install this runs on; off-hosted it is a 404 (tested below).
        config(['app.hosted' => true, 'app.growth_data_token' => self::TOKEN]);
    }

    private function pull(?string $authorization = 'Bearer '.self::TOKEN, string $query = '')
    {
        $headers = $authorization === null ? [] : ['Authorization' => $authorization];

        return $this->withHeaders($headers)->getJson('/api/internal/growth'.$query);
    }

    public function test_the_browser_download_is_gone_and_the_endpoint_replaces_it(): void
    {
        $this->assertFalse(Route::has('admin.growth.export'));
        $this->assertTrue(Route::has('api.growth_data'));
    }

    public function test_it_does_not_exist_until_a_token_is_configured(): void
    {
        config(['app.growth_data_token' => '']);

        $this->pull()->assertNotFound();
        $this->pull(null)->assertNotFound();
        $this->pull('Bearer ')->assertNotFound();
    }

    /** A weak value disables the endpoint rather than guarding it with a guessable secret. */
    public function test_a_short_token_disables_it_even_when_it_matches(): void
    {
        $short = str_repeat('a', 31);
        config(['app.growth_data_token' => $short]);

        $this->pull('Bearer '.$short)->assertNotFound();
    }

    public function test_it_is_not_found_on_a_selfhosted_install(): void
    {
        config(['app.hosted' => false]);

        $this->pull()->assertNotFound();
    }

    public function test_missing_or_wrong_credentials_are_refused_and_audited(): void
    {
        $this->pull(null)->assertUnauthorized();
        $this->pull('Bearer '.strrev(self::TOKEN))->assertUnauthorized();
        $this->pull('Bearer '.self::TOKEN.'0')->assertUnauthorized();
        $this->pull('Basic '.base64_encode('admin:'.self::TOKEN))->assertUnauthorized();
        // Never from the query string, where it would land in every access log.
        $this->pull(null, '?token='.self::TOKEN)->assertUnauthorized();

        $codes = AuditLog::forAction(AuditService::API_AUTH_FAILED)->pluck('metadata')->all();
        $this->assertSame([
            'growth_data:no_bearer', 'growth_data:mismatch', 'growth_data:mismatch',
            'growth_data:no_bearer', 'growth_data:no_bearer',
        ], $codes, 'a stripped header must be distinguishable from a wrong token');

        foreach (AuditLog::all() as $row) {
            $this->assertStringNotContainsString(self::TOKEN, json_encode($row->toArray()), 'the audit trail must never hold the token');
        }

        $this->assertSame(0, AuditLog::forAction(AuditService::ADMIN_GROWTH_DATA_PULL)->count());
    }

    public function test_the_right_token_returns_the_payload_uncached_and_audited(): void
    {
        $this->freeRole();

        $response = $this->pull(query: '?range=last_90_days');

        $response->assertOk();
        $response->assertJsonPath('meta.schema_version', GrowthExportService::SCHEMA_VERSION);
        $this->assertArrayHasKey('signups', $response->json());
        $this->assertArrayHasKey('schedules', $response->json());
        $this->assertStringContainsString('no-store', (string) $response->headers->get('Cache-Control'));
        // The api group has no session: a pull must not mint a cookie.
        $this->assertSame([], $response->headers->getCookies());

        $pull = AuditLog::forAction(AuditService::ADMIN_GROWTH_DATA_PULL)->sole();
        $this->assertNull($pull->user_id);
        $this->assertSame('last_90_days', $pull->new_values['range']);
        $this->assertArrayHasKey('duration_ms', $pull->new_values, 'the build cost is recorded once it finishes');
        $this->assertArrayHasKey('peak_memory_mb', $pull->new_values);
    }

    /** ?range[]=x arrives as an array, which used to TypeError into a 500 on every admin page. */
    public function test_an_array_range_falls_back_instead_of_failing(): void
    {
        $this->pull(query: '?range[]=x')->assertOk();

        $this->assertSame('all_time', AuditLog::forAction(AuditService::ADMIN_GROWTH_DATA_PULL)->sole()->new_values['range']);
    }

    public function test_only_one_build_runs_at_a_time(): void
    {
        $lock = Cache::lock('growth_data_build', 120);
        $this->assertTrue($lock->get());

        try {
            $this->pull()->assertStatus(429)->assertHeader('Retry-After');
        } finally {
            $lock->release();
        }

        $this->pull()->assertOk();
    }

    /**
     * The named limiter keys on the visitor's real address. The positional `throttle:N,M,x` form
     * keys on $request->ip(), which on hosted is a Cloudflare edge, so one bucket would be shared
     * by everyone and anyone could lock the operator out of their own pulls.
     */
    public function test_the_limiter_keys_on_the_real_client_not_the_cloudflare_edge(): void
    {
        $limiter = RateLimiter::limiter('growth_data');
        $request = fn (string $edge, string $client) => Request::create('/api/internal/growth', 'GET', server: [
            'REMOTE_ADDR' => $edge, 'HTTP_CF_CONNECTING_IP' => $client,
        ]);

        $this->assertNotSame(
            $limiter($request('172.68.1.1', '198.51.100.1'))->key,
            $limiter($request('172.68.1.1', '198.51.100.2'))->key,
            'two people behind one edge must not share a budget'
        );
        $this->assertSame(
            $limiter($request('172.68.1.1', '198.51.100.1'))->key,
            $limiter($request('172.68.9.9', '198.51.100.1'))->key,
            'one person arriving through two edges is one budget'
        );
        $this->assertStringNotContainsString('198.51.100.1', $limiter($request('172.68.1.1', '198.51.100.1'))->key);
    }

    /**
     * The throttle middleware returns early under app.is_testing, so a broken limiter name would
     * pass every other test while 500ing in production (it has happened - see ThrottleRequests).
     * This drives it with the flag off.
     */
    public function test_the_throttle_is_live_outside_the_test_flag(): void
    {
        config(['app.is_testing' => false, 'app.growth_data_token' => '']);

        foreach (range(1, 10) as $n) {
            $this->pull()->assertNotFound();
        }

        $this->pull()->assertStatus(429);
    }

    /**
     * The real surface, over HTTP, with values shaped the way production stores them - landing
     * paths have no leading slash and no host, referrers are full URLs with query strings.
     */
    public function test_the_payload_over_http_carries_no_personal_data(): void
    {
        $owner = $this->createOwner();
        $owner->forceFill([
            'name' => 'Marina Delacroix',
            'email' => 'marina.delacroix@gmail.com',
            'email_verified_at' => now(),
            'referrer_url' => 'https://marina-delacroix-photography.fr/about?email=leak@gmail.com',
            'landing_page' => 'ticket/view/x7Kq2/k3Jd9sLq2mZx8vB1nC4tY6wR0pE5hG7a',
            'utm_source' => 'marina.delacroix@gmail.com',
        ])->save();

        $role = $this->freeRole($owner);
        DB::table('roles')->where('id', $role->id)->update([
            'email' => 'boxoffice@gmail.com', 'address1' => '19 Rue Lepic', 'stripe_id' => 'cus_TESTCUSTOMER',
            'custom_domain' => 'https://tickets.marinadelacroix.fr',
        ]);
        $event = $this->createEvent($role, ['name' => 'Summa 30th Anniversary Party']);
        $ticket = $this->createTicket($event, ['price' => 20]);
        $this->createSale($event, $role, ['status' => 'paid', 'payment_amount' => 20, 'email' => 'buyer@gmail.com', 'name' => 'Pat Buyer'], $ticket, 1);

        // A tenant event slug one person landed on. (Three landing on it would be exported by
        // design: a page three signups share describes no one of them.)
        $this->signup(['landing_page' => 'summa-30th-anniversary-party']);
        foreach (range(1, 3) as $n) {
            $this->signup(['referrer_url' => 'http://203.0.113.7/']);
            $this->signup(['referrer_url' => 'https://tickets.marinadelacroix.fr/events']);
            $this->signup(['utm_source' => 'promo-'.$n.'@gmail.com']);
        }

        $body = $this->pull()->assertOk()->getContent();

        foreach ([
            'Marina', 'Delacroix', 'marina.delacroix@gmail.com', 'boxoffice@gmail.com', 'buyer@gmail.com',
            'Pat Buyer', 'leak@gmail.com', '19 Rue Lepic', 'cus_TESTCUSTOMER', $role->subdomain,
            'marina-delacroix-photography', 'marinadelacroix.fr', '203.0.113.7',
            'k3Jd9sLq2mZx8vB1nC4tY6wR0pE5hG7a', 'Summa', 'summa-30th',
        ] as $secret) {
            $this->assertStringNotContainsStringIgnoringCase($secret, $body, "the payload leaked: {$secret}");
        }

        $this->assertDoesNotMatchRegularExpression('/[\w.+-]+@[\w-]+\.[\w.]+/', $body, 'nothing may look like an email address');
        $this->assertDoesNotMatchRegularExpression('/\b\d{1,3}(\.\d{1,3}){3}\b/', $body, 'nothing may look like an IP');

        foreach (['(ip)', '(schedule)', '(redacted)', '(other)'] as $placeholder) {
            $this->assertStringContainsString($placeholder, $body);
        }
    }

    private function signup(array $attrs): User
    {
        $user = $this->createOwner();
        $user->forceFill($attrs)->save();

        return $user;
    }

    private function freeRole(?User $owner = null)
    {
        return $this->createRole($owner ?? $this->createOwner(), 'venue', [
            'plan_type' => 'free',
            'plan_expires' => now()->subYear()->format('Y-m-d'),
            'trial_ends_at' => null,
        ]);
    }
}
