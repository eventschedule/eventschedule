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
     * A genuine completion, and - rarely - an overlap that slipped past the filter.
     *
     * An ordinary overlap does NOT arrive here. withoutOverlapping() is a ->skip() reject filter
     * (ManagesAttributes::withoutOverlapping() ends with $this->skip(...)), and ScheduleRunCommand
     * evaluates filtersPass() BEFORE runEvent(), so it dispatches ScheduledTaskSkipped and neither
     * Starting nor Finished. skipped() below is the handler that fires.
     *
     * Event::run()'s own shouldSkipDueToOverlapping() is still reachable in the race between the
     * filter passing and mutex->create() succeeding, and it returns before finish() assigns
     * exitCode - so a null exit code does arrive here occasionally, and must not be read as
     * success. It is recorded as a skip, exactly as the filtered path would have been.
     */
    public static function finished(ScheduledTaskFinished $event): void
    {
        self::guard(function () use ($event) {
            if ($event->task->exitCode === null) {
                self::recordSkip($event);

                return;
            }

            // What 'failed' can and cannot see: every entry in routes/console.php is a closure
            // wrapping Artisan::call(), and CallbackEvent::execute() maps only a literal `false`
            // return to exit code 1. A closure that returns nothing is therefore always exit 0, so
            // a command returning Command::FAILURE WITHOUT throwing lands here as a success.
            // In practice the commands that matter throw, and returning `Artisan::call(...) === 0`
            // from all 38 closures would also paint a task red for benign non-zero exits -
            // app:check-version returns FAILURE when GitHub is merely unreachable. So this state
            // means "threw", not "exited non-zero", and the page should be read that way.
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

    /**
     * The normal overlap path: withoutOverlapping()'s reject filter fired.
     *
     * Deliberately does not touch last_started_at or last_finished_at - no run began. That is what
     * lets SchedulerHealth tell a task queued behind a live run from one wedged behind a mutex it
     * cannot take: only last_skipped_at moves, so last_finished_at keeps ageing underneath it.
     */
    public static function skipped(ScheduledTaskSkipped $event): void
    {
        self::guard(fn () => self::recordSkip($event));
    }

    /**
     * Stamp a skip WITHOUT letting it erase a recorded failure.
     *
     * A skip means no run happened, so it is not evidence that the previous failure is over. Writing
     * last_status unconditionally turned a red row green on the next blocked tick while last_error
     * and consecutive_failures sat unread underneath - which contradicts state()'s own "a failure
     * is real data whatever the heartbeat says".
     *
     * Two statements rather than a read-then-write, so a real run finishing in between wins
     * outright instead of being half-overwritten - the same argument the failure counter makes.
     */
    private static function recordSkip(object $event): void
    {
        $run = self::write($event, ['last_skipped_at' => now()]);

        if ($run === null) {
            return;
        }

        ScheduledTaskRun::whereKey($run->getKey())
            ->where(fn ($query) => $query
                ->whereNull('last_status')
                ->orWhere('last_status', '!=', ScheduledTaskRun::STATUS_FAILED))
            ->update(['last_status' => ScheduledTaskRun::STATUS_SKIPPED]);
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
    private static function write(object $event, array $values, bool $incrementFailures = false, bool $resetFailures = false): ?ScheduledTaskRun
    {
        $name = $event->task->description ?? null;

        // Every entry carries ->name() (SchedulerHealthTest enforces it), but an unnamed task
        // would collapse every other unnamed task onto one row, so skip rather than corrupt.
        if (! is_string($name) || $name === '') {
            return null;
        }

        // An un-migrated container must not report once per task per minute. Memoized because this
        // runs twice per due task per tick, and hasTable is a real information_schema query.
        self::$tableExists ??= Schema::hasTable('scheduled_task_runs');

        if (! self::$tableExists) {
            return null;
        }

        // Clamped like last_host below: SCHEDULER_RAIL is operator-settable and the column is
        // varchar(20), so an over-long value is a 1406 on a strict connection - swallowed by
        // guard(), reported ~38 times a minute, and per-task recording silently dead while the
        // heartbeat stays green. Exactly the invisible failure this class is written against.
        $values['last_via'] = TextUtils::clamp(config('app.scheduler_rail', 'cron'), 20);
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

        return $run;
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
