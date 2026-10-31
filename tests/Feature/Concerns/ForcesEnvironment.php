<?php

namespace Tests\Feature\Concerns;

/**
 * Boot the app as a different kind of install for one test: forceEnv(), then refreshApplication().
 *
 * routes/web.php registers different halves on hosted and selfhost, gates the hosted subdomain
 * group on `hosted && ! is_testing`, and the blog host on that plus `is_nexus`, so the only honest
 * way to exercise those routes is to change the env and rebuild the app. See RouteLoadTest::test_hosted_gp_routes_load() for the whole
 * sequence, including the transaction the test has to close itself.
 *
 * Every value is restored after the test, because these decide which half of routes/web.php the
 * NEXT test class registers.
 */
trait ForcesEnvironment
{
    /** Env values as they stood before forceEnv() overrode them, keyed by variable name. */
    private array $originalEnv = [];

    /**
     * Pin an env value ahead of every layer Laravel's Env repository reads.
     *
     * The repository queries $_SERVER, then $_ENV, then getenv(), so a putenv() alone loses to
     * anything phpunit.xml or the .env loader already wrote - which is why the whole $_SERVER
     * mirror in tests/bootstrap.php exists. Original values are captured for the restore below.
     */
    protected function forceEnv(string $key, string $value): void
    {
        if (! array_key_exists($key, $this->originalEnv)) {
            $this->originalEnv[$key] = [
                'server' => $_SERVER[$key] ?? null,
                'env' => $_ENV[$key] ?? null,
                'getenv' => getenv($key),
            ];
        }

        $_SERVER[$key] = $value;
        $_ENV[$key] = $value;
        putenv($key.'='.$value);
    }

    /**
     * Step out of RefreshDatabase's transaction so the app can be rebuilt, and step back in at
     * teardown. Call it where a test used to roll that transaction back by hand.
     *
     * RefreshDatabase decides at teardown whether a test left the schema dirty by asking whether
     * ITS connection is still inside the transaction it opened, and when it is not it rebuilds the
     * schema for the next test (RefreshDatabaseState::$migrated = false). A test that rolled back
     * to rebuild the app always answered "not inside", so the next test ran migrate:fresh: about
     * 36 seconds on CI, after every forced-environment test, which was 7 of the suite's 45 minutes.
     * What the test does after the rebuild runs on the NEW app's connection, inside a transaction
     * the test closes itself, so nothing reached the schema and the rebuild was never needed.
     *
     * The callback goes FIRST in the list: RefreshDatabase registered its own in setUp(), and the
     * question has to be answered before that one asks it. ForcedEnvironmentKeepsTheSchemaTest
     * fails if the next test rebuilds the schema again.
     */
    protected function leaveTestTransaction(): void
    {
        $connection = $this->app['db']->connection();
        $connection->rollBack();

        array_unshift($this->beforeApplicationDestroyedCallbacks, function () use ($connection): void {
            if (! $connection->getPdo()->inTransaction()) {
                $connection->beginTransaction();
            }
        });
    }

    /**
     * Called by Laravel's setUpTraits() as a beforeApplicationDestroyed callback, so it runs in
     * every tearDown, a failed test's included.
     */
    protected function tearDownForcesEnvironment(): void
    {
        foreach ($this->originalEnv as $key => $original) {
            if ($original['server'] === null) {
                unset($_SERVER[$key]);
            } else {
                $_SERVER[$key] = $original['server'];
            }

            if ($original['env'] === null) {
                unset($_ENV[$key]);
            } else {
                $_ENV[$key] = $original['env'];
            }

            if ($original['getenv'] === false) {
                putenv($key);
            } else {
                putenv($key.'='.$original['getenv']);
            }
        }

        $this->originalEnv = [];
    }
}
