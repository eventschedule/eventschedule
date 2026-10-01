<?php

namespace App\Services;

use App\Utils\PlanPriceUtils;
use Illuminate\Support\Facades\DB;

/**
 * The one definition of platform recurring revenue: the dashboard's ARR and the growth page's MRR
 * both read summary(), so ARR is always twelve times MRR (to the cent: ARR is the exact sum, MRR
 * is it divided by twelve and rounded).
 *
 * They used to be computed separately and disagreed on almost every edge: one walked subscription
 * rows and the other schedules, one dropped past_due and unverified owners while the other dropped
 * deleted schedules, and one booked an unrecognized price or a plan with no subscription row at a
 * by-tier estimate while the other booked it at zero.
 *
 * What counts is what Stripe is collecting on, read from the subscription rather than from the
 * schedule's plan columns:
 *
 * - active and past_due. past_due is a failed renewal Stripe is still retrying, and the schedule
 *   keeps its tier through it (RoleBillable::hasActiveSubscription()), so it is still revenue
 *   until Stripe gives up and the status moves on.
 * - Cancelled at period end, until ends_at. That period is paid for.
 * - NOT trialing. A trial has not paid anything and may never convert; it is reported as
 *   trialing_count beside the figure instead of inside it.
 * - NOT a paid plan with no live subscription (an admin grant, a referral credit, a legacy
 *   plan_type/plan_expires row). Nothing is billing it, so there is nothing recurring to count.
 *
 * Amounts come from PlanPriceUtils::amountFor(), which reads config and must keep doing so: this is
 * what subscribers are being charged, which a marketing change to PlatformPricing must not restate.
 * A price ID config no longer names contributes zero and is counted in unrecognized_count rather
 * than estimated - guessing the tier is how an Enterprise customer on an old price got booked at
 * the Pro rate. AdminAlertService's subscriptions_unrecognized row and /admin/revenue list them,
 * over a wider population than this class counts (trials and deleted schedules included), so the
 * badge can read higher than unrecognized_count.
 */
class RecurringRevenue
{
    /** Stripe statuses that bill (or are mid-retry), in addition to the trial reported beside them. */
    private const BILLING_STATUSES = ['active', 'past_due'];

    /**
     * @return array{mrr: float, arr: float, billing_count: int, trialing_count: int, unrecognized_count: int}
     */
    public static function summary(): array
    {
        $rows = DB::table('subscriptions')
            ->join('roles', 'roles.id', '=', 'subscriptions.role_id')
            ->where('subscriptions.type', 'default')
            ->whereIn('subscriptions.stripe_status', [...self::BILLING_STATUSES, 'trialing'])
            ->where(fn ($q) => $q->whereNull('subscriptions.ends_at')->orWhere('subscriptions.ends_at', '>', now()))
            // No verification filter: a card being charged is verification enough. A deleted
            // schedule's live subscription is AdminAlertService's subscriptions_orphaned row, to
            // be cancelled and refunded, not revenue that will recur.
            ->whereNotNull('roles.user_id')
            ->where('roles.is_deleted', false)
            ->where('roles.subdomain', '!=', DemoService::DEMO_ROLE_SUBDOMAIN)
            ->where('roles.subdomain', 'not like', 'demo-%')
            ->get(['subscriptions.id', 'subscriptions.stripe_status', 'subscriptions.stripe_price', 'subscriptions.ends_at']);

        // Cashier fills stripe_price only for a single-price subscription. A multi-price one keeps
        // NULL there and its prices on subscription_items, which is where hasPrice() and
        // AdminAlertService::unrecognizedPrice() look too - read only the column and a customer
        // the alert calls recognized would be booked at zero with nothing explaining it.
        $multiPrice = $rows->whereNull('stripe_price')->pluck('id');
        $itemPrices = $multiPrice->isEmpty() ? collect() : DB::table('subscription_items')
            ->whereIn('subscription_id', $multiPrice)
            ->get(['subscription_id', 'stripe_price'])
            ->groupBy('subscription_id');

        $arr = 0.0;
        $billing = 0;
        $trialing = 0;
        $unrecognized = 0;

        foreach ($rows as $row) {
            if ($row->stripe_status === 'trialing') {
                // A cancelled trial keeps status trialing with ends_at at the trial's end (Cashier's
                // cancel()), and can never convert, so it is not pipeline either.
                // RoleBillable::hasActiveSubscription() treats it as inactive for the same reason.
                if ($row->ends_at === null) {
                    $trialing++;
                }

                continue;
            }

            $billing++;

            $prices = $row->stripe_price !== null
                ? [$row->stripe_price]
                : ($itemPrices[$row->id] ?? collect())->pluck('stripe_price')->all();
            $recognized = false;

            foreach ($prices as $price) {
                $amount = PlanPriceUtils::amountFor($price);
                $term = PlanPriceUtils::termFor($price);

                // Both, or neither: annualizing an amount whose term we had to assume is how a
                // yearly price gets counted twelve times over.
                if ($amount === null || $term === null) {
                    continue;
                }

                $recognized = true;
                $arr += $term === 'year' ? $amount : $amount * 12;
            }

            if (! $recognized) {
                $unrecognized++;
            }
        }

        return [
            'mrr' => round($arr / 12, 2),
            'arr' => round($arr, 2),
            'billing_count' => $billing,
            'trialing_count' => $trialing,
            'unrecognized_count' => $unrecognized,
        ];
    }
}
