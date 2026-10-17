<?php

namespace App\Services;

use App\Models\Role;
use App\Utils\PlatformPricing;
use Illuminate\Support\Carbon;

/**
 * What a referral credit does to the schedule it is spent on (/referrals).
 *
 * One rule, read twice: by the page, which tells the referrer what Apply will do before they press
 * it, and by ReferralController::applyCredit(), which does it. The two used to be one controller
 * branch and a page that said nothing, and the branch set the schedule onto the credit's tier
 * whatever it was on: a Pro credit spent on a schedule with time left on Enterprise moved it down
 * to Pro.
 *
 * A schedule with a live subscription takes the credit as money off its next invoice (the
 * controller posts it to the Stripe balance). Any other schedule gets thirty days of a plan, added
 * to the end of the time it has left, on the higher of the credit's tier and the one it is on.
 */
class ReferralCreditService
{
    /** Days a subscription must last before the credit is earned (app:process-referral-credits). */
    public const DAYS = 30;

    /** Days a sign-up has to subscribe before the referral expires (the same command). */
    public const PENDING_DAYS = 90;

    private const RANK = ['pro' => 1, 'enterprise' => 2];

    /** The credit's tier: a referral recorded without one was a Pro subscription. */
    public static function tier(?string $planType): string
    {
        return $planType === 'enterprise' ? 'enterprise' : 'pro';
    }

    /** What a credit of the tier is worth, from the same source the pricing page quotes. */
    public static function amount(?string $planType): float
    {
        return self::tier($planType) === 'enterprise' ? PlatformPricing::enterpriseMonthly() : PlatformPricing::proMonthly();
    }

    /**
     * @return array{kind: 'invoice'}|array{kind: 'plan', plan: string, until: Carbon, kept: bool}
     */
    public function outcome(?string $creditTier, Role $role): array
    {
        if ($role->hasActiveSubscription()) {
            return ['kind' => 'invoice'];
        }

        $credit = self::tier($creditTier);

        // plan_expires is a string column holding Y-m-d, compared as Role::isPro() compares it.
        $running = $role->plan_expires && $role->plan_expires >= now()->format('Y-m-d');
        $current = $running && isset(self::RANK[$role->plan_type]) ? $role->plan_type : null;
        $plan = $current && self::RANK[$current] > self::RANK[$credit] ? $current : $credit;

        return [
            'kind' => 'plan',
            'plan' => $plan,
            'until' => ($running ? Carbon::parse($role->plan_expires) : now())->startOfDay()->addDays(self::DAYS),
            'kept' => $current === $plan,
        ];
    }

    /** The outcome as one sentence, for the line under the picker and the message after Apply. */
    public function sentence(array $outcome, ?string $creditTier, Role $role): string
    {
        if ($outcome['kind'] === 'invoice') {
            return __('messages.referral_outcome_invoice', [
                'amount' => plan_price(self::amount($creditTier)),
                'schedule' => $role->name,
            ]);
        }

        return __($outcome['kept'] ? 'messages.referral_outcome_stays' : 'messages.referral_outcome_moves', [
            'schedule' => $role->name,
            'plan' => __('messages.'.$outcome['plan']),
            'date' => $outcome['until']->translatedFormat('M j, Y'),
        ]);
    }
}
