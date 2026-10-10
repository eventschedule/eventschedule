<?php

namespace App\Services\Payments\Razorpay;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Razorpay's REST API, for the calls RazorpayGateway makes.
 *
 * Built from a RESOLVED credential set, never from config() directly, for the same reason as
 * PayPalClient: credentialsFor() is all-or-nothing, and a client reaching for config() itself could
 * pair one account's key id with another's secret.
 *
 * Read calls swallow transport failures and answer null, so a Razorpay outage never escapes into
 * the checkout path as a 500. refund() and listRefunds() throw instead: SaleRefundService needs to
 * tell a definite refusal from an unknown outcome.
 */
class RazorpayClient
{
    public const BASE_URL = 'https://api.razorpay.com/v1';

    /** @param  array<string, mixed>  $credentials */
    public function __construct(private array $credentials) {}

    /**
     * Test-mode keys are prefixed rzp_test_, live ones rzp_live_. Razorpay has one API host for both;
     * the key alone decides whether money moves.
     */
    public static function isTestKey(?string $keyId): bool
    {
        return str_starts_with((string) $keyId, 'rzp_test_');
    }

    /**
     * Create a Payment Link and return it, or null if Razorpay refused or was unreachable.
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>|null
     */
    public function createPaymentLink(array $payload): ?array
    {
        try {
            $response = $this->http()->post(self::BASE_URL.'/payment_links', $payload);
        } catch (\Throwable $e) {
            Log::warning('Razorpay payment link request failed', ['error' => $e->getMessage()]);

            return null;
        }

        if (! $response->successful()) {
            Log::warning('Razorpay refused the payment link', [
                'status' => $response->status(),
                'error' => $response->json('error.description'),
            ]);

            return null;
        }

        return (array) $response->json();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getPaymentLink(string $id): ?array
    {
        return $this->getJson('/payment_links/'.rawurlencode($id));
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getPayment(string $id): ?array
    {
        return $this->getJson('/payments/'.rawurlencode($id));
    }

    /**
     * Refunds already issued against a payment. Throws, like refund(), because a caller using this
     * to avoid a duplicate must not mistake "could not ask" for "there are none".
     *
     * @return list<array<string, mixed>>
     */
    public function listRefunds(string $paymentId): array
    {
        $response = $this->http()->get(self::BASE_URL.'/payments/'.rawurlencode($paymentId).'/refunds', ['count' => 100]);
        $response->throw();

        return array_values((array) $response->json('items', []));
    }

    /**
     * Refund $amountMinor paise (null = Razorpay's default, the full payment) and return the refund id.
     *
     * @param  array<string, string>  $notes
     */
    public function refund(string $paymentId, ?int $amountMinor, array $notes): string
    {
        $body = ['speed' => 'normal', 'notes' => $notes];

        if ($amountMinor !== null) {
            $body['amount'] = $amountMinor;
        }

        $response = $this->http()->post(self::BASE_URL.'/payments/'.rawurlencode($paymentId).'/refund', $body);
        $response->throw();

        $id = (string) $response->json('id');

        if ($id === '') {
            throw new \RuntimeException('Razorpay refund response carried no id.');
        }

        return $id;
    }

    /**
     * HMAC-SHA256 over the payment-link callback's four values, in Razorpay's documented order,
     * keyed with the API key secret.
     */
    public function verifyCallbackSignature(string $linkId, string $referenceId, string $status, string $paymentId, string $signature): bool
    {
        $secret = (string) ($this->credentials['razorpay_key_secret'] ?? '');

        if ($secret === '' || $signature === '') {
            return false;
        }

        $expected = hash_hmac('sha256', $linkId.'|'.$referenceId.'|'.$status.'|'.$paymentId, $secret);

        return hash_equals($expected, $signature);
    }

    /**
     * HMAC-SHA256 over the RAW webhook body, keyed with the webhook secret set in Razorpay's
     * dashboard - a different secret from the API key's.
     */
    public function verifyWebhookSignature(string $rawBody, string $signature): bool
    {
        $secret = (string) ($this->credentials['razorpay_webhook_secret'] ?? '');

        if ($secret === '' || $signature === '') {
            return false;
        }

        return hash_equals(hash_hmac('sha256', $rawBody, $secret), $signature);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function getJson(string $path): ?array
    {
        try {
            $response = $this->http()->get(self::BASE_URL.$path);
        } catch (\Throwable $e) {
            Log::warning('Razorpay request failed', ['path' => $path, 'error' => $e->getMessage()]);

            return null;
        }

        return $response->successful() ? (array) $response->json() : null;
    }

    private function http(): PendingRequest
    {
        return Http::withBasicAuth(
            (string) ($this->credentials['razorpay_key_id'] ?? ''),
            (string) ($this->credentials['razorpay_key_secret'] ?? ''),
        )->acceptJson()->asJson()->timeout(20)->connectTimeout(10);
    }
}
