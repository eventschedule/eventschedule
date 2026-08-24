<?php

namespace Tests\Feature;

use App\Utils\CounterUtils;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Exceptions;
use PDOException;
use Tests\TestCase;

/**
 * Regression cover for CounterUtils, the write path behind every *_daily counter table.
 *
 * These exist because AnalyticsDaily::incrementView() ran a bare DB::statement(). The
 * INSERT ... ON DUPLICATE KEY UPDATE it issues is race-safe against duplicate keys (1062)
 * but not against deadlocks (1213), and a hot (role_id, date) row plus the role_id foreign
 * key - which makes every insert also take a shared lock on the same roles row - is enough
 * for InnoDB to pick a victim. The 1213 then propagated out of a fire-and-forget view
 * counter and 500'd the public schedule page it was counting.
 *
 * The two behaviours pinned here are the whole point of the helper, and they pull in
 * opposite directions: swallow outside a transaction, never swallow inside one.
 *
 * A third pull was added later: the same foreign key can fail outright (1452) when the
 * parent row is deleted mid-request. That one is swallowed AND not reported, so the tests
 * below also pin the boundary - everything else still reaches Sentry.
 */
class AnalyticsCounterResilienceTest extends TestCase
{
    /**
     * A deadlock as PDO reports it: SQLSTATE 40001, which is what
     * DetectsConcurrencyErrors::causedByConcurrencyError() matches on.
     */
    private function deadlock(): QueryException
    {
        return new QueryException(
            'mysql',
            'INSERT INTO analytics_daily ...',
            [],
            new PDOException('SQLSTATE[40001]: Serialization failure: 1213 Deadlock found when trying to get lock; try restarting transaction')
        );
    }

    /**
     * A foreign key violation as PDO reports it: SQLSTATE 23000 with driver code 1452 in
     * errorInfo[1], which is what CounterUtils matches on. Verbatim shape of the production
     * failure in Sentry EVENTSCHEDULE-PHP-46, where the hourly demo reset deleted the event
     * between the guest page's SELECT and this INSERT.
     */
    private function foreignKeyViolation(): QueryException
    {
        $pdoException = new PDOException(
            'SQLSTATE[23000]: Integrity constraint violation: 1452 Cannot add or update a child row: '
            .'a foreign key constraint fails (`analytics_events_daily`, CONSTRAINT '
            .'`analytics_events_daily_event_id_foreign` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE)'
        );

        // QueryException copies errorInfo off the previous PDOException, which is the only
        // place the 1452 lives - the SQLSTATE alone ('23000') does not distinguish it from
        // a duplicate key.
        $pdoException->errorInfo = [
            '23000',
            1452,
            'Cannot add or update a child row: a foreign key constraint fails',
        ];

        return new QueryException(
            'mysql',
            'INSERT INTO analytics_events_daily (event_id, date, mobile_views) VALUES (?, ?, 1) ON DUPLICATE KEY UPDATE mobile_views = mobile_views + 1',
            [114168, '2026-08-22'],
            $pdoException
        );
    }

    public function test_a_deadlock_outside_a_transaction_is_retried_then_swallowed(): void
    {
        DB::shouldReceive('transactionLevel')->andReturn(0);
        DB::shouldReceive('statement')->times(3)->andThrow($this->deadlock());

        // The assertion is that this returns at all. Before CounterUtils the QueryException
        // escaped incrementView() and took the guest page render down with it.
        CounterUtils::statement('INSERT INTO analytics_daily (role_id, date) VALUES (?, ?)', [1, '2026-08-16'], 3);

        $this->assertTrue(true, 'A counter deadlock must never escape to the caller.');
    }

    public function test_a_deadlock_stops_retrying_as_soon_as_the_write_lands(): void
    {
        DB::shouldReceive('transactionLevel')->andReturn(0);
        DB::shouldReceive('statement')->once()->andThrow($this->deadlock());
        DB::shouldReceive('statement')->once()->andReturn(true);

        CounterUtils::statement('INSERT INTO analytics_daily (role_id, date) VALUES (?, ?)', [1, '2026-08-16'], 3);

        // Mockery verifies the exact call count on teardown: two attempts, not three.
        $this->assertTrue(true);
    }

    public function test_a_non_concurrency_error_is_not_retried_but_is_still_swallowed(): void
    {
        $notADeadlock = new QueryException(
            'mysql',
            'INSERT INTO analytics_daily ...',
            [],
            new PDOException('SQLSTATE[42S02]: Base table or view not found')
        );

        Exceptions::fake();
        DB::shouldReceive('transactionLevel')->andReturn(0);
        // Exactly once - retrying a schema error would just burn the request's time budget.
        DB::shouldReceive('statement')->once()->andThrow($notADeadlock);

        CounterUtils::statement('INSERT INTO analytics_daily (role_id, date) VALUES (?, ?)', [1, '2026-08-16'], 3);

        // Swallowed, but LOUD. This is the boundary on the 1452 branch below: that branch
        // returns silently, and it must not widen into "a counter never reports anything".
        Exceptions::assertReported(QueryException::class);
    }

    public function test_a_foreign_key_violation_outside_a_transaction_is_swallowed_silently(): void
    {
        Exceptions::fake();
        DB::shouldReceive('transactionLevel')->andReturn(0);
        // Exactly once. A 1452 means the parent roles/events row is permanently gone, so
        // there is nothing a retry could land.
        DB::shouldReceive('statement')->once()->andThrow($this->foreignKeyViolation());

        CounterUtils::statement(
            'INSERT INTO analytics_events_daily (event_id, date, mobile_views) VALUES (?, ?, 1) ON DUPLICATE KEY UPDATE mobile_views = mobile_views + 1',
            [114168, '2026-08-22'],
            3
        );

        // The point of the branch: the hourly demo reset hard-deletes and recreates every
        // demo-% schedule and its events, so a guest mid-render loses this race about once
        // an hour. Reporting it filled Sentry with a failure nobody can act on.
        Exceptions::assertNothingReported();
    }

    public function test_a_deadlock_inside_a_transaction_propagates(): void
    {
        // This is the guard that keeps checkout correct. MySQL rolls back the WHOLE
        // transaction when it picks a deadlock victim, so swallowing here would let
        // TicketController's checkout closure carry on marking a sale paid on top of rows
        // that no longer exist. AnalyticsEventsDaily::incrementSale() runs in exactly that
        // position, so the error has to reach the enclosing DB::transaction().
        DB::shouldReceive('transactionLevel')->andReturn(1);
        DB::shouldReceive('statement')->once()->andThrow($this->deadlock());

        $this->expectException(QueryException::class);

        CounterUtils::statement('INSERT INTO analytics_events_daily (event_id, date) VALUES (?, ?)', [1, '2026-08-16'], 3);
    }

    public function test_a_foreign_key_violation_inside_a_transaction_propagates(): void
    {
        // Same guard, other failure mode. incrementSale() runs inside the checkout
        // transaction: if the event vanished mid-checkout the sale rows referencing it are
        // doomed too, so swallowing the 1452 would commit a sale against a dead event.
        DB::shouldReceive('transactionLevel')->andReturn(1);
        DB::shouldReceive('statement')->once()->andThrow($this->foreignKeyViolation());

        $this->expectException(QueryException::class);

        CounterUtils::statement(
            'INSERT INTO analytics_events_daily (event_id, date, sales_count) VALUES (?, ?, 1)',
            [114168, '2026-08-22'],
            3
        );
    }
}
