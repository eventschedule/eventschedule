<?php

namespace Tests\Feature;

use App\Jobs\SendQueuedEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Exception\UnexpectedResponseException;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * A 4xx SMTP reply ("421 Service shutting down, try again shortly") is the mail server asking
 * to be tried again. The job retried it already, but by throwing, and the worker reports every
 * failed attempt, so each restart at the provider paged Sentry for a mail that went out a
 * minute later. It is released now, and only the last attempt throws.
 */
class SendQueuedEmailTemporaryRefusalTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['queue.default' => 'database']);
    }

    private function refuse(int $code): void
    {
        Mail::shouldReceive('to')->andThrow(new UnexpectedResponseException(
            'Expected response code "250" but got code "'.$code.'", with message "'.$code.' refused".',
            $code
        ));
    }

    private function job(?int $roleId = null): SendQueuedEmail
    {
        return (new SendQueuedEmail(new Mailable, 'guest@gmail.com', $roleId))->withFakeQueueInteractions();
    }

    public function test_a_temporary_refusal_is_released_without_a_throw(): void
    {
        $this->refuse(421);

        $job = $this->job();
        $job->handle();

        $job->assertReleased(60);
    }

    public function test_a_temporary_refusal_through_a_schedule_without_its_own_smtp_is_released(): void
    {
        // The path of the reported trace: RoleMailerService falls through to the platform mailer.
        $role = $this->createRole($this->createOwner());
        $this->refuse(421);

        $job = $this->job($role->id);
        $job->handle();

        $job->assertReleased(60);
    }

    public function test_the_last_attempt_throws_so_the_failure_is_still_reported(): void
    {
        $this->refuse(421);

        $job = $this->job();
        $job->job->attempts = $job->tries;

        try {
            $job->handle();
            $this->fail('The last attempt must throw');
        } catch (UnexpectedResponseException $e) {
            $this->assertSame(421, $e->getCode());
        }

        $job->assertNotReleased();
    }

    public function test_a_permanent_refusal_is_not_retried_by_release(): void
    {
        $this->refuse(550);

        $job = $this->job();

        try {
            $job->handle();
            $this->fail('A 5xx must throw');
        } catch (UnexpectedResponseException $e) {
            $this->assertSame(550, $e->getCode());
        }

        $job->assertNotReleased();
    }

    public function test_on_the_sync_connection_a_temporary_refusal_throws_rather_than_being_dropped(): void
    {
        config(['queue.default' => 'sync']);
        $this->refuse(421);

        $job = $this->job();

        $this->expectException(UnexpectedResponseException::class);

        try {
            $job->handle();
        } finally {
            $job->assertNotReleased();
        }
    }
}
