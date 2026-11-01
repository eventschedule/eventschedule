<?php

namespace App\Support;

use Illuminate\Cache\RateLimiter;
use Illuminate\Database\DetectsConcurrencyErrors;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * The rate limiter, with a deadlock on its own counter retried instead of thrown.
 *
 * On hosted the limiter lives in the `cache` table (CACHE_STORE=database), and one hit is an
 * INSERT IGNORE for the timer, another for the counter, then a SELECT ... FOR UPDATE and an
 * UPDATE in a transaction (DatabaseStore::add() and incrementOrDecrement()). Two requests
 * hitting the SAME key at the same moment can deadlock on that row, and InnoDB rolls one back
 * with 1213. A key is shared whenever requests arrive together: the Realtime beacon's is one
 * per visitor address per day, so two tabs or an office behind one address do it, and the
 * deadlock 500'd /api/realtime from inside ThrottleRequests (Sentry EVENTSCHEDULE-PHP-4M).
 *
 * The rolled-back statement changed nothing, so the hit is simply made again. If it still
 * deadlocks after a few tries the error is thrown as before: a limiter that let a request
 * through whenever its counter was contended would be a limiter one could switch off by
 * contending it. Inside a transaction nothing is retried, because the deadlock has already
 * rolled back the caller's whole transaction (the cache table is on the same connection).
 *
 * Bound by extending the RateLimiter singleton in AppServiceProvider::register(), so the named
 * limiters registered in boot() land on this instance.
 */
class RetryingRateLimiter extends RateLimiter
{
    use DetectsConcurrencyErrors;

    public const ATTEMPTS = 3;

    public static function wrap(RateLimiter $limiter): self
    {
        $wrapped = new self($limiter->cache);
        $wrapped->limiters = $limiter->limiters;

        return $wrapped;
    }

    public function increment($key, $decaySeconds = 60, $amount = 1)
    {
        for ($attempt = 1; ; $attempt++) {
            try {
                return parent::increment($key, $decaySeconds, $amount);
            } catch (Throwable $e) {
                if ($attempt >= self::ATTEMPTS
                    || DB::transactionLevel() > 0
                    || ! $this->causedByConcurrencyError($e)) {
                    throw $e;
                }

                // Jittered, so the two requests that collided do not collide again.
                usleep(random_int(10, 40) * 1000 * $attempt);
            }
        }
    }
}
