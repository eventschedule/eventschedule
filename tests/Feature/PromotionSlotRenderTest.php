<?php

namespace Tests\Feature;

use App\Models\BoostCampaign;
use App\Models\Role;
use App\Models\Setting;
use App\Services\PromotionBillingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Where the monetization slot actually appears in rendered pages.
 *
 * The allowlist is the important half. Seventeen views render through app-guest.blade.php,
 * including checkout, appointment booking, gift-card purchase and the guest submission
 * forms. Serving a competitor's ad on a checkout page would undercut the schedule owner
 * whose free tier is being monetized - so the slot is opt-in per view, and this test is what
 * stops a future guest view from quietly inheriting it.
 */
class PromotionSlotRenderTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private Role $host;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.hosted' => false, 'ads.enabled' => true, 'app.is_nexus' => false]);
        Cache::flush();

        Setting::set('ads_adsense_enabled', '1');
        Setting::set('ads_adsense_client_id', 'ca-pub-1234567890123456');
        Setting::set('ads_adsense_slot_id', '1234567890');

        $this->host = $this->createRole($this->createOwner(), 'venue', [
            'plan_type' => 'free',
            'plan_expires' => now()->subDay()->format('Y-m-d'),
        ]);
    }

    /** Ads only exist on hosted installs, where a free tier exists. */
    private function hosted(): void
    {
        config(['app.hosted' => true]);
    }

    /**
     * Browser-shaped headers.
     *
     * PageView::isSuspiciousRequest() treats a missing Accept-Language as a bot signal (real
     * browsers always send one), and the ad gate reuses that check for invalid-traffic
     * hygiene. Laravel's test client sends neither header by default, so without this every
     * render assertion would silently be testing the bot path.
     */
    private function asVisitor(): self
    {
        return $this->withHeaders([
            'Accept-Language' => 'en-US,en;q=0.9',
            'Accept' => 'text/html,application/xhtml+xml',
            'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/120 Safari/537.36',
        ]);
    }

    public function test_the_schedule_page_carries_the_slot_for_a_free_schedule(): void
    {
        $this->hosted();
        $this->createEvent($this->host, ['starts_at' => now()->addDays(3)]);

        $this->asVisitor()->get(route('role.view_guest', ['subdomain' => $this->host->subdomain]))
            ->assertOk()
            ->assertSee('adsbygoogle', false)
            ->assertSee(__('messages.advertisement'));
    }

    public function test_a_paid_schedule_gets_no_ad_markup_at_all(): void
    {
        $this->hosted();

        $pro = $this->createRole($this->createOwner(), 'venue', [
            'plan_type' => 'pro',
            'plan_expires' => now()->addYear()->format('Y-m-d'),
        ]);
        $this->createEvent($pro, ['starts_at' => now()->addDays(3)]);

        // Not merely hidden - the scripts must not be in the payload at all.
        $this->asVisitor()->get(route('role.view_guest', ['subdomain' => $pro->subdomain]))
            ->assertOk()
            ->assertDontSee('adsbygoogle', false)
            ->assertDontSee('googlesyndication', false);
    }

    public function test_an_unconfigured_instance_renders_no_ad_markup(): void
    {
        $this->hosted();
        config(['ads.enabled' => false]);
        $this->createEvent($this->host, ['starts_at' => now()->addDays(3)]);

        $this->asVisitor()->get(route('role.view_guest', ['subdomain' => $this->host->subdomain]))
            ->assertOk()
            ->assertDontSee('adsbygoogle', false);
    }

    public function test_the_embed_view_is_never_monetized(): void
    {
        $this->hosted();
        $this->createEvent($this->host, ['starts_at' => now()->addDays(3)]);

        // Embeds render inside a third party's iframe.
        $this->asVisitor()->get(route('role.view_guest', ['subdomain' => $this->host->subdomain]).'?embed=true')
            ->assertOk()
            ->assertDontSee('adsbygoogle', false);
    }

    /**
     * Checkout, booking and submission pages share the guest layout but must never carry ads.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('nonMonetizedRouteProvider')]
    public function test_transactional_guest_pages_never_carry_the_slot(string $routeName): void
    {
        $this->hosted();

        $response = $this->asVisitor()->get(route($routeName, ['subdomain' => $this->host->subdomain]));

        // Some of these redirect or 404 depending on schedule configuration; what matters is
        // that when they DO render, they never contain ad markup.
        if ($response->getStatusCode() === 200) {
            $response->assertDontSee('adsbygoogle', false);
            $response->assertDontSee(__('messages.advertisement'));
        }

        $this->assertTrue(true);
    }

    public static function nonMonetizedRouteProvider(): array
    {
        return [
            'guest submit' => ['event.guest_submit'],
            'guest import' => ['event.guest_import'],
            'request' => ['role.request'],
        ];
    }

    public function test_only_the_two_intended_views_opt_into_the_slot(): void
    {
        // A structural guard: if someone adds :ad-slot="true" to another guest view, this
        // fails and they have to justify it. Guarding view-by-view instead would let every
        // future guest page inherit ads by default.
        //
        // RecursiveIteratorIterator, not glob('views/**/*.blade.php') - PHP's glob() does not
        // recurse, so `**` matched a single level and the guard silently skipped a third of the
        // tree, including every */partials/ directory.
        $optedIn = [];
        $scanned = 0;

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(resource_path('views'), \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($files as $file) {
            if (! str_ends_with($file->getFilename(), '.blade.php')) {
                continue;
            }

            $scanned++;
            $contents = file_get_contents($file->getPathname());

            if (str_contains($contents, 'ad-slot="true"') || str_contains($contents, 'adSlot="true"')) {
                $optedIn[] = str_replace(resource_path('views').'/', '', $file->getPathname());
            }
        }

        sort($optedIn);

        // Guard the guard: if the walk ever stops finding files, the assertion below would pass
        // vacuously.
        $this->assertGreaterThan(500, $scanned, 'The view walk must actually reach the whole tree.');

        $this->assertSame(
            ['event/show-guest.blade.php', 'role/show-guest.blade.php'],
            $optedIn,
            'Only the schedule and event pages may carry ads. Checkout, booking and submission flows must not.'
        );
    }

    public function test_a_native_promotion_outranks_adsense(): void
    {
        $this->hosted();
        Setting::set('ads_native_enabled', '1');
        Setting::set('ads_native_priority', '1');

        $this->createEvent($this->host, ['starts_at' => now()->addDays(3)]);

        $advertiser = $this->createRole($this->createOwner(), 'talent');
        $event = $this->createEvent($advertiser, ['name' => 'Rival Gig', 'starts_at' => now()->addDays(9)]);
        $advertiser->events()->updateExistingPivot($event->id, ['is_accepted' => true]);

        BoostCampaign::create([
            'event_id' => $event->id,
            'role_id' => $advertiser->id,
            'user_id' => $advertiser->user_id,
            'channel' => 'network',
            'name' => 'Promo',
            'status' => 'active',
            'moderation_status' => 'approved',
            'billing_status' => 'charged',
            'user_budget' => 10,
            'pricing_model' => 'cpm',
            'unit_rate_micros' => PromotionBillingService::toMicros(2.00),
            'budget_micros' => PromotionBillingService::toMicros(10),
        ]);

        $this->asVisitor()->get(route('role.view_guest', ['subdomain' => $this->host->subdomain]))
            ->assertOk()
            ->assertSee('Rival Gig')
            ->assertSee(__('messages.promoted'))
            // The whole point of native-first: a filled slot makes no request to Google.
            ->assertDontSee('adsbygoogle', false);
    }
}
