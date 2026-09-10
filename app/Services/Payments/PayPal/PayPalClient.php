<?php

namespace App\Services\Payments\PayPal;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * PayPal's REST hosts and the calls this integration makes.
 *
 * Built from a RESOLVED credential set, never from config() directly: PaymentGatewayDriver's
 * credentialsFor() is all-or-nothing for a reason, and a client that reached for config() on its own
 * could pair an owner's id with the installation's secret.
 *
 * Every method swallows transport failures and answers null/false rather than throwing, so a PayPal
 * outage never escapes into the checkout path as a 500. The one exception is refundCapture(), whose
 * caller (SaleRefundService) needs to tell a definite refusal from an unknown outcome.
 */
class PayPalClient
{
    /** @param  array<string, mixed>  $credentials */
    public function __construct(private array $credentials) {}

    public function baseUrl(): string
    {
        return ! empty($this->credentials['paypal_sandbox'])
            ? 'https://api-m.sandbox.paypal.com'
            : 'https://api-m.paypal.com';
    }

    /**
     * An OAuth2 access token for this credential set.
     *
     * Cached because every other call needs one and PayPal's live for about nine hours. The key
     * includes the client id AND the host, so sandbox and live never share an entry and two owners
     * never share each other's token.
     *
     * A failure is never cached: storing it would keep checkout broken for the whole TTL after
     * PayPal had already recovered. Same call PayfastClient makes about a failed DNS resolution.
     */
    public function accessToken(): ?string
    {
        $clientId = (string) ($this->credentials['paypal_client_id'] ?? '');
        $secret = (string) ($this->credentials['paypal_client_secret'] ?? '');

        if ($clientId === '' || $secret === '') {
            return null;
        }

        $key = 'paypal:token:'.sha1($clientId.'|'.$this->baseUrl());

        if ($cached = Cache::get($key)) {
            return $cached;
        }

        $response = $this->mintToken($clientId, $secret);

        if (! $response || ! $response->successful()) {
            return null;
        }

        $token = (string) $response->json('access_token');

        if ($token === '') {
            return null;
        }

        // A minute of headroom, and never longer than eight hours even if PayPal claims more.
        $ttl = min(max((int) $response->json('expires_in') - 60, 60), 28800);

        Cache::put($key, $token, $ttl);

        return $token;
    }

