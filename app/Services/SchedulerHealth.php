<?php

namespace App\Services;

use App\Models\ScheduledTaskRun;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

/**
 * What /admin/queue shows about the scheduler: which rails are ticking, and how each task is doing.
 *
 * The task list comes from the LIVE schedule, joined onto scheduled_task_runs - never the other way
 * round. That gives three things a DB-driven list cannot: tasks that have never run still appear,
 * a task removed from routes/console.php stops appearing with no prune job, and the cadence is
 * always current (a stored cron expression would only refresh when the task next ran, i.e. it would
 * be stale exactly when the task has stopped, which is the case this page exists for).
 */
class SchedulerHealth
{
    /**
     * The rail names this app can produce: 'http' is hardcoded by AppController::translateData(),
     * and the scheduler rail names itself from config('app.scheduler_rail'), which defaults to
     * 'cron' and is set to 'worker' on a dedicated container.
     *
     * rails() also merges this container's own rail and the EXPECTED rail, so an operator who sets
     * SCHEDULER_RAIL to something outside this list still sees their rail instead of it silently
     * vanishing - which would leave isStalled() unable to find the rail it is judging.
     */
    public const RAILS = ['worker', 'cron', 'http'];

    /** The rail that MUST be alive. Empty means "any rail will do", which is the selfhost case. */
    public static function expectedRail(): ?string
    {
        $rail = config('app.scheduler_expected_rail');

        return is_string($rail) && $rail !== '' ? $rail : null;
    }

    public static function staleMinutes(): int
    {
        return (int) config('app.scheduler_stale_minutes', 15);
    }

    /** The aggregate heartbeat: has ANY rail ticked recently. */
    public static function lastRunAt(): ?Carbon
    {
        $value = Cache::get('scheduler.last_run_at');

        // ->setTimezone: Carbon 3's createFromTimestamp() returns UTC regardless of the default
        // timezone, so on an install where APP_TIMEZONE is not UTC the card's tooltip would print a
        // different clock from last_finished_at right beside it, which is an Eloquent cast and lands
        // in the app timezone. diffForHumans is unaffected either way; the printed time is not.
        return is_numeric($value)
            ? Carbon::createFromTimestamp((int) $value)->setTimezone(config('app.timezone'))
            : null;
    }

    /**
     * Is scheduled work actually happening?
     *
     * The aggregate key alone is not enough once more than one rail can write it. During the
     * cutover the HTTP cron keeps it fresh every minute, so a worker that died an hour ago left
     * this returning false: no banner, no alert, and the page cheerfully asserting that the cron
     * endpoint's heartbeat was the liveness signal for the install. That is the precise blindness
     * the per-rail keys were added to remove, and reading only the aggregate did not remove it.
     *
     * The fix is NOT "alert if any rail with a key is stale". A retired rail's key lingers for its
     * full 7-day TTL, so that rule would cry wolf for a week after a *successful* cutover, when the
     * external cron is deliberately switched off. Key presence cannot tell "died" from "retired".
     *
     * So the expectation is explicit: name the rail that must be alive and judge that one. Unset,
     * this behaves exactly as it always did.
     */
    public static function isStalled(): bool
    {
        if ($expected = self::expectedRail()) {
            $rail = self::rails()->firstWhere('name', $expected);

            // Never seen at all is stalled too - a worker that has never ticked is not healthy.
            return $rail === null || $rail->stale;
        }

        $last = self::lastRunAt();

        return $last === null || $last->diffInMinutes(now()) > self::staleMinutes();
    }

    /**
     * One entry per rail that has ticked in the last week, each with its own staleness.
     *
     * This is the whole point of the per-rail keys: with a single aggregate, a worker that died
     * while the HTTP cron kept running looks perfectly healthy.
     *
     * @return Collection<int, object>
     */
    public static function rails(): Collection
    {
        return collect(self::RAILS)
            // This container's rail AND the expected one. Without the latter, naming a rail
            // outside RAILS in SCHEDULER_EXPECTED_RAIL means the web container never reads that
            // rail's key, firstWhere() in isStalled() returns null, and the install sits on a
            // permanent red "scheduler stalled" alert while the worker is perfectly healthy.
            ->merge([config('app.scheduler_rail', 'cron'), self::expectedRail()])
            // Before the map, not after: expectedRail() is nullable and the closure is typed.
            ->filter()
            ->unique()
            ->map(function (string $rail) {
                $value = Cache::get('scheduler.last_run_at.'.$rail);

                if (! is_numeric($value)) {
                    return null;
                }

                // App timezone, for the same reason as lastRunAt() above: the card prints this.
                $at = Carbon::createFromTimestamp((int) $value)->setTimezone(config('app.timezone'));

                return (object) [
                    'name' => $rail,
                    'at' => $at,
                    'stale' => $at->diffInMinutes(now()) > self::staleMinutes(),
                ];
            })
            ->filter()
            ->values();
    }

    /**
     * True when the only rail ticking is the HTTP endpoint.
     *
     * That rail dispatches no ScheduledTask* events, so the per-task table can never fill on such
     * an install. The page has to say so rather than render an empty table, which would read as a
     * bug. Detected from the rails rather than from cron_secret, which is set on hosted too.
     */
    public static function isHttpRailOnly(): bool
    {
        // Any non-http rail key at all, stale or not, means this install HAS a scheduler rail - so
        // it must never be shown copy claiming the cron endpoint is its liveness signal. Suppressing
        // that panel for a week after genuinely retiring a worker is a far smaller harm than telling
        // an operator a dead worker is healthy. An install that never had one has no such key.
        $rails = self::rails();

        if ($rails->contains(fn ($rail) => $rail->name !== 'http')) {
            return false;
        }

        // Past the guard above every remaining rail is named 'http', so this only has to ask
        // whether one of them is still fresh.
        return $rails->reject->stale->isNotEmpty();
    }

