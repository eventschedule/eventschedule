<?php

namespace App\Console\Commands;

use App\Mail\SubscriptionRenewal;
use App\Mail\SubscriptionTrialEnding;
use App\Models\Role;
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
        $this->sendRenewalReminders();

        return 0;
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
                $amount = $this->getAmountForSubscription($subscription);
                $renewalDate = $periodEnd->format('F j, Y');

                $hasCard = $role->hasDefaultPaymentMethod();

                Mail::to($role->user->email)->send(
                    new SubscriptionRenewal($role, $amount, $planLabel, $renewalDate, $hasCard)
                );

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
        $enterpriseMonthly = config('services.stripe_platform.enterprise_price_monthly');
        $enterpriseYearly = config('services.stripe_platform.enterprise_price_yearly');

        if (($enterpriseMonthly && $subscription->hasPrice($enterpriseMonthly)) ||
            ($enterpriseYearly && $subscription->hasPrice($enterpriseYearly))) {
            return 'Enterprise';
        }

        return 'Pro';
    }

    protected function getAmountForSubscription(Subscription $subscription): string
    {
        $priceId = $subscription->stripe_price;

        $priceMap = [
            config('services.stripe_platform.price_monthly') => config('services.stripe_platform.price_monthly_amount'),
            config('services.stripe_platform.price_yearly') => config('services.stripe_platform.price_yearly_amount'),
            config('services.stripe_platform.enterprise_price_monthly') => config('services.stripe_platform.enterprise_price_monthly_amount'),
            config('services.stripe_platform.enterprise_price_yearly') => config('services.stripe_platform.enterprise_price_yearly_amount'),
        ];

        $amount = $priceMap[$priceId] ?? null;

        if ($amount) {
            return '$'.$amount;
        }

        return '';
    }
}
