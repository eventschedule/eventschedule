<?php

namespace App\Services\Payments;

use App\Models\Event;
use App\Models\Sale;
use App\Models\User;
use App\Services\AuditService;
use App\Utils\UrlUtils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

/**
 * One payment gateway, as far as the rest of the app is concerned.
 *
 * An abstract class rather than a bare interface on purpose: nearly every question the app asks a
 * gateway has a sensible answer for a plain redirect-and-notify gateway, which is what most of them
 * are. A new driver declares its key, its label, whether it is configured and how a checkout starts,
 * and inherits the rest. That is the difference between adding a gateway being one class and being
 * the five-endpoint copy-paste job it used to be.
 *
 * The capability methods below exist because each one replaced a hardcoded payment-method string
 * somewhere in a blade, a query or a validation rule. When adding a capability, put it here rather
 * than reaching for another in_array() at the call site - the whole point is that call sites stop
 * naming gateways.
 */
abstract class PaymentGatewayDriver
{
    // ---------------------------------------------------------------- identity

    /**
     * The value stored in events.payment_method and sales.payment_method. Both are MySQL enums, so a
     * new key needs an ALTER on both before it can be selected or sold.
     */
    abstract public function key(): string;

    /**
     * What the owner sees in the event Payment dropdown, e.g. "Payfast - MyStore". Includes the
     * connected account name where the gateway knows it, because owners connect more than one
     * gateway and the bare product name is not enough to tell them apart.
     */
    abstract public function label(?User $owner): string;

    /**
     * Does this owner have working credentials?
     *
     * This is where hosted-versus-selfhost divergence belongs. Stripe is the example: hosted checks
     * the owner's completed Connect onboarding, selfhost checks whether the installation has
     * platform keys at all.
     */
    abstract public function isConfiguredFor(?User $owner): bool;

    // ------------------------------------------------------------ capabilities

    /**
     * False for the provenance markers. sales.payment_method also carries 'rsvp' and 'import', which
     * describe where a row came from rather than how it gets paid, and must never appear in the
     * event Payment dropdown or an API validation rule.
     */
    public function isSelectableForEvents(): bool
    {
        return true;
    }

    /**
     * Does this gateway have to be connected before it can be used?
     *
     * False only for cash, and the distinction is load-bearing. Cash is always selectable, but an
     * owner who has connected nothing must still see the "connect a gateway to get paid" nudge
     * rather than a dropdown whose only entry is cash - which is why the event form asks two
     * separate questions: what can I offer (includes cash) and has this owner set anything up
     * (does not).
     */
    public function needsCredentials(): bool
    {
        return true;
    }

    public function supportsCurrency(string $currencyCode): bool
    {
        return true;
    }

    /**
     * Gateway-imposed bounds on a single payment, in major units, as [min, max] with null for "no
     * bound". Enforced before the owner can pick the gateway and before a buyer is sent to it, so a
     * gateway with a floor (Payfast will not take under R5) fails as a setup error rather than as an
     * unexplained rejection on the gateway's own page.
     *
     * @return array{0: float|null, 1: float|null}
     */
    public function amountLimits(string $currencyCode): array
    {
        return [null, null];
    }

    /**
     * Can several events be bought in one payment?
     *
     * Only rails that settle a whole multi-event order in a single transaction qualify. Invoice
     * Ninja does not: its invoice and payment-link modes are both per event.
     */
    public function supportsCart(): bool
    {
        return false;
    }

    /**
     * Can a buyer come back to an unpaid sale and finish paying it? False for cash, which is settled
     * in person and has nothing for the buyer to return to.
     */
    public function canResumePayment(): bool
    {
        return true;
    }

    /**
     * Does paying navigate the buyer to a third-party page?
     *
     * Embedded ticket widgets need to know: a checkout form inside someone else's iframe has to
     * break out with target="_top" or the gateway's page renders in a widget-sized box, and many
     * gateways refuse to be framed at all. Deliberately separate from canResumePayment() even though
     * the two agree on every gateway today - an on-site modal would resume without redirecting.
     */
    public function redirectsOffsite(): bool
    {
        return true;
    }

    /**
     * Can this gateway charge a stored card later, unattended? Installments depend on it, which is
     * why they are Stripe-only today.
     */
    public function supportsInstallments(): bool
    {
        return false;
    }

    /**
     * Does the owner write their own instructions for how to pay, rather than the gateway collecting
     * the money? Cash only.
     */
    public function usesPaymentInstructions(): bool
    {
        return false;
    }

