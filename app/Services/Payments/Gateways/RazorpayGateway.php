<?php

namespace App\Services\Payments\Gateways;

use App\Models\Sale;
use App\Models\SaleInstallment;
use App\Models\User;
use App\Services\Payments\CheckoutContext;
use App\Services\Payments\CredentialField;
use App\Services\Payments\PaymentGatewayDriver;
use App\Services\Payments\Razorpay\RazorpayClient;
use App\Services\SaleSettlementService;
use App\Utils\UrlUtils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Razorpay, for INR events.
 *
 * Built on Payment Links rather than Standard Checkout: a Payment Link is a hosted page we redirect
 * to, so it fits the redirect-and-notify shape every other driver here has and needs no
 * checkout.razorpay.com script on our pages (the same call PayfastGateway makes about engine.js).
 *
 * Two ways a payment reaches us, either of which settles the sale:
 *
 *  - the buyer's return (callback_url), signed with the API key secret. Normal path.
 *  - the payment_link.paid webhook, signed with the webhook secret. Late path, for a buyer who
 *    paid and closed the tab. Optional: without a webhook secret configured it is ignored.
 *
 * Neither signature is trusted on its own. Both paths re-fetch the Payment Link from Razorpay's API
 * with our own key and settle from THAT answer, which nobody outside Razorpay can forge.
 */
class RazorpayGateway extends PaymentGatewayDriver
{
    /** Razorpay will not take less than ₹1.00. */
    private const MINIMUM_AMOUNT = 1.0;

    /**
     * How long the link stays payable. Razorpay requires at least 15 minutes.
     *
     * Deliberately SHORTER than the shortest unpaid-sale expiry ReleaseTickets can apply (whole
     * hours, minimum one), so a link can never be paid after its sale's seats were handed back -
     * which would be money taken with no ticket to give.
     */
    private const LINK_LIFETIME_MINUTES = 30;

    private const PAYMENT_ID_PATTERN = '/^pay_[A-Za-z0-9]{14}$/';

    public function __construct(private SaleSettlementService $settlement) {}

    public function key(): string
    {
        return 'razorpay';
    }

    public function label(?User $owner): string
    {
        $credentials = $this->credentialsFor($owner);

        // A forgotten test key sells tickets nobody paid for, so it is named wherever the owner picks
        // the gateway.
        return RazorpayClient::isTestKey($credentials['razorpay_key_id'] ?? null)
            ? 'Razorpay ('.__('messages.razorpay_test_mode').')'
            : 'Razorpay';
    }

    public function isConfiguredFor(?User $owner): bool
    {
        return $this->credentialsFor($owner) !== null;
    }

    /**
     * The installation's own account, from .env. Selfhost only, as for every gateway: hosted
     * settles into the event owner's own account, never the operator's.
     *
     * The webhook secret is optional - the return path settles on its own - so it does not gate
     * whether the set exists.
     *
     * @return array<string, mixed>
     */
    public function platformCredentials(): array
    {
        if (config('app.hosted')) {
            return [];
        }

        $keyId = (string) config('payments.razorpay.key_id');
        $keySecret = (string) config('payments.razorpay.key_secret');

        if ($keyId === '' || $keySecret === '') {
            return [];
        }

        return [
            'razorpay_key_id' => $keyId,
            'razorpay_key_secret' => $keySecret,
            'razorpay_webhook_secret' => (string) config('payments.razorpay.webhook_secret'),
        ];
    }

    /**
     * INR only. Razorpay can take international currencies, but only once an account is approved
     * for them, and each has its own minor-unit rules; offering them here would turn that into a
     * buyer-facing rejection on Razorpay's page.
     */
    public function supportsCurrency(string $currencyCode): bool
    {
        return strtoupper($currencyCode) === 'INR';
    }

    public function amountLimits(string $currencyCode): array
    {
        return [self::MINIMUM_AMOUNT, null];
    }

    /** One link for the whole order at the order total, as PayPal does with one purchase unit. */
    public function supportsCart(): bool
    {
        return true;
    }

    /**
     * The buyer is usually back before the webhook, but the return itself settles, so there is
     * nothing to wait for unless the return was lost - in which case the webhook is on its way.
     */
    public function awaitsConfirmation(Sale $sale): bool
    {
        return true;
    }

