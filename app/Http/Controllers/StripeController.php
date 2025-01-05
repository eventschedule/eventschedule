<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\StripeClient;
use Stripe\Account;


class StripeController extends Controller
{
    public function link()
    {
        $stripe = new StripeClient([
            'api_key' => config('services.stripe.key')
        ]);

        $user = auth()->user();
        $accountId = $user->stripe_account_id;

        if (! $accountId) {
            $account = $stripe->accounts->create();
            $user->stripe_account_id = $account->id;
            $user->save();

            $accountId = $account->id;
        }

        $link = $stripe->accountLinks->create([
            'account' => $accountId,
            'return_url' => route('stripe.complete', ['stripe_account_id' => $accountId]),
            'refresh_url' => route('profile.edit'),
            'type' => 'account_onboarding',
        ]);

        return redirect($link->url);                  
    }

    public function complete($stripeAccountId)
    {
        $user = auth()->user();
        
        if ($stripeAccountId == $user->stripe_account_id) {
            $account = Account::retrieve($user->stripe_account_id);
            
            if ($account->details_submitted) {
                $user->update([
                    'stripe_completed_at' => now()
                ]);
                
                return redirect()->route('profile.edit')->with('success', __('messages.stripe_connected'));
            }
        }

        return redirect()->route('profile.edit')->with('error', __('messages.failed_to_connect_stripe'));
    }
}
