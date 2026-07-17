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

                        $currencyCode = $sale->event?->ticket_currency_code ?? 'USD';
                        $webhookAmount = $paymentIntent->amount / MoneyUtils::getSmallestUnitMultiplier($currencyCode);

                        // For grouped purchases (individual tickets) the buyer pays the group total in one charge.
                        $expectedAmount = $sale->isPrimarySale() ? $sale->groupTotalPayment() : (float) $sale->payment_amount;
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

                        // Preserve per-seat payment_amount on grouped primaries; only overwrite for ungrouped sales
                        if (! $sale->isPrimarySale()) {
                            $sale->payment_amount = $webhookAmount;
                        }
                        $sale->status = 'paid';
                        $sale->transaction_reference = $paymentIntent->id;
                        $sale->save();
                        $didTransitionToPaid = true;

                        AuditService::log(AuditService::SALE_PAID, $sale->user_id, 'Sale', $sale->id,
                            ['status' => 'unpaid'], ['status' => 'paid'], 'stripe:event_id:'.$sale->event_id);

                        AnalyticsEventsDaily::incrementSale($sale->event_id, $webhookAmount);
                        $promoTotal = $sale->isPrimarySale() ? $sale->groupTotalDiscount() : (float) ($sale->discount_amount ?? 0);
                        if ($promoTotal > 0) {
                            AnalyticsEventsDaily::incrementPromoSale($sale->event_id, $promoTotal);
                        }
                        UsageTrackingService::track(UsageTrackingService::STRIPE_PAYMENT);

                        // Send conversion event to Meta CAPI if event has active boost
                        $this->sendMetaConversion($sale, $webhookAmount);

                        WebhookService::dispatch('sale.paid', $sale);
                        if ($sale->group_id && $sale->isPrimarySale()) {
                            foreach (Sale::where('group_id', $sale->group_id)->where('id', '!=', $sale->id)->get() as $gs) {
                                WebhookService::dispatch('sale.paid', $gs);
                            }
                        }
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

                            $currencyCode = $sale->event?->ticket_currency_code ?? 'USD';
                            $webhookAmount = $session->amount_total / MoneyUtils::getSmallestUnitMultiplier($currencyCode);

                            // For grouped purchases (individual tickets) the buyer pays the group total in one charge.
                            $expectedAmount = $sale->isPrimarySale() ? $sale->groupTotalPayment() : (float) $sale->payment_amount;
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

                            // Preserve per-seat payment_amount on grouped primaries; only overwrite for ungrouped sales
                            if (! $sale->isPrimarySale()) {
                                $sale->payment_amount = $webhookAmount;
                            }
                            $sale->status = 'paid';
                            $sale->transaction_reference = $session->payment_intent;
                            $sale->save();
                            $didTransitionToPaid = true;

                            AuditService::log(AuditService::SALE_PAID, $sale->user_id, 'Sale', $sale->id,
                                ['status' => 'unpaid'], ['status' => 'paid'], 'stripe_checkout:event_id:'.$sale->event_id);

                            UsageTrackingService::track(UsageTrackingService::STRIPE_PAYMENT);

                            // Record sale in analytics
                            AnalyticsEventsDaily::incrementSale($sale->event_id, $webhookAmount);
                            $promoTotal = $sale->isPrimarySale() ? $sale->groupTotalDiscount() : (float) ($sale->discount_amount ?? 0);
                            if ($promoTotal > 0) {
                                AnalyticsEventsDaily::incrementPromoSale($sale->event_id, $promoTotal);
                            }

                            // Send conversion event to Meta CAPI if event has active boost
                            $this->sendMetaConversion($sale, $webhookAmount);

                            WebhookService::dispatch('sale.paid', $sale);
                            if ($sale->group_id && $sale->isPrimarySale()) {
                                foreach (Sale::where('group_id', $sale->group_id)->where('id', '!=', $sale->id)->get() as $gs) {
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
