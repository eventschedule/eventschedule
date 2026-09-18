<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\URL;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        // Must run BEFORE parent::setUp(): booting the app fires RefreshDatabase
        // (migrate:fresh), which would wipe whatever database is configured.
        // Accepts the per-session schema tests/bootstrap.php hands out
        // (eventschedule_test_<token>); the dev database still fails. The predicate
        // lives in TestDatabase so TestDatabaseSchemaTest can pin it.
        if (! TestDatabase::isDedicatedTestSchema((string) getenv('DB_DATABASE'))) {
            self::fail('Refusing to run: DB_DATABASE must be a dedicated *_test database (see phpunit.xml and tests/bootstrap.php). Got: '.getenv('DB_DATABASE'));
        }

        parent::setUp();

        // Its counts are memoized for the request; tests share a process, so a
        // previous test's totals would otherwise carry over.
        \App\Services\AdminAlertService::flush();

        // Same reasoning, and the same trap: these two resolve once per process and are NOT
        // reset by RefreshDatabase. A test that config()s an amount or a currency and then
        // asserts on rendered output would silently read whatever an earlier test warmed - so
        // the failure lands in an innocent file and only shows up in some orderings.
        \App\Utils\PlatformCurrency::flush();
        \App\Utils\PlatformPricing::flush();

        // Memoizes whether scheduled_task_runs exists; RefreshDatabase rebuilds the schema under it.
        \App\Services\ScheduledTaskRecorder::flush();
    }

    /**
     * Rebuild the application, keeping the Vite stub in place.
     *
     * withoutVite() swaps its stub into the CURRENT container, and refreshApplication() builds a
     * brand new one, so a test that rebuilds the app mid-body silently gets the real Vite back.
     * That is invisible locally, where public/build/manifest.json exists, and a bare 500 on CI,
     * where public/build is gitignored and the workflow never builds assets - which is the whole
     * reason withoutVite() is here at all. RouteLoadTest rebuilds the app to register the hosted
     * subdomain routes, and the guest page it then requests renders @vite through
     * layouts/app.blade.php.
     *
     * Applying it here rather than in setUp() covers both: Laravel's setUpTheTestEnvironment()
     * calls this whenever $this->app is unset, which is every fresh test.
     */
    protected function refreshApplication(): void
    {
        parent::refreshApplication();

        $this->withoutVite();
    }

    /**
     * Point the app at a URL, for a test that needs a specific base domain.
     *
     * config(['app.url' => ...]) on its own is a trap. Laravel's SetRequestForConsole synthesized
     * the app's request from APP_URL when the app booted, and MakesHttpRequests::prepareUrlForRequest()
     * is trim(url($uri), '/'), so the host a relative $this->get('/path') reaches was fixed before
     * the test body ran. Moving app.url alone therefore moves _base_domain() and leaves that host
     * behind, and with IS_HOSTED=true ResolveCustomDomain reads the mismatch as an unknown custom
     * domain and aborts 404 before the session middleware ever runs. Locally the pin is usually a
     * no-op (it matches .env) so the split only ever showed up on CI.
     *
     * Forcing the root as well keeps the two halves in step. The alternative is to drive absolute
     * URLs ($this->get('https://host/path')), which is what SitemapTest and HostedLoginRedirectTest do.
     *
     * This moves the HOST only. AppServiceProvider forces the https scheme whenever app.env is not
     * 'local', and phpunit pins it to 'testing', so passing an http:// URL here still generates
     * https:// links - pass one to config() directly if the scheme is what a test is pinning.
     */
    protected function pinAppUrl(string $url): void
    {
        config(['app.url' => $url]);
        URL::forceRootUrl($url);
    }

    /**
     * preg_match(), with a PCRE error reported as a PCRE error.
     *
     * preg_match() returns FALSE - not 0 - when it runs out of JIT stack, backtracks or recursion,
     * and `if (! preg_match(...))` at the call site cannot tell the two apart. So an unsound
     * pattern over a rendered page does not report itself: it reports the PAGE as wrong, for every
     * value the caller was checking at once.
     *
     * That is not hypothetical here. FederationSettingsCardTest matched `<li[^>]*>` against a whole
     * admin page; `<li[^>]*>` also matches `<link ...>`, of which layouts/app.blade.php alone
     * renders five in <head>, and the first real `</li>` is in the nav on the far side of tens of
     * KB of inline <style>. The scan blew the limit, preg_match() returned false, and all three
     * reported as carrying the wrong badge - including one whose state is a single
     * `if ($event->federated_at)` and could not have been wrong.
     *
     * It survived two rounds of debugging because the limit that trips is pcre.jit's 32KB stack,
     * and pcre.jit is an ini setting, not anything the repo or .env controls: CI runs PHP's default
     * of 1, a dev machine may ship 0, and 0 tolerates a span roughly three times larger. phpunit.xml
     * now pins it so both agree - but prefer parsing over a big regex, and use this when a regex is
     * genuinely the right tool.
     */
    protected function pregMatchOrFail(string $pattern, string $subject, string $context = ''): bool
    {
        $result = preg_match($pattern, $subject);

        $this->assertNotFalse($result, trim($context.' PCRE error: '.preg_last_error_msg()
            .' - the pattern is unsound for a subject this size ('.strlen($subject)
            .' bytes). The subject is not necessarily wrong.'));

        return (bool) $result;
    }
}
