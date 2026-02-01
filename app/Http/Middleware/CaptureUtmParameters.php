<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CaptureUtmParameters
{
    public function handle(Request $request, Closure $next): Response
    {
        // Only capture if UTM params are present and session doesn't already have them (first-touch)
        if (! $request->session()->has('utm_params') && $this->hasUtmParams($request)) {
            $utmParams = [
                'utm_source' => $this->sanitize($request->query('utm_source')),
                'utm_medium' => $this->sanitize($request->query('utm_medium')),
                'utm_campaign' => $this->sanitize($request->query('utm_campaign')),
                'utm_content' => $this->sanitize($request->query('utm_content')),
                'utm_term' => $this->sanitize($request->query('utm_term')),
            ];

            $request->session()->put('utm_params', $utmParams);

            // Also store in a long-lived cookie as fallback for cross-session attribution
            $response = $next($request);
            $response->cookie('utm_params', json_encode($utmParams), 60 * 24 * 30, '/', null, true, true, false, 'Lax');

            $referer = $request->header('Referer');
            if ($referer && ! $this->isSameDomain($referer, $request)) {
                $referrerUrl = mb_substr(trim($referer), 0, 2048);
                $request->session()->put('utm_referrer_url', $referrerUrl);
                $response->cookie('utm_referrer_url', $referrerUrl, 60 * 24 * 30, '/', null, true, true, false, 'Lax');
            }

            // Capture landing page on first visit
            if (! $request->session()->has('utm_landing_page')) {
                $landingPage = mb_substr($request->path(), 0, 2048);
                $request->session()->put('utm_landing_page', $landingPage);
                $response->cookie('utm_landing_page', $landingPage, 60 * 24 * 30, '/', null, true, true, false, 'Lax');
            }

            return $response;
        }

        // Capture referrer independently of UTM params (first-touch), filtering same-domain
        if (! $request->session()->has('utm_referrer_url')) {
            $referer = $request->header('Referer');
            if ($referer && ! $this->isSameDomain($referer, $request)) {
                $referrerUrl = mb_substr(trim($referer), 0, 2048);
                $request->session()->put('utm_referrer_url', $referrerUrl);
            }
        }

        // Capture landing page on first visit (even without UTM params)
        if (! $request->session()->has('utm_landing_page')) {
            $landingPage = mb_substr($request->path(), 0, 2048);
            $request->session()->put('utm_landing_page', $landingPage);
        }

        return $next($request);
    }

    private function hasUtmParams(Request $request): bool
    {
        return $request->has('utm_source')
            || $request->has('utm_medium')
            || $request->has('utm_campaign')
            || $request->has('utm_content')
            || $request->has('utm_term');
    }

    private function isSameDomain(string $referer, Request $request): bool
    {
        $refererHost = parse_url($referer, PHP_URL_HOST);

        if (! $refererHost) {
            return false;
        }

        return strcasecmp($refererHost, $request->getHost()) === 0;
    }

    private function sanitize(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        // Strip control characters, trim, and limit length
        $value = preg_replace('/[\x00-\x1F\x7F]/u', '', trim($value));

        return mb_substr($value, 0, 255) ?: null;
    }
}
