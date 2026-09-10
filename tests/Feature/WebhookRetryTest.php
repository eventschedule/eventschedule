<?php

namespace Tests\Feature;

use App\Jobs\SendWebhook;
use App\Models\Sale;
use App\Models\User;
use App\Models\Webhook;
use App\Models\WebhookDelivery;
use App\Services\WebhookService;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Promise\Create;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Webhook retries.
 *
 * WebhookService sends attempt 1 with dispatchAfterResponse(), which always runs on the sync
 * connection inside Application::terminate(). So SendWebhook's old $tries = 3 and $backoff never
 * applied, and a failed delivery was never sent again. Worse, the exception it threw to ask for
 * that retry escaped terminate(), which skips every callback after a throw, so one unreachable
 * endpoint silenced every other subscriber's webhook for the same request.
 *
 * Harness notes:
 * - The endpoint is a public IP literal. A hostname such as example.test never resolves, so the
 *   SSRF check refuses it and every test would take the Blocked branch.
 * - A retry needs a real queue, so the queued cases switch to the database connection with
 *   after_commit off (see QueueFailureHandlingTest::useDatabaseQueue()).
 * - Application::terminate() does not clear its callbacks, so each test calls it exactly once.
 */
class WebhookRetryTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private const URL = 'https://93.184.216.34/hook';

    protected function setUp(): void
    {
        parent::setUp();

        // Nothing here may reach a real endpoint.
        Http::preventStrayRequests();
    }

    private function useDatabaseQueue(): void
    {
        config([
            'queue.default' => 'database',
            'queue.connections.database.after_commit' => false,
        ]);
    }

    /** --sleep=0 matters: Worker::daemon() sleeps before it checks stopWhenEmpty. */
    private function work(): void
    {
        Artisan::call('queue:work', [
            '--stop-when-empty' => true,
            '--sleep' => 0,
            '--tries' => 3,
        ]);
    }

    /** @return array{0: Sale, 1: Webhook} */
    private function subscribedSale(string $url = self::URL): array
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue');
        $event = $this->createEvent($role, ['payment_method' => 'stripe']);
        $ticket = $this->createTicket($event, ['price' => 100, 'quantity' => 10]);
        $sale = $this->createSale($event, $role, ['status' => 'paid', 'payment_amount' => 100], $ticket);

        return [$sale, $this->webhookFor($owner, $url)];
    }

    private function webhookFor(User $owner, string $url): Webhook
    {
        return Webhook::create([
            'user_id' => $owner->id,
            'url' => $url,
            'secret' => 'shh',
            'event_types' => ['sale.paid'],
            'is_active' => true,
        ]);
    }

    /** Attempt 1, the way production sends it: dispatched after the response, run on sync. */
    private function sendFirstAttempt(Sale $sale): void
    {
        WebhookService::dispatch('sale.paid', $sale);

        $this->app->terminate();
    }

    /** @return Collection<int, object{attempt: int, available_at: int}> */
    private function queuedAttempts(): Collection
    {
        return DB::table('jobs')->orderBy('id')->get()->map(function ($row) {
            $job = unserialize(json_decode($row->payload, true)['data']['command']);

            return (object) ['attempt' => $job->attempt, 'available_at' => (int) $row->available_at];
        });
    }

    /** A stub for an endpoint that cannot be reached, keyed so a data provider can name it. */
    private function unreachable(string $how): \Closure
    {
        return match ($how) {
            'refused' => Http::failedConnection(),
            'timeout' => Http::failedConnection('cURL error 28: Operation timed out after 5001 milliseconds with 0 bytes received'),
            // A transfer that failed without a response. Laravel converts only ConnectException,
            // so this one reaches the job as Guzzle's own RequestException.
            'reset' => fn ($request) => Create::rejectionFor(new RequestException(
                'cURL error 56: Recv failure: Connection reset by peer',
                $request->toPsrRequest()
            )),
        };
    }

    public static function unreachableEndpoints(): array
    {
        return [
            'connection refused' => ['refused'],
            'timeout' => ['timeout'],
            'connection reset' => ['reset'],
        ];
    }

    public function test_a_5xx_queues_attempt_two_about_thirty_seconds_out(): void
    {
        $this->useDatabaseQueue();
        Http::fake(['*' => Http::response('down', 503)]);
        [$sale] = $this->subscribedSale();

        $this->sendFirstAttempt($sale);

        $delivery = WebhookDelivery::sole();
        $this->assertFalse($delivery->success);
        $this->assertSame(503, $delivery->response_status);

        $queued = $this->queuedAttempts();
        $this->assertCount(1, $queued, 'a 503 must queue exactly one retry');
        $this->assertSame(2, $queued[0]->attempt);
        $this->assertEqualsWithDelta(now()->addSeconds(30)->getTimestamp(), $queued[0]->available_at, 2);
    }

    /**
     * End to end through the real worker: attempt 1 after the response, then two queued retries,
     * then nothing. Every attempt leaves its own row, and none of them parks a failed job.
     */
    public function test_a_failing_endpoint_gets_three_attempts_in_all(): void
    {
        $this->useDatabaseQueue();
        Http::fake(['*' => Http::response('down', 503)]);
        [$sale] = $this->subscribedSale();

        $this->sendFirstAttempt($sale);

        $this->travel(31)->seconds();
        $this->work();

        $queued = $this->queuedAttempts();
        $this->assertCount(1, $queued);
        $this->assertSame(3, $queued[0]->attempt);
        $this->assertEqualsWithDelta(now()->addSeconds(60)->getTimestamp(), $queued[0]->available_at, 2);

        $this->travel(61)->seconds();
        $this->work();

        $this->assertSame(3, WebhookDelivery::count(), 'one delivery row per attempt, and no fourth attempt');
        $this->assertSame(0, WebhookDelivery::where('success', true)->count());
        $this->assertDatabaseCount('jobs', 0);
        $this->assertDatabaseCount('failed_jobs', 0);
        Http::assertSentCount(3);
    }

    public function test_a_retry_that_gets_through_ends_the_sequence(): void
    {
        $this->useDatabaseQueue();
        Http::fake(['*' => Http::sequence()->push('down', 503)->push('ok', 200)]);
        [$sale] = $this->subscribedSale();

        $this->sendFirstAttempt($sale);
        $this->travel(31)->seconds();
        $this->work();

        $this->assertSame([false, true], WebhookDelivery::orderBy('id')->pluck('success')->all());
        $this->assertDatabaseCount('jobs', 0);
    }

    /** @dataProvider unreachableEndpoints */
    public function test_an_unreachable_endpoint_is_retried(string $how): void
    {
        $this->useDatabaseQueue();
        Http::fake(['*' => $this->unreachable($how)]);
        [$sale] = $this->subscribedSale();

        $this->sendFirstAttempt($sale);

        $delivery = WebhookDelivery::sole();
        $this->assertFalse($delivery->success);
        $this->assertNull($delivery->response_status);
        $this->assertStringContainsString('cURL error', $delivery->response_body);

        $this->assertSame([2], $this->queuedAttempts()->pluck('attempt')->all());
    }

    public static function finalAnswers(): array
    {
        return [
            'redirect' => [301],
            'client error' => [404],
        ];
    }

    /** @dataProvider finalAnswers */
    public function test_a_3xx_or_4xx_is_not_retried(int $status): void
    {
        $this->useDatabaseQueue();
        Http::fake(['*' => Http::response('', $status, ['Location' => 'https://93.184.216.34/elsewhere'])]);
        [$sale] = $this->subscribedSale();

        $this->sendFirstAttempt($sale);

        $this->assertSame($status, WebhookDelivery::sole()->response_status);
        $this->assertDatabaseCount('jobs', 0);
    }

    public function test_a_blocked_address_is_not_retried(): void
    {
        $this->useDatabaseQueue();
        Http::fake();
        [$sale] = $this->subscribedSale('https://127.0.0.1/hook');

        $this->sendFirstAttempt($sale);

        $this->assertStringStartsWith('Blocked', WebhookDelivery::sole()->response_body);
        $this->assertDatabaseCount('jobs', 0);
        Http::assertNothingSent();
    }

    public function test_the_third_attempt_is_the_last(): void
    {
        $this->useDatabaseQueue();
        Http::fake(['*' => Http::response('down', 503)]);
        [, $webhook] = $this->subscribedSale();

        (new SendWebhook($webhook, ['event' => 'sale.paid', 'data' => []], 'sale.paid', 3))->handle();

        $this->assertSame(1, WebhookDelivery::count());
        $this->assertDatabaseCount('jobs', 0);
    }

    public static function failures(): array
    {
        return [
            '5xx' => ['5xx'],
            'unreachable' => ['refused'],
        ];
    }

    /**
     * The selfhost default. There is no queue to hold a retry back, and a retry dispatched on
     * sync would ignore its delay and run at once, so it would show up here as a second row.
     *
     * @dataProvider failures
     */
    public function test_on_sync_a_failure_neither_throws_nor_retries(string $failure): void
    {
        // phpunit.xml's default, pinned so the test cannot pass by accident if that changes.
        config(['queue.default' => 'sync']);
        Http::fake(['*' => $failure === '5xx' ? Http::response('down', 503) : $this->unreachable($failure)]);
        [$sale] = $this->subscribedSale();

        // Before the fix the exception meant to trigger $tries escaped terminate() from here.
        $this->sendFirstAttempt($sale);

        $this->assertSame(1, WebhookDelivery::count());
        Http::assertSentCount(1);
    }

    /**
     * The headline regression. Both webhooks belong to one owner, so one sale fires both, each
     * from its own terminating callback. The unreachable one runs first; before the fix its
     * ConnectionException escaped terminate() and the healthy endpoint heard nothing.
     */
    public function test_one_unreachable_endpoint_does_not_silence_the_next(): void
    {
        Http::fake([
            '93.184.216.34/*' => Http::failedConnection(),
            '93.184.216.35/*' => Http::response('ok', 200),
        ]);
        [$sale, $broken] = $this->subscribedSale('https://93.184.216.34/hook');
        $healthy = $this->webhookFor($broken->user, 'https://93.184.216.35/hook');

        $this->sendFirstAttempt($sale);

        $this->assertSame(
            ['https://93.184.216.34/hook', 'https://93.184.216.35/hook'],
            Http::recorded()->map(fn ($pair) => $pair[0]->url())->all(),
            'the unreachable endpoint has to go first for this test to prove anything'
        );
        $this->assertFalse(WebhookDelivery::where('webhook_id', $broken->id)->sole()->success);
        $this->assertTrue(WebhookDelivery::where('webhook_id', $healthy->id)->sole()->success);
    }

    public function test_a_retry_for_a_deleted_webhook_is_dropped(): void
    {
        $this->useDatabaseQueue();
        Http::fake(['*' => Http::response('down', 503)]);
        [$sale, $webhook] = $this->subscribedSale();

        $this->sendFirstAttempt($sale);
        $this->assertDatabaseCount('jobs', 1);

        $webhook->delete();
        $this->travel(31)->seconds();
        $this->work();

        Http::assertSentCount(1);
        $this->assertDatabaseCount('jobs', 0);
        $this->assertDatabaseCount('failed_jobs', 0);
    }

    public function test_a_retry_for_a_webhook_switched_off_meanwhile_is_skipped(): void
    {
        $this->useDatabaseQueue();
        Http::fake(['*' => Http::response('down', 503)]);
        [$sale, $webhook] = $this->subscribedSale();

        $this->sendFirstAttempt($sale);
        $webhook->update(['is_active' => false]);

        $this->travel(31)->seconds();
        $this->work();

        Http::assertSentCount(1);
        $this->assertSame(1, WebhookDelivery::count());
        $this->assertDatabaseCount('jobs', 0);
        $this->assertDatabaseCount('failed_jobs', 0);
    }

    /**
     * A job queued before $attempt existed has no such key in its payload. With constructor
     * promotion there would be no default to fall back on, and reading the typed property would
     * throw; the class-level default makes it attempt 1.
     */
    public function test_a_payload_queued_before_retries_existed_is_attempt_one(): void
    {
        [, $webhook] = $this->subscribedSale();

        $values = (new SendWebhook($webhook, [], 'sale.paid', 2))->__serialize();
        unset($values['attempt']);

        $job = (new \ReflectionClass(SendWebhook::class))->newInstanceWithoutConstructor();
        $job->__unserialize($values);

        $this->assertSame(1, $job->attempt);
    }
}
