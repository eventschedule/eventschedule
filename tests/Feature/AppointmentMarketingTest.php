<?php

namespace Tests\Feature;

use App\Http\Controllers\MarketingController;
use Tests\TestCase;

class AppointmentMarketingTest extends TestCase
{
    public function test_features_and_docs_pages_render(): void
    {
        // The /features/* and /docs/* pages are nexus-host gated at the HTTP layer; render the
        // views directly to confirm the Blade compiles and the content is present.
        $features = view('marketing.appointments')->render();
        $this->assertStringContainsString('Appointment booking', $features);

        $ref = new \ReflectionMethod(MarketingController::class, 'getDocNavigation');
        $ref->setAccessible(true);
        $nav = $ref->invoke(app(MarketingController::class), 'marketing.docs.appointments');
        $docs = view('marketing.docs.appointments', $nav)->render();
        $this->assertStringContainsString('Appointment types', $docs);
    }

    /**
     * The admin group is domain-less and registered first, so /features/{tab} matched
     * role.view_admin as subdomain=features and 302'd anonymous visitors (and Googlebot, which reads
     * these URLs from the sitemap) to the login page. Rendering the view is not enough to catch
     * that - the URL itself has to resolve here.
     */
    public function test_feature_urls_resolve_to_the_marketing_pages(): void
    {
        foreach (['/features/appointments' => 'marketing.appointments', '/features/availability' => 'marketing.availability'] as $url => $name) {
            $this->get($url)->assertOk();
            $this->assertSame($name, app('router')->current()->getName(), $url.' is shadowed by another route');
        }
    }
}
