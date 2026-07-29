<?php

namespace App\Services;

use App\Models\AnalyticsDaily;
use App\Models\Role;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * How much promotable inventory this instance actually has.
 *
 * Exists so the purchase form can tell the advertiser whether the campaign they are about to
 * prepay for can realistically be delivered. What a budget buys is simple arithmetic the form
 * does in JS; the number that arithmetic cannot supply is how much traffic the free tier
 * actually gets, which is what this reads out of the analytics rollups.
 *
 * Derived from actual recent page views on free-tier schedules that have not opted out - not
 * a projection, and deliberately conservative.
 */
class PromotionInventoryService
{
    /**
     * Average daily impressions available across eligible host schedules.
     */
    public function dailyImpressions(int $lookbackDays = 30): int
    {
        return Cache::remember('promo:inventory', 3600, function () use ($lookbackDays) {
            $hostIds = $this->eligibleHostIds();

            if (empty($hostIds)) {
                return 0;
            }

            // Never alias an aggregate to a name that is also a column on the grouped table:
            // MySQL binds the alias to the column and errors 1055.
            $total = AnalyticsDaily::query()
                ->whereIn('role_id', $hostIds)
                ->where('date', '>=', now()->subDays($lookbackDays)->toDateString())
                ->sum(DB::raw('desktop_views + mobile_views + tablet_views + unknown_views'));

            return (int) floor($total / max(1, $lookbackDays));
        });
    }

    /**
     * Free-tier schedules that have not opted out of hosting promotions.
     *
     * Free is expressed as the inverse of the wherePro() scope rather than by calling
     * actualPlanTier() per row - this runs on the purchase form, and a per-row plan check
     * across every schedule on the instance would not be worth an estimate.
     *
     * @return array<int>
     */
    protected function eligibleHostIds(): array
    {
        return Role::query()
            ->where('promotions_opt_out', false)
            ->whereNotNull('user_id')
            ->whereNot(fn ($q) => $q->wherePro())
            ->pluck('id')
            ->all();
    }
}
