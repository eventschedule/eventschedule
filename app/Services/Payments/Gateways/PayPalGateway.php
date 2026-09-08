<?php

namespace App\Services\Payments\Gateways;

use App\Models\Sale;
use App\Models\User;
use App\Services\Payments\CheckoutContext;
use App\Services\Payments\CredentialField;
use App\Services\Payments\PaymentGatewayDriver;
use App\Services\Payments\PayPal\PayPalClient;
use App\Services\Payments\PayPal\PayPalMoney;
use App\Services\SaleSettlementService;
use App\Utils\UrlUtils;
use Illuminate\Http\Request;
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

    /**
     * Resumable in general - but NOT while PayPal is still reviewing a payment for this sale.
     *
     * The sale is `unpaid` either way, and ticket/view.blade.php renders "this ticket is not paid"
     * plus a Complete payment button for any unpaid sale on a resumable rail. That button starts a
     * FRESH checkout against a NEW Sale, so a buyer whose payment is merely under review would be
     * charged a second time for the same seat. The base method takes a $sale for exactly this: a
     * rail can be resumable in principle and not for one row.
     */
    public function canResumePayment(?Sale $sale = null): bool
    {
        return ! $sale?->paypal_pending_at;
    }

    // ---------------------------------------------------------------- checkout

    /**
     * Create the order and send the buyer to PayPal to approve it.
     *
     * A plain redirect, not a rendered form: PayPal takes a GET, so there is nothing to sign and
     * nothing to POST. ResolveCustomDomain only rewrites a Location by str_replace of our own
     * subdomain URL, so a paypal.com Location passes through it untouched.
     */
    public function startCheckout(CheckoutContext $context): Response
    {
        $sale = $context->sale;
        $event = $context->event;
        $currency = $context->currency();

        $credentials = $this->credentialsFor($context->owner());

        if (! $credentials) {
            // Reachable when an owner disconnects PayPal while an event still names it. Give the
            // seats back first: TicketController has committed the Sale and SaleTicket rows and
            // `sold` is already incremented, so simply landing the buyer would hold that inventory
            // forever - expire_unpaid_tickets defaults to 0 and ReleaseTickets only sweeps events
            // that opted in.
            Log::warning('PayPal checkout attempted with no credentials', ['sale_id' => $sale->id]);
            $this->releaseAbandonedSale($sale, 'paypal_unconfigured');

            return $this->redirectToPurchaseLanding($sale, $event, $context->isEmbed);
        }

        // Checked again here, not just in the dropdown: the stored payment method outlives a
        // currency edit and can be set straight through the API.
        if (! $this->supportsCurrency($currency)) {
            Log::warning('PayPal checkout refused - unsupported currency', [
                'sale_id' => $sale->id,
                'currency' => $currency,
            ]);
            $this->releaseAbandonedSale($sale, 'paypal_refused');

            return back()->withInput()->with('error', __('messages.paypal_checkout_unavailable'));
        }

        $encodedSaleId = UrlUtils::encodeId($sale->id);
        $value = PayPalMoney::value($context->total(), $currency);

        $callbackParams = [
            'gateway' => $this->key(),
            'sale_id' => $encodedSaleId,
            // The id alone is a Sqid and proves nothing; PaymentGatewayController::resolve() refuses
            // both callbacks without the secret.
            'secret' => $sale->secret,
        ];

        if ($context->isEmbed) {
            $callbackParams['embed'] = 'true';
        }

        $payload = [
            'intent' => 'CAPTURE',
            'purchase_units' => [[
                // ONE unit for the whole order, even for a multi-event cart - see supportsCart().
                // custom_id is how both callbacks find their way back to this sale, and it is the
                // only identifier of ours that rides onto the capture.
                'custom_id' => $encodedSaleId,
                'description' => $this->clean($event->name ?: __('messages.tickets'), 127),
                'amount' => ['currency_code' => strtoupper($currency), 'value' => $value],
            ]],
            'payment_source' => ['paypal' => ['experience_context' => [
                'return_url' => custom_domain_url(route('payments.return', $callbackParams)),
                'cancel_url' => custom_domain_url(route('payments.cancel', $callbackParams)),
                'user_action' => 'PAY_NOW',
                'shipping_preference' => 'NO_SHIPPING',
                'brand_name' => $this->clean($event->creatorRole?->name ?: config('app.name'), 127),
                // Refuse funding that settles days later - an eCheck. A ticket is issued at once or
                // not at all, and a sale left unpaid for three days is one ReleaseTickets expires
                // (its gift-card sweep does so at a hard 48 hours regardless of the event's own
                // setting), which would hand the seats back on money PayPal had already taken.
                'payment_method_preference' => 'IMMEDIATE_PAYMENT_REQUIRED',
            ]]],
        ];

        // Sale-scoped so a double-submit reuses one order, amount-hashed so a resumed checkout at a
        // different total is a NEW order rather than a DUPLICATE_REQUEST_ID rejection.
        $requestId = 'ord-'.$encodedSaleId.'-'.substr(sha1($value.'|'.$currency), 0, 8);

        $order = (new PayPalClient($credentials))->createOrder($payload, $requestId);
        $approveUrl = $order ? $this->approveUrlFrom($order) : null;

        if (! $approveUrl) {
            // This arm stands in for the amount floor Payfast has to model: PayPal refuses an amount
            // it will not take here, server-side, before the buyer has gone anywhere.
            Log::warning('PayPal order could not be created', ['sale_id' => $sale->id]);
            $this->releaseAbandonedSale($sale, 'paypal_order_failed');

            return back()->withInput()->with('error', __('messages.paypal_checkout_unavailable'));
        }

        $sale->forceFill(['paypal_order_id' => (string) $order['id']])->saveQuietly();

        return redirect($approveUrl);
    }

    // -------------------------------------------------------------- settlement

    /**
     * The buyer is back from approving. Capture, then settle.
     *
     * This is where the money moves, which is why it does not follow the base class's advice to
     * treat the return as untrustworthy - see the class docblock. Everything the buyer could have
     * tampered with is cross-checked against PayPal's own answer before a cent is recognised.
     */
    public function handleReturn(Request $request, Sale $sale): Response
    {
        $orderId = (string) ($request->query('token') ?: $sale->paypal_order_id);
        $landing = fn () => $this->redirectToPurchaseLanding($sale, $sale->event, $request->boolean('embed'));

        if ($orderId === '') {
            Log::warning('PayPal return carried no order id', ['sale_id' => $sale->id]);

            return $landing();
        }

        // Both sets that could legitimately settle this, owner's first. An owner who connects their
        // own account between checkout and return would otherwise have the capture attempted with
        // credentials that never created the order.
        foreach ($this->candidateCredentials($sale->event?->user) as $credentials) {
            $client = new PayPalClient($credentials);

            // Deterministic, so a refreshed return URL is the same request to PayPal rather than a
            // second one.
            [$status, $body] = $client->captureOrder($orderId, 'cap-'.UrlUtils::encodeId($sale->id));

            if ($status !== null && $status >= 400) {
                // 422 ORDER_ALREADY_CAPTURED is a SUCCESS, not a failure: the buyer refreshed the
                // return URL, or the webhook won the race. Read the order back and settle from the
                // capture that already exists. Treating it as a failure and calling
                // releaseAbandonedSale() would hand back the seats, the promo redemption and the
                // gift-card balance for a payment PayPal actually took - by far the worst outcome
                // available here, and the reason this branch exists at all.
                if (! $this->mentionsAlreadyCaptured($body)) {
                    continue;
                }

                $body = $client->getOrder($orderId) ?? [];
            }

            $capture = $this->captureFrom($body);

            if (! $capture) {
                continue;
            }

            return $this->settleCapture($sale, $capture, $landing);
        }

        Log::warning('PayPal capture did not complete', ['sale_id' => $sale->id]);

        return $landing();
    }

    /**
     * PayPal's account-level webhook. Late settlement only - the return above is the normal path.
     *
     * $sale arrives null: the listener is registered once per PayPal app, so its URL carries no sale
     * segment and PaymentWebhookController has nothing to look up.
     */
    public function handleWebhook(Request $request, ?Sale $sale): Response
    {
        $payload = (array) $request->json()->all();

        if (! in_array((string) ($payload['event_type'] ?? ''), self::WEBHOOK_EVENTS, true)) {
            // Not something we subscribed to. 204 rather than an error: PayPal retries a non-2xx for
            // three days and disables a listener that keeps failing.
            return response()->noContent();
        }

        // Cheap gates first. Verification here is an outbound API call on an unauthenticated route,
        // so anything that can refuse a forgery without spending one must run ahead of it.
        $certUrl = (string) $request->header('paypal-cert-url');
        $certHost = parse_url($certUrl, PHP_URL_HOST);

        if (! in_array($certHost, ['api.paypal.com', 'api.sandbox.paypal.com'], true)) {
            return response('bad cert url', 400);
        }

        $sale ??= $this->saleFromWebhook($request);

        if (! $sale) {
            return response('missing sale', 400);
        }

        // PaymentWebhookController enforces this only when a sale id is in the URL, and ours has
        // none - so without this line a PayPal webhook could settle a Payfast sale.
        if ($sale->payment_method !== $this->key()) {
            return response('gateway mismatch', 400);
        }

        $owner = $sale->event?->user;
        $candidates = $this->candidateCredentials($owner);

        if (! $owner || ! $candidates) {
            // Deliberately its own message: an operator chasing a missing payment needs "the account
            // went away" to read differently from "somebody is forging notifications".
            Log::warning('PayPal webhook for a sale whose owner has no credentials', ['sale_id' => $sale->id]);

            return response('not configured', 400);
        }

        $captureId = (string) ($payload['resource']['id'] ?? '');

        if ($captureId === '') {
            return response('no capture', 400);
        }

        foreach ($candidates as $credentials) {
            $client = new PayPalClient($credentials);

            // Advisory, exactly as Payfast demoted its source-IP check and for the same reason: it is
            // subsumed by the lookup below. An explicit FAILURE is still a refusal; not holding a
            // webhook id at all is not.
            $webhookId = (string) ($credentials['paypal_webhook_id'] ?? $owner->paypal_webhook_id ?? '');

            if ($webhookId !== '') {
                $verified = $client->verifyWebhookSignature($request->headers->all(), $request->getContent(), $webhookId);

                if ($verified === false) {
                    continue;
                }
            }

            // THE gate. Nobody can make PayPal's own API report a capture that never happened.
            $capture = $client->getCapture($captureId);

            if (! $capture) {
                continue;
            }

            return $this->settleCapture($sale, $capture, fn () => response()->noContent());
        }

        Log::warning('PayPal webhook could not be confirmed with PayPal', ['sale_id' => $sale->id]);

        return response('unconfirmed', 400);
    }

    /**
     * Which owner an account-level webhook belongs to.
     *
     * First implementation of this hook - nothing called it before, because every other gateway here
     * registers its callback per payment.
     *
     * Resolving the owner from the payload before the payload is trusted is fine, and worth saying
     * why: this only chooses WHICH credentials to check against. A forged body naming any owner still
     * has to survive the capture lookup made with that owner's own keys.
     */
    public function resolveOwnerFromWebhook(Request $request): ?User
    {
        return $this->saleFromWebhook($request)?->event?->user;
    }

    // ----------------------------------------------------------------- helpers

    private function saleFromWebhook(Request $request): ?Sale
    {
        $customId = (string) $request->json('resource.custom_id', '');

        if ($customId === '') {
            return null;
        }

        $id = UrlUtils::decodeId($customId);

        return $id ? Sale::with('event.user')->find($id) : null;
    }

    /**
     * The completed capture out of an order or capture payload, whichever shape arrived.
     *
     * @param  array<string, mixed>  $body
     * @return array<string, mixed>|null
     */
    private function captureFrom(array $body): ?array
    {
        if (isset($body['purchase_units'][0]['payments']['captures'][0])) {
            return $body['purchase_units'][0]['payments']['captures'][0];
        }

        // A getCapture() response is already the capture.
        return isset($body['id'], $body['amount']) ? $body : null;
    }

    /**
     * Cross-check a capture against the sale it claims to pay for, then settle.
     *
     * @param  array<string, mixed>  $capture
     */
    private function settleCapture(Sale $sale, array $capture, callable $respond): Response
    {
        $expectedCurrency = strtoupper((string) ($sale->event?->ticket_currency_code ?: 'USD'));
        $captureCurrency = strtoupper((string) ($capture['amount']['currency_code'] ?? ''));

        // Stops a tampered token naming another order inside the same merchant account - the same
        // guard Payfast gets from m_payment_id.
        if (! hash_equals(UrlUtils::encodeId($sale->id), (string) ($capture['custom_id'] ?? ''))) {
            Log::warning('PayPal capture named a different sale', ['sale_id' => $sale->id]);

            return $respond();
        }

        // Otherwise a yen capture would reconcile against a dollar sale on face value.
        if ($captureCurrency !== $expectedCurrency) {
            Log::warning('PayPal capture currency did not match the event', [
                'sale_id' => $sale->id,
                'expected' => $expectedCurrency,
                'received' => $captureCurrency,
            ]);

            return $respond();
        }

        $status = strtoupper((string) ($capture['status'] ?? ''));

        if ($status !== 'COMPLETED') {
            // PENDING (a fraud review; eChecks are refused up front) and DECLINED both leave the sale
            // unpaid for ReleaseTickets to expire on its own schedule. Marking it anything else would
            // take seats out of circulation on a payment that has not happened.
            Log::info('PayPal capture is not complete', ['sale_id' => $sale->id, 'status' => $status]);

            if ($status === 'PENDING') {
                $sale->forceFill(['paypal_pending_at' => now()])->saveQuietly();
                session()->flash('message', __('messages.paypal_payment_pending'));
            }

            return $respond();
        }

        $outcome = $this->settlement->settle(
            $sale,
            (string) ($capture['id'] ?? ''),
            isset($capture['amount']['value']) ? (float) $capture['amount']['value'] : null,
            $this->key(),
        );

        $this->recordOutcome($sale, $outcome);

        return $respond();
    }

    /**
     * How loudly a settlement outcome is recorded. Exhaustive on purpose.
     *
     * Some of these mean PayPal HAS the buyer's money and this install cannot honour it, which is a
     * person's problem, not a retry's. An outcome nobody anticipated is far more likely to be that
     * than business as usual, so a new settle() return value must fail LOUD rather than land in a
     * quiet default logged as "settled".
     */
    private function recordOutcome(Sale $sale, string $outcome): void
    {
        match ($outcome) {
            'released', 'deleted', 'missing' => (function () use ($sale, $outcome) {
                Log::error('PayPal payment received for a sale that can no longer be honoured', [
                    'sale_id' => $sale->id,
                    'outcome' => $outcome,
                ]);

                // report() so hosted surfaces this in Sentry. Only the sale id and outcome: the
                // capture id and amount would ride Sentry's breadcrumbs to a vendor DSN on a
                // selfhost install, and both are already in the database.
                report(new \RuntimeException(
                    'PayPal payment received for sale '.$sale->id." that can no longer be honoured (outcome: {$outcome})"
                ));
            })(),

            // Parked for review; AdminAlertService already counts these.
            'amount_mismatch' => Log::warning('PayPal capture amount mismatch - sale parked for review', [
                'sale_id' => $sale->id,
            ]),

            'paid', 'already_paid' => Log::info('PayPal capture settled', [
                'sale_id' => $sale->id,
                'outcome' => $outcome,
            ]),

            default => (function () use ($sale, $outcome) {
                Log::error('PayPal capture produced an unhandled settlement outcome', [
                    'sale_id' => $sale->id,
                    'outcome' => $outcome,
                ]);

                report(new \RuntimeException(
                    'PayPal capture produced an unhandled settlement outcome for sale '.$sale->id." (outcome: {$outcome})"
                ));
            })(),
        };
    }

    /**
     * The link the buyer is sent to.
     *
     * Both rel names are accepted: Orders v2 returns 'payer-action' alongside payment_source, and
     * 'approve' with the older application_context. Pinning one is a silent breakage the day PayPal
     * changes which shape it answers with.
     *
     * @param  array<string, mixed>  $order
     */
    private function approveUrlFrom(array $order): ?string
    {
        foreach ((array) ($order['links'] ?? []) as $link) {
            if (in_array($link['rel'] ?? '', ['payer-action', 'approve'], true) && ! empty($link['href'])) {
                return (string) $link['href'];
            }
        }

        return null;
    }

    /** @param  array<string, mixed>  $body */
    private function mentionsAlreadyCaptured(array $body): bool
    {
        foreach ((array) ($body['details'] ?? []) as $detail) {
            if (($detail['issue'] ?? null) === 'ORDER_ALREADY_CAPTURED') {
                return true;
            }
        }

        return false;
    }

    private function clean(?string $value, int $max): string
    {
        return mb_substr(trim(preg_replace('/\s+/u', ' ', (string) $value)), 0, $max);
    }
}
