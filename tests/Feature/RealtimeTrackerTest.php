<?php

namespace Tests\Feature;

use App\Models\RealtimeHit;
use App\Models\Setting;
use App\Services\AdminAlertService;
use App\Utils\RealtimeTracker;
use App\Utils\UserAgentUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

class RealtimeTrackerTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
    }

    public static function referrers(): array
    {
        return [
            'chatgpt is AI, not Social via t.co' => ['chatgpt.com', [], 'ai', 'chatgpt.com'],
            'gemini is AI, not Search' => ['gemini.google.com', [], 'ai', 'gemini.google.com'],
            'gmail is Email, not Search' => ['mail.google.com', [], 'email', 'mail.google.com'],
            'country google is Search' => ['www.google.co.uk', [], 'search', 'google.co.uk'],
            // google.* used to claim every Google product: a Calendar invite or a shared Doc is not
            // a search visit.
            'a calendar invite is not Search' => ['calendar.google.com', [], 'other', 'calendar.google.com'],
            'a shared doc is not Search' => ['docs.google.com', [], 'other', 'docs.google.com'],
            't.co is Social' => ['t.co', [], 'social', 't.co'],
            'facebook link shim is Social' => ['l.facebook.com', [], 'social', 'l.facebook.com'],
            'a look-alike is not Social' => ['notfacebook.com', [], 'other', 'notfacebook.com'],
            'microsoft.com is not Social' => ['microsoft.com', [], 'other', 'microsoft.com'],
            'boost utm is Paid' => ['instagram.com', ['source' => 'boost', 'campaign' => 'abc'], 'paid', 'boost'],
            'newsletter utm is Email' => [null, ['source' => 'newsletter'], 'email', 'newsletter'],
            'an AI utm source is AI' => [null, ['source' => 'chatgpt.com'], 'ai', 'chatgpt.com'],
            'other utm is Campaign' => [null, ['source' => 'partner', 'medium' => 'referral'], 'campaign', 'partner'],
        ];
    }

    #[DataProvider('referrers')]
    public function test_source_rules(?string $host, array $utm, string $channel, string $name): void
    {
        $source = RealtimeTracker::classifySource($host, $utm, 'navigate', 'wp', Request::create('/'));

        $this->assertFalse($source['inherit']);
        $this->assertSame($channel, $source['channel']);
        $this->assertSame($name, $source['name']);
    }

    public function test_internal_handoff_empty_and_reloaded_referrers_inherit(): void
    {
        $request = Request::create('/');
        $base = parse_url(config('app.url'), PHP_URL_HOST);

        foreach ([$base, 'app.'.$base, 'jazz.'.$base, 'checkout.stripe.com', 'accounts.google.com', null] as $host) {
            $this->assertTrue(RealtimeTracker::classifySource($host, [], 'navigate', 'wp', $request)['inherit'], (string) $host);
        }

        $this->assertTrue(RealtimeTracker::classifySource('www.google.com', [], 'reload', 'wp', $request)['inherit']);
        $this->assertTrue(RealtimeTracker::classifySource('facebook.com', [], 'navigate', 'ap', $request)['inherit'], 'the OAuth dialog handing back');
        $this->assertFalse(RealtimeTracker::classifySource('facebook.com', [], 'navigate', 'gp', $request)['inherit']);
    }

    public function test_an_active_custom_domain_is_internal(): void
    {
        config(['app.hosted' => true]);
        $role = $this->createRole($this->createOwner());
        DB::table('roles')->where('id', $role->id)->update(['custom_domain_host' => 'events.jazzclub.example', 'custom_domain_status' => 'active']);

        $this->assertTrue(RealtimeTracker::isInternalHost('events.jazzclub.example', Request::create('/')));
        $this->assertFalse(RealtimeTracker::isInternalHost('jazzclub.example', Request::create('/')));
    }

    public function test_secret_route_parameters_never_reach_the_stored_path(): void
    {
        $request = Request::create('/ticket/view/12/s3cr3tV4lue');
        $route = (new Route('GET', 'ticket/view/{event_id}/{secret}', fn () => null))->bind($request);
        $request->setRouteResolver(fn () => $route);

        $this->assertSame('/ticket/view/12/{secret}', RealtimeTracker::path($request));

        $plain = Request::create('/pricing?utm_source=x&token=abc');
        $this->assertSame('/pricing', RealtimeTracker::path($plain));
    }

    public function test_the_visitor_key_ignores_ipv6_privacy_rotation_and_rotates_daily(): void
    {
        $a = Request::create('/', 'GET', server: ['REMOTE_ADDR' => '2001:db8:1:2:aaaa::1', 'HTTP_USER_AGENT' => 'UA', 'HTTP_ACCEPT_LANGUAGE' => 'en']);
        $b = Request::create('/', 'GET', server: ['REMOTE_ADDR' => '2001:db8:1:2:bbbb::9', 'HTTP_USER_AGENT' => 'UA', 'HTTP_ACCEPT_LANGUAGE' => 'en']);
        $other = Request::create('/', 'GET', server: ['REMOTE_ADDR' => '2001:db8:1:3::1', 'HTTP_USER_AGENT' => 'UA', 'HTTP_ACCEPT_LANGUAGE' => 'en']);

        $this->assertSame(RealtimeTracker::visitorKey($a), RealtimeTracker::visitorKey($b));
        $this->assertNotSame(RealtimeTracker::visitorKey($a), RealtimeTracker::visitorKey($other));
        $this->assertNotSame(RealtimeTracker::visitorKey($a), RealtimeTracker::visitorKey($a, now()->addDay()));
    }

    public function test_yesterdays_key_is_still_looked_up_in_the_first_half_hour_of_a_day(): void
    {
        $request = Request::create('/', 'GET', server: ['REMOTE_ADDR' => '203.0.113.7']);

        $this->travelTo(now()->utc()->startOfDay()->addMinutes(10));
        $this->assertCount(2, RealtimeTracker::visitorKeys($request));

        $this->travelTo(now()->utc()->startOfDay()->addHour());
        $this->assertCount(1, RealtimeTracker::visitorKeys($request));
    }

    public function test_client_text_loses_control_and_bidi_override_characters(): void
    {
        $this->assertSame('Pricing', RealtimeTracker::clean("\u{202E}Pri\u{0007}cing\u{2066} ", 150));
        $this->assertNull(RealtimeTracker::clean("\u{200F}", 150));
        $this->assertSame(5, mb_strlen(RealtimeTracker::clean('abcdefgh', 5)));
    }

    public function test_user_agent_families(): void
    {
        $edge = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36 Edg/126.0.0.0';
        $chrome = 'Mozilla/5.0 (Linux; Android 14; Pixel 8) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Mobile Safari/537.36';

        $this->assertSame(['Edge', 'Windows'], [UserAgentUtils::browser($edge), UserAgentUtils::os($edge)]);
        $this->assertSame(['Chrome', 'Android'], [UserAgentUtils::browser($chrome), UserAgentUtils::os($chrome)]);
        $this->assertSame('Other', UserAgentUtils::browser(null));
    }

    public function test_tracking_defaults_on_for_the_nexus_and_off_elsewhere_and_saves_off(): void
    {
        config(['app.is_nexus' => true]);
        $this->assertTrue(RealtimeTracker::enabled());

        config(['app.is_nexus' => false]);
        $this->assertFalse(RealtimeTracker::enabled());

        config(['app.is_nexus' => true]);
        Setting::set('realtime_enabled', '0');
        $this->assertFalse(RealtimeTracker::enabled(), 'an explicit off must win over the nexus default');
    }

    /**
     * Every layout render asks for the heartbeat interval, so a selfhost install that skipped
     * `migrate` must still serve pages. Simulated with a throwing query rather than by dropping the
     * table: DDL commits implicitly and would escape RefreshDatabase's transaction.
     */
    public function test_page_rendering_survives_a_missing_table(): void
    {
        DB::partialMock()->shouldReceive('table')->with('realtime_hits')
            ->andThrow(new \RuntimeException("Table 'realtime_hits' doesn't exist"));

        $this->assertSame(60, RealtimeTracker::heartbeatSeconds());
        RealtimeTracker::pruneIfDue();
    }

    public function test_prune_deletes_only_rows_past_retention(): void
    {
        $this->hit(['hit_key' => str_repeat('a', 32), 'last_seen_at' => RealtimeTracker::ts(now()->subMinutes(61))]);
        $this->hit(['hit_key' => str_repeat('b', 32), 'last_seen_at' => RealtimeTracker::ts(now()->subMinutes(59))]);

        $this->artisan('realtime:prune')->assertSuccessful();

        $this->assertSame([str_repeat('b', 32)], RealtimeHit::pluck('hit_key')->all());
    }

    public function test_a_stalled_prune_raises_an_admin_alert(): void
    {
        $this->hit(['hit_key' => str_repeat('a', 32), 'last_seen_at' => RealtimeTracker::ts(now()->subHours(3))]);

        $row = AdminAlertService::items()->firstWhere('type', 'realtime_prune_stalled');

        $this->assertNotNull($row);
        $this->assertSame('amber', $row['color']);
        $this->assertStringNotContainsString('admin_alert_', $row['title'], 'the alert needs its translation');
    }

    public function test_the_rate_limit_key_holds_no_raw_ip(): void
    {
        $request = Request::create('/api/realtime', 'POST', server: ['REMOTE_ADDR' => '203.0.113.77']);

        $limit = RateLimiter::limiter('realtime')($request);

        $this->assertStringNotContainsString('203.0.113.77', $limit->key);
        $this->assertSame(600, $limit->maxAttempts);
    }

    public function test_an_admin_log_in_hides_only_that_browsers_sign_in_page_views(): void
    {
        $server = ['REMOTE_ADDR' => '203.0.113.7', 'HTTP_USER_AGENT' => 'Mozilla/5.0 Test Browser', 'HTTP_ACCEPT_LANGUAGE' => 'en'];
        $key = RealtimeTracker::visitorKey(Request::create('/', 'GET', server: $server));

        $this->hit(['hit_key' => str_repeat('a', 32), 'visitor_key' => $key, 'surface' => 'auth', 'path' => '/login']);
        $this->hit(['hit_key' => str_repeat('b', 32), 'visitor_key' => $key, 'surface' => 'wp', 'path' => '/pricing']);
        $this->hit(['hit_key' => str_repeat('c', 32), 'visitor_key' => 'ffffffffffffffff', 'surface' => 'auth', 'path' => '/login']);

        $admin = $this->createOwner(true);
        $this->app->instance('request', Request::create('/login', 'POST', server: $server));
        event(new \Illuminate\Auth\Events\Login('web', $admin, false));

        $this->assertSame([str_repeat('a', 32)], RealtimeHit::where('is_admin', true)->pluck('hit_key')->all());
    }

    public function test_the_signature_cannot_be_re_split_across_fields(): void
    {
        $base = ['s' => 'wp', 'r' => '', 'e' => '', 'u' => '', 'a' => 0, 'd' => 0, 'em' => 0, 'hb' => 60, 't' => 1];

        // With the old implode('|'), these two signed the same string: "/a|b|x".
        $this->assertNotSame(
            RealtimeTracker::sign($base + ['p' => '/a|b', 'pt' => 'x']),
            RealtimeTracker::sign($base + ['p' => '/a', 'pt' => 'b|x'])
        );
    }

    public function test_an_invalid_utf8_path_is_scrubbed_so_the_beacon_script_still_parses(): void
    {
        $path = RealtimeTracker::path(Request::create('/caf%FF'));

        $this->assertTrue(mb_check_encoding($path, 'UTF-8'));
        $this->assertNotFalse(json_encode(['p' => $path]));
    }

    public function test_custom_domains_are_checked_against_one_cached_map(): void
    {
        config(['app.hosted' => true]);
        $role = $this->createRole($this->createOwner());
        DB::table('roles')->where('id', $role->id)->update(['custom_domain_host' => 'Events.JazzClub.example', 'custom_domain_status' => 'active']);

        $this->assertTrue(RealtimeTracker::isInternalHost('events.jazzclub.example', Request::create('/')));
        $this->assertFalse(RealtimeTracker::isInternalHost('random-host-'.uniqid().'.example', Request::create('/')));

        $this->assertTrue(Cache::has('realtime_custom_hosts'));
        $this->assertFalse(Cache::has('realtime_custom_host:events.jazzclub.example'), 'no key per host');
    }

    public function test_the_request_time_prune_stops_after_its_batch_budget(): void
    {
        $old = RealtimeTracker::ts(now()->subHours(2));
        foreach (array_chunk(range(1, 5001), 1000) as $chunk) {
            DB::table('realtime_hits')->insert(array_map(fn ($n) => [
                'hit_key' => str_pad(dechex($n), 32, '0', STR_PAD_LEFT),
                'consented' => false, 'surface' => 'wp', 'path' => '/', 'device' => 'desktop', 'hb' => 60,
                'started_at' => $old, 'last_seen_at' => $old, 'engaged_at' => $old,
            ], $chunk));
        }

        $this->assertSame(5000, RealtimeTracker::prune(maxBatches: 1));
        $this->assertSame(1, RealtimeHit::count());
    }

    private function hit(array $attributes): void
    {
        $now = RealtimeTracker::ts(now());

        DB::table('realtime_hits')->insert(array_merge([
            'hit_key' => str_repeat('0', 32),
            'visitor_key' => null,
            'consented' => true,
            'surface' => 'wp',
            'path' => '/',
            'device' => 'desktop',
            'hb' => 60,
            'started_at' => $now,
            'last_seen_at' => $now,
            'engaged_at' => $now,
        ], $attributes));
    }
}
