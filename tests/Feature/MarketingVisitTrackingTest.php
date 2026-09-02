<?php

namespace Tests\Feature;

use App\Http\Middleware\TrackMarketingVisit;
use App\Models\MarketingDailyStat;
use App\Models\PageView;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * Bot filtering for the onboarding funnel's "Visited site" counter
 * (marketing_daily_stats.visitors), written by TrackMarketingVisit.
 *
 * Most of this drives the middleware directly with a request bound to a fake marketing.*
 * route rather than hitting a live URL, so the counting rules can be exercised without
 * depending on which marketing routes happen to be registered.
 *
 * Since anonymous marketing HTML became edge-cacheable (docs/CACHING.md) there are two
 * entry points into the same counter and exactly one of them may fire per page view: the
 * layout's sendBeacon (POST marketing.visit -> TrackMarketingVisit::record) whenever the
 * page ships the beacon, and this middleware otherwise. The last group of tests below pins
 * that single-writer invariant end to end.
 */
class MarketingVisitTrackingTest extends TestCase
{
    use RefreshDatabase;

    private const REAL_UA = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Safari/537.36';

    protected function setUp(): void
    {
        parent::setUp();

        // Isolate the per-IP+UA daily dedup keys between tests.
        Cache::flush();
    }

    public function test_marketing_counter_filters_bots_and_dedupes_by_ip_ua(): void
    {
        config(['app.is_nexus' => true]);

        // 1. Real anonymous browser -> counted once.
        $this->track();
        $this->assertSame(1, $this->visitors());
        $this->assertSame(1, $this->pageViews());

        // 2. Same IP+UA again on a fresh session (a cookieless client): the raw view is
        //    recorded but the visitor is NOT recounted. This is the core anti-inflation fix.
        $this->track();
        $this->assertSame(1, $this->visitors());
        $this->assertSame(2, $this->pageViews());

        // 3. A different IP is a new unique visitor.
        $this->track(['HTTP_CF_CONNECTING_IP' => '203.0.113.20']);
        $this->assertSame(2, $this->visitors());
        $this->assertSame(3, $this->pageViews());

        // 4. A UA-blocklisted bot is not counted at all.
        $this->track([
            'HTTP_CF_CONNECTING_IP' => '203.0.113.30',
            'HTTP_USER_AGENT' => 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)',
        ]);
        $this->assertSame(2, $this->visitors());
        $this->assertSame(3, $this->pageViews());