    // ----------------------------------------------------------------- refunds

    public function supportsRefunds(): bool
    {
        return true;
    }

    public function supportsPartialRefunds(): bool
    {
        return true;
    }

    /**
     * The pay_ id. transaction_reference also holds the translated manual_payment sentinel for a
     * sale marked paid by hand, which must get "Mark as refunded" rather than a refund that 404s.
     */
    public function refundReferenceFor(Sale $sale, ?SaleInstallment $leg = null): ?string
    {
        $reference = trim((string) $sale->transaction_reference);

        return preg_match(self::PAYMENT_ID_PATTERN, $reference) ? $reference : null;
    }

    /**
     * Razorpay has no idempotency header for refunds, so the claim's key rides in the refund's notes
     * and is looked for first: a retried request finds the refund the first attempt made instead of
     * sending the money twice.
     */
    public function refund(Sale $sale, ?float $amount, string $idempotencyKey, ?SaleInstallment $leg = null, ?string $currency = null): string
    {
        $paymentId = $this->refundReferenceFor($sale, $leg);

        if (! $paymentId) {
            throw new \LogicException('Razorpay payment reference is missing for sale '.$sale->id.'.');
        }

        $candidates = $this->candidateCredentials($sale->event?->user);

        if (! $candidates) {
            throw new \LogicException('Razorpay credentials are unavailable for sale '.$sale->id.'.');
        }

        $amountMinor = $amount === null ? null : (int) round($amount * 100);

        // Stepping to the next set only on 404 - this account does not hold the payment, a definite
        // "nothing happened". Anything else is rethrown for classifyRefundFailure() to judge.
        $lastNotFound = null;

        foreach ($candidates as $credentials) {
            $client = new RazorpayClient($credentials);

            try {
                foreach ($client->listRefunds($paymentId) as $existing) {
                    if (($existing['notes']['idempotency_key'] ?? null) === $idempotencyKey) {
                        return (string) $existing['id'];
                    }
                }

                return $client->refund($paymentId, $amountMinor, ['idempotency_key' => $idempotencyKey]);
            } catch (\Illuminate\Http\Client\RequestException $e) {
                if ($e->response->status() !== 404) {
                    throw $e;
                }

                $lastNotFound = $e;
            }
        }

        throw new \LogicException(
            'No Razorpay account available to this install holds payment '.$paymentId.' for sale '.$sale->id.'.',
            0,
            $lastNotFound,
        );
    }

    /**
     * Name the figure rather than omit it. Razorpay documents an omitted amount as "the full
     * payment", and after a partial refund that is not what is left.
     */
    public function omittedRefundAmountMeansRemainder(): bool
    {
        return false;
    }

    public function classifyRefundFailure(\Throwable $e): ?string
    {
        if ($e instanceof \Illuminate\Http\Client\ConnectionException) {
            return 'park';
        }

        if ($e instanceof \Illuminate\Http\Client\RequestException) {
            return $e->response->status() >= 500 ? 'park' : 'fail';
        }

        return null;
    }

    public function referenceUrl(Sale $sale): ?string
    {
        $reference = trim((string) $sale->transaction_reference);

        return preg_match(self::PAYMENT_ID_PATTERN, $reference)
            ? 'https://dashboard.razorpay.com/app/payments/'.$reference
            : null;
    }

    // ----------------------------------------------------------- settings form

    /**
     * @return list<CredentialField>
     */
    public function credentialFields(): array
    {
        return [
            new CredentialField(
                name: 'razorpay_key_id',
                label: 'messages.razorpay_key_id',
                help: 'messages.razorpay_key_id_help',
                required: true,
            ),
            new CredentialField(
                name: 'razorpay_key_secret',
                label: 'messages.razorpay_key_secret',
                type: 'password',
                required: true,
            ),
            new CredentialField(
                name: 'razorpay_webhook_secret',
                label: 'messages.razorpay_webhook_secret',
                type: 'password',
                help: 'messages.razorpay_webhook_secret_help',
            ),
        ];
    }

