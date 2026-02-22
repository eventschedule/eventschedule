<?php

namespace App\Traits;

use Laravel\Cashier\Billable;

trait RoleBillable
{
    use Billable;

    /**
     * Get the email address used for Stripe customer.
     *
     * @return string|null
     */
    public function stripeEmail()
    {
        return $this->email;
    }

    /**
     * Get the name used for Stripe customer.
     *
     * @return string|null
     */
    public function stripeName()
    {
        return $this->name;
    }

    /**
     * Check if the role has an active subscription.
     *
     * @return bool
     */
    public function hasActiveSubscription()
    {
        return $this->subscribed('default');
    }

    /**
     * Check if the role is on a grace period after cancellation.
     *
     * @return bool
     */
    public function onGracePeriod()
    {
        $subscription = $this->subscription('default');

        return $subscription && $subscription->onGracePeriod();
    }

    /**
     * Get the number of days remaining in trial.
     *
     * @return int|null
     */
    public function trialDaysRemaining()
    {
        if (! $this->onGenericTrial()) {
            return null;
        }

        return (int) floor(now()->diffInDays($this->trial_ends_at, false));
    }

    /**
     * Calculate remaining trial days based on current plan_expires date.
     * Used when converting existing trial users to Stripe subscriptions.
     *
     * @return int
     */
    public function calculateRemainingTrialDays()
    {
        if (! $this->plan_expires) {
            return 0;
        }

        $expiresAt = \Carbon\Carbon::parse($this->plan_expires);
        $daysRemaining = now()->startOfDay()->diffInDays($expiresAt->startOfDay(), false);

        return max(0, $daysRemaining);
    }

    /**
     * Check if the role is eligible for first year free.
     * Eligible if they haven't had a subscription before.
     *
     * @return bool
     */
    public function isEligibleForFreeYear()
    {
        if ($this->plan_expires || $this->trial_ends_at) {
            return false;
        }

        return ! $this->stripe_id || ! $this->subscriptions()->exists();
    }

    /**
     * Get the subscription status label for display.
     *
     * @return string
     */
    public function subscriptionStatusLabel()
    {
        $subscription = $this->subscription('default');

        if (! $subscription) {
            if ($this->onGenericTrial()) {
                return 'trial';
            }

            return 'none';
        }

        if ($subscription->onTrial()) {
            return 'trial';
        }

        if ($subscription->onGracePeriod()) {
            return 'grace_period';
        }

        if ($subscription->canceled()) {
            return 'cancelled';
        }

        if ($subscription->pastDue()) {
            return 'past_due';
        }

        if ($subscription->active()) {
            return 'active';
        }

        return 'inactive';
    }

    /**
     * Get the current plan term (monthly or yearly).
     *
     * @return string|null
     */
    public function currentPlanTerm()
    {
        $subscription = $this->subscription('default');

        if (! $subscription) {
            return $this->plan_term === 'year' ? 'yearly' : 'monthly';
        }

        $yearlyPriceId = config('services.stripe_platform.price_yearly');
        $enterpriseYearlyPriceId = config('services.stripe_platform.enterprise_price_yearly');

        if ($subscription->hasPrice($yearlyPriceId) || ($enterpriseYearlyPriceId && $subscription->hasPrice($enterpriseYearlyPriceId))) {
            return 'yearly';
        }

        return 'monthly';
    }

    /**
     * Check if the role has an active enterprise subscription.
     *
     * @return bool
     */
    public function hasActiveEnterpriseSubscription()
    {
        $subscription = $this->subscription('default');

        if (! $subscription || ! $subscription->active()) {
            return false;
        }

        $enterpriseMonthly = config('services.stripe_platform.enterprise_price_monthly');
        $enterpriseYearly = config('services.stripe_platform.enterprise_price_yearly');

        return ($enterpriseMonthly && $subscription->hasPrice($enterpriseMonthly)) ||
               ($enterpriseYearly && $subscription->hasPrice($enterpriseYearly));
    }
}
