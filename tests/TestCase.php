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

        $this->withoutVite();

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
}
