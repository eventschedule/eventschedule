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
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ScheduledTaskRecorderTest extends TestCase
{
    use RefreshDatabase;

    private function event(string $name = 'demo-task', ?int $exitCode = 0): CallbackEvent
    {
        $event = new CallbackEvent(new CacheEventMutex(app('cache')), fn () => null);
        $event->name($name);
        $event->exitCode = $exitCode;

        return $event;
    }

    public function test_a_run_is_recorded_as_one_upserted_row(): void
    {
        foreach (range(1, 3) as $ignored) {
            ScheduledTaskRecorder::starting(new ScheduledTaskStarting($this->event()));
            ScheduledTaskRecorder::finished(new ScheduledTaskFinished($this->event(), 1.25));
        }

        $this->assertSame(1, ScheduledTaskRun::count(), 'the table must not grow with every run');

        $row = ScheduledTaskRun::firstWhere('name', 'demo-task');
        $this->assertSame(ScheduledTaskRun::STATUS_SUCCEEDED, $row->last_status);
        $this->assertSame('1.25', (string) $row->last_runtime_seconds);
        $this->assertNotNull($row->last_finished_at);
    }

    /**
     * An overlap skip reaches the FINISHED listener with a null exit code - Event::run() returns
     * before finish() assigns one, and ScheduledTaskSkipped is not dispatched for it. Reading that
     * as success would mean a task wedged behind a stranded mutex reports "succeeded" every minute,
     * which is the failure the bounded withoutOverlapping() expiries exist to catch.
     */
    public function test_an_overlap_skip_is_not_recorded_as_success(): void
    {
        ScheduledTaskRecorder::starting(new ScheduledTaskStarting($this->event()));
        ScheduledTaskRecorder::finished(new ScheduledTaskFinished($this->event('demo-task', null), 0.0));

        $row = ScheduledTaskRun::firstWhere('name', 'demo-task');

        $this->assertSame(ScheduledTaskRun::STATUS_SKIPPED, $row->last_status);
        $this->assertNotNull($row->last_skipped_at);
        $this->assertNull($row->last_finished_at, 'a skip did not run, so it did not finish');
        $this->assertSame(0, $row->consecutive_failures, 'a skip is not a failure');
    }

    public function test_a_failure_records_the_message_and_counts_up_then_resets(): void
    {
        foreach (range(1, 2) as $ignored) {
            ScheduledTaskRecorder::starting(new ScheduledTaskStarting($this->event()));
            ScheduledTaskRecorder::failed(new ScheduledTaskFailed($this->event(), new \RuntimeException('boom')));
        }

        $row = ScheduledTaskRun::firstWhere('name', 'demo-task');
        $this->assertSame(ScheduledTaskRun::STATUS_FAILED, $row->last_status);
        $this->assertStringContainsString('boom', $row->last_error);
        $this->assertSame(2, $row->consecutive_failures);
        $this->assertNotNull($row->last_finished_at, 'a failure is a finish, or the row also reads as never-finished');

        ScheduledTaskRecorder::finished(new ScheduledTaskFinished($this->event(), 0.5));
        $this->assertSame(0, ScheduledTaskRun::firstWhere('name', 'demo-task')->consecutive_failures);
    }

    /**
     * The single most important property here. ScheduleRunCommand dispatches ScheduledTaskStarting
     * OUTSIDE its try/catch, so an exception escaping this listener kills schedule:run for the
     * whole minute - and the heartbeat still stamps, so the admin alert would stay green while
     * nothing ran. A monitor that can stop the scheduler is worse than no monitor.
     *
     * Uses an over-length name rather than a dropped table: the recorder short-circuits on
     * Schema::hasTable(), so a missing table never reaches the query and would not exercise the
     * guard at all. A 300-character name reaches updateOrCreate and raises a real QueryException
     * (1406) on this strict connection - verified by reverting the catch, at which point this test
     * fails.
     */
    public function test_a_database_error_can_never_abort_the_scheduler(): void
    {
        $name = str_repeat('a', 300);

        ScheduledTaskRecorder::starting(new ScheduledTaskStarting($this->event($name)));
        ScheduledTaskRecorder::finished(new ScheduledTaskFinished($this->event($name), 1.0));
        ScheduledTaskRecorder::failed(new ScheduledTaskFailed($this->event($name), new \RuntimeException('x')));

        $this->assertSame(0, ScheduledTaskRun::count(), 'nothing was stored, and nothing was thrown');
    }

    /**
     * An un-migrated container must short-circuit BEFORE the query, and without reporting - the
     * listeners run twice per due task per minute, so reporting there would be a Sentry event every
     * few seconds until someone migrated.
     *
     * Fakes the schema check rather than dropping the table: DDL implicitly commits in MySQL, so a
     * real drop escapes RefreshDatabase's transaction and costs the next test a full re-migration.
     * It also would not have exercised the guard - without it, the write throws and guard() catches
     * it, so the old version of this test passed either way.
     */
    public function test_a_missing_table_is_short_circuited_without_reporting(): void
    {
        Schema::shouldReceive('hasTable')->with('scheduled_task_runs')->andReturnFalse();

        $handler = $this->spy(ExceptionHandler::class);

        // An over-length name so the write would FAIL if it were ever reached. Without that, the
        // guard's absence would simply write to a table RefreshDatabase has migrated, nothing would
        // throw, and this test would pass either way - which is exactly how the previous version of
        // it managed to assert nothing.
        $name = str_repeat('a', 300);

        ScheduledTaskRecorder::starting(new ScheduledTaskStarting($this->event($name)));
        ScheduledTaskRecorder::finished(new ScheduledTaskFinished($this->event($name), 1.0));

        $handler->shouldNotHaveReceived('report');
    }

    public function test_an_unnamed_task_is_skipped_rather_than_collapsing_every_row_onto_one(): void
    {
        $event = new CallbackEvent(new CacheEventMutex(app('cache')), fn () => null);
        $event->exitCode = 0;

        ScheduledTaskRecorder::finished(new ScheduledTaskFinished($event, 1.0));

        $this->assertSame(0, ScheduledTaskRun::count());
    }

    public function test_the_rail_is_recorded_so_a_dead_worker_is_visible(): void
    {
        config(['app.scheduler_rail' => 'worker']);

        ScheduledTaskRecorder::starting(new ScheduledTaskStarting($this->event()));

        $this->assertSame('worker', ScheduledTaskRun::firstWhere('name', 'demo-task')->last_via);
    }

    /**
     * last_via is varchar(20) and SCHEDULER_RAIL is operator-settable.
     *
     * Unclamped, an over-long name is a 1406 on a strict connection - which guard() swallows, so
     * per-task recording would go silently dead for every task on every tick while the heartbeat
     * stayed green, and report() would fire ~38 times a minute. The monitor failing invisibly is
     * the one outcome this class exists to prevent.
     */
    public function test_an_over_long_rail_name_does_not_kill_recording(): void
    {
        config(['app.scheduler_rail' => str_repeat('scheduler-container-', 5)]);

        ScheduledTaskRecorder::starting(new ScheduledTaskStarting($this->event()));

        $row = ScheduledTaskRun::firstWhere('name', 'demo-task');

        $this->assertNotNull($row, 'the run must still be recorded');
        $this->assertLessThanOrEqual(20, mb_strlen($row->last_via));
    }

    /**
     * A skip must not erase a recorded failure.
     *
     * Both cron rails can be live at once during a cutover, so a task that just failed on one rail
     * is routinely skipped on the other a moment later. Letting that skip advance last_status
     * turned the row green while last_error and consecutive_failures sat unread - and
     * SchedulerHealth::state() only renders the error when the state is 'failed', so the failure
     * vanished from the page and from the needs-attention list entirely.
     */
    public function test_a_skip_does_not_erase_a_recorded_failure(): void
    {
        ScheduledTaskRecorder::failed(new ScheduledTaskFailed($this->event(), new \RuntimeException('boom')));

        ScheduledTaskRecorder::skipped(new ScheduledTaskSkipped($this->event()));

        $row = ScheduledTaskRun::firstWhere('name', 'demo-task');

        $this->assertSame(ScheduledTaskRun::STATUS_FAILED, $row->last_status,
            'a skip is not evidence the failure is over');
        $this->assertNotNull($row->last_skipped_at, 'but the skip is still recorded');
        $this->assertSame(1, $row->consecutive_failures);
    }

    /** A real run finishing IS evidence, so it clears the failure as before. */
    public function test_a_successful_run_does_clear_a_recorded_failure(): void
    {
        ScheduledTaskRecorder::failed(new ScheduledTaskFailed($this->event(), new \RuntimeException('boom')));

        ScheduledTaskRecorder::starting(new ScheduledTaskStarting($this->event()));
        ScheduledTaskRecorder::finished(new ScheduledTaskFinished($this->event(exitCode: 0), 0.5));

        $row = ScheduledTaskRun::firstWhere('name', 'demo-task');

        $this->assertSame(ScheduledTaskRun::STATUS_SUCCEEDED, $row->last_status);
        $this->assertSame(0, $row->consecutive_failures);
    }

    /**
     * The aggregate heartbeat cannot distinguish rails, so a worker that died while the HTTP cron
     * kept ticking would look healthy. Per-rail keys are what make that visible.
     */
    public function test_rails_age_independently_of_the_aggregate(): void
    {
        Cache::put('scheduler.last_run_at', now()->timestamp, now()->addDay());
        Cache::put('scheduler.last_run_at.http', now()->timestamp, now()->addDays(7));
        Cache::put('scheduler.last_run_at.worker', now()->subHours(3)->timestamp, now()->addDays(7));

        $rails = SchedulerHealth::rails()->keyBy('name');

        $this->assertFalse(SchedulerHealth::isStalled(), 'something is still ticking');
        $this->assertTrue($rails['worker']->stale, 'the dead worker must be visible even so');
        $this->assertFalse($rails['http']->stale);
    }
}
