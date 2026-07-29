<?php

namespace Tests\Feature;

use App\Http\Middleware\SecurityHeaders;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Tests\TestCase;

/**
 * Golden-file cover for the Content-Security-Policy header.
 *
 * The policy used to live in two hand-maintained arrays (local and production) that had to be
 * edited in lockstep; adding a host to one and forgetting the other was a silent,
 * environment-specific failure. They are now generated from one directive map, and these
 * expectations pin the exact emitted header so that refactor - and every future one - cannot
 * quietly change what the browser enforces.
 *
 * If a change here is intentional, update the constant AND say why in the commit message.
 */
class SecurityHeadersCspTest extends TestCase
{
    private const EXPECTED_LOCAL = "default-src 'self'; "
        ."script-src 'self' 'unsafe-inline' 'unsafe-eval' 'nonce-NNN' sub.eventschedule.test:* *.googleapis.com *.gstatic.com *.googletagmanager.com *.stripe.com unpkg.com js.sentry-cdn.com *.sentry.io challenges.cloudflare.com cdn.jsdelivr.net cdn.onesignal.com *.onesignal.com; "
        ."style-src 'self' 'unsafe-inline' sub.eventschedule.test:* *.googleapis.com *.gstatic.com *.bootstrapcdn.com cdn.jsdelivr.net; "
        ."img-src 'self' data: https: sub.eventschedule.test:* *.googleapis.com *.gstatic.com *.googletagmanager.com *.stripe.com *.ytimg.com eventschedule.nyc3.cdn.digitaloceanspaces.com eventschedule.nyc3.digitaloceanspaces.com cdn.jsdelivr.net; "
        ."font-src 'self' data: sub.eventschedule.test:* *.googleapis.com *.gstatic.com *.bootstrapcdn.com; "
        ."connect-src 'self' sub.eventschedule.test:* ws://sub.eventschedule.test:* wss://sub.eventschedule.test:* *.googleapis.com *.google-analytics.com *.googletagmanager.com *.jsdelivr.net *.stripe.com *.sentry.io *.sentry-cdn.com ipapi.co *.onesignal.com *.os.tc; "
        ."worker-src 'self' cdn.onesignal.com *.onesignal.com; "
        ."manifest-src 'self'; "
        ."frame-src 'self' *.sub.eventschedule.test *.stripe.com *.youtube.com *.youtube-nocookie.com *.googletagmanager.com *.google.com challenges.cloudflare.com; "
        ."object-src 'none'; "
        ."base-uri 'self'; "
        ."frame-ancestors 'none'";

    private const EXPECTED_PRODUCTION = "default-src 'self'; "
        ."script-src 'self' 'unsafe-inline' 'unsafe-eval' 'nonce-NNN' *.googleapis.com *.gstatic.com *.googletagmanager.com *.stripe.com unpkg.com js.sentry-cdn.com browser.sentry-cdn.com *.sentry.io challenges.cloudflare.com cdn.jsdelivr.net cdn.onesignal.com *.onesignal.com; "
        ."style-src 'self' 'unsafe-inline' *.googleapis.com *.gstatic.com *.bootstrapcdn.com cdn.jsdelivr.net; "
        ."img-src 'self' data: https: *.googleapis.com *.gstatic.com *.googletagmanager.com *.stripe.com *.ytimg.com eventschedule.nyc3.cdn.digitaloceanspaces.com eventschedule.nyc3.digitaloceanspaces.com cdn.jsdelivr.net; "
        ."font-src 'self' data: *.googleapis.com *.gstatic.com *.bootstrapcdn.com; "
        ."connect-src 'self' *.googleapis.com *.google-analytics.com *.googletagmanager.com *.jsdelivr.net *.stripe.com *.sentry.io *.sentry-cdn.com ipapi.co *.onesignal.com *.os.tc; "
        ."worker-src 'self' cdn.onesignal.com *.onesignal.com; "
        ."manifest-src 'self'; "
        ."frame-src 'self' *.eventschedule.com *.stripe.com *.youtube.com *.youtube-nocookie.com *.googletagmanager.com *.google.com challenges.cloudflare.com; "
        ."object-src 'none'; "
        ."base-uri 'self'; "
        .'upgrade-insecure-requests; '
        ."frame-ancestors 'none'";

    private function cspFor(string $env): string
    {
        $this->app['env'] = $env;

        $request = Request::create('https://sub.eventschedule.test/', 'GET');
        $response = (new SecurityHeaders)->handle($request, fn ($r) => new Response('ok'));

        return preg_replace("/'nonce-[^']+'/", "'nonce-NNN'", $response->headers->get('Content-Security-Policy'));
    }

    public function test_local_policy_is_unchanged(): void
    {
        config(['services.meta.pixel_id' => null]);

        $this->assertSame(self::EXPECTED_LOCAL, $this->cspFor('local'));
    }

