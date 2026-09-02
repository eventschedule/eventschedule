<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Makes anonymous marketing (WP) HTML cacheable by a shared cache (Cloudflare).
 *
 * Every marketing page used to respond `no-cache, private` with a `laravel_session` and an
 * `XSRF-TOKEN` cookie, so the CDN could not hold a single one of the ~150 pages and every
 * visitor paid a 0.4 to 0.6 s origin round trip before the first byte. That is the floor
 * under every Core Web Vital on the whole marketing site.
 *
 * For a request that is provably anonymous and provably identical for every visitor this
 * middleware:
 *   1. swaps the session store to `array` for the request, BEFORE StartSession runs, so
 *      csrf_token(), session()->has() and everything downstream keep working against an
 *      in-memory store that is never persisted;
 *   2. strips the session and CSRF cookies from the response (the `array` driver still
 *      queues them - Laravel's StartSession::sessionIsPersistent() only checks that the
 *      driver is non-null, and ValidateCsrfToken queues XSRF-TOKEN unconditionally); and
 *   3. sets `Cache-Control: public, max-age=0, s-maxage=600`, so browsers always
 *      revalidate but the edge serves from cache for 10 minutes.
 *
 * The two support routes the cached pages themselves call - the page-view beacon and the
 * docs search-index JSON (STATELESS_ROUTES) - get steps 1 and 2 but never step 3. They are
 * not pages, they hold nothing per visitor, and handing either one a session cookie is what
 * would end the visitor's time at the edge: Cloudflare's Cache Rule bypasses on the presence
 * of a `laravel_session` cookie, so a beacon fired from the FIRST cached page would make
 * every page after it dynamic.
 *
 * Anything that could differ per visitor keeps today's behaviour: a request carrying a
 * session or remember-me cookie, any query string at all (`?lang=`, `?utm_*`, `?ref=`),
 * the form/search/browse pages, a non-200, and any response that set a cookie of its own.
 * Cloudflare is configured to bypass the cache whenever a `laravel_session` or a
 * `remember_*` cookie is present, so a signed-in or remembered visitor never sees a cached
 * anonymous page. See docs/CACHING.md.
 *
 * Where the route name comes from: this is a `web` GROUP middleware, not a global one, so
 * the router has already matched and bound the route by the time it runs - `$request->route()`
 * is populated before `$next` as well as after. That is the single source of truth for
 * eligibility, so the pre-`$next` session decision and the post-`$next` header decision can
 * never disagree.
 */
class CacheableMarketingResponse
{
    /**
     * Browsers revalidate on every navigation (max-age=0) while the shared cache serves a
     * stored copy for 10 minutes.
     *
     * Deliberately no `stale-while-revalidate`: Firefox and Safari honour it in the BROWSER
     * cache on a normal navigation, so a visitor who signed in would keep being painted the
     * stored anonymous copy (guest header, guest-only scripts) for as long as it lasted.
     * Serving stale while revalidating is a shared-cache behaviour and belongs to
     * Cloudflare's own "Serve stale content" setting, where no browser can act on it.
     */
    public const CACHE_CONTROL = 'public, max-age=0, s-maxage=600';

    /**
     * Set on the request whenever the session driver was swapped for `array`, so anything
     * downstream can tell that the session it is writing to is a throwaway in-memory store
     * (CaptureUtmParameters reads this). The configured driver name cannot answer that
     * question - `phpunit.xml` runs the whole suite on the `array` driver.
     */
    public const STATELESS_ATTRIBUTE = 'marketing_stateless_session';

    /**
     * Marketing routes that must never be held in a shared cache: two carry a form that
     * needs a per-visitor CSRF token, one renders per-visitor query results, and the
     * search index is JSON that sets its OWN, much longer, shared-cache header - the page
     * header below must never overwrite it.
     */
    private const EXCLUDED_ROUTES = [
        'marketing.contact',
        'marketing.search',
        'marketing.browse',
        'marketing.docs.search_index',
    ];

    /**
     * Marketing routes that hold nothing per visitor and so must never start a session.
     *
     * Both are fetched BY a cached page rather than being pages themselves, which is what
     * makes a session cookie on either of them so expensive: it is the cookie Cloudflare
     * bypasses the cache on, so one beacon would take the visitor off the edge for the rest
     * of their session in exchange for a session nothing ever reads. Method is irrelevant
     * here (the beacon is a POST), and neither response is ever marked public - the JSON
     * route sets its own `public, max-age=3600` and the beacon stays private.
     *
     * The same two routes are TrackMarketingVisit::NON_PAGE_ROUTES, for the same underlying
     * reason (neither is a page); MarketingEdgeCacheTest pins the two lists together.
     */
    public const STATELESS_ROUTES = [
        'marketing.visit',
        'marketing.docs.search_index',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        // Mutually exclusive by construction: marketing.visit is a POST and
        // marketing.docs.search_index is in EXCLUDED_ROUTES, so neither can be eligible.
        $stateless = $this->isStateless($request);
        $eligible = $this->isEligible($request);

        if ($stateless || $eligible) {
            // Must happen before StartSession resolves the driver, which is why this
            // middleware is prepended to the web group rather than appended.
            config(['session.driver' => 'array']);
            $request->attributes->set(self::STATELESS_ATTRIBUTE, true);
        }

        $response = $next($request);

        if (! $stateless && ! $eligible) {
            return $response;
        }

        // Always, for an eligible or stateless request: the session was an in-memory one that
        // was never written anywhere, so handing the browser its id is worse than useless. It
        // would make Cloudflare bypass the cache for the rest of that visitor's session (the
        // Cache Rule keys on the presence of a laravel_session cookie) in exchange for a
        // session that resolves to nothing. This matters most for a visitor who accepted
        // cookies: their FIRST marketing page writes an attribution cookie and so cannot be
        // marked public, and without this they would then bypass the edge on every page after
        // it too.
        $this->stripSessionCookies($response);

        if (! $eligible || ! $this->responseIsAnonymous($response)) {
            return $response;
        }

        $response->headers->set('Cache-Control', self::CACHE_CONTROL);

        return $response;
    }