    /**
     * The registered schedule, loaded on demand.
     *
     * routes/console.php is required by the CONSOLE kernel, so in a web request the Schedule
     * singleton is empty. Requiring it here costs ~38 CallbackEvent constructions and no I/O: the
     * closures are not invoked, appendOutputTo only stores a string, and the one invokable entry
     * has no constructor.
     *
     * @return \Illuminate\Console\Scheduling\Event[]
     */
    public static function events(): array
    {
        $schedule = app(Schedule::class);

        if ($schedule->events() === []) {
            require base_path('routes/console.php');
        }

        return app(Schedule::class)->events();
    }

    /**
     * Every scheduled task with its cadence and current state.
     *
     * @return Collection<int, object>
     */
    public static function tasks(): Collection
    {
        // Same guard the recorder has on the write side. Hosted migrates before serving, so this
        // is for a selfhost operator who has pulled the code and not migrated yet - and /admin/queue
        // is exactly where they would go to find out why nothing is running.
        $rows = Schema::hasTable('scheduled_task_runs')
            ? ScheduledTaskRun::all()->keyBy('name')
            : collect();
        $stalled = self::isStalled();

        return collect(self::events())
            ->map(function ($event) use ($rows, $stalled) {
                $name = $event->description;

                if (! is_string($name) || $name === '') {
                    return null;
                }

                $row = $rows->get($name);
                $interval = self::intervalSeconds($event);

                return (object) [
                    'name' => $name,
                    'expression' => $event->expression,
                    'interval' => $interval,
                    'row' => $row,
                    'state' => self::state($event, $row, $interval, $stalled),
                    'lastSeenAt' => $row?->lastSeenAt(),
                ];
            })
            ->filter()
            ->values();
    }

    /** Seconds between two consecutive due times, from the cron expression itself. */
    public static function intervalSeconds($event): int
    {
        try {
            return max(60, (int) $event->nextRunDate()->diffInSeconds($event->nextRunDate('now', 1)));
        } catch (\Throwable $e) {
            report($e);

            return 3600;
        }
    }

    /**
     * ok | running | never_finished | failed | overdue | not_yet_run | unknown
     *
     * Ordered so the worst true statement wins.
     */
    private static function state($event, ?ScheduledTaskRun $row, int $interval, bool $stalled): string
    {
        // A failure is real data whatever the heartbeat says, so it is checked before the stall
        // suppression below.
        if ($row?->last_status === ScheduledTaskRun::STATUS_FAILED) {
            return 'failed';
        }

        // One dead scheduler must not paint 38 red rows for a single root cause the banner above
        // already names. Everything below this line is an inference from elapsed time, and none of
        // it means anything while nothing is ticking.
        if ($stalled) {
            return 'unknown';
        }

        // withoutOverlapping's own expiry is the author's declared budget for this task, so it is
        // the right threshold for both checks below - no invented constant.
        $budget = max(1, (int) $event->expiresAt);

        // A SKIP STREAK, checked before anything else that infers from elapsed time.
        //
        // withoutOverlapping() is a ->skip() REJECT FILTER: ManagesAttributes::withoutOverlapping()
        // ends with $this->skip(fn () => $this->mutex->exists($this)), and ScheduleRunCommand
        // evaluates filtersPass() BEFORE runEvent(). So an overlap dispatches ScheduledTaskSkipped
        // and NOT ScheduledTaskStarting/Finished - Event::run()'s own shouldSkipDueToOverlapping()
        // is only reachable in the race between the filter and mutex->create().
        //
        // A skipped tick therefore touches last_skipped_at and nothing else. Without this check a
        // task wedged behind a stranded mutex keeps a fresh lastSeenAt() while last_started_at
        // stays at or behind last_finished_at, so isRunning() is false, the freshness test below
        // passes, and the row reads 'ok' forever - which is precisely the failure this table
        // exists to catch.
        //
        // Measured from the last time the task really ran, so a routine skip while a run is
        // legitimately in flight stays quiet: only a streak outlasting the task's own overlap
        // budget is trouble. created_at covers a task that has never once run.
        if ($row?->last_status === ScheduledTaskRun::STATUS_SKIPPED && $row->last_skipped_at) {
            $lastReal = $row->lastRanAt() ?? $row->created_at;

            if ($lastReal && $lastReal->diffInMinutes($row->last_skipped_at) > $budget) {
                return 'never_finished';
            }
        }

        if ($row?->isRunning()) {
            // Measured from the start, full stop. A skip does not restamp last_started_at (see
            // above), so a skipped row needs no special origin - and the one it used to get,
            // measured from the PREVIOUS completion, over-reports by the idle gap between runs and
            // so flags a perfectly healthy long run as never_finished early.
            return $row->last_started_at->diffInMinutes(now()) > $budget ? 'never_finished' : 'running';
        }

        $lastSeen = $row?->lastSeenAt();

        // Never run is neutral, never overdue.
        //
        // This used to compare against the table's oldest row, as a stand-in for "how long have we
        // been watching". That is wrong for the case that actually happens: a task ADDED to
        // routes/console.php months later has no row, the window is months wide, and it would
        // render red from the moment it deployed until its first run - up to a month for a monthly
        // task. There is no per-task first-seen to compare against, so the honest answer is to say
        // it has not run yet and let the "N of M reporting" summary carry the signal.
        if ($lastSeen === null) {
            return 'not_yet_run';
        }

        $threshold = $interval + max(120, (int) ($interval * 0.25));

        return $lastSeen->diffInSeconds(now()) > $threshold ? 'overdue' : 'ok';
    }
}
