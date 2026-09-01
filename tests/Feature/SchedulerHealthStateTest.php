<?php

namespace Tests\Feature;

use App\Models\ScheduledTaskRun;
use App\Services\ScheduledTaskRecorder;
use App\Services\SchedulerHealth;
use Illuminate\Console\Events\ScheduledTaskFinished;
use Illuminate\Console\Events\ScheduledTaskStarting;
use Illuminate\Console\Scheduling\CacheEventMutex;
use Illuminate\Console\Scheduling\CallbackEvent;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schedule as ScheduleFacade;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * How /admin/queue decides a task is in trouble.
 *
 * The interesting property is that "last run 20 hours ago" is healthy for a daily task and
 * catastrophic for an every-minute one, so every judgement is made against the task's own cadence,
 * read live from the schedule rather than stored.
 */
class SchedulerHealthStateTest extends TestCase
{
    use RefreshDatabase;

    private function tick(string $rail = 'cron'): void
    {
        Cache::put('scheduler.last_run_at', now()->timestamp, now()->addDay());
        Cache::put('scheduler.last_run_at.'.$rail, now()->timestamp, now()->addDays(7));
    }

    private function state(string $name): ?string
    {
        return SchedulerHealth::tasks()->firstWhere('name', $name)?->state;
    }

    /**
     * Drive one tick through the real listeners: starting always fires, then finished carries the
     * exit code (null being how an overlap skip actually arrives).
     */
    private function record(string $name, ?int $exitCode): void
    {
        $event = new CallbackEvent(new CacheEventMutex(app('cache')), fn () => null);
        $event->name($name);

        ScheduledTaskRecorder::starting(new ScheduledTaskStarting($event));

        $event->exitCode = $exitCode;
        ScheduledTaskRecorder::finished(new ScheduledTaskFinished($event, 0.5));
    }

    public function test_the_task_list_loads_the_schedule_itself(): void
    {
        // Empty the singleton first. Without this the test proves nothing: PHPUnit's TestCase
        // bootstraps the CONSOLE kernel, which requires routes/console.php before every test, so
        // the schedule is always already populated and SchedulerHealth's own require never runs.
        // A web request under FPM gets no such help - verified separately by booting the HTTP
        // kernel, where the schedule starts empty - so this is the branch that keeps /admin/queue
        // from rendering an empty task list in production.
        $this->app->instance(Schedule::class, new Schedule);

        // The facade caches its resolved instance, and routes/console.php registers through the
        // facade - so without this the require would populate the OLD Schedule object while
        // SchedulerHealth read the new one. A web request never hits that, because nothing has
        // resolved the facade yet, but the test has to reproduce the clean state faithfully.
        ScheduleFacade::clearResolvedInstances();

        $this->assertSame([], app(Schedule::class)->events(), 'the fixture must start empty');

        $this->assertGreaterThan(30, SchedulerHealth::tasks()->count(),
            'SchedulerHealth must load routes/console.php itself when the schedule is empty');
    }

    /**
     * Carbon's second argument to diffForHumans() is $syntax, not "omit the suffix". Passing false
     * gives DIFF_RELATIVE_TO_NOW, i.e. "5m ago" - and the string appends its own "ago", so the
     * largest text on the Scheduler card read "last tick 5m ago ago", in every locale.
     */
    public function test_the_last_tick_label_does_not_say_ago_twice(): void
    {
        $label = __('messages.scheduler_last_tick', [
            'age' => now()->subMinutes(5)->diffForHumans(null, true, true),
        ]);

        $this->assertSame(1, substr_count($label, 'ago'), "rendered: {$label}");
    }

    /** The reader needs the writer's guard, or /admin/queue 500s on an un-migrated selfhost. */
    public function test_the_task_list_survives_a_missing_table(): void
    {
        Schema::shouldReceive('hasTable')->with('scheduled_task_runs')->andReturnFalse();

        $this->assertGreaterThan(30, SchedulerHealth::tasks()->count(),
            'the schedule still renders; only the recorded rows are missing');
    }

    /** A task removed from the schedule stops rendering; no prune job needed. */
    public function test_a_row_for_an_unknown_task_is_not_rendered(): void
    {
        ScheduledTaskRun::create(['name' => 'a-task-that-no-longer-exists', 'last_started_at' => now()]);

        $this->assertNull($this->state('a-task-that-no-longer-exists'));
    }