    public function credentialRules(): array
    {
        return [
            // Constrained to Razorpay's real shape: the key id is echoed into the event dropdown's
            // option text inside a Vue mount, the same sink PayfastGateway guards its merchant id for.
            'razorpay_key_id' => ['required', 'string', 'max:64', 'regex:/^rzp_(test|live)_[A-Za-z0-9]+$/'],
            'razorpay_key_secret' => [$this->requiredUnlessStored('razorpay_key_secret'), 'string', 'max:128'],
            'razorpay_webhook_secret' => ['nullable', 'string', 'max:128'],
        ];
    }

    public function credentialHelp(): ?string
    {
        return __('messages.razorpay_help', ['url' => app_url(route('payments.webhook', ['gateway' => $this->key()], false))]);
    }

    // ---------------------------------------------------------------- checkout

    public function startCheckout(CheckoutContext $context): Response
    {
        $sale = $context->sale;
        $event = $context->event;
        $credentials = $this->credentialsFor($context->owner());

        if (! $credentials) {
            // An owner disconnected Razorpay while an event still names it. Give the seats back
            // first - see releaseAbandonedSale() for why landing the buyer alone would hold them.
            Log::warning('Razorpay checkout attempted with no credentials', ['sale_id' => $sale->id]);
            $this->releaseAbandonedSale($sale, 'razorpay_unconfigured');

            return $this->redirectToPurchaseLanding($sale, $event, $context->isEmbed);
        }

        // Re-checked here, not just in the dropdown: the stored method outlives a currency edit and
        // can be set through the API.
        [$minimum] = $this->amountLimits($context->currency());
        $amountMinor = (int) round($context->total() * 100);

        if (! $this->supportsCurrency($context->currency()) || $amountMinor < $minimum * 100) {
            Log::warning('Razorpay checkout refused: unsupported currency or below minimum', [
                'sale_id' => $sale->id,
                'currency' => $context->currency(),
                'total' => $context->total(),
            ]);
            $this->releaseAbandonedSale($sale, 'razorpay_refused');

            return back()->withInput()->with('error', __('messages.razorpay_checkout_unavailable'));
        }

        $encodedSaleId = UrlUtils::encodeId($sale->id);

        $callbackParams = [
            'gateway' => $this->key(),
            'sale_id' => $encodedSaleId,
            // The id alone is a Sqid and proves nothing; PaymentGatewayController::resolve() refuses
            // the return without the secret.
            'secret' => $sale->secret,
        ];

        if ($context->isEmbed) {
            $callbackParams['embed'] = 'true';
        }

        $link = (new RazorpayClient($credentials))->createPaymentLink([
            'amount' => $amountMinor,
            'currency' => 'INR',
            'accept_partial' => false,
            // Razorpay refuses a reused reference_id, and a sale can be sent to pay more than once
            // (the buyer backs out and resumes), so the sale id gets a random suffix. Both settlement
            // paths check the prefix and notes.sale_id against the sale.
            'reference_id' => $encodedSaleId.'-'.Str::lower(Str::random(8)),
            'description' => $this->clean($event->name ?: __('messages.tickets'), 255),
            'customer' => array_filter([
                'name' => $this->clean($sale->name, 100),
                'email' => $sale->email,
            ]),
            // We send the buyer there ourselves; Razorpay texting or emailing them the link as well
            // would just be noise.
            'notify' => ['sms' => false, 'email' => false],
            'reminder_enable' => false,
            'expire_by' => now()->addMinutes(self::LINK_LIFETIME_MINUTES)->getTimestamp(),
            'notes' => ['sale_id' => $encodedSaleId],
            'callback_url' => custom_domain_url(route('payments.return', $callbackParams)),
            'callback_method' => 'get',
        ]);

        $url = (string) ($link['short_url'] ?? '');

        if ($url === '') {
            Log::warning('Razorpay payment link could not be created', ['sale_id' => $sale->id]);
            $this->releaseAbandonedSale($sale, 'razorpay_link_failed');

            return back()->withInput()->with('error', __('messages.razorpay_checkout_unavailable'));
        }

        return redirect($url);
    }

    // -------------------------------------------------------------- settlement

