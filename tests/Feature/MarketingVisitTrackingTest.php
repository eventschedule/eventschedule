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
}