    public function test_cadence_decides_overdue_not_a_fixed_window(): void
    {
        $this->tick();

        // Both last ran an hour ago. app-release-tickets is hourly, process-queue every minute.
        foreach (['process-queue', 'app-release-tickets'] as $name) {
            ScheduledTaskRun::create([
                'name' => $name,
                'last_started_at' => now()->subMinutes(60),
                'last_finished_at' => now()->subMinutes(60),
                'last_status' => ScheduledTaskRun::STATUS_SUCCEEDED,
            ]);
        }

        $this->assertSame('overdue', $this->state('process-queue'));
        $this->assertSame('ok', $this->state('app-release-tickets'));
    }

    /**
     * An overlap skip is evidence the scheduler considered the task, so it counts toward liveness.
     * process-queue runs a 120s queue:work under a mutex and is legitimately skipped on busy
     * minutes; without this it would sit permanently overdue on the busiest installs.
     *
     * Written through the recorder rather than hand-built. An earlier version of this test invented
     * a row with last_finished_at AFTER last_started_at, which the recorder cannot produce, and so
     * it passed on data that never occurs.
     */
    public function test_a_skip_counts_as_liveness(): void
    {
        $this->tick();

        // The real process-queue cycle: a run completes, the next minute's tick bounces off the
        // mutex because a fresh queue:work is still draining.
        $this->record('process-queue', exitCode: 0);
        ScheduledTaskRun::where('name', 'process-queue')->update([
            'last_started_at' => now()->subMinutes(2),
            'last_finished_at' => now()->subMinutes(2),
        ]);
        $this->record('process-queue', exitCode: null);

        // "running", not "overdue": the mutex being held is evidence a run is in flight, which is
        // the normal state of a task whose work outlasts its own interval. What matters is that a
        // routine skip is never counted as trouble.
        $this->assertSame('running', $this->state('process-queue'));
        $this->assertNotContains($this->state('process-queue'), ['overdue', 'never_finished', 'failed']);
    }

    /**
     * The stranded-mutex case, driven through the real event sequence.
     *
     * ScheduledTaskStarting fires BEFORE Event::run() consults the overlap mutex, so every skipped
     * tick re-stamps last_started_at. Measuring "started and never came back" from that stamp would
     * read ~0 seconds forever and this state could never fire - which is what happened before, for
     * exactly the frequently-skipped tasks that matter most.
     */
    public function test_a_task_stranded_behind_its_mutex_is_flagged(): void
    {
        $this->tick();

        $this->record('process-queue', exitCode: 0);
        ScheduledTaskRun::where('name', 'process-queue')->update([
            'last_started_at' => now()->subHours(3),
            'last_finished_at' => now()->subHours(3),
        ]);

        // Three minutes of ticks that each bounce off the held mutex.
        foreach (range(1, 3) as $ignored) {
            $this->record('process-queue', exitCode: null);
        }

        $this->assertSame('never_finished', $this->state('process-queue'),
            'a task skipped every minute since its last completion must not read as "running"');
    }

    /** One dead scheduler must not paint every row red for a single root cause. */
    public function test_per_task_states_are_suppressed_while_the_scheduler_is_stalled(): void
    {
        Cache::forget('scheduler.last_run_at');

        ScheduledTaskRun::create([
            'name' => 'process-queue',
            'last_started_at' => now()->subDay(),
            'last_finished_at' => now()->subDay(),
            'last_status' => ScheduledTaskRun::STATUS_SUCCEEDED,
        ]);

        $this->assertTrue(SchedulerHealth::isStalled());
        $this->assertSame('unknown', $this->state('process-queue'));
    }

    /** A recorded failure is real data regardless of liveness, so it survives the suppression. */
    public function test_a_failure_is_reported_even_while_stalled(): void
    {
        Cache::forget('scheduler.last_run_at');

        ScheduledTaskRun::create([
            'name' => 'process-queue',
            'last_started_at' => now()->subDay(),
            'last_finished_at' => now()->subDay(),
            'last_status' => ScheduledTaskRun::STATUS_FAILED,
        ]);

        $this->assertSame('failed', $this->state('process-queue'));
    }

