<?php

namespace Tests\Feature;

use App\Utils\CounterUtils;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
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

        DB::shouldReceive('transactionLevel')->andReturn(0);
        // Exactly once - retrying a schema error would just burn the request's time budget.
        DB::shouldReceive('statement')->once()->andThrow($notADeadlock);

        CounterUtils::statement('INSERT INTO analytics_daily (role_id, date) VALUES (?, ?)', [1, '2026-08-16'], 3);

        $this->assertTrue(true, 'A counter write must not break the page for any reason.');
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
}
