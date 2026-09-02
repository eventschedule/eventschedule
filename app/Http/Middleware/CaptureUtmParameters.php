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

    /**
     * First-touch attribution written by the BROWSER, in layouts/marketing.blade.php.
     *
     * Anonymous marketing HTML is cached at the edge (CacheableMarketingResponse), so on most
     * marketing page views this middleware never runs and there is no server session to hold
     * the marketing-to-signup hop. This cookie carries exactly what that session used to -
     * landing page, off-site referrer, the utm_* values and ?ref= - and nothing else, which is
     * why it is strictly necessary in the same sense the session cookie it stands in for was
     * and is deliberately NOT gated on consent. The consented 30-day ATTRIBUTION_COOKIES above
     * are a different thing (cross-session marketing attribution) and are unchanged.
     *
     * Exempt from cookie encryption in bootstrap/app.php, since the browser writes it.
     */
    public const CLIENT_COOKIE = 'es_attribution';

    /**
     * The keys the client cookie may carry. Anything else in it is discarded.
     */
    private const CLIENT_UTM_KEYS = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term'];

    public function handle(Request $request, Closure $next): Response
    {
        // The guest appointment surfaces authenticate on a 32-char secret in the PATH, and this
        // middleware would persist that path as `utm_landing_page` into a 30-day cookie, the session,
        // and eventually a `landing_page` column. Attribution from a booking-management link is
        // meaningless anyway - the guest has already booked - so skip the whole middleware.
        if (str_starts_with($request->path(), 'appointment/')) {
            return $next($request);
        }

        // The beacon POST and the docs search-index JSON are fetched BY a marketing page, so
        // neither is a landing page or a referrer. Recording one as either would be wrong in
        // the session and worse in a 30-day cookie, and a Set-Cookie on the JSON route's
        // `public, max-age=3600` response would be handed to every visitor after it.
        if (in_array($request->route()?->getName(), TrackMarketingVisit::NON_PAGE_ROUTES, true)) {
            return $next($request);
        }

        // Whatever the browser recorded on the visitor's first marketing page comes first,
        // because it happened first. Anonymous marketing HTML is served from the edge, so
        // there is no server session on the pages that matter and the FIRST request with a
        // real session is typically /sign_up itself - which used to record `sign_up` as the
        // landing page and the same-domain hop as no referrer at all, then win every read
        // site's `session ?? cookie ?? es_attribution` chain. Seeding here puts the cookie
        // exactly where that session write used to go, so every consumer is covered without
        // a fallback of its own: SocialAuthController (Google sign-up) and TicketController's
        // two stub-account paths read the session and the consented cookies only.
        $this->seedSessionFromClientAttribution($request);

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
                'utm_source' => self::sanitize($request->query('utm_source')),
                'utm_medium' => self::sanitize($request->query('utm_medium')),
                'utm_campaign' => self::sanitize($request->query('utm_campaign')),
                'utm_content' => self::sanitize($request->query('utm_content')),
                'utm_term' => self::sanitize($request->query('utm_term')),
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

        // These two run on EVERY plain GET, so the cookie has to count as first-touch evidence
        // alongside the session, for two reasons that arrived together with edge caching:
        //
        //  - Correctness. A consented visitor whose session has expired (2 hours) would
        //    otherwise have the cookie rewritten with whatever page they are on now, quietly
        //    turning 30-day first-touch attribution into last-touch.
        //  - Cacheability. An anonymous marketing GET runs against an in-memory session
        //    (CacheableMarketingResponse), so the session can never remember anything - and a
        //    response that writes a cookie is not edge-cacheable. Without this, a consented
        //    visitor would rewrite the cookie on every page and never be served a cached one.
        //
        // Whichever copy exists first wins, and it is never overwritten while it lives.

        // Capture referrer independently of UTM params (first-touch), filtering same-domain
        if (! $request->session()->has('utm_referrer_url') && ! $request->cookie('utm_referrer_url')) {
            $referer = $request->header('Referer');
            if ($referer && ! $this->isSameDomain($referer, $request)) {
                $referrerUrl = mb_substr(trim($referer), 0, 2048);
                $request->session()->put('utm_referrer_url', $referrerUrl);
                $this->rememberAttribution($response, $consented, 'utm_referrer_url', $referrerUrl);
            }
        }

        // Capture landing page on first visit (GET only, even without UTM params)
        if ($request->isMethod('GET')
            && ! $request->session()->has('utm_landing_page')
            && ! $request->cookie('utm_landing_page')) {
            $landingPage = mb_substr($request->path(), 0, 2048);
            $request->session()->put('utm_landing_page', $landingPage);
            $this->rememberAttribution($response, $consented, 'utm_landing_page', $landingPage);
        }

        return $this->forgetAttributionCookies($request, $response, $consented);
    }

    /**
     * Copy the browser-written first-touch cookie into the session, before anything below
     * can record a first touch of its own.
     *
     * Priority is unchanged and deliberate: the session wins if it already holds a key, and
     * the consented 30-day cookies win over this one, because those carry an EARLIER first
     * touch (up to 30 days back) while `es_attribution` is scoped to the browser session.
     * That is the same order every read site applies; all this does is apply it soon enough
     * that the first-touch capture below cannot overwrite the answer first.
     */
    private function seedSessionFromClientAttribution(Request $request): void
    {
        if (! $request->hasSession()) {
            return;
        }

        // On a cacheable request the session is an in-memory store that is thrown away, so
        // seeding it would achieve nothing except suppressing the consented 30-day cookie
        // the visitor's first marketing page is meant to write.
        if ($request->attributes->get(CacheableMarketingResponse::STATELESS_ATTRIBUTE, false)) {
            return;
        }

        $client = self::clientAttribution($request);
        $session = $request->session();

        if (! empty($client['utm_params'])
            && ! $session->has('utm_params')
            && ! $request->cookie('utm_params')) {
            $session->put('utm_params', $client['utm_params']);
        }

        foreach (['utm_referrer_url', 'utm_landing_page'] as $key) {
            if ($client[$key] !== null && ! $session->has($key) && ! $request->cookie($key)) {
                $session->put($key, $client[$key]);
            }
        }

        // No 30-day twin for this one: referral codes were session-only, which is why a
        // referred visitor who landed on a cached page credited nobody.
        if ($client['referral_code'] !== null && ! $session->has('referral_code')) {
            $session->put('referral_code', $client['referral_code']);
        }
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
        if ($consented || ! method_exists($response, 'cookie')) {
            return $response;
        }

        foreach (self::ATTRIBUTION_COOKIES as $name) {
            if ($request->cookies->has($name)) {
                // Through the CookieJar, NOT Symfony's clearCookie(). A Set-Cookie deletion only
                // matches on (name, DOMAIN, path), and rememberAttribution() writes these with a
                // null domain that the jar substitutes config('session.domain') into - which on
                // hosted is '.<base domain>'. clearCookie() takes the null literally and emits a
                // host-only expiry, so withdrawing consent looked like it worked while the real
                // cookie kept being sent for its full 30 days. Reading the config through the jar
                // also keeps this correct on a custom domain, where ResolveCustomDomain nulls
                // session.domain per request.
                $response->cookie(cookie()->forget($name));
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

    private static function sanitize(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        // Strip control characters, trim, and limit length
        $value = preg_replace('/[\x00-\x1F\x7F]/u', '', trim($value));

        return mb_substr($value, 0, 255) ?: null;
    }

    /**
     * Read the browser-written first-touch attribution cookie.
     *
     * The LAST fallback at every read site, after the session and after the consented 30-day
     * cookies, so a visitor whose page was served dynamically keeps exactly today's behaviour
     * and one served from the edge still gets attributed. Defensive by construction: the value
     * is client-controlled, so malformed JSON is ignored, unknown keys are dropped, and every
     * value goes through the same sanitiser and the same length caps the session path uses.
     *
     * @return array{utm_params: array<string, string|null>, utm_referrer_url: string|null, utm_landing_page: string|null, referral_code: string|null}
     */
    public static function clientAttribution(Request $request): array
    {
        $empty = [
            'utm_params' => [],
            'utm_referrer_url' => null,
            'utm_landing_page' => null,
            'referral_code' => null,
        ];

        $raw = $request->cookie(self::CLIENT_COOKIE);

        // 4 KB is the practical per-cookie ceiling; the writer caps itself at ~2 KB.
        if (! is_string($raw) || $raw === '' || strlen($raw) > 4096) {
            return $empty;
        }

        $data = json_decode($raw, true);

        if (! is_array($data)) {
            return $empty;
        }

        $utmParams = [];
        foreach (self::CLIENT_UTM_KEYS as $key) {
            $value = isset($data[$key]) && is_string($data[$key]) ? self::sanitize($data[$key]) : null;
            $utmParams[$key] = $value;
        }

        // All null means the visitor simply arrived without campaign parameters; hand back an
        // empty array so the read sites' `empty($utmParams)` fallback chain keeps working.
        if (! array_filter($utmParams, fn ($value) => $value !== null)) {
            $utmParams = [];
        }

        $referralCode = null;
        if (isset($data['ref']) && is_string($data['ref'])) {
            $referralCode = substr(preg_replace('/[^a-zA-Z0-9]/', '', $data['ref']), 0, 8) ?: null;
        }

        return [
            'utm_params' => $utmParams,
            'utm_referrer_url' => self::sanitizeUrlish($data['referrer'] ?? null),
            'utm_landing_page' => self::sanitizeUrlish($data['landing'] ?? null),
            'referral_code' => $referralCode,
        ];
    }

    /**
     * Same shape the session path stores: control characters stripped, trimmed, capped at the
     * 2048 characters users.referrer_url / users.landing_page are sized for.
     */
    private static function sanitizeUrlish(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = preg_replace('/[\x00-\x1F\x7F]/u', '', trim($value));

        return mb_substr((string) $value, 0, 2048) ?: null;
    }
}
