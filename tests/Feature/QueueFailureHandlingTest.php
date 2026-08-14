<?php

namespace Tests\Feature;

use App\Console\Commands\RetryFailedJobs;
use App\Jobs\SendQueuedEmail;
use App\Mail\TicketPurchase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\Support\MissingModelProbeJob;
use Tests\TestCase;

/**
 * Covers what happens when a queued job outlives the rows it carries.
 *
 * Sale has no SoftDeletes and sales.event_id / ticket_id / user_id are ON DELETE CASCADE, so
 * deleting an event, a schedule or an account hard-deletes its sales. A TicketPurchase already
 * on the queue then cannot be deserialized, and the failure lands in CallQueuedHandler before
 * the job's handle() ever runs.
 *
 * Note on the harness: Queue::fake() is useless for all of this. The exception happens while the
 * worker deserializes the payload, which a fake never does, so these tests drive the real
 * database queue and the real queue:work.
 */
class QueueFailureHandlingTest extends TestCase
{
    use CreatesScheduleData, RefreshDatabase;

    /**
     * phpunit.xml pins QUEUE_CONNECTION=sync (not forced), so it can be overridden per test.
     *
     * after_commit MUST be turned off with it. config/queue.php enables it for the database
     * connection, and RefreshDatabase wraps every test in a transaction that never commits, so
     * a dispatch would be deferred forever and nothing would reach the jobs table - leaving
     * every "failed_jobs is empty" assertion below trivially, and silently, true.
     */
    private function useDatabaseQueue(): void
    {
        config([
            'queue.default' => 'database',
            'queue.connections.database.after_commit' => false,
        ]);
    }

    /**
     * --sleep=0 matters: Worker::daemon() sleeps before it checks stopWhenEmpty, so the default
     * costs a real 3 second sleep on every call.
     */
    private function work(): void
    {
        Artisan::call('queue:work', [
            '--stop-when-empty' => true,
            '--sleep' => 0,
            '--tries' => 1,
        ]);
    }

    /** A failed_jobs row the retry command can push back without deserializing anything. */
    private function seedFailedJob(string $uuid, ?string $failedAt = null): void
    {
        DB::table('failed_jobs')->insert([
            'uuid' => $uuid,
            'connection' => 'database',
            'queue' => 'default',
            // No data.command key, so RetryCommand::refreshRetryUntil() returns early instead of
            // unserializing. That keeps this fixture about the cap, not about deserialization.
            'payload' => json_encode([
                'uuid' => $uuid,
                'displayName' => 'Tests\Support\BenignProbe',
                'job' => 'Illuminate\Queue\CallQueuedHandler@call',
                'data' => [],
            ]),
            'exception' => 'seeded for test',
            'failed_at' => $failedAt ?? now()->subHour(),
        ]);
    }

    private function saleFixture(): array
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->createEvent($role);
        $sale = $this->createSale($event, $role);

