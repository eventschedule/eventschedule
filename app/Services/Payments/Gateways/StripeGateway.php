<?php

namespace App\Services\Payments\Gateways;

use App\Models\Sale;
use App\Models\User;
use App\Services\Payments\CheckoutContext;
use App\Services\Payments\PaymentGatewayDriver;
use App\Utils\MoneyUtils;
use App\Utils\UrlUtils;
use Stripe\StripeClient;
use Symfony\Component\HttpFoundation\Response;

/**
 * Stripe Checkout, on the schedule owner's behalf.
 *
 * Two rails behind one key, chosen by `config('app.hosted') && $owner->stripe_account_id`: hosted
 * installs use Stripe Connect against the platform key, selfhost installs use their own platform
 * keys directly. isConfiguredFor() is where that divergence lives, so nothing outside this class has
 * to know which rail an install is on.
 */
class StripeGateway extends PaymentGatewayDriver
{
    public function key(): string
    {
        return 'stripe';
    }

    public function label(?User $owner): string
    {
        // The connected account name only exists once onboarding finished. Before that the owner can
        // still select Stripe, so fall back to the bare product name rather than "Stripe - ".
        if ($owner?->stripe_completed_at && $owner->stripe_company_name) {
            return 'Stripe - '.$owner->stripe_company_name;
        }

        return 'Stripe';
    }

    public function isConfiguredFor(?User $owner): bool
    {
        return (bool) $owner?->canAcceptStripePayments();
    }

    /**
     * The only rail that can settle a whole multi-event order in one payment.
     */
    public function supportsCart(): bool
    {
        return true;
    }

    /**
     * The only rail that can charge a saved card off-session, which is what installments need.
     */
    public function supportsInstallments(): bool
    {
        return true;
    }

    /**
     * Stripe refuses a charge below roughly 50 smallest currency units. It matters well before the
     * gateway sees it: a gift card that covers all but a few cents of an order would leave an
     * unchargeable remainder, so the guest form has to know the floor to decide how much of the card
     * to apply.
     */
    public function amountLimits(string $currencyCode): array
    {
        return [50 / MoneyUtils::getSmallestUnitMultiplier($currencyCode), null];
    }

    public function referenceUrl(Sale $sale): ?string
    {
        if (! $sale->transaction_reference) {
            return null;
        }

        return 'https://dashboard.stripe.com/payments/'.$sale->transaction_reference;
    }

    /**
     * Custom UI: connecting is not a credentials form here.
     */
    public function settingsView(): ?string
    {
        return 'profile.partials.payments.stripe';
    }

    /**
     * Stripe Checkout. Moved here from TicketController, where it was one arm of a switch on a
     * payment-method string; the body is unchanged.
     */
    public function startCheckout(CheckoutContext $context): Response
    {
        return $this->stripeCheckout($context->subdomain, $context->sale, $context->event, $context->isEmbed);
    }

