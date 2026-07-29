<?php

namespace App\Services;

use App\Models\BoostCampaign;
use App\Models\Role;

/**
 * Review policy for on-network promotions.
 *
 * A paid schedule buying placement in front of every free schedule's audience is the main
 * abuse surface in this feature, so campaigns are approve-before-serve. The trust rule
 * exists so the queue stays useful: without it every repeat advertiser re-queues forever
 * and the operator stops looking at it.
 *
 * Kept in one place because two very different paths need the same answer - the purchase
 * flow, and the stale-payment recovery in SyncBoostCampaigns.
 */
class PromotionModerationService
{
    /**
     * The status a freshly-paid campaign should take.
     */
    public static function activationStatusFor(BoostCampaign $campaign): string
    {
        return self::autoApproves($campaign->role) ? 'active' : 'pending_review';
    }

    public static function moderationStatusFor(BoostCampaign $campaign): string
    {
        return self::autoApproves($campaign->role) ? 'approved' : 'pending';
    }

    /**
     * Whether this schedule has earned a pass on manual review.
     *
     * Deliberately strict: a single rejection ever puts a schedule back in the queue
     * permanently, because the cost of re-reviewing a known-good advertiser is far lower
     * than the cost of auto-approving one that has already broken the rules once.
     */
    public static function autoApproves(?Role $role): bool
    {
        if (! $role) {
            return false;
        }

        $threshold = (int) config('ads.native_auto_approve_after', 3);

        if ($threshold <= 0) {
            return false;
        }

        // Trust is earned by campaigns that actually RAN, not by ones that were merely
        // approved. Counting approvals alone made the ladder free to climb: buy three
        // minimum-budget campaigns with innocuous creative, wait for approval, cancel each for
        // a full refund (cancel writes only `status`, never `moderation_status`), and the
        // fourth purchase skips review entirely at a net cost of nothing.
        //
        // Delivery is measured with `impressions`, not `spent_micros`: a CPC campaign is not
        // charged for impressions at all, so a legitimate one that earned no clicks has zero
        // spend and would otherwise never count.
        $counts = BoostCampaign::network()
            ->where('role_id', $role->id)
            ->selectRaw("SUM(moderation_status = 'approved' AND status = 'completed' AND impressions > 0) as approved_count")
            ->selectRaw("SUM(moderation_status = 'rejected') as rejected_count")
            ->first();

        if (! $counts || (int) $counts->rejected_count > 0) {
            return false;
        }

        return (int) $counts->approved_count >= $threshold;
    }
}
