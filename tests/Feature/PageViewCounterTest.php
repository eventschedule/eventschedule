<?php

namespace Tests\Feature;

use App\Models\PageView;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * Regression cover for the daily-counter helpers on PageView.
 *
 * These exist because RoleController::recordSocialClick() built its own TTL with the
 * operands reversed - now()->endOfDay()->diffInSeconds(now()) is NEGATIVE under Carbon's
 * signed diffs, so Cache::add() rejected it, stored nothing, and the 10-clicks-per-IP-per-day
 * cap silently never applied. Anything that rate-limits per visitor per day must go through
 * incrementDailyCounter() rather than re-deriving the TTL.
 */
class PageViewCounterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
    }

    public function test_daily_counter_increments_and_persists(): void
    {
        $key = 'test_counter:'.uniqid();

        $this->assertSame(1, PageView::incrementDailyCounter($key));
        $this->assertSame(2, PageView::incrementDailyCounter($key));
        $this->assertSame(3, PageView::incrementDailyCounter($key));
    }

    public function test_daily_counter_actually_stores_the_key(): void
    {
        // The original bug: a non-positive TTL meant Cache::add stored nothing, so the
        // counter never accumulated across requests and the cap was unreachable.
        $key = 'test_counter:'.uniqid();

        PageView::incrementDailyCounter($key);

        $this->assertTrue(Cache::has($key), 'The counter key must survive the write, or the rate cap is dead.');
    }

    public function test_daily_counter_reaches_a_rate_cap(): void
    {
        $key = 'test_counter:'.uniqid();
        $capped = false;

        for ($i = 0; $i < 12; $i++) {
            if (PageView::incrementDailyCounter($key) > 10) {
                $capped = true;
                break;
            }
        }

        $this->assertTrue($capped, 'A 10-per-day cap must be reachable within 12 increments.');
    }

    public function test_visitor_hash_is_stable_per_visitor_and_distinct_across_visitors(): void
    {
        $a = PageView::visitorHash('203.0.113.10', 'Mozilla/5.0');

        $this->assertSame($a, PageView::visitorHash('203.0.113.10', 'Mozilla/5.0'));
        $this->assertNotSame($a, PageView::visitorHash('203.0.113.11', 'Mozilla/5.0'));
        $this->assertNotSame($a, PageView::visitorHash('203.0.113.10', 'Other/1.0'));

        // No resolvable IP means the caller cannot dedup, so it must not count.
        $this->assertNull(PageView::visitorHash(null, 'Mozilla/5.0'));
    }

    public function test_visitor_hash_does_not_leak_the_raw_ip(): void
    {
        $this->assertStringNotContainsString('203.0.113.10', PageView::visitorHash('203.0.113.10', 'Mozilla/5.0'));
    }

    public function test_client_ip_prefers_the_cloudflare_header(): void
    {
        $request = \Illuminate\Http\Request::create('/', 'GET');
        $request->headers->set('CF-Connecting-IP', '203.0.113.55');

        $this->assertSame('203.0.113.55', PageView::clientIp($request));

        $bare = \Illuminate\Http\Request::create('/', 'GET');
        $this->assertNotSame('203.0.113.55', PageView::clientIp($bare));
    }

    public function test_google_ad_crawlers_are_recognised_separately_from_generic_bots(): void
    {
        $mediapartners = 'Mozilla/5.0 (compatible; Mediapartners-Google/2.1; +http://www.google.com/bot.html)';
        $adsbot = 'Mozilla/5.0 (compatible; AdsBot-Google; +http://www.google.com/adsbot.html)';

        // Still bots for analytics purposes...
        $this->assertTrue(PageView::isBot($mediapartners));
        $this->assertTrue(PageView::isBot($adsbot));

        // ...but an ad slot must still render for them, or AdSense cannot read the page
        // to pick contextually relevant ads.
        $this->assertTrue(PageView::isGoogleAdsCrawler($mediapartners));
        $this->assertTrue(PageView::isGoogleAdsCrawler($adsbot));

        $this->assertFalse(PageView::isGoogleAdsCrawler('Mozilla/5.0 (compatible; Googlebot/2.1)'));
        $this->assertFalse(PageView::isGoogleAdsCrawler('Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)'));
        $this->assertFalse(PageView::isGoogleAdsCrawler(null));
    }
}
