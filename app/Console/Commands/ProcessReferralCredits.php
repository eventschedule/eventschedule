<?php

namespace App\Console\Commands;

use App\Mail\ReferralCreditEarned;
use App\Models\Referral;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class ProcessReferralCredits extends Command
{
    protected $signature = 'app:process-referral-credits';

    protected $description = 'Process referral qualifications and expirations';

    public function handle()
    {
        if (! config('app.hosted')) {
            return;
        }

        \Log::info('Processing referral credits...');

        // 1. Expire stale pendings (>90 days with no subscription)
        $expiredPending = Referral::where('status', 'pending')
            ->where('created_at', '<=', now()->subDays(90))
            ->update(['status' => 'expired']);

        \Log::info("Expired {$expiredPending} stale pending referrals");

        // 2. Qualify subscribed referrals (30+ days since subscription, still active)
        $subscribedReferrals = Referral::where('status', 'subscribed')
            ->where('subscribed_at', '<=', now()->subDays(30))
            ->with('referredRole', 'referrer')
            ->get();

        $qualified = 0;
        foreach ($subscribedReferrals as $referral) {
            $role = $referral->referredRole;

            if (! $role) {
                continue;
            }

            $subscription = $role->subscription('default');
            $hasActiveSubscription = $subscription && ($subscription->active() || $subscription->onGracePeriod());

            if ($hasActiveSubscription) {
                $currentTier = $role->plan_type ?? 'pro';
                $referral->update([
                    'plan_type' => $currentTier,
                    'qualified_at' => now(),
                    'status' => 'qualified',
                ]);

                // Send email to referrer
                if ($referral->referrer && $referral->referrer->email) {
                    try {
                        Mail::to($referral->referrer->email)->send(
                            new ReferralCreditEarned($referral)
                        );
                    } catch (\Exception $e) {
                        report($e);
                    }
                }

                $qualified++;
            }
        }

        \Log::info("Qualified {$qualified} referrals");

        // 3. Expire cancelled referrals (subscribed but no longer active)
        $cancelledReferrals = Referral::where('status', 'subscribed')
            ->with('referredRole')
            ->get();

        $expiredCancelled = 0;
        foreach ($cancelledReferrals as $referral) {
            $role = $referral->referredRole;

            if (! $role) {
                $referral->update(['status' => 'expired']);
                $expiredCancelled++;

                continue;
            }

            $subscription = $role->subscription('default');
            $hasActiveSubscription = $subscription && ($subscription->active() || $subscription->onGracePeriod());

            if (! $hasActiveSubscription) {
                $referral->update(['status' => 'expired']);
                $expiredCancelled++;
            }
        }

        \Log::info("Expired {$expiredCancelled} cancelled referrals");
    }
}