    public function test_production_policy_is_unchanged(): void
    {
        config(['services.meta.pixel_id' => null]);

        $this->assertSame(self::EXPECTED_PRODUCTION, $this->cspFor('production'));
    }

    public function test_meta_pixel_hosts_are_absent_when_no_pixel_is_configured(): void
    {
        config(['services.meta.pixel_id' => null]);

        $csp = $this->cspFor('production');

        $this->assertStringNotContainsString('connect.facebook.net', $csp);
        $this->assertStringNotContainsString('www.facebook.com', $csp);
    }

    public function test_meta_pixel_hosts_are_added_when_a_pixel_is_configured(): void
    {
        // Without this the pixel bootstrap in app-guest.blade.php is blocked: it
        // script-inserts fbevents.js, which is matched against script-src host sources.
        config(['services.meta.pixel_id' => '1234567890']);

        $csp = $this->cspFor('production');

        $directives = $this->directiveMap($csp);

        // The addition must be scoped: it widens exactly script-src and connect-src.
        $this->assertContains('connect.facebook.net', $directives['script-src']);
        $this->assertContains('www.facebook.com', $directives['connect-src']);

        foreach ($directives as $name => $sources) {
            if (in_array($name, ['script-src', 'connect-src'], true)) {
                continue;
            }

            foreach ($sources as $source) {
                $this->assertStringNotContainsString('facebook', $source, "{$name} must not gain a Facebook host.");
            }
        }
    }

    public function test_the_meta_pixel_widening_is_independent_of_the_ads_switch(): void
    {
        // This is the one CSP change that reaches installs which never enable monetization:
        // it keys off services.meta.pixel_id, a pre-existing setting, not off ADS_ENABLED.
        // Deliberate - the pixel was silently blocked before and an operator who configured
        // it expects it to work - but it means an ads-disabled install with a pixel gets a
        // different (wider) policy than it did, so pin that rather than leave it incidental.
        config(['services.meta.pixel_id' => '1234567890', 'ads.enabled' => false]);

        $csp = $this->cspFor('production');

        $this->assertStringContainsString('connect.facebook.net', $csp);
        // ...and nothing from the ads half leaks in while the master switch is off.
        $this->assertStringNotContainsString('googlesyndication', $csp);
    }

    /** @return array<string, list<string>> */
    private function directiveMap(string $csp): array
    {
        $map = [];

        foreach (explode('; ', $csp) as $chunk) {
            $parts = preg_split('/\s+/', trim($chunk));
            $map[array_shift($parts)] = $parts;
        }

        return $map;
    }

    public function test_adsense_hosts_are_absent_until_adsense_is_configured(): void
    {
        config(['services.meta.pixel_id' => null, 'ads.enabled' => false]);

        $csp = $this->cspFor('production');

        $this->assertStringNotContainsString('googlesyndication', $csp);
        $this->assertStringNotContainsString('doubleclick', $csp);
    }

    public function test_adsense_hosts_are_added_once_adsense_is_configured(): void
    {
        config([
            'services.meta.pixel_id' => null,
            'ads.enabled' => true,
            'ads.adsense_enabled' => true,
            'ads.adsense_client_id' => 'ca-pub-1234567890123456',
            'ads.adsense_slot_id' => '1234567890',
        ]);

        $directives = $this->directiveMap($this->cspFor('production'));

        $this->assertContains('pagead2.googlesyndication.com', $directives['script-src']);
        $this->assertContains('*.doubleclick.net', $directives['frame-src']);
        $this->assertContains('*.googlesyndication.com', $directives['connect-src']);

        // img-src already carries the bare https: scheme source, so creatives need nothing.
        $this->assertContains('https:', $directives['img-src']);
        foreach ($directives['img-src'] as $source) {
            $this->assertStringNotContainsString('googlesyndication', $source);
        }
    }

    public function test_strict_dynamic_is_never_emitted(): void
    {
        // 'strict-dynamic' makes conforming browsers ignore every host-source expression in
        // script-src, which would silently disable Stripe, Turnstile, jsDelivr, unpkg and any
        // operator-injected tag. It must never appear.
        config(['services.meta.pixel_id' => '1234567890']);

        $this->assertStringNotContainsString("'strict-dynamic'", $this->cspFor('production'));
        $this->assertStringNotContainsString("'strict-dynamic'", $this->cspFor('local'));
    }

    public function test_embeddable_routes_relax_frame_ancestors_only(): void
    {
        $this->app['env'] = 'production';

        $request = Request::create('https://sub.eventschedule.test/?embed=true', 'GET');
        $route = new \Illuminate\Routing\Route('GET', '/', ['as' => 'role.view_guest']);
        $request->setRouteResolver(fn () => $route);

        $response = (new SecurityHeaders)->handle($request, fn ($r) => new Response('ok'));
        $csp = $response->headers->get('Content-Security-Policy');

        $this->assertStringContainsString('frame-ancestors *', $csp);
        $this->assertFalse($response->headers->has('X-Frame-Options'));
    }
}