    /**
     * The buyer is back. Verify the callback signature, then settle from Razorpay's own copy of the
     * link - never from the query string, which is the buyer's to edit.
     */
    public function handleReturn(Request $request, Sale $sale): Response
    {
        $landing = fn () => $this->redirectToPurchaseLanding($sale, $sale->event, $request->boolean('embed'));

        if ($sale->status !== 'unpaid') {
            return $landing();
        }

        $linkId = (string) $request->query('razorpay_payment_link_id', '');
        $referenceId = (string) $request->query('razorpay_payment_link_reference_id', '');
        $status = (string) $request->query('razorpay_payment_link_status', '');
        $paymentId = (string) $request->query('razorpay_payment_id', '');
        $signature = (string) $request->query('razorpay_signature', '');

        if ($linkId === '' || $status !== 'paid') {
            Log::info('Razorpay return without a paid link', ['sale_id' => $sale->id, 'status' => $status]);

            return $landing();
        }

        foreach ($this->candidateCredentials($sale->event?->user) as $credentials) {
            $client = new RazorpayClient($credentials);

            if (! $client->verifyCallbackSignature($linkId, $referenceId, $status, $paymentId, $signature)) {
                continue;
            }

            $link = $client->getPaymentLink($linkId);

            if (! $link) {
                // Signed by this account but Razorpay is unreachable. The webhook will settle it.
                Log::warning('Razorpay return verified but the link could not be fetched', ['sale_id' => $sale->id]);

                return $landing();
            }

            $this->settleLink($sale, $link, $paymentId);

            return $landing();
        }

        Log::warning('Razorpay return signature did not verify', ['sale_id' => $sale->id]);

        return $landing();
    }

    /**
     * Razorpay's account-level webhook (Settings > Webhooks in the dashboard), subscribed to
     * payment_link.paid. Arrives with no sale in the URL; the sale is found from the link's notes.
     */
    public function handleWebhook(Request $request, ?Sale $sale): Response
    {
        $payload = (array) $request->json()->all();

        // Not something we act on. 2xx rather than an error: Razorpay disables a webhook that keeps
        // failing, and the account may be subscribed to more than this app needs.
        if (($payload['event'] ?? null) !== 'payment_link.paid') {
            return response()->noContent();
        }

        $sale ??= $this->saleFromWebhook($payload);

        // Not a link this app made (the account may take payments elsewhere), or not a Razorpay sale.
        if (! $sale || $sale->payment_method !== $this->key()) {
            return response()->noContent();
        }

        $signature = (string) $request->header('X-Razorpay-Signature', '');
        $rawBody = $request->getContent();
        $linkId = (string) ($payload['payload']['payment_link']['entity']['id'] ?? '');
        $paymentId = (string) ($payload['payload']['payment']['entity']['id'] ?? '');
        $anySecret = false;

        foreach ($this->candidateCredentials($sale->event?->user) as $credentials) {
            if (($credentials['razorpay_webhook_secret'] ?? '') === '') {
                continue;
            }

            $anySecret = true;
            $client = new RazorpayClient($credentials);

            if (! $client->verifyWebhookSignature($rawBody, $signature)) {
                continue;
            }

            $link = $linkId !== '' ? $client->getPaymentLink($linkId) : null;

            if (! $link) {
                // Genuine, but Razorpay's API did not answer. A non-2xx earns a retry.
                Log::warning('Razorpay webhook verified but the link could not be fetched', ['sale_id' => $sale->id]);

                return response('unconfirmed', 503);
            }

            $this->settleLink($sale, $link, $paymentId);

            return response()->noContent();
        }

        if (! $anySecret) {
            // Webhooks are optional; with no secret there is nothing to authenticate one with.
            Log::info('Razorpay webhook received but no webhook secret is configured - ignored', ['sale_id' => $sale->id]);

            return response()->noContent();
        }

        Log::warning('Razorpay webhook signature did not verify', ['sale_id' => $sale->id]);

        return response('invalid signature', 400);
    }

    public function resolveOwnerFromWebhook(Request $request, ?Sale $sale = null): ?User
    {
        return ($sale ?? $this->saleFromWebhook((array) $request->json()->all()))?->event?->user;
    }

    // ----------------------------------------------------------------- helpers

