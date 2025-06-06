<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

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

        // Add security headers
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        //$response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
        
        // Add HSTS header for HTTPS
        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }
        
        // Content Security Policy
        $csp = [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' *.googleapis.com *.gstatic.com *.stripe.com",
            "style-src 'self' 'unsafe-inline' *.googleapis.com *.gstatic.com",
            "img-src 'self' data: *.googleapis.com *.gstatic.com *.stripe.com",
            "font-src 'self' *.googleapis.com *.gstatic.com",
            "connect-src 'self' *.googleapis.com *.stripe.com",
            "frame-src 'self' *.stripe.com",
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'"
        ];
        
        $response->headers->set('Content-Security-Policy', implode('; ', $csp));

        return $response;
    }
} 