<?php

namespace Tests\Feature;

use App\Models\BoostCampaign;
use App\Models\Setting;
use App\Services\MetaAdsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The two cookie-consent categories (analytics, marketing) and everything they gate.
 *
 * The choice lives in the browser (resources/js/consent-state.js, inlined into <head> by
 * partials/consent-state.blade.php) and is mirrored into the `cookie_consent` cookie for the
 * server (consent_granted()). These tests pin what the server can see: the mapping, that every
 * consent-gated third party ships INERT in the HTML and waits for window.esConsent, and that
 * nothing reads the cookie while rendering a page the edge may cache.
 */
class ConsentCategoriesTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    public function test_the_consent_cookie_maps_to_categories(): void
    {
        $now = time();
        $cases = [
            "analytics.marketing.{$now}" => [true, true],
            "marketing.analytics.{$now}" => [true, true],
            "analytics.{$now}" => [true, false],
            "marketing.{$now}" => [false, true],
            "denied.{$now}" => [false, false],
            // Undated, which only a version-1 value may be.
            'analytics.marketing' => [false, false],
            // Made more than twelve months ago: lapsed, whatever the browser still sends.
            'analytics.marketing.'.($now - 366 * 24 * 60 * 60) => [false, false],
            // Version 1, from a banner that only ever said "analytics": never marketing.
            'granted' => [true, false],
            'denied' => [false, false],
            '' => [false, false],
            'everything' => [false, false],
        ];

        foreach ($cases as $value => [$analytics, $marketing]) {
            $request = Request::create('/', 'GET', [], ['cookie_consent' => $value]);

            $this->assertSame($analytics, consent_granted('analytics', $request), "analytics for '{$value}'");
            $this->assertSame($marketing, consent_granted('marketing', $request), "marketing for '{$value}'");
        }

        $this->assertFalse(consent_granted('marketing', Request::create('/')), 'no cookie at all');
    }

    /**
     * An undated version-1 "granted" cannot lapse after twelve months, so it stops counting on a
     * fixed day; consent-state.js and consent_granted() must agree on which.
     */
    public function test_a_version_one_grant_stops_counting_on_the_same_day_in_php_and_js(): void
    {
        $request = Request::create('/', 'GET', [], ['cookie_consent' => 'granted']);

        $this->travelTo(\Carbon\Carbon::parse('2027-01-03 23:59:59'));
        $this->assertTrue(consent_granted('analytics', $request));

        $this->travelTo(\Carbon\Carbon::parse('2027-01-04 00:00:00'));
        $this->assertFalse(consent_granted('analytics', $request));

        $this->travelBack();

        $this->assertStringContainsString(
            'Date.UTC(2027, 0, 4)',
            file_get_contents(resource_path('js/consent-state.js')),
            'consent-state.js must stop honouring "granted" on the same day as consent_granted()'
        );
    }

    public function test_global_privacy_control_refuses_every_category(): void
    {
        $request = Request::create('/', 'GET', [], ['cookie_consent' => 'analytics.marketing.'.time()], [], ['HTTP_SEC_GPC' => '1']);

        $this->assertFalse(consent_granted('analytics', $request));
        $this->assertFalse(consent_granted('marketing', $request));
    }

    /**
     * Anonymous marketing HTML is cached at the edge, so a choice read while rendering would be
     * served to every later visitor. Pages decide in the browser; the helper is for request-scoped
     * server decisions only.
     */
    public function test_no_view_reads_the_consent_cookie(): void
    {
        $offenders = [];
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(resource_path('views')));

        foreach ($files as $file) {
            if ($file->isFile() && str_ends_with($file->getFilename(), '.blade.php')
                && str_contains(file_get_contents($file->getPathname()), 'consent_granted(')) {
                $offenders[] = $file->getPathname();
            }
        }

        $this->assertSame([], $offenders);
    }

    public function test_google_analytics_is_not_requested_before_analytics_consent(): void
    {
        config(['services.google.analytics' => 'G-TEST123']);

        $html = $this->get('/pricing')->assertOk()->getContent();

        $this->assertStringNotContainsString('<script async src="https://www.googletagmanager.com', $html,
            'gtag.js must be injected by the consent check, never as a tag in the page');
        $this->assertStringContainsString("consent.has('analytics')", $html);

        // The reader is inlined before the partial that calls it.
        $reader = strpos($html, 'w.esConsent = {');
        $this->assertNotFalse($reader, 'consent-state.js must be inlined into the page');
        $this->assertLessThan(strpos($html, "gtag('consent', 'default'"), $reader);
    }

    /**
     * Google is told the route, not the link: a reset, ticket or unsubscribe page reports its
     * template, and a page with no secret leaves the browser to use its own path.
     */
    public function test_google_analytics_reports_a_secret_route_by_its_template(): void
    {
        config(['services.google.analytics' => 'G-TEST123']);
        $token = str_repeat('a1', 20);

        $html = $this->get('/update-password/'.$token.'?email=someone%40example.com')->assertOk()->getContent();
        $this->assertStringContainsString('var redactedPath = "\/update-password\/{token}";', $html);

        $this->assertStringContainsString('var redactedPath = null;', $this->get('/pricing')->getContent());
    }

    public function test_the_meta_pixel_waits_for_marketing_consent(): void
    {
        config(['services.meta.pixel_id' => 'PX123456']);
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->createEvent($role);
        BoostCampaign::create([
            'event_id' => $event->id,
            'role_id' => $role->id,
            'user_id' => $owner->id,
            'channel' => 'meta',
            'name' => 'Boost',
            'status' => 'active',
            'user_budget' => 50,
        ]);

        $html = $this->get($this->guestEventUrl($role, $event))->assertOk()->getContent();

        $gate = strpos($html, "window.esConsent.has('marketing')");
        $init = strpos($html, "fbq('init'");
        $this->assertNotFalse($gate);
        $this->assertNotFalse($init);
        $this->assertLessThan($init, $gate, 'the pixel initialises only behind the consent check');
        $this->assertStringNotContainsString('facebook.com/tr?id=', $html, 'no <noscript> pixel, which no one is asked about');

        $this->assertStringNotContainsString('fbevents.js', $this->get($this->guestEventUrl($role, $event).'?embed=true')->getContent());
    }

    public function test_the_conversions_api_hears_only_about_a_consenting_buyers_purchase(): void
    {
        config(['services.meta.pixel_id' => 'PX123456', 'services.meta.access_token' => 'token']);
        Http::fake();

        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->createEvent($role);
        BoostCampaign::create([
            'event_id' => $event->id,
            'role_id' => $role->id,
            'user_id' => $owner->id,
            'channel' => 'meta',
            'name' => 'Boost',
            'status' => 'active',
            'user_budget' => 50,
        ]);

        $declined = $this->createSale($event, $role, ['email' => 'declined@example.com']);
        (new MetaAdsService)->sendSaleConversion($declined, 20);
        Http::assertNothingSent();

        $consented = $this->createSale($event, $role, ['email' => 'consented@example.com']);
        $consented->forceFill(['ad_consent' => true])->save();
        (new MetaAdsService)->sendSaleConversion($consented->fresh(), 20);
        Http::assertSent(fn ($request) => str_contains($request->url(), '/PX123456/events'));
    }

    public function test_onesignal_is_not_downloaded_until_push_is_asked_for(): void
    {
        config(['services.onesignal.app_id' => 'app', 'services.onesignal.rest_api_key' => 'key']);
        $role = $this->createRole($this->createOwner());

        $html = $this->get('/'.$role->subdomain)->assertOk()->getContent();

        $this->assertStringNotContainsString('<script src="https://cdn.onesignal.com', $html);
        $this->assertStringContainsString('window.esPushLoad', $html);
        $this->assertStringNotContainsString('esPushLoad', $this->get('/'.$role->subdomain.'?embed=true')->getContent());
    }

    public function test_browser_error_reporting_is_served_locally(): void
    {
        config(['app.report_errors' => true, 'app.sentry_js_dsn' => null]);
        Setting::set('realtime_enabled', '0');

        $html = $this->get('/pricing')->assertOk()->getContent();

        $this->assertStringNotContainsString('sentry-cdn.com', $html);
        $this->assertStringContainsString('vendor\/sentry\/bundle-8.55.2.min.js', $html);
        $this->assertFileExists(public_path('vendor/sentry/bundle-8.55.2.min.js'));
        $this->assertSame('never', config('sentry.max_request_body_size'), 'no request bodies in error reports');
    }
}
