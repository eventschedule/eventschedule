<?php

namespace Tests\Feature;

use App\Models\MarketingCopyHost;
use App\Models\RealtimeHit;
use App\Models\User;
use App\Services\AdminAlertService;
use App\Services\MarketingCopyWatch;
use App\Utils\RealtimeTracker;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/**
 * Sites that may be serving a copy of the marketing pages (GitHub #136), found from the sites that
 * send visitors here and from federated installs, and looked at for what only those pages carry.
 * It finds and never acts: /admin/copies records what the operator decided. Nothing here reaches
 * the network: MarketingCopyWatch::$fetchUsing stands in for the SSRF-guarded fetch.
 */
class MarketingCopyWatchTest extends TestCase
{
    use RefreshDatabase;

    private const UA = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 14_5) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.5 Safari/605.1.15';

    /** @var array<string, ?string> URL => body a fake site answers with */
    private array $sites = [];

    protected function setUp(): void
    {
        parent::setUp();

        Http::preventStrayRequests();
        MarketingCopyWatch::$fetchUsing = fn (string $url) => $this->sites[$url] ?? null;
    }

    protected function tearDown(): void
    {
        MarketingCopyWatch::$fetchUsing = null;

        parent::tearDown();
    }

    public function test_the_real_homepage_and_pricing_page_are_recognised(): void
    {
        foreach (['/', '/pricing'] as $path) {
            $html = $this->get($path)->assertOk()->getContent();

            // The suite renders without the Vite build, so the asset marker cannot show here.
            $markers = MarketingCopyWatch::markers($html);
            $this->assertGreaterThanOrEqual(2, count($markers), "{$path} matched only: ".implode(', ', $markers));
        }
    }

    public function test_pages_that_are_not_the_marketing_site_are_not(): void
    {
        // An app page, and a page that writes about us and carries the licence's attribution.
        $this->assertLessThan(2, count(MarketingCopyWatch::markers($this->get('/login')->getContent())));
        $this->assertSame([], MarketingCopyWatch::markers(
            '<html><head><title>Best event tools</title></head><body><h1>Event Schedule review</h1>'
            .'<p>Event Schedule is free. Powered by <a href="https://www.eventschedule.com">Event Schedule</a></p></body></html>'
        ));
    }

    public function test_the_built_assets_count(): void
    {
        $this->assertContains('assets', MarketingCopyWatch::markers('<link rel="stylesheet" href="/build/assets/marketing-gUuWdZ3a.css">'));
        $this->assertContains('assets', MarketingCopyWatch::markers('<script src="https://x.example/build/assets/marketing-app-CVO-eJN6.css"></script>'));
    }

    /** Editing the homepage or the pricing page must not quietly switch a phrase off. */
    public function test_every_phrase_is_still_on_the_pages_it_comes_from(): void
    {
        $pages = $this->get('/')->getContent().$this->get('/pricing')->getContent();
        $text = html_entity_decode(strip_tags($pages), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        foreach (MarketingCopyWatch::PHRASES as $phrase) {
            $this->assertStringContainsString($phrase, $text, "\"{$phrase}\" is no longer on the homepage or the pricing page");
        }
    }

    public function test_a_referring_host_is_kept_and_counted(): void
    {
        MarketingCopyWatch::sawReferrer('www.copy.example.net');
        MarketingCopyWatch::sawReferrer('copy.example.net');

        $row = MarketingCopyHost::sole();
        $this->assertSame('copy.example.net', $row->host);
        $this->assertSame(2, $row->visits);
        $this->assertSame('referrer', $row->source);
    }

    public function test_known_and_own_hosts_are_not_kept(): void
    {
        foreach (['www.google.com', 'l.facebook.com', 'chatgpt.com', 'mail.google.com', parse_url(config('app.url'), PHP_URL_HOST), 'localhost', '', null] as $host) {
            MarketingCopyWatch::sawReferrer($host);
        }

        $this->assertSame(0, MarketingCopyHost::count());
    }

    public function test_nothing_is_kept_off_the_nexus(): void
    {
        config(['app.is_nexus' => false]);

        MarketingCopyWatch::sawReferrer('copy.example.net');

        $this->assertSame(0, MarketingCopyHost::count());
        $this->assertSame(0, MarketingCopyWatch::count());
    }

    /** The beacon hands over the host of a visit that starts on a marketing page, and only that. */
    public function test_the_realtime_beacon_records_the_referring_site(): void
    {
        $this->beacon(['t' => 'pv', 'm' => 'c', 'k' => $this->key(1), 'c' => $this->context(), 'r' => 'copy.example.net'])->assertNoContent();
        $this->beacon(['t' => 'pv', 'm' => 'c', 'k' => $this->key(2), 'c' => $this->context(), 'r' => 'www.google.com'])->assertNoContent();
        // A schedule's page linked from a venue's own site is the product working, not a copy.
        $this->beacon(['t' => 'pv', 'm' => 'c', 'k' => $this->key(3), 'c' => $this->context(['s' => 'gp']), 'r' => 'venue.example.org'])->assertNoContent();

        $this->assertSame(3, RealtimeHit::count(), 'the beacon stored the page views');
        $this->assertSame(['copy.example.net'], MarketingCopyHost::pluck('host')->all());
    }

    public function test_a_copy_is_found_and_a_site_that_is_not_is_cleared(): void
    {
        $copy = $this->host('copy.example.net', visits: 3);
        $clear = $this->host('fan.example.org', visits: 5);
        $once = $this->host('typo.example.com', visits: 1);

        $this->sites['https://copy.example.net/'] = $this->get('/')->getContent();
        $this->sites['https://fan.example.org/'] = '<html><title>A fan blog</title><p>We use Event Schedule.</p></html>';

        $result = MarketingCopyWatch::run();

        $this->assertSame(2, $result['checked'], 'a host that sent one visit waits for a second');
        $this->assertSame('copy', $copy->refresh()->status);
        $this->assertNotNull($copy->copy_found_at);
        $this->assertSame('https://copy.example.net/', $copy->checked_url);
        $this->assertGreaterThanOrEqual(2, count($copy->markers));
        $this->assertSame('clear', $clear->refresh()->status);
        $this->assertSame('unchecked', $once->refresh()->status);
        $this->assertSame(1, MarketingCopyWatch::count());
    }

    public function test_a_site_that_does_not_answer_is_given_up_on(): void
    {
        $row = $this->host('down.example.net', visits: 4);

        for ($i = 0; $i < MarketingCopyWatch::MAX_FAILURES; $i++) {
            $this->travel(MarketingCopyWatch::RECHECK_DAYS + 1)->days();
            MarketingCopyWatch::run();
        }

        $this->assertSame('unreachable', $row->refresh()->status);
        $this->assertSame(MarketingCopyWatch::MAX_FAILURES, $row->check_failures);

        $this->travel(MarketingCopyWatch::RECHECK_DAYS + 1)->days();
        $this->assertSame(0, MarketingCopyWatch::run()['checked']);
    }

    public function test_a_contacted_copy_leaves_the_list_once_it_is_taken_down(): void
    {
        $row = $this->host('copy.example.net', visits: 3);
        $row->forceFill(['status' => 'contacted', 'checked_at' => now()->subDays(8), 'copy_found_at' => now()->subDays(8)])->save();

        $this->sites['https://copy.example.net/'] = $this->get('/')->getContent();
        MarketingCopyWatch::run();
        $this->assertSame('contacted', $row->refresh()->status, 'still up');

        unset($this->sites['https://copy.example.net/']);
        $this->sites['https://copy.example.net/'] = '<html><title>Our events</title></html>';
        $this->travel(MarketingCopyWatch::RECHECK_DAYS + 1)->days();
        MarketingCopyWatch::run();
        $this->assertSame('clear', $row->refresh()->status, 'taken down');
    }

    public function test_a_dismissed_site_is_never_looked_at_again(): void
    {
        $row = $this->host('mirror.example.net', visits: 9);
        $row->forceFill(['status' => 'dismissed'])->save();

        $this->assertSame(0, MarketingCopyWatch::run()['checked']);
    }

    public function test_a_quiet_site_that_was_never_a_copy_is_forgotten(): void
    {
        $this->host('old.example.net', visits: 1)->forceFill(['last_seen_at' => now()->subDays(MarketingCopyWatch::FORGET_DAYS + 1)])->save();
        $kept = $this->host('copy.example.net', visits: 3);
        $kept->forceFill(['status' => 'copy', 'checked_at' => now(), 'last_seen_at' => now()->subDays(MarketingCopyWatch::FORGET_DAYS + 1)])->save();

        MarketingCopyWatch::run();

        $this->assertSame(['copy.example.net'], MarketingCopyHost::pluck('host')->all());
    }

    public function test_the_page_lists_copies_and_records_decisions(): void
    {
        $admin = $this->admin();
        $copy = $this->host('copy.example.net', visits: 3);
        $copy->forceFill(['status' => 'copy', 'markers' => ['layout', 'phrases'], 'page_title' => 'Event Schedule - copy', 'copy_found_at' => now()])->save();
        $this->host('watched.example.org', visits: 1);

        $this->asAdmin($admin)->get(route('admin.copies'))
            ->assertOk()
            ->assertSee('copy.example.net')
            ->assertSee('watched.example.org')
            ->assertSee(__('messages.copies_marker_layout'));

        AdminAlertService::flush();
        $alerts = AdminAlertService::items()->pluck('type');
        $this->assertContains('marketing_copies', $alerts);

        $this->asAdmin($admin)->post(route('admin.copies.decide', ['hash' => UrlUtils::encodeId($copy->id)]), ['action' => 'contacted'])
            ->assertRedirect();
        $this->assertSame('contacted', $copy->refresh()->status);
        $this->assertSame($admin->id, $copy->decided_by);
        $this->assertSame(0, MarketingCopyWatch::count(), 'the alert drains once the operator has acted');

        $this->asAdmin($admin)->post(route('admin.copies.decide', ['hash' => UrlUtils::encodeId($copy->id)]), ['action' => 'nonsense'])
            ->assertSessionHasErrors('action');
    }

    public function test_check_now_looks_at_the_site_at_once(): void
    {
        $row = $this->host('copy.example.net', visits: 1);
        $this->sites['https://copy.example.net/'] = $this->get('/')->getContent();

        $this->asAdmin($this->admin())->post(route('admin.copies.check', ['hash' => UrlUtils::encodeId($row->id)]))
            ->assertRedirect(route('admin.copies').'#copies')
            ->assertSessionHas('message', __('messages.copies_checked_copy', ['host' => 'copy.example.net']));

        $this->assertSame('copy', $row->refresh()->status);
    }

    public function test_only_a_platform_admin_on_the_nexus_reaches_the_page(): void
    {
        $member = User::factory()->create(['email_verified_at' => now()]);
        // The admin group turns a non-admin away before the controller is reached.
        $this->actingAs($member)->get(route('admin.copies'))->assertRedirect()->assertDontSee('copies_lead');

        config(['app.is_nexus' => false]);
        $this->asAdmin($this->admin())->get(route('admin.copies'))->assertNotFound();
    }

    private function host(string $host, int $visits): MarketingCopyHost
    {
        return MarketingCopyHost::create([
            'host' => $host,
            'source' => 'referrer',
            'visits' => $visits,
            'first_seen_at' => now(),
            'last_seen_at' => now(),
        ])->refresh();
    }

    private function admin(): User
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->forceFill(['is_admin' => true])->save();

        return $admin;
    }

    private function asAdmin(User $admin): static
    {
        return $this->withSession(['admin_password_confirmed_at' => now()->timestamp, 'admin_password_confirmed_first_at' => now()->timestamp])
            ->actingAs($admin);
    }

    private function beacon(array $message): TestResponse
    {
        return $this->call('POST', '/api/realtime', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_USER_AGENT' => self::UA,
            'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.9',
            'REMOTE_ADDR' => '203.0.113.7',
        ], json_encode($message));
    }

    private function context(array $overrides = []): array
    {
        $context = array_merge([
            's' => 'wp', 'p' => '/pricing', 'pt' => '/pricing', 'r' => '', 'e' => '', 'u' => '', 'a' => 0, 'd' => 0, 'em' => 0,
            'hb' => 60, 't' => RealtimeTracker::now()->timestamp,
        ], $overrides);

        $context['sig'] = RealtimeTracker::sign($context);

        return $context;
    }

    private function key(int $n): string
    {
        return str_pad(dechex($n), 32, '0', STR_PAD_LEFT);
    }
}
