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
 *   3. sets `Cache-Control: public, max-age=0, s-maxage=600, stale-while-revalidate=3600`,
 *      so browsers always revalidate but the edge serves from cache for 10 minutes.
 *
 * Anything that could differ per visitor keeps today's behaviour: a request carrying a
 * session or remember-me cookie, any query string at all (`?lang=`, `?utm_*`, `?ref=`),
 * the form/search/browse pages, a non-200, and any response that set a cookie of its own.
 * Cloudflare is configured to bypass the cache whenever a `laravel_session` cookie is
 * present, so a signed-in visitor never sees a cached anonymous page. See docs/CACHING.md.
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
     * stored copy for 10 minutes and may serve a stale one for an hour while it refreshes.
     */
    public const CACHE_CONTROL = 'public, max-age=0, s-maxage=600, stale-while-revalidate=3600';

    /**
     * Marketing routes that must never be held in a shared cache: two carry a form that
     * needs a per-visitor CSRF token, one renders per-visitor query results, and the
     * search index is JSON served to a script rather than a page.
     */
    private const EXCLUDED_ROUTES = [
        'marketing.contact',
        'marketing.search',
        'marketing.browse',
        'marketing.docs.search_index',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $eligible = $this->isEligible($request);

        if ($eligible) {
            // Must happen before StartSession resolves the driver, which is why this
            // middleware is prepended to the web group rather than appended.
            config(['session.driver' => 'array']);
        }

        $response = $next($request);

        if (! $eligible) {
            return $response;
        }

        // Always, for an eligible request: the session was an in-memory one that was never
        // written anywhere, so handing the browser its id is worse than useless. It would make
        // Cloudflare bypass the cache for the rest of that visitor's session (the Cache Rule
        // keys on the presence of a laravel_session cookie) in exchange for a session that
        // resolves to nothing. This matters most for a visitor who accepted cookies: their
        // FIRST marketing page writes an attribution cookie and so cannot be marked public,
        // and without this they would then bypass the edge on every page after it too.
        $this->stripSessionCookies($response);

        if (! $this->responseIsAnonymous($response)) {
            return $response;
        }

        $response->headers->set('Cache-Control', self::CACHE_CONTROL);

        return $response;
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

        $routeName = $request->route()?->getName();

        if ($routeName === null || ! str_starts_with($routeName, 'marketing.')) {
            return false;
        }

        return ! in_array($routeName, self::EXCLUDED_ROUTES, true);
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
