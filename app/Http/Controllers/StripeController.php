<?php

namespace App\Http\Controllers;

use App\Models\GiftCard;
use App\Models\Sale;
use App\Models\SaleInstallment;
use App\Models\SaleInstallmentPlan;
use App\Services\AuditService;
use App\Services\EmailService;
use App\Services\SaleSettlementService;
use App\Services\UsageTrackingService;
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

                    // The amount arrives in Stripe's smallest unit; the settlement service reconciles
                    // in major units.
                    $currencyCode = $sale->event?->ticket_currency_code ?? 'USD';

                    app(SaleSettlementService::class)->settle(
                        $sale,
                        $paymentIntent->id,
                        $paymentIntent->amount / MoneyUtils::getSmallestUnitMultiplier($currencyCode),
                        'stripe',
                        UsageTrackingService::STRIPE_PAYMENT,
                    );
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

                        $currencyCode = $sale->event?->ticket_currency_code ?? 'USD';

                        // payment_intent can be null on a session, and the hand-written version wrote
                        // it through unconditionally - nulling out a reference the success redirect had
                        // already stamped. Passing null leaves the stored one intact instead.
                        app(SaleSettlementService::class)->settle(
                            $sale,
                            $session->payment_intent ?: null,
                            $session->amount_total / MoneyUtils::getSmallestUnitMultiplier($currencyCode),
                            'stripe_checkout',
                            UsageTrackingService::STRIPE_PAYMENT,
                        );
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

        // The session rail is handed a session, not an intent - `checkout.session.completed`
        // carries `payment_intent` as a bare id - so fetch what it was not given. Without this the
        // capture inside settle() has nothing to read on that rail and the plan is left with no
        // card, which is silent: chargeDue() skips it and both reminder sweeps filter it out.
        //
        // Deliberately AFTER the account and currency guards above, so a forged or misdirected
        // event cannot make us spend an API call. The `pi_` test matters because $reference falls
        // back to the session id when a session carries no intent.
        if (! $paymentIntentObject && is_string($reference) && str_starts_with($reference, 'pi_')) {
            $paymentIntentObject = app(\App\Services\InstallmentService::class)
                ->retrievePaymentIntent($plan, $reference);
        }

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

        if (! $session->setup_intent) {
            return;
        }

        // Shared with InstallmentController::view(), which applies the same swap on the redirect
        // back from Stripe. Both paths exist because this webhook only arrives if the install's
        // endpoint happens to subscribe to checkout.session.completed - our own Connect setup docs
        // tell operators to subscribe to payment_intent.succeeded alone, and a `mode: 'setup'`
        // session never emits that one. The implementation is idempotent, so whichever gets here
        // first wins and the other is free.
        app(\App\Services\InstallmentService::class)->applyCardUpdate($plan, $session->setup_intent);
    }
}
