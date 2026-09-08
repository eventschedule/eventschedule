<?php

namespace App\Services\Payments\Gateways;

use App\Models\Sale;
use App\Models\User;
use App\Services\Payments\CheckoutContext;
use App\Services\Payments\CredentialField;
use App\Services\Payments\PaymentGatewayDriver;
use App\Services\Payments\PayPal\PayPalClient;
use App\Services\SaleSettlementService;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

/**
 * PayPal, via the Orders v2 REST API with intent CAPTURE.
 *
 * THIS DRIVER INVERTS THE BASE CLASS'S ASSUMPTION, and it is worth knowing why before changing
 * anything here. handleReturn()'s docblock says the gateway's own callback is what settles a sale and
 * the buyer's return is not to be trusted. That is right for Payfast, whose ITN is the only
 * server-to-server word it gets. It is wrong for PayPal: with intent CAPTURE, the buyer approving on
 * paypal.com moves NO money. Money moves only when WE call capture with the owner's credentials, and
 * that response is one the buyer cannot influence - the same property PayfastClient::confirmsPayment()
 * was built to obtain. So the return is the settlement path here, and the webhook is the safety net
 * for the two genuinely asynchronous cases: a capture that comes back PENDING, and a capture whose
 * response we lost to a timeout.
 *
 * Consequences that fall out of that, all deliberate:
 *  - An abandoned return is harmless. Nothing was charged, so expiring the sale releases the seats
 *    and costs nobody anything. Payfast's "buyer charged, ITN lost, seats held forever" hazard does
 *    not exist here.
 *  - There is no signature to hand-roll. startCheckout() returns a plain redirect, not a signed
 *    self-posting form, so there is no PayPalSignature and no skip_body_rewrite.
 *  - The whole happy path is testable on localhost, which Payfast's own docs have to warn is
 *    impossible for it. Only the webhook needs a reachable host.
 *
 * Per-owner API credentials rather than PayPal's Partner onboarding: Partner needs PayPal to approve
 * this platform as a partner and has no meaning at all on a selfhost install, where there is no
 * platform. Installation-wide keys are a DEFAULT that an owner's own account overrides, which is
 * Payfast's semantics - see PaymentGatewayDriver::credentialsFor().
 */
class PayPalGateway extends PaymentGatewayDriver
{
    /**
     * The events we ask PayPal to send us, and no more.
     *
     * PAYMENT.CAPTURE.REFUNDED and .REVERSED are deliberately absent. A refund raised inside PayPal's
     * own dashboard is a real thing, but nothing in this app has anywhere to put it - StripeController
     * subscribes to no refund event either - so handling it would be a half-feature bolted onto this
     * change. Registering an event we then ignore is worse than not registering it: PayPal retries a
     * non-2xx for three days and disables a listener that keeps failing. They belong with refunds.
     */
    private const WEBHOOK_EVENTS = [
        'PAYMENT.CAPTURE.COMPLETED',
        'PAYMENT.CAPTURE.DENIED',
    ];

    public function __construct(private SaleSettlementService $settlement) {}

    public function key(): string
    {
        return 'paypal';
    }

    /**
     * The product name, and nothing else.
     *
     * Payfast names its merchant id here because that id is digits-only-validated and tells an owner
     * with several accounts which one is selected. A PayPal client id is an 80-character opaque blob
     * that would tell them nothing, and this string is echoed server-side into a Vue-mounted <option>,
     * so the less of it that comes from user input the better.
     *
     * Test mode IS named, and that is not cosmetic. A forgotten sandbox toggle sells tickets that look
     * entirely normal and take no money - and for PayPal sandbox is a different API host AND a
     * different set of credentials, so it is easier to leave on than Payfast's single flag.
     */
    public function label(?User $owner): string
    {
        $credentials = $this->credentialsFor($owner);

        return empty($credentials['paypal_sandbox'])
            ? 'PayPal'
            : 'PayPal ('.__('messages.paypal_sandbox').')';
    }

    public function isConfiguredFor(?User $owner): bool
    {
        return $this->credentialsFor($owner) !== null;
    }

    /**
     * PayPal settles about two dozen currencies and no others - notably NOT ZAR and NOT INR.
     *
     * The base returns true for everything, which would let an owner pick PayPal for a rand-priced
     * event and have every buyer rejected on PayPal's own page, after the seats were already held.
     * Same reasoning as PayfastGateway::supportsCurrency(); config-driven so an operator can follow
     * a PayPal change without waiting for a release.
     */
    public function supportsCurrency(string $currencyCode): bool
    {
        return in_array(strtoupper($currencyCode), array_map('strtoupper', (array) config('payments.paypal.currencies', [])), true);
    }

