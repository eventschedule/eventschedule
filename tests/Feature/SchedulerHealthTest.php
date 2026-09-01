<?php

namespace Tests\Feature;

use App\Services\AdminAlertService;
use Illuminate\Console\Events\CommandFinished;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Tests\TestCase;

/**
 * Guards for running the scheduler as a long-lived process (schedule:work on the DigitalOcean
 * worker) rather than inside the /translate_data HTTP request.
 *
 * Two properties matter once nothing serialises the whole chain under a single lock any more, and
 * neither held before the worker: every task must have overlap protection, and a stranded mutex
 * must expire in minutes rather than a day.
 */
class SchedulerHealthTest extends TestCase
{
    use RefreshDatabase;

    /** @return \Illuminate\Console\Scheduling\Event[] */
    private function events(): array
    {
        $events = app(Schedule::class)->events();

        $this->assertNotEmpty($events, 'routes/console.php registered no scheduled tasks');

        return $events;
    }

    /**
     * CallbackEvent::withoutOverlapping() throws a LogicException without a prior ->name(), and
     * shouldSkipDueToOverlapping() short-circuits on the same property - so an unnamed callback
     * task cannot have overlap protection at all, silently.
     *
     * That was survivable while hosted ran the schedule inside translateData(), which holds one
     * lock around its entire chain. schedule:work starts a fresh schedule:run every minute WITHOUT
     * waiting for the previous one, so an unprotected task genuinely double-runs whenever a tick
     * runs long - and most of these send mail or spend money.
     *
     * An unnamed task is also indistinguishable in the logs: it prints as "Running [Callback]".
     */
    /**
     * scheduled_task_runs is keyed on the name, so two entries sharing one would silently merge
     * into a single row and overwrite each other's status - the page would under-report by a task,
     * forever, with no error anywhere.
     */
    public function test_scheduled_task_names_are_unique(): void
    {
        $names = collect($this->events())->map(fn ($event) => $event->description)->filter();

        $this->assertSame(
            $names->count(),
            $names->unique()->count(),
            'duplicate task names: '.$names->duplicates()->implode(', ')
        );
    }

    public function test_every_scheduled_task_is_named(): void
    {
        foreach ($this->events() as $event) {
            $this->assertNotEmpty(
                $event->description,
                'A scheduled task has no ->name(): '.$event->getSummaryForDisplay().
                '. Without one it can have no overlap protection and logs as "Callback".'
            );
        }
    }

    public function test_every_scheduled_task_has_overlap_protection(): void
    {
        foreach ($this->events() as $event) {
            $this->assertTrue(
                $event->withoutOverlapping,
                "Scheduled task [{$event->description}] has no withoutOverlapping(). ".
                'schedule:work does not wait for the previous tick, so it can run twice at once.'
            );
        }
    }

    /**
     * The default expiry is 1440 MINUTES - a full day. The mutex is released in a finally block,
     * which a SIGKILL or an OOM does not run, and App Platform SIGTERMs the worker on every
     * deploy. One bad kill on the default would stop that task for 24 hours with no error
     * anywhere. A stranded mutex should cost one skipped run, not a day of them.
     */
    public function test_no_scheduled_task_keeps_a_day_long_mutex(): void
    {
        foreach ($this->events() as $event) {
            $this->assertLessThan(
                1440,
                $event->expiresAt,
                "Scheduled task [{$event->description}] uses the default 1440-minute overlap ".
                'expiry. Pass an explicit expiry sized just above its own budget.'
            );
        }
    }

    public function test_a_finished_schedule_run_records_the_heartbeat(): void
    {
        Cache::forget('scheduler.last_run_at');

        Event::dispatch(new CommandFinished('schedule:run', new ArrayInput([]), new NullOutput, 0));

        $this->assertNotNull(
            Cache::get('scheduler.last_run_at'),
            'schedule:run must stamp the heartbeat AdminAlertService reads'
        );
    }

    /** Any other command finishing must not look like a scheduler tick. */
    public function test_other_commands_do_not_record_the_heartbeat(): void
    {
        Cache::forget('scheduler.last_run_at');

        Event::dispatch(new CommandFinished('migrate', new ArrayInput([]), new NullOutput, 0));

        $this->assertNull(Cache::get('scheduler.last_run_at'));
    }

    /**
     * The alert is suppressed under APP_TESTING, which phpunit.xml pins on - otherwise it would
     * fire in every test and break the assertions that the admin panel is empty. These two cases
     * turn the flag off so the row is actually exercised.
     */
    public function test_the_alert_fires_when_the_heartbeat_is_stale(): void
    {
        config(['app.is_testing' => false, 'app.scheduler_stale_minutes' => 15]);
        Cache::put('scheduler.last_run_at', now()->subMinutes(30)->timestamp, now()->addDay());
        AdminAlertService::flush();

        $this->assertSame(1, $this->alertCount());
    }

    public function test_the_alert_fires_when_the_scheduler_has_never_run(): void
    {
        config(['app.is_testing' => false]);
        Cache::forget('scheduler.last_run_at');
        AdminAlertService::flush();

        $this->assertSame(1, $this->alertCount());
    }

    public function test_the_alert_clears_while_the_scheduler_is_ticking(): void
    {
        config(['app.is_testing' => false, 'app.scheduler_stale_minutes' => 15]);
        Cache::put('scheduler.last_run_at', now()->subMinutes(2)->timestamp, now()->addDay());
        AdminAlertService::flush();

        $this->assertSame(0, $this->alertCount());
    }

    /** The scheduler_stalled row's count, or 0 when the row is absent (i.e. the alert cleared). */
    private function alertCount(): int
    {
        $row = AdminAlertService::items()->firstWhere('type', 'scheduler_stalled');

        return $row === null ? 0 : (int) $row['count'];
    }
}
