<?php

namespace App\Traits;

use App\Utils\PlanPriceUtils;
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
        $subscription = $this->subscription('default');

        // Retain access during the Stripe dunning window: a past_due subscription
        // (renewal charge failed, Stripe still retrying, not yet cancelled) keeps
        // feature access until it becomes unpaid/cancelled. valid() excludes
        // past_due, so accept it explicitly.
        if (! $subscription || (! $subscription->valid() && ! $subscription->pastDue())) {
            return false;
        }

        // Cancelled trials should not be considered active
        if ($subscription->canceled() && $subscription->onTrial()) {
            return false;
        }

        return true;
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
     * Check if the role is eligible for a free trial.
     * Eligible if they haven't had a subscription before.
     *
     * @return bool
     */
    public function isEligibleForTrial()
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

        if ($subscription->onTrial() && ! $subscription->canceled()) {
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

        // Only the configured yearly IDs resolve. An unrecognized price falls through to the
        // 'monthly' return below - the one place in this file that guesses rather than returning
        // null - which misstates the renewal date. Repoint STRIPE_PRICE_* at whatever your
        // subscribers are actually billed on rather than leaving a generation behind.
        foreach (PlanPriceUtils::yearlyIds() as $priceId) {
            if ($subscription->hasPrice($priceId)) {
                return 'yearly';
            }
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

        // Retain Enterprise access during the Stripe dunning window: a past_due
        // subscription (renewal charge failed, Stripe still retrying, not yet
        // cancelled) keeps access until it becomes unpaid/cancelled. active()
        // excludes past_due, so accept it explicitly.
        if (! $subscription || (! $subscription->active() && ! $subscription->pastDue())) {
            return false;
        }

        // Only the configured enterprise IDs grant Enterprise. Stripe keeps billing an archived
        // price, so a customer left on a price ID config no longer names keeps paying the
        // Enterprise rate while this returns false and their features are withdrawn. A price
        // change means repointing STRIPE_PRICE_* at what subscribers are actually billed on.
        foreach (PlanPriceUtils::enterpriseIds() as $priceId) {
            if ($subscription->hasPrice($priceId)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Stripe statuses a subscription never bills from again. Everything else - active,
     * trialing, past_due, unpaid, incomplete - is still open in Stripe and will be charged (or
     * retried) on its own schedule unless someone cancels it.
     */
    public const BILLING_ENDED_STATUSES = ['canceled', 'incomplete_expired'];

    /**
     * Subscriptions Stripe will still charge: not in an ended status, and not already cancelled
     * at period end (Cashier stamps ends_at the moment cancel() is called, so a null ends_at is
     * what "will renew" looks like locally).
     *
     * Shared with AdminAlertService::orphanedBilling() so the alert and the cancellation agree on
     * what "still billing" means.
     */
    public function liveBillingSubscriptions()
    {
        return $this->subscriptions()
            ->whereNotIn('stripe_status', self::BILLING_ENDED_STATUSES)
            ->whereNull('ends_at');
    }

    public function hasLiveBilling(): bool
    {
        return $this->stripe_id !== null && $this->liveBillingSubscriptions()->exists();
    }

    /**
     * Cancel every subscription Stripe would otherwise keep charging, immediately.
     *
     * Every path that removes a schedule has to call this BEFORE the schedule goes. The
     * subscription belongs to the schedule (Cashier::useCustomerModel), subscriptions.role_id has
     * no foreign key, and nothing on Stripe's side knows the schedule is gone - so a deleted
     * schedule, or an account deletion that cascades its schedules away, left Stripe renewing the
     * saved card every month. A customer who deleted their account on 10 Sep 2026 was charged
     * again on 27 Sep.
     *
     * Throws BillingCancellationException on a Stripe failure. Callers must abort the deletion
     * BEFORE tearing anything down, rather than carry on: deleting anyway is exactly the bug this
     * exists to prevent, and afterwards nobody can reach the subscription from the app.
     * Idempotent - a second call finds nothing live and makes no Stripe request - and a no-op on
     * a schedule that never subscribed (every selfhost install).
     *
     * Not wrapped in a transaction: no network I/O inside one.
     *
     * @return int how many subscriptions were cancelled
     *
     * @throws \App\Exceptions\BillingCancellationException
     */
    public function cancelBillingForDeletion(?int $actorUserId = null): int
    {
        if ($this->stripe_id === null) {
            return 0;
        }

        $cancelled = 0;

        foreach ($this->liveBillingSubscriptions()->get() as $subscription) {
            // cancelNow(), not cancel(): cancel() only stops the NEXT renewal, leaving the
            // subscription open until period end on a schedule that no longer exists.
            try {
                $subscription->cancelNow();
            } catch (\Throwable $e) {
                // Already over on Stripe's side, and only our row is stale (a missed webhook, or
                // the subscription was cancelled from the Stripe dashboard): record that and move
                // on. Aborting here would make the schedule undeletable forever, over a charge
                // that can no longer happen.
                if ($e instanceof \Stripe\Exception\InvalidRequestException && self::stripeSubscriptionIsOver($subscription->stripe_id)) {
                    $subscription->markAsCanceled();

                    continue;
                }

                throw new \App\Exceptions\BillingCancellationException(
                    "Could not cancel subscription {$subscription->stripe_id} for schedule {$this->id}: {$e->getMessage()}",
                    0,
                    $e,
                );
            }

            $cancelled++;

            // Counted as churn with no reason: nobody was asked, the schedule is going.
            \App\Models\SubscriptionCancellation::record([
                'role_id' => $this->id,
                'user_id' => $actorUserId,
                'stripe_subscription_id' => $subscription->stripe_id,
                'source' => 'schedule_deleted',
                'plan_type' => $this->plan_type,
                'plan_term' => $this->plan_term,
            ]);

            \App\Services\AuditService::log(
                \App\Services\AuditService::SUBSCRIPTION_CANCEL,
                $actorUserId,
                'Role',
                $this->id,
                null,
                ['stripe_id' => $subscription->stripe_id],
                'Cancelled because the schedule is being deleted',
            );
        }

        return $cancelled;
    }

    /**
     * Whether Stripe itself says this subscription can never bill again. Asked only after a
     * cancel request was refused, and fails closed: any doubt means the caller aborts.
     *
     * Static because it needs no schedule: AdminController::cancelOrphanedSubscription() asks it
     * about subscriptions whose schedule no longer exists.
     *
     * Deliberately NOT satisfied by a resource_missing refusal alone. Stripe answers "No such
     * subscription" just the same when the install is holding the other mode's key (test vs
     * live), and treating that as "over" would record a cancellation while the real
     * subscription keeps billing.
     */
    public static function stripeSubscriptionIsOver(string $stripeSubscriptionId): bool
    {
        try {
            $remote = \Laravel\Cashier\Cashier::stripe()->subscriptions->retrieve($stripeSubscriptionId);
        } catch (\Throwable $e) {
            return false;
        }

        return in_array($remote->status, self::BILLING_ENDED_STATUSES, true);
    }
}