    /**
     * A support route that a cached page calls, on a request that is provably anonymous.
     *
     * Everything the eligibility rule below asks about the VISITOR applies unchanged; what
     * does not apply is everything it asks about the response being a shareable page, since
     * these two are never marked public.
     */
    private function isStateless(Request $request): bool
    {
        if (! config('app.is_nexus') || ! $this->requestIsAnonymous($request)) {
            return false;
        }

        return in_array($request->route()?->getName(), self::STATELESS_ROUTES, true);
    }

    /**
     * Everything knowable before the response exists. All of it must hold.
     */
    private function isEligible(Request $request): bool
    {
        // Marketing pages only exist on the nexus.
        if (! config('app.is_nexus')) {
            return false;
        }

        if (! $request->isMethod('GET') && ! $request->isMethod('HEAD')) {
            return false;
        }

        // Any query string keeps the request dynamic: ?lang= must be able to persist the
        // choice in the session, and ?utm_*/?ref= must be able to write attribution.
        if ($request->getQueryString() !== null) {
            return false;
        }

        if (! $this->requestIsAnonymous($request)) {
            return false;
        }

        $routeName = $request->route()?->getName();

        if ($routeName === null || ! str_starts_with($routeName, 'marketing.')) {
            return false;
        }

        return ! in_array($routeName, self::EXCLUDED_ROUTES, true);
    }

    /**
     * Nothing on the request identifies a visitor, and the request reached the one host
     * marketing is served from.
     */
    private function requestIsAnonymous(Request $request): bool
    {
        // Belt and braces on top of the route match: marketing routes are registered under
        // Route::domain(_base_domain()) on a real nexus, so they can never answer on the app
        // subdomain, a tenant subdomain or a custom domain - but the testing branch registers
        // them without a domain, and a cached page served to the wrong host would be a leak.
        if (strcasecmp($request->getHost(), _base_domain()) !== 0) {
            return false;
        }

        if ($request->cookies->has(config('session.cookie'))) {
            return false;
        }

        if ($request->headers->has('Authorization')) {
            return false;
        }

        // A remember-me cookie logs the visitor in with no session cookie present, which
        // would both render the signed-in header into a shared copy and lose the session
        // the recaller login writes. The guard's cookie is `remember_<guard>_<sha1>`.
        foreach (array_keys($request->cookies->all()) as $name) {
            if (str_starts_with((string) $name, 'remember_')) {
                return false;
            }
        }

        return true;
    }

    /**
     * Only a genuinely anonymous, cookie-free 200 may be marked public. A response that set
     * any cookie of its own (attribution, a withdrawn-consent expiry, anything a controller
     * queued) is visitor-specific by definition, so it stays private and keeps that cookie.
     *
     * The session and CSRF cookies are already gone by the time this runs; they are named
     * here anyway so the check does not depend on that ordering.
     */
    private function responseIsAnonymous(Response $response): bool
    {
        if ($response->getStatusCode() !== 200) {
            return false;
        }

        if (auth()->check()) {
            return false;
        }

        $allowed = $this->strippableCookieNames();

        foreach ($response->headers->getCookies() as $cookie) {
            if (! in_array($cookie->getName(), $allowed, true)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Drop the two cookies the framework queues on every response. Removing by the cookie's
     * OWN path and domain rather than re-deriving them from config: Symfony keys its cookie
     * jar on (domain, path, name), so a mismatch would silently leave the Set-Cookie in
     * place and quietly make the whole page uncacheable at the edge.
     */
    private function stripSessionCookies(Response $response): void
    {
        $strippable = $this->strippableCookieNames();

        foreach ($response->headers->getCookies() as $cookie) {
            if (in_array($cookie->getName(), $strippable, true)) {
                $response->headers->removeCookie($cookie->getName(), $cookie->getPath(), $cookie->getDomain());
            }
        }
    }

    /**
     * @return array<int, string>
     */
    private function strippableCookieNames(): array
    {
        return [config('session.cookie'), 'XSRF-TOKEN'];
    }
}
