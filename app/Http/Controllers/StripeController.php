<?php

namespace App\Http\Controllers;

use App\Models\AnalyticsEventsDaily;
use App\Models\Sale;
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
        }

        $link = AccountLink::create([
            'account' => $accountId,
            'return_url' => route('stripe.complete'),
            'refresh_url' => route('profile.edit').'#section-payment-methods',
            'type' => 'account_onboarding',
        ]);

        return redirect($link->url);
    }

    public function unlink()
    {
        $user = auth()->user();
        $user->stripe_account_id = null;
        $user->stripe_completed_at = null;
        $user->save();

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
                if ($sale) {
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
                    $sale->save();
                }
                break;

            case 'checkout.session.completed':
                // Direct Stripe payments (self-hosted mode)
                $session = $event->data->object;
                if ($session->payment_status === 'paid' && isset($session->metadata->sale_id)) {
                    $saleId = UrlUtils::decodeId($session->metadata->sale_id);
                    $sale = Sale::find($saleId);
                    if ($sale && $sale->status !== 'paid') {
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

                        // Record sale in analytics
                        AnalyticsEventsDaily::incrementSale($sale->event_id, $sale->payment_amount);
                    }
                }
                break;

            default:
                \Log::info('Received Stripe event type: '.$event->type);
        }

        return response()->json(['status' => 'success']);
    }
}
