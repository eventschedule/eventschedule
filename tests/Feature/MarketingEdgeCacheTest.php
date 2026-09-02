<?php

namespace Tests\Feature;

use App\Http\Middleware\CacheableMarketingResponse;
use App\Http\Middleware\TrackMarketingVisit;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * The origin half of the edge-caching contract (docs/CACHING.md).
 *
 * An anonymous marketing GET must come back with NO Set-Cookie at all and a public,
 * s-maxage'd Cache-Control, and everything that could differ per visitor must keep today's
 * `no-cache, private` plus its session. Cloudflare's Cache Rule bypasses on the presence of
 * a `laravel_session` or a `remember_*` cookie, so these two halves together are what stops
 * a cached anonymous page ever reaching a signed-in visitor.
 *
 * The two support routes a cached page calls (the visit beacon and the docs search index)
 * are covered here too: they are never public, but they must never start a session either,
 * or the first one a visitor triggers takes them off the edge for the rest of their visit.
 */
class MarketingEdgeCacheTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // The eligibility rule compares the request host against _base_domain(), which is
        // derived from app.url - so a machine with a different APP_URL must not change the
        // answer. Pinning it also fixes the host $this->get() builds its URLs against.
        config(['app.url' => 'https://eventschedule.test']);
    }

    public function test_anonymous_marketing_page_is_cookie_free_and_publicly_cacheable(): void
    {
        $response = $this->get('/pricing');

        $response->assertOk();

        $this->assertSame([], $response->headers->all('set-cookie'), 'An edge-cacheable page must set no cookies at all.');

        $cacheControl = $response->headers->get('Cache-Control');
        $this->assertStringContainsString('public', $cacheControl);
        $this->assertStringContainsString('s-maxage=600', $cacheControl);
        $this->assertStringContainsString('max-age=0', $cacheControl);
        $this->assertStringNotContainsString('private', $cacheControl);

        // Firefox and Safari apply stale-while-revalidate in the BROWSER cache on an ordinary
        // navigation, so with it a visitor who signs in keeps being painted the stored
        // anonymous page. Serving stale belongs to Cloudflare's own setting, not the header.
        $this->assertStringNotContainsString('stale-while-revalidate', $cacheControl);
    }

    /**
     * The array session driver still has to produce a working token, or every form on a
     * cached page (and every fetch() reading the meta tag) breaks. This is the check that
     * would have caught swapping the driver out for "no session at all".
     */
    public function test_a_cached_page_still_renders_a_csrf_token(): void
    {
        $response = $this->get('/pricing');

        $this->assertSame(1, preg_match('/<meta name="csrf-token" content="([^"]*)"/', $response->getContent(), $matches));
        $this->assertNotSame('', $matches[1]);
        $this->assertGreaterThanOrEqual(32, strlen($matches[1]));
    }

    public function test_a_request_carrying_a_session_cookie_keeps_its_session(): void
    {
        $response = $this->withCookie(config('session.cookie'), 'whatever')->get('/pricing');

        $response->assertOk();
        $this->assertStringContainsString('private', $response->headers->get('Cache-Control'));
        $this->assertCookieWasSet($response, config('session.cookie'));
    }

    /**
     * ?lang= must stay dynamic: SetUserLanguage persists the choice in the session for every
     * later request, so the request that makes the choice cannot be cookie-free.
     */
    public function test_a_language_query_string_stays_private(): void
    {
        $response = $this->get('/pricing?lang=fr');

        $response->assertOk();
        $this->assertStringContainsString('private', $response->headers->get('Cache-Control'));
        $this->assertCookieWasSet($response, config('session.cookie'));
    }

    public function test_form_and_per_visitor_pages_stay_private(): void
    {
        foreach (['/contact', '/browse', '/search'] as $path) {
            $response = $this->get($path);

            $response->assertOk();
            $this->assertStringContainsString(
                'private',
                $response->headers->get('Cache-Control'),
                $path.' must never be held in a shared cache.'
            );
            $this->assertCookieWasSet($response, config('session.cookie'), $path.' must keep its session.');
        }
    }

    /**
     * A response that queues a cookie of its own is visitor-specific by definition, so it
     * keeps its private headers AND its cookies.
     *
     * The case exercised here reaches that check for real: a visitor carrying an attribution
     * cookie with no consent makes CaptureUtmParameters queue an expiry for it, on an
     * otherwise perfectly eligible request (no query string, no session). Without the
     * post-response cookie check the page would be marked public and that expiry would be
     * handed to every later visitor.
     */
    public function test_a_response_that_sets_any_other_cookie_stays_private(): void
    {
        $response = $this->withCookie('utm_params', '{"utm_source":"old"}')->get('/pricing');

        $response->assertOk();
        $this->assertStringContainsString('private', $response->headers->get('Cache-Control'));
        $this->assertCookieWasSet($response, 'utm_params');
    }

    /**
     * The same rule from the consented side, where the cookie is a real write rather than an
     * expiry. Excluded twice over (query string, then the response cookie).
     */
    public function test_a_response_that_writes_a_consented_attribution_cookie_stays_private(): void
    {
        $response = $this->withUnencryptedCookie('cookie_consent', 'granted')
            ->get('/pricing?utm_source=newsletter');

        $response->assertOk();
        $this->assertStringContainsString('private', $response->headers->get('Cache-Control'));
        $this->assertCookieWasSet($response, 'utm_params');
    }

    /**
     * actingAs() authenticates without putting a session cookie on the REQUEST, so every
     * pre-response check passes and only the post-response auth()->check() stands between a
     * signed-in header and a shared cache. Pinned because it is the one eligibility check
     * that cannot be made before the response exists.
     */
    public function test_an_authenticated_request_is_never_marked_cacheable(): void
    {
        $response = $this->actingAs(\App\Models\User::factory()->create())->get('/pricing');

        $response->assertOk();
        $this->assertStringContainsString('private', $response->headers->get('Cache-Control'));
    }

    /**
     * A visitor who accepted cookies must still get cached pages.
     *
     * Their first marketing page writes an attribution cookie, so it cannot be public - but
     * the response must not also hand them a session cookie, because the Cloudflare rule
     * bypasses the cache for anything carrying one and the session behind it was an in-memory
     * store that was never written. Without that, accepting cookies opted a visitor out of
     * edge caching for good.
     */
    public function test_a_consented_visitor_is_cacheable_from_the_second_page(): void
    {
        $first = $this->withUnencryptedCookie('cookie_consent', 'granted')->get('/pricing');

        $first->assertOk();
        $this->assertStringContainsString('private', $first->headers->get('Cache-Control'));
        $this->assertCookieWasSet($first, 'utm_landing_page');
        $this->assertNotContains(
            config('session.cookie'),
            array_map(fn ($cookie) => $cookie->getName(), $first->headers->getCookies()),
            'An eligible request must never be handed the id of a session that was never stored.'
        );

        // The next page carries the attribution cookie, so nothing new is written and the
        // response is fully cacheable. flushSession() so only the COOKIE can be carrying the
        // first-touch record - otherwise this passes on the session and pins nothing.
        $this->flushSession();

        $second = $this->withUnencryptedCookie('cookie_consent', 'granted')
            ->withCookie('utm_landing_page', 'pricing')
            ->get('/faq');

        $second->assertOk();
        $this->assertSame([], $second->headers->all('set-cookie'));
        $this->assertStringContainsString('public', $second->headers->get('Cache-Control'));
    }

    /**
     * Marketing routes are registered under Route::domain(_base_domain()) on a real nexus, so
     * they cannot answer anywhere else - but the testing branch registers them domain-less,
     * and a cached page handed to the app subdomain or a tenant host would be a leak.
     */
    public function test_a_non_apex_host_is_never_marked_cacheable(): void
    {
        foreach (['app.eventschedule.test', 'someschedule.eventschedule.test', 'example.com'] as $host) {
            $response = $this->get('https://'.$host.'/pricing');

            $this->assertStringContainsString(
                'private',
                $response->headers->get('Cache-Control'),
                $host.' is not the marketing apex.'
            );
        }
    }

    public function test_a_not_found_response_stays_private(): void
    {
        $response = $this->get('/a/b/c/d/e');

        $response->assertNotFound();
        $this->assertStringContainsString('private', $response->headers->get('Cache-Control'));
    }

    /**
     * The three marketing POST routes keep CSRF verification; only the beacon drops it.
     *
     * Asserted against the router rather than by posting: ValidateCsrfToken short-circuits on
     * runningUnitTests(), so a live POST cannot tell an exempted route from a protected one.
     * gatherRouteMiddleware() resolves the web group AND applies withoutMiddleware(), which is
     * exactly the list the kernel will run.
     */
    public function test_marketing_post_routes_keep_csrf_protection(): void
    {
        foreach (['marketing.discovery.toggle', 'marketing.federation.block', 'marketing.federation.click'] as $name) {
            $this->assertContains(
                ValidateCsrfToken::class,
                $this->middlewareFor($name),
                $name.' must keep CSRF verification.'
            );
        }

        $beacon = $this->middlewareFor('marketing.visit');

        $this->assertNotContains(ValidateCsrfToken::class, $beacon, 'sendBeacon cannot carry a CSRF token.');
        $this->assertNotEmpty(
            preg_grep('/ThrottleRequests:120,1$/', $beacon),
            'The beacon must be rate limited: '.implode(', ', $beacon)
        );
    }

    public function test_the_beacon_endpoint_accepts_a_post_without_a_csrf_token(): void
    {
        $response = $this->postJson('/marketing/visit', ['route' => 'marketing.pricing']);

        $response->assertNoContent();
    }

    /**
     * The eligibility rule is a constant list, not a fingerprint of one page: this pins the
     * headers the Cloudflare rule in docs/CACHING.md is written against.
     */
    public function test_the_cache_control_header_is_the_documented_one(): void
    {
        $this->assertSame(
            'public, max-age=0, s-maxage=600',
            CacheableMarketingResponse::CACHE_CONTROL
        );
    }

    /**
     * The beacon defeated the whole scheme after exactly one page.
     *
     * It is a POST, so it was never eligible, so its 204 carried `Set-Cookie: laravel_session`
     * and wrote a sessions row per anonymous visitor. The next navigation carried that cookie,
     * Cloudflare's rule bypassed on it and the origin refused to mark the page public, in
     * exchange for a session nothing ever reads.
     */
    public function test_the_beacon_response_is_stateless(): void
    {
        $response = $this->postJson('/marketing/visit', ['route' => 'marketing.pricing']);

        $response->assertNoContent();
        $this->assertSame([], $response->headers->all('set-cookie'), 'The beacon must not start a session.');

        // The rejection path too: a 422 that hands out a session cookie costs just as much.
        $rejected = $this->postJson('/marketing/visit', ['route' => 'marketing.no-such-page']);

        $rejected->assertStatus(422);
        $this->assertSame([], $rejected->headers->all('set-cookie'));

        // ...and it is still private. Nothing about being stateless makes a beacon shareable.
        $this->assertStringContainsString('private', $response->headers->get('Cache-Control'));
    }

    /**
     * The other half: the docs search index is JSON fetched BY a cached page, and it used to
     * answer with both framework cookies.
     */
    public function test_the_docs_search_index_is_stateless_and_keeps_its_own_cache_header(): void
    {
        $response = $this->get('/docs/search-index.json');

        $response->assertOk();
        $this->assertSame([], $response->headers->all('set-cookie'), 'The search index must not start a session.');

        // Its controller sets an hour of shared caching; the 10-minute page header must not
        // overwrite it, which is what keeping it in EXCLUDED_ROUTES buys. (Symfony reorders
        // the directives, so this is asserted piecewise rather than as one string.)
        $cacheControl = $response->headers->get('Cache-Control');
        $this->assertStringContainsString('public', $cacheControl);
        $this->assertStringContainsString('max-age=3600', $cacheControl);
        $this->assertStringNotContainsString('s-maxage', $cacheControl);
    }

    /**
     * Statelessness is for anonymous visitors only. A signed-in one already carries a session
     * cookie (so there is nothing left to protect at the edge) and must keep their session:
     * dropping it here would log them out on their own page-view beacon.
     */
    public function test_a_stateless_route_keeps_the_session_of_a_visitor_who_has_one(): void
    {
        // withCredentials(), or postJson() sends no cookies at all: the harness drops them
        // from a JSON request unless credentials are asked for, which would make this pass
        // for the wrong reason.
        $response = $this->withCredentials()
            ->withCookie(config('session.cookie'), 'whatever')
            ->postJson('/marketing/visit', ['route' => 'marketing.pricing']);

        $response->assertNoContent();
        $this->assertCookieWasSet($response, config('session.cookie'));
    }

    /**
     * Two lists, one meaning: a marketing.* route that is not a page. They are declared
     * separately because they answer different questions (may it hold a session / is it a
     * page view), and drift between them is exactly how the search-index JSON ended up
     * counted as a docs page view while being excluded from the cache.
     */
    public function test_the_stateless_and_non_page_route_lists_agree(): void
    {
        $this->assertSame(
            TrackMarketingVisit::NON_PAGE_ROUTES,
            CacheableMarketingResponse::STATELESS_ROUTES
        );
    }

    /**
     * @return array<int, string>
     */
    private function middlewareFor(string $routeName): array
    {
        $route = Route::getRoutes()->getByName($routeName);

        $this->assertNotNull($route, $routeName.' is not registered.');

        return app('router')->gatherRouteMiddleware($route);
    }

    private function assertCookieWasSet($response, string $name, string $message = ''): void
    {
        $names = array_map(fn ($cookie) => $cookie->getName(), $response->headers->getCookies());

        $this->assertContains($name, $names, $message ?: "Expected a {$name} cookie on the response.");
    }
}
