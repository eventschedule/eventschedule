<?php

namespace App\Http\Controllers;

use App\Http\Requests\SubscriptionStoreRequest;
use App\Http\Requests\SubscriptionSwapRequest;
use App\Models\Referral;
use App\Models\Role;
use App\Services\AuditService;
use App\Services\UsageTrackingService;
use App\Utils\PlanPriceUtils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        // If requesting Enterprise, verify price IDs are configured
        $enterpriseConfigured = PlanPriceUtils::current('enterprise', 'monthly') && PlanPriceUtils::current('enterprise', 'yearly');
        if ($requestedTier === 'enterprise' && ! $enterpriseConfigured) {
            $requestedTier = 'pro';
        }

        $intent = $role->createSetupIntent();

        // Onboarding funnel: "reached checkout". First-touch stamp, deliberately placed after
        // every redirect above AND after createSetupIntent() - a user bounced back because they
        // are already subscribed never saw the form, and neither did one whose page 500'd
        // because the Stripe call threw. The stamp has to mean the form actually rendered, or
        // the stage it feeds measures something else.
        // Base query builder + whereNull writes at most once and does not bump users.updated_at
        // (which the admin active-users metric keys off).
        DB::table('users')
            ->where('id', auth()->id())
            ->whereNull('subscribe_form_viewed_at')
            ->update(['subscribe_form_viewed_at' => now()]);

        return view('subscription.show', [
            'role' => $role,
            'intent' => $intent,
            'monthlyPrice' => PlanPriceUtils::current('pro', 'monthly'),
            'yearlyPrice' => PlanPriceUtils::current('pro', 'yearly'),
            'selectedTier' => $requestedTier,
            'enterpriseConfigured' => $enterpriseConfigured,
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

        // Validate Enterprise price IDs are configured
        if ($tier === 'enterprise' && (! PlanPriceUtils::current('enterprise', 'monthly') || ! PlanPriceUtils::current('enterprise', 'yearly'))) {
            return redirect()->back()->with('error', __('messages.subscription_error'));
        }

        // If upgrading from Pro to Enterprise with existing subscription, use swap
        if ($tier === 'enterprise' && $role->hasActiveSubscription() && ! $role->hasActiveEnterpriseSubscription()) {
            // current(), never the legacy sets: a new or swapped subscription is always created
            // at what we sell today. Retired price IDs exist only to be RECOGNIZED.
            $priceId = PlanPriceUtils::current('enterprise', $request->plan);

            try {
                $subscription = $role->subscription('default');
                $role->createOrGetStripeCustomer();
                $role->updateDefaultPaymentMethod($request->payment_method);
                $subscription->swap($priceId);

                // Update plan info with lock to prevent race with webhook
                \DB::transaction(function () use ($role, $request) {
                    $role = \App\Models\Role::lockForUpdate()->find($role->id);
                    $role->plan_type = 'enterprise';
                    $role->plan_term = $request->plan === 'yearly' ? 'year' : 'month';
                    $role->plan_source = null;
                    $role->save();
                });

                return redirect()
                    ->route('role.view_admin', ['subdomain' => $subdomain, 'tab' => 'plan'])
                    ->with('message', __('messages.subscription_updated'));
            } catch (IncompletePayment $exception) {
                return redirect()->route(
                    'cashier.payment',
                    [$exception->payment->id, 'redirect' => route('role.view_admin', ['subdomain' => $subdomain, 'tab' => 'plan'])]
                );
            } catch (\Exception $e) {
                UsageTrackingService::track(UsageTrackingService::STRIPE_SUBSCRIPTION_FAILED, $role->id);
                \Log::error('Subscription upgrade failed', ['error' => $e->getMessage(), 'role' => $role->id]);

                return redirect()->back()->with('error', __('messages.subscription_error'));
            }
        }

        if ($role->hasActiveSubscription()) {
            return redirect()
                ->route('role.view_admin', ['subdomain' => $subdomain, 'tab' => 'plan'])
                ->with('message', __('messages.subscription_already_active'));
        }

        $priceId = PlanPriceUtils::current($tier, $request->plan);

        try {
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

            // Update the role's plan info with lock to prevent race with webhook
            // and clear legacy plan_expires to prevent dual-path access via legacy fields
            \DB::transaction(function () use ($role, $tier, $request) {
                $role = Role::lockForUpdate()->find($role->id);
                $role->plan_type = $tier;
                $role->plan_term = $request->plan === 'yearly' ? 'year' : 'month';
                $role->plan_expires = null;
                // Paying now, so drop any hand-granted or referral provenance along with the
                // legacy expiry - both are what the guest-footer credit keys off.
                $role->plan_source = null;
                $role->save();
            });

            UsageTrackingService::track(UsageTrackingService::STRIPE_SUBSCRIPTION, $role->id);

            AuditService::log(AuditService::SUBSCRIPTION_CREATE, auth()->id(), 'Role', $role->id,
                null, ['plan_type' => $tier, 'plan_term' => $request->plan], $role->subdomain);

            // Track referral subscription
            if (config('app.hosted')) {
                $referral = Referral::where('referred_user_id', $role->user_id)
                    ->where('status', 'pending')
                    ->first();

                if ($referral) {
                    $referral->update([
                        'referred_role_id' => $role->id,
                        'plan_type' => $tier,
                        'subscribed_at' => now(),
                        'status' => 'subscribed',
                    ]);
                }
            }

            return redirect()
                ->route('role.view_admin', ['subdomain' => $subdomain, 'tab' => 'plan'])
                ->with('message', __('messages.subscription_created'));

        } catch (IncompletePayment $exception) {
            return redirect()->route(
                'cashier.payment',
                [$exception->payment->id, 'redirect' => route('role.view_admin', ['subdomain' => $subdomain, 'tab' => 'plan'])]
            );
        } catch (\Exception $e) {
            UsageTrackingService::track(UsageTrackingService::STRIPE_SUBSCRIPTION_FAILED, $role->id);
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

        if (! $role->hasStripeId()) {
            return redirect()->back()->with('error', __('messages.no_active_subscription'));
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

        AuditService::log(AuditService::SUBSCRIPTION_CANCEL, auth()->id(), 'Role', $role->id, null, null, $role->subdomain);

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

        AuditService::log(AuditService::SUBSCRIPTION_RESUME, auth()->id(), 'Role', $role->id, null, null, $role->subdomain);

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

        // Validate Enterprise price IDs are configured
        if ($tier === 'enterprise' && (! PlanPriceUtils::current('enterprise', 'monthly') || ! PlanPriceUtils::current('enterprise', 'yearly'))) {
            return redirect()->back()->with('error', __('messages.subscription_error'));
        }

        $priceId = PlanPriceUtils::current($tier, $request->plan);

        $subscription = $role->subscription('default');

        if (! $subscription || ! $subscription->active() || $subscription->onGracePeriod()) {
            return redirect()->back()->with('error', __('messages.no_active_subscription'));
        }

        try {
            $subscription->swap($priceId);

            // Update plan info with lock to prevent race with webhook
            \DB::transaction(function () use ($role, $tier, $request) {
                $role = Role::lockForUpdate()->find($role->id);
                $role->plan_type = $tier;
                $role->plan_term = $request->plan === 'yearly' ? 'year' : 'month';
                $role->plan_source = null;
                $role->save();
            });
        } catch (IncompletePayment $exception) {
            return redirect()->route(
                'cashier.payment',
                [$exception->payment->id, 'redirect' => route('role.view_admin', ['subdomain' => $subdomain, 'tab' => 'plan'])]
            );
        } catch (\Exception $e) {
            \Log::error('Subscription swap failed', ['error' => $e->getMessage(), 'role' => $role->id]);

            return redirect()->back()->with('error', __('messages.subscription_error'));
        }

        AuditService::log(AuditService::SUBSCRIPTION_SWAP, auth()->id(), 'Role', $role->id,
            null, ['plan_type' => $tier, 'plan_term' => $request->plan], $role->subdomain);

        return redirect()
            ->route('role.view_admin', ['subdomain' => $subdomain, 'tab' => 'plan'])
            ->with('message', __('messages.subscription_updated'));
    }
}