    /** On a fresh deploy nothing has run yet, and that is not the same as late. */
    public function test_a_task_that_has_never_run_is_neutral_not_overdue(): void
    {
        $this->tick();

        $this->assertSame('not_yet_run', $this->state('app-update-geoip'));
    }

    public function test_a_run_that_started_and_never_returned_is_flagged(): void
    {
        $this->tick();

        ScheduledTaskRun::create([
            'name' => 'app-translate',
            'last_started_at' => now()->subHours(4),
            'last_finished_at' => null,
            'last_status' => ScheduledTaskRun::STATUS_SUCCEEDED,
        ]);

        $this->assertSame('never_finished', $this->state('app-translate'));
    }

    /**
     * The failure this whole per-rail design exists to catch: the worker dies, the HTTP cron keeps
     * ticking, and the aggregate heartbeat stays fresh. Before naming an expected rail, that left
     * isStalled() false - no banner, no admin alert - AND isHttpRailOnly() true, which replaced the
     * task list with copy asserting the cron endpoint was this install's liveness signal. The page
     * confidently said everything was fine while nothing was running.
     */
    public function test_a_dead_worker_is_reported_even_while_the_http_cron_ticks(): void
    {
        config(['app.scheduler_expected_rail' => 'worker']);

        Cache::put('scheduler.last_run_at', now()->timestamp, now()->addDay());
        Cache::put('scheduler.last_run_at.http', now()->timestamp, now()->addDays(7));
        Cache::put('scheduler.last_run_at.worker', now()->subHour()->timestamp, now()->addDays(7));

        $this->assertTrue(SchedulerHealth::isStalled(),
            'a dead worker must alert even though another rail keeps the aggregate heartbeat fresh');
        $this->assertFalse(SchedulerHealth::isHttpRailOnly(),
            'an install that has a worker must never be told the cron endpoint is its liveness signal');
    }

    /**
     * The mirror image, and the reason this is driven by an explicit expected rail rather than by
     * asking whether any rail looks stale. A retired rail's key lingers for its full 7-day TTL, so
     * the obvious-looking rule would cry wolf for a week after a SUCCESSFUL cutover - exactly when
     * the operator has just deliberately switched the HTTP cron off.
     */
    public function test_retiring_the_http_rail_after_a_cutover_raises_no_alarm(): void
    {
        config(['app.scheduler_expected_rail' => 'worker']);

        Cache::put('scheduler.last_run_at', now()->timestamp, now()->addDay());
        Cache::put('scheduler.last_run_at.worker', now()->timestamp, now()->addDays(7));
        Cache::put('scheduler.last_run_at.http', now()->subHour()->timestamp, now()->addDays(7));

        $this->assertFalse(SchedulerHealth::isStalled());
    }

    /** With no expected rail named, behaviour is exactly what it was - selfhost is unaffected. */
    public function test_an_install_that_names_no_expected_rail_is_unchanged(): void
    {
        config(['app.scheduler_expected_rail' => null]);

        Cache::put('scheduler.last_run_at', now()->timestamp, now()->addDay());
        Cache::put('scheduler.last_run_at.worker', now()->subHour()->timestamp, now()->addDays(7));

        $this->assertFalse(SchedulerHealth::isStalled());
    }

    /** An expected rail that has never ticked at all is stalled, not merely unknown. */
    public function test_an_expected_rail_that_never_ticked_is_stalled(): void
    {
        config(['app.scheduler_expected_rail' => 'worker']);

        Cache::put('scheduler.last_run_at', now()->timestamp, now()->addDay());
        Cache::put('scheduler.last_run_at.http', now()->timestamp, now()->addDays(7));

        $this->assertTrue(SchedulerHealth::isStalled());
    }

    /**
     * The /translate_data rail dispatches no ScheduledTask* events, so the table can never fill on
     * such an install. The page must say so rather than render an empty list that reads as a bug.
     */
    public function test_an_http_only_install_is_detected(): void
    {
        $this->tick('http');
        $this->assertTrue(SchedulerHealth::isHttpRailOnly());

        $this->tick('worker');
        $this->assertFalse(SchedulerHealth::isHttpRailOnly(), 'a live worker means per-task data is real');
    }
}
