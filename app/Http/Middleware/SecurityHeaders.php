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
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Generate nonce for inline scripts
        $nonce = base64_encode(Str::random(16));
        $request->attributes->set('csp_nonce', $nonce);

        // Add security headers
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=()');
        
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
                "script-src 'self' 'unsafe-inline' 'unsafe-eval' {$host}:* *.googleapis.com *.gstatic.com *.googletagmanager.com *.stripe.com unpkg.com js.sentry-cdn.com *.sentry.io",
                "style-src 'self' 'unsafe-inline' {$host}:* *.googleapis.com *.gstatic.com rsms.me",
                "img-src 'self' data: {$host}:* *.googleapis.com *.gstatic.com *.stripe.com *.ytimg.com eventschedule.nyc3.cdn.digitaloceanspaces.com",
                "font-src 'self' data: {$host}:* *.googleapis.com *.gstatic.com rsms.me",
                "connect-src 'self' {$host}:* ws://{$host}:* wss://{$host}:* *.googleapis.com *.stripe.com *.sentry.io",
                "frame-src 'self' *.stripe.com *.youtube.com *.googletagmanager.com",
                "object-src 'none'",
                "base-uri 'self'",
                "form-action 'self'"
            ];
        } else {
            // Stricter CSP for production
            $csp = [
                "default-src 'self'",
                "script-src 'self' 'nonce-{$nonce}' 'unsafe-eval' 'unsafe-inline' *.googleapis.com *.gstatic.com *.googletagmanager.com *.stripe.com unpkg.com js.sentry-cdn.com browser.sentry-cdn.com *.sentry.io",
                "style-src 'self' 'unsafe-inline' *.googleapis.com *.gstatic.com rsms.me",
                "img-src 'self' data: *.googleapis.com *.gstatic.com *.stripe.com *.ytimg.com eventschedule.nyc3.cdn.digitaloceanspaces.com",
                "font-src 'self' data: *.googleapis.com *.gstatic.com rsms.me",
                "connect-src 'self' *.googleapis.com *.stripe.com *.sentry.io",
                "frame-src 'self' *.stripe.com *.youtube.com *.googletagmanager.com",
                "object-src 'none'",
                "base-uri 'self'",
                "form-action 'self'",
                "frame-ancestors 'none'",
                "upgrade-insecure-requests"
            ];
        }
        
        // Don't set CSP for debug toolbar
        if (!$request->is('_debugbar/*')) {
            $response->headers->set('Content-Security-Policy', implode('; ', $csp));
        }

        return $response;
    }
} 