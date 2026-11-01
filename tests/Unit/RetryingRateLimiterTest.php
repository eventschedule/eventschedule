<?php

namespace Tests\Unit;

use App\Support\RetryingRateLimiter;
use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\RateLimiter;
use Illuminate\Cache\Repository;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use PDOException;
use RuntimeException;
use Tests\TestCase;

/**
 * Sentry EVENTSCHEDULE-PHP-4M: two Realtime beacons from one address hit the same limiter row
 * in the database cache, InnoDB rolled one back with 1213, and the beacon answered 500 from
 * inside ThrottleRequests.
 */
class RetryingRateLimiterTest extends TestCase
{
    private function deadlock(): QueryException
    {
        return new QueryException(
            'mysql',
            'insert ignore into `cache` (`key`, `value`, `expiration`) values (?, ?, ?)',
            [],
            new PDOException('SQLSTATE[40001]: Serialization failure: 1213 Deadlock found when trying to get lock; try restarting transaction')
        );
    }

    /** A store whose add() throws the given errors first, one per call, then works. */
    private function limiterFailingWith(array $errors): RetryingRateLimiter
    {
        $store = new class($errors) extends ArrayStore
        {
            public function __construct(public array $errors)
            {
                parent::__construct();
            }

            public function add($key, $value, $seconds)
            {
                if ($this->errors) {
                    throw array_shift($this->errors);
                }

                if ($this->get($key) !== null) {
                    return false;
                }

                return $this->put($key, $value, $seconds);
            }
        };

        return RetryingRateLimiter::wrap(new RateLimiter(new Repository($store)));
    }

    public function test_the_app_resolves_the_retrying_limiter_with_its_named_limiters(): void
    {
        $this->assertInstanceOf(RetryingRateLimiter::class, app(RateLimiter::class));
        $this->assertNotNull(app(RateLimiter::class)->limiter('realtime'), 'Limiters registered in boot() must land on the wrapped instance');
    }

    public function test_a_deadlocked_hit_is_made_again_and_counted_once(): void
    {
        $limiter = $this->limiterFailingWith([$this->deadlock()]);

        $this->assertSame(1, $limiter->hit('beacon'));
        $this->assertSame(1, $limiter->attempts('beacon'));
    }

    public function test_a_hit_that_keeps_deadlocking_still_throws(): void
    {
        $limiter = $this->limiterFailingWith(array_fill(0, RetryingRateLimiter::ATTEMPTS, $this->deadlock()));

        $this->expectException(QueryException::class);

        $limiter->hit('beacon');
    }

    public function test_any_other_error_is_not_retried(): void
    {
        $limiter = $this->limiterFailingWith([new RuntimeException('the cache is down')]);

        $this->expectExceptionMessage('the cache is down');

        $limiter->hit('beacon');
    }

    public function test_inside_a_transaction_a_deadlock_is_not_retried(): void
    {
        $limiter = $this->limiterFailingWith([$this->deadlock()]);

        DB::beginTransaction();

        try {
            $this->expectException(QueryException::class);

            $limiter->hit('beacon');
        } finally {
            DB::rollBack();
        }
    }
}
