<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * The marketing side of the vendor map: its feature page, the three audience pages written for it,
 * and the farmers market page that used to describe a hand-made stand-in for it.
 */
class VendorMapMarketingPagesTest extends TestCase
{
    private const AUDIENCE_PAGES = [
        'marketing.for_craft_fairs' => ['/for-craft-fairs', 'craft_fair'],
        'marketing.for_flea_markets' => ['/for-flea-markets', 'flea_market'],
        'marketing.for_conferences_and_expos' => ['/for-conferences-and-expos', 'expo'],
        'marketing.for_farmers_markets' => ['/for-farmers-markets', 'market'],
    ];

    public function test_the_feature_page_renders_and_says_what_it_is(): void
    {
        $body = $this->get(route('marketing.vendor_map'))->assertOk()->getContent();

        $this->assertStringContainsString('Vendor map for markets, fairs and expos', $body);
        $this->assertStringContainsString('/sign_up?type=venue&amp;vendor_map=market', $body);
        // Real screens below the hero, never a drawing of one.
        foreach (['tab-desktop-light', 'map-phone', 'designer-desktop-light', 'claim-phone', 'sign', 'map-desktop'] as $shot) {
            $this->assertStringContainsString('images/marketing/vendor-map/'.$shot.'.webp', $body);
            $this->assertFileExists(public_path('images/marketing/vendor-map/'.$shot.'.webp'));
        }
        // Both tiers are named where the page says what each one includes.
        $this->assertStringContainsString('es-vm-pill is-free', $body);
        $this->assertStringContainsString('es-vm-pill is-pro', $body);
    }

    public function test_the_feature_page_is_reachable_and_indexable(): void
    {
        $features = $this->get(route('marketing.features'))->assertOk()->getContent();
        $this->assertStringContainsString(marketing_url('/features/vendor-map'), $features);

        $xml = $this->get('/sitemap-pages.xml')->assertOk()->streamedContent();
        $this->assertStringContainsString(url('/features/vendor-map'), $xml);
        $this->assertStringContainsString(url('/docs/vendor-maps'), $xml);
        foreach (self::AUDIENCE_PAGES as [$path]) {
            $this->assertStringContainsString(url($path), $xml);
        }
    }

    public function test_each_audience_page_signs_up_into_its_own_kind_of_map(): void
    {
        foreach (self::AUDIENCE_PAGES as $route => [$path, $kind]) {
            $body = $this->get(route($route))->assertOk()->getContent();

            $this->assertStringContainsString('/sign_up?type=venue&amp;vendor_map='.$kind, $body, "{$path} does not start its own kind of map");
            $this->assertStringContainsString(marketing_url('/features/vendor-map'), $body, "{$path} does not link the feature page");
            $this->assertStringContainsString('application/ld+json', $body);
        }
    }

    public function test_the_farmers_market_page_describes_the_map_and_not_the_old_work_around(): void
    {
        $body = $this->get(route('marketing.for_farmers_markets'))->assertOk()->getContent();

        $this->assertStringContainsString('images/marketing/vendor-map/', $body);
        // The mock pitch map is gone, and so is the advice that stood in for the feature.
        $this->assertStringNotContainsString('class="fm-map"', $body);
        $this->assertStringNotContainsString('Name them as participants on the event', $body);
    }

    public function test_the_festival_page_offers_a_food_festival_map_on_a_venue(): void
    {
        $body = $this->get(route('marketing.for_festivals'))->assertOk()->getContent();

        $this->assertStringContainsString('/sign_up?type=venue&amp;vendor_map=food_festival', $body);
    }
}
