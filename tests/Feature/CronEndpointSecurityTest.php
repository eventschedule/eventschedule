<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * The two secret-gated cron endpoints, /translate_data and /release_tickets.
 *
 * They are reachable by anyone, so the guard has to survive whatever the query string parses to -
 * not just a wrong string.
 */
class CronEndpointSecurityTest extends TestCase
{
    use RefreshDatabase;

    public static function endpoints(): array
    {
        return [['/translate_data'], ['/release_tickets']];
    }

    /**
     * request()->get() returns whatever the query string parsed to, so ?secret[]=x yields an
     * ARRAY. An array is truthy, so it passed the emptiness check and reached hash_equals(), which
     * type-errors on a non-string - an uncaught 500 for any anonymous caller, plus an error-tracker
     * event per probe. Exactly the shape of a bug this codebase has been bitten by before.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('endpoints')]
    public function test_an_array_secret_is_rejected_rather_than_fatal(string $path): void
    {
        config(['app.cron_secret' => 'the-real-secret']);

        $this->withoutExceptionHandling([])
            ->get($path.'?secret[]=x')
            ->assertStatus(403);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('endpoints')]
    public function test_a_wrong_secret_is_rejected(string $path): void
    {
        config(['app.cron_secret' => 'the-real-secret']);

        $this->get($path.'?secret=nope')->assertStatus(403);
    }

    /** An unset server secret fails closed on BOTH endpoints, so a misconfigured install is not wide open. */
    #[\PHPUnit\Framework\Attributes\DataProvider('endpoints')]
    public function test_an_unset_server_secret_rejects_everything(string $path): void
    {
        config(['app.cron_secret' => '']);

        $this->get($path.'?secret=anything')->assertStatus(403);
        $this->get($path)->assertStatus(403);
    }

    /**
     * The other half of the guard: a CORRECT secret must be accepted.
     *
     * Every other test here asserts a 403, so a change that failed closed - silently stopping all
     * scheduled work on every install driven by the HTTP cron - passed this whole file. The lock is
     * taken first so the request short-circuits on the "already running" branch instead of
     * executing the entire schedule; that is after the auth check, which is the thing under test.
     */
    public function test_a_correct_secret_is_accepted(): void
    {
        config(['app.cron_secret' => 'the-real-secret']);

        $lock = Cache::lock('translate_data_lock', 60);
        $this->assertTrue($lock->get(), 'the test must be the one holding the lock');

        try {
            $this->get('/translate_data?secret=the-real-secret')
                ->assertStatus(200)
                ->assertJson(['message' => 'Already running']);
        } finally {
            $lock->release();
        }
    }

    /**
     * The HTTP rail's heartbeat, which nothing covered: deleting both Cache::put lines passed the
     * suite, and SchedulerHealth::isHttpRailOnly() depends on the per-rail key existing.
     *
     * Every tier key is pre-claimed so only the "EVERY CALL" block runs - the tiers are covered by
     * CronRailSyncTest, and running all of them here would execute the entire schedule.
     */
    public function test_a_completed_run_stamps_both_heartbeat_keys(): void
    {
        config(['app.cron_secret' => 'the-real-secret']);

        foreach (['td_5min', 'td_15min', 'td_hourly', 'td_translate', 'td_daily', 'td_monthly', 'notified_pending_today'] as $tier) {
            Cache::put($tier, true, now()->addDay());
        }

        Cache::forget('scheduler.last_run_at');
        Cache::forget('scheduler.last_run_at.http');

        $this->get('/translate_data?secret=the-real-secret')->assertStatus(200);

        $this->assertNotNull(Cache::get('scheduler.last_run_at'),
            'the aggregate heartbeat is what AdminAlertService reads');
        $this->assertNotNull(Cache::get('scheduler.last_run_at.http'),
            'the per-rail key is what tells /admin/queue this install has an HTTP rail');
    }
}
