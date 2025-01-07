<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Account;
use Stripe\AccountLink;

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
            'refresh_url' => route('profile.edit'),
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

        return redirect()->route('profile.edit')->with('success', __('messages.stripe_unlinked'));
    }

    public function complete()
    {
        $user = auth()->user();
        
        if ($user->stripe_account_id) {
            $account = Account::retrieve($user->stripe_account_id);
            
            if ($account->charges_enabled) {
                $user->stripe_completed_at = now();
                $user->save();
                
                return redirect()->route('profile.edit')->with('success', __('messages.stripe_connected'));
            }
        }

        return redirect()->route('profile.edit')->with('error', __('messages.failed_to_connect_stripe'));
    }

    public function webhook(Request $request)
    {
        $endpoint_secret = config('services.stripe.webhook_secret');
        $payload = $request->getContent();
        $sig_header = $request->header('stripe-signature');

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload, $sig_header, $endpoint_secret
            );
        } catch(\UnexpectedValueException $e) {
            return response()->json(['error' => 'Invalid payload'], 400);
        } catch(\Stripe\Exception\SignatureVerificationException $e) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        // Handle the event
        switch ($event->type) {
            case 'payment_intent.succeeded':
                $paymentIntent = $event->data->object;
                // handlePaymentIntentSucceeded($paymentIntent);
                break;
            case 'payment_method.attached':
                $paymentMethod = $event->data->object;
                // handlePaymentMethodAttached($paymentMethod);
                break;
            default:
                \Log::warning('Received unknown event type: ' . $event->type);
        }

        return response()->json(['status' => 'success']);
    }

}