    /**
     * Cross-check Razorpay's copy of the link against the sale, then settle.
     *
     * @param  array<string, mixed>  $link  as fetched from the API, not as received
     */
    private function settleLink(Sale $sale, array $link, string $paymentIdHint): void
    {
        $encodedSaleId = UrlUtils::encodeId($sale->id);

        // A valid link from the same account, but for a different sale, must not pay this one.
        if (! hash_equals($encodedSaleId, (string) ($link['notes']['sale_id'] ?? ''))
            || ! str_starts_with((string) ($link['reference_id'] ?? ''), $encodedSaleId.'-')) {
            Log::warning('Razorpay link named a different sale', ['sale_id' => $sale->id]);

            return;
        }

        if (strtoupper((string) ($link['currency'] ?? '')) !== 'INR'
            || strtoupper((string) ($sale->event?->ticket_currency_code ?: '')) !== 'INR') {
            Log::warning('Razorpay link currency did not match the event', ['sale_id' => $sale->id]);

            return;
        }

        if (($link['status'] ?? null) !== 'paid') {
            Log::info('Razorpay link is not paid', ['sale_id' => $sale->id, 'status' => $link['status'] ?? null]);

            return;
        }

        $outcome = $this->settlement->settle(
            $sale,
            $this->capturedPaymentId($link, $paymentIdHint),
            isset($link['amount_paid']) ? ((int) $link['amount_paid']) / 100 : null,
            $this->key(),
        );

        $this->recordOutcome($sale, $outcome);
    }

    /**
     * The captured payment on the link: the one the callback named if the link lists it, otherwise
     * the first captured one. Never the hint alone, which came from the request.
     *
     * @param  array<string, mixed>  $link
     */
    private function capturedPaymentId(array $link, string $hint): ?string
    {
        $captured = array_values(array_filter(
            (array) ($link['payments'] ?? []),
            fn ($p) => is_array($p) && ($p['status'] ?? null) === 'captured' && ! empty($p['payment_id']),
        ));

        foreach ($captured as $payment) {
            if ($payment['payment_id'] === $hint) {
                return $hint;
            }
        }

        return isset($captured[0]) ? (string) $captured[0]['payment_id'] : null;
    }

    /** @param  array<string, mixed>  $payload */
    private function saleFromWebhook(array $payload): ?Sale
    {
        $encoded = (string) ($payload['payload']['payment_link']['entity']['notes']['sale_id'] ?? '');
        $id = $encoded !== '' ? UrlUtils::decodeId($encoded) : null;

        return $id ? Sale::with('event.user')->find($id) : null;
    }

    /**
     * Exhaustive on purpose, as in PayPalGateway: some outcomes mean Razorpay HAS the buyer's money
     * and this install cannot honour it, which a person has to act on.
     */
    private function recordOutcome(Sale $sale, string $outcome): void
    {
        match ($outcome) {
            'released', 'deleted', 'missing' => (function () use ($sale, $outcome) {
                Log::error('Razorpay payment received for a sale that can no longer be honoured', [
                    'sale_id' => $sale->id,
                    'outcome' => $outcome,
                ]);

                report(new \RuntimeException(
                    'Razorpay payment received for sale '.$sale->id." that can no longer be honoured (outcome: {$outcome})"
                ));
            })(),

            'amount_mismatch' => Log::warning('Razorpay amount mismatch - sale parked for review', [
                'sale_id' => $sale->id,
            ]),

            'paid', 'already_paid' => Log::info('Razorpay payment settled', [
                'sale_id' => $sale->id,
                'outcome' => $outcome,
            ]),

            default => (function () use ($sale, $outcome) {
                Log::error('Razorpay produced an unhandled settlement outcome', [
                    'sale_id' => $sale->id,
                    'outcome' => $outcome,
                ]);

                report(new \RuntimeException(
                    'Razorpay produced an unhandled settlement outcome for sale '.$sale->id." (outcome: {$outcome})"
                ));
            })(),
        };
    }

    private function clean(?string $value, int $max): string
    {
        return mb_substr(trim(preg_replace('/\s+/u', ' ', (string) $value) ?? ''), 0, $max);
    }
}
