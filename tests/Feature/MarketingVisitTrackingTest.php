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
 * The marketing routes are only registered when app.is_nexus is true at boot and the
 * suite runs non-nexus, so we drive the middleware directly with a request bound to a
 * fake marketing.* route instead of hitting a live URL.
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

    private function track(array $overrides = [], string $routeName = 'marketing.index'): void
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
