<?php

namespace App\Jobs;

use App\Models\Webhook;
use App\Models\WebhookDelivery;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendWebhook implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    protected Webhook $webhook;

    protected array $payload;

    protected string $eventType;

    public function __construct(Webhook $webhook, array $payload, string $eventType)
    {
        $this->webhook = $webhook;
        $this->payload = $payload;
        $this->eventType = $eventType;
    }

    public function handle(): void
    {
        $jsonBody = json_encode($this->payload);
        $signature = hash_hmac('sha256', $jsonBody, $this->webhook->secret);
        $timestamp = now()->toIso8601String();

        $startTime = microtime(true);
        $responseStatus = null;
        $responseBody = null;
        $success = false;

        try {
            // SSRF prevention: block private IPs in hosted mode only
            if (config('app.hosted')) {
                $host = parse_url($this->webhook->url, PHP_URL_HOST);
                if ($host && $this->isPrivateHost($host)) {
                    $responseBody = 'Blocked: private/reserved IP address';
                    $this->logDelivery($responseStatus, $responseBody, false, $startTime);

                    return;
                }
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
                ->post($this->webhook->url);

            $responseStatus = $response->status();
            $responseBody = substr($response->body(), 0, 500);
            $success = $response->successful();
        } catch (\Exception $e) {
            $responseBody = substr($e->getMessage(), 0, 500);
        }

        $this->logDelivery($responseStatus, $responseBody, $success, $startTime);
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

    protected function isPrivateHost(string $host): bool
    {
        $ip = gethostbyname($host);
        if ($ip === $host && ! filter_var($host, FILTER_VALIDATE_IP)) {
            return true;
        }

        return ! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
    }
}
