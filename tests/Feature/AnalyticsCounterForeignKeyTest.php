<?php

namespace Tests\Feature;

use App\Models\AnalyticsEventsDaily;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * The real-MySQL half of the CounterUtils cover.
 *
 * AnalyticsCounterResilienceTest mocks DB::statement and hand-builds the QueryException, so
 * it pins the branching but cannot prove the one thing the fix actually depends on: that a
 * real foreign key failure arrives as errorInfo[1] === 1452, an integer rather than the
 * '23000' SQLSTATE or a numeric string. If the driver ever reported it differently the
 * mocked tests would stay green while production quietly went back to filling Sentry.
 *
 * Note what is NOT here: the silent-swallow path cannot be exercised in this file, because
 * RefreshDatabase holds an open transaction for the whole test and CounterUtils deliberately
 * refuses to swallow inside one. That branch is covered by the mocked test; between the two
 * files the real error shape and the real branching are both pinned.
 *
 * Sentry EVENTSCHEDULE-PHP-46.
 */
class AnalyticsCounterForeignKeyTest extends TestCase
{
    use RefreshDatabase;

    /**
     * An events id that does not exist. InnoDB cannot tell "never existed" from "deleted one
     * millisecond ago", which is the actual production case: the hourly demo reset
     * (DemoService::resetDemoData) hard-deletes and recreates every demo-% schedule and its
     * events while guest pages are mid-render.
     */
    private const GHOST_EVENT_ID = 987654321;

    public function test_a_real_foreign_key_failure_reports_error_1452(): void
    {
        $this->expectException(QueryException::class);

        try {
            DB::statement(
                'INSERT INTO analytics_events_daily (event_id, date, mobile_views) VALUES (?, ?, 1)',
                [self::GHOST_EVENT_ID, '2026-08-22']
            );
        } catch (QueryException $e) {
            // The exact shape CounterUtils matches on. Strict ===, so the type matters.
            $this->assertSame(1452, $e->errorInfo[1] ?? null);
            $this->assertIsInt($e->errorInfo[1]);
            $this->assertSame('23000', $e->getCode());

            throw $e;
        }
    }

    public function test_a_real_foreign_key_failure_inside_a_transaction_still_propagates(): void
    {
        // RefreshDatabase's own wrapping transaction stands in for the checkout transaction
        // here, which makes this the unmocked version of the guard that keeps checkout
        // correct: AnalyticsEventsDaily::incrementSale() runs inside DB::transaction(), and
        // an event that vanished mid-checkout has to abort the sale rather than be counted
        // away silently.
        $this->assertGreaterThan(0, DB::transactionLevel(), 'Precondition: the harness holds a transaction.');

        $this->expectException(QueryException::class);

        AnalyticsEventsDaily::incrementView(self::GHOST_EVENT_ID, 'mobile');
    }
}
