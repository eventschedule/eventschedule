<?php

namespace App\Http\Controllers;

use App\Http\Requests\SubscriptionStoreRequest;
use App\Http\Requests\SubscriptionSwapRequest;
use App\Models\Role;
use App\Services\UsageTrackingService;
use Illuminate\Http\Request;
use Laravel\Cashier\Exceptions\IncompletePayment;

class SubscriptionController extends Controller
{
    /**
     * Show the subscription page.
     */
    public function show(Request $request, $subdomain)
    {
        $role = Role::subdomain($subdomain)->firstOrFail();

        if (auth()->user()->id != $role->user_id) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $requestedTier = $request->query('tier', 'pro');

        // Block if already on Enterprise, or if already on the requested tier
        if ($role->hasActiveEnterpriseSubscription()) {
            return redirect()
                ->route('role.view_admin', ['subdomain' => $subdomain, 'tab' => 'plan'])
                ->with('message', __('messages.subscription_already_active'));
        }

        // If they have an active Pro subscription and aren't requesting Enterprise, block
        // Also block trial subscriptions from upgrading to Enterprise
        if ($role->hasActiveSubscription() && ($requestedTier !== 'enterprise' || $role->subscription('default')?->onTrial())) {
            return redirect()
                ->route('role.view_admin', ['subdomain' => $subdomain, 'tab' => 'plan'])
                ->with('message', __('messages.subscription_already_active'));
        }

        $intent = $role->createSetupIntent();

        return view('subscription.show', [
            'role' => $role,
            'intent' => $intent,
            'monthlyPrice' => config('services.stripe_platform.price_monthly'),
            'yearlyPrice' => config('services.stripe_platform.price_yearly'),
            'selectedTier' => $requestedTier,
        ]);
    }

    /**
     * Create a new subscription.
     */
    public function store(SubscriptionStoreRequest $request, $subdomain)
    {
        $role = Role::subdomain($subdomain)->firstOrFail();

        if (auth()->user()->id != $role->user_id) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $tier = $request->input('tier', 'pro');

        // If upgrading from Pro to Enterprise with existing subscription, use swap
        if ($tier === 'enterprise' && $role->hasActiveSubscription() && ! $role->hasActiveEnterpriseSubscription()) {
            $priceId = $request->plan === 'yearly'
                ? config('services.stripe_platform.enterprise_price_yearly')
                : config('services.stripe_platform.enterprise_price_monthly');

            try {
                $subscription = $role->subscription('default');
                $role->updateDefaultPaymentMethod($request->payment_method);
                $subscription->swap($priceId);

                $role->plan_type = 'enterprise';
                $role->plan_term = $request->plan === 'yearly' ? 'year' : 'month';
                $role->save();

                return redirect()
                    ->route('role.view_admin', ['subdomain' => $subdomain, 'tab' => 'plan'])
                    ->with('message', __('messages.subscription_updated'));
            } catch (IncompletePayment $exception) {
                return redirect()->route(
                    'cashier.payment',
                    [$exception->payment->id, 'redirect' => route('role.view_admin', ['subdomain' => $subdomain, 'tab' => 'plan'])]
                );
            } catch (\Exception $e) {
                \Log::error('Subscription upgrade failed', ['error' => $e->getMessage(), 'role' => $role->id]);

                return redirect()->back()->with('error', __('messages.subscription_error'));
            }
        }

        if ($role->hasActiveSubscription()) {
            return redirect()
                ->route('role.view_admin', ['subdomain' => $subdomain, 'tab' => 'plan'])
                ->with('message', __('messages.subscription_already_active'));
        }

        if ($tier === 'enterprise') {
            $priceId = $request->plan === 'yearly'
                ? config('services.stripe_platform.enterprise_price_yearly')
                : config('services.stripe_platform.enterprise_price_monthly');
        } else {
            $priceId = $request->plan === 'yearly'
                ? config('services.stripe_platform.price_yearly')
                : config('services.stripe_platform.price_monthly');
        }

        try {
            // Set the payment method
            $role->updateDefaultPaymentMethod($request->payment_method);

            // Calculate trial days
            $trialDays = 0;

            // If eligible for free trial
            if ($role->isEligibleForTrial()) {
                $trialDays = config('app.trial_days');
            } elseif ($role->plan_expires) {
                // If they have remaining days from legacy trial
                $trialDays = $role->calculateRemainingTrialDays();
            }

            // Create the subscription
            $subscriptionBuilder = $role->newSubscription('default', $priceId);

            if ($trialDays > 0) {
                $subscriptionBuilder->trialDays($trialDays);
            }

            $subscriptionBuilder->create($request->payment_method);

            // Update the role's plan info
            $role->plan_type = $tier;
            $role->plan_term = $request->plan === 'yearly' ? 'year' : 'month';
            $role->save();

            UsageTrackingService::track(UsageTrackingService::STRIPE_SUBSCRIPTION, $role->id);

            return redirect()
                ->route('role.view_admin', ['subdomain' => $subdomain, 'tab' => 'plan'])
                ->with('message', __('messages.subscription_created'));

        } catch (IncompletePayment $exception) {
            return redirect()->route(
                'cashier.payment',
                [$exception->payment->id, 'redirect' => route('role.view_admin', ['subdomain' => $subdomain, 'tab' => 'plan'])]
            );
        } catch (\Exception $e) {
            \Log::error('Subscription creation failed', ['error' => $e->getMessage(), 'role' => $role->id]);

            return redirect()->back()->with('error', __('messages.subscription_error'));
        }
    }