    /**
     * Should unpaid sales on this rail be auto-expired to give the seats back?
     *
     * False for cash: a cash order is settled at the door, sometimes long after the window, so
     * releasing it would cancel a sale the organizer is still expecting to collect. See
     * ReleaseTickets, which used to spell this out as `payment_method != 'cash'` in two queries.
     */
    public function expiresUnpaidSales(): bool
    {
        return true;
    }

    /**
     * The specific instruments this gateway can be told to offer, as code => translated label.
     *
     * Non-empty means the owner may narrow it - Payfast can be pinned to card, Instant EFT, Capitec
     * Pay and so on. Empty means the gateway decides, which is the common case.
     *
     * @return array<string, string>
     */
    public function paymentTypes(): array
    {
        return [];
    }

    // ----------------------------------------------------------- settings form

    /**
     * @return list<CredentialField>
     */
    public function credentialFields(): array
    {
        return [];
    }

    /**
     * Validation rules for the credentialFields() payload, keyed by field name.
     *
     * @return array<string, mixed>
     */
    public function credentialRules(): array
    {
        return [];
    }

    /**
     * An already-translated sentence shown above the credentials form, or null. Typically where to
     * find these values in the gateway's own dashboard.
     */
    public function credentialHelp(): ?string
    {
        return null;
    }

    /**
     * Persist validated credentials. The default writes each declared field straight onto the owner,
     * which is all a form-based gateway needs; encryption is handled by the model's casts rather
     * than here, so a driver never sees ciphertext.
     *
     * @param  array<string, mixed>  $input
     */
    public function saveCredentials(User $owner, array $input): void
    {
        foreach ($this->credentialFields() as $field) {
            if (! array_key_exists($field->name, $input)) {
                continue;
            }

            $value = $input[$field->name];

            // A blank secret means "leave it alone", so an owner can correct a merchant id without
            // retyping a key they cannot read back. Same sentinel the Invoice Ninja form uses.
            if ($field->isSecret() && ($value === null || $value === '')) {
                continue;
            }

            // Multi-selects arrive as an array of checked values and are stored as a comma list, with
            // empty meaning "no restriction" rather than "nothing allowed".
            if ($field->type === 'multiselect') {
                $value = implode(',', array_intersect(
                    array_map('strval', (array) $value),
                    array_keys($field->options),
                ));
            }

            if ($field->type === 'toggle') {
                $value = (bool) $value;
            }

            $owner->{$field->name} = $value;
        }

        $owner->save();
    }

    /**
     * Clear this gateway's credentials. The default nulls every declared field.
     */
    public function disconnect(User $owner): void
    {
        foreach ($this->credentialFields() as $field) {
            $owner->{$field->name} = $field->type === 'toggle' ? false : null;
        }

        $owner->save();
    }

    /**
     * A blade partial for gateways whose connect flow is not a form, or null to use the generic
     * credentialFields() tab. Stripe (OAuth) and Invoice Ninja (validate then register a webhook)
     * are the only two that need this.
     */
    public function settingsView(): ?string
    {
        return null;
    }

    // ---------------------------------------------------------------- checkout

    /**
     * Send the buyer to pay.
     *
     * Returns a Response rather than a RedirectResponse because not every gateway is reachable by a
     * 302: Payfast needs a signed POST, so its driver returns a rendered self-submitting form.
     *
     * The default collects nothing online and simply lands the buyer on their ticket, leaving the
     * sale unpaid for the owner to settle in person. That is cash's real behaviour, so CashGateway
     * needs no override; any gateway that actually takes money must provide one.
     */
    public function startCheckout(CheckoutContext $context): Response
    {
        return $this->redirectToPurchaseLanding($context->sale, $context->event, $context->isEmbed);
    }

    /**
     * Where the buyer lands on the way back from a successful payment.
     *
     * The default assumes the gateway's own callback is what settles the sale, which is the safe
     * assumption: a return URL is a browser redirect the buyer can tamper with, replay or simply
     * never reach. Drivers that also learn something authoritative here (Stripe reads the session to
     * stamp the reference) override it.
     */
    public function handleReturn(Request $request, Sale $sale): Response
    {
        return $this->redirectToPurchaseLanding($sale, $sale->event, $request->boolean('embed'));
    }