    /**
     * A multi-event cart is fine here, unlike Payfast.
     *
     * Payfast refuses because one ITN carries one m_payment_id. PayPal has no such limit, and the
     * answer is deliberately NOT several purchase_units - it is ONE unit at the order total with
     * items[] as line detail. That keeps the whole order to a single capture, which is what the rest
     * of the app already assumes: settle() takes one reference, sales.transaction_reference is one
     * column, and refundReferenceFor() returns one id. Several units would mean several captures and
     * none of that would hold.
     *
     * TicketController sorts the legs by event id before choosing $event precisely so that the event
     * handed to CheckoutContext is the same leg the transaction returns as the order primary, and the
     * cart guard has already proven every leg shares an owner, a currency and a payment method - so
     * total(), currency() and owner() are all correct for the whole order.
     */
    public function supportsCart(): bool
    {
        return true;
    }

    /**
     * No amount limits.
     *
     * Payfast models a floor because its Custom integration has no server-side pre-flight: the first
     * time it can refuse an amount is on the buyer's screen. PayPal creates the order server-side
     * BEFORE the buyer goes anywhere, so an amount it will not take comes back as a 4xx inside
     * startCheckout() and is handled by the same release-and-refuse path. Modelling a floor here
     * would be guessing at a number PayPal does not publish.
     */
    public function amountLimits(string $currencyCode): array
    {
        return [null, null];
    }

    /**
     * Three fields. paypal_webhook_id is deliberately NOT among them.
     *
     * It is not a credential for taking money - it is operational state this driver registers and
     * stores - and a "Webhook ID" input would be developer jargon an organiser cannot act on. The
     * consequence to remember is that credentialsFor() builds its array FROM this list, so the column
     * is absent from that array and the webhook path reads it off the owner directly.
     *
     * Only the id and the secret are required, which is what lets hasOwnCredentials() return true and
     * therefore what makes installation-wide keys a default rather than the only rail.
     */
    public function credentialFields(): array
    {
        return [
            new CredentialField(
                name: 'paypal_client_id',
                label: 'messages.paypal_client_id',
                required: true,
                help: 'messages.paypal_client_id_help',
            ),
            new CredentialField(
                name: 'paypal_client_secret',
                label: 'messages.paypal_client_secret',
                type: 'password',
                required: true,
            ),
            new CredentialField(
                name: 'paypal_sandbox',
                label: 'messages.paypal_sandbox',
                type: 'toggle',
                help: 'messages.paypal_sandbox_help',
            ),
        ];
    }

    public function credentialRules(): array
    {
        return [
            // Charset-constrained at the input as well as at the sink. label() does not echo this
            // one, but the same reasoning that pinned Payfast's merchant id to digits applies: a
            // credential that reaches a Vue-mounted template has no business carrying punctuation.
            'paypal_client_id' => ['required', 'string', 'max:120', 'regex:/^[A-Za-z0-9_-]+$/'],
            // Blank means "keep what is stored", so an owner can correct the client id without
            // retyping a secret they cannot read back - but a FIRST connect cannot leave it empty.
            'paypal_client_secret' => [$this->requiredUnlessStored('paypal_client_secret'), 'string', 'max:200'],
            'paypal_sandbox' => ['nullable', 'boolean'],
        ];
    }

    public function credentialHelp(): ?string
    {
        return __('messages.paypal_help');
    }

    /**
     * Installation-wide credentials, for a selfhost operator who wants one PayPal account to serve
     * every schedule.
     *
     * A DEFAULT, never an override: an owner who connects their own keeps using it. Empty whenever
     * hosted - PaymentGatewayDriver calls that a MUST and the registry test asserts it across every
     * driver, because getting it wrong routes every hosted sale into the operator's account.
     *
     * config() is read on every call rather than memoized into a property: PaymentGatewayManager is a
     * singleton and would otherwise outlive a config change.
     *
     * The webhook id is NOT required for the set to count, unlike Payfast's passphrase. Payfast needs
     * its passphrase or the ITN signature proves nothing; PayPal's gate is the capture re-fetch, so a
     * missing webhook id costs only the late-settlement path. Do not "fix" this by requiring it.
     */
    public function platformCredentials(): array
    {
        if (config('app.hosted')) {
            return [];
        }

        $clientId = (string) config('payments.paypal.client_id', '');
        $secret = (string) config('payments.paypal.client_secret', '');

        if ($clientId === '' || $secret === '') {
            return [];
        }

        return [
            'paypal_client_id' => $clientId,
            'paypal_client_secret' => $secret,
            'paypal_sandbox' => (bool) config('payments.paypal.sandbox', false),
            'paypal_webhook_id' => (string) config('payments.paypal.webhook_id', ''),
        ];
    }

