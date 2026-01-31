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
            $request->session()->put('utm_params', [
                'utm_source' => $this->sanitize($request->query('utm_source')),
                'utm_medium' => $this->sanitize($request->query('utm_medium')),
                'utm_campaign' => $this->sanitize($request->query('utm_campaign')),
                'utm_content' => $this->sanitize($request->query('utm_content')),
                'utm_term' => $this->sanitize($request->query('utm_term')),
            ]);

            $referer = $request->header('Referer');
            if ($referer) {
                $request->session()->put('utm_referrer_url', mb_substr(trim($referer), 0, 2048));
            }
        }

        // Capture referrer independently of UTM params (first-touch)
        if (! $request->session()->has('utm_referrer_url')) {
            $referer = $request->header('Referer');
            if ($referer) {
                $request->session()->put('utm_referrer_url', mb_substr(trim($referer), 0, 2048));
            }
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
