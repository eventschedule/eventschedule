<?php

namespace App\Services;

use App\Models\AnalyticsPromotionsDaily;
use App\Models\BoostCampaign;
use App\Models\PromotionLocationsDaily;
use App\Models\Sale;
use App\Utils\CountryUtils;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Advertiser-facing reporting for one on-network promotion.
 *
 * Separate from AnalyticsService rather than bolted onto it: every method there is scoped to
 * "a user's schedules' web analytics" and takes a User plus a set of role ids. This is scoped
 * to a single campaign the buyer owns - a different axis entirely.
 *
 * MySQL note throughout: never alias an aggregate to a name that is also a column on the
 * table being grouped. The alias binds to the column and the query fails with 1055.
 */
class PromotionAnalyticsService
{
    /**
     * Headline numbers for the campaign dashboard.
     */
    public function summary(BoostCampaign $campaign): array
    {
        $totals = AnalyticsPromotionsDaily::forCampaign($campaign->id)
            ->selectRaw('SUM(impressions) as impression_count, SUM(clicks) as click_count, SUM(unique_visitors) as visitor_count')
            ->first();

        $impressions = (int) ($totals->impression_count ?? 0);
        $clicks = (int) ($totals->click_count ?? 0);
        $spend = $campaign->spentAmount();

        return [
            'impressions' => $impressions,
            'clicks' => $clicks,
            // Named for what it measures. True "reach" needs a raw event log, which this
            // codebase deliberately does not keep.
            'unique_visitors' => (int) ($totals->visitor_count ?? 0),
            'ctr' => $impressions > 0 ? round(($clicks / $impressions) * 100, 2) : 0.0,
            'spend' => $spend,
            'effective_cpc' => $clicks > 0 ? round($spend / $clicks, 2) : 0.0,
            'effective_cpm' => $impressions > 0 ? round(($spend / $impressions) * 1000, 2) : 0.0,
            'budget' => (float) $campaign->user_budget,
            'remaining' => PromotionBillingService::fromMicros($campaign->remainingMicros()),
            'utilization' => $campaign->budget_micros > 0
                ? min(100, round(((int) $campaign->spent_micros / (int) $campaign->budget_micros) * 100, 1))
                : 0.0,
        ];
    }

    /**
     * Daily delivery series for the chart.
     */
    public function dailySeries(BoostCampaign $campaign): Collection
    {
        return AnalyticsPromotionsDaily::forCampaign($campaign->id)
            ->groupBy('date')
            ->orderBy('date')
            ->selectRaw('date, SUM(impressions) as impression_count, SUM(clicks) as click_count')
            ->get()
            ->map(fn ($row) => [
                'date' => $row->date instanceof \DateTimeInterface ? $row->date->format('Y-m-d') : (string) $row->date,
                'impressions' => (int) $row->impression_count,
                'clicks' => (int) $row->click_count,
            ]);
    }

    /**
     * Visitor countries. Country-level only - see PromotionLocationsDaily.
     */
    public function countries(BoostCampaign $campaign, int $limit = 10): Collection
    {
        return PromotionLocationsDaily::forCampaign($campaign->id)
            ->groupBy('country_code')
            ->orderByDesc(DB::raw('SUM(impressions)'))
            ->limit($limit)
            ->selectRaw('country_code, SUM(impressions) as impression_count, SUM(clicks) as click_count')
            ->get()
            ->map(fn ($row) => [
                'code' => $row->country_code,
                'name' => CountryUtils::getName($row->country_code) ?: $row->country_code,
                'impressions' => (int) $row->impression_count,
                'clicks' => (int) $row->click_count,
            ]);
    }

    /**
     * Where the promotion ran, WITHOUT naming the host schedules.
     *
     * A free schedule did not agree to have its traffic volume disclosed to a paying
     * advertiser, and "your ad ran 4,200 times on kellys-bar" is exactly that. The buyer gets
     * the shape of the distribution - how many schedules, and of what kind - which is what
     * they actually need to judge the placement. host_role_id stays in the database for abuse
     * investigation and a possible future revenue share; it is never rendered here.
     */
    public function placementSummary(BoostCampaign $campaign): array
    {
        $rows = AnalyticsPromotionsDaily::forCampaign($campaign->id)
            ->join('roles', 'roles.id', '=', 'analytics_promotions_daily.host_role_id')
            ->groupBy('roles.type')
            ->selectRaw('roles.type as role_type, COUNT(DISTINCT host_role_id) as schedule_count, SUM(impressions) as impression_count')
            ->get();

        return [
            'schedule_count' => (int) $rows->sum('schedule_count'),
            'by_type' => $rows->map(fn ($row) => [
                'type' => $row->role_type,
                'schedules' => (int) $row->schedule_count,
                'impressions' => (int) $row->impression_count,
            ])->all(),
        ];
    }

    /**
     * Ticket sales attributable to this promotion.
     *
     * Works with no extra plumbing because the click URL carries utm_source=boost, which
     * TicketController already maps onto sales.boost_campaign_id.
     */
    public function conversions(BoostCampaign $campaign): array
    {
        $sales = Sale::where('boost_campaign_id', $campaign->id)
            ->where('status', 'paid')
            ->where('is_deleted', false)
            ->get();

        return [
            // Conversions are PURCHASES, not rows. newSaleForLeg() stamps the campaign onto every
            // leg of a cart from the same session UTM, so counting rows read one boosted checkout
            // as several and halved the campaign's apparent cost per conversion. Revenue stays a
            // straight sum: each row's payment_amount is its own share, so it never double-counts.
            'count' => $sales->map(fn ($sale) => $sale->order_id ?: ($sale->group_id ?: $sale->id))->unique()->count(),
            'revenue' => round((float) $sales->sum('payment_amount'), 2),
        ];
    }
}
