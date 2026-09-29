<?php

namespace Tests\Feature;

use App\Console\Commands\SendCarpoolReminders;
use App\Http\Controllers\CarpoolController;
use App\Jobs\SendQueuedEmail;
use App\Models\CarpoolOffer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Carpool mail carries the signed /user/unsubscribe link, so it has to honour what that link sets.
 *
 * Both senders compared users.is_subscribed with `=== false`. The column has no cast, so an
 * opted-out row reads back as int 0 and the check never fired: the unsubscribe page said "Done"
 * and the carpool emails kept coming. The users here are saved and reloaded for that reason - an
 * in-memory `false` would pass the broken check.
 */
class CarpoolOptOutTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Both senders bail on the log and array mailers before reaching the opt-out.
        config(['app.hosted' => false, 'mail.default' => 'smtp']);
        Queue::fake();
    }

    private function recipient(bool $subscribed): User
    {
        $user = $this->createOwner();
        $user->update(['is_subscribed' => $subscribed]);

        return $user->fresh();
    }

    private function send(object $target, string $method, array $args): void
    {
        (new \ReflectionMethod($target, $method))->invoke($target, ...$args);
    }

    private function dispatched(): int
    {
        return count(Queue::pushedJobs()[SendQueuedEmail::class] ?? []);
    }

    public function test_a_carpool_notification_skips_an_unsubscribed_user(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->createEvent($role);
        $controller = app(CarpoolController::class);

        $this->send($controller, 'sendCarpoolEmail', [$this->recipient(false), $role, 'request_received', $event, new CarpoolOffer]);
        $this->assertSame(0, $this->dispatched());

        // Anchor the absence: the same call for a subscribed user does send.
        $this->send($controller, 'sendCarpoolEmail', [$this->recipient(true), $role, 'request_received', $event, new CarpoolOffer]);
        $this->assertSame(1, $this->dispatched());
    }

    public function test_a_carpool_reminder_skips_an_unsubscribed_user(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->createEvent($role);
        $command = app(SendCarpoolReminders::class);

        $this->send($command, 'sendReminder', [$this->recipient(false), $role, $event, new CarpoolOffer, null]);
        $this->assertSame(0, $this->dispatched());

        $this->send($command, 'sendReminder', [$this->recipient(true), $role, $event, new CarpoolOffer, null]);
        $this->assertSame(1, $this->dispatched());
    }
}
