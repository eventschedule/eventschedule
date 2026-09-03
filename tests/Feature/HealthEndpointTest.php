<?php

namespace Tests\Feature;

use App\Listeners\VerifyApplicationHealth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * GET /up, and the two dependencies App\Listeners\VerifyApplicationHealth makes it prove.
 *
 * Before that listener existed, Laravel's health route dispatched DiagnosingHealth into an
 * empty listener list and returned 200 unconditionally - so /up answered "up" during a total
 * database outage. Every assertion here fails without the listener registered, which is the
 * point: a health check nothing can fail is not a health check.
 */
class HealthEndpointTest extends TestCase
{
    use RefreshDatabase;

    public function test_up_returns_200_when_the_database_and_cache_both_work(): void
    {
        $this->get('/up')->assertOk();
    }

    /**
     * FAILS before the change: /up returned 200 with the cache store unresolvable.
     *
     * An unresolvable store name is the shape CACHE_STORE present-but-EMPTY takes on a deployed
     * app spec. config('cache.default') is env('CACHE_STORE', 'file'), and a second argument to
     * env() never fires for a blank value, so '' reaches CacheManager::resolve() and throws
     * "Cache store [] is not defined" on the first cache read anywhere in the app.
     */
    public function test_up_reports_a_problem_when_the_cache_store_cannot_be_resolved(): void
    {
        // app.debug false, i.e. as deployed: ApplicationBuilder's health route only CATCHES the
        // listener's throwable when debug mode is off. With it on - the default under phpunit -
        // it rethrows and the debug handler renders the exception instead.
        config(['app.debug' => false, 'cache.default' => 'a-store-that-does-not-exist']);

        $this->get('/up')->assertStatus(500);
    }

    /**
     * FAILS before the change: nothing ran, so an unreachable database raised nothing at all.
     *
     * Driven through the listener rather than through GET /up because mocking the DB facade
     * replaces the whole DatabaseManager, and AppServiceProvider's query-log hook then dies on
     * the mock first - failing the test for a reason that has nothing to do with health. An
     * unroutable port is the honest version of "the database is gone".
     */
    public function test_the_listener_raises_when_the_database_is_unreachable(): void
    {
        config(['database.connections.health_probe_broken' => [
            'driver' => 'mysql',
            'host' => '127.0.0.1',
            'port' => 1,
            'database' => 'nope',
            'username' => 'nope',
            'password' => 'nope',
        ]]);

        $original = config('database.default');
        config(['database.default' => 'health_probe_broken']);

        try {
            $this->expectException(\Throwable::class);

            (new VerifyApplicationHealth)->handle();
        } finally {
            // Before the assertion is evaluated, so RefreshDatabase's rollback still finds the
            // connection it opened its transaction on.
            config(['database.default' => $original]);
            DB::purge('health_probe_broken');
        }
    }

    /**
     * The listener's exception messages are written for Sentry, and /up is unauthenticated, so
     * the stock health-up view must keep treating $exception as a boolean. If someone ever
     * renders the message, this is what notices.
     */
    public function test_a_failing_health_check_leaks_no_detail_to_an_anonymous_visitor(): void
    {
        config(['app.debug' => false, 'cache.default' => 'a-store-that-does-not-exist']);

        $body = $this->get('/up')->assertStatus(500)->getContent();

        $this->assertStringNotContainsString('a-store-that-does-not-exist', $body);
        $this->assertStringNotContainsString('Cache store', $body);
        $this->assertStringContainsString('experiencing problems', $body);
    }
}
