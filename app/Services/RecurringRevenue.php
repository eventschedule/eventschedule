<?php

namespace App\Services;

use App\Models\Role;
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

    /** The rows of breakdown(), tier then term. Term is PlanPriceUtils::termFor()'s month|year. */
    public const PLANS = ['pro_month', 'pro_year', 'enterprise_month', 'enterprise_year'];

    /**
     * @return array{mrr: float, arr: float, billing_count: int, trialing_count: int, unrecognized_count: int}
     */
    public static function summary(): array
    {
        $totals = self::breakdown()['totals'];

        return [
            'mrr' => $totals['mrr'],
            'arr' => $totals['arr'],
            'billing_count' => $totals['billing_count'],
            'trialing_count' => $totals['trialing_count'],
            'unrecognized_count' => $totals['unrecognized_count'],
        ];
    }

    /**
     * summary(), split by plan, from the same loop - so the rows add up to the total by
     * construction rather than by two queries happening to agree.
     *
     * Beside what is billing, each row carries what its trials would add if they converted. A
     * trial is priced the same way as a paying subscription and kept out of mrr and arr.
     *
     * Two overlays sit INSIDE the billing figures and are not added to them, or to each other:
     * past due (a renewal Stripe is retrying) and cancelling (paid up to ends_at, then gone).
     * at_risk_* counts a subscription that is either, once.
     *
     * A subscription with several prices is ONE customer, on the row of its highest recognized
     * price (Enterprise before Pro, as hasActiveEnterpriseSubscription() reads it; then the larger
     * amount), carrying all its recognized amounts. Splitting it by price would make rows with
     * money and no customer.
     *
     * Money is added up in annualized cents and divided once. A row's mrr is its share of the total
     * monthly cents by largest remainder: rounding each row on its own can leave the rows a cent
     * off the total when two yearly prices divide unevenly.
     *
     * @return array{
     *     plans: array<string, array{tier: string, term: string, billing_count: int, mrr: float, arr: float, trialing_count: int, trial_mrr: float}>,
     *     unrecognized: array{billing_count: int, trialing_count: int},
     *     totals: array{mrr: float, arr: float, billing_count: int, trialing_count: int, unrecognized_count: int, trial_mrr: float, past_due_count: int, cancelling_count: int, at_risk_count: int, at_risk_mrr: float}
     * }
     */
    public static function breakdown(): array
    {
        $rows = self::liveRows();

        // Cashier fills stripe_price only for a single-price subscription. A multi-price one keeps
        // NULL there and its prices on subscription_items, which is where hasPrice() and
        // AdminAlertService::unrecognizedPrice() look too - read only the column and a customer
        // the alert calls recognized would be booked at zero with nothing explaining it.
        $multiPrice = $rows->whereNull('stripe_price')->pluck('id');
        $itemPrices = $multiPrice->isEmpty() ? collect() : DB::table('subscription_items')
            ->whereIn('subscription_id', $multiPrice)
            ->get(['subscription_id', 'stripe_price'])
            ->groupBy('subscription_id');

        $plans = array_fill_keys(self::PLANS, ['billing_count' => 0, 'cents' => 0, 'trialing_count' => 0, 'trial_cents' => 0]);
        $unrecognized = ['billing_count' => 0, 'trialing_count' => 0];
        $pastDue = 0;
        $cancelling = 0;
        $atRisk = 0;
        $atRiskCents = 0;

        foreach ($rows as $row) {
            $trial = $row->stripe_status === 'trialing';

            // A cancelled trial keeps status trialing with ends_at at the trial's end (Cashier's
            // cancel()), and can never convert, so it is not pipeline either.
            // RoleBillable::hasActiveSubscription() treats it as inactive for the same reason.
            if ($trial && $row->ends_at !== null) {
                continue;
            }

            $prices = $row->stripe_price !== null
                ? [$row->stripe_price]
                : ($itemPrices[$row->id] ?? collect())->pluck('stripe_price')->all();

            [$plan, $cents] = self::place($prices);

            if ($trial) {
                if ($plan === null) {
                    $unrecognized['trialing_count']++;
                } else {
                    $plans[$plan]['trialing_count']++;
                    $plans[$plan]['trial_cents'] += $cents;
                }

                continue;
            }

            if ($plan === null) {
                $unrecognized['billing_count']++;
            } else {
                $plans[$plan]['billing_count']++;
                $plans[$plan]['cents'] += $cents;
            }

            $isPastDue = $row->stripe_status === 'past_due';
            $isCancelling = $row->ends_at !== null;
            $pastDue += (int) $isPastDue;
            $cancelling += (int) $isCancelling;

            if ($isPastDue || $isCancelling) {
                $atRisk++;
                $atRiskCents += $cents;
            }
        }

        $cents = array_sum(array_column($plans, 'cents'));
        $trialCents = array_sum(array_column($plans, 'trial_cents'));
        $monthly = self::apportion(array_column($plans, 'cents'), (int) round($cents / 12));
        $trialMonthly = self::apportion(array_column($plans, 'trial_cents'), (int) round($trialCents / 12));

        $out = [];
        foreach (array_keys($plans) as $index => $key) {
            [$tier, $term] = explode('_', $key);

            $out[$key] = [
                'tier' => $tier,
                'term' => $term,
                'billing_count' => $plans[$key]['billing_count'],
                'mrr' => round($monthly[$index] / 100, 2),
                'arr' => round($plans[$key]['cents'] / 100, 2),
                'trialing_count' => $plans[$key]['trialing_count'],
                'trial_mrr' => round($trialMonthly[$index] / 100, 2),
            ];
        }

        return [
            'plans' => $out,
            'unrecognized' => $unrecognized,
            'totals' => [
                'mrr' => round($cents / 1200, 2),
                'arr' => round($cents / 100, 2),
                'billing_count' => array_sum(array_column($plans, 'billing_count')) + $unrecognized['billing_count'],
                'trialing_count' => array_sum(array_column($plans, 'trialing_count')) + $unrecognized['trialing_count'],
                // Billing only, as it has always been: a trial on an unrecognized price is counted
                // in trialing_count and nowhere else.
                'unrecognized_count' => $unrecognized['billing_count'],
                'trial_mrr' => round($trialCents / 1200, 2),
                'past_due_count' => $pastDue,
                'cancelling_count' => $cancelling,
                'at_risk_count' => $atRisk,
                'at_risk_mrr' => round($atRiskCents / 1200, 2),
            ],
        ];
    }

    /**
     * The row a subscription belongs on and what it is worth a year, in cents: [null, 0] when none
     * of its prices is one config names.
     *
     * Both amount and term, or neither: annualizing an amount whose term we had to assume is how a
     * yearly price gets counted twelve times over.
     *
     * @param  array<int, ?string>  $prices
     * @return array{0: ?string, 1: int}
     */
    private static function place(array $prices): array
    {
        $best = null;
        $bestRank = null;
        $cents = 0;

        foreach ($prices as $price) {
            $amount = PlanPriceUtils::amountFor($price);
            $term = PlanPriceUtils::termFor($price);
            $tier = PlanPriceUtils::tierFor($price);

            if ($amount === null || $term === null || $tier === null) {
                continue;
            }

            $annual = (int) round(($term === 'year' ? $amount : $amount * 12) * 100);
            $cents += $annual;
            $rank = [$tier === 'enterprise' ? 1 : 0, $annual, $term === 'year' ? 1 : 0];

            if ($bestRank === null || $rank > $bestRank) {
                $bestRank = $rank;
                $best = $tier.'_'.$term;
            }
        }

        return [$best, $best === null ? 0 : $cents];
    }

    /**
     * Each row's monthly cents, as whole cents that add up to $total: floor every share, then hand
     * the leftover cents to the largest remainders.
     *
     * @param  array<int, int>  $annual  annualized cents per row
     * @return array<int, int>
     */
    private static function apportion(array $annual, int $total): array
    {
        $shares = array_map(fn (int $cents) => intdiv($cents, 12), $annual);
        $remainders = array_map(fn (int $cents) => $cents % 12, $annual);
        $left = $total - array_sum($shares);

        arsort($remainders);
        foreach (array_keys($remainders) as $index) {
            if ($left <= 0) {
                break;
            }

            $shares[$index]++;
            $left--;
        }

        return $shares;
    }

    /**
     * The schedules summary() counts as billing: a live subscription that is not a trial. Whether
     * its price is recognized does not matter here - an unrecognized price is still a customer
     * being charged, it only contributes zero to the amount.
     *
     * This is what the growth payload means by a PAYING schedule. actualPlanTier() cannot answer
     * that: it returns pro for admin grants, referral credits, legacy plan_expires rows and trials,
     * which made the old "paid vs free" comparisons mostly comped-vs-free.
     *
     * @return array<int, true> role_id => true
     */
    public static function billingRoleIds(): array
    {
        return self::liveRows()
            ->where('stripe_status', '!=', 'trialing')
            ->pluck('role_id')
            ->mapWithKeys(fn ($id) => [(int) $id => true])
            ->all();
    }

    private static function liveRows(): \Illuminate\Support\Collection
    {
        return DB::table('subscriptions')
            ->join('roles', 'roles.id', '=', 'subscriptions.role_id')
            ->where('subscriptions.type', 'default')
            ->whereIn('subscriptions.stripe_status', [...self::BILLING_STATUSES, 'trialing'])
            ->where(fn ($q) => $q->whereNull('subscriptions.ends_at')->orWhere('subscriptions.ends_at', '>', now()))
            // No verification filter: a card being charged is verification enough. A deleted
            // schedule's live subscription is AdminAlertService's subscriptions_orphaned row, to
            // be cancelled and refunded, not revenue that will recur.
            ->whereNotNull('roles.user_id')
            ->where('roles.is_deleted', false)
            // Demo CONTENT, not the `demo-%` subdomain shape: a real schedule named before the
            // prefix was reserved (a "Demo Night" got `demo-night`) is a real customer, and its
            // subscription is real revenue. Every arm is null-safe, so whereNot() keeps rows with
            // no contact email - see Role::constrainDemoContent().
            ->whereNot(fn ($q) => Role::constrainDemoContent($q))
            ->get(['subscriptions.id', 'subscriptions.role_id', 'subscriptions.stripe_status',
                'subscriptions.stripe_price', 'subscriptions.ends_at']);
    }
}
