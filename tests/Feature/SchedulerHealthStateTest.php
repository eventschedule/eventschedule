<?php

namespace Tests\Feature;

use App\Models\ScheduledTaskRun;
use App\Services\ScheduledTaskRecorder;
use App\Services\SchedulerHealth;
use Illuminate\Console\Events\ScheduledTaskFailed;
use Illuminate\Console\Events\ScheduledTaskFinished;
use Illuminate\Console\Events\ScheduledTaskSkipped;
use Illuminate\Console\Events\ScheduledTaskStarting;
use Illuminate\Console\Scheduling\CacheEventMutex;
use Illuminate\Console\Scheduling\CallbackEvent;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
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

    /** A tick that actually RAN, driven through the real listeners: starting, then finished. */
    private function record(string $name, ?int $exitCode): void
    {
        $event = $this->event($name);

        ScheduledTaskRecorder::starting(new ScheduledTaskStarting($event));

        $event->exitCode = $exitCode;
        ScheduledTaskRecorder::finished(new ScheduledTaskFinished($event, 0.5));
    }

    /**
     * A tick BLOCKED by withoutOverlapping(), which is a ->skip() reject filter.
     *
     * ScheduleRunCommand evaluates filtersPass() before runEvent(), so an overlap dispatches
     * ScheduledTaskSkipped alone - neither Starting nor Finished. Recording it any other way
     * invents a sequence the framework never produces, and every inference the page draws from a
     * skipped row then rests on data that cannot occur.
     * test_without_overlapping_is_a_reject_filter_not_an_in_run_check() pins that contract.
     */
    private function skip(string $name): void
    {
        ScheduledTaskRecorder::skipped(new ScheduledTaskSkipped($this->event($name)));
    }

    private function event(string $name): CallbackEvent
    {
        $event = new CallbackEvent(new CacheEventMutex(app('cache')), fn () => null);

        return $event->name($name);
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
     */
    public function test_a_skip_counts_as_liveness(): void
    {
        $this->tick();

        // A completed run, then two minutes in which the mutex was held by the NEXT run - which is
        // over by now, so nothing is in flight and only the skips keep the row fresh. Built this
        // way on purpose: with isRunning() false, state() has to fall through to lastSeenAt(),
        // which is the property under test. A row that is still running short-circuits above it.
        $this->record('process-queue', exitCode: 0);
        ScheduledTaskRun::where('name', 'process-queue')->update([
            'last_started_at' => now()->subMinutes(4),
            'last_finished_at' => now()->subMinutes(3),
        ]);
        $this->skip('process-queue');

        $run = ScheduledTaskRun::where('name', 'process-queue')->first();
        $this->assertFalse($run->isRunning(), 'the fixture must reach the lastSeenAt() comparison');

        // 'ok', not 'overdue': process-queue is due every minute and its last START was 4 minutes
        // ago, which is past the overdue threshold. Only the skip keeps it healthy.
        $this->assertSame('ok', $this->state('process-queue'));
    }

    /** The property the row above rests on, asserted directly rather than inferred from a state. */
    public function test_last_seen_at_counts_a_skip(): void
    {
        $run = new ScheduledTaskRun([
            'last_started_at' => now()->subHour(),
            'last_skipped_at' => now()->subMinute(),
        ]);

        $this->assertTrue($run->lastSeenAt()->equalTo($run->last_skipped_at),
            'a skip is evidence the scheduler considered the task and must count toward liveness');
    }

    /**
     * The failure this table exists to catch, driven through a timeline production can actually
     * produce.
     *
     * A run hangs. Its mutex TTL is the task's own withoutOverlapping() budget, so every tick
     * inside that window is skipped - and when the TTL lapses a FRESH COPY launches, restamping
     * last_started_at. That respawn is why the verdict cannot be measured from the start: doing so
     * resets the clock every budget minutes and the task reads "running" forever. Anchoring on
     * last_finished_at is what makes it stick.
     */
    public function test_a_hung_task_that_keeps_respawning_is_flagged(): void
    {
        $this->tick();

        // 10:00 - a clean run completes.
        $this->record('process-queue', exitCode: 0);
        ScheduledTaskRun::where('name', 'process-queue')->update([
            'last_started_at' => now()->subMinutes(62),
            'last_finished_at' => now()->subMinutes(61),
        ]);

        // 10:01 onward - a copy starts and hangs; ticks bounce off the mutex until the 20-minute
        // TTL lapses, at which point another copy starts. Sixty minutes of that.
        foreach ([60, 40, 20] as $minutesAgo) {
            ScheduledTaskRecorder::starting(new ScheduledTaskStarting($this->event('process-queue')));
            ScheduledTaskRun::where('name', 'process-queue')
                ->update(['last_started_at' => now()->subMinutes($minutesAgo)]);

            $this->skip('process-queue');
        }

        $run = ScheduledTaskRun::where('name', 'process-queue')->first();
        $this->assertTrue($run->isRunning(), 'a copy is in flight, so the row looks live');
        $this->assertTrue($run->lastSeenAt()->gt(now()->subMinute()),
            'and the skips keep lastSeenAt() fresh, which is why freshness cannot be the test');

        $this->assertSame('never_finished', $this->state('process-queue'),
            'nothing has COMPLETED for an hour; a respawn must not reset the verdict');
    }

    /**
     * The other half of the hung test: a run that legitimately outlasts its own interval.
     *
     * This is the false positive the interval slack exists for. app-translate is due every fifteen
     * minutes with a twenty-minute budget, so a run that takes eighteen is healthy - but by the time
     * it is sixteen minutes in, the PREVIOUS completion is thirty-one minutes old. Measuring the
     * anchor against the budget alone would call that never_finished while the run is well inside
     * the window its author declared for it.
     */
    public function test_a_run_that_outlasts_its_own_interval_is_not_flagged(): void
    {
        $this->tick();

        // T+0 completes. T+15 the next one starts and is still going at T+31, so the T+30 tick was
        // skipped. Sixteen minutes into a run budgeted for twenty.
        $this->record('app-translate', exitCode: 0);
        ScheduledTaskRun::where('name', 'app-translate')->update([
            'last_finished_at' => now()->subMinutes(31),
            'last_started_at' => now()->subMinutes(16),
        ]);
        $this->skip('app-translate');

        $this->assertSame('running', $this->state('app-translate'),
            'a run inside its own withoutOverlapping budget must not be called never_finished '.
            'just because the previous completion is older than that budget'
        );
    }

    /**
     * A task wedged behind a mutex nothing in this table created.
     *
     * Reachable on a mixed deploy: the pre-4d23fdfcb code took its mutexes with the framework
     * default of 1440 minutes, so one stranded by a SIGKILL before this table existed outlives
     * every current expiry. The row is then created by the skips themselves - no start, no finish -
     * and created_at is the only window there is.
     */
    public function test_a_task_wedged_with_no_recorded_run_is_flagged(): void
    {
        $this->tick();

        $this->skip('process-queue');
        ScheduledTaskRun::where('name', 'process-queue')->update(['created_at' => now()->subHours(3)]);
        $this->skip('process-queue');

        $run = ScheduledTaskRun::where('name', 'process-queue')->first();
        $this->assertNull($run->last_started_at, 'nothing ever recorded a start for this task');

        $this->assertSame('never_finished', $this->state('process-queue'));
    }

    /**
     * That row has no last_started_at, so the label must not claim a start time it does not have.
     *
     * Rendered rather than asserted on the state string: the state was correct and the ROW read
     * "started ? ago and never finished" at the operator, which no state assertion could see.
     */
    public function test_a_wedged_task_with_no_recorded_run_renders_an_honest_label(): void
    {
        $this->tick();

        $this->skip('process-queue');
        ScheduledTaskRun::where('name', 'process-queue')->update(['created_at' => now()->subHours(3)]);
        $this->skip('process-queue');

        $task = SchedulerHealth::tasks()->firstWhere('name', 'process-queue');
        $html = view('admin.partials._scheduled-task-row', ['task' => $task])->render();

        $this->assertStringContainsString(__('messages.scheduler_never_ran'), $html);
        $this->assertStringNotContainsString('never finished', $html,
            'a task with no recorded start must not be described as having started');
    }

    /**
     * The rendered age must come from the last COMPLETION, not the latest respawn.
     *
     * state() was moved off last_started_at because a run that overruns its expiry lets the mutex
     * lapse and a fresh copy launch, restamping it. The label kept reading it, so a task that had
     * produced nothing for an hour rendered "started 20m ago and never finished" - contradicting
     * the verdict printed beside it.
     */
    public function test_the_never_finished_label_reports_time_since_the_last_completion(): void
    {
        $this->tick();

        $this->record('process-queue', exitCode: 0);
        ScheduledTaskRun::where('name', 'process-queue')->update([
            'last_finished_at' => now()->subMinutes(90),
            'last_started_at' => now()->subMinutes(5),
            'last_status' => ScheduledTaskRun::STATUS_SKIPPED,
            'last_skipped_at' => now(),
        ]);

        $task = SchedulerHealth::tasks()->firstWhere('name', 'process-queue');
        $this->assertSame('never_finished', $task->state);

        $html = view('admin.partials._scheduled-task-row', ['task' => $task])->render();
        $label = strip_tags(explode('<details', $html)[0]);

        $this->assertStringContainsString('1h', $label,
            'the age must be measured from the last completion (90m), not the latest respawn (5m)');
        $this->assertStringNotContainsString('5m', $label);
    }

    /**
     * A failure must not go on masking a hang that started after it.
     *
     * recordSkip() deliberately refuses to move last_status off 'failed', and only a completed run
     * with exit 0 clears it - so without this a task that failed once and then wedged rendered its
     * old one-off error forever and never_finished was unreachable.
     */
    public function test_a_hang_after_a_failure_is_reported_as_the_hang(): void
    {
        $this->tick();

        ScheduledTaskRecorder::failed(
            new ScheduledTaskFailed($this->event('process-queue'), new \RuntimeException('boom'))
        );
        ScheduledTaskRun::where('name', 'process-queue')->update(['last_finished_at' => now()->subHour()]);

        // A later copy starts and hangs; every tick since has bounced off its mutex.
        ScheduledTaskRecorder::starting(new ScheduledTaskStarting($this->event('process-queue')));
        $this->skip('process-queue');

        $row = ScheduledTaskRun::where('name', 'process-queue')->first();
        $this->assertSame(ScheduledTaskRun::STATUS_FAILED, $row->last_status,
            'the failure is still on the row - it is the VERDICT that must move on');

        $this->assertSame('never_finished', $this->state('process-queue'));
    }

    /** With no later run, the failure is still the latest thing that happened and still governs. */
    public function test_a_failure_with_no_later_run_still_reports_failed(): void
    {
        $this->tick();

        ScheduledTaskRecorder::failed(
            new ScheduledTaskFailed($this->event('process-queue'), new \RuntimeException('boom'))
        );
        $this->skip('process-queue');

        $this->assertSame('failed', $this->state('process-queue'));
    }

    /**
     * The framework contract the whole model rests on, driven through ScheduleRunCommand itself.
     *
     * withoutOverlapping() ends with $this->skip(fn () => $this->mutex->exists($this)) - a REJECT
     * FILTER - and ScheduleRunCommand evaluates filtersPass() BEFORE runEvent(). So an overlap
     * dispatches ScheduledTaskSkipped and neither Starting nor Finished. An earlier version of this
     * file reasoned from Event::run()'s internal shouldSkipDueToOverlapping() instead, concluded
     * that an overlap arrives as Starting + Finished(exitCode: null), and built the per-task health
     * model on a sequence that never occurs.
     *
     * Asserting on filtersPass() alone was not enough: moving the check back inside run() would
     * leave that green. This runs the real command and asserts on the events it actually emits.
     */
    public function test_an_overlap_dispatches_only_the_skipped_event(): void
    {
        $schedule = new Schedule;
        $this->app->instance(Schedule::class, $schedule);

        $ran = false;
        $event = $schedule->call(function () use (&$ran) {
            $ran = true;
        })->everyMinute()->name('overlap-probe');
        $event->withoutOverlapping(20);

        $this->assertTrue($event->filtersPass($this->app), 'an unheld mutex must not reject the event');

        $event->mutex->create($event);

        try {
            $seen = [];
            Event::listen(ScheduledTaskStarting::class, function () use (&$seen) {
                $seen[] = 'starting';
            });
            Event::listen(ScheduledTaskFinished::class, function () use (&$seen) {
                $seen[] = 'finished';
            });
            Event::listen(ScheduledTaskSkipped::class, function () use (&$seen) {
                $seen[] = 'skipped';
            });

            $this->artisan('schedule:run');

            $this->assertSame(['skipped'], $seen,
                'a held overlap mutex must dispatch ScheduledTaskSkipped ALONE, before runEvent()');
            $this->assertFalse($ran, 'and the task itself must not have run');
        } finally {
            $event->mutex->forget($event);
        }
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

        // created_at is written when the row is first inserted, i.e. when that run started - it
        // cannot be newer than last_started_at in production, and the verdict anchors on it while
        // there is no completion to measure from. Left at now() this fixture would be unreachable.
        ScheduledTaskRun::where('name', 'app-translate')->update(['created_at' => now()->subHours(4)]);

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
    /**
     * A rail named outside SchedulerHealth::RAILS must still be read.
     *
     * rails() iterates a hardcoded list, so before it merged expectedRail() the web container never
     * looked up the key a worker with a custom SCHEDULER_RAIL was writing. firstWhere() in
     * isStalled() then returned null and the install sat on a permanent red alert - the exact
     * opposite of what naming an expected rail is for.
     */
    public function test_a_custom_expected_rail_is_read_rather_than_reported_stalled(): void
    {
        config(['app.scheduler_expected_rail' => 'scheduler']);

        Cache::put('scheduler.last_run_at', now()->timestamp, now()->addDay());
        Cache::put('scheduler.last_run_at.scheduler', now()->timestamp, now()->addDays(7));

        $this->assertNotNull(SchedulerHealth::rails()->firstWhere('name', 'scheduler'),
            'a rail named only by SCHEDULER_EXPECTED_RAIL must still be listed');
        $this->assertFalse(SchedulerHealth::isStalled(),
            'the expected rail is ticking, so nothing is stalled');
    }

    /**
     * FAILS before the change: isHttpRailOnly() was true, so /admin/queue rendered "the cron
     * endpoint is the liveness signal for this install" underneath a red stalled banner.
     *
     * A worker that has NEVER ticked writes no cache key at all, so rails() had nothing to find
     * and the "does any non-http rail exist" guard never fired. That is the state after step 7 of
     * the cutover if the worker fails to build, crash-loops, or has a SCHEDULER_RAIL typo - the
     * one moment the page must not reassure anybody. A DECLARED rail settles it without consulting
     * any key.
     */
    public function test_a_worker_that_never_ticked_is_not_called_an_http_only_install(): void
    {
        config(['app.scheduler_expected_rail' => 'worker']);

        $this->tick('http');   // the cron keeps the aggregate and the http rail fresh

        $this->assertTrue(SchedulerHealth::isStalled(),
            'an expected rail that has never been seen is stalled');
        $this->assertFalse(SchedulerHealth::isHttpRailOnly(),
            'an install that declares a worker rail must never be told the cron endpoint is its liveness signal');
    }

    /**
     * FAILS before the change: rails() dropped any rail with no cache key, so with a worker that
     * had never ticked the word "worker" appeared nowhere on /admin/queue - the operator saw http
     * ticking happily and no sign of the thing they were waiting for.
     *
     * Scoped to the EXPECTED rail: an unused entry in RAILS stays hidden rather than every install
     * growing permanent "never seen" rows.
     */
    public function test_an_expected_rail_that_never_ticked_is_listed_as_never_seen(): void
    {
        config(['app.scheduler_expected_rail' => 'worker']);

        $this->tick('http');

        $worker = SchedulerHealth::rails()->firstWhere('name', 'worker');

        $this->assertNotNull($worker, 'the rail being waited on must be listed');
        $this->assertNull($worker->at, 'it has never ticked, so it has no timestamp');
        $this->assertTrue($worker->stale);

        $this->assertNull(SchedulerHealth::rails()->firstWhere('name', 'cron'),
            'a rail that is neither expected nor ticking stays hidden');
    }

    /**
     * The cutover failure nothing else on the page can distinguish: a cache store that is not
     * shared between containers. The worker writes its heartbeat into its own container while its
     * task rows land in the shared database, so the scheduler reads as stalled on a completely
     * healthy install.
     */
    public function test_a_per_container_cache_with_live_tasks_is_reported_as_the_cause(): void
    {
        config(['app.scheduler_expected_rail' => 'worker', 'cache.default' => 'file']);

        ScheduledTaskRun::create([
            'name' => 'process-queue',
            'last_started_at' => now(),
            'last_finished_at' => now(),
            'last_status' => ScheduledTaskRun::STATUS_SUCCEEDED,
            'last_via' => 'worker',
            'last_host' => 'scheduler-abc123',
        ]);

        $this->assertTrue(SchedulerHealth::isStalled());
        $this->assertFalse(SchedulerHealth::cacheStoreIsShared());
        $this->assertTrue(SchedulerHealth::cacheIsHidingAHealthyScheduler(),
            'fresh task rows in the database with no readable heartbeat means the cache is the broken thing');
    }

    /**
     * The mirror image: on a shared store the page must NOT blame the cache, because that
     * explanation has not been established. The operator still sees the stall and the store name.
     */
    public function test_a_shared_cache_is_never_blamed(): void
    {
        config(['app.scheduler_expected_rail' => 'worker', 'cache.default' => 'database']);

        ScheduledTaskRun::create([
            'name' => 'process-queue',
            'last_started_at' => now(),
            'last_status' => ScheduledTaskRun::STATUS_SUCCEEDED,
            'last_via' => 'worker',
            'last_host' => 'scheduler-abc123',
        ]);

        $this->assertTrue(SchedulerHealth::cacheStoreIsShared());
        $this->assertFalse(SchedulerHealth::cacheIsHidingAHealthyScheduler());
    }

    /**
     * And with no task activity at all it really is a dead worker, so the cache must not be blamed
     * for that either.
     */
    public function test_a_dead_worker_on_a_per_container_cache_is_not_blamed_on_the_cache(): void
    {
        config(['app.scheduler_expected_rail' => 'worker', 'cache.default' => 'file']);

        $this->assertTrue(SchedulerHealth::isStalled());
        $this->assertFalse(SchedulerHealth::cacheIsHidingAHealthyScheduler(),
            'no task rows means nothing is running - that is a dead worker, not a cache problem');
    }

    public function test_an_http_only_install_is_detected(): void
    {
        $this->tick('http');
        $this->assertTrue(SchedulerHealth::isHttpRailOnly());

        $this->tick('worker');
        $this->assertFalse(SchedulerHealth::isHttpRailOnly(), 'a live worker means per-task data is real');
    }
}
