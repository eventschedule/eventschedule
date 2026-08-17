<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SecurityHeaders
{
    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Generate nonce BEFORE response so Blade templates can use it
        $nonce = base64_encode(Str::random(16));
        $request->attributes->set('csp_nonce', $nonce);
        \Illuminate\Support\Facades\Vite::useCspNonce($nonce);

        if (app()->bound('debugbar')) {
            app('debugbar')->getJavascriptRenderer()->setCspNonce($nonce);
        }

        $response = $next($request);

        // Add security headers
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Only allow embedding on specific embeddable routes (e.g., public calendar views)
        // The embed parameter must be present AND the route must be in the allowed list
        $embeddableRoutes = [
            'role.view_guest',           // Selfhosted guest view
            'event.view_guest',          // Hosted guest view (slug only)
            'event.view_guest_with_id',  // Hosted guest view (slug + id)
            'event.view_guest_full',     // Hosted guest view (slug + id + date)
            'event.checkout',            // Ticket checkout POST
            'checkout.success',          // Stripe success redirect
            'checkout.cancel',           // Stripe cancel redirect
            'ticket.view',               // Ticket confirmation page
            'event.rsvp',                // RSVP POST
            'payment_url.success',       // Payment URL success
            'payment_url.cancel',        // Payment URL cancel
            // Every gateway's return and cancel landings share these two route names, so this covers
            // any gateway added later rather than needing a new entry each time.
            'payments.return',
            'payments.cancel',
        ];
        $currentRouteName = $request->route()?->getName();
        $isEmbeddable = $request->has('embed')
            && ($request->embed === 'true' || $request->embed === '1')
            && in_array($currentRouteName, $embeddableRoutes);

        if ($isEmbeddable) {
            // ALLOW-FROM is deprecated; modern browsers use CSP frame-ancestors
            // Remove X-Frame-Options to let CSP take precedence
            $response->headers->remove('X-Frame-Options');
        } else {
            $response->headers->set('X-Frame-Options', 'DENY');
        }

        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(self), microphone=(), geolocation=(), payment=()');

        // Add HSTS header for HTTPS
        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        }

        $isLocal = app()->environment('local');
        $host = $request->getHost();

        $directives = $this->baseDirectives($isLocal, $host, $nonce);

        // Opt-in third-party integrations widen the policy only when they are actually
        // configured, so an install that uses none of them keeps the tight default.
        foreach ($this->conditionalSources() as $directive => $sources) {
            if (isset($directives[$directive])) {
                $directives[$directive] = array_merge($directives[$directive], $sources);
            }
        }

        $csp = [];
        foreach ($directives as $directive => $sources) {
            // A valueless directive (upgrade-insecure-requests) is stored with an empty source list.
            $csp[] = $sources === [] ? $directive : $directive.' '.implode(' ', $sources);
        }

        // Allow frame-ancestors only on validated embeddable routes
        if ($isEmbeddable) {
            $csp[] = 'frame-ancestors *';
        } else {
            $csp[] = "frame-ancestors 'none'";
        }

        // Don't set CSP for debug toolbar
        if (! $request->is('_debugbar/*')) {
            $response->headers->set('Content-Security-Policy', implode('; ', $csp));
        }

        return $response;
    }

    /**
     * The always-on CSP directives, as directive => list of source expressions.
     *
     * This used to be two hand-maintained arrays (one for local, one for production) that
     * had to be edited in lockstep - adding a host to one and forgetting the other was a
     * silent, environment-specific failure. The two differ only in the marked spots.
     *
     * Note: 'unsafe-inline' is kept as a fallback for CSP Level 1 browsers; Level 2+
     * browsers ignore it when nonces are present.
     *
     * @return array<string, list<string>>
     */
    protected function baseDirectives(bool $isLocal, string $host, string $nonce): array
    {
        return [
            'default-src' => ["'self'"],

            'script-src' => array_merge(
                ["'self'", "'unsafe-inline'", "'unsafe-eval'", "'nonce-{$nonce}'"],
                $isLocal ? ["{$host}:*"] : [],
                ['*.googleapis.com', '*.gstatic.com', '*.googletagmanager.com', '*.stripe.com',
                    'unpkg.com', 'js.sentry-cdn.com'],
                $isLocal ? [] : ['browser.sentry-cdn.com'],
                ['*.sentry.io', 'challenges.cloudflare.com', 'cdn.jsdelivr.net',
                    'cdn.onesignal.com', '*.onesignal.com'],
            ),

            'style-src' => array_merge(
                ["'self'", "'unsafe-inline'"],
                $isLocal ? ["{$host}:*"] : [],
                ['*.googleapis.com', '*.gstatic.com', '*.bootstrapcdn.com', 'cdn.jsdelivr.net'],
            ),

            'img-src' => array_merge(
                ["'self'", 'data:', 'https:'],
                $isLocal ? ["{$host}:*"] : [],
                ['*.googleapis.com', '*.gstatic.com', '*.googletagmanager.com', '*.stripe.com',
                    '*.ytimg.com', 'eventschedule.nyc3.cdn.digitaloceanspaces.com',
                    'eventschedule.nyc3.digitaloceanspaces.com', 'cdn.jsdelivr.net'],
            ),

            'font-src' => array_merge(
                ["'self'", 'data:'],
                $isLocal ? ["{$host}:*"] : [],
                ['*.googleapis.com', '*.gstatic.com', '*.bootstrapcdn.com'],
            ),

            'connect-src' => array_merge(
                ["'self'"],
                $isLocal ? ["{$host}:*", "ws://{$host}:*", "wss://{$host}:*"] : [],
                ['*.googleapis.com', '*.google-analytics.com', '*.googletagmanager.com',
                    '*.jsdelivr.net', '*.stripe.com', '*.sentry.io', '*.sentry-cdn.com',
                    'ipapi.co', '*.onesignal.com', '*.os.tc'],
            ),

            'worker-src' => ["'self'", 'cdn.onesignal.com', '*.onesignal.com'],

            'manifest-src' => ["'self'"],

            // Production hardcodes *.eventschedule.com rather than *.{$host}. That is wrong for a
            // self-hosted SaaS operator on their own domain, but fixing it is a behaviour change
            // that does not belong in a monetization PR - left as-is deliberately.
            'frame-src' => array_merge(
                ["'self'", $isLocal ? "*.{$host}" : '*.eventschedule.com'],
                ['*.stripe.com', '*.youtube.com', '*.youtube-nocookie.com',
                    '*.googletagmanager.com', '*.google.com', 'challenges.cloudflare.com'],
            ),

            'object-src' => ["'none'"],

            'base-uri' => ["'self'"],

            ...($isLocal ? [] : ['upgrade-insecure-requests' => []]),
        ];
    }

    /**
     * Extra sources contributed by optional integrations, keyed by directive.
     *
     * Each block is gated on that integration actually being configured, so a install that
     * has not opted in keeps exactly the policy it had before the integration existed.
     *
     * @return array<string, list<string>>
     */
    protected function conditionalSources(): array
    {
        $extra = [];

        // Google AdSense. Host-source allow-listing is sufficient and 'strict-dynamic' would
        // be actively harmful here: it makes conforming browsers ignore EVERY host-source
        // expression in the directive, which would silently disable Stripe, Turnstile,
        // jsDelivr, unpkg and anything an operator injects via custom_header_code. The
        // creatives themselves render in cross-origin iframes, into which this policy does
        // not propagate, so frame-src is all that is needed to reach them.
        if (\App\Services\AdsService::adSenseConfigured()) {
            $extra['script-src'] = [
                'pagead2.googlesyndication.com', '*.googlesyndication.com',
                'partner.googleadservices.com', '*.googletagservices.com',
                'adservice.google.com', '*.doubleclick.net',
                // Google's own consent management platform, which an operator serving the
                // EEA/UK has to enable in their AdSense account.
                'fundingchoicesmessages.google.com',
            ];
            $extra['frame-src'] = [
                '*.googlesyndication.com', '*.doubleclick.net', '*.adtrafficquality.google',
            ];
            $extra['connect-src'] = [
                '*.googlesyndication.com', '*.doubleclick.net', '*.google.com',
                '*.adtrafficquality.google', 'fundingchoicesmessages.google.com',
            ];
            // img-src already carries the bare `https:` scheme source, so creatives load
            // without any addition there.
        }

        // The Stay22 accommodation map renders in a plain cross-origin iframe, so frame-src
        // is the only directive it needs: our policy does not propagate into that document,
        // and the widget loads no script or stylesheet of ours. img-src already carries the
        // bare `https:` scheme source.
        //
        // Gated on config only - never on Setting::get() and never on "an affiliate ID
        // resolves". This method runs on every request including health checks, so it must
        // not touch the database; and a schedule owner can set their own ID on an install
        // where the operator has none, so gating on a resolvable ID would block the frame
        // with no visible cause. STAY22_ENABLED is the operator saying "my customers may
        // opt into this". Note this widening is independent of ADS_ENABLED, and unlike the
        // hardcoded *.eventschedule.com above it is host-independent, so it is enforced
        // identically on a customer's custom domain.
        if (\App\Services\Stay22Service::isEnabled()) {
            $extra['frame-src'][] = '*.stay22.com';
        }

        // The Meta Pixel bootstrap in app-guest.blade.php script-inserts fbevents.js. A
        // script-inserted script is still matched against script-src host sources (no
        // 'strict-dynamic' here, and Meta's snippet does not copy our nonce onto the tag),
        // so without this the pixel was silently blocked and fbq never flushed its queue.
        if (config('services.meta.pixel_id')) {
            $extra['script-src'][] = 'connect.facebook.net';
            $extra['connect-src'][] = 'www.facebook.com';
        }

        return $extra;
    }
}
