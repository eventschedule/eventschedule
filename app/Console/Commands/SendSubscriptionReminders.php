<?php

namespace App\Console\Commands;

use App\Jobs\SendQueuedEmail;
use App\Mail\SubscriptionRenewal;
use App\Mail\SubscriptionTrialEnding;
use App\Models\Role;
use App\Services\OneSignalService;
use App\Utils\MoneyUtils;
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

                // Claim the window BEFORE sending, conditionally. The stamp is the only thing
                // stopping a second send, and the two schedulers (routes/console.php and
                // AppController::translateData) hold different mutexes, so a read-then-write can
                // let a concurrent run see the same rows and email everyone twice. A conditional
                // UPDATE is atomic, so exactly one runner claims each row. Same condition the
                // read used: unsent, or last sent longer ago than this window.
                //
                // winddown_reminder_sent_at has its own column for a reason: the Stripe path in
                // sendTrialReminders() reads trial_reminder_sent_at with NO time window, so any
                // value there means "already sent, forever" - stamping that column here would
                // permanently suppress the genuine "your trial ends tomorrow" email.
                //
                // The claim STANDS if the dispatch below throws. Clearing it would let the next
                // tick re-send to someone who may already have the mail; "only ever moves
                // forward" is what makes a double-fired scheduler safe.
                $claimed = Role::where('id', $role->id)
                    ->where(function ($query) use ($daysOut) {
                        $query->whereNull('winddown_reminder_sent_at')
                            ->orWhere('winddown_reminder_sent_at', '<=', now()->subDays($daysOut));
                    })
                    ->update(['winddown_reminder_sent_at' => now()]);

                if ($claimed === 0) {
                    continue;
                }

                try {
                    $isEnterprise = $role->plan_type === 'enterprise';
                    $amount = \App\Utils\PlatformPricing::amount($isEnterprise ? 'enterprise' : 'pro', 'monthly');

                    // Queued, and in the recipient's own language. Inline Mail::to()->send()
                    // was two separate problems: on hosted this command runs inside a web
                    // request (AppController::translateData), and WindDownCompedPlans gives
                    // every addressable role the SAME trial_ends_at, so one daily run would
                    // try to deliver the whole cohort synchronously in one request. A bare
                    // address also renders in the CLI locale, so the he and ro
                    // subscription_winddown_* strings that shipped with this feature went unused.
                    SendQueuedEmail::dispatch(new SubscriptionTrialEnding(
                        $role,
                        // Formatted, like getAmountForSubscription() does for every other caller.
                        // A bare int coerced to "9" and the copy renders it verbatim: "will be
                        // charged 9."
                        plan_price($amount),
                        $isEnterprise ? 'Enterprise' : 'Pro',
                        $role->trial_ends_at->format('F j, Y'),
                        $role->hasDefaultPaymentMethod(),
                        // Wind-down copy: there is no subscription here, so neither "your card
                        // will be charged" nor "add a payment method" is true. The owner has to
                        // start a subscription, and being told otherwise lets the plan lapse
                        // while they believe they have acted.
                        windDown: true,
                    ),
                        $role->user->email,
                        $role->id,
                        $role->user->language_code ?? app()->getLocale()
                    );

                    $this->info("Queued {$daysOut}-day wind-down reminder for {$role->subdomain}.");
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
            // The currency comes off the same Stripe price as the amount, not from our own
            // setting: this line states what the customer is actually charged, and a
            // grandfathered subscriber may well be billed in a currency the platform has
            // since stopped selling in.
            //
            // The divisor is looked up rather than a literal 100. JPY and KRW have no minor
            // unit, so unit_amount is already whole yen - dividing by 100 there understated
            // a renewal by a factor of a hundred.
            $currency = strtoupper($stripeSubscription->items->data[0]->price->currency ?? platform_currency());

            return MoneyUtils::format(
                $unitAmount / MoneyUtils::getSmallestUnitMultiplier($currency),
                $currency
            );
        }

        $amount = PlanPriceUtils::amountFor($subscription->stripe_price);

        if ($amount) {
            return plan_price($amount);
        }

        return '';
    }
}
