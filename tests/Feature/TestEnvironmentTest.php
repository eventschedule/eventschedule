<?php

namespace Tests\Feature;

use App\Utils\GitHubUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Asserts the test harness itself, the way TestDatabaseSchemaTest does for the schema rules.
 *
 * The host a relative $this->get('/path') reaches and the base domain ResolveCustomDomain
 * compares it against come from two different reads of APP_URL: one at bootstrap
 * (SetRequestForConsole synthesizes the app's request from it, and
 * MakesHttpRequests::prepareUrlForRequest() is trim(url($uri), '/')) and one per request
 * (_base_domain()). .env.example ships APP_URL empty and CI copies it verbatim, so they were
 * 'localhost' and 'eventschedule.test' on the build while being identical on every developer's
 * machine - 18 tests that could only ever fail in CI, and that failed as an opaque 404 with an
 * empty CSRF meta rather than as anything resembling a URL problem.
 *
 * Nothing is pinned here on purpose: this asserts the DEFAULT every other test inherits, so
 * removing phpunit.xml's APP_URL entry or tests/bootstrap.php's $_SERVER mirror fails it. A test
 * that pins app.url for itself must use TestCase::pinAppUrl().
 */
class TestEnvironmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_test_client_reaches_a_host_this_install_answers_on(): void
    {
        $this->assertNotSame(
            '',
            (string) config('app.url'),
            'phpunit.xml must pin APP_URL: .env.example ships it empty and CI copies it verbatim.'
        );

        $host = parse_url(url('/'), PHP_URL_HOST);
        $base = _base_domain();

        // The rule ResolveCustomDomain actually applies, not a stricter one: _base_domain()
        // strips an app./www./blog./demo. prefix, so an APP_URL on one of those subdomains is
        // legitimate and must not fail here.
        $this->assertTrue(
            $host === $base || str_ends_with((string) $host, '.'.$base),
            "A relative \$this->get() reaches {$host}, which ResolveCustomDomain reads as an unknown "
            ."custom domain of {$base} and 404s before the web group runs."
        );

        // The end-to-end proof in one line: a marketing GET that nothing intercepts.
        $this->get('/pricing')->assertOk();
    }

    /**
     * No page render may reach the network.
     *
     * GitHubUtils::getStars() is called by two view composers - layouts.app-admin and
     * marketing.partials.header - so it sat on the render path of nearly every admin and marketing
     * page. Its hour-long cache makes that one call an hour in production, but CACHE_STORE is
     * `array` here and the container is rebuilt per test method, so the cache never hit and the
     * suite made one live GET to api.github.com per rendered page. Unauthenticated GitHub allows
     * 60 an hour: the run exhausted that almost immediately and every render afterwards blocked
     * for the full timeout(5), turning a ~20s suite into minutes of a process sitting at 0% CPU
     * inside curl_exec - and making it fail outright without a network.
     *
     * The test above renders /pricing, so it was one of the callers.
     *
     * Http::fake() rather than preventStrayRequests(): a fake records what WOULD have been sent,
     * so this fails with the URL in the message instead of an exception from inside a composer.
     * It only sees the Http facade, so a raw curl_exec (GeminiUtils and friends) would slip past.
     */
    public function test_no_page_render_reaches_the_network(): void
    {
        Http::fake();

        // The function itself, which is what both composers call.
        $this->assertNull(GitHubUtils::getStars());

        // End to end, on the marketing header composer's own path.
        $this->get('/pricing')->assertOk();

        Http::assertNothingSent();
    }

    /**
     * The other half of the harness that only CI can disprove.
     *
     * public/build is gitignored and .github/workflows/test.yml never builds assets, so the real
     * Vite throws ViteManifestNotFoundException on CI and every page rendering a layout 500s.
     * TestCase stubs Vite out for that reason - but withoutVite() swaps into the CURRENT container,
     * so an app rebuilt mid-test drops the stub. TestCase::refreshApplication() re-applies it; this
     * fails if that override is ever removed.
     *
     * Asserting on the rendered output rather than the bound class makes it a real guard on both
     * sides: with a manifest present the real Vite returns tags, and without one it throws.
     */
    public function test_the_vite_stub_survives_an_application_refresh(): void
    {
        $this->refreshApplication();

        $this->assertSame(
            '',
            (string) app(\Illuminate\Foundation\Vite::class)(['resources/js/app.js']),
            'refreshApplication() dropped the withoutVite() stub, so every @vite page 500s on CI.'
        );
    }
}
