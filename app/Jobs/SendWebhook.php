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

    public int $tries = 3;

    public array $backoff = [30, 60];

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
            $resolvedIp = null;
            if (config('app.hosted')) {
                $host = parse_url($this->webhook->url, PHP_URL_HOST);
                if ($host) {
                    $resolvedIp = gethostbyname($host);
                    if (($resolvedIp === $host && ! filter_var($host, FILTER_VALIDATE_IP))
                        || ! filter_var($resolvedIp, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                        $responseBody = 'Blocked: private/reserved IP address';
                        $this->logDelivery($responseStatus, $responseBody, false, $startTime);

                        return;
                    }
                }
            }

            $httpClient = Http::timeout(5)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'X-Webhook-Signature' => 'sha256='.$signature,
                    'X-Webhook-Event' => $this->eventType,
                    'X-Webhook-Timestamp' => $timestamp,
                    'User-Agent' => 'EventSchedule-Webhook/1.0',
                ])
                ->withBody($jsonBody, 'application/json');

            // Pin to the resolved IP to prevent DNS rebinding attacks
            if ($resolvedIp) {
                $port = parse_url($this->webhook->url, PHP_URL_PORT)
                    ?? (parse_url($this->webhook->url, PHP_URL_SCHEME) === 'https' ? 443 : 80);
                $response = $httpClient
                    ->withOptions([
                        'curl' => [
                            CURLOPT_RESOLVE => ["{$host}:{$port}:{$resolvedIp}"],
                        ],
                    ])
                    ->post($this->webhook->url);
            } else {
                $response = $httpClient->post($this->webhook->url);
            }

            $responseStatus = $response->status();
            $responseBody = substr($response->body(), 0, 500);
            $success = $response->successful();
        } catch (\Exception $e) {
            $responseBody = substr($e->getMessage(), 0, 500);
            $this->logDelivery($responseStatus, $responseBody, false, $startTime);

            throw $e;
        }

        $this->logDelivery($responseStatus, $responseBody, $success, $startTime);

        // Retry on server errors (5xx)
        if ($responseStatus && $responseStatus >= 500) {
            throw new \RuntimeException("Webhook returned HTTP {$responseStatus}");
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
