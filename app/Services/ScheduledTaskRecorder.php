<?php

namespace App\Services;

use App\Models\ScheduledTaskRun;
use App\Utils\TextUtils;
use Illuminate\Console\Events\ScheduledTaskFailed;
use Illuminate\Console\Events\ScheduledTaskFinished;
use Illuminate\Console\Events\ScheduledTaskSkipped;
use Illuminate\Console\Events\ScheduledTaskStarting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Records the last outcome of each scheduled task into scheduled_task_runs, for /admin/queue.
 *
 * THE FIRST RULE HERE IS THAT THIS MUST NEVER BREAK THE SCHEDULER. ScheduleRunCommand dispatches
 * ScheduledTaskStarting OUTSIDE the try/catch that wraps the run itself, and the Task view
 * component re-throws, so an exception escaping the starting listener propagates out of the
 * foreach and kills schedule:run for that whole minute - every remaining due task silently skipped.
 * Worse, CommandFinished rides ConsoleEvents::TERMINATE and fires on the error path too, so the
 * heartbeat would still stamp and the admin alert would stay green while nothing ran.
 *
 * So every entry point swallows Throwable and reports. This is the same argument CounterUtils
 * makes for analytics: a counter must never break the page it is counting.
 */
class ScheduledTaskRecorder
{
    /** Memoized for the process: the schema does not change under a running scheduler. */
    private static ?bool $tableExists = null;

    public static function starting(ScheduledTaskStarting $event): void
    {
        self::guard(fn () => self::write($event, [
            'last_started_at' => now(),
        ]));
    }

    /**
     * Finished fires for a genuine completion AND for a task skipped by withoutOverlapping().
     *
     * Event::run() returns early on an overlap, before finish() ever assigns exitCode, so the skip
     * arrives here with exitCode still null - ScheduledTaskSkipped is NOT dispatched for it (that
     * event is only for ->when()/->skip() filters, which this app does not use). Reading a null
     * exit code as success would mean a task wedged behind a stranded mutex reports "succeeded"
     * every minute forever, which is precisely the failure the bounded withoutOverlapping()
     * expiries exist to catch.
     */
    public static function finished(ScheduledTaskFinished $event): void
    {
        self::guard(function () use ($event) {
            if ($event->task->exitCode === null) {
                self::write($event, [
                    'last_skipped_at' => now(),
                    'last_status' => ScheduledTaskRun::STATUS_SKIPPED,
                ]);

                return;
            }

            $failed = $event->task->exitCode !== 0;

            self::write($event, [
                'last_finished_at' => now(),
                'last_status' => $failed ? ScheduledTaskRun::STATUS_FAILED : ScheduledTaskRun::STATUS_SUCCEEDED,
                // Formatted, not the raw float: the decimal cast hands the value to brick/math,
                // which deprecates float input and removes it in 0.15 - at which point asDecimal()
                // would throw in here, get swallowed by guard(), and per-task recording would die
                // silently while the heartbeat stayed green.
                'last_runtime_seconds' => number_format((float) $event->runtime, 2, '.', ''),
                'last_error' => $failed ? 'Exited with code '.$event->task->exitCode : null,
            ], incrementFailures: $failed, resetFailures: ! $failed);
        });
    }

    public static function failed(ScheduledTaskFailed $event): void
    {
        self::guard(fn () => self::write($event, [
            // A failure IS a finish. Without this the row also looks "started but never finished"
            // and the page shows two red states for one event.
            'last_finished_at' => now(),
            'last_status' => ScheduledTaskRun::STATUS_FAILED,
            'last_error' => TextUtils::clamp(
                TextUtils::normalizeNewlines($event->exception->getMessage()),
                ScheduledTaskRun::ERROR_LENGTH
            ),
        ], incrementFailures: true));
    }

    public static function skipped(ScheduledTaskSkipped $event): void
    {
        self::guard(fn () => self::write($event, [
            'last_skipped_at' => now(),
            'last_status' => ScheduledTaskRun::STATUS_SKIPPED,
        ]));
    }

    /**
     * Upsert one row, keyed on the task name's unique index.
     *
     * schedule:work starts a new schedule:run every minute without waiting for the last one, and
     * the starting event fires before the overlap mutex is even consulted, so two processes can
     * write the same row in the same second. updateOrCreate on a unique key survives that; the
     * consecutive_failures counter is moved with an atomic expression rather than a read-then-write
     * so two racing writers cannot both read 3 and both store 4.
     */
    private static function write(object $event, array $values, bool $incrementFailures = false, bool $resetFailures = false): void
    {
        $name = $event->task->description ?? null;

        // Every entry carries ->name() (SchedulerHealthTest enforces it), but an unnamed task
        // would collapse every other unnamed task onto one row, so skip rather than corrupt.
        if (! is_string($name) || $name === '') {
            return;
        }

        // An un-migrated container must not report once per task per minute. Memoized because this
        // runs twice per due task per tick, and hasTable is a real information_schema query.
        self::$tableExists ??= Schema::hasTable('scheduled_task_runs');

        if (! self::$tableExists) {
            return;
        }

        $values['last_via'] = config('app.scheduler_rail', 'cron');
        $values['last_host'] = TextUtils::clamp(gethostname() ?: null, 64);

        if ($resetFailures) {
            $values['consecutive_failures'] = 0;
        }

        $run = ScheduledTaskRun::updateOrCreate(['name' => $name], $values);

        if ($incrementFailures) {
            // A second statement, so a success landing between the two would reset the counter that
            // this then bumps off zero. Scoped to a row that still holds the failure we just wrote,
            // so an interleaved success wins outright instead of being half-overwritten.
            ScheduledTaskRun::whereKey($run->getKey())
                ->where('last_status', ScheduledTaskRun::STATUS_FAILED)
                ->update(['consecutive_failures' => DB::raw('consecutive_failures + 1')]);
        }
    }

    /**
     * Drop the memoized schema probe. Only needed in tests, which share one process across
     * databases that RefreshDatabase rebuilds underneath this static.
     */
    public static function flush(): void
    {
        self::$tableExists = null;
    }

    /** @param  callable():void  $work */
    private static function guard(callable $work): void
    {
        try {
            $work();
        } catch (\Throwable $e) {
            // Never rethrow: see the class docblock. A monitor that can stop the scheduler is
            // worse than no monitor.
            report($e);
        }
    }
}
