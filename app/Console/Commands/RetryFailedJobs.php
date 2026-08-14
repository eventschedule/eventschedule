<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Queue\Attributes\DeleteWhenMissingModels;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use ReflectionClass;
use Throwable;

/**
 * Push failed jobs back onto the queue, capped per job.
 *
 * Shared by both cron rails - AppController::translateData() every minute and
 * routes/console.php every five - so the cap lives here rather than in either rail and the
 * two cadences cannot produce two different retry budgets. Keep both callers in sync.
 *
 * This replaces an inline loop that called queue:retry on the 50 oldest failed jobs with no
 * per-job limit, so a job that could never succeed was re-pushed every minute forever.
 */
class RetryFailedJobs extends Command
{
    protected $signature = 'app:retry-failed-jobs
                            {--max-attempts= : Automatic retries allowed per job before it is left alone}
                            {--cooldown-minutes= : Minimum gap between automatic retries of the same job (0 disables)}
                            {--batch= : Maximum failed_jobs rows examined in one run}
                            {--skip-work : Do not run queue:work afterwards}';

    protected $description = 'Retry failed queue jobs, capped per job so one that can never succeed is not re-pushed forever';

    /**
     * Cache key holding how many automatic retries a failed job uuid has had.
     *
     * The uuid is the right identity here: queue:retry deletes the failed_jobs row and pushes
     * the payload back, and when the job fails again DatabaseUuidFailedJobProvider::log()
     * re-inserts it reading 'uuid' straight out of that payload. So the uuid survives a retry
     * while failed_at is refreshed - which is also why a maximum-age filter would be exactly
     * the wrong cap, since a churning job always looks fresh.
     */
    public static function attemptsKey(string $uuid): string
    {
        return 'failed_job_retries:'.$uuid;
    }

    /**
     * Drop a uuid's automatic-retry budget. Called from AdminController whenever an operator
     * retries or removes a failed job: clicking Retry is a statement that the underlying cause
     * is fixed, so the job should start again from a clean budget rather than be refused by a
     * throttle the operator cannot see.
     */
    public static function forgetAttempts(string $uuid): void
    {
        Cache::forget(self::attemptsKey($uuid));
    }

    public function handle(): int
    {
        $maxAttempts = (int) ($this->option('max-attempts') ?? config('queue.retry_failed.max_attempts', 5));
        $cooldown = (int) ($this->option('cooldown-minutes') ?? config('queue.retry_failed.cooldown_minutes', 15));
        $batch = (int) ($this->option('batch') ?? config('queue.retry_failed.batch', 50));
        $ttl = now()->addHours((int) config('queue.retry_failed.counter_ttl_hours', 24));

        // Both rails call this, and withoutOverlapping() only serialises the scheduler against
        // itself. queue:retry deletes the failed_jobs row and pushes a copy, so two concurrent
        // runs could push the same job twice. Same shape as app_translate_lock.
        $lock = Cache::lock('retry_failed_jobs_lock', 300);

        if (! $lock->get()) {
            return self::SUCCESS;
        }

        try {
            // Oldest first, unchanged. That ordering suits the cooldown below: a job that is
            // cooling down has a recent failed_at, so it sorts last and cannot starve the tail.
            $rows = DB::table('failed_jobs')
                ->orderBy('failed_at')
                ->limit($batch)
                ->get(['uuid', 'payload', 'failed_at']);

            if ($rows->isEmpty()) {
                return self::SUCCESS;
            }

            $retried = $capped = $cooling = $dropped = 0;

            foreach ($rows as $row) {
                $key = self::attemptsKey($row->uuid);
                $attempts = (int) Cache::get($key, 0);

                if ($attempts >= $maxAttempts) {
                    $capped++;

                    continue;
                }

                // A MINIMUM age, not a maximum. failed_at is refreshed every time a job fails
                // again, so this spaces out a job that really is being re-attempted while still
                // picking up a genuinely old one-off failure on the first pass.
                //
                // The first retry is exempt: the job has already burned its own tries and
                // backoff inside the worker, so making a one-off blip wait out the full
                // cooldown would be a regression on the normal path.
                if ($attempts > 0 && $cooldown > 0
                    && Carbon::parse($row->failed_at)->gt(now()->subMinutes($cooldown))) {
                    $cooling++;

                    continue;
                }

                // Counted BEFORE the attempt: queue:retry can throw (see handleRetryFailure),
                // and an attempt that throws must still consume budget or nothing is ever
                // capped. Explicit get/put rather than Cache::increment - on the file store
                // increment() of a missing key writes a key that never expires.
                Cache::put($key, $attempts + 1, $ttl);

                try {
                    Artisan::call('queue:retry', ['id' => [$row->uuid]]);
                    $retried++;
                } catch (Throwable $e) {
                    $dropped += $this->handleRetryFailure($row, $e);
                }
            }

            if ($retried || $capped || $dropped) {
                Log::warning('Retried failed jobs', [
                    'total' => $rows->count(),
                    'retried' => $retried,
                    'cooling_down' => $cooling,
                    'over_retry_limit' => $capped,
                    'dropped' => $dropped,
                ]);
            }

            if ($retried > 0 && ! $this->option('skip-work')) {
                Artisan::call('queue:work', [
                    '--stop-when-empty' => true,
                    '--max-time' => 60,
                    '--tries' => 3,
                ]);
            }
        } finally {
            $lock->release();
        }

        return self::SUCCESS;
    }

