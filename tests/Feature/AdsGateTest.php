<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Services\AdsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The monetization gate: who sees ads, and - much more importantly - who does not.
 *
 * Every guard here exists for a concrete reason (AdSense policy, a downgrade edge case, or
 * not undermining the schedule owner), so each gets its own test rather than being folded
 * into one happy-path assertion.
 */
class AdsGateTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Plan gates only exist on hosted installs; selfhost resolves to enterprise.
        config(['app.hosted' => true, 'app.is_nexus' => false, 'ads.enabled' => true]);

        Setting::set('ads_adsense_enabled', '1');
        Setting::set('ads_adsense_client_id', 'ca-pub-1234567890123456');
        Setting::set('ads_adsense_slot_id', '1234567890');
    }

    private function freeRole()
    {
        return $this->createRole($this->createOwner(), 'venue', [
            'plan_type' => 'free',
            'plan_expires' => now()->subDay()->format('Y-m-d'),
        ]);
    }

    /** A request that looks like a real browser, so the bot heuristics do not fire. */
    private function visitorRequest(array $query = []): Request
    {
        $request = Request::create('https://sub.eventschedule.test/'.($query ? '?'.http_build_query($query) : ''), 'GET');
        $request->headers->set('Accept-Language', 'en-US,en;q=0.9');
        $request->headers->set('Accept', 'text/html,application/xhtml+xml');
        $request->headers->set('User-Agent', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/120 Safari/537.36');

        return $request;
    }

    public function test_a_free_schedule_is_monetized(): void
    {
        $this->assertTrue($this->freeRole()->showAds());
        $this->assertTrue(app(AdsService::class)->isEligible($this->freeRole(), $this->visitorRequest()));
    }

    public function test_paid_tiers_are_never_monetized(): void
    {
        $owner = $this->createOwner();

        foreach (['pro', 'enterprise'] as $tier) {
            $role = $this->createRole($owner, 'venue', [
                'plan_type' => $tier,
                'plan_expires' => now()->addYear()->format('Y-m-d'),
            ]);

            $this->assertFalse($role->showAds(), "A {$tier} schedule must not show ads - that is the whole upgrade incentive.");
        }
    }

    public function test_selfhost_is_never_monetized(): void
    {
        // actualPlanTier() returns 'enterprise' off hosted, so a single-tenant install has
        // no free tier and therefore no inventory.
        config(['app.hosted' => false]);

        $this->assertFalse($this->freeRole()->showAds());
    }

    public function test_the_nexus_stays_ad_free(): void
    {
        config(['app.is_nexus' => true]);

        $this->assertFalse($this->freeRole()->showAds());
    }

    public function test_nothing_is_monetized_without_the_env_gate(): void
    {
        // The database toggles are all on; only ADS_ENABLED is not.
        config(['ads.enabled' => false]);

        $this->assertFalse($this->freeRole()->showAds());
        $this->assertFalse(AdsService::adSenseConfigured());
    }

    public function test_embeds_and_graphic_captures_are_excluded(): void
    {
        $role = $this->freeRole();
        $service = app(AdsService::class);

        // An embed renders inside a third party's iframe; ?graphic=1 renders a share image.
        $this->assertFalse($service->isEligible($role, $this->visitorRequest(['embed' => 'true'])));
        $this->assertFalse($service->isEligible($role, $this->visitorRequest(['graphic' => '1'])));
    }

    public function test_password_gated_pages_are_excluded(): void
    {
        $this->assertFalse(
            app(AdsService::class)->isEligible($this->freeRole(), $this->visitorRequest(), passwordGate: true)
        );
    }

    public function test_a_lapsed_enterprise_schedule_on_its_own_domain_is_excluded(): void
    {
        // RoleController only blocks CHANGING custom_domain, so a schedule that downgrades
        // keeps the domain it already had. Serving AdSense on a domain the operator does
        // not own would breach AdSense policy.
        $role = $this->freeRole();

        $request = $this->visitorRequest();
        $request->attributes->set('custom_domain_host', 'events.acme.test');

        $this->assertFalse(app(AdsService::class)->isEligible($role, $request));
    }

    public function test_members_do_not_see_their_own_ads(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', [
            'plan_type' => 'free',
            'plan_expires' => now()->subDay()->format('Y-m-d'),
        ]);

        $this->actingAs($owner);

        // AdSense policy prohibits publishers viewing their own ads, and owners reload
        // their own guest page constantly.
        $this->assertFalse(app(AdsService::class)->isEligible($role, $this->visitorRequest()));

        // ...but they can deliberately preview what visitors see.
        $this->assertTrue(app(AdsService::class)->isEligible($role, $this->visitorRequest(['preview_ads' => '1'])));
    }

    public function test_bots_are_excluded_but_google_ad_crawlers_are_not(): void
    {
        $role = $this->freeRole();
        $service = app(AdsService::class);

        $bot = $this->visitorRequest();
        $bot->headers->set('User-Agent', 'Mozilla/5.0 (compatible; SemrushBot/7~bl)');
        $this->assertFalse($service->isEligible($role, $bot));

        // Mediapartners-Google is how AdSense reads the page to pick contextually relevant
        // ads. Hiding the unit from it costs real revenue with no visible symptom.
        $adsCrawler = $this->visitorRequest();
        $adsCrawler->headers->set('User-Agent', 'Mozilla/5.0 (compatible; Mediapartners-Google/2.1; +http://www.google.com/bot.html)');
        $this->assertTrue($service->isEligible($role, $adsCrawler));
    }

    public function test_opting_out_suppresses_adsense_not_just_native_promotions(): void
    {
        // promotions_opt_out was only read in PromotionService, so a schedule that switched it
        // off still carried Google ads - and the toggle is only rendered when the network is on,
        // so an AdSense-only instance offered no way to decline at all.
        $role = $this->freeRole();

        $this->assertTrue(app(AdsService::class)->isEligible($role, $this->visitorRequest()));

        $role->forceFill(['promotions_opt_out' => true])->save();

        $this->assertFalse(app(AdsService::class)->isEligible($role->fresh(), $this->visitorRequest()));
    }

    public function test_the_demo_schedule_stays_ad_free(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue', [
            'subdomain' => \App\Services\DemoService::DEMO_ROLE_SUBDOMAIN,
            'plan_type' => 'free',
            'plan_expires' => now()->subDay()->format('Y-m-d'),
        ]);

        $this->assertFalse($role->showAds(), 'The demo schedule is a sales surface.');
    }

    public function test_boolean_settings_stored_as_zero_beat_a_truthy_env_default(): void
    {
        // Storing null for "off" would fall through to the config default and silently
        // re-enable the feature. '0' must win.
        config(['ads.adsense_enabled' => true]);
        Setting::set('ads_adsense_enabled', '0');

        $this->assertFalse(AdsService::boolSetting('adsense_enabled'));
        $this->assertFalse(AdsService::adSenseConfigured());
    }

    public function test_non_personalized_ads_are_the_default_and_gpc_is_honoured(): void
    {
        $this->assertTrue(AdsService::requestNonPersonalizedAds($this->visitorRequest()));

        Setting::set('ads_personalized', '1');
        $this->assertFalse(AdsService::requestNonPersonalizedAds($this->visitorRequest()));

        // Global Privacy Control overrides the operator's preference.
        $gpc = $this->visitorRequest();
        $gpc->headers->set('Sec-GPC', '1');
        $this->assertTrue(AdsService::requestNonPersonalizedAds($gpc));
    }
}