        // 5. A UA-spoofing scraper that omits Accept-Language is dropped as suspicious.
        $this->track([
            'HTTP_CF_CONNECTING_IP' => '203.0.113.40',
            'HTTP_ACCEPT_LANGUAGE' => '',
        ]);
        $this->assertSame(2, $this->visitors());
        $this->assertSame(3, $this->pageViews());
    }

    /**
     * Docs and selfhost readers are counted as a SUBSET of the totals, so buyer-intent traffic
     * is (visitors - docs_visitors). Without this split a selfhoster reading /docs/installation
     * sits in the same denominator as someone on /pricing, and the headline visitor -> signup
     * rate cannot tell a conversion problem from a traffic-mix one.
     */
    public function test_docs_and_selfhost_traffic_is_counted_as_a_subset(): void
    {
        config(['app.is_nexus' => true]);

        // A product page: counted in the totals, not in the docs bucket.
        $this->track(routeName: 'marketing.pricing');
        $this->assertSame(1, $this->visitors());
        $this->assertSame(0, $this->docsVisitors());
        $this->assertSame(0, $this->docsPageViews());

        // A docs page from a different IP: counted in BOTH the totals and the docs bucket.
        $this->track(['HTTP_CF_CONNECTING_IP' => '203.0.113.20'], 'marketing.docs.installation');
        $this->assertSame(2, $this->visitors());
        $this->assertSame(1, $this->docsVisitors());
        $this->assertSame(1, $this->docsPageViews());

        // The selfhost landing page counts as docs traffic too.
        $this->track(['HTTP_CF_CONNECTING_IP' => '203.0.113.30'], 'marketing.selfhost');
        $this->assertSame(3, $this->visitors());
        $this->assertSame(2, $this->docsVisitors());

        // Buyer-intent traffic is what is left.
        $this->assertSame(1, $this->visitors() - $this->docsVisitors());

        // The docs bucket dedupes on its own key, so a second docs view from an already-counted
        // IP adds a page view but not a visitor - matching how the totals behave.
        $this->track(['HTTP_CF_CONNECTING_IP' => '203.0.113.20'], 'marketing.docs.installation');
        $this->assertSame(2, $this->docsVisitors());
        $this->assertSame(3, $this->docsPageViews());
        $this->assertLessThanOrEqual($this->visitors(), $this->docsVisitors());
    }

    /**
     * /pricing is counted on its own, as a SUBSET of the totals like the docs buckets. Before
     * this it folded into page_views with every other marketing page, so "did a price change
     * move interest?" had no numerator at all - which is how a price rise ran for 25 days with
     * nothing to read but ~1 conversion/month at the very bottom of the funnel.
     */
    public function test_pricing_traffic_is_counted_as_its_own_subset(): void
    {
        config(['app.is_nexus' => true]);

        // A non-pricing product page: in the totals, not in the pricing bucket.
        $this->track(routeName: 'marketing.index');
        $this->assertSame(1, $this->visitors());
        $this->assertSame(0, $this->pricingVisitors());
        $this->assertSame(0, $this->pricingViews());

        // /pricing from a different IP: counted in BOTH.
        $this->track(['HTTP_CF_CONNECTING_IP' => '203.0.113.20'], 'marketing.pricing');
        $this->assertSame(2, $this->visitors());
        $this->assertSame(1, $this->pricingVisitors());
        $this->assertSame(1, $this->pricingViews());

        // Its own dedup key: a second /pricing view from an already-counted IP adds a view but
        // not a visitor, matching how the totals and the docs bucket behave.
        $this->track(['HTTP_CF_CONNECTING_IP' => '203.0.113.20'], 'marketing.pricing');
        $this->assertSame(1, $this->pricingVisitors());
        $this->assertSame(2, $this->pricingViews());

        // A subset can never exceed the total it is drawn from.
        $this->assertLessThanOrEqual($this->visitors(), $this->pricingVisitors());
        $this->assertLessThanOrEqual($this->pageViews(), $this->pricingViews());

        // Pricing and docs are independent buckets, not one shared counter: a docs reader must
        // not land in the pricing bucket, or buyer-intent traffic reads as interest in the price.
        $this->track(['HTTP_CF_CONNECTING_IP' => '203.0.113.30'], 'marketing.docs.installation');
        $this->assertSame(1, $this->pricingVisitors());
        $this->assertSame(1, $this->docsVisitors());
    }

    /**
     * The blind spot this counter still has, pinned so it is a known limit rather than a
     * surprise: TrackMarketingVisit short-circuits on auth()->check(), so a signed-in owner
     * weighing an upgrade is never counted. users.subscribe_form_viewed_at is what covers them.
     */
    public function test_pricing_views_do_not_count_signed_in_users(): void
    {
        config(['app.is_nexus' => true]);

        $user = \App\Models\User::factory()->create();
        $this->actingAs($user);

        $this->track(routeName: 'marketing.pricing');

        $this->assertNull($this->stat());
    }

    public function test_marketing_counter_ignores_guest_portal_routes(): void
    {
        config(['app.is_nexus' => true]);

        // A guest-portal (GP) page is not a marketing.* route -> never counted.
        $this->track(overrides: [], routeName: 'viewGuest');

        $this->assertNull($this->stat());
    }

    public function test_is_first_daily_visit_dedupes_by_ip_and_ua(): void
    {
        $this->assertTrue(PageView::isFirstDailyVisit('b', '203.0.113.10', self::REAL_UA));
        $this->assertFalse(PageView::isFirstDailyVisit('b', '203.0.113.10', self::REAL_UA)); // repeat
        $this->assertTrue(PageView::isFirstDailyVisit('b', '203.0.113.11', self::REAL_UA));  // different IP
        $this->assertTrue(PageView::isFirstDailyVisit('b', '203.0.113.10', self::REAL_UA.' x')); // different UA
        $this->assertTrue(PageView::isFirstDailyVisit('other', '203.0.113.10', self::REAL_UA)); // different bucket
        $this->assertFalse(PageView::isFirstDailyVisit('b', null, self::REAL_UA)); // no resolvable IP
    }

    public function test_seconds_until_end_of_day_is_positive(): void
    {
        // Guards the Carbon signed-diff regression: a negative TTL makes Cache::add/put reject
        // the write, which broke both isFirstDailyVisit() (every call looked "first") and
        // hasExceededViewLimit()'s daily reset (the Redis key was created with no expiry).
        $method = new \ReflectionMethod(PageView::class, 'secondsUntilEndOfDay');
        $method->setAccessible(true);
        $seconds = $method->invoke(null);

        $this->assertIsInt($seconds);
        $this->assertGreaterThan(0, $seconds);
        $this->assertLessThanOrEqual(86400, $seconds);
    }

    /**
     * The single-writer invariant, from the middleware's side: a page that shipped the
     * beacon must NOT also be counted at the origin, or every dynamically served marketing
     * page (a signed-out visitor whose request carried a session cookie, a ?lang= request,
     * a CDN miss that revalidates) would count twice.
     */
    public function test_the_middleware_stands_down_when_the_page_ships_the_beacon(): void
    {
        config(['app.is_nexus' => true]);

        $this->track(beacon: true);

        $this->assertNull($this->stat());

        // ...and still counts a marketing response that carries no beacon (the docs
        // search-index JSON, or any future marketing.* route that skips the layout).
        $this->track();

        $this->assertSame(1, $this->pageViews());
    }

    public function test_the_beacon_counts_a_visit(): void
    {
        $this->beacon('marketing.pricing')->assertNoContent();

        $this->assertSame(1, $this->pageViews());
        $this->assertSame(1, $this->visitors());
        $this->assertSame(1, $this->pricingViews());
        $this->assertSame(1, $this->pricingVisitors());
        $this->assertSame(0, $this->docsPageViews());

        // Same IP+UA again: the raw view counts, the visitor does not - the same dedup the
        // middleware applies, because it is literally the same code.
        $this->beacon('marketing.pricing')->assertNoContent();

        $this->assertSame(2, $this->pageViews());
        $this->assertSame(1, $this->visitors());
    }

    /**
     * A beacon cannot send a document Accept header: navigator.sendBeacon takes no headers at
     * all and both it and fetch() default to a wildcard. That one check is relaxed for the
     * beacon (PageView::isSuspiciousRequest's second argument) and nothing else is, so this
     * pins the relaxation as deliberate rather than an oversight.
     */
    public function test_the_beacon_is_not_dropped_for_a_wildcard_accept_header(): void
    {
        $this->beacon('marketing.index', ['Accept' => '*/*'])->assertNoContent();

        $this->assertSame(1, $this->pageViews());
    }

    public function test_the_beacon_rejects_bots_and_suspicious_requests(): void
    {
        // A UA-blocklisted bot that somehow runs JavaScript.
        $this->beacon('marketing.index', [
            'User-Agent' => 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)',
        ])->assertNoContent();

        $this->assertNull($this->stat());

        // No Accept-Language: a real browser always sends one, on a beacon too.
        $this->beacon('marketing.index', ['Accept-Language' => ''], ip: '203.0.113.20')->assertNoContent();

        $this->assertNull($this->stat());
    }

    public function test_the_beacon_rejects_route_names_it_does_not_recognise(): void
    {
        foreach (['', 'marketing.no-such-page', 'viewGuest', 'marketing.visit', 'admin.users'] as $routeName) {
            $this->beacon($routeName)->assertStatus(422);
        }

        // A hand-built payload is not a way to write to a counter for a page that does not
        // exist, or to a POST-only route - marketing.visit itself included.
        $this->assertNull($this->stat());
    }

    public function test_the_beacon_is_rate_limited(): void
    {
        // ThrottleRequests short-circuits while app.is_testing is true, so the limit has to
        // be switched on to be observed at all.
        config(['app.is_testing' => false]);

        for ($i = 0; $i < 120; $i++) {
            $this->beacon('marketing.no-such-page')->assertStatus(422);
        }

        $this->beacon('marketing.no-such-page')->assertStatus(429);
    }

    /**
     * The single-writer invariant end to end: fetching a real marketing page counts nothing
     * at the origin, and the beacon that page ships counts it exactly once.
     */
    public function test_a_real_page_view_is_counted_once_by_the_beacon_and_not_by_the_origin(): void
    {
        $page = $this->withHeaders($this->browserHeaders())->get('/pricing');

        $page->assertOk();
        $page->assertSee('sendBeacon', false);
        $this->assertNull($this->stat(), 'The origin must not count a page that ships the beacon.');

        $this->beacon('marketing.pricing')->assertNoContent();

        $this->assertSame(1, $this->pageViews());
        $this->assertSame(1, $this->pricingViews());
    }

    /**
     * A request shaped exactly like the layout's navigator.sendBeacon(): a JSON Blob body, a
     * wildcard Accept the browser will not let the page override, and no CSRF token.
     *
     * Built with call() rather than postJson() so the body really is a JSON document - a form
     * POST with a JSON content type parses to nothing, which silently 422s every case and
     * makes the whole group pass for the wrong reason.
     */
    private function beacon(string $routeName, array $headers = [], string $ip = '203.0.113.10'): \Illuminate\Testing\TestResponse
    {
        $content = json_encode(['route' => $routeName]);

        $server = [
            'HTTP_USER_AGENT' => self::REAL_UA,
            'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
            'HTTP_ACCEPT' => '*/*',
            'HTTP_CF_CONNECTING_IP' => $ip,
            'CONTENT_TYPE' => 'application/json',
            'CONTENT_LENGTH' => (string) strlen($content),
        ];

        foreach ($headers as $name => $value) {
            $server['HTTP_'.strtoupper(str_replace('-', '_', $name))] = $value;
        }

        return $this->call('POST', '/marketing/visit', [], [], [], $server, $content);
    }

    /**
     * @return array<string, string>
     */
    private function browserHeaders(): array
    {
        return [
            'User-Agent' => self::REAL_UA,
            'Accept-Language' => 'en-US,en;q=0.9',
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'CF-Connecting-IP' => '203.0.113.10',
        ];
    }

    private function track(array $overrides = [], string $routeName = 'marketing.index', bool $beacon = false): void
    {
        $server = array_replace([
            'HTTP_USER_AGENT' => self::REAL_UA,
            'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
            'HTTP_CF_CONNECTING_IP' => '203.0.113.10',
        ], $overrides);

        $request = Request::create('/', 'GET', server: $server);
        $route = (new Route('GET', '/', []))->name($routeName);
        $request->setRouteResolver(fn () => $route);

        if ($beacon) {
            $request->attributes->set(TrackMarketingVisit::BEACON_ATTRIBUTE, true);
        }

        (new TrackMarketingVisit)->handle($request, fn () => new Response('OK'));
    }

    private function stat(): ?MarketingDailyStat
    {
        return MarketingDailyStat::where('date', now()->toDateString())->first();
    }

    private function visitors(): int
    {
        return (int) ($this->stat()?->visitors ?? 0);
    }

    private function pageViews(): int
    {
        return (int) ($this->stat()?->page_views ?? 0);
    }

    private function docsVisitors(): int
    {
        return (int) ($this->stat()?->docs_visitors ?? 0);
    }

    private function docsPageViews(): int
    {
        return (int) ($this->stat()?->docs_page_views ?? 0);
    }

    private function pricingVisitors(): int
    {
        return (int) ($this->stat()?->pricing_visitors ?? 0);
    }

    private function pricingViews(): int
    {
        return (int) ($this->stat()?->pricing_views ?? 0);
    }
}
