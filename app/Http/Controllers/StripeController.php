<?php

namespace App\Http\Controllers;

use App\Models\AnalyticsEventsDaily;
use App\Models\Sale;
use App\Services\AuditService;
use App\Services\MetaAdsService;
use App\Services\UsageTrackingService;
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

        // Try Connect webhook secret first (hosted mode)
        $connectSecret = config('services.stripe.webhook_secret');
        if ($connectSecret) {
            try {
                $event = \Stripe\Webhook::constructEvent(
                    $payload, $sig_header, $connectSecret
                );
            } catch (\Stripe\Exception\SignatureVerificationException $e) {
                // Connect secret didn't work, will try platform secret
            } catch (\UnexpectedValueException $e) {
                return response()->json(['error' => 'Invalid payload'], 400);
            }
        }

        // Try platform webhook secret (self-hosted mode / direct payments)
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
                    // Use lockForUpdate to prevent race with the success redirect handler
                    \DB::transaction(function () use ($sale, $paymentIntent) {
                        $sale = Sale::lockForUpdate()->find($sale->id);
                        if ($sale->status === 'paid') {
                            return;
                        }

                        $webhookAmount = $paymentIntent->amount / 100;

                        // Validate that the webhook amount matches the expected sale amount
                        $expectedAmount = $sale->payment_amount;
                        $amountDifference = abs($webhookAmount - $expectedAmount);

                        // Allow small tolerance for floating point differences (e.g., 0.01)
                        if ($amountDifference > 0.01) {
                            \Log::warning('Payment amount mismatch in Stripe webhook', [
                                'sale_id' => $sale->id,
                                'expected_amount' => $expectedAmount,
                                'webhook_amount' => $webhookAmount,
                                'difference' => $amountDifference,
                                'payment_intent_id' => $paymentIntent->id,
                            ]);
                        }

                        $sale->payment_amount = $webhookAmount;
                        $sale->status = 'paid';
                        $sale->transaction_reference = $paymentIntent->id;
                        $sale->save();

                        AnalyticsEventsDaily::incrementSale($sale->event_id, $webhookAmount);
                        UsageTrackingService::track(UsageTrackingService::STRIPE_PAYMENT);

                        // Send conversion event to Meta CAPI if event has active boost
                        $this->sendMetaConversion($sale, $webhookAmount);
                    });
                }
                break;

            case 'checkout.session.completed':
                // Direct Stripe payments (self-hosted mode)
                $session = $event->data->object;
                if ($session->payment_status === 'paid' && isset($session->metadata->sale_id)) {
                    $saleId = UrlUtils::decodeId($session->metadata->sale_id);
                    $sale = Sale::find($saleId);
                    if ($sale) {
                        // Use lockForUpdate to prevent race with the success redirect handler
                        \DB::transaction(function () use ($sale, $session) {
                            $sale = Sale::lockForUpdate()->find($sale->id);
                            if ($sale->status === 'paid') {
                                return;
                            }

                            $webhookAmount = $session->amount_total / 100;

                            // Validate that the webhook amount matches the expected sale amount
                            $expectedAmount = $sale->payment_amount;
                            $amountDifference = abs($webhookAmount - $expectedAmount);

                            // Allow small tolerance for floating point differences (e.g., 0.01)
                            if ($amountDifference > 0.01) {
                                \Log::warning('Payment amount mismatch in Stripe checkout webhook', [
                                    'sale_id' => $sale->id,
                                    'expected_amount' => $expectedAmount,
                                    'webhook_amount' => $webhookAmount,
                                    'difference' => $amountDifference,
                                    'session_id' => $session->id,
                                ]);
                            }

                            $sale->payment_amount = $webhookAmount;
                            $sale->status = 'paid';
                            $sale->transaction_reference = $session->payment_intent;
                            $sale->save();

                            UsageTrackingService::track(UsageTrackingService::STRIPE_PAYMENT);

                            // Record sale in analytics
                            AnalyticsEventsDaily::incrementSale($sale->event_id, $sale->payment_amount);

                            // Send conversion event to Meta CAPI if event has active boost
                            $this->sendMetaConversion($sale, $sale->payment_amount);
                        });
                    }
                }
                break;

            default:
                \Log::info('Received Stripe event type: '.$event->type);
        }

        return response()->json(['status' => 'success']);
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
