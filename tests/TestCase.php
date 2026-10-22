<?php

namespace Tests;

use Illuminate\Console\Events\CommandFinished;
use Illuminate\Console\Events\CommandStarting;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Queue\Events\JobExceptionOccurred;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\URL;
use Illuminate\Testing\TestResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Tests\Support\QueryGuard;

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

        // Whether its tables exist is memoized per process, and a test may drop one to prove the
        // app survives without it.
        \App\Services\ActiveDays::flush();

        // Same reasoning, and the same trap: these two resolve once per process and are NOT
        // reset by RefreshDatabase. A test that config()s an amount or a currency and then
        // asserts on rendered output would silently read whatever an earlier test warmed - so
        // the failure lands in an innocent file and only shows up in some orderings.
        \App\Utils\PlatformCurrency::flush();
        \App\Utils\PlatformPricing::flush();

        // And this one: which legal documents the operator has written, memoized per process. A
        // test that writes one and removes it with a query (no model event, so no flush) left
        // every later test in the run believing /privacy was an operator document, which then
        // rendered as an empty page instead of the built-in policy.
        \App\Models\LegalDocument::flush();

        // Memoizes whether scheduled_task_runs exists; RefreshDatabase rebuilds the schema under it.
        \App\Services\ScheduledTaskRecorder::flush();

        // Which schedules made their subdomain the main address, read once per request.
        \App\Models\CustomDomainSetting::flush();

        // N+1 queries, counted in everything this test runs (tests/Support/QueryGuard.php). The
        // handler only records: its return value is ignored and the relation still loads
        // (HasAttributes::getRelationValue), so the app behaves exactly as it does without it.
        Model::preventLazyLoading();
        Model::handleLazyLoadingViolationUsing(static fn (Model $model, string $relation) => QueryGuard::lazyLoad($model, $relation));
        QueryGuard::beginTest(static::class.'::'.$this->name());
    }

    protected function tearDown(): void
    {
        // What the test body, a command or a job found outside a request. A request's findings
        // fail at the request itself (call() below).
        $found = QueryGuard::endTest();

        // A controller's set_time_limit() is meant for one web request, but here it lasts as long as
        // the PHPUnit process and starts a new timer. /translate_data sets 900 seconds, and CI died
        // that many CPU seconds later in an unrelated test. Go back to the CLI's default of no limit.
        // TestEnvironmentTest fails if this is removed.
        set_time_limit(0);

        parent::tearDown();

        if ($found && QueryGuard::enforcing() && $this->status()->isSuccess()) {
            $this->fail(QueryGuard::describe($found));
        }
    }

    /**
     * Every HTTP request a test makes, measured on its own by QueryGuard.
     *
     * A stack, not one recorder: followingRedirects() calls get() from inside parent::call(), and a
     * test without exception handling throws out of it, so the scope is closed in `finally` or the
     * test's own assertions would be counted as the request's. A streamed response does its work
     * when its content is read, which a test does after this returns, so it is read here, inside
     * the request (TestResponse keeps the content for the test's own read).
     */
    public function call($method, $uri, $parameters = [], $cookies = [], $files = [], $server = [], $content = null)
    {
        $from = count(QueryGuard::pending());
        $id = QueryGuard::push('request', $method.' '.$uri);

        try {
            $response = parent::call($method, $uri, $parameters, $cookies, $files, $server, $content);

            $route = $response->baseRequest?->route();
            if ($route instanceof Route) {
                QueryGuard::requested($route->getName());
                QueryGuard::relabel($id, $method.' '.($route->getName() ?: '/'.ltrim($route->uri(), '/')));
            }

            if ($response->baseResponse instanceof StreamedResponse) {
                $this->readStreamInsideRequest($response);
            }
        } finally {
            QueryGuard::pop($id);
        }

        if (QueryGuard::enforcing() && ($found = QueryGuard::takePendingSince($from))) {
            $this->fail(QueryGuard::describe($found));
        }

        return $response;
    }

    /**
     * Assert a request asks the database no more as the data behind it grows: the N+1 check.
     *
     * $grow(int $n) brings the fixture up to $n of everything the page lists; $request() makes the
     * request (and asserts what it must). Each measured request is cold: the userRoles singleton,
     * the cache and the signed-in user are dropped first, because a warm request inherits
     * relations and cached values that hide exactly the growth this measures. One unmeasured
     * request first warms what lives for the whole process, so both sizes start equal.
     *
     * @param  array{0: int, 1: int}  $sizes  both below any cap the page puts on a list
     * @return array{0: int, 1: int} the two counts
     */
    protected function assertQueriesDoNotGrow(callable $grow, callable $request, array $sizes = [2, 5], int $slack = 0, ?int $budget = null, string $label = ''): array
    {
        [$small, $large] = $sizes;

        $grow($small);
        $request();
        [$smallCount, $smallSql] = $this->countQueries($request);

        $grow($large);
        [$largeCount, $largeSql] = $this->countQueries($request);

        $grew = [];
        foreach ($largeSql as $sql => $count) {
            $more = $count - ($smallSql[$sql] ?? 0);
            if ($more > 0) {
                $grew[$sql] = $more;
            }
        }
        arsort($grew);
        $why = '';
        foreach (array_slice($grew, 0, 5, true) as $sql => $more) {
            $why .= "\n  +{$more}  ".mb_strimwidth($sql, 0, 240, '...');
        }

        $this->assertLessThanOrEqual(
            $smallCount + $slack,
            $largeCount,
            trim("{$label} asks more as the data grows: {$smallCount} queries for {$small} of everything, {$largeCount} for {$large}.")
                ."\nThe queries that grew:{$why}"
                ."\nEager load what the page reads per row, or count it in one query.",
        );

        if ($budget !== null) {
            $this->assertLessThanOrEqual($budget, $largeCount, trim("{$label} ran {$largeCount} queries, over its budget of {$budget}.")
                .' If the new ones are needed, raise the budget beside the page in the same change and say why in the commit.');
        }

        return [$smallCount, $largeCount];
    }

    /** @return array{0: int, 1: array<string, int>} */
    private function countQueries(callable $request): array
    {
        $this->app->forgetInstance('userRoles');
        \Illuminate\Support\Facades\Cache::flush();
        \Illuminate\Support\Facades\Auth::forgetGuards();

        $sql = [];
        $total = 0;
        $listening = true;
        \Illuminate\Support\Facades\DB::listen(function ($query) use (&$sql, &$total, &$listening) {
            if ($listening) {
                $total++;
                $key = QueryGuard::normalize($query->sql);
                $sql[$key] = ($sql[$key] ?? 0) + 1;
            }
        });

        try {
            $request();
        } finally {
            $listening = false;
        }

        return [$total, $sql];
    }

    private function readStreamInsideRequest(TestResponse $response): void
    {
        $level = ob_get_level();

        try {
            $response->streamedContent();
        } catch (\Throwable) {
            // Leave a stream that throws to the test that reads it.
            while (ob_get_level() > $level) {
                ob_end_clean();
            }
            \Closure::bind(fn () => $this->streamedContent = null, $response, TestResponse::class)();
        }
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

        // Here and not in setUp(): a test that rebuilds the app mid-body (ForcesEnvironment) gets a
        // new event dispatcher, and a listener on the old one would hear nothing after that.
        $events = $this->app['events'];
        $events->listen(QueryExecuted::class, [QueryGuard::class, 'query']);
        $events->listen(JobProcessing::class, fn (JobProcessing $e) => QueryGuard::push('job', 'job '.$e->job->resolveName()));
        $events->listen(JobProcessed::class, fn () => QueryGuard::popKind('job'));
        $events->listen(JobExceptionOccurred::class, fn () => QueryGuard::popKind('job'));
        $events->listen(CommandStarting::class, fn (CommandStarting $e) => QueryGuard::push('command', 'artisan '.$e->command));
        $events->listen(CommandFinished::class, fn () => QueryGuard::popKind('command'));
        QueryGuard::rememberRoutes($this->app['router']->getRoutes());
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