    /**
     * The raw token call, for the connect-time credential check.
     *
     * Returns the response rather than a bool so the caller can tell "PayPal answered and rejected
     * these credentials" (a 401, which should fail the save) from "we could not reach PayPal" (null,
     * which should not). That is the same definite-versus-unknown split SaleRefundService draws, and
     * for the same reason: the two failures deserve opposite handling.
     */
    public function mintToken(string $clientId, string $secret): ?\Illuminate\Http\Client\Response
    {
        try {
            return Http::asForm()
                ->withBasicAuth($clientId, $secret)
                ->timeout(15)
                ->post($this->baseUrl().'/v1/oauth2/token', ['grant_type' => 'client_credentials']);
        } catch (\Throwable $e) {
            Log::warning('PayPal token request could not be completed: '.$e->getMessage());

            return null;
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>|null
     */
    public function createOrder(array $payload, string $requestId): ?array
    {
        $response = $this->send('post', '/v2/checkout/orders', $payload, $requestId);

        if (! $response || ! $response->successful()) {
            $this->logFailure('create order', $response);

            return null;
        }

        return $response->json();
    }

    /**
     * Capture an approved order. This is the call that moves the money.
     *
     * Returns [status, body] rather than a bool because 422 ORDER_ALREADY_CAPTURED is a SUCCESS the
     * caller has to recognise - the buyer refreshed the return URL, or the webhook won the race.
     * Treating it as a failure and releasing the sale would hand back seats, a promo redemption and
     * a gift-card balance for a payment PayPal actually took.
     *
     * @return array{0: int|null, 1: array<string, mixed>}
     */
    public function captureOrder(string $orderId, string $requestId): array
    {
        $response = $this->send('post', '/v2/checkout/orders/'.urlencode($orderId).'/capture', [], $requestId);

        if (! $response) {
            return [null, []];
        }

        $body = (array) $response->json();

        // 422 ORDER_ALREADY_CAPTURED is the normal result of a buyer refreshing the return URL, or
        // of the webhook winning the race. The caller treats it as a success, so logging it as a
        // failure would put a warning on the happy path.
        if (! $response->successful() && ! $this->mentionsIssue($body, 'ORDER_ALREADY_CAPTURED')) {
            $this->logFailure('capture order', $response);
        }

        return [$response->status(), $body];
    }

    /** @return array<string, mixed>|null */
    public function getOrder(string $orderId): ?array
    {
        $response = $this->send('get', '/v2/checkout/orders/'.urlencode($orderId));

        return $response && $response->successful() ? $response->json() : null;
    }

    /**
     * Read a capture back from PayPal.
     *
     * This is the webhook's real gate. A signature can be verified only when we hold a webhook id,
     * but nobody can make PayPal's own API report a capture that never happened - the same property
     * PayfastClient::confirmsPayment() exists for, and the reason the signature check here is
     * advisory rather than load-bearing.
     *
     * @return array<string, mixed>|null
     */
    public function getCapture(string $captureId): ?array
    {
        $response = $this->send('get', '/v2/payments/captures/'.urlencode($captureId));

        return $response && $response->successful() ? $response->json() : null;
    }

    /**
     * Refund a capture, in full when $amount is null.
     *
     * The one method here that THROWS rather than answering null. Everything else is on the checkout
     * path, where a PayPal outage must not become a 500; this is on the refund path, where
     * SaleRefundService needs to know whether PayPal answered and refused (release the claim) or
     * whether the request might have arrived (park it). Collapsing both into null would make every
     * failure look identical, and the conservative reading - park - permanently locks the sale out
     * of the refund path.
     *
     * @param  array<string, mixed>|null  $amount
     */
    public function refundCapture(string $captureId, ?array $amount, string $requestId): string
    {
        $token = $this->accessToken();

        if (! $token) {
            // Nothing was sent, and nothing can be. A LogicException is the one bucket that both
            // fails the claim and tells the owner it is a configuration fault rather than sending
            // them to a dashboard we never called.
            throw new \LogicException('PayPal credentials are not usable for a refund.');
        }

        $response = Http::withToken($token)
            ->timeout(15)
            ->acceptJson()
            ->withHeaders(['PayPal-Request-Id' => $requestId])
            ->post($this->baseUrl().'/v2/payments/captures/'.urlencode($captureId).'/refund',
                $amount ? ['amount' => $amount] : [])
            ->throw();

        $refundId = (string) $response->json('id');
        $status = strtoupper((string) $response->json('status'));

        // A 2xx from this endpoint means PayPal ACCEPTED the instruction, not that the money moved.
        // The refund object carries its own status - COMPLETED | PENDING | FAILED | CANCELLED - and
        // reading it is the same discipline the capture path already applies (PayPalGateway only
        // recognises a capture on status === 'COMPLETED'). Without this, a PENDING refund - a hold
        // on the funding source, or too little balance in the merchant account - was written to the
        // ledger as `succeeded` and the sale flipped to `refunded`: seats back, gift card credited,
        // the sale.refunded webhook fired and the owner told it worked. Nothing would ever correct
        // it, because WEBHOOK_EVENTS deliberately omits PAYMENT.CAPTURE.REFUNDED, so a refund that
        // later went FAILED left the buyer unpaid and our ledger saying otherwise.
        //
        // RuntimeException, deliberately NOT LogicException: the money may well be moving, and
        // LogicException is the one bucket SaleRefundService treats as "nothing left this machine"
        // and fails outright. This shape returns null from classifyRefundFailure(), falls to the
        // conservative \Throwable arm and PARKS the claim as awaiting_reconciliation - which holds
        // the amount against refundableRemaining() and puts the sale on /admin/revenue for a human.
        // The refund id travels in the message so it reaches last_error and can be chased at PayPal.
        if ($status !== '' && $status !== 'COMPLETED') {
            throw new \RuntimeException(
                'PayPal accepted the refund but reported status '.$status.' (refund '.$refundId.')'
            );
        }

        return $refundId;
    }

    /**
     * Ask PayPal whether it sent this webhook.
     *
     * @param  array<string, mixed>  $headers  the inbound request's headers, however cased
     */
    public function verifyWebhookSignature(array $headers, string $rawBody, string $webhookId): ?bool
    {
        $header = function (string $name) use ($headers): string {
            foreach ($headers as $key => $value) {
                if (strtolower($key) === $name) {
                    return is_array($value) ? (string) ($value[0] ?? '') : (string) $value;
                }
            }

            return '';
        };

        $payload = [
            'transmission_id' => $header('paypal-transmission-id'),
            'transmission_time' => $header('paypal-transmission-time'),
            'cert_url' => $header('paypal-cert-url'),
            'auth_algo' => $header('paypal-auth-algo'),
            'transmission_sig' => $header('paypal-transmission-sig'),
            'webhook_id' => $webhookId,
            // Decoded from the RAW body, never $request->all(): the signature covers the bytes
            // PayPal sent, and Laravel's input array has already been through parsing that can
            // reorder and retype them.
            'webhook_event' => json_decode($rawBody, true),
        ];

        $response = $this->send('post', '/v1/notifications/verify-webhook-signature', $payload);

        if (! $response || ! $response->successful()) {
            // Unknown, not "forged" - the caller must not reject on this.
            return null;
        }

        return $response->json('verification_status') === 'SUCCESS';
    }

    /**
     * Register a listener and return its id, or null if PayPal would not take it.
     *
     * @param  list<string>  $eventTypes
     */
    public function registerWebhook(string $url, array $eventTypes): ?string
    {
        $response = $this->send('post', '/v1/notifications/webhooks', [
            'url' => $url,
            'event_types' => array_map(fn ($name) => ['name' => $name], $eventTypes),
        ]);

        if (! $response) {
            return null;
        }

        if ($response->successful()) {
            return (string) $response->json('id') ?: null;
        }

        // A re-connect with the same URL: PayPal refuses the duplicate, so adopt the existing one
        // rather than leaving the owner with no listener at all.
        if ($this->issues($response)->contains('WEBHOOK_URL_ALREADY_EXISTS')) {
            return $this->findWebhookIdByUrl($url);
        }

        $this->logFailure('register webhook', $response);

        return null;
    }

    public function findWebhookIdByUrl(string $url): ?string
    {
        $response = $this->send('get', '/v1/notifications/webhooks');

        if (! $response || ! $response->successful()) {
            return null;
        }

        foreach ((array) $response->json('webhooks', []) as $webhook) {
            if (($webhook['url'] ?? null) === $url) {
                return (string) $webhook['id'];
            }
        }

        return null;
    }

    public function deleteWebhook(string $webhookId): bool
    {
        $response = $this->send('delete', '/v1/notifications/webhooks/'.urlencode($webhookId));

        return (bool) $response?->successful();
    }

    /**
     * The issue codes PayPal returned, if any. Used to tell one 4xx from another.
     *
     * @return \Illuminate\Support\Collection<int, string>
     */
    public function issues(?\Illuminate\Http\Client\Response $response): \Illuminate\Support\Collection
    {
        return collect((array) $response?->json('details', []))
            ->pluck('issue')
            ->filter()
            ->map(fn ($issue) => (string) $issue)
            ->values();
    }

    /** @param  array<string, mixed>  $body */
    public function mentionsIssue(array $body, string $issue): bool
    {
        foreach ((array) ($body['details'] ?? []) as $detail) {
            if (($detail['issue'] ?? null) === $issue) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function send(string $method, string $path, array $payload = [], ?string $requestId = null): ?\Illuminate\Http\Client\Response
    {
        $token = $this->accessToken();

        if (! $token) {
            return null;
        }

        try {
            $request = Http::withToken($token)->timeout(15)->acceptJson();

            if ($requestId !== null) {
                // PayPal's idempotency header. On the capture call this is what stops a refreshed
                // return URL becoming a second charge.
                $request = $request->withHeaders(['PayPal-Request-Id' => $requestId]);
            }

            return $method === 'get' || $method === 'delete'
                ? $request->{$method}($this->baseUrl().$path)
                : $request->{$method}($this->baseUrl().$path, $payload);
        } catch (\Throwable $e) {
            Log::warning('PayPal request could not be completed', [
                'path' => $path,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    private function logFailure(string $what, ?\Illuminate\Http\Client\Response $response): void
    {
        Log::warning('PayPal '.$what.' failed', [
            'status' => $response?->status(),
            'name' => $response?->json('name'),
            // Codes only. The body can carry buyer detail, and this line reaches a selfhost
            // installation's log as readily as ours.
            'issues' => $this->issues($response)->all(),
        ]);
    }
}
