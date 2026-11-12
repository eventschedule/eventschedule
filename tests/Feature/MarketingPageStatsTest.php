<?php

namespace Tests\Feature;

use App\Models\MarketingPageDaily;
use App\Services\GrowthExportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/**
 * The per-page marketing counters (marketing_page_daily): the beacon in
 * partials/marketing-page-stats, its endpoint (MarketingPageStatController) and the growth
 * export's `marketing_pages` section.
 */
class MarketingPageStatsTest extends TestCase
{
    use RefreshDatabase;

    private const REAL_UA = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Safari/537.36';

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.is_nexus' => true]);
        Cache::flush();
    }

    public function test_each_kind_counts_once_per_visitor_per_day_per_page(): void
    {
        foreach (['view', 'view', 'reached_end', 'signup_click', 'signup_click'] as $kind) {
            $this->beacon(['page' => 'marketing.pricing', 'kind' => $kind, 'w' => 390])->assertNoContent();
        }

        $this->assertSame([1, 1, 1], $this->counts('marketing.pricing', 'phone'));

        // Another page is its own count for the same visitor.
        $this->beacon(['page' => 'marketing.index', 'kind' => 'view', 'w' => 390])->assertNoContent();
        $this->assertSame([1, 0, 0], $this->counts('marketing.index', 'phone'));

        // Another visitor on a wide screen is a desktop visitor.
        $this->beacon(['page' => 'marketing.pricing', 'kind' => 'view', 'w' => 1440], ip: '203.0.113.20')->assertNoContent();
        $this->assertSame([1, 0, 0], $this->counts('marketing.pricing', 'desktop'));
        $this->assertSame([1, 1, 1], $this->counts('marketing.pricing', 'phone'));

        // The next day the same visitor counts again.
        $this->travel(1)->days();
        $this->beacon(['page' => 'marketing.pricing', 'kind' => 'view', 'w' => 390])->assertNoContent();
        $this->assertSame(2, (int) MarketingPageDaily::where('page', 'marketing.pricing')->where('device', 'phone')->sum('visitors'));
    }

    public function test_the_device_threshold_is_a_640_pixel_viewport(): void
    {
        $this->assertSame('phone', MarketingPageDaily::deviceFor(639));
        $this->assertSame('desktop', MarketingPageDaily::deviceFor(640));
    }

    public function test_a_blog_page_is_counted_and_its_body_may_come_as_plain_text(): void
    {
        // sendBeacon with a string body sends it as text/plain; it is read from the raw content.
        $this->beacon(['page' => 'blog.index', 'kind' => 'view', 'w' => 1280], contentType: 'text/plain;charset=UTF-8')->assertNoContent();

        $this->assertSame([1, 0, 0], $this->counts('blog.index', 'desktop'));
    }

    public function test_an_unknown_page_kind_or_width_is_refused(): void
    {
        $bad = [
            ['page' => 'marketing.no-such-page', 'kind' => 'view', 'w' => 390],
            ['page' => '/pricing', 'kind' => 'view', 'w' => 390],
            ['page' => 'marketing.visit', 'kind' => 'view', 'w' => 390],
            ['page' => 'marketing_page.beacon', 'kind' => 'view', 'w' => 390],
            // blog.* also names the admin's blog screens, which are not pages a visitor reads.
            ['page' => 'blog.admin.index', 'kind' => 'view', 'w' => 390],
            ['page' => 'admin.users', 'kind' => 'view', 'w' => 390],
            ['page' => ['marketing.pricing'], 'kind' => 'view', 'w' => 390],
            ['page' => 'marketing.pricing', 'kind' => 'click', 'w' => 390],
            ['page' => 'marketing.pricing', 'kind' => 'visitors', 'w' => 390],
            ['page' => 'marketing.pricing', 'kind' => 'view'],
            ['page' => 'marketing.pricing', 'kind' => 'view', 'w' => 0],
            ['page' => 'marketing.pricing', 'kind' => 'view', 'w' => 'wide'],
        ];

        foreach ($bad as $payload) {
            $this->beacon($payload)->assertStatus(422);
        }

        $this->call('POST', '/api/marketing-page', [], [], [], $this->server('not json'), 'not json')->assertStatus(422);

        $this->assertSame(0, MarketingPageDaily::count());
    }

    public function test_bots_and_requests_that_are_not_a_browser_are_ignored(): void
    {
        $this->beacon(['page' => 'marketing.index', 'kind' => 'view', 'w' => 390], [
            'User-Agent' => 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)',
        ])->assertNoContent();

        $this->beacon(['page' => 'marketing.index', 'kind' => 'view', 'w' => 390], ['Accept-Language' => ''], ip: '203.0.113.30')->assertNoContent();

        $this->assertSame(0, MarketingPageDaily::count());
    }

    public function test_a_post_from_another_site_is_not_counted(): void
    {
        $this->beacon(['page' => 'marketing.index', 'kind' => 'view', 'w' => 390], ['Sec-Fetch-Site' => 'cross-site'])->assertNoContent();
        $this->assertSame(0, MarketingPageDaily::count());

        // Not vacuous: the same visitor from the page itself is counted.
        $this->beacon(['page' => 'marketing.index', 'kind' => 'view', 'w' => 390], ['Sec-Fetch-Site' => 'same-origin'])->assertNoContent();
        $this->assertSame([1, 0, 0], $this->counts('marketing.index', 'phone'));
    }

    public function test_the_endpoint_is_rate_limited(): void
    {
        // ThrottleRequests short-circuits while app.is_testing is true.
        config(['app.is_testing' => false]);

        for ($i = 0; $i < 60; $i++) {
            $this->beacon(['page' => 'marketing.no-such-page', 'kind' => 'view', 'w' => 390])->assertStatus(422);
        }

        $this->beacon(['page' => 'marketing.no-such-page', 'kind' => 'view', 'w' => 390])->assertStatus(429);
    }

    public function test_the_endpoint_answers_only_on_the_nexus(): void
    {
        config(['app.is_nexus' => false]);

        $this->beacon(['page' => 'marketing.index', 'kind' => 'view', 'w' => 390])->assertNotFound();

        $this->assertSame(0, MarketingPageDaily::count());
    }

    public function test_the_beacon_sets_no_cookie(): void
    {
        $response = $this->beacon(['page' => 'marketing.index', 'kind' => 'view', 'w' => 390]);

        $response->assertNoContent();
        $this->assertSame([], $response->headers->getCookies(), 'A beacon from an edge-cached page must not hand out a session.');
        $this->assertNull(Route::getRoutes()->getByName('marketing_page.beacon')?->getDomain(), 'It answers on every host, the blog\'s too.');
    }

    public function test_a_marketing_page_ships_the_beacon_with_its_route_name(): void
    {
        $page = $this->withHeaders([
            'User-Agent' => self::REAL_UA,
            'Accept-Language' => 'en-US,en;q=0.9',
            'Accept' => 'text/html',
        ])->get('/pricing');

        $page->assertOk();
        $page->assertSee('\\/api\\/marketing-page', false);
        $page->assertSee('"marketing.pricing"', false);
        $page->assertSee("'signup_click'", false);
        $page->assertSee("'reached_end'", false);
    }

    public function test_the_page_family_is_read_from_the_route(): void
    {
        $this->assertSame('home', MarketingPageDaily::groupOf('marketing.index'));
        $this->assertSame('pricing', MarketingPageDaily::groupOf('marketing.pricing'));
        $this->assertSame('for', MarketingPageDaily::groupOf('marketing.for_venues'));
        $this->assertSame('features', MarketingPageDaily::groupOf('marketing.ticketing'));
        $this->assertSame('docs', MarketingPageDaily::groupOf('marketing.docs.tickets'));
        $this->assertSame('blog', MarketingPageDaily::groupOf('blog.show'));
        $this->assertSame('other', MarketingPageDaily::groupOf('marketing.gone'));
    }

    public function test_the_export_carries_the_section(): void
    {
        MarketingPageDaily::insert([
            ['date' => now()->toDateString(), 'page' => 'marketing.pricing', 'device' => 'phone', 'visitors' => 10, 'reached_end' => 4, 'signup_clicks' => 1],
            ['date' => now()->subDays(3)->toDateString(), 'page' => 'marketing.pricing', 'device' => 'phone', 'visitors' => 5, 'reached_end' => 2, 'signup_clicks' => 1],
            ['date' => now()->toDateString(), 'page' => 'marketing.for_venues', 'device' => 'desktop', 'visitors' => 7, 'reached_end' => 3, 'signup_clicks' => 2],
            ['date' => now()->toDateString(), 'page' => 'marketing.for_bars', 'device' => 'desktop', 'visitors' => 1, 'reached_end' => 1, 'signup_clicks' => 0],
            // Outside the 90 days and the recent months alike.
            ['date' => now()->subYear()->toDateString(), 'page' => 'marketing.pricing', 'device' => 'phone', 'visitors' => 99, 'reached_end' => 99, 'signup_clicks' => 99],
        ]);

        $section = app(GrowthExportService::class)
            ->build(now()->subDays(30), now(), now()->subDays(60), now()->subDays(31))['marketing_pages'];

        $this->assertSame(['page', 'group', 'device', 'visitors', 'reached_end', 'signup_clicks'], $section['columns']);
        $this->assertSame(MarketingPageDaily::TRACKED_FROM, $section['tracked_from']);
        $this->assertSame([
            ['marketing.pricing', 'pricing', 'phone', 15, 6, 2],
            ['marketing.for_venues', 'for', 'desktop', 7, 3, 2],
            ['marketing.for_bars', 'for', 'desktop', 1, 1, 0],
        ], $section['rows']);

        $month = now()->format('Y-m');
        $monthly = collect($section['monthly']['rows'])->keyBy(fn ($r) => $r[1].'|'.$r[2]);
        $this->assertSame(['month', 'group', 'device', 'visitors', 'reached_end', 'signup_clicks'], $section['monthly']['columns']);
        $this->assertSame([$month, 'for', 'desktop', 8, 4, 2], $monthly['for|desktop']);
        $this->assertSame('pricing', $monthly['pricing|phone'][1]);
        $this->assertCount(2, $monthly);
    }

    /**
     * @return array{0: int, 1: int, 2: int} visitors, reached_end, signup_clicks
     */
    private function counts(string $page, string $device): array
    {
        $row = MarketingPageDaily::where('page', $page)->where('device', $device)->where('date', now()->toDateString())->first();

        return $row ? [(int) $row->visitors, (int) $row->reached_end, (int) $row->signup_clicks] : [0, 0, 0];
    }

    private function server(string $content, string $contentType = 'application/json', array $headers = [], string $ip = '203.0.113.10'): array
    {
        $server = [
            'HTTP_USER_AGENT' => self::REAL_UA,
            'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
            'HTTP_ACCEPT' => '*/*',
            'HTTP_CF_CONNECTING_IP' => $ip,
            'CONTENT_TYPE' => $contentType,
            'CONTENT_LENGTH' => (string) strlen($content),
        ];

        foreach ($headers as $name => $value) {
            $server['HTTP_'.strtoupper(str_replace('-', '_', $name))] = $value;
        }

        return $server;
    }

    /**
     * Shaped like the partial's navigator.sendBeacon(): a raw body, a wildcard Accept, no token.
     */
    private function beacon(array $payload, array $headers = [], string $ip = '203.0.113.10', string $contentType = 'application/json'): TestResponse
    {
        $content = json_encode($payload);

        return $this->call('POST', '/api/marketing-page', [], [], [], $this->server($content, $contentType, $headers, $ip), $content);
    }
}
