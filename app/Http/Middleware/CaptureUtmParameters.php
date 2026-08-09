<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CaptureUtmParameters
{
    /**
     * The cross-session attribution cookies, and how long they live once consented to.
     */
    private const ATTRIBUTION_COOKIES = ['utm_params', 'utm_referrer_url', 'utm_landing_page'];

    private const COOKIE_MINUTES = 60 * 24 * 30;

    public function handle(Request $request, Closure $next): Response
    {
        // The guest appointment surfaces authenticate on a 32-char secret in the PATH, and this
        // middleware would persist that path as `utm_landing_page` into a 30-day cookie, the session,
        // and eventually a `landing_page` column. Attribution from a booking-management link is
        // meaningless anyway - the guest has already booked - so skip the whole middleware.
        if (str_starts_with($request->path(), 'appointment/')) {
            return $next($request);
        }

        // Capture referral code (first-touch)
        if (! $request->session()->has('referral_code') && $request->has('ref')) {
            $refCode = preg_replace('/[^a-zA-Z0-9]/', '', $request->query('ref'));
            $refCode = substr($refCode, 0, 8);
            if ($refCode) {
                $request->session()->put('referral_code', $refCode);
            }
        }

        $consented = $this->hasConsent($request);

        // Attribution is first-touch, with one deliberate exception: a paid on-network
        // placement overrides whatever came before it. Without this, a visitor who once
        // arrived from a newsletter keeps that attribution for 30 days, so an advertiser
        // who just paid for this click would have the resulting sale credited elsewhere.
        // The query string is also the only carrier that survives the hop to a custom
        // domain, where ResolveCustomDomain nulls session.domain.
        // The signed token is what makes this safe to honour. Without it, appending these two
        // parameters to any link would let an advertiser overwrite a visitor's existing
        // attribution at will and claim every subsequent sale in that browser.
        $isPaidPlacement = $request->query('utm_source') === 'boost'
            && $request->query('utm_medium') === 'network'
            && \App\Services\PromotionService::verifyClickToken(
                $request->query('utm_token'),
                $request->query('utm_campaign')
            );

        // Only capture if UTM params are present and session doesn't already have them (first-touch)
        if (($isPaidPlacement || ! $request->session()->has('utm_params')) && $this->hasUtmParams($request)) {
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
            $this->rememberAttribution($response, $consented, 'utm_params', json_encode($utmParams));

            $referer = $request->header('Referer');
            if ($referer && ! $this->isSameDomain($referer, $request)) {
                $referrerUrl = mb_substr(trim($referer), 0, 2048);
                $request->session()->put('utm_referrer_url', $referrerUrl);
                $this->rememberAttribution($response, $consented, 'utm_referrer_url', $referrerUrl);
            }

            // Capture landing page on first visit (GET only)
            if ($request->isMethod('GET') && ! $request->session()->has('utm_landing_page')) {
                $landingPage = mb_substr($request->path(), 0, 2048);
                $request->session()->put('utm_landing_page', $landingPage);
                $this->rememberAttribution($response, $consented, 'utm_landing_page', $landingPage);
            }

            return $this->forgetAttributionCookies($request, $response, $consented);
        }

        $response = $next($request);

        // Capture referrer independently of UTM params (first-touch), filtering same-domain
        if (! $request->session()->has('utm_referrer_url')) {
            $referer = $request->header('Referer');
            if ($referer && ! $this->isSameDomain($referer, $request)) {
                $referrerUrl = mb_substr(trim($referer), 0, 2048);
                $request->session()->put('utm_referrer_url', $referrerUrl);
                $this->rememberAttribution($response, $consented, 'utm_referrer_url', $referrerUrl);
            }
        }

        // Capture landing page on first visit (GET only, even without UTM params)
        if ($request->isMethod('GET') && ! $request->session()->has('utm_landing_page')) {
            $landingPage = mb_substr($request->path(), 0, 2048);
            $request->session()->put('utm_landing_page', $landingPage);
            $this->rememberAttribution($response, $consented, 'utm_landing_page', $landingPage);
        }

        return $this->forgetAttributionCookies($request, $response, $consented);
    }

    /**
     * Has the visitor accepted cookies?
     *
     * The choice really lives in localStorage, which is invisible from here, so
     * resources/js/cookie-consent.js mirrors it into an unencrypted `cookie_consent`
     * cookie (exempted in bootstrap/app.php) purely so this check is possible.
     *
     * Where consent_required() is false no banner is ever shown, so this is always false
     * and the attribution cookies are simply never written. That is deliberate: an install
     * with nothing consent-gated turned on should not have to ask about cookies it can
     * manage without.
     */
    private function hasConsent(Request $request): bool
    {
        return $request->cookie('cookie_consent') === 'granted';
    }

    /**
     * Write one of the 30-day attribution cookies, if the visitor allowed it.
     *
     * These are marketing cookies, not strictly necessary ones, so ePrivacy Art. 5(3) puts
     * them behind consent. The matching session entry is written either way and every
     * consumer reads `session(...) ?? $request->cookie(...)` (TicketController,
     * EventController), so declining costs cross-session attribution only - attribution
     * within the visit still works, carried by the strictly-necessary session cookie.
     */
    private function rememberAttribution(Response $response, bool $consented, string $name, string $value): void
    {
        if (! $consented || ! method_exists($response, 'cookie')) {
            return;
        }

        $response->cookie($name, $value, self::COOKIE_MINUTES, '/', null, true, true, false, 'Lax');
    }

    /**
     * Expire attribution cookies the visitor has not consented to: ones set before this was
     * gated, and ones left behind when consent was withdrawn. Only touched when the request
     * actually carries one, so the ordinary request adds no headers at all.
     */
    private function forgetAttributionCookies(Request $request, Response $response, bool $consented): Response
    {
        if ($consented) {
            return $response;
        }

        foreach (self::ATTRIBUTION_COOKIES as $name) {
            if ($request->cookies->has($name)) {
                $response->headers->clearCookie($name, '/', null, true, true, 'Lax');
            }
        }

        return $response;
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

        $refererBase = $this->getBaseDomain($refererHost);
        $requestBase = $this->getBaseDomain($request->getHost());

        return strcasecmp($refererBase, $requestBase) === 0;
    }

    private function getBaseDomain(string $host): string
    {
        // Remove port if present
        $host = strtolower(preg_replace('/:\d+$/', '', $host));

        // IP addresses or localhost - return as-is
        if (filter_var($host, FILTER_VALIDATE_IP) || $host === 'localhost') {
            return $host;
        }

        // Extract last two segments (e.g., eventschedule.com from sub.eventschedule.com)
        $parts = explode('.', $host);
        if (count($parts) >= 2) {
            return implode('.', array_slice($parts, -2));
        }

        return $host;
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
