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
}
