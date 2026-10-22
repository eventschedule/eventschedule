<?php

namespace App\Services;

use App\Models\Role;
use Closure;

/**
 * The schedules that have a paid plan's features without a subscription paying for them, as the
 * cards on /admin/schedules count them. (The admin dashboard's "Outside Stripe" line read them
 * here too until 2026-10, when that card was cut down to its two totals.)
 *
 * None of these is revenue. What is being billed is RecurringRevenue's to say.
 */
class AdminPlanCounts
{
    /**
     * A subscription Cashier considers valid: active, on trial, or in the grace period after a
     * cancellation.
     */
    public static function validSubscription(): Closure
    {
        return function ($sq) {
            $sq->where(function ($q) {
                $q->active();
            })->orWhere(function ($q) {
                $q->onTrial();
            })->orWhere(function ($q) {
                $q->onGracePeriod();
            });
        };
    }

    /**
     * The schedules the plan cards count: owned, live, verified, and not the demo.
     *
     * Deleted schedules are not customers, and the list on /admin/schedules excludes them from
     * every state but status=deleted - so counting them here would put these cards at odds with
     * the page they sit on.
     */
    public static function verifiedNonDemo(): Closure
    {
        return function ($query) {
            $query->whereNotNull('user_id')
                ->where('is_deleted', false)
                ->where(function ($q) {
                    $q->whereNotNull('email_verified_at')
                        ->orWhereNotNull('phone_verified_at');
                })
                ->where('subdomain', '!=', DemoService::DEMO_ROLE_SUBDOMAIN)
                ->where('subdomain', 'not like', 'demo-%');
        };
    }

    /**
     * A paid plan somebody granted: an admin, a referral credit, or a legacy plan_expires row.
     * Not free, not expired, no valid subscription, and not a trial.
     */
    public static function manual(): int
    {
        return Role::where(self::verifiedNonDemo())
            ->where('plan_type', '!=', 'free')
            ->whereNotNull('plan_expires')
            ->where('plan_expires', '>=', now()->format('Y-m-d'))
            ->whereDoesntHave('subscriptions', self::validSubscription())
            ->whereNull('trial_ends_at')
            ->count();
    }

    /** A Pro trial started without a card (roles.trial_ends_at), still running. */
    public static function trial(): int
    {
        return Role::where(self::verifiedNonDemo())
            ->whereNotNull('trial_ends_at')
            ->where('trial_ends_at', '>', now())
            ->count();
    }
}
