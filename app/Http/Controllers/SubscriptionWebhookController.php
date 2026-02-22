<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Laravel\Cashier\Http\Controllers\WebhookController;
use Symfony\Component\HttpFoundation\Response;

class SubscriptionWebhookController extends WebhookController
{
    /**
     * Handle customer subscription deleted.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function handleCustomerSubscriptionDeleted(array $payload)
    {
        // Let Cashier handle the base logic
        $response = parent::handleCustomerSubscriptionDeleted($payload);

        // Find the role by stripe customer ID
        $role = Role::where('stripe_id', $payload['data']['object']['customer'])->first();

        if ($role) {
            // Downgrade to free plan when subscription is deleted
            $role->plan_type = 'free';
            $role->plan_expires = null;
            $role->save();
        }

        return $response;
    }

    /**
     * Handle customer subscription trial will end.
     * This is sent 3 days before the trial ends.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function handleCustomerSubscriptionTrialWillEnd(array $payload)
    {
        $role = Role::where('stripe_id', $payload['data']['object']['customer'])->first();

        if ($role) {
            // You could send a notification here if desired
            // $role->notify(new TrialEndingNotification());
        }

        return new Response('Webhook Handled', 200);
    }

    /**
     * Handle invoice payment succeeded.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function handleInvoicePaymentSucceeded(array $payload)
    {
        $role = Role::where('stripe_id', $payload['data']['object']['customer'])->first();

        if ($role) {
            $role->plan_type = $role->hasActiveEnterpriseSubscription() ? 'enterprise' : 'pro';
            $role->save();
        }

        return new Response('Webhook Handled', 200);
    }

    /**
     * Handle invoice payment failed.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function handleInvoicePaymentFailed(array $payload)
    {
        $role = Role::where('stripe_id', $payload['data']['object']['customer'])->first();

        if ($role) {
            // You could send a notification here if desired
            // $role->notify(new PaymentFailedNotification());
        }

        return new Response('Webhook Handled', 200);
    }

    /**
     * Handle customer subscription updated.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function handleCustomerSubscriptionUpdated(array $payload)
    {
        // Let Cashier handle the base logic
        $response = parent::handleCustomerSubscriptionUpdated($payload);

        $data = $payload['data']['object'];
        $role = Role::where('stripe_id', $data['customer'])->first();

        if ($role) {
            // Update the plan term based on the price
            $yearlyPriceId = config('services.stripe_platform.price_yearly');
            $enterpriseYearlyPriceId = config('services.stripe_platform.enterprise_price_yearly');
            $currentPrice = $data['items']['data'][0]['price']['id'] ?? null;

            if ($currentPrice) {
                $isYearly = ($currentPrice === $yearlyPriceId) || ($currentPrice === $enterpriseYearlyPriceId);
                $role->plan_term = $isYearly ? 'year' : 'month';
                $role->save();
            }
        }

        return $response;
    }
}