    /**
     * Save, but check the credentials first - PayPal is the only gateway here that can be asked.
     *
     * Without this an owner who transposes a character sees "Connected", picks PayPal on an event,
     * and finds out when a BUYER cannot pay. Payfast has to live with that because it has no cheap
     * verify call; minting a PayPal token is free and has no side effects.
     *
     * The two failures get opposite handling, which is the same distinction SaleRefundService draws:
     * PayPal ANSWERED and rejected the credentials, so refuse the save; or PayPal could not be
     * reached at all, in which case store them anyway - refusing to save good credentials because a
     * third party is down is the worse error.
     */
    public function saveCredentials(User $owner, array $input): void
    {
        $clientId = (string) ($input['paypal_client_id'] ?? $owner->paypal_client_id);
        $secret = (string) ($input['paypal_client_secret'] ?? '') ?: (string) $owner->paypal_client_secret;
        $sandbox = (bool) ($input['paypal_sandbox'] ?? $owner->paypal_sandbox);

        $client = new PayPalClient([
            'paypal_client_id' => $clientId,
            'paypal_client_secret' => $secret,
            'paypal_sandbox' => $sandbox,
        ]);

        if ($clientId !== '' && $secret !== '') {
            $response = $client->mintToken($clientId, $secret);

            if ($response && ! $response->successful()) {
                throw ValidationException::withMessages([
                    'paypal_client_id' => __('messages.paypal_credentials_rejected'),
                ]);
            }

            if (! $response) {
                // Unknown, not wrong. Saved, with the owner told we could not check.
                session()->flash('warning', __('messages.paypal_credentials_unverified'));
            }
        }

        parent::saveCredentials($owner, $input);

        $this->ensureWebhookRegistered($owner->refresh());
    }

    /**
     * Best-effort listener registration, and never a reason to fail a save.
     *
     * It legitimately cannot work on a localhost or otherwise unreachable APP_URL, and the return
     * path settles without it, so a failure costs only the late cases - a PENDING capture that
     * completes later, or a capture whose response we lost.
     */
    private function ensureWebhookRegistered(User $owner): void
    {
        if (! empty($owner->paypal_webhook_id) || ! $this->hasOwnCredentials($owner)) {
            return;
        }

        try {
            // route(..., false) because app_url() prepends the root, and on selfhost that root
            // already carries the front-controller base path - handing it an absolute path would
            // send PayPal to /public/public/... See PayfastGateway's notify_url for the same idiom.
            $url = app_url(route('payments.webhook', ['gateway' => $this->key()], false));

            $webhookId = (new PayPalClient($this->credentialsFor($owner) ?? []))
                ->registerWebhook($url, self::WEBHOOK_EVENTS);

            if ($webhookId) {
                $owner->forceFill(['paypal_webhook_id' => $webhookId])->saveQuietly();
            }
        } catch (\Throwable $e) {
            Log::warning('PayPal webhook registration failed: '.$e->getMessage());
        }
    }

    /**
     * Remove the listener before the credentials that authorise removing it are gone.
     */
    public function disconnect(User $owner): void
    {
        if ($owner->paypal_webhook_id && $this->hasOwnCredentials($owner)) {
            try {
                (new PayPalClient($this->credentialsFor($owner) ?? []))->deleteWebhook($owner->paypal_webhook_id);
            } catch (\Throwable $e) {
                // A stale listener at PayPal is a nuisance; a failed disconnect is a support ticket.
                Log::warning('PayPal webhook removal failed: '.$e->getMessage());
            }
        }

        parent::disconnect($owner);

        // Not a declared credential field, so the parent does not clear it.
        $owner->paypal_webhook_id = null;
        $owner->save();
    }

    /**
     * PayPal does have a stable per-transaction page, unlike Payfast.
     *
     * Guarded on the capture-id shape because transaction_reference also holds the translated
     * manual_payment sentinel for a sale marked paid by hand, and linking that would 404. In-memory
     * only: the admin sales table calls this once per row.
     */
    public function referenceUrl(Sale $sale): ?string
    {
        $reference = trim((string) $sale->transaction_reference);

        if (! preg_match('/^[A-Z0-9]{17}$/', $reference)) {
            return null;
        }

        $sandbox = ! empty($this->credentialsFor($sale->event?->user)['paypal_sandbox']);

        return 'https://www.'.($sandbox ? 'sandbox.' : '').'paypal.com/activity/payment/'.$reference;
    }
}
