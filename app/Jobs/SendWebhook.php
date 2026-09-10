<?php

namespace App\Jobs;

use App\Models\Webhook;
use App\Models\WebhookDelivery;
use App\Utils\UrlUtils;
use GuzzleHttp\Exception\TransferException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * One delivery attempt of one webhook. Every attempt writes its own WebhookDelivery row.
 *
 * Attempt 1 is dispatched by WebhookService with dispatchAfterResponse(), which ALWAYS runs the job
 * on the sync connection, inside Application::terminate(), whatever QUEUE_CONNECTION says. Two
 * rules follow from that:
 *
 * - The job's own $tries and $backoff could never apply: the sync queue attempts a job once. A
 *   retry is therefore a NEW job, dispatched to the default connection with a delay. Never
 *   re-dispatch $this or a clone of it: attempt 1 carries connection 'sync', and the sync queue
 *   ignores a delay, so that "retry" would run at once, inside the same terminate pass.
 * - handle() must never throw. terminate() runs its callbacks in a plain loop, so one exception
 *   skips every callback after it, and those include the next subscriber's webhook for the same
 *   request. Before this, one endpoint timing out silenced every endpoint queued behind it.
 *
 * Only a failure that can clear up by itself is retried: the endpoint could not be reached (a
 * timeout, a refused or reset connection) or it answered 5xx. A 3xx or 4xx is the endpoint's
 * considered answer, and an address the SSRF check refused will be refused again. The queue is
 * drained once a minute, so the two retries land about 30-90 and 60-120 seconds after the
 * attempt before them.
 *
 * An install whose default connection is sync has no queue to wait on, so it makes one attempt.
 */
class SendWebhook implements ShouldQueue
{
    use Queueable;

    public const MAX_ATTEMPTS = 3;

    // A missing Webhook means the owner deleted the integration between dispatch and delivery,
    // so there is no endpoint left to deliver to. Without this the payload cannot be
    // deserialized and the job fails permanently instead of being dropped.
    public $deleteWhenMissingModels = true;

    // One try per job: handle() never throws, and a retry is a new job (see the class docblock).
    public int $tries = 1;

    // A class default rather than constructor promotion, so a payload queued before retries
    // existed deserializes as attempt 1 instead of leaving a typed property uninitialized.
    public int $attempt = 1;

    protected Webhook $webhook;

    protected array $payload;

    protected string $eventType;

    public function __construct(Webhook $webhook, array $payload, string $eventType, int $attempt = 1)
    {
        $this->webhook = $webhook;
        $this->payload = $payload;
        $this->eventType = $eventType;
        $this->attempt = $attempt;
    }

    public function handle(): void
    {
        try {
            $this->deliver();
        } catch (\Throwable $e) {
            report($e);
        }
    }

    private function deliver(): void
    {
        // A retry reloads the webhook from the database, so this is the owner's setting now.
        // Attempt 1 needs no check: WebhookService only dispatches to active webhooks.
        if ($this->attempt > 1 && ! $this->webhook->is_active) {
            return;
        }

        $jsonBody = json_encode($this->payload);
        $signature = hash_hmac('sha256', $jsonBody, $this->webhook->secret);
        $timestamp = now()->toIso8601String();

        $startTime = microtime(true);

        try {
            // SSRF prevention: validate the target and pin DNS to the vetted IP so a
            // private/reserved/metadata address - or a redirect/DNS-rebind to one -
            // cannot be reached. Applied in all modes (hosted and selfhost).
            $curl = UrlUtils::safePinnedCurlOptions($this->webhook->url);
            if ($curl === null) {
                // Not retried: the same address would be refused again.
                $this->logDelivery(null, 'Blocked: private/reserved or unresolvable address', false, $startTime);

                return;
            }

            $response = Http::timeout(5)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'X-Webhook-Signature' => 'sha256='.$signature,
                    'X-Webhook-Event' => $this->eventType,
                    'X-Webhook-Timestamp' => $timestamp,
                    'User-Agent' => 'EventSchedule-Webhook/1.0',
                ])
                ->withBody($jsonBody, 'application/json')
                ->withOptions([
                    'allow_redirects' => false,
                    'curl' => $curl,
                ])
                ->post($this->webhook->url);
        } catch (ConnectionException|TransferException $e) {
            // The endpoint could not be reached, so a later attempt may get through. Laravel turns
            // Guzzle's ConnectException (timeouts included) into ConnectionException; a transfer
            // that failed without a response, such as a connection reset mid-reply, arrives as
            // Guzzle's own exception. The message names the endpoint's problem, so it is logged.
            $this->logDelivery(null, substr($e->getMessage(), 0, 500), false, $startTime);
            $this->retry();

            return;
        } catch (\Throwable $e) {
            // Not the endpoint's doing, so the owner's delivery log gets a generic line and the
            // exception goes to the error tracker.
            report($e);
            $this->logDelivery(null, 'Not sent: internal error', false, $startTime);

            return;
        }

        $responseStatus = $response->status();
        $this->logDelivery($responseStatus, substr($response->body(), 0, 500), $response->successful(), $startTime);

        if ($responseStatus >= 500) {
            $this->retry();
        }
    }

    /**
     * Queue the next attempt, if there is one to queue.
     *
     * Gated on the configured default and never on $this->connection: attempt 1 always reports
     * 'sync' (see the class docblock), and only a real queue can hold a job back for a delay.
     */
    private function retry(): void
    {
        if ($this->attempt >= self::MAX_ATTEMPTS || config('queue.default') === 'sync') {
            return;
        }

        try {
            self::dispatch($this->webhook, $this->payload, $this->eventType, $this->attempt + 1)
                ->delay(now()->addSeconds($this->attempt === 1 ? 30 : 60));
        } catch (\Throwable $e) {
            report($e);
        }
    }

    protected function logDelivery(?int $responseStatus, ?string $responseBody, bool $success, float $startTime): void
    {
        $durationMs = (int) round((microtime(true) - $startTime) * 1000);

        try {
            WebhookDelivery::create([
                'webhook_id' => $this->webhook->id,
                'event_type' => $this->eventType,
                'payload' => $this->payload,
                'response_status' => $responseStatus,
                'response_body' => $responseBody,
                'success' => $success,
                'duration_ms' => $durationMs,
                'created_at' => now(),
            ]);

            $this->webhook->update(['last_triggered_at' => now()]);
        } catch (\Exception $e) {
            Log::warning('Failed to log webhook delivery', [
                'webhook_id' => $this->webhook->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