        return [$role, $event, $sale];
    }

    /**
     * The headline: this is the production failure. Fails without
     * SendQueuedEmail::$deleteWhenMissingModels, passes with it.
     */
    public function test_a_missing_sale_no_longer_parks_the_ticket_email_in_failed_jobs(): void
    {
        $this->useDatabaseQueue();
        [$role, $event, $sale] = $this->saleFixture();

        SendQueuedEmail::dispatch(
            new TicketPurchase($sale, $event, $role),
            $sale->email,
            $role->id,
            'en'
        );

        // Without this guard the test could pass by never having queued anything at all.
        $this->assertDatabaseCount('jobs', 1);

        // Raw delete, matching the ON DELETE CASCADE path rather than any model hook.
        DB::table('sales')->where('id', $sale->id)->delete();

        $this->work();

        $this->assertSame(0, DB::table('failed_jobs')->count(), 'a ticket email whose sale is gone must be dropped, not parked');
        $this->assertDatabaseCount('jobs', 0);
    }

    /**
     * The control for the test above: same harness, same deleted sale, but a job that has not
     * opted in still fails loudly. Without this, a green test above would not distinguish
     * "the flag works" from "the worker never ran".
     */
    public function test_a_job_without_the_flag_still_fails_loudly(): void
    {
        $this->useDatabaseQueue();
        [, , $sale] = $this->saleFixture();

        MissingModelProbeJob::dispatch($sale);
        $this->assertDatabaseCount('jobs', 1);

        DB::table('sales')->where('id', $sale->id)->delete();

        $this->work();

        $this->assertDatabaseCount('failed_jobs', 1);
    }

    /**
     * queue:retry unserializes the payload to refresh retryUntil, so a poisoned row throws out
     * of Artisan::call before RetryCommand::handle() reaches its forget(). Its failed_at can
     * therefore never move, it sorts first in the oldest-first batch, and before the per-job
     * catch it took every other failed job in the install down with it.
     */
    public function test_one_undeserializable_job_no_longer_blocks_every_other_retry(): void
    {
        $this->useDatabaseQueue();
        [, , $sale] = $this->saleFixture();

        // A genuinely poisoned row: oldest, so it sorts to the head of the batch.
        MissingModelProbeJob::dispatch($sale);
        DB::table('sales')->where('id', $sale->id)->delete();
        $this->work();
        $this->assertDatabaseCount('failed_jobs', 1);
        DB::table('failed_jobs')->update(['failed_at' => now()->subDay()]);

        $benign = (string) Str::uuid();
        $this->seedFailedJob($benign, now()->subHour());

        Artisan::call('app:retry-failed-jobs', ['--skip-work' => true]);

        $pushed = DB::table('jobs')->pluck('payload')
            ->map(fn ($p) => json_decode($p, true)['uuid'] ?? null);

        $this->assertTrue(
            $pushed->contains($benign),
            'a poisoned row at the head of the batch must not stop the jobs behind it from being retried'
        );
    }

    /**
     * Move a dispatched job's payload from the jobs table into failed_jobs, reproducing a row
     * that failed BEFORE its class opted in to deleteWhenMissingModels - which is exactly the
     * shape of the rows already sitting in production.
     */
    private function stranded(): string
    {
        $job = DB::table('jobs')->first();
        $uuid = json_decode($job->payload, true)['uuid'];

        DB::table('jobs')->delete();
        DB::table('failed_jobs')->insert([
            'uuid' => $uuid,
            'connection' => 'database',
            'queue' => 'default',
            'payload' => $job->payload,
            'exception' => 'ModelNotFoundException (from before the flag was added)',
            'failed_at' => now()->subDay(),
        ]);

        return $uuid;
    }

    /**
     * The production cleanup path. A row that failed before the flag existed can never be
     * retried - queue:retry throws on it - so it would sit in the table forever, holding the
     * System badge red. Since its class now opts in, finishing the drop the flag was meant to
     * do is provably what would have happened had the flag been there all along.
     */
    public function test_a_row_stranded_before_the_flag_existed_is_dropped_on_the_next_run(): void
    {
        $this->useDatabaseQueue();
        [$role, $event, $sale] = $this->saleFixture();

        SendQueuedEmail::dispatch(new TicketPurchase($sale, $event, $role), $sale->email, $role->id, 'en');
        $this->stranded();
        DB::table('sales')->where('id', $sale->id)->delete();

        $this->assertDatabaseCount('failed_jobs', 1);

        Artisan::call('app:retry-failed-jobs', ['--skip-work' => true]);

        $this->assertSame(0, DB::table('failed_jobs')->count(), 'a stranded row whose job opts in must be cleared');
        $this->assertSame(0, DB::table('jobs')->count(), 'and must not be pushed back onto the queue');
    }

    /**
     * The safety half of the rule above: a class that has NOT opted in keeps its failed row.
     * Dropping it would silently override a deliberate default and lose the evidence.
     */
    public function test_a_stranded_row_whose_job_did_not_opt_in_is_left_alone(): void
    {
        $this->useDatabaseQueue();
        [, , $sale] = $this->saleFixture();

        MissingModelProbeJob::dispatch($sale);
        $this->stranded();
        DB::table('sales')->where('id', $sale->id)->delete();

        Artisan::call('app:retry-failed-jobs', ['--skip-work' => true]);

        $this->assertSame(1, DB::table('failed_jobs')->count(), 'a job that never opted in must be left for a human');
    }

    /**
     * The cap itself. Re-inserting the same uuid is exactly what
     * DatabaseUuidFailedJobProvider::log() does when a retried job fails again, so this also
     * pins the "uuid is stable across retries" property the whole design rests on.
     */
    public function test_a_uuid_stops_being_retried_after_the_attempt_limit(): void
    {
        $this->useDatabaseQueue();
        $uuid = (string) Str::uuid();
        $options = ['--max-attempts' => 2, '--cooldown-minutes' => 0, '--skip-work' => true];

        $this->seedFailedJob($uuid);
        Artisan::call('app:retry-failed-jobs', $options);
        $this->assertDatabaseCount('jobs', 1);
        $this->assertDatabaseCount('failed_jobs', 0);

        // It failed again.
        $this->seedFailedJob($uuid);
        Artisan::call('app:retry-failed-jobs', $options);
        $this->assertDatabaseCount('jobs', 2);
        $this->assertDatabaseCount('failed_jobs', 0);

        // And again. This third run is the one that must refuse.
        $this->seedFailedJob($uuid);
        Artisan::call('app:retry-failed-jobs', $options);
        $this->assertSame(2, DB::table('jobs')->count(), 'a job past its retry limit must not be pushed again');
        $this->assertSame(1, DB::table('failed_jobs')->count(), 'and must be left in the table for an operator');
    }

    /** The first retry is deliberately exempt; later ones wait out the cooldown. */
    public function test_a_recently_failed_job_waits_out_its_cooldown(): void
    {
        $this->useDatabaseQueue();
        $uuid = (string) Str::uuid();
        $options = ['--max-attempts' => 5, '--cooldown-minutes' => 15, '--skip-work' => true];

        $this->seedFailedJob($uuid, now());
        Artisan::call('app:retry-failed-jobs', $options);
        $this->assertSame(1, DB::table('jobs')->count(), 'the first retry does not wait: the job already burned its own backoff');

        // Failed again just now - inside the cooldown.
        $this->seedFailedJob($uuid, now());
        Artisan::call('app:retry-failed-jobs', $options);
        $this->assertSame(1, DB::table('jobs')->count(), 'a job that failed seconds ago must not be retried immediately');
        $this->assertDatabaseCount('failed_jobs', 1);

        // Once the gap has passed it goes again.
        DB::table('failed_jobs')->where('uuid', $uuid)->update(['failed_at' => now()->subMinutes(16)]);
        Artisan::call('app:retry-failed-jobs', $options);
        $this->assertDatabaseCount('jobs', 2);
    }

    /** An operator who fixed the cause must be able to un-park a job. */
    public function test_an_operator_retry_clears_the_automatic_budget(): void
    {
        $uuid = (string) Str::uuid();
        Cache::put(RetryFailedJobs::attemptsKey($uuid), 99, now()->addHour());

        RetryFailedJobs::forgetAttempts($uuid);

        $this->assertNull(Cache::get(RetryFailedJobs::attemptsKey($uuid)));
    }

    /**
     * CLAUDE.md requires translateData() and console.php to stay in sync. The second pair is
     * what actually enforces it here: it fails the moment someone re-inlines the retry loop in
     * either rail instead of calling the shared command.
     */
    public function test_both_cron_rails_call_the_capped_retry_command(): void
    {
        $console = file_get_contents(base_path('routes/console.php'));
        $controller = file_get_contents(app_path('Http/Controllers/AppController.php'));

        $this->assertStringContainsString('app:retry-failed-jobs', $console);
        $this->assertStringContainsString('app:retry-failed-jobs', $controller);

        $this->assertStringNotContainsString("'queue:retry'", $console);
        $this->assertStringNotContainsString("'queue:retry'", $controller);
    }
}
