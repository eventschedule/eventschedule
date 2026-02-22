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

        // Build CSP based on environment
        if ($isLocal) {
            // More permissive CSP for development
            $csp = [
                "default-src 'self'",
                "script-src 'self' 'unsafe-inline' 'unsafe-eval' 'nonce-{$nonce}' {$host}:* *.googleapis.com *.gstatic.com *.googletagmanager.com *.stripe.com unpkg.com js.sentry-cdn.com *.sentry.io challenges.cloudflare.com cdn.jsdelivr.net",
                "style-src 'self' 'unsafe-inline' {$host}:* *.googleapis.com *.gstatic.com *.bootstrapcdn.com cdn.jsdelivr.net",
                "img-src 'self' data: {$host}:* *.googleapis.com *.gstatic.com *.googletagmanager.com *.stripe.com *.ytimg.com eventschedule.nyc3.cdn.digitaloceanspaces.com",
                "font-src 'self' data: {$host}:* *.googleapis.com *.gstatic.com *.bootstrapcdn.com",
                "connect-src 'self' {$host}:* ws://{$host}:* wss://{$host}:* *.googleapis.com *.google-analytics.com *.googletagmanager.com *.jsdelivr.net *.stripe.com *.sentry.io *.sentry-cdn.com ipapi.co",
                "frame-src 'self' *.{$host} *.stripe.com *.youtube.com *.youtube-nocookie.com *.googletagmanager.com challenges.cloudflare.com",
                "object-src 'none'",
                "base-uri 'self'",
            ];
        } else {
            // Stricter CSP for production
            // Note: 'unsafe-inline' is kept as a fallback for CSP Level 1 browsers; Level 2+ browsers ignore it when nonces are present
            $csp = [
                "default-src 'self'",
                "script-src 'self' 'unsafe-inline' 'unsafe-eval' 'nonce-{$nonce}' *.googleapis.com *.gstatic.com *.googletagmanager.com *.stripe.com unpkg.com js.sentry-cdn.com browser.sentry-cdn.com *.sentry.io challenges.cloudflare.com cdn.jsdelivr.net",
                "style-src 'self' 'unsafe-inline' *.googleapis.com *.gstatic.com *.bootstrapcdn.com cdn.jsdelivr.net",
                "img-src 'self' data: *.googleapis.com *.gstatic.com *.googletagmanager.com *.stripe.com *.ytimg.com eventschedule.nyc3.cdn.digitaloceanspaces.com",
                "font-src 'self' data: *.googleapis.com *.gstatic.com *.bootstrapcdn.com",
                "connect-src 'self' *.googleapis.com *.google-analytics.com *.googletagmanager.com *.jsdelivr.net *.stripe.com *.sentry.io *.sentry-cdn.com ipapi.co",
                "frame-src 'self' *.eventschedule.com *.stripe.com *.youtube.com *.youtube-nocookie.com *.googletagmanager.com challenges.cloudflare.com",
                "object-src 'none'",
                "base-uri 'self'",
                'upgrade-insecure-requests',
            ];
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
}
