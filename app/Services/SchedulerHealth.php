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
     * rails() reads the stored keys rather than iterating this list, so an operator who sets
     * SCHEDULER_RAIL to something else still sees their rail instead of it silently vanishing.
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

        return is_numeric($value) ? Carbon::createFromTimestamp((int) $value) : null;
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
            ->merge([config('app.scheduler_rail', 'cron')])
            ->unique()
            ->map(function (string $rail) {
                $value = Cache::get('scheduler.last_run_at.'.$rail);

                if (! is_numeric($value)) {
                    return null;
                }

                $at = Carbon::createFromTimestamp((int) $value);

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
        if (self::rails()->contains(fn ($rail) => $rail->name !== 'http')) {
            return false;
        }

        $fresh = self::rails()->reject->stale;

        return $fresh->isNotEmpty() && $fresh->every(fn ($rail) => $rail->name === 'http');
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

        if ($row?->isRunning()) {
            // withoutOverlapping's own expiry is the author's declared budget for this task, so it
            // is the right threshold for "started and never came back" - no invented constant.
            $budget = max(1, (int) $event->expiresAt);

            // Which timestamp to measure from is the whole difficulty here.
            //
            // ScheduleRunCommand dispatches ScheduledTaskStarting BEFORE calling Event::run(), and
            // the overlap check lives inside run() - so a tick that bounces straight off a held
            // mutex still stamps last_started_at. For a task skipped every minute (process-queue
            // and friends) that stamp is always ~now, isRunning() is permanently true, and
            // measuring from it would report "running for 0 seconds" forever. The stranded-mutex
            // case - the single failure this whole table exists to catch - would be unreachable.
            //
            // A skip means no run began, so the last start stamp is an attempt, not a run. Measure
            // from the last real completion instead: "how long has this task been unable to
            // finish" is exactly the number the budget should be compared against.
            // Falling back to last_started_at would defeat the whole point, because that is the
            // stamp the skips keep refreshing. created_at is when this row was first seen, so for
            // a task that has never once completed it still yields a growing age.
            $since = $row->last_status === ScheduledTaskRun::STATUS_SKIPPED
                ? ($row->last_finished_at ?? $row->created_at)
                : $row->last_started_at;

            return $since->diffInMinutes(now()) > $budget ? 'never_finished' : 'running';
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
