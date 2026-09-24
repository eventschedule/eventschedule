<?php

namespace Tests\Feature\Concerns;

/**
 * Boot the app as a different kind of install for one test: forceEnv(), then refreshApplication().
 *
 * routes/web.php registers different halves on hosted and selfhost, and gates the hosted subdomain
 * and blog groups on `hosted && ! is_testing`, so the only honest way to exercise those routes is to
 * change the env and rebuild the app. See RouteLoadTest::test_hosted_gp_routes_load() for the whole
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
