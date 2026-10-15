<?php

namespace App\Services\Payments\Gateways;

use App\Models\Sale;
use App\Models\SaleInstallment;
use App\Models\User;
use App\Services\Payments\CheckoutContext;
use App\Services\Payments\CredentialField;
use App\Services\Payments\PaymentGatewayDriver;
use App\Services\Payments\Razorpay\RazorpayClient;
use App\Services\Payments\Razorpay\RazorpayRefundNotSent;
use App\Services\SaleSettlementService;
use App\Utils\MoneyUtils;
use App\Utils\UrlUtils;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
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
 *
 * A Payment Link has no cancel URL, so a buyer who walks away is never sent back to us. The
 * payment_link.expired webhook stands in for PayPal's and Payfast's cancel return: it gives a ticket
 * sale's seats back once its link can no longer be paid.
 */
class RazorpayGateway extends PaymentGatewayDriver
{
    /** Razorpay will not take less than ₹1.00. */
    private const MINIMUM_AMOUNT = 1.0;

    /**
     * How long a link stays payable, at most. linkExpiresAt() shortens it to fit inside the sale's
     * hold: a booking's pay step reuses the booking's sale, whose hold started when it was booked.
     */
    private const LINK_LIFETIME_MINUTES = 30;

    /**
     * Razorpay refuses an expire_by less than 15 minutes ahead; the extra minute covers the time
     * between our clock and theirs and the request in between.
     */
    private const SHORTEST_LINK_MINUTES = 16;

    private const PAYMENT_ID_PATTERN = '/^pay_[A-Za-z0-9]{14}$/';

    /** The webhook events this driver acts on. Anything else is acknowledged and ignored. */
    private const WEBHOOK_EVENTS = ['payment_link.paid', 'payment_link.expired', 'payment_link.cancelled'];

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
     * Razorpay sends the buyer to callback_url only once a payment has gone through, so a buyer who
     * is back and still unpaid has paid: the return could not settle it (Razorpay's API did not
     * answer), and the webhook, if there is one, is on its way. Either way they must not be asked to
     * pay a second time, which is what true buys: "Confirming your payment" on a ticket, and no
     * Complete payment on a booking that has landed (AppointmentController::bookingState()).
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

        // The list of refunds is a GET, so asking it of an account that does not hold the payment
        // costs nothing. Razorpay answers that with a 400 ("The id provided does not exist"), not a
        // 404, so any refusal there moves on to the next set. Only the POST can move money.
        $lastRefusal = null;

        foreach ($candidates as $credentials) {
            $client = new RazorpayClient($credentials);

            try {
                $existing = $client->listRefunds($paymentId);
            } catch (RequestException $e) {
                if ($e->response->clientError() && $e->response->status() !== 429) {
                    $lastRefusal = $e;

                    continue;
                }

                throw new RazorpayRefundNotSent('Razorpay could not list the refunds of payment '.$paymentId.', so none was sent.', 0, $e);
            } catch (ConnectionException $e) {
                throw new RazorpayRefundNotSent('Razorpay could not be reached to list the refunds of payment '.$paymentId.', so none was sent.', 0, $e);
            }

            foreach ($existing as $refund) {
                if (($refund['notes']['idempotency_key'] ?? null) === $idempotencyKey) {
                    return (string) $refund['id'];
                }
            }

            return $client->refund($paymentId, $amountMinor, ['idempotency_key' => $idempotencyKey]);
        }