    private function stripeCheckout($subdomain, $sale, $event, $isEmbed = false)
    {
        // For a grouped (individual-tickets) primary the line items are aggregated across the
        // whole group, so the discount must be the group total too - using the primary's own
        // per-seat share here skews the ratio and, combined with gift-card line scaling, can
        // drive a reconciled unit_amount negative (Stripe rejects it). Mirrors how
        // $expectedTotal below uses the same scope.
        //
        // A multi-event order widens the scope again: one session pays for every leg, so the
        // line items and the expected total have to span the order, not the leg. The PROMO does
        // not: each leg carries its own code and its own discount_amount, so the pair is kept
        // per leg and matched to that leg's tickets by event - see promoRatiosByTicket().
        $isOrder = $sale->isOrderPrimary();

        $promoLegs = ($isOrder ? $sale->orderLegs() : collect([$sale]))
            ->map(fn (Sale $leg) => [
                'promo' => $leg->promo_code_id ? $leg->promoCode : null,
                'discount' => (float) $leg->legTotalDiscount(),
                'event_id' => (int) $leg->event_id,
            ])
            ->all();

        // Aggregate SaleTickets across the whole order, or the whole group.
        if ($isOrder || ($sale->group_id && $sale->isPrimarySale())) {
            // is_deleted matters on the order branch: $expectedTotal below is
            // orderTotalPayment(), which DOES filter deleted rows, and Sale carries no global
            // scope - so an unfiltered aggregation here bills for a leg the total does not
            // include. The reconciliation further down assumes the drift is per-unit rounding and
            // therefore absorbable one cent at a time; a whole extra leg breaks that assumption
            // and the session is created for the wrong amount.
            // The group branch is deliberately left alone: it has the same latent inconsistency
            // but predates the cart, so changing it here would alter a long-settled path.
            $allGroupSales = $isOrder
                ? Sale::where('order_id', $sale->order_id)->where('is_deleted', false)->with('saleTickets.ticket')->get()
                : Sale::where('group_id', $sale->group_id)->with('saleTickets.ticket')->get();
            $aggregatedTickets = [];
            foreach ($allGroupSales as $gs) {
                foreach ($gs->saleTickets as $st) {
                    $tid = $st->ticket_id;
                    if (isset($aggregatedTickets[$tid])) {
                        $aggregatedTickets[$tid]['quantity'] += $st->quantity;
                    } else {
                        $aggregatedTickets[$tid] = [
                            'ticket_id' => $tid,
                            'quantity' => $st->quantity,
                            'ticket' => $st->ticket,
                        ];
                    }
                }
            }
            $stripeSaleTickets = collect($aggregatedTickets)->map(function ($item) {
                $st = new \App\Models\SaleTicket(['ticket_id' => $item['ticket_id'], 'quantity' => $item['quantity']]);
                $st->setRelation('ticket', $item['ticket']);

                return $st;
            });
        } else {
            $stripeSaleTickets = $sale->saleTickets;
        }

        // Pre-bind the event so the pricing helpers below (lineSubtotalAfterVolumeDiscount(),
        // PromoCode::appliesToTicket()) do not lazy-load it once per ticket.
        //
        // The event_id guard is what makes this safe to aggregate over: today every row above
        // belongs to $event, so the guard changes nothing. Once a single payment can span events,
        // an unguarded bind would silently re-parent the other legs' tickets to this one and price
        // them against the wrong event's volume discounts and promo codes - a wrong total, with
        // nothing in the logs to say so.
        foreach ($stripeSaleTickets as $saleTicket) {
            if ($saleTicket->ticket && (int) $saleTicket->ticket->event_id === (int) $event->id) {
                $saleTicket->ticket->setRelation('event', $event);
            }
        }

        $giftTotal = $isOrder ? $sale->orderTotalGiftCard() : $sale->legTotalGiftCard();
        $expectedTotal = $isOrder ? $sale->orderTotalPayment() : $sale->legTotalPayment();

        // An installment order charges only the FIRST payment now, as a single line item.
        //
        // buildStripeLineItems() exists to spread promo and gift-card ratios across per-ticket
        // lines so the session total reconciles exactly; re-deriving that against a quarter of
        // the order would fight the very reconciliation it was built to satisfy, and a rounding
        // slip there drives a unit price negative and 500s the checkout. The itemised breakdown
        // lives on our own confirmation page and email instead.
        $plan = $sale->installmentPlan;

        if ($plan && $plan->status === 'active') {
            $first = $plan->installments()->where('sequence', 1)->first();

            if ($first) {
                $currency = $plan->currency;
                $lineItems = [[
                    'price_data' => [
                        'currency' => strtolower($currency),
                        'product_data' => [
                            'name' => $event->name.' - '.__('messages.installment_line_item', [
                                'number' => 1,
                                'count' => $plan->installment_count,
                            ]),
                        ],
                        'unit_amount' => (int) round((float) $first->amount * MoneyUtils::getSmallestUnitMultiplier($currency)),
                    ],
                    'quantity' => 1,
                ]];
            } else {
                $plan = null;
            }
        } else {
            $plan = null;
        }

        if (! $plan) {
            $lineItems = $this->buildStripeLineItems($stripeSaleTickets, $event, $promoLegs, $giftTotal, $expectedTotal);
        }

        $data = [
            'sale_id' => UrlUtils::encodeId($sale->id),
            'subdomain' => $subdomain,
            'date' => $sale->event_date,
        ];

        // Determine if using Stripe Connect (hosted mode) or direct payments (self-hosted)
        $useConnect = config('app.hosted') && $event->user->stripe_account_id;

        try {
            $session = $this->createStripeSession($useConnect, $lineItems, $sale, $event, $data, $isEmbed, $plan);
        } catch (\Exception $e) {
            // The sale rows, the seat holds and any promo times_used are already committed by
            // now, so an uncaught throw here answered a paid checkout with a 500 and left the
            // inventory decremented. Report it and hand the buyer their cart back instead.
            report($e);

            return back()->withInput()->with('error', __('messages.error'));
        }

        return redirect($session->url);
    }

    /**
     * Create the Stripe Checkout Session, on the event owner's Connect account when hosted or on
     * the platform account otherwise. Split out of stripeCheckout() only so the call has one
     * catchable seam; the two payloads are unchanged.
     */
    private function createStripeSession(bool $useConnect, array $lineItems, $sale, $event, array $data, bool $isEmbed, $plan = null)
    {
        // An installment session has to leave a REUSABLE payment method behind on the connected
        // account, or there is nothing for app:charge-installments to charge next month. Nothing
        // else in this app has ever needed that: every other ticket charge is one-shot and
        // on-session.
        //
        // setup_future_usage forces customer creation on its own; customer_creation is set
        // explicitly so the intent is legible. The mandate is also spelled out on Stripe's own
        // page via custom_text - belt and braces alongside the consent checkbox we collect
        // ourselves, which is the part we actually keep a record of.
        $installmentExtras = [];

        if ($plan) {
            $first = $plan->installments()->where('sequence', 1)->first();

            $installmentExtras = [
                'customer_creation' => 'always',
                'payment_intent_data' => [
                    'setup_future_usage' => 'off_session',
                    'metadata' => [
                        'sale_id' => UrlUtils::encodeId($sale->id),
                        'installment_id' => UrlUtils::encodeId($first->id),
                    ],
                ],
                'custom_text' => [
                    'submit' => [
                        'message' => __('messages.installments_stripe_mandate', [
                            'count' => $plan->installment_count - 1,
                            'amount' => MoneyUtils::format($first->amount, $plan->currency),
                        ]),
                    ],
                ],
            ];
        }

        if ($useConnect) {
            // Hosted mode: Use Stripe Connect with event creator's account
            $stripe = new StripeClient(config('services.stripe.key'));

            $session = $stripe->checkout->sessions->create(
                array_replace([
                    'line_items' => $lineItems,
                    'mode' => 'payment',
                    'customer_email' => $sale->email,
                    // Filtered on null only. A bare array_filter() drops every falsy value, so a
                    // buyer named "0" lost customer_name from the metadata on ordinary,
                    // non-installment checkouts too.
                    'metadata' => array_filter([
                        'customer_name' => $sale->name,
                        'sale_id' => UrlUtils::encodeId($sale->id),
                        'installment_id' => $plan ? UrlUtils::encodeId($plan->installments()->where('sequence', 1)->value('id')) : null,
                    ], fn ($v) => $v !== null),
                    'payment_intent_data' => [
                        'metadata' => [
                            'sale_id' => UrlUtils::encodeId($sale->id),
                        ],
                    ],
                    'success_url' => custom_domain_url(route('checkout.success', $data).(str_contains(route('checkout.success', $data), '?') ? '&' : '?').'session_id={CHECKOUT_SESSION_ID}'.($isEmbed ? '&embed=true' : '')),
                    'cancel_url' => custom_domain_url(route('checkout.cancel', $data).(str_contains(route('checkout.cancel', $data), '?') ? '&' : '?').'secret='.$sale->secret),
                ], $installmentExtras),
                [
                    'stripe_account' => $event->user->stripe_account_id,
                ],
            );
        } else {
            // Self-hosted mode: Use direct Stripe payments with platform keys
            $stripe = new StripeClient(config('services.stripe_platform.secret'));

            $session = $stripe->checkout->sessions->create(array_replace([
                'line_items' => $lineItems,
                'mode' => 'payment',
                'customer_email' => $sale->email,
                // The selfhost/platform rail settles on checkout.session.completed, so the
                // installment id has to be on the SESSION metadata too - the webhook branch there
                // reads it from here.
                'metadata' => array_filter([
                    'customer_name' => $sale->name,
                    'sale_id' => UrlUtils::encodeId($sale->id),
                    'installment_id' => $plan ? UrlUtils::encodeId($plan->installments()->where('sequence', 1)->value('id')) : null,
                ], fn ($v) => $v !== null),
                'payment_intent_data' => [
                    'metadata' => [
                        'sale_id' => UrlUtils::encodeId($sale->id),
                    ],
                ],
                'success_url' => custom_domain_url(route('checkout.success', $data).(str_contains(route('checkout.success', $data), '?') ? '&' : '?').'session_id={CHECKOUT_SESSION_ID}&direct=1'.($isEmbed ? '&embed=true' : '')),
                'cancel_url' => custom_domain_url(route('checkout.cancel', $data).(str_contains(route('checkout.cancel', $data), '?') ? '&' : '?').'secret='.$sale->secret),
            ], $installmentExtras));
        }

        return $session;
    }

    /**
     * Build the Stripe Checkout line items for an order. Applies the promo discount ratio to
     * eligible ticket lines, scales every line by the gift-card tender ratio (a gift card pays
     * for the whole order, add-ons included), then reconciles the rounding so the line-item sum
     * equals $expectedTotal (in the currency's smallest unit) exactly - absorbing the cents-level
     * diff into the largest line so no unit_amount ever goes negative (Stripe rejects negatives).
     * Extracted from stripeCheckout() so the money math is unit-testable in isolation.
     *
     * @param  iterable  $stripeSaleTickets  SaleTickets (group-aggregated for grouped primaries),
     *                                       each with its ticket relation and event set.
     * @return array<int, array> Stripe line_items
     */
    private function buildStripeLineItems($stripeSaleTickets, $event, array $promoLegs, float $giftTotal, float $expectedTotal): array
    {
        // Discount ratio per eligible ticket line (add-ons excluded); base is post-volume line
        // totals. Resolved PER LEG rather than once for the session: a cart is one Stripe session
        // spanning several events, each with its own promo code and its own discount_amount, so a
        // single ratio built from one leg's code and the whole order's discount either applies no
        // discount at all (the code sits on a leg that is not the anchor -> overcharge, then
        // amount_mismatch) or spends one leg's discount against another leg's eligible subtotal.
        $ratioByTicketId = $this->promoRatiosByTicket($stripeSaleTickets, $promoLegs);

        // A gift card is tender for the whole order (add-ons included), so scale every
        // line proportionally. Absorbing the deduction into one line could push its
        // unit_amount negative; the ratio keeps all lines valid, and the rounding
        // reconciliation below pins the session total to payment_amount exactly.
        $preGiftTotal = 0.0;
        if ($giftTotal > 0) {
            foreach ($stripeSaleTickets as $saleTicket) {
                $qty = (int) $saleTicket->quantity;
                if ($saleTicket->ticket->is_addon) {
                    $preGiftTotal += $saleTicket->ticket->price * $qty;
                } else {
                    $lineAmount = $saleTicket->ticket->lineSubtotalAfterVolumeDiscount($qty);
                    $lineAmount *= $ratioByTicketId[$saleTicket->ticket_id] ?? 1;
                    $preGiftTotal += $lineAmount;
                }
            }
        }
        $giftRatio = ($giftTotal > 0 && $preGiftTotal > 0)
            ? max(0, ($preGiftTotal - $giftTotal) / $preGiftTotal)
            : 1;

        $lineItems = [];
        $totalCents = 0;

        foreach ($stripeSaleTickets as $index => $saleTicket) {
            $qty = (int) $saleTicket->quantity;
            if ($saleTicket->ticket->is_addon) {
                $unitPrice = $saleTicket->ticket->price;
            } else {
                $linePostVolume = $saleTicket->ticket->lineSubtotalAfterVolumeDiscount($qty);
                $unitPrice = $qty > 0 ? $linePostVolume / $qty : 0;
                $unitPrice = $unitPrice * ($ratioByTicketId[$saleTicket->ticket_id] ?? 1);
            }
            if ($giftRatio < 1) {
                $unitPrice = $unitPrice * $giftRatio;
            }
            $unitAmountCents = (int) round($unitPrice * MoneyUtils::getSmallestUnitMultiplier($event->ticket_currency_code));
            $totalCents += $unitAmountCents * $saleTicket->quantity;

            $lineItems[] = [
                'price_data' => [
                    'currency' => $event->ticket_currency_code,
                    'product_data' => [
                        'name' => $saleTicket->ticket->type ?: ($saleTicket->ticket->is_addon ? __('messages.add_on') : __('messages.tickets')),
                        ...$saleTicket->ticket->description ? ['description' => $saleTicket->ticket->description] : [],
                    ],
                    'unit_amount' => $unitAmountCents,
                ],
                'quantity' => $saleTicket->quantity,
            ];
        }

        // Fix rounding difference to match payment_amount exactly (group total when primary, else own per-seat amount).
        // This runs whenever the summed line items drift from the expected total, whatever the cause:
        // gift-card / promo scaling OR plain per-unit rounding on a multi-quantity line (e.g. a fixed
        // volume discount that yields a fractional-cent unit price). The reconciliation considers every line.
        $expectedCents = (int) round($expectedTotal * MoneyUtils::getSmallestUnitMultiplier($event->ticket_currency_code));
        if ($totalCents !== $expectedCents) {
            // Spread the (cents-level) rounding diff ONE cent at a time across individual units so
            // the line-item sum equals $expectedCents exactly with no unit_amount going below zero.
            // A single-line adjustment cannot absorb a diff larger than that line's unit_amount
            // (e.g. many cheap units heavily gift-scaled), so distribute across units instead.
            // Adjusted units are split off into their own quantity-1 line items. Taking from the
            // priciest units first guarantees a -1 step never drives a unit negative; the diff is
            // always absorbable because it comes from per-unit rounding on multi-quantity lines,
            // so |diff| <= the count of >=1-cent units available to adjust.
            $step = ($expectedCents - $totalCents) > 0 ? 1 : -1;
            $need = abs($expectedCents - $totalCents);

            $order = array_keys($lineItems);
            usort($order, fn ($a, $b) => $lineItems[$b]['price_data']['unit_amount'] <=> $lineItems[$a]['price_data']['unit_amount']);

            foreach ($order as $idx) {
                if ($need <= 0) {
                    break;
                }
                $unit = $lineItems[$idx]['price_data']['unit_amount'];
                if ($step < 0 && $unit <= 0) {
                    continue; // cannot reduce a zero-cost unit below zero
                }
                $qty = $lineItems[$idx]['quantity'];
                $take = min($need, $qty);
                $adjustedUnit = $unit + $step; // magnitude 1; stays >= 0 by the guard above

                if ($take === $qty) {
                    $lineItems[$idx]['price_data']['unit_amount'] = $adjustedUnit;
                } else {
                    $lineItems[$idx]['quantity'] = $qty - $take;
                    $lineItems[] = [
                        'price_data' => [
                            'currency' => $lineItems[$idx]['price_data']['currency'],
                            'product_data' => $lineItems[$idx]['price_data']['product_data'],
                            'unit_amount' => $adjustedUnit,
                        ],
                        'quantity' => $take,
                    ];
                }
                $need -= $take;
            }
        }

        return $lineItems;
    }

    /**
     * The promo discount ratio to apply to each ticket line, keyed by ticket id.
     *
     * One entry per leg that actually carries a code, scoped by event: a leg's discount is only
     * ever spent against that same leg's eligible subtotal, so a code on one event can neither
     * be lost (because the anchor happened to carry no code) nor bleed into another event's
     * pricing. Tickets with no entry are billed at full post-volume price.
     *
     * The ratio is clamped at zero. It can only go negative when a leg's recorded
     * discount_amount exceeds the subtotal its code is actually eligible for - a data problem
     * rather than a pricing one - and a negative unit_amount is rejected by Stripe outright,
     * which used to surface as a 500 with the sale rows already committed.
     *
     * @param  array<int, array{promo: ?\App\Models\PromoCode, discount: float, event_id: int}>  $promoLegs
     * @return array<int, float> ticket_id => ratio
     */
    private function promoRatiosByTicket($stripeSaleTickets, array $promoLegs): array
    {
        $ratios = [];

        foreach ($this->promoLegsByEvent($promoLegs) as $leg) {
            $promo = $leg['promo'] ?? null;
            $discount = (float) ($leg['discount'] ?? 0);

            if (! $promo || $discount <= 0) {
                continue;
            }

            $eligible = [];
            $eligibleSubtotal = 0.0;

            foreach ($stripeSaleTickets as $saleTicket) {
                if ($saleTicket->ticket->is_addon
                    || (int) $saleTicket->ticket->event_id !== (int) $leg['event_id']
                    || ! $promo->appliesToTicket($saleTicket->ticket_id)) {
                    continue;
                }

                $eligible[] = $saleTicket->ticket_id;
                $eligibleSubtotal += $saleTicket->ticket->lineSubtotalAfterVolumeDiscount((int) $saleTicket->quantity);
            }

            if ($eligibleSubtotal <= 0) {
                continue;
            }

            $ratio = max(0, ($eligibleSubtotal - $discount) / $eligibleSubtotal);

            foreach ($eligible as $ticketId) {
                $ratios[$ticketId] = $ratio;
            }
        }

        return $ratios;
    }

    /**
     * One promo context per EVENT, with the discounts of every leg on that event summed.
     *
     * A cart holds one entry per event AND date, so two dates of a recurring event are two legs
     * of the same order carrying the same ticket ids - and the line items are aggregated by
     * ticket id, so there is only ever one ratio to apply to them. Keeping the legs separate had
     * the second silently overwrite the first's ratio and spend half the order's discount.
     *
     * Summing is the right merge: both legs resolve the SAME PromoCode row (priceSaleLeg looks it
     * up with `where('event_id', $event->id)`), and each records its own share in
     * discount_amount, which is what orderTotalPayment() - the figure the session must match -
     * already nets out.
     *
     * @param  array<int, array{promo: ?\App\Models\PromoCode, discount: float, event_id: int}>  $promoLegs
     * @return array<int, array{promo: ?\App\Models\PromoCode, discount: float, event_id: int}>
     */
    private function promoLegsByEvent(array $promoLegs): array
    {
        $merged = [];

        foreach ($promoLegs as $leg) {
            $eventId = (int) ($leg['event_id'] ?? 0);

            if (! isset($merged[$eventId])) {
                $merged[$eventId] = $leg;
                $merged[$eventId]['discount'] = (float) ($leg['discount'] ?? 0);

                continue;
            }

            $merged[$eventId]['discount'] += (float) ($leg['discount'] ?? 0);
            $merged[$eventId]['promo'] = $merged[$eventId]['promo'] ?? ($leg['promo'] ?? null);
        }

        return array_values($merged);
    }
}
