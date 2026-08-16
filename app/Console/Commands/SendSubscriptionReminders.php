<?php

namespace App\Console\Commands;

use App\Mail\SubscriptionRenewal;
use App\Mail\SubscriptionTrialEnding;
use App\Models\Role;
use App\Services\OneSignalService;
use App\Utils\PlanPriceUtils;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Laravel\Cashier\Subscription;

class SendSubscriptionReminders extends Command
{
    protected $signature = 'app:send-subscription-reminders';

    protected $description = 'Send reminder emails for ending trials and upcoming annual renewals';

    public function handle()
    {
        if (! config('app.hosted')) {
            $this->info('Skipping: not in hosted mode.');

            return 0;
        }

        $this->sendTrialReminders();
        $this->sendCompedWindDownReminders();
        $this->sendRenewalReminders();

        return 0;
    }

    /**
     * Reminders for the wind-down trials set by app:wind-down-comped-plans.
     *
     * sendTrialReminders() above only sees Stripe `Subscription` rows with
     * stripe_status = 'trialing'. A comped schedule has no subscription row at all - that is
     * precisely how the wind-down identifies it - so without this those trials would end in
     * silence.
     *
     * Three touches rather than one, because these people never chose to start a trial and
     * are being asked to pay for something that was free. trial_reminder_sent_at holds the
     * last touch, so each window fires at most once.
     */
    protected function sendCompedWindDownReminders(): void
    {
        $this->info('Checking for comped wind-down reminders...');

        $windows = [14, 3, 1];
        $sent = 0;

        foreach ($windows as $daysOut) {
            $target = Carbon::today()->addDays($daysOut);

            $roles = Role::query()
                ->where('plan_source', 'admin')
                ->where('is_deleted', false)
                ->whereNotNull('trial_ends_at')
                ->whereBetween('trial_ends_at', [$target->copy()->startOfDay(), $target->copy()->endOfDay()])
                ->whereDoesntHave('subscriptions')
                ->get();

            foreach ($roles as $role) {
                if (! $role->user) {
                    continue;
                }

                // Already reminded inside this window.
                if ($role->winddown_reminder_sent_at
                    && $role->winddown_reminder_sent_at->greaterThan(now()->subDays($daysOut))) {
                    continue;
                }

                try {
                    $isEnterprise = $role->plan_type === 'enterprise';
                    $amount = (int) config($isEnterprise
                        ? 'services.stripe_platform.enterprise_price_monthly_amount'
                        : 'services.stripe_platform.price_monthly_amount', $isEnterprise ? 29 : 9);

                    Mail::to($role->user->email)->send(new SubscriptionTrialEnding(
                        $role,
                        // Formatted, like getAmountForSubscription() does for every other caller.
                        // A bare int coerced to "9" and the copy renders it verbatim: "will be
                        // charged 9."
                        '$'.$amount,
                        $isEnterprise ? 'Enterprise' : 'Pro',
                        $role->trial_ends_at->format('F j, Y'),
                        $role->hasDefaultPaymentMethod(),
                        // Wind-down copy: there is no subscription here, so neither "your card
                        // will be charged" nor "add a payment method" is true. The owner has to
                        // start a subscription, and being told otherwise lets the plan lapse
                        // while they believe they have acted.
                        windDown: true,
                    ));

                    // Its own column. trial_reminder_sent_at is read by the Stripe path below with
                    // NO time window - any value at all means "already sent, forever" - so
                    // stamping it here permanently suppressed the genuine "your trial ends
                    // tomorrow" email for every schedule this wound down.
                    $role->winddown_reminder_sent_at = now();
                    $role->save();

                    $this->info("Sent {$daysOut}-day wind-down reminder to {$role->subdomain}.");
                    $sent++;
                } catch (\Exception $e) {
                    $this->error("Failed wind-down reminder for {$role->subdomain}: {$e->getMessage()}");
                    Log::error('Failed to send comped wind-down reminder', [
                        'role_id' => $role->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        $this->info("Wind-down reminders: {$sent} sent.");
    }

    protected function sendTrialReminders(): void
    {
        $this->info('Checking for trial ending reminders...');

        $tomorrow = Carbon::tomorrow();

        $subscriptions = Subscription::where('stripe_status', 'trialing')
            ->whereBetween('trial_ends_at', [
                $tomorrow->copy()->startOfDay(),
                $tomorrow->copy()->endOfDay(),
            ])
            ->get();

        if ($subscriptions->isEmpty()) {
            $this->info('No trials ending tomorrow.');

            return;
        }

        $sent = 0;
        $skipped = 0;

        foreach ($subscriptions as $subscription) {
            $role = Role::find($subscription->role_id);

            if (! $role || ! $role->user) {
                $this->warn("Skipping subscription {$subscription->id}: role or user not found.");
                $skipped++;

                continue;
            }

            if ($role->trial_reminder_sent_at) {
                $this->info("Skipping {$role->subdomain}: trial reminder already sent.");
                $skipped++;

                continue;
            }

            if ($subscription->canceled()) {
                $this->info("Skipping {$role->subdomain}: subscription already cancelled.");
                $skipped++;

                continue;
            }

            try {
                $planLabel = $this->getPlanLabel($subscription);
                $amount = $this->getAmountForSubscription($subscription);
                $trialEndDate = Carbon::parse($subscription->trial_ends_at)->format('F j, Y');

                $hasCard = $role->hasDefaultPaymentMethod();

                Mail::to($role->user->email)->send(
                    new SubscriptionTrialEnding($role, $amount, $planLabel, $trialEndDate, $hasCard)
                );

                OneSignalService::pushToUser($role->user, [
                    'title_key' => 'messages.push_subscription_trial_title',
                    'body_key' => 'messages.push_subscription_trial_body',
                    'url' => route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'plan']),
                    'options' => [],
                ], null);

                $role->trial_reminder_sent_at = now();
                $role->save();

                $this->info("Sent trial ending reminder to {$role->subdomain} ({$role->user->email}).");
                $sent++;
            } catch (\Exception $e) {
                $this->error("Failed to send trial reminder for {$role->subdomain}: {$e->getMessage()}");
                Log::error('Failed to send trial ending reminder', [
                    'role_id' => $role->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info("Trial reminders: {$sent} sent, {$skipped} skipped.");
    }

    protected function sendRenewalReminders(): void
    {
        $this->info('Checking for annual renewal reminders...');

        $roles = Role::where('plan_term', 'year')
            ->whereHas('subscriptions', function ($query) {
                $query->where('stripe_status', 'active');
            })
            ->where(function ($query) {
                $query->whereNull('renewal_reminder_sent_at')
                    ->orWhere('renewal_reminder_sent_at', '<', now()->subDays(30));
            })
            ->get();

        if ($roles->isEmpty()) {
            $this->info('No annual renewals to check.');

            return;
        }

        $threeDaysFromNow = Carbon::now()->addDays(3);
        $sent = 0;
        $skipped = 0;

        foreach ($roles as $role) {
            $subscription = $role->subscription('default');

            if (! $subscription || ! $subscription->active() || ! $role->user) {
                $this->warn("Skipping {$role->subdomain}: active subscription or user not found.");
                $skipped++;

                continue;
            }

            try {
                $stripeSubscription = $subscription->asStripeSubscription();
                $periodEnd = Carbon::createFromTimestamp($stripeSubscription->current_period_end);

                if (! $periodEnd->isSameDay($threeDaysFromNow)) {
                    $skipped++;

                    continue;
                }

                $planLabel = $this->getPlanLabel($subscription);
                $amount = $this->getAmountForSubscription($subscription, $stripeSubscription);
                $renewalDate = $periodEnd->format('F j, Y');

                if (! $amount) {
                    $this->warn("Skipping {$role->subdomain}: unable to determine renewal amount for price ID {$subscription->stripe_price}.");
                    $skipped++;

                    continue;
                }

                $hasCard = $role->hasDefaultPaymentMethod();

                Mail::to($role->user->email)->send(
                    new SubscriptionRenewal($role, $amount, $planLabel, $renewalDate, $hasCard)
                );

                OneSignalService::pushToUser($role->user, [
                    'title_key' => 'messages.push_subscription_renewal_title',
                    'body_key' => 'messages.push_subscription_renewal_body',
                    'url' => route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'plan']),
                    'options' => [],
                ], null);

                $role->renewal_reminder_sent_at = now();
                $role->save();

                $this->info("Sent renewal reminder to {$role->subdomain} ({$role->user->email}).");
                $sent++;
            } catch (\Exception $e) {
                $this->error("Failed to send renewal reminder for {$role->subdomain}: {$e->getMessage()}");
                Log::error('Failed to send renewal reminder', [
                    'role_id' => $role->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info("Renewal reminders: {$sent} sent, {$skipped} skipped.");
    }

    protected function getPlanLabel(Subscription $subscription): string
    {
        foreach (PlanPriceUtils::enterpriseIds() as $priceId) {
            if ($subscription->hasPrice($priceId)) {
                return 'Enterprise';
            }
        }

        return 'Pro';
    }

    /**
     * What this subscriber will actually be charged at renewal.
     *
     * Read off the live Stripe subscription first, which the caller has already fetched. That is
     * the only figure guaranteed to be right for a grandfathered subscriber: the config amounts
     * describe what we sell TODAY, and quoting those to someone still billing on a retired price
     * would state a number they are not being charged. Config is the fallback for when the
     * Stripe object is unavailable.
     *
     * @param  \Stripe\Subscription|null  $stripeSubscription  the already-fetched Stripe object
     */
    protected function getAmountForSubscription(Subscription $subscription, $stripeSubscription = null): string
    {
        $unitAmount = $stripeSubscription->items->data[0]->price->unit_amount ?? null;

        if ($unitAmount !== null) {
            return '$'.rtrim(rtrim(number_format($unitAmount / 100, 2, '.', ''), '0'), '.');
        }

        $amount = PlanPriceUtils::amountFor($subscription->stripe_price);

        if ($amount) {
            return '$'.rtrim(rtrim(number_format($amount, 2, '.', ''), '0'), '.');
        }

        return '';
    }
}
