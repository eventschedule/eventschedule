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
        if ($owner?->payfast_merchant_id) {
            return 'Payfast - '.$owner->payfast_merchant_id;
        }

        return 'Payfast';
    }

    /**
     * Both the merchant id and key are needed to post a checkout at all. The passphrase is optional
     * at Payfast, but see credentialRules(): we require it, because without one the ITN signature
     * check is worthless.
     */
    public function isConfiguredFor(?User $owner): bool
    {
        return (bool) ($owner?->payfast_merchant_id && $owner->payfast_merchant_key);
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
            'payfast_merchant_id' => ['required', 'string', 'max:32'],
            // Not `required`: blank means "keep the stored one", which is how an owner fixes a
            // merchant id without retyping a secret they cannot read back.
            'payfast_merchant_key' => ['nullable', 'string', 'max:64'],
            'payfast_passphrase' => ['nullable', 'string', 'max:100'],
            'payfast_sandbox' => ['nullable', 'boolean'],
            'payfast_payment_types' => ['nullable', 'array'],
            'payfast_payment_types.*' => ['string', 'in:'.implode(',', array_keys($this->paymentTypes()))],
        ];
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

        $encodedSaleId = UrlUtils::encodeId($sale->id);

        $fields = [
            'merchant_id' => $owner->payfast_merchant_id,
            'merchant_key' => $owner->payfast_merchant_key,
            // The sale secret rides along on the buyer-facing callbacks: the id alone is a Sqid and
            // proves nothing, and PaymentGatewayController::resolve() refuses both without it. Not on
            // notify_url, which Payfast authenticates by signature instead and which must not carry a
            // ticket token through a third party's logs.
            'return_url' => route('payments.return', [
                'gateway' => $this->key(),
                'sale_id' => $encodedSaleId,
                'secret' => $sale->secret,
            ]),
            'cancel_url' => route('payments.cancel', [
                'gateway' => $this->key(),
                'sale_id' => $encodedSaleId,
                'secret' => $sale->secret,
            ]),
            'notify_url' => route('payments.webhook', ['gateway' => $this->key(), 'sale_id' => $encodedSaleId]),
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

        // Signed with the passphrase, which is then discarded. It must never reach the form: it is the
        // shared secret that makes an ITN signature meaningful, and the buyer's browser is not a place
        // to keep it.
        $signature = PayfastSignature::sign($fields, $owner->payfast_passphrase);

        return response()->view('payments.payfast.redirect', [
            'action' => (new PayfastClient((bool) $owner->payfast_sandbox))->processUrl(),
            'fields' => $fields,
            'signature' => $signature,
            'event' => $event,
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

        if (! $client->isValidSourceIp($request->ip())) {
            Log::warning('Payfast ITN from an unrecognised source address', [
                'sale_id' => $sale->id,
                'ip' => $request->ip(),
            ]);

            return response('invalid source', 403);
        }

        if (! $client->confirmsPayment($payload)) {
            Log::warning('Payfast did not confirm the ITN payload', ['sale_id' => $sale->id]);

            return response('not confirmed', 400);
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

        Log::info('Payfast ITN settled', ['sale_id' => $sale->id, 'outcome' => $outcome]);

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
