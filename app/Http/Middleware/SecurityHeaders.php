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
        
        // Improved Content Security Policy
        $csp = [
            "default-src 'self'",
            "script-src 'self' 'nonce-{$nonce}' *.googleapis.com *.gstatic.com *.stripe.com",
            "style-src 'self' 'unsafe-inline' *.googleapis.com *.gstatic.com",
            "img-src 'self' data: *.googleapis.com *.gstatic.com *.stripe.com",
            "font-src 'self' *.googleapis.com *.gstatic.com",
            "connect-src 'self' *.googleapis.com *.stripe.com *.sentry.io",
            "frame-src 'self' *.stripe.com",
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            "frame-ancestors 'none'",
            "upgrade-insecure-requests"
        ];
        
        // Don't set CSP for debug toolbar in development
        if (!app()->environment('local') || !$request->is('_debugbar/*')) {
            $response->headers->set('Content-Security-Policy', implode('; ', $csp));
        }

        return $response;
    }
} 