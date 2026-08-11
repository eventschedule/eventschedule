<?php

namespace App\Http\Controllers;

use App\Models\AnalyticsEventsDaily;
use App\Models\GiftCard;
use App\Models\Sale;
use App\Models\SaleInstallment;
use App\Models\SaleInstallmentPlan;
use App\Services\AuditService;
use App\Services\EmailService;
use App\Services\MetaAdsService;
use App\Services\UsageTrackingService;
use App\Services\WebhookService;
use App\Utils\MoneyUtils;
use App\Utils\UrlUtils;
use Illuminate\Http\Request;
use Stripe\Account;
use Stripe\AccountLink;
use Stripe\Stripe;

class StripeController extends Controller
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.key'));
    }

    public function link()
    {
        $user = auth()->user();
        $accountId = $user->stripe_account_id;

        if (! $accountId) {
            $account = Account::create();
            $user->stripe_account_id = $account->id;
            $user->save();

            $accountId = $account->id;

            UsageTrackingService::track(UsageTrackingService::STRIPE_ACCOUNT);
        }

        $link = AccountLink::create([
            'account' => $accountId,
            'return_url' => route('stripe.complete'),
            'refresh_url' => route('profile.edit').'#section-payment-methods',
            'type' => 'account_onboarding',
        ]);

        AuditService::log(AuditService::STRIPE_LINK, $user->id);

        return redirect($link->url);
    }

    public function unlink()
    {
        $user = auth()->user();
        $user->stripe_account_id = null;
        $user->stripe_completed_at = null;
        $user->save();

        AuditService::log(AuditService::STRIPE_UNLINK, $user->id);

        return redirect()->to(route('profile.edit').'#section-payment-methods')->with('message', __('messages.stripe_unlinked'));
    }

    public function complete()
    {
        $user = auth()->user();

        if ($user->stripe_account_id) {
            $account = Account::retrieve($user->stripe_account_id);

            if ($account->charges_enabled) {
                $user->stripe_company_name = $account->business_profile->name;
                $user->stripe_completed_at = now();
                $user->save();

                return redirect()->to(route('profile.edit').'#section-payment-methods')->with('message', __('messages.stripe_connected'));
            }
        }

        return redirect()->to(route('profile.edit').'#section-payment-methods')->with('error', __('messages.failed_to_connect_stripe'));
    }

    public function webhook(Request $request)
    {
        $payload = $request->getContent();
        $sig_header = $request->header('stripe-signature');
        $event = null;
        $verifiedViaConnect = false;

        // Try Connect webhook secret first (hosted mode)
        $connectSecret = config('services.stripe.webhook_secret');
        if ($connectSecret) {
            try {
                $event = \Stripe\Webhook::constructEvent(
                    $payload, $sig_header, $connectSecret
                );
                $verifiedViaConnect = true;
            } catch (\Stripe\Exception\SignatureVerificationException $e) {
                // Connect secret didn't work, will try platform secret
            } catch (\UnexpectedValueException $e) {
                return response()->json(['error' => 'Invalid payload'], 400);
            }
        }

        // Try platform webhook secret (selfhosted mode / direct payments)
        if (! $event) {
            $platformSecret = config('services.stripe_platform.webhook_secret');
            if ($platformSecret) {
                try {
                    $event = \Stripe\Webhook::constructEvent(
                        $payload, $sig_header, $platformSecret
                    );
                } catch (\Stripe\Exception\SignatureVerificationException $e) {
                    return response()->json(['error' => 'Invalid signature'], 400);
                } catch (\UnexpectedValueException $e) {
                    return response()->json(['error' => 'Invalid payload'], 400);
                }
            } else {
                return response()->json(['error' => 'Invalid signature'], 400);
            }
        }

        switch ($event->type) {
            case 'payment_intent.succeeded':
                // Stripe Connect payments (hosted mode)
                $paymentIntent = $event->data->object;

                // Installments branch FIRST, before the sale lookup below.
                //
                // Not merely before the amount reconciliation: TicketController::success() stamps
                // installment 1's PaymentIntent onto sales.transaction_reference, so the lookup
                // on the next line WILL match a sale, and the reconciliation lives inside that
                // `if ($sale)`. Anything placed in there is already fighting committed code, and
                // a 1-of-4 payment would be reconciled against the full order total and land the
                // sale in amount_mismatch - killing a valid ticket.
                if (isset($paymentIntent->metadata->installment_id)
                    || isset($paymentIntent->metadata->installment_plan_payoff)) {
                    $this->handleInstallmentPayment(
                        $paymentIntent->metadata,
                        $paymentIntent->amount,
                        $paymentIntent->currency ?? null,
                        $paymentIntent->id,
                        $verifiedViaConnect,
                        $event->account ?? null,
                        $paymentIntent,
                    );
                    break;
                }

                $sale = Sale::where('payment_method', 'stripe')
                    ->where('transaction_reference', $paymentIntent->id)
                    ->first();

                // Fallback: find by sale_id in payment intent metadata (when success page wasn't reached)
                if (! $sale && isset($paymentIntent->metadata->sale_id)) {
                    $saleId = UrlUtils::decodeId($paymentIntent->metadata->sale_id);
                    $sale = Sale::where('payment_method', 'stripe')->find($saleId);
                }

                if ($sale) {
                    // Verify webhook key matches payment context to prevent cross-context forgery
                    $isConnectSale = $sale->event && $sale->event->user && $sale->event->user->stripe_account_id;
                    if (! $verifiedViaConnect && $isConnectSale) {
                        \Log::warning('Stripe webhook key mismatch: platform key used for Connect sale', [
                            'sale_id' => $sale->id,
                            'payment_intent_id' => $paymentIntent->id,
                        ]);
                        break;
                    }

                    $didTransitionToPaid = false;

                    // Use lockForUpdate to prevent race with the success redirect handler
                    \DB::transaction(function () use ($sale, $paymentIntent, &$didTransitionToPaid) {
                        $sale = Sale::lockForUpdate()->find($sale->id);
                        if ($sale->status === 'paid') {
                            return;
                        }

                        // A released sale must never be revived. Expiry already gave the seats back and
                        // restored any gift-card balance, and marking paid does not re-take them - so
                        // flipping expired -> paid oversells the event and double-spends the card. A
                        // multi-event order widens the window: one leg's expiry window can elapse while
                        // the order's single Stripe session is still open.
                        if (in_array($sale->status, ['expired', 'cancelled', 'refunded'], true)) {
                            \Log::warning('Stripe webhook for a released sale - not marking paid', [
                                'sale_id' => $sale->id,
                                'status' => $sale->status,
                            ]);

                            return;
                        }

                        $currencyCode = $sale->event?->ticket_currency_code ?? 'USD';
                        $webhookAmount = $paymentIntent->amount / MoneyUtils::getSmallestUnitMultiplier($currencyCode);

                        // For grouped purchases (individual tickets) the buyer pays the group total in one charge.
                        $expectedAmount = $sale->isOrderPrimary()
                            ? $sale->orderTotalPayment()
                            : $sale->legTotalPayment();
                        $amountDifference = abs($webhookAmount - $expectedAmount);

                        // Allow small tolerance for floating point/rounding differences
                        if ($amountDifference > 0.01) {
                            \Log::error('Payment amount mismatch in Stripe webhook - sale NOT marked as paid', [
                                'sale_id' => $sale->id,
                                'expected_amount' => $expectedAmount,
                                'webhook_amount' => $webhookAmount,
                                'difference' => $amountDifference,
                                'payment_intent_id' => $paymentIntent->id,
                            ]);

                            $sale->status = 'amount_mismatch';
                            $sale->transaction_reference = $paymentIntent->id;
                            $sale->save();

                            AuditService::log(AuditService::SALE_PAID, $sale->user_id, 'Sale', $sale->id,
                                ['status' => 'unpaid'], ['status' => 'amount_mismatch'], 'stripe_amount_mismatch:event_id:'.$sale->event_id);

                            return;
                        }

                        // Preserve per-seat payment_amount on grouped primaries; only overwrite for
                        // ungrouped sales. An ORDER primary is excluded for the same reason:
                        // $webhookAmount is the WHOLE order's total here, so writing it onto the
                        // one leg that anchors the order would count every other leg twice in
                        // orderTotalPayment(), the AP sales table and the revenue reports.
                        if (! $sale->isPrimarySale() && ! $sale->isOrderPrimary()) {
                            $sale->payment_amount = $webhookAmount;
                        }
                        $sale->status = 'paid';
                        $sale->transaction_reference = $paymentIntent->id;
                        $sale->save();
                        $didTransitionToPaid = true;

                        AuditService::log(AuditService::SALE_PAID, $sale->user_id, 'Sale', $sale->id,
                            ['status' => 'unpaid'], ['status' => 'paid'], 'stripe:event_id:'.$sale->event_id);

                        // Analytics, the Meta conversion and the sale.paid deliveries are each
                        // attributed to ONE event, so a payment spanning several is posted leg by
                        // leg. Posting $webhookAmount against $sale->event_id credits the anchoring
                        // leg's event with the entire order and the rest with nothing - and it
                        // never washes out, because the decrement side in
                        // HandlesSaleStatusActions works per leg. orderLegs() is just [$sale] for
                        // an ordinary sale, so nothing changes off the cart path.
                        foreach ($sale->orderLegs() as $leg) {
                            // A leg released before the payment landed keeps its own status - the
                            // paid cascade deliberately skips cancelled/refunded/expired rows - so
                            // it earns its event nothing and gets no delivery. Read after the save,
                            // so these statuses are the post-cascade ones.
                            if ($leg->status !== 'paid') {
                                continue;
                            }

                            $legTotal = $leg->legTotalPayment();

                            AnalyticsEventsDaily::incrementSale($leg->event_id, $legTotal);
                            $promoTotal = $leg->legTotalDiscount();
                            if ($promoTotal > 0) {
                                AnalyticsEventsDaily::incrementPromoSale($leg->event_id, $promoTotal);
                            }

                            // Send conversion event to Meta CAPI if event has active boost
                            $this->sendMetaConversion($leg, $legTotal);

                            WebhookService::dispatch('sale.paid', $leg);
                            foreach ($leg->guestSales()->get() as $gs) {
                                WebhookService::dispatch('sale.paid', $gs);
                            }
                        }

                        UsageTrackingService::track(UsageTrackingService::STRIPE_PAYMENT);
                    });

                    if ($didTransitionToPaid) {
                        (new EmailService)->sendSaleConfirmationEmails($sale->refresh());
                    }
                } elseif (isset($paymentIntent->metadata->gift_card_id)) {
                    $giftCard = GiftCard::find(UrlUtils::decodeId($paymentIntent->metadata->gift_card_id));
                    if ($giftCard) {
                        $this->handleGiftCardPayment(
                            $giftCard,
                            $paymentIntent->amount,
                            $paymentIntent->currency ?? null,
                            $paymentIntent->id,
                            $verifiedViaConnect,
                            $event->account ?? null
                        );
                    }
                }
                break;

            case 'checkout.session.completed':
                // Direct Stripe payments (selfhosted mode)
                $session = $event->data->object;

                // The buyer replaced the card on their plan. A setup-mode session is never
                // `payment_status === 'paid'`, so it matched no branch here at all and the whole
                // "update card" flow was a no-op: the buyer saw Stripe's success page and our
                // confirmation banner while the cron carried on charging the dead card until the
                // plan went delinquent and their ticket was refused at the door.
                if (isset($session->metadata->installment_plan_card_update)) {
                    $this->handleInstallmentCardUpdate(
                        $session,
                        $verifiedViaConnect,
                        $event->account ?? null,
                    );
                    break;
                }

                // The selfhost / platform rail settles here, not on payment_intent.succeeded, and
                // the block below reconciles session->amount_total against the FULL order total.
                // Without this branch every selfhosted installment purchase would be flagged
                // amount_mismatch, which is exactly the failure the design exists to avoid.
                if ($session->payment_status === 'paid'
                    && (isset($session->metadata->installment_id)
                        || isset($session->metadata->installment_plan_payoff))) {
                    $this->handleInstallmentPayment(
                        $session->metadata,
                        $session->amount_total,
                        $session->currency ?? null,
                        $session->payment_intent ?? $session->id,
                        $verifiedViaConnect,
                        $event->account ?? null,
                    );
                    break;
                }

                if ($session->payment_status === 'paid' && isset($session->metadata->sale_id)) {
                    $saleId = UrlUtils::decodeId($session->metadata->sale_id);
                    $sale = Sale::find($saleId);
                    if ($sale) {
                        // Verify webhook key matches: checkout.session.completed is for direct/platform payments
                        $isConnectSale = $sale->event && $sale->event->user && $sale->event->user->stripe_account_id;
                        if ($verifiedViaConnect && $isConnectSale) {
                            // Connect sales should use payment_intent.succeeded, not checkout.session.completed
                            \Log::warning('Stripe webhook: checkout.session.completed received for Connect sale', [
                                'sale_id' => $sale->id,
                                'session_id' => $session->id,
                            ]);
                            break;
                        }

                        $didTransitionToPaid = false;

                        // Use lockForUpdate to prevent race with the success redirect handler
                        \DB::transaction(function () use ($sale, $session, &$didTransitionToPaid) {
                            $sale = Sale::lockForUpdate()->find($sale->id);
                            if ($sale->status === 'paid') {
                                return;
                            }

                            // A released sale must never be revived. Expiry already gave the seats back and
                            // restored any gift-card balance, and marking paid does not re-take them - so
                            // flipping expired -> paid oversells the event and double-spends the card. A
                            // multi-event order widens the window: one leg's expiry window can elapse while
                            // the order's single Stripe session is still open.
                            if (in_array($sale->status, ['expired', 'cancelled', 'refunded'], true)) {
                                \Log::warning('Stripe webhook for a released sale - not marking paid', [
                                    'sale_id' => $sale->id,
                                    'status' => $sale->status,
                                ]);

                                return;
                            }

                            $currencyCode = $sale->event?->ticket_currency_code ?? 'USD';
                            $webhookAmount = $session->amount_total / MoneyUtils::getSmallestUnitMultiplier($currencyCode);

                            // For grouped purchases (individual tickets) the buyer pays the group total in one charge.
                            $expectedAmount = $sale->isOrderPrimary()
                            ? $sale->orderTotalPayment()
                            : $sale->legTotalPayment();
                            $amountDifference = abs($webhookAmount - $expectedAmount);

                            // Allow small tolerance for floating point/rounding differences
                            if ($amountDifference > 0.01) {
                                \Log::error('Payment amount mismatch in Stripe checkout webhook - sale NOT marked as paid', [
                                    'sale_id' => $sale->id,
                                    'expected_amount' => $expectedAmount,
                                    'webhook_amount' => $webhookAmount,
                                    'difference' => $amountDifference,
                                    'session_id' => $session->id,
                                ]);

                                $sale->status = 'amount_mismatch';
                                $sale->transaction_reference = $session->payment_intent;
                                $sale->save();

                                AuditService::log(AuditService::SALE_PAID, $sale->user_id, 'Sale', $sale->id,
                                    ['status' => 'unpaid'], ['status' => 'amount_mismatch'], 'stripe_checkout_amount_mismatch:event_id:'.$sale->event_id);

                                return;
                            }

                            // Preserve per-seat payment_amount on grouped primaries; only overwrite
                            // for ungrouped sales. An ORDER primary is excluded for the same
                            // reason: $webhookAmount is the WHOLE order's total here, so writing it
                            // onto the one leg that anchors the order would count every other leg
                            // twice in orderTotalPayment(), the AP sales table and the reports.
                            if (! $sale->isPrimarySale() && ! $sale->isOrderPrimary()) {
                                $sale->payment_amount = $webhookAmount;
                            }
                            $sale->status = 'paid';
                            $sale->transaction_reference = $session->payment_intent;
                            $sale->save();
                            $didTransitionToPaid = true;

                            AuditService::log(AuditService::SALE_PAID, $sale->user_id, 'Sale', $sale->id,
                                ['status' => 'unpaid'], ['status' => 'paid'], 'stripe_checkout:event_id:'.$sale->event_id);

                            UsageTrackingService::track(UsageTrackingService::STRIPE_PAYMENT);

                            // Record the sale in analytics leg by leg - see the matching comment in
                            // the payment_intent.succeeded branch above. Each of these side effects
                            // belongs to one event, and orderLegs() is just [$sale] unless this
                            // payment covered several.
                            foreach ($sale->orderLegs() as $leg) {
                                // Released legs are skipped by the paid cascade, so they earn
                                // nothing here either - see the matching guard above.
                                if ($leg->status !== 'paid') {
                                    continue;
                                }

                                $legTotal = $leg->legTotalPayment();

                                AnalyticsEventsDaily::incrementSale($leg->event_id, $legTotal);
                                $promoTotal = $leg->legTotalDiscount();
                                if ($promoTotal > 0) {
                                    AnalyticsEventsDaily::incrementPromoSale($leg->event_id, $promoTotal);
                                }

                                // Send conversion event to Meta CAPI if event has active boost
                                $this->sendMetaConversion($leg, $legTotal);

                                WebhookService::dispatch('sale.paid', $leg);
                                foreach ($leg->guestSales()->get() as $gs) {
                                    WebhookService::dispatch('sale.paid', $gs);
                                }
                            }
                        });

                        if ($didTransitionToPaid) {
                            (new EmailService)->sendSaleConfirmationEmails($sale->refresh());
                        }
                    }
                } elseif ($session->payment_status === 'paid' && isset($session->metadata->gift_card_id)) {
                    $giftCard = GiftCard::find(UrlUtils::decodeId($session->metadata->gift_card_id));
                    if ($giftCard) {
                        $this->handleGiftCardPayment(
                            $giftCard,
                            $session->amount_total,
                            $session->currency ?? null,
                            $session->payment_intent,
                            $verifiedViaConnect,
                            $event->account ?? null
                        );
                    }
                }
                break;

            default:
                break;
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * Activate a gift card once its Stripe payment settles. Locked and idempotent.
     *
     * Verifying WHICH connected account paid is load-bearing: the Connect webhook
     * secret only proves the event came from SOME connected account, so without the
     * account match a malicious user could pay themselves on their own Connect
     * account with a victim's gift_card_id in metadata and mint balance the victim
     * would owe. The payload currency is verified for the same reason.
     */
    private function handleGiftCardPayment(GiftCard $giftCard, $rawAmount, ?string $payloadCurrency, ?string $reference, bool $verifiedViaConnect, ?string $eventAccount): void
    {
        $merchantAccount = $giftCard->role?->user?->stripe_account_id;

        if ($merchantAccount && ! $verifiedViaConnect) {
            \Log::warning('Stripe webhook key mismatch: platform key used for Connect gift card', [
                'gift_card_id' => $giftCard->id,
                'reference' => $reference,
            ]);

            return;
        }

        if ($verifiedViaConnect && (! $merchantAccount || $eventAccount !== $merchantAccount)) {
            \Log::error('Stripe gift card webhook: connected account mismatch - card NOT activated', [
                'gift_card_id' => $giftCard->id,
                'event_account' => $eventAccount,
                'reference' => $reference,
            ]);

            return;
        }

        $didActivate = false;

        \DB::transaction(function () use ($giftCard, $rawAmount, $payloadCurrency, $reference, &$didActivate) {
            $giftCard = GiftCard::lockForUpdate()->find($giftCard->id);
            if ($giftCard->status !== 'unpaid') {
                return;
            }

            $webhookAmount = $rawAmount / MoneyUtils::getSmallestUnitMultiplier($giftCard->currency_code);
            $currencyMatches = ! $payloadCurrency || strcasecmp($payloadCurrency, $giftCard->currency_code) === 0;

            if (! $currencyMatches || abs($webhookAmount - (float) $giftCard->amount) > 0.01) {
                \Log::error('Payment mismatch in Stripe gift card webhook - card NOT activated', [
                    'gift_card_id' => $giftCard->id,
                    'expected_amount' => (float) $giftCard->amount,
                    'webhook_amount' => $webhookAmount,
                    'expected_currency' => $giftCard->currency_code,
                    'webhook_currency' => $payloadCurrency,
                    'reference' => $reference,
                ]);

                $giftCard->status = 'amount_mismatch';
                $giftCard->transaction_reference = $reference;
                $giftCard->save();

                AuditService::log(AuditService::GIFT_CARD_PAID, null, 'GiftCard', $giftCard->id,
                    ['status' => 'unpaid'], ['status' => 'amount_mismatch'], 'stripe_amount_mismatch:role_id:'.$giftCard->role_id);

                return;
            }

            $giftCard->activate($reference);
            $didActivate = true;

            AuditService::log(AuditService::GIFT_CARD_PAID, null, 'GiftCard', $giftCard->id,
                ['status' => 'unpaid'], ['status' => 'active'], 'stripe:role_id:'.$giftCard->role_id);

            UsageTrackingService::track(UsageTrackingService::STRIPE_PAYMENT);
        });

        if ($didActivate) {
            (new EmailService)->sendGiftCardEmails($giftCard->refresh());
        }
    }

    /**
     * Settle an installment payment, or a payoff of the whole remaining balance.
     *
     * Mirrors handleGiftCardPayment()'s security properties, because the same attack applies: the
     * Connect webhook secret only proves the event came from SOME connected account, so without
     * matching the account a hostile user could pay themselves on their own Connect account with
     * a victim's installment_id in metadata and have the victim's balance credited.
     *
     * Never writes sales.status = 'amount_mismatch'. That status feeds the operator's /admin
     * review queue, and flipping a sale that is already 3/4 paid out of `paid` would kill the
     * buyer's QR at the door and let approveSale() re-credit analytics with the whole order total
     * that installment 1 already booked. A bad installment amount is recorded on the installment
     * row and surfaces on the organizer's Installments tab instead.
     *
     * @param  mixed  $metadata  Stripe metadata object from the PaymentIntent or Session
     * @param  mixed  $paymentIntentObject  Full PaymentIntent, when available, for card details
     */
    private function handleInstallmentPayment(
        $metadata,
        $rawAmount,
        ?string $payloadCurrency,
        ?string $reference,
        bool $verifiedViaConnect,
        ?string $eventAccount,
        $paymentIntentObject = null,
    ): void {
        $isPayoff = isset($metadata->installment_plan_payoff);

        $plan = null;
        $installment = null;

        if ($isPayoff) {
            $planId = UrlUtils::decodeId($metadata->installment_plan_payoff);
            $plan = SaleInstallmentPlan::find($planId);
        } else {
            // Ids reaching the app from outside are always encoded (CLAUDE.md), and this one is
            // attacker-supplied in the sense that it round-trips through Stripe metadata.
            $installmentId = UrlUtils::decodeId($metadata->installment_id);
            $installment = SaleInstallment::find($installmentId);
            $plan = $installment?->plan;
        }

        if (! $plan) {
            \Log::warning('Stripe installment webhook: plan not found', ['reference' => $reference]);

            return;
        }

        $merchantAccount = $plan->stripe_account_id;

        // Direction 1: a Connect merchant's object confirmed by the PLATFORM key. Both secrets are
        // configured in hosted mode, so this direction is live and must be rejected too - the
        // gift-card handler learned this the hard way.
        if ($merchantAccount && ! $verifiedViaConnect) {
            \Log::warning('Stripe webhook key mismatch: platform key used for Connect installment', [
                'plan_id' => $plan->id,
                'reference' => $reference,
            ]);

            return;
        }

        // Direction 2: a Connect-verified event whose account is not this plan's merchant.
        if ($verifiedViaConnect && (! $merchantAccount || $eventAccount !== $merchantAccount)) {
            \Log::error('Stripe installment webhook: connected account mismatch - NOT credited', [
                'plan_id' => $plan->id,
                'event_account' => $eventAccount,
                'reference' => $reference,
            ]);

            return;
        }

        if ($payloadCurrency && strcasecmp($payloadCurrency, $plan->currency) !== 0) {
            \Log::error('Stripe installment webhook: currency mismatch - NOT credited', [
                'plan_id' => $plan->id,
                'expected_currency' => $plan->currency,
                'webhook_currency' => $payloadCurrency,
                'reference' => $reference,
            ]);

            return;
        }

        $paidAmount = $rawAmount / MoneyUtils::getSmallestUnitMultiplier($plan->currency);

        // Everything above is authentication; the money itself is settled by the one shared
        // implementation, which app:charge-installments also calls so a missing or delayed webhook
        // can no longer leave a charged card unrecorded.
        $payoffIds = null;
        if ($isPayoff && isset($metadata->installment_ids)) {
            $payoffIds = array_filter(array_map(
                fn ($id) => UrlUtils::decodeId($id),
                explode(',', (string) $metadata->installment_ids)
            ));
        }

        app(\App\Services\InstallmentService::class)->settle(
            $plan,
            $installment,
            $isPayoff,
            $paidAmount,
            $reference,
            $paymentIntentObject,
            $payoffIds,
        );
    }

    /**
     * Swap the card a plan's future installments are charged to.
     *
     * Same account guards as a payment: the SetupIntent was created on the organizer's connected
     * account, so an event confirmed by a different account has no business rewriting this plan's
     * payment method.
     */
    private function handleInstallmentCardUpdate($session, bool $verifiedViaConnect, ?string $eventAccount): void
    {
        $plan = SaleInstallmentPlan::find(UrlUtils::decodeId($session->metadata->installment_plan_card_update));

        if (! $plan) {
            \Log::warning('Stripe card-update webhook: plan not found', ['session' => $session->id ?? null]);

            return;
        }

        $merchantAccount = $plan->stripe_account_id;

        if ($merchantAccount && ! $verifiedViaConnect) {
            \Log::warning('Stripe webhook key mismatch: platform key used for a Connect card update', [
                'plan_id' => $plan->id,
            ]);

            return;
        }

        if ($verifiedViaConnect && (! $merchantAccount || $eventAccount !== $merchantAccount)) {
            \Log::error('Stripe card-update webhook: connected account mismatch - NOT applied', [
                'plan_id' => $plan->id,
                'event_account' => $eventAccount,
            ]);

            return;
        }

        try {
            [$stripe, $options] = app(\App\Services\InstallmentService::class)->stripeContextFor($plan);
            $setupIntent = $stripe->setupIntents->retrieve($session->setup_intent, [], $options);

            if (($setupIntent->status ?? null) !== 'succeeded' || ! $setupIntent->payment_method) {
                return;
            }

            $paymentMethod = $stripe->paymentMethods->retrieve($setupIntent->payment_method, [], $options);

            $plan->forceFill([
                'stripe_customer_id' => $setupIntent->customer ?: $plan->stripe_customer_id,
                'stripe_payment_method_id' => $setupIntent->payment_method,
                'card_brand' => $paymentMethod->card->brand ?? $plan->card_brand,
                'card_last4' => $paymentMethod->card->last4 ?? $plan->card_last4,
                'card_exp_month' => $paymentMethod->card->exp_month ?? $plan->card_exp_month,
                'card_exp_year' => $paymentMethod->card->exp_year ?? $plan->card_exp_year,
            ])->save();

            // A new card is exactly the remedy for the state that parked these rows, so put them
            // back in the ladder rather than leaving the buyer stuck after doing what we asked.
            SaleInstallment::where('sale_installment_plan_id', $plan->id)
                ->whereIn('status', ['awaiting_customer', 'failed'])
                ->update(['status' => 'scheduled', 'attempts' => 0, 'next_attempt_at' => null, 'last_error' => null]);

            if ($plan->status === 'delinquent') {
                $plan->forceFill(['status' => 'active', 'delinquent_at' => null])->save();
            }
        } catch (\Exception $e) {
            report($e);
            \Log::error('Could not apply an installment card update', [
                'plan_id' => $plan->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function sendMetaConversion(Sale $sale, float $amount): void
    {
        try {
            $event = $sale->event;
            if (! $event || ! $event->activeBoostCampaign) {
                return;
            }

            $metaService = app()->make(MetaAdsService::class);
            $metaService->sendConversionEvent('Purchase', [
                'event_id' => 'es_sale_'.$sale->id,
                'user_data' => [
                    'em' => [hash('sha256', strtolower(trim($sale->email)))],
                ],
                'custom_data' => [
                    'value' => $amount,
                    'currency' => $sale->event->ticket_currency_code ?? config('services.meta.default_currency', 'USD'),
                    'content_name' => $event->name,
                ],
            ]);
        } catch (\Exception $e) {
            \Log::warning('Failed to send Meta conversion event', [
                'sale_id' => $sale->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