    /**
     * Redirect to Stripe Customer Portal.
     */
    public function portal(Request $request, $subdomain)
    {
        $role = Role::subdomain($subdomain)->firstOrFail();

        if (auth()->user()->id != $role->user_id) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        return $role->redirectToBillingPortal(
            route('role.view_admin', ['subdomain' => $subdomain, 'tab' => 'plan'])
        );
    }

    /**
     * Cancel the subscription.
     */
    public function cancel(Request $request, $subdomain)
    {
        $role = Role::subdomain($subdomain)->firstOrFail();

        if (auth()->user()->id != $role->user_id) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $subscription = $role->subscription('default');

        if (! $subscription || ! $subscription->active()) {
            return redirect()->back()->with('error', __('messages.no_active_subscription'));
        }

        try {
            $subscription->cancel();
        } catch (\Exception $e) {
            \Log::error('Subscription cancellation failed', ['error' => $e->getMessage(), 'role' => $role->id]);

            return redirect()->back()->with('error', __('messages.subscription_error'));
        }

        return redirect()
            ->route('role.view_admin', ['subdomain' => $subdomain, 'tab' => 'plan'])
            ->with('message', __('messages.subscription_cancelled'));
    }

    /**
     * Resume a cancelled subscription.
     */
    public function resume(Request $request, $subdomain)
    {
        $role = Role::subdomain($subdomain)->firstOrFail();

        if (auth()->user()->id != $role->user_id) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $subscription = $role->subscription('default');

        if (! $subscription || ! $subscription->onGracePeriod()) {
            return redirect()->back()->with('error', __('messages.subscription_not_resumable'));
        }

        try {
            $subscription->resume();
        } catch (\Exception $e) {
            \Log::error('Subscription resume failed', ['error' => $e->getMessage(), 'role' => $role->id]);

            return redirect()->back()->with('error', __('messages.subscription_error'));
        }

        return redirect()
            ->route('role.view_admin', ['subdomain' => $subdomain, 'tab' => 'plan'])
            ->with('message', __('messages.subscription_resumed'));
    }

    /**
     * Swap between monthly/yearly plans and/or upgrade tier.
     */
    public function swap(SubscriptionSwapRequest $request, $subdomain)
    {
        $role = Role::subdomain($subdomain)->firstOrFail();

        if (auth()->user()->id != $role->user_id) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $tier = $request->input('tier', $role->plan_type ?: 'pro');

        if ($role->plan_type === 'enterprise' && $tier !== 'enterprise') {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        if ($tier === 'enterprise') {
            $priceId = $request->plan === 'yearly'
                ? config('services.stripe_platform.enterprise_price_yearly')
                : config('services.stripe_platform.enterprise_price_monthly');
        } else {
            $priceId = $request->plan === 'yearly'
                ? config('services.stripe_platform.price_yearly')
                : config('services.stripe_platform.price_monthly');
        }

        $subscription = $role->subscription('default');

        if (! $subscription || ! $subscription->active() || $subscription->onTrial() || $subscription->onGracePeriod()) {
            return redirect()->back()->with('error', __('messages.no_active_subscription'));
        }

        try {
            $subscription->swap($priceId);

            // Update the role's plan type and term
            $role->plan_type = $tier;
            $role->plan_term = $request->plan === 'yearly' ? 'year' : 'month';
            $role->save();
        } catch (IncompletePayment $exception) {
            return redirect()->route(
                'cashier.payment',
                [$exception->payment->id, 'redirect' => route('role.view_admin', ['subdomain' => $subdomain, 'tab' => 'plan'])]
            );
        } catch (\Exception $e) {
            \Log::error('Subscription swap failed', ['error' => $e->getMessage(), 'role' => $role->id]);

            return redirect()->back()->with('error', __('messages.subscription_error'));
        }

        return redirect()
            ->route('role.view_admin', ['subdomain' => $subdomain, 'tab' => 'plan'])
            ->with('message', __('messages.subscription_updated'));
    }
}