    /**
     * queue:retry unserializes the payload to refresh retryUntil
     * (RetryCommand::refreshRetryUntil), so a job whose models were deleted throws THERE, before
     * RetryCommand::handle() reaches its forget($id). Artisan runs with setCatchExceptions(false)
     * and the task component rethrows, so the exception escapes Artisan::call.
     *
     * That is why such a job had never actually been re-pushed since the day it failed, and why -
     * sitting at the head of an oldest-first list whose failed_at could never move - it silently
     * blocked every OTHER failed job in the install from being retried at all. Caught per job so
     * one poisoned payload can no longer do that.
     *
     * @return int 1 if the row was dropped, 0 if it was left for an operator
     */
    private function handleRetryFailure(object $row, Throwable $e): int
    {
        $class = json_decode($row->payload, true)['displayName'] ?? null;

        if (! $e instanceof ModelNotFoundException) {
            Log::warning('Failed job could not be retried', [
                'uuid' => $row->uuid,
                'job' => $class ?? 'unknown',
                'error' => $e->getMessage(),
            ]);

            return 0;
        }

        // Honour the job class's own declaration. A class that opts in to
        // deleteWhenMissingModels would already have been dropped by the framework
        // (CallQueuedHandler::handleModelNotFound) - a row like this only exists because it
        // failed before the flag was added - so finish the job the flag was meant to do. A
        // class that has NOT opted in is left alone, because deleting it would silently
        // override a deliberate default.
        if ($class && $this->deletesWhenMissingModels($class)) {
            Artisan::call('queue:forget', ['id' => $row->uuid]);
            self::forgetAttempts($row->uuid);

            Log::info('Dropped failed job whose models no longer exist', [
                'uuid' => $row->uuid,
                'job' => $class,
            ]);

            return 1;
        }

        Log::warning('Failed job references a deleted model and can never be retried; delete it from /admin/queue', [
            'uuid' => $row->uuid,
            'job' => $class ?? 'unknown',
        ]);

        return 0;
    }

    /** Mirrors CallQueuedHandler::handleModelNotFound, including the attribute form. */
    private function deletesWhenMissingModels(string $class): bool
    {
        try {
            $reflection = new ReflectionClass($class);

            return $reflection->getDefaultProperties()['deleteWhenMissingModels']
                ?? count($reflection->getAttributes(DeleteWhenMissingModels::class)) !== 0;
        } catch (Throwable) {
            return false;
        }
    }
}
