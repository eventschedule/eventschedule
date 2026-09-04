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
        // No default and no floor here: config/app.php owns both, and a spare copy is one that can
        // go stale. It already had - this line used to carry its own default of 15, which is
        // exactly translate_data_lock's 900-second TTL and the value SchedulerHealthTest proves
        // raises a false alarm.
        return (int) config('app.scheduler_stale_minutes');
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
            // Explicit predicate rather than bare filter(), which would drop a rail named "0" -
            // reachable through SCHEDULER_EXPECTED_RAIL, which has no `?:` to map it away.
            ->filter(fn ($rail) => is_string($rail) && $rail !== '')
            ->unique()
            ->map(function (string $rail) {
                $value = Cache::get('scheduler.last_run_at.'.$rail);

                if (! is_numeric($value)) {
                    // The EXPECTED rail is still worth a row when it has never ticked: it is the
                    // thing the operator is waiting on, and dropping it means the word "worker"
                    // appears nowhere on /admin/queue at exactly the moment it is missing. Any
                    // other rail with no key is simply not in use on this install, so it stays
                    // hidden rather than growing a permanent "never seen" row per install.
                    // `at` is null here - every reader must handle that.
                    return $rail === self::expectedRail()
                        ? (object) ['name' => $rail, 'at' => null, 'stale' => true]
                        : null;
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
     * Cache drivers that every container of one deployment can read.
     *
     * The distinction is the whole reason the heartbeat can lie. `file`, `array` and `octane`
     * keep their data inside one container, so a worker writing its heartbeat there is invisible
     * to the web container reading it - while the database, which scheduled_task_runs uses, is
     * shared by definition.
     */
    public const SHARED_CACHE_DRIVERS = ['database', 'redis', 'memcached', 'dynamodb'];

    public static function cacheStore(): string
    {
        return (string) config('cache.default');
    }

    public static function cacheStoreIsShared(): bool
    {
        return in_array(self::cacheStore(), self::SHARED_CACHE_DRIVERS, true);
    }

    /**
     * The most recent moment any scheduled task STARTED, from the database.
     *
     * Deliberately a different medium from the heartbeat: scheduled_task_runs is a table, the
     * heartbeat is a cache key. Comparing the two is what separates "nothing is running" from
     * "everything is running and I cannot see the heartbeat".
     */
    public static function lastTaskActivityAt(): ?Carbon
    {
        if (! Schema::hasTable('scheduled_task_runs')) {
            return null;
        }

        $at = ScheduledTaskRun::max('last_started_at');

        return $at ? Carbon::parse($at) : null;
    }

    /**
     * The container and rail that most recently STARTED a scheduled task, or null.
     *
     * Guarded like every other reader of this table (lastTaskActivityAt() above, tasks() below,
     * ScheduledTaskRecorder::write()). /admin/queue used to run these two columns as raw
     * ScheduledTaskRun queries of its own, unguarded - so an install that pulled this release
     * without running `php artisan migrate` got a 42S02 on the one page written to keep working
     * in exactly that state, and the page that would have told the operator what was wrong was
     * the page that crashed.
     *
     * One query, not two: both columns come off the same row.
     *
     * @return object{host: ?string, via: ?string}|null
     */
    public static function lastTaskRunner(): ?object
    {
        if (! Schema::hasTable('scheduled_task_runs')) {
            return null;
        }

        $row = ScheduledTaskRun::whereNotNull('last_started_at')
            ->orderByDesc('last_started_at')
            ->first(['last_host', 'last_via']);

        return $row ? (object) ['host' => $row->last_host, 'via' => $row->last_via] : null;
    }

    /**
     * The scheduler reports stalled, but the database says tasks are still completing.
     *
     * That combination has one common cause: a cache store that is not shared between
     * containers. The worker writes its heartbeat into its own container's cache while its task
     * rows land in the shared database, so isStalled() is permanently true on a completely
     * healthy install - and nothing else on /admin/queue can tell that apart from a worker that
     * genuinely died. docs/DIGITALOCEAN_WORKER.md makes "is CACHE_STORE still database" step 2
     * of its triage list precisely because the UI could not answer it.
     *
     * Gated on the store actually being unshared so the page never asserts a cause it has not
     * established. Fresh task rows on a SHARED store are a different anomaly, and the operator
     * still sees the stall and the store name; they are just not told why.
     */
    public static function cacheIsHidingAHealthyScheduler(): bool
    {
        if (self::cacheStoreIsShared() || ! self::isStalled()) {
            return false;
        }

        $last = self::lastTaskActivityAt();

        return $last !== null && $last->diffInMinutes(now()) <= self::staleMinutes();
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
        // A DECLARED rail settles it before any key is consulted. Keys only prove a rail has
        // ticked at least once, and the worst moment for this panel is a worker that has never
        // ticked at all - a failed build, a crash loop, a SCHEDULER_RAIL typo - where the key is
        // simply absent. Without this the page renders "the cron endpoint is your liveness
        // signal" underneath a red stalled banner, on cutover day, which is the one moment it
        // must not. Naming an expected rail is that declaration.
        $expected = self::expectedRail();

        if ($expected !== null && $expected !== 'http') {
            return false;
        }

        // Any non-http rail key at all, stale or not, means this install HAS a scheduler rail - so
        // it must never be shown copy claiming the cron endpoint is its liveness signal. Suppressing
        // that panel for a week after genuinely retiring a worker is a far smaller harm than telling
        // an operator a dead worker is healthy. An install that never had one has no such key.
        $rails = self::rails();

        if ($rails->contains(fn ($rail) => $rail->name !== 'http')) {
            return false;
        }

        // The database can DISPROVE the claim, and the cache cannot. scheduled_task_runs is written
        // only by the ScheduledTask* events, which the HTTP rail never dispatches - so a fresh row
        // in it is proof that a schedule:run rail is alive, whatever the per-rail cache keys say.
        //
        // That gap is reachable through one plausible misconfiguration: SCHEDULER_EXPECTED_RAIL set
        // at component scope instead of app scope (docs/DIGITALOCEAN_WORKER.md section 3 warns about
        // exactly this), so the web container reads null; CACHE_STORE still unset, so the worker's
        // heartbeat is written to its own container's file cache and is invisible here; and
        // /translate_data still firing as the documented fallback, so the http key IS fresh. The
        // page then tells the operator the cron endpoint is their liveness signal while the worker
        // is quietly running everything - and the branch suppresses the per-task list that would
        // have named the failing task.
        //
        // A genuinely http-only install never writes this table at all, and one that really did
        // retire its worker has rows that have gone stale, so neither is affected.
        $lastTask = self::lastTaskActivityAt();

        if ($lastTask !== null && $lastTask->diffInMinutes(now()) <= self::staleMinutes()) {
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
        // suppression below - but only while it is still the LATEST thing that happened.
        //
        // isRunning() means a run has begun since that failure was recorded, which makes the stored
        // error history. Without this a task that failed once and then hung would render its old
        // one-off error forever: recordSkip() deliberately refuses to move last_status off 'failed',
        // and only a completed run with exit 0 clears it - so 'never_finished' became unreachable
        // and the operator was shown a stale cause for a live outage.
        if ($row?->last_status === ScheduledTaskRun::STATUS_FAILED && ! $row->isRunning()) {
            return 'failed';
        }

        // One dead scheduler must not paint 38 red rows for a single root cause the banner above
        // already names. Everything below this line is an inference from elapsed time, and none of
        // it means anything while nothing is ticking.
        if ($stalled) {
            return 'unknown';
        }

        // How long may this task go without COMPLETING before something is wrong?
        //
        // Two parts. withoutOverlapping's own expiry is the author's declared budget for one run -
        // and it is also the mutex TTL, since CacheEventMutex::create() stores the mutex for
        // expiresAt * 60 seconds. The task's interval is added on top because a task that finished
        // and is simply waiting for its next due minute has legitimately completed nothing for
        // that long: the worst healthy gap is one idle interval plus one full-length run.
        $budget = max(1, (int) $event->expiresAt) + intdiv($interval, 60);

        // The anchor is last_finished_at, and nothing else.
        //
        // NOT last_started_at. A run that overruns its own expiry lets the mutex lapse and a fresh
        // copy launch - routes/console.php's header says so - and every launch restamps
        // last_started_at. Measuring from the start therefore resets the clock every expiresAt
        // minutes, so a permanently hung task that keeps respawning reads "running" forever. A
        // start is evidence the task was LAUNCHED; only a finish is evidence it ran.
        //
        // created_at is the fallback because it is written once and never moves: for a task that
        // has started but never once completed it still yields a growing age.
        //
        // Checked for a SKIPPED row as well as a running one. withoutOverlapping() is a ->skip()
        // reject filter and ScheduleRunCommand evaluates filtersPass() before runEvent(), so an
        // overlap dispatches ScheduledTaskSkipped ALONE - leaving isRunning() false while
        // last_skipped_at keeps lastSeenAt() looking fresh. Without this arm a task wedged behind a
        // mutex it cannot take reads 'ok' indefinitely.
        if ($row?->last_status === ScheduledTaskRun::STATUS_SKIPPED || $row?->isRunning()) {
            $anchor = $row->last_finished_at ?? $row->created_at;

            if ($anchor && $anchor->diffInMinutes(now()) > $budget) {
                return 'never_finished';
            }

            if ($row->isRunning()) {
                return 'running';
            }
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
