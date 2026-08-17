<?php

namespace App\Services\Payments\Gateways;

use App\Models\Sale;
use App\Models\User;
use App\Services\Payments\CheckoutContext;
use App\Services\Payments\CredentialField;
use App\Services\Payments\Payfast\PayfastClient;
use App\Services\Payments\Payfast\PayfastSignature;
use App\Services\Payments\PaymentGatewayDriver;
use App\Services\SaleSettlementService;
use App\Utils\UrlUtils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Payfast, the dominant gateway in South Africa (issue #113).
 *
 * Redirect ("Custom") integration: we render a signed form that posts the buyer to Payfast, and
 * Payfast reports the outcome by POSTing an ITN to our webhook. The ITN is the authority - the
 * buyer's return is just where they land.
 *
 * Payfast's on-site (embedded modal) flow is deliberately not implemented. It requires loading
 * engine.js from payfast.co.za on the ticket page, and this app does not call out to third-party
 * hosts from selfhost installs. The seam for it is startCheckout(), which already returns a Response
 * rather than a redirect.
 *
 * Hand-rolled rather than pulling in a package: the protocol is a signed form post and an MD5 check,
 * and Invoice Ninja's own driver hand-writes exactly this too.
 */
class PayfastGateway extends PaymentGatewayDriver
{
    /**
     * Payfast will not process under R5.00, and it reports that as a failure on its own page rather
     * than telling us up front.
     */
    private const MINIMUM_AMOUNT = 5.0;

    /** Payfast's own field limits. Exceeding them is rejected at the redirect. */
    private const MAX_ITEM_NAME = 100;

    private const MAX_ITEM_DESCRIPTION = 255;

    public function __construct(private SaleSettlementService $settlement) {}

    public function key(): string
    {
        return 'payfast';
    }

    public function label(?User $owner): string
    {
        $label = $owner?->payfast_merchant_id
            ? 'Payfast - '.$owner->payfast_merchant_id
            : 'Payfast';

        // A forgotten sandbox toggle sells free tickets that look completely normal, so test mode is
        // named everywhere the owner chooses the gateway.
        if ($owner?->payfast_sandbox) {
            $label .= ' ('.__('messages.payfast_sandbox').')';
        }

        return $label;
    }

    /**
     * All three are required before Payfast is offered as a payment method.
     *
     * The merchant id and key are needed to sign a checkout at all. The passphrase is optional at
     * Payfast itself, but not here: without one the ITN signature is a plain MD5 anyone can
     * reproduce, so an account missing it would accept notifications it cannot actually authenticate.
     * Excluded here rather than only validated on save, so a half-configured account never reaches an
     * event's payment dropdown.
     */
    public function isConfiguredFor(?User $owner): bool
    {
        return (bool) ($owner?->payfast_merchant_id
            && $owner->payfast_merchant_key
            && $owner->payfast_passphrase);
    }

    /**
     * ZAR only. Payfast settles in nothing else, and letting an owner pick it for a USD event turns a
     * setup mistake into a buyer-facing rejection on somebody else's page.
     */
    public function supportsCurrency(string $currencyCode): bool
    {
        return strtoupper($currencyCode) === 'ZAR';
    }

    public function amountLimits(string $currencyCode): array
    {
        return [self::MINIMUM_AMOUNT, null];
    }

    /**
     * One event per redirect: a Payfast payment carries a single m_payment_id, and settling several
     * legs from one ITN is not something this integration attempts.
     */
    public function supportsCart(): bool
    {
        return false;
    }

    /**
     * The instruments an owner can pin the checkout to. Payfast shows all of them when the
     * payment_method field is omitted, which is the default here.
     *
     * @return array<string, string>
     */
    public function paymentTypes(): array
    {
        return [
            'cc' => __('messages.payfast_type_cc'),
            'dc' => __('messages.payfast_type_dc'),
            'ef' => __('messages.payfast_type_ef'),
            'cp' => __('messages.payfast_type_cp'),
            'ap' => __('messages.payfast_type_ap'),
            'sp' => __('messages.payfast_type_sp'),
            'gp' => __('messages.payfast_type_gp'),
            'mc' => __('messages.payfast_type_mc'),
            'mt' => __('messages.payfast_type_mt'),
            'ss' => __('messages.payfast_type_ss'),
            'zp' => __('messages.payfast_type_zp'),
            'mu' => __('messages.payfast_type_mu'),
            'nd' => __('messages.payfast_type_nd'),
            'pf' => __('messages.payfast_type_pf'),
            'rc' => __('messages.payfast_type_rc'),
            'ab' => __('messages.payfast_type_ab'),
            'sc' => __('messages.payfast_type_sc'),
            'mp' => __('messages.payfast_type_mp'),
        ];
    }

    /**
     * @return list<CredentialField>
     */
    public function credentialFields(): array
    {
        return [
            new CredentialField(
                name: 'payfast_merchant_id',
                label: 'messages.payfast_merchant_id',
                required: true,
            ),
            new CredentialField(
                name: 'payfast_merchant_key',
                label: 'messages.payfast_merchant_key',
                type: 'password',
                required: true,
            ),
            new CredentialField(
                name: 'payfast_passphrase',
                label: 'messages.payfast_passphrase',
                type: 'password',
                help: 'messages.payfast_passphrase_help',
                required: true,
            ),
            new CredentialField(
                name: 'payfast_sandbox',
                label: 'messages.payfast_sandbox',
                type: 'toggle',
                help: 'messages.payfast_sandbox_help',
            ),
            new CredentialField(
                name: 'payfast_payment_types',
                label: 'messages.payfast_payment_types',
                type: 'multiselect',
                options: $this->paymentTypes(),
                help: 'messages.payfast_payment_types_help',
            ),
        ];
    }

    public function credentialRules(): array
    {
        return [
            // Digits only: real Payfast merchant ids are numeric, and the id is echoed into the
            // event dropdown's option text inside a Vue mount - constraining the charset here closes
            // the template-injection source at the input as well as at the sink (the option carries
            // v-pre too).
            'payfast_merchant_id' => ['required', 'string', 'max:32', 'regex:/^\d+$/'],
            // Both secrets are "required unless one is already stored". Blank means "keep what is
            // there", so an owner correcting a merchant id does not have to retype values they cannot
            // read back - but a FIRST connect cannot leave them empty and save a half-configured
            // account that isConfiguredFor() would then reject anyway.
            //
            // The passphrase in particular: Payfast treats it as optional, we do not. Without one the
            // ITN signature is a plain MD5 of the payload that anyone can reproduce, so the check
            // meant to prove a notification came from Payfast would prove nothing.
            'payfast_merchant_key' => [$this->requiredUnlessStored('payfast_merchant_key'), 'string', 'max:64'],
            'payfast_passphrase' => [$this->requiredUnlessStored('payfast_passphrase'), 'string', 'max:100'],
            'payfast_sandbox' => ['nullable', 'boolean'],
            'payfast_payment_types' => ['nullable', 'array'],
            // nullable: the form posts a blank sentinel entry so that unticking every box still
            // sends the key, which is what lets an owner clear a restriction. It arrives as null
            // (ConvertEmptyStringsToNull recurses) and saveCredentials' array_intersect drops it.
            'payfast_payment_types.*' => ['nullable', 'string', 'in:'.implode(',', array_keys($this->paymentTypes()))],
        ];
    }

    /**
     * 'required' on a first connect, 'nullable' once a value is stored - the blank-means-unchanged
     * convention the credentials form uses for every secret.
     */
    private function requiredUnlessStored(string $field): string
    {
        return request()->user()?->{$field} ? 'nullable' : 'required';
    }

    public function credentialHelp(): ?string
    {
        return __('messages.payfast_help');
    }

    public function referenceUrl(Sale $sale): ?string
    {
        // Payfast's dashboard has no stable per-transaction deep link, so the reference is shown as
        // plain text. Returning null is how a driver says that.
        return null;
    }

    // ---------------------------------------------------------------- checkout

    /**
     * Render the signed self-submitting form that posts the buyer to Payfast.
     *
     * A rendered page rather than a redirect because Payfast's Custom integration takes a POST. This
     * is why startCheckout() returns a Response.
     */
    public function startCheckout(CheckoutContext $context): Response
    {
        $owner = $context->owner();
        $sale = $context->sale;
        $event = $context->event;

        if (! $this->isConfiguredFor($owner)) {
            // Reachable when an owner disconnects Payfast while an event still names it. Better to
            // land the buyer on their (unpaid) ticket than to post an unsigned form.
            Log::warning('Payfast checkout attempted with no credentials', ['sale_id' => $sale->id]);

            return $this->redirectToPurchaseLanding($sale, $event, $context->isEmbed);
        }

        // Refuse anything Payfast cannot actually take, BEFORE the buyer is posted anywhere.
        //
        // Currency is the one that loses money: Payfast's Custom integration has no currency field -
        // every amount is implicitly rand - so a USD event reaching this point would debit the ZAR
        // face value (R100 for a $100 ticket), and the ITN would then reconcile cleanly because both
        // sides compare the bare number. The dropdown filters by currency at render time, but the
        // stored payment_method survives an API update or a later currency edit, so the authority has
        // to live here.
        //
        // The floor is the same class with a smaller blast radius: Payfast refuses under R5.00 on its
        // own page, after the seats are held and with no way back but the browser's Back button.
        [$minimum] = $this->amountLimits($context->currency());
        $total = $context->total();

        if (! $this->supportsCurrency($context->currency())
            || ($total > 0 && $minimum !== null && $total < $minimum)) {
            Log::warning('Payfast checkout refused: unsupported currency or below minimum', [
                'sale_id' => $sale->id,
                'currency' => $context->currency(),
                'total' => $total,
            ]);

            // Same failure shape as stripeCheckout()'s catch: back to the event page with a message
            // (guest pages render session('error')), sale left unpaid for ReleaseTickets to reclaim.
            return back()->with('error', __('messages.payfast_checkout_unavailable'));
        }

        $encodedSaleId = UrlUtils::encodeId($sale->id);

        // The embed flag rides the buyer-facing callbacks so an embedded purchase lands back on the
        // embedded ticket view, exactly as the Stripe session URLs do.
        $callbackParams = [
            'gateway' => $this->key(),
            'sale_id' => $encodedSaleId,
            'secret' => $sale->secret,
        ];

        if ($context->isEmbed) {
            $callbackParams['embed'] = 'true';
        }

        // custom_domain_url() on all three: a checkout served from a custom domain has its HTML body
        // rewritten by ResolveCustomDomain AFTER this method signs the fields, and the rewrite cannot
        // update the signature. Generating the URLs on the custom domain up front means the rewrite
        // finds nothing to replace in them, so what the browser posts is exactly what was signed.
        // A no-op away from custom domains, and the same helper createStripeSession() uses.
        $fields = [
            'merchant_id' => $owner->payfast_merchant_id,
            'merchant_key' => $owner->payfast_merchant_key,
            // The sale secret rides along on the buyer-facing callbacks: the id alone is a Sqid and
            // proves nothing, and PaymentGatewayController::resolve() refuses both without it. Not on
            // notify_url, which Payfast authenticates by signature instead and which must not carry a
            // ticket token through a third party's logs.
            'return_url' => custom_domain_url(route('payments.return', $callbackParams)),
            'cancel_url' => custom_domain_url(route('payments.cancel', $callbackParams)),
            'notify_url' => custom_domain_url(route('payments.webhook', ['gateway' => $this->key(), 'sale_id' => $encodedSaleId])),
            'name_first' => $this->clean($sale->name, 100),
            'email_address' => $sale->email,
            // The encoded sale id, so the ITN can be cross-checked against the sale in its own URL.
            // Payfast caps this at 100 characters.
            'm_payment_id' => $encodedSaleId,
            // Major units with exactly two decimals, and no thousands separator - not cents.
            'amount' => number_format($context->total(), 2, '.', ''),
            'item_name' => $this->clean($event->name ?: __('messages.tickets'), self::MAX_ITEM_NAME),
        ];

        $description = $this->clean($event->short_description ?: '', self::MAX_ITEM_DESCRIPTION);
        if ($description !== '') {
            $fields['item_description'] = $description;
        }

        $restrictTo = $this->restrictedPaymentType($owner);
        if ($restrictTo !== null) {
            $fields['payment_method'] = $restrictTo;
        }

        // Drop empties BEFORE signing, so the set posted by the view is exactly the set signed here.
        // sign() skips empty values, while the view renders whatever it is given - so a field that
        // was blank would go to Payfast unsigned and risk a signature mismatch. Not reachable today
        // (name and email are required at checkout, and TrimStrings rejects whitespace-only input),
        // but a signature should hold by construction rather than by luck.
        $fields = array_filter($fields, fn ($value) => $value !== null && $value !== '');

        // Signed with the passphrase, which is then discarded. It must never reach the form: it is the
        // shared secret that makes an ITN signature meaningful, and the buyer's browser is not a place
        // to keep it.
        $signature = PayfastSignature::sign($fields, $owner->payfast_passphrase);

        return response()->view('payments.payfast.redirect', [
            'action' => (new PayfastClient((bool) $owner->payfast_sandbox))->processUrl(),
            'fields' => $fields,
            'signature' => $signature,
            'event' => $event,
            'sandbox' => (bool) $owner->payfast_sandbox,
        ]);
    }

    // -------------------------------------------------------------- settlement

    /**
     * The ITN. Five checks before a cent is recognised, in cheapest-first order:
     *
     *  1. the sale is in a state that can be paid at all;
     *  2. the signature matches, computed over the RAW body;
     *  3. the merchant id is the owner's, so one merchant's ITN cannot settle another's sale;
     *  4. the source address really belongs to Payfast;
     *  5. Payfast itself confirms the payload, which is the one an attacker cannot influence.
     *
     * Only then does the amount get reconciled, by SaleSettlementService, which is also where
     * idempotency and the released-sale guard live.
     *
     * Always answers 200 once the request is judged genuine, including for a duplicate: Payfast
     * retries anything else, and re-delivering a payment we have already booked achieves nothing.
     */
    public function handleWebhook(Request $request, ?Sale $sale): Response
    {
        if (! $sale) {
            return response('missing sale', 400);
        }

        $owner = $sale->event?->user;

        if (! $owner || ! $this->isConfiguredFor($owner)) {
            Log::warning('Payfast ITN for a sale whose owner has no credentials', ['sale_id' => $sale->id]);

            return response('not configured', 400);
        }

        $rawBody = $request->getContent();
        $payload = PayfastSignature::parseItn($rawBody);

        if (! PayfastSignature::verifyItn($rawBody, $owner->payfast_passphrase)) {
            Log::warning('Payfast ITN signature mismatch', ['sale_id' => $sale->id]);

            return response('invalid signature', 400);
        }

        // Guards against a Payfast account other than the owner's settling this sale. Cheap, and
        // Invoice Ninja's driver omits it.
        if (! hash_equals((string) $owner->payfast_merchant_id, (string) ($payload['merchant_id'] ?? ''))) {
            Log::warning('Payfast ITN merchant id mismatch', ['sale_id' => $sale->id]);

            return response('merchant mismatch', 400);
        }

        // The sale is also identified by the URL, so this catches a valid ITN replayed against a
        // different sale's endpoint.
        if (($payload['m_payment_id'] ?? null) !== UrlUtils::encodeId($sale->id)) {
            Log::warning('Payfast ITN payment id does not match the sale in the URL', ['sale_id' => $sale->id]);

            return response('payment id mismatch', 400);
        }

        $client = new PayfastClient((bool) $owner->payfast_sandbox);

        if (! $client->confirmsPayment($payload)) {
            Log::warning('Payfast did not confirm the ITN payload', ['sale_id' => $sale->id]);

            return response('not confirmed', 400);
        }

        // Advisory, NOT a gate - and deliberately AFTER the confirmation, because it costs DNS
        // lookups and gates nothing. $request->ip() is only the buyer-facing address when Laravel
        // trusts the upstream proxy, and config/trustedproxy.php trusts none unless IS_NEXUS is set -
        // so on a selfhost install behind Cloudflare, a host-level proxy or Docker this is the
        // proxy's address and would never match. Rejecting on it meant every Payfast payment on
        // those installs took the buyer's money and issued no ticket, silently.
        //
        // Nothing is really lost by demoting it. An IP allowlist is the weakest of the checks here
        // and is entirely subsumed by confirmsPayment() above: nobody can make Payfast confirm a
        // payment that did not happen, from any address. It is also a moving target - Payfast has
        // added sending addresses that broke merchants relying on the published host list.
        if (! $client->isValidSourceIp($request->ip())) {
            Log::warning('Payfast ITN from an unrecognised source address - continuing, see confirmsPayment', [
                'sale_id' => $sale->id,
                'ip' => $request->ip(),
            ]);
        }

        $status = strtoupper((string) ($payload['payment_status'] ?? ''));

        if ($status !== 'COMPLETE') {
            // FAILED, PENDING and CANCELLED all leave the sale unpaid, for ReleaseTickets to expire
            // on its own schedule. Marking it anything else here would take the seats out of
            // circulation on the strength of a payment that never happened.
            Log::info('Payfast ITN reported a non-complete payment', [
                'sale_id' => $sale->id,
                'payment_status' => $status,
            ]);

            return response()->noContent();
        }

        $outcome = $this->settlement->settle(
            $sale,
            (string) ($payload['pf_payment_id'] ?? ''),
            isset($payload['amount_gross']) ? (float) $payload['amount_gross'] : null,
            $this->key(),
        );

        // The outcome decides how loudly this is recorded, because some of them mean Payfast HAS the
        // buyer's money and this install cannot honour it. All of them still answer 204: a retry
        // cannot fix any of these, and a non-2xx would just make Payfast redeliver the same news.
        //
        //  - released: the sale was expired (ReleaseTickets) or cancelled before the ITN landed -
        //    realistic for Instant EFT's PENDING-then-COMPLETE flow. Seats already went back;
        //    reviving would oversell. The buyer paid and holds nothing, so a human must act.
        //  - deleted / missing: the sale row is gone or flagged deleted; same money-with-no-ticket
        //    shape.
        //  - amount_mismatch: parked for review; AdminAlertService already counts these.
        if (in_array($outcome, ['released', 'deleted', 'missing'], true)) {
            Log::error('Payfast payment received for a sale that can no longer be honoured', [
                'sale_id' => $sale->id,
                'outcome' => $outcome,
                'pf_payment_id' => $payload['pf_payment_id'] ?? null,
                'amount_gross' => $payload['amount_gross'] ?? null,
            ]);

            // report() so hosted installs surface this in Sentry (REPORT_ERRORS); a log line alone
            // is how the fatal-source-IP bug stayed invisible.
            report(new \RuntimeException(
                'Payfast payment received for sale '.$sale->id." that can no longer be honoured (outcome: {$outcome})"
            ));
        } elseif ($outcome === 'amount_mismatch') {
            Log::warning('Payfast ITN amount mismatch - sale parked for review', [
                'sale_id' => $sale->id,
                'amount_gross' => $payload['amount_gross'] ?? null,
            ]);
        } else {
            Log::info('Payfast ITN settled', ['sale_id' => $sale->id, 'outcome' => $outcome]);
        }

        return response()->noContent();
    }

    // ----------------------------------------------------------------- helpers

    /**
     * The single payment type to pin the checkout to, or null to let Payfast offer everything.
     *
     * Payfast's payment_method field takes one code, not a list, so an owner who has ticked several
     * gets the unrestricted page - which is what they meant by choosing more than one.
     */
    private function restrictedPaymentType(User $owner): ?string
    {
        $selected = array_values(array_filter(
            explode(',', (string) $owner->payfast_payment_types),
        ));

        if (count($selected) !== 1) {
            return null;
        }

        return array_key_exists($selected[0], $this->paymentTypes()) ? $selected[0] : null;
    }

    /**
     * Payfast rejects the whole redirect on an over-long field, and newlines break the signature
     * string, so both are dealt with before signing rather than after being bounced.
     */
    private function clean(?string $value, int $max): string
    {
        $value = trim(preg_replace('/\s+/u', ' ', (string) $value) ?? '');

        return mb_substr($value, 0, $max);
    }
}
