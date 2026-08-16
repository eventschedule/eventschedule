<?php

namespace App\Http\Controllers;

use App\Mail\SubscriptionPaymentFailed;
use App\Models\Role;
use App\Services\OneSignalService;
use App\Utils\PlanPriceUtils;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
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
            $role->plan_source = null;
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
            if ($role->hasActiveSubscription()) {
                // Place the invoiced price before writing anything. A price we cannot resolve
                // means config is incomplete - most likely one was retired without being listed
                // in STRIPE_LEGACY_* - and hasActiveEnterpriseSubscription() answers false for
                // exactly that price, so trusting it here would stamp plan_type = 'pro' onto a
                // customer this very invoice just charged the Enterprise rate. Renewal invoices
                // fire every billing cycle, so leave the row alone and let the warning stand.
                $lines = $payload['data']['object']['lines']['data'] ?? [];
                $priceId = $lines[0]['price']['id'] ?? null;
                $planTier = PlanPriceUtils::tierFor($priceId);
                $planTerm = PlanPriceUtils::termFor($priceId);

                if ($priceId && (! $planTier || ! $planTerm)) {
                    Log::warning('Unrecognized Stripe price on paid invoice; leaving plan unchanged', [
                        'role_id' => $role->id,
                        'price_id' => $priceId,
                    ]);

                    return new Response('Webhook Handled', 200);
                }

                // No line items to read: fall back to the subscription-scoped answer, which is
                // what this handler did before the price was consulted at all.
                $planType = $planTier ?: ($role->hasActiveEnterpriseSubscription() ? 'enterprise' : 'pro');

                // Use lock to prevent race with controller swap
                \DB::transaction(function () use ($role, $planType, $planTerm) {
                    $role = Role::lockForUpdate()->find($role->id);
                    $role->plan_type = $planType;
                    if ($planTerm) {
                        $role->plan_term = $planTerm;
                    }
                    $role->plan_expires = null;
                    // Stripe is paying for this now, so any earlier hand-granted or referral
                    // provenance no longer applies - and neither does the footer credit.
                    $role->plan_source = null;
                    $role->save();
                });
            }
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

        if ($role && $role->user) {
            try {
                Mail::to($role->user->email)->send(new SubscriptionPaymentFailed($role));

                OneSignalService::pushToUser($role->user, [
                    'title_key' => 'messages.push_subscription_payment_failed_title',
                    'body_key' => 'messages.push_subscription_payment_failed_body',
                    'url' => route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'plan']),
                    'options' => [],
                ], null);
            } catch (\Exception $e) {
                \Log::error('Failed to send payment failed email', [
                    'role_id' => $role->id,
                    'error' => $e->getMessage(),
                ]);
            }
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
        // Newer Stripe API versions (2025-03-31 "basil"+) moved current_period_end from the
        // subscription object onto its items. Cashier v15 still reads the top-level key on the
        // cancel_at_period_end branch (WebhookController line 173), so backfill it to avoid an
        // "Undefined array key" error.
        $object = &$payload['data']['object'];
        if (! isset($object['current_period_end']) && isset($object['items']['data'][0]['current_period_end'])) {
            $object['current_period_end'] = $object['items']['data'][0]['current_period_end'];
        }
        unset($object);

        // Let Cashier handle the base logic
        $response = parent::handleCustomerSubscriptionUpdated($payload);

        $data = $payload['data']['object'];
        $role = Role::where('stripe_id', $data['customer'])->first();

        if ($role) {
            // Update the plan type and term based on the price
            $currentPrice = $data['items']['data'][0]['price']['id'] ?? null;
            $planType = PlanPriceUtils::tierFor($currentPrice);
            $planTerm = PlanPriceUtils::termFor($currentPrice);

            if ($currentPrice && (! $planType || ! $planTerm)) {
                // A price we cannot place means config is incomplete - most likely a price was
                // retired without being listed in STRIPE_LEGACY_*. Writing the old fallback here
                // (pro/month) would persist a downgrade onto a customer who is still being
                // charged the Enterprise rate, so leave the row alone and let the alert stand.
                Log::warning('Unrecognized Stripe price on subscription update; leaving plan unchanged', [
                    'role_id' => $role->id,
                    'price_id' => $currentPrice,
                ]);
            } elseif ($currentPrice) {
                // Use lock to prevent race with controller swap
                \DB::transaction(function () use ($role, $planType, $planTerm) {
                    $role = Role::lockForUpdate()->find($role->id);
                    $role->plan_type = $planType;
                    $role->plan_term = $planTerm;
                    $role->plan_expires = null;
                    $role->plan_source = null;
                    $role->save();
                });
            }
        }

        return $response;
    }
}
