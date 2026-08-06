<?php

namespace App\Http\Controllers;

use App\Models\AnalyticsEventsDaily;
use App\Models\GiftCard;
use App\Models\Sale;
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