    /**
     * Buyer abandoned the payment: release the seats and put them back on the tickets page.
     */
    public function handleCancel(Request $request, Sale $sale): Response
    {
        $expired = DB::transaction(function () use ($sale) {
            $locked = Sale::lockForUpdate()->find($sale->id);
            if (! $locked || $locked->status !== 'unpaid') {
                return false;
            }

            $locked->status = 'expired';
            $locked->save();

            return true;
        });

        if ($expired) {
            AuditService::log(AuditService::SALE_EXPIRED, $sale->user_id, 'Sale', $sale->id,
                ['status' => 'unpaid'], ['status' => 'expired'],
                $this->key().'_abandon:event_id:'.$sale->event_id);
        }

        $event = $sale->event;

        $url = $event->getGuestUrl($sale->subdomain, $sale->event_date).'?tickets=true';

        // Same courtesy handleReturn() extends: an embedded buyer who cancels should land back in an
        // embedded page, not the full guest site.
        if ($request->boolean('embed')) {
            $url .= '&embed=true';
        }

        return redirect($url);
    }

    // -------------------------------------------------------------- settlement

    /**
     * The gateway calling us back to report a payment. $sale is null for account-level callbacks
     * that are not addressed to one sale, in which case the driver resolves it from the payload.
     *
     * Default is a 204 so an unimplemented gateway acknowledges rather than making the provider
     * retry forever.
     */
    public function handleWebhook(Request $request, ?Sale $sale): Response
    {
        return response()->noContent();
    }

    /**
     * Which owner an account-level callback belongs to, for gateways whose webhook is registered
     * once per merchant account rather than per sale. Compare shared secrets with hash_equals here,
     * never a plain string compare.
     */
    public function resolveOwnerFromWebhook(Request $request): ?User
    {
        return null;
    }

    // --------------------------------------------------------------- reporting

    /**
     * Deep link to this payment in the gateway's own dashboard, for the admin sales table, or null
     * when the gateway has no such page. The driver owns the format of its own
     * sales.transaction_reference, which is the column this reads.
     */
    public function referenceUrl(Sale $sale): ?string
    {
        return null;
    }

    // ----------------------------------------------------------------- helpers

    /**
     * Give back the seats of a sale the buyer can never complete.
     *
     * A driver that refuses a checkout AFTER TicketController committed the sale rows must call this,
     * or the inventory is held indefinitely: SaleTicket::created already incremented `sold`, the only
     * release is Sale::booted's status transition off `unpaid`, and ReleaseTickets sweeps by event
     * opt-in (events.expire_unpaid_tickets defaults to 0) so it will never come back on its own.
     */
    protected function releaseAbandonedSale(Sale $sale, string $reason): void
    {
        $released = DB::transaction(function () use ($sale) {
            $locked = Sale::lockForUpdate()->find($sale->id);

            if (! $locked || $locked->status !== 'unpaid') {
                return false;
            }

            // Sale::booted turns this into the seat and gift-card restore.
            $locked->status = 'expired';
            $locked->save();

            return true;
        });

        if ($released) {
            AuditService::log(AuditService::SALE_EXPIRED, $sale->user_id, 'Sale', $sale->id,
                ['status' => 'unpaid'], ['status' => 'expired'], $reason.':event_id:'.$sale->event_id);
        }
    }

    /**
     * Where a buyer lands after a purchase: the order page when the checkout covered several events,
     * that leg's own ticket otherwise.
     *
     * Every leg keeps its own ticket page and its own scannable QR, so the order page is the only
     * surface that says the other legs exist at all. Dropping a multi-event buyer on one ticket hides
     * the rest of what they just paid for.
     */
    protected function purchaseLandingUrl(Sale $sale, Event $event, bool $isEmbed = false): string
    {
        $url = $sale->isOrderPrimary()
            ? route('ticket.order', ['order_id' => UrlUtils::encodeId($sale->id), 'secret' => $sale->secret])
            : route('ticket.view', ['event_id' => UrlUtils::encodeId($event->id), 'secret' => $sale->secret]);

        return $isEmbed ? $url.'?embed=true' : $url;
    }

    /**
     * Redirect to the purchase landing page, telling the guest cart what was just bought.
     *
     * The cart lives in localStorage and is rendered only by the guest layout; both landing pages go
     * through x-app-layout, so the widget is not there to empty itself. Left alone, the cart still
     * holds the completed purchase on the buyer's next visit, with a live CHECKOUT button that would
     * charge them for it a second time.
     */
    protected function redirectToPurchaseLanding(Sale $sale, Event $event, bool $isEmbed = false): Response
    {
        session()->flash('cart_purchased', $sale->orderLegs()->map(fn (Sale $leg) => [
            'subdomain' => $leg->subdomain,
            'event_id' => UrlUtils::encodeId($leg->event_id),
            'event_date' => (string) $leg->event_date,
        ])->values()->all());

        return redirect($this->purchaseLandingUrl($sale, $event, $isEmbed));
    }
}
