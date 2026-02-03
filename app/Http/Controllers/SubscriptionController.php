<?php

namespace App\Http\Controllers;

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

        $intent = $role->createSetupIntent();

        return view('subscription.show', [
            'role' => $role,
            'intent' => $intent,
            'monthlyPrice' => config('services.stripe_platform.price_monthly'),
            'yearlyPrice' => config('services.stripe_platform.price_yearly'),
        ]);
    }

    /**
     * Create a new subscription.
     */
    public function store(Request $request, $subdomain)
    {
        $role = Role::subdomain($subdomain)->firstOrFail();

        if (auth()->user()->id != $role->user_id) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $request->validate([
            'payment_method' => 'required|string',
            'plan' => 'required|in:monthly,yearly',
        ]);

        $priceId = $request->plan === 'yearly'
            ? config('services.stripe_platform.price_yearly')
            : config('services.stripe_platform.price_monthly');

        try {
            // Set the payment method
            $role->updateDefaultPaymentMethod($request->payment_method);

            // Calculate trial days
            $trialDays = 0;

            // If eligible for first year free
            if ($role->isEligibleForFreeYear()) {
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
            $role->plan_type = 'pro';
            $role->plan_term = $request->plan;
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
            return redirect()->back()->with('error', $e->getMessage());
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

        if ($subscription) {
            $subscription->cancel();
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

        if ($subscription && $subscription->onGracePeriod()) {
            $subscription->resume();
        }

        return redirect()
            ->route('role.view_admin', ['subdomain' => $subdomain, 'tab' => 'plan'])
            ->with('message', __('messages.subscription_resumed'));
    }

    /**
     * Swap between monthly and yearly plans.
     */
    public function swap(Request $request, $subdomain)
    {
        $role = Role::subdomain($subdomain)->firstOrFail();

        if (auth()->user()->id != $role->user_id) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $request->validate([
            'plan' => 'required|in:monthly,yearly',
        ]);

        $priceId = $request->plan === 'yearly'
            ? config('services.stripe_platform.price_yearly')
            : config('services.stripe_platform.price_monthly');

        $subscription = $role->subscription('default');

        if ($subscription) {
            $subscription->swap($priceId);

            // Update the role's plan term
            $role->plan_term = $request->plan;
            $role->save();
        }

        return redirect()
            ->route('role.view_admin', ['subdomain' => $subdomain, 'tab' => 'plan'])
            ->with('message', __('messages.subscription_updated'));
    }
}