        throw new \LogicException(
            'No Razorpay account available to this install holds payment '.$paymentId.' for sale '.$sale->id.'.',
            0,
            $lastRefusal,
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
        // Only the GET failed, so no refund request was ever made.
        if ($e instanceof RazorpayRefundNotSent) {
            return 'fail';
        }

        if ($e instanceof ConnectionException) {
            return 'park';
        }

        if ($e instanceof RequestException) {
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
                // The owner adds this address in Razorpay's dashboard, so it sits right above the
                // field the webhook's secret goes in.
                copy: app_url(route('payments.webhook', ['gateway' => $this->key()], false)),
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
        return __('messages.razorpay_help');
    }

    /**
     * Check the keys with Razorpay before storing them, as PayPalGateway does, so a mistyped secret
     * is refused here rather than by a buyer.
     *
     * Razorpay ANSWERED and refused: refuse the save. Razorpay could not be reached, or was busy:
     * store them anyway and say they were not checked - refusing good keys because a third party is
     * down is the worse error.
     */
    public function saveCredentials(User $owner, array $input): void
    {
        $keyId = (string) ($input['razorpay_key_id'] ?? $owner->razorpay_key_id);
        $secret = (string) ($input['razorpay_key_secret'] ?? '') ?: (string) $owner->razorpay_key_secret;

        if ($keyId !== '' && $secret !== '') {
            $status = (new RazorpayClient([
                'razorpay_key_id' => $keyId,
                'razorpay_key_secret' => $secret,
            ]))->verifyKeys();

            if ($status !== null && $status >= 400 && $status < 500 && $status !== 429) {
                throw ValidationException::withMessages([
                    'razorpay_key_id' => __('messages.razorpay_credentials_rejected'),
                ]);
            }

            if ($status === null || $status >= 500 || $status === 429) {
                session()->flash('warning', __('messages.razorpay_credentials_unverified'));
            }
        }

        // Razorpay keeps Test Mode and Live Mode apart, webhooks included, so a secret saved beside
        // test keys belongs to a webhook live payments never reach. Kept, it would read as "webhook
        // set up" while every live webhook failed its signature. Cleared unless a new one came with
        // the switch, so the row says "No webhook" until the live one is added.
        $previousKeyId = (string) $owner->getRawOriginal('razorpay_key_id');
        $newWebhookSecret = trim((string) ($input['razorpay_webhook_secret'] ?? '')) !== '';

        if ($previousKeyId !== '' && $keyId !== ''
            && RazorpayClient::isTestKey($previousKeyId) !== RazorpayClient::isTestKey($keyId)
            && ! $newWebhookSecret) {
            // parent::saveCredentials() skips a blank secret, so this null is what its save stores.
            $owner->razorpay_webhook_secret = null;
        }

        parent::saveCredentials($owner, $input);
    }

    /**
     * What the Payment Methods row adds after "Connected": test keys, and a missing webhook, which
     * costs the late payments and the seats of checkouts nobody finished. Only for the owner's own
     * keys; an installation's account is the operator's to set up.
     *
     * @return list<string>
     */
    public function settingsStatusNotes(?User $owner): array
    {
        if (! $owner || ! $this->hasOwnCredentials($owner)) {
            return [];
        }

        $notes = [];

        if (RazorpayClient::isTestKey($owner->getRawOriginal('razorpay_key_id'))) {
            $notes[] = __('messages.razorpay_test_mode');
        }

        if (blank($owner->getRawOriginal('razorpay_webhook_secret'))) {
            $notes[] = __('messages.razorpay_no_webhook');
        }

        return $notes;
    }

    // ---------------------------------------------------------------- checkout

    public function startCheckout(CheckoutContext $context): Response
    {
        $sale = $context->sale;
        $credentials = $this->credentialsFor($context->owner());

        // Every refusal goes through refuseCheckout(): a ticket sale gives its seats back, and a
        // booking keeps its hold and goes back to its own page, which offers to pay again.
        if (! $credentials) {
            // An owner disconnected Razorpay while an event still names it.
            Log::warning('Razorpay checkout attempted with no credentials', ['sale_id' => $sale->id]);

            return $this->refuseCheckout($sale, 'razorpay_unconfigured', __('messages.razorpay_checkout_unavailable'));
        }

        // Re-checked here, not just in the dropdown: the stored method outlives a currency edit and
        // can be set through the API.
        if (! $this->supportsCurrency($context->currency())) {
            Log::warning('Razorpay checkout refused: unsupported currency', [
                'sale_id' => $sale->id,
                'currency' => $context->currency(),
            ]);

            return $this->refuseCheckout($sale, 'razorpay_refused', __('messages.razorpay_checkout_unavailable'));
        }

        $amountMinor = (int) round($context->total() * 100);

        if ($amountMinor < (int) round(self::MINIMUM_AMOUNT * 100)) {
            Log::warning('Razorpay checkout refused: below the minimum', [
                'sale_id' => $sale->id,
                'total' => $context->total(),
            ]);

            return $this->refuseCheckout($sale, 'razorpay_refused', __('messages.payment_method_amount_below_minimum', [
                'method' => 'Razorpay',
                'amount' => MoneyUtils::format(self::MINIMUM_AMOUNT, 'INR'),
            ]));
        }

        $client = new RazorpayClient($credentials);

        // A link already made for this sale and still open is where the buyer goes. A booking's pay
        // step can be pressed again (the guest backs out and comes back), and a second link would
        // leave two that can each be paid - and, late in the hold, one still open after the booking
        // was released for want of time.
        if ($open = $this->openLinkFor($sale, $client, $amountMinor)) {
            return redirect($open);
        }

        $expireBy = $this->linkExpiresAt($sale);

        if ($expireBy === null) {
            return $this->refuseTooLate($sale);
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

        $link = $client->createPaymentLink([
            'amount' => $amountMinor,
            'currency' => 'INR',
            'accept_partial' => false,
            // Razorpay refuses a reused reference_id, and a booking can be sent to pay more than once
            // (the guest backs out and comes back), so the sale id gets a random suffix. Every
            // settlement path checks the prefix and notes.sale_id against the sale.
            'reference_id' => $encodedSaleId.'-'.Str::lower(Str::random(8)),
            'description' => $this->clean($context->event->name ?: __('messages.tickets'), 255),
            'customer' => array_filter([
                'name' => $this->clean($sale->name, 100),
                'email' => $sale->email,
            ]),
            // We send the buyer there ourselves; Razorpay texting or emailing them the link as well
            // would just be noise.
            'notify' => ['sms' => false, 'email' => false],
            'reminder_enable' => false,
            'expire_by' => $expireBy,
            'notes' => ['sale_id' => $encodedSaleId],
            'callback_url' => custom_domain_url(route('payments.return', $callbackParams)),
            'callback_method' => 'get',
        ]);

        $url = (string) ($link['short_url'] ?? '');

        if ($url === '') {
            // Usually a passing failure (Razorpay's API timing out), so the buyer is asked to try
            // again rather than to contact the organizer.
            Log::warning('Razorpay payment link could not be created', ['sale_id' => $sale->id]);

            return $this->refuseCheckout($sale, 'razorpay_link_failed', __('messages.razorpay_link_failed'));
        }

        $this->rememberLink($sale, (string) ($link['id'] ?? ''), $amountMinor, $expireBy);

        return redirect($url);
    }

    private function linkCacheKey(Sale $sale): string
    {
        return 'razorpay:link:'.$sale->id;
    }

    /**
     * Remember the link made for this sale until it expires, so a second press of the pay step goes
     * back to it (openLinkFor()). Only the id and the amount: the link itself is re-read from
     * Razorpay, which is the only answer to whether it can still be paid.
     */
    private function rememberLink(Sale $sale, string $linkId, int $amountMinor, int $expireBy): void
    {
        if ($linkId === '') {
            return;
        }

        try {
            Cache::put($this->linkCacheKey($sale), ['id' => $linkId, 'amount' => $amountMinor], Carbon::createFromTimestamp($expireBy));
        } catch (\Throwable $e) {
            // Without it the next press makes a new link, which is how it worked before.
            report($e);
        }
    }

    /**
     * The short URL of this sale's link while Razorpay still says it can be paid ("created", not
     * yet past its expiry) for the amount now owed, or null. Asked before any new link is made, and
     * so before a booking is released for having too little time left: with a link still open, the
     * guest is sent to it, and nothing is released that the guest could still pay.
     */
    private function openLinkFor(Sale $sale, RazorpayClient $client, int $amountMinor): ?string
    {
        try {
            $remembered = Cache::get($this->linkCacheKey($sale));
        } catch (\Throwable $e) {
            report($e);

            return null;
        }

        if (! is_array($remembered) || ($remembered['amount'] ?? null) !== $amountMinor || empty($remembered['id'])) {
            return null;
        }

        $link = $client->getPaymentLink((string) $remembered['id']);

        if (! $link || ($link['status'] ?? null) !== 'created' || ! $this->linkBelongsTo($sale, $link)
            || (int) ($link['expire_by'] ?? 0) <= Carbon::now()->getTimestamp()) {
            return null;
        }

        $url = (string) ($link['short_url'] ?? '');

        return $url !== '' ? $url : null;
    }

    /**
     * When the link must stop being payable, or null when there is not enough of the sale's hold left
     * for Razorpay to accept one.
     *
     * ReleaseTickets frees an unpaid sale once events.expire_unpaid_tickets hours have passed since
     * it was made, and an order at its shortest leg. A payment that lands after that cannot bring it
     * back - the seats or the slot may already be someone else's - so the link expires a minute
     * before. A ticket checkout always makes a fresh sale, so this only bites a booking, whose pay
     * step reuses the sale made when it was booked.
     */
    private function linkExpiresAt(Sale $sale): ?int
    {
        $now = Carbon::now();
        $expires = $now->copy()->addMinutes(self::LINK_LIFETIME_MINUTES);

        foreach ($sale->orderLegs() as $leg) {
            $hours = (int) ($leg->event?->expire_unpaid_tickets ?? 0);

            if ($hours <= 0 || ! $leg->created_at) {
                continue;
            }

            $deadline = $leg->created_at->copy()->addHours($hours)->subMinute();

            if ($deadline->lt($expires)) {
                $expires = $deadline;
            }
        }

        return $expires->lt($now->copy()->addMinutes(self::SHORTEST_LINK_MINUTES))
            ? null
            : $expires->getTimestamp();
    }

    /**
     * Too little of the hold is left for a link Razorpay will accept.
     *
     * Unlike every other refusal, this one never passes: the booking can only get closer to its
     * release. Keeping the hold, as refuseCheckout() does, would leave Complete payment on the
     * booking's page and refuse it on every press until the hour ran out. So the booking is released
     * now, and its page says so and offers to book again. startCheckout() has already sent the guest
     * to any link of theirs that is still open, so nothing released here can still be paid.
     */
    private function refuseTooLate(Sale $sale): Response
    {
        Log::info('Razorpay checkout refused: too little of the hold left', ['sale_id' => $sale->id]);

        if (! $sale->event?->appointment_type_id) {
            return $this->refuseCheckout($sale, 'razorpay_too_late', __('messages.razorpay_link_failed'));
        }

        $this->releaseAbandonedSale($sale, 'razorpay_too_late');

        return redirect($this->appointmentManageUrl($sale))->with('error', __('messages.razorpay_too_late_to_pay'));
    }

    // -------------------------------------------------------------- settlement

    /**
     * The buyer is back. Verify the callback signature, then settle from Razorpay's own copy of the
     * link - never from the query string, which is the buyer's to edit.
     *
     * Nothing returns early on the sale's status. settleLink() knows the payment that already
     * settled a sale from a different one, and settle() a released sale from a parked one, so money
     * that reached a sale whose seats have gone back, or a second payment for a sale already paid, is
     * reported from here too - not only from the webhook an owner may not have set up.
     */
    public function handleReturn(Request $request, Sale $sale): Response
    {
        $landing = fn () => $this->redirectToPurchaseLanding($sale, $sale->event, $request->boolean('embed'));

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

            // Once more before giving up: Razorpay sends the buyer here only after a payment, so a
            // link that cannot be read is money taken and not yet recorded.
            $link = $client->getPaymentLink($linkId) ?? $client->getPaymentLink($linkId);

            if (! $link) {
                Log::warning('Razorpay return verified but the link could not be fetched', ['sale_id' => $sale->id]);

                // With a webhook the payment is settled when its notice arrives. Without one nothing
                // else will ever settle it, so a person has to.
                if (($credentials['razorpay_webhook_secret'] ?? '') === '') {
                    report(new \RuntimeException(
                        'Razorpay took a payment for sale '.$sale->id.' that could not be confirmed, and no webhook is set up to confirm it later'
                    ));
                }

                return $landing();
            }

            $this->settleLink($sale, $link, $paymentId);

            return $landing();
        }

        Log::warning('Razorpay return signature did not verify', ['sale_id' => $sale->id]);

        return $landing();
    }

    /**
     * Razorpay's account-level webhook (Account & Settings > Webhooks in the dashboard), subscribed
     * to payment_link.paid and payment_link.expired. Arrives with no sale in the URL; the sale is
     * found from the link's notes.
     */
    public function handleWebhook(Request $request, ?Sale $sale): Response
    {
        $payload = (array) $request->json()->all();

        // Not something we act on. 2xx rather than an error: Razorpay disables a webhook that keeps
        // failing, and the account may be subscribed to more than this app needs.
        if (! in_array($payload['event'] ?? null, self::WEBHOOK_EVENTS, true)) {
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

            // What the link IS decides, not which event announced it: an expired notice for a link
            // that was paid in its last second settles.
            if (($link['status'] ?? null) === 'paid') {
                $this->settleLink($sale, $link, $paymentId);
            } elseif (in_array($link['status'] ?? null, ['expired', 'cancelled'], true)) {
                $this->releaseExpiredLink($sale, $link);
            }

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

    // ----------------------------------------------------------------- helpers

    /**
     * Cross-check Razorpay's copy of the link against the sale, then settle.
     *
     * @param  array<string, mixed>  $link  as fetched from the API, not as received
     */
    private function settleLink(Sale $sale, array $link, string $paymentIdHint): void
    {
        if (! $this->linkBelongsTo($sale, $link)) {
            return;
        }

        if (strtoupper((string) ($link['currency'] ?? '')) !== 'INR'
            || strtoupper((string) ($sale->event?->ticket_currency_code ?: '')) !== 'INR') {
            // The event's currency was changed while the buyer was on Razorpay's page (it locks only
            // once money has settled). Razorpay holds the money, so a person has to deal with it.
            Log::error('Razorpay link currency did not match the event', ['sale_id' => $sale->id]);
            report(new \RuntimeException('Razorpay payment for sale '.$sale->id.' is in INR, but the event is no longer priced in INR - not settled'));

            return;
        }

        if (($link['status'] ?? null) !== 'paid') {
            Log::info('Razorpay link is not paid', ['sale_id' => $sale->id, 'status' => $link['status'] ?? null]);

            return;
        }

        $paymentId = $this->capturedPaymentId($link, $paymentIdHint);
        $current = Sale::find($sale->id);

        // This payment already settled this sale, and the sale has moved on since: a refreshed
        // return, a repeated webhook, or Back to Razorpay's success page after a refund. settle()
        // would read a refunded or cancelled sale as released and report money with no ticket,
        // when the money is the very payment that was refunded.
        if ($current && $current->status !== 'unpaid' && $paymentId !== null
            && $current->transaction_reference === $paymentId) {
            Log::info('Razorpay payment already settled this sale', ['sale_id' => $sale->id, 'status' => $current->status]);

            return;
        }

        // Strict, so a sale already parked as amount_mismatch is not reconciled (and audited) again
        // on every refresh: it answers not_settleable instead.
        $outcome = $this->settlement->settle(
            $sale,
            $paymentId,
            isset($link['amount_paid']) ? ((int) $link['amount_paid']) / 100 : null,
            $this->key(),
            onlyWhenUnpaid: true,
        );

        $this->recordOutcome($sale, $outcome, $paymentId);
    }

    /**
     * A ticket sale whose link can no longer be paid gives its seats back, as PayPal's and Payfast's
     * cancel return does - Payment Links have no cancel return. A booking is left alone: its hold has
     * a clock of its own (ReleaseTickets), and a guest who let one link lapse can still pay again.
     *
     * @param  array<string, mixed>  $link  as fetched from the API
     */
    private function releaseExpiredLink(Sale $sale, array $link): void
    {
        if (! $this->linkBelongsTo($sale, $link) || $sale->event?->appointment_type_id) {
            return;
        }

        if ($sale->status !== 'unpaid') {
            return;
        }

        Log::info('Razorpay link expired unpaid - releasing the sale', ['sale_id' => $sale->id, 'status' => $link['status'] ?? null]);

        $this->releaseAbandonedSale($sale, 'razorpay_link_expired');
    }

    /**
     * A valid link from the same account, but for a different sale, must not touch this one.
     *
     * @param  array<string, mixed>  $link
     */
    private function linkBelongsTo(Sale $sale, array $link): bool
    {
        $encodedSaleId = UrlUtils::encodeId($sale->id);

        if (! hash_equals($encodedSaleId, (string) ($link['notes']['sale_id'] ?? ''))
            || ! str_starts_with((string) ($link['reference_id'] ?? ''), $encodedSaleId.'-')) {
            Log::warning('Razorpay link named a different sale', ['sale_id' => $sale->id]);

            return false;
        }

        return true;
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
    private function recordOutcome(Sale $sale, string $outcome, ?string $paymentId): void
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

            // Paid already - by this payment (the return and the webhook raced), or by ANOTHER one.
            // A booking can be sent to pay more than once, and an earlier link stays payable until it
            // expires, so a guest can pay twice; the second is money to give back.
            'already_paid' => (function () use ($sale, $paymentId) {
                $stored = (string) Sale::whereKey($sale->id)->value('transaction_reference');

                if ($paymentId === null || $stored === $paymentId) {
                    Log::info('Razorpay payment settled', ['sale_id' => $sale->id, 'outcome' => 'already_paid']);

                    return;
                }

                // Marked paid by hand (the stored reference is the manual_payment text, not a pay_
                // id), perhaps for this very payment when its confirmation was slow. Not necessarily
                // money twice, so say what to check rather than what to refund.
                if (! preg_match(self::PAYMENT_ID_PATTERN, $stored)) {
                    Log::warning('Razorpay payment arrived for a sale marked paid by hand', [
                        'sale_id' => $sale->id,
                        'payment_id' => $paymentId,
                    ]);

                    report(new \RuntimeException(
                        'Razorpay payment '.$paymentId.' arrived for sale '.$sale->id.', which had been marked paid by hand - check whether it was paid twice'
                    ));

                    return;
                }

                Log::error('Razorpay took a second payment for a sale that was already paid', [
                    'sale_id' => $sale->id,
                    'payment_id' => $paymentId,
                ]);

                report(new \RuntimeException(
                    'Razorpay payment '.$paymentId.' arrived for sale '.$sale->id.', which was already paid - refund it in Razorpay'
                ));
            })(),

            'amount_mismatch' => Log::warning('Razorpay amount mismatch - sale parked for review', [
                'sale_id' => $sale->id,
            ]),

            // The sale is parked for review (amount_mismatch). settleLink() has already passed over
            // the payment that parked it, so this is a DIFFERENT one: more money a person has to see.
            'not_settleable' => (function () use ($sale, $paymentId) {
                Log::error('Razorpay took another payment for a sale awaiting review', [
                    'sale_id' => $sale->id,
                    'payment_id' => $paymentId,
                ]);

                report(new \RuntimeException(
                    'Razorpay payment '.$paymentId.' arrived for sale '.$sale->id.', which is awaiting review for an earlier payment'
                ));
            })(),

            'paid' => Log::info('Razorpay payment settled', [
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
