<?php

namespace App\Services;

use App\Models\BoostCampaign;
use Illuminate\Support\Facades\DB;

/**
 * Budget accounting for on-network promotions.
 *
 * Money is handled in integer micros (1e-6 of a currency unit) rather than decimals for two
 * reasons: a CPM impression costs a fraction of a cent, so decimal rounding would drift over
 * millions of impressions, and integers let the budget check be a single comparison inside
 * the same UPDATE that spends the budget.
 *
 * That last point is the whole design. Spending is a compare-and-swap, never a
 * read-then-write, so two concurrent impressions cannot both pass a "do we have budget?"
 * check and then both spend it.
 */
class PromotionBillingService
{
    /**
     * Charge one impression. Returns false when the campaign must not be served.
     *
     * CPC campaigns cost nothing to impress, so they short-circuit before touching the
     * database - which is what keeps the render path free of writes for them.
     */
    public function chargeImpression(BoostCampaign $campaign): bool
    {
        $cost = $campaign->impressionCostMicros();

        if ($cost <= 0) {
            // CPC: free to impress, but still only servable while budget remains for the
            // click it is trying to earn.
            return $campaign->remainingMicros() >= $campaign->clickCostMicros();
        }

        return $this->debit($campaign, $cost);
    }

    /**
     * Charge one click. Returns false when the click must not be counted or billed.
     */
    public function chargeClick(BoostCampaign $campaign): bool
    {
        // A CPM click is free, but "free" must not mean "unconditional". The CAS below carries
        // its own channel/status predicates, so without this check the CPM branch was the one
        // path that returned true for a paused, completed, rejected or unapproved campaign -
        // letting an unauthenticated request write rollup rows that reconciliation then
        // persisted onto the campaign as real delivery.
        if (! $campaign->isNetwork() || ! $campaign->isActive()) {
            return false;
        }

        $cost = $campaign->clickCostMicros();

        if ($cost <= 0) {
            // CPM: the impression was already billed, so the click itself costs nothing.
            return true;
        }

        return $this->debit($campaign, $cost);
    }

    /**
     * Atomically spend $cost micros if - and only if - the budget covers it.
     *
     * InnoDB row-locks the UPDATE, so concurrent callers serialize and the second sees the
     * first's spent_micros. Overspend is therefore impossible by construction; the worst
     * case is underspending by less than the cost of one unit.
     *
     * A zero-row result means the campaign is exhausted or no longer active. Marking it
     * completed is a separate guarded UPDATE so it can only fire once.
     */
    protected function debit(BoostCampaign $campaign, int $cost): bool
    {
        // exhausted_at is assigned BEFORE spent_micros deliberately. MySQL evaluates single-table
        // SET assignments left to right, and a later assignment sees the value an earlier one
        // just wrote - so with the order reversed the IF would read the already-incremented
        // spent_micros and test `old + 2*cost >= budget`, retiring the campaign one unit early.
        // (It could never overspend: the WHERE predicate is evaluated against pre-update values.)
        // The exhaustion test is "cannot afford ANOTHER unit after this one", not "exactly
        // finished". A budget that is not a whole multiple of the unit cost - a $5 budget at a
        // $2.37 CPM, say - always ends with a remainder smaller than one impression. Testing
        // only `spent + cost >= budget` never fired for those, so exhausted_at stayed null, the
        // campaign stayed in the candidate pool, and every time it won the weighted roll the
        // charge failed and flushed the shared candidate cache for the whole install.
        $affected = DB::update(
            'UPDATE boost_campaigns
                SET exhausted_at = IF(spent_micros + ? + ? > budget_micros, COALESCE(exhausted_at, NOW()), exhausted_at),
                    spent_micros = spent_micros + ?
              WHERE id = ?
                AND channel = ?
                AND status = ?
                AND budget_micros IS NOT NULL
                AND spent_micros + ? <= budget_micros',
            [$cost, $cost, $cost, $campaign->id, 'network', 'active', $cost]
        );

        if ($affected === 0) {
            $this->completeIfExhausted($campaign);

            return false;
        }

        $campaign->spent_micros = (int) $campaign->spent_micros + $cost;

        return true;
    }

    /**
     * Retire a campaign whose budget is gone.
     *
     * Keyed on exhausted_at, which debit() sets when the campaign can no longer afford another
     * unit - NOT on `spent_micros >= budget_micros`. Those two are not the same thing: a budget
     * that is not a whole multiple of the unit cost always ends with a remainder, so the
     * arithmetic test could never become true and the campaign stayed 'active' forever, kept
     * winning rolls, kept failing the charge, and flushed the install-wide candidate cache on
     * every attempt.
     *
     * Guarded on status so it transitions exactly once per campaign lifetime no matter how
     * many concurrent requests discover the exhaustion at the same moment.
     */
    public function completeIfExhausted(BoostCampaign $campaign): bool
    {
        $affected = DB::update(
            'UPDATE boost_campaigns
                SET status = ?, updated_at = NOW()
              WHERE id = ? AND channel = ? AND status = ?
                AND exhausted_at IS NOT NULL',
            ['completed', $campaign->id, 'network', 'active']
        );

        return $affected > 0;
    }

    /**
     * Convert a currency amount to micros for storage.
     */
    public static function toMicros(float $amount): int
    {
        return (int) round($amount * 1_000_000);
    }

    public static function fromMicros(int $micros): float
    {
        return round($micros / 1_000_000, 2);
    }
}
