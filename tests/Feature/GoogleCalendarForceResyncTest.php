<?php

namespace Tests\Feature;

use App\Jobs\ForceResyncGoogleCalendar;
use App\Models\CalendarSync;
use App\Models\Event;
use App\Models\Role;
use App\Models\User;
use App\Services\GoogleCalendarService;
use Google\Service\Calendar\Event as GoogleEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Support\Facades\Queue;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * "Resync to Google Calendar" on QUEUE_CONNECTION=sync, the selfhost default.
 *
 * ForceResyncGoogleCalendar works ten events at a time and dispatches the next batch from inside
 * handle(). On sync that dispatch ran on the spot, while the current batch still held its
 * WithoutOverlapping lock, and dontRelease() dropped it without a word: a sync resync synced ten
 * events and stopped. On sync the follow-up now goes afterResponse(), into
 * Application::terminate(), where each batch starts after the previous one released the lock.
 *
 * The job is dispatched exactly the way GoogleCalendarController::forceSyncToGoogle() does it,
 * and terminate() stands in for the end of that request.
 */
class GoogleCalendarForceResyncTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private int $pushed = 0;

    /** @return array{0: User, 1: Role} */
    private function scheduleWithEvents(int $count): array
    {
        $owner = $this->createOwner();
        $owner->google_token = json_encode(['access_token' => 'test-token']);
        $owner->save();

        $role = $this->createRole($owner, 'venue', ['sync_direction' => 'to']);

        for ($i = 1; $i <= $count; $i++) {
            $this->createEvent($role, ['name' => 'Event '.$i]);
        }

        return [$owner, $role];
    }

    /** Google answers every push with a new event id, or throws from the given push onward. */
    private function mockGoogle(?int $failFromPush = null): void
    {
        $this->pushed = 0;

        $this->mock(GoogleCalendarService::class, function ($mock) use ($failFromPush) {
            $mock->shouldReceive('ensureValidToken')->andReturn(true);
            $mock->shouldReceive('createEvent')->andReturnUsing(function (Event $event) use ($failFromPush) {
                $this->pushed++;

                if ($failFromPush !== null && $this->pushed >= $failFromPush) {
                    throw new \RuntimeException('Google said no');
                }

                $googleEvent = new GoogleEvent;
                $googleEvent->setId('g'.$event->id);

                return $googleEvent;
            });
        });
    }

    public function test_on_sync_the_resync_reaches_the_last_event(): void
    {
        // phpunit.xml's default, pinned so the test cannot pass by accident if that changes.
        config(['queue.default' => 'sync']);
        $this->mockGoogle();
        [$owner, $role] = $this->scheduleWithEvents(25);

        ForceResyncGoogleCalendar::dispatch($owner, $role);

        // The first batch runs inside the request; the rest wait for the response to go out.
        $this->assertSame(10, CalendarSync::where('role_id', $role->id)->count());

        $this->app->terminate();

        $this->assertSame(25, CalendarSync::where('role_id', $role->id)->count(), 'a sync resync stopped after the first ten events');
        $this->assertSame(25, $this->pushed, 'no event may be pushed twice');
    }

    /**
     * A real queue keeps the plain dispatch: the follow-up is on the queue as soon as the batch
     * finishes, rather than parked until the end of the request, and a worker picks it up after
     * this batch has released its lock.
     */
    public function test_on_a_real_queue_the_follow_up_is_pushed_at_once(): void
    {
        config(['queue.default' => 'database']);
        Queue::fake();
        $this->mockGoogle();
        [$owner, $role] = $this->scheduleWithEvents(25);

        (new ForceResyncGoogleCalendar($owner, $role))->handle(app(GoogleCalendarService::class));

        $tenth = Event::orderBy('id')->skip(9)->value('id');

        Queue::assertPushed(ForceResyncGoogleCalendar::class, 1);
        Queue::assertPushed(ForceResyncGoogleCalendar::class, function (ForceResyncGoogleCalendar $job) use ($tenth) {
            $cursor = (new \ReflectionProperty($job, 'cursor'))->getValue($job);

            // Not pinned to sync: it goes to the configured connection.
            return $cursor === $tenth && $job->connection === null;
        });
        $this->assertSame(10, CalendarSync::where('role_id', $role->id)->count());
    }

    /**
     * A batch that runs inside terminate() must not throw: terminate() runs its callbacks in a
     * plain loop, so a throw would skip everything registered after it, such as the request's
     * webhooks. The error is reported instead, and the chain stops.
     */
    public function test_on_sync_a_failing_later_batch_does_not_break_the_rest_of_the_request(): void
    {
        config(['queue.default' => 'sync']);
        Exceptions::fake();
        $this->mockGoogle(failFromPush: 11);
        [$owner, $role] = $this->scheduleWithEvents(25);

        ForceResyncGoogleCalendar::dispatch($owner, $role);

        // Registered after the job queued its follow-up, so it runs after that batch.
        $ranAfterIt = false;
        $this->app->terminating(function () use (&$ranAfterIt) {
            $ranAfterIt = true;
        });

        $this->app->terminate();

        $this->assertTrue($ranAfterIt, 'a throwing batch skipped the terminating callbacks after it');
        $this->assertSame(10, CalendarSync::where('role_id', $role->id)->count());
        Exceptions::assertReported(fn (\RuntimeException $e) => $e->getMessage() === 'Google said no');
    }

    /**
     * The button's own first batch runs inside the request, not inside terminate(), so its error
     * still reaches the controller, which answers with an error instead of "queued".
     */
    public function test_on_sync_a_failing_first_batch_still_reaches_the_caller(): void
    {
        config(['queue.default' => 'sync']);
        $this->mockGoogle(failFromPush: 1);
        [$owner, $role] = $this->scheduleWithEvents(3);

        $this->expectException(\RuntimeException::class);

        ForceResyncGoogleCalendar::dispatch($owner, $role);
    }
}
