<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
