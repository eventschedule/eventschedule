<?php

namespace App\Services;

use App\Models\AnalyticsAppearancesDaily;
use App\Models\AnalyticsDaily;
use App\Models\AnalyticsEventsDaily;
use App\Models\AnalyticsReferrersDaily;
use App\Models\BoostCampaign;
use App\Models\Event;
use App\Models\Newsletter;
use App\Models\PageView;
use App\Models\PromoCode;
use App\Models\Role;
use App\Models\Sale;
use App\Models\User;
use App\Utils\UrlUtils;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    /**
     * Record a page view (returns false if bot detected)
     */
    public function recordView(Role $role, ?Event $event, Request $request): bool
    {
        return PageView::recordView($role, $event, $request);
    }

    /**
     * Get statistics for a user's schedules
     */
    public function getStatsForUser(User $user, Carbon $start, Carbon $end): array
    {
        $roleIds = $this->getUserRoleIds($user);

        if ($roleIds->isEmpty()) {
            return $this->emptyStats();
        }

        $totalViews = $this->getTotalViewsForRoles($roleIds);
        $periodViews = $this->getPeriodViewsForRoles($roleIds, $start, $end);

        return [
            'total_views' => $totalViews,
            'period_views' => $periodViews,
        ];
    }

    /**
     * Get statistics for a specific role
     */
    public function getStatsForRole(Role $role, Carbon $start, Carbon $end): array
    {
        $totalViews = $this->getTotalViewsForRoles(collect([$role->id]));
        $periodViews = $this->getPeriodViewsForRoles(collect([$role->id]), $start, $end);

        return [
            'total_views' => $totalViews,
            'period_views' => $periodViews,
        ];
    }

    /**
     * Get top events by view count
     */
    public function getTopEvents(User $user, int $limit, Carbon $start, Carbon $end): Collection
    {
        $roleIds = $this->getUserRoleIds($user);

        if ($roleIds->isEmpty()) {
            return collect();
        }

        // Get event IDs that belong to user's roles (via pivot table)
        $eventIds = DB::table('event_role')
            ->whereIn('role_id', $roleIds)
            ->pluck('event_id')
            ->unique();

        if ($eventIds->isEmpty()) {
            return collect();
        }

        return AnalyticsEventsDaily::select(
            'event_id',
            DB::raw('SUM(desktop_views + mobile_views + tablet_views + unknown_views) as view_count')
        )
            ->forEvents($eventIds)
            ->inDateRange($start, $end)
            ->groupBy('event_id')
            ->orderByDesc('view_count')
            ->limit($limit)
            ->with('event')
            ->get()
            ->filter(fn ($item) => $item->event !== null)
            ->map(fn ($item) => [
                'event' => $item->event,
                'view_count' => (int) $item->view_count,
            ]);
    }

    /**
     * Get views grouped by period (daily, weekly, monthly)
     */
    public function getViewsByPeriod(User $user, string $period, Carbon $start, Carbon $end, ?int $roleId = null): Collection
    {
        $roleIds = $roleId ? collect([$roleId]) : $this->getUserRoleIds($user);

        if ($roleIds->isEmpty()) {
            return collect();
        }

        // Use whitelisted format expressions to prevent SQL injection
        // The period parameter comes from user input, so we must validate it
        $dateFormatExpr = match ($period) {
            'daily' => DB::raw("DATE_FORMAT(date, '%Y-%m-%d') as period"),
            'weekly' => DB::raw("DATE_FORMAT(date, '%x-%v') as period"),
            'monthly' => DB::raw("DATE_FORMAT(date, '%Y-%m') as period"),
            default => DB::raw("DATE_FORMAT(date, '%Y-%m-%d') as period"),
        };

        return AnalyticsDaily::select(
            $dateFormatExpr,
            DB::raw('SUM(desktop_views + mobile_views + tablet_views + unknown_views) as view_count')
        )
            ->forRoles($roleIds)
            ->inDateRange($start, $end)
            ->groupBy('period')
            ->orderBy('period')
            ->get();
    }

    /**
     * Get month-over-month comparison
     */
    public function getMonthOverMonthComparison(User $user, ?int $roleId = null): array
    {
        $roleIds = $roleId ? collect([$roleId]) : $this->getUserRoleIds($user);

        if ($roleIds->isEmpty()) {
            return [
                'this_month' => 0,
                'last_month' => 0,
                'percentage_change' => 0,
            ];
        }

        $thisMonthStart = now()->startOfMonth();
        $thisMonthEnd = now()->endOfMonth();
        $lastMonthStart = now()->subMonth()->startOfMonth();
        $lastMonthEnd = now()->subMonth()->endOfMonth();

        $thisMonthViews = $this->getPeriodViewsForRoles($roleIds, $thisMonthStart, $thisMonthEnd);
        $lastMonthViews = $this->getPeriodViewsForRoles($roleIds, $lastMonthStart, $lastMonthEnd);

        $percentageChange = $lastMonthViews > 0
            ? round((($thisMonthViews - $lastMonthViews) / $lastMonthViews) * 100, 1)
            : ($thisMonthViews > 0 ? 100 : 0);

        return [
            'this_month' => $thisMonthViews,
            'last_month' => $lastMonthViews,
            'percentage_change' => $percentageChange,
        ];
    }

    /**
     * Get period comparison based on date range
     */
    public function getPeriodComparison(User $user, string $range, Carbon $start, Carbon $end, ?int $roleId = null): array
    {
        $roleIds = $roleId ? collect([$roleId]) : $this->getUserRoleIds($user);

        if ($roleIds->isEmpty()) {
            return [
                'current_period' => 0,
                'previous_period' => 0,
                'percentage_change' => 0,
                'comparison_label' => '',
            ];
        }

        // Calculate previous period based on range
        [$previousStart, $previousEnd, $label] = match ($range) {
            'last_7_days' => [
                now()->subDays(14)->startOfDay(),
                now()->subDays(8)->endOfDay(),
                'vs_previous_7_days',
            ],
            'last_30_days' => [
                now()->subDays(60)->startOfDay(),
                now()->subDays(31)->endOfDay(),
                'vs_previous_30_days',
            ],
            'last_90_days' => [
                now()->subDays(180)->startOfDay(),
                now()->subDays(91)->endOfDay(),
                'vs_previous_90_days',
            ],
            'this_month' => [
                now()->subMonth()->startOfMonth(),
                now()->subMonth()->endOfMonth(),
                'vs_last_month',
            ],
            'last_month' => [
                now()->subMonths(2)->startOfMonth(),
                now()->subMonths(2)->endOfMonth(),
                'vs_previous_month',
            ],
            'this_year' => [
                now()->subYear()->startOfYear(),
                now()->subYear()->endOfYear(),
                'vs_last_year',
            ],
            default => [
                now()->subDays(60)->startOfDay(),
                now()->subDays(31)->endOfDay(),
                'vs_previous_30_days',
            ],
        };

        $currentViews = $this->getPeriodViewsForRoles($roleIds, $start, $end);
        $previousViews = $this->getPeriodViewsForRoles($roleIds, $previousStart, $previousEnd);

        $percentageChange = $previousViews > 0
            ? round((($currentViews - $previousViews) / $previousViews) * 100, 1)
            : ($currentViews > 0 ? 100 : 0);

        return [
            'current_period' => $currentViews,
            'previous_period' => $previousViews,
            'percentage_change' => $percentageChange,
            'comparison_label' => $label,
        ];
    }

    /**
     * Get device breakdown
     */
    public function getDeviceBreakdown(User $user, Carbon $start, Carbon $end, ?int $roleId = null): Collection
    {
        $roleIds = $roleId ? collect([$roleId]) : $this->getUserRoleIds($user);

        if ($roleIds->isEmpty()) {
            return collect();
        }

        $result = AnalyticsDaily::select(
            DB::raw('SUM(desktop_views) as desktop'),
            DB::raw('SUM(mobile_views) as mobile'),
            DB::raw('SUM(tablet_views) as tablet'),
            DB::raw('SUM(unknown_views) as unknown')
        )
            ->forRoles($roleIds)
            ->inDateRange($start, $end)
            ->first();

        if (! $result) {
            return collect();
        }

        return collect([
            'desktop' => (int) $result->desktop,
            'mobile' => (int) $result->mobile,
            'tablet' => (int) $result->tablet,
            'unknown' => (int) $result->unknown,
        ])->filter(fn ($count) => $count > 0);
    }

    /**
     * Get views by schedule (role)
     */
    public function getViewsBySchedule(User $user, Carbon $start, Carbon $end, ?int $roleId = null): Collection
    {
        $roleIds = $roleId ? collect([$roleId]) : $this->getUserRoleIds($user);

        if ($roleIds->isEmpty()) {
            return collect();
        }

        return AnalyticsDaily::select(
            'role_id',
            DB::raw('SUM(desktop_views + mobile_views + tablet_views + unknown_views) as view_count')
        )
            ->forRoles($roleIds)
            ->inDateRange($start, $end)
            ->groupBy('role_id')
            ->with('role')
            ->get()
            ->filter(fn ($item) => $item->role !== null)
            ->map(fn ($item) => [
                'role' => $item->role,
                'view_count' => (int) $item->view_count,
            ]);
    }

    /**
     * Get total views for given roles (all time)
     */
    public function getTotalViewsForRoles(Collection $roleIds): int
    {
        return (int) AnalyticsDaily::forRoles($roleIds)
            ->sum(DB::raw('desktop_views + mobile_views + tablet_views + unknown_views'));
    }

    /**
     * Get views for given roles in a date range
     */
    protected function getPeriodViewsForRoles(Collection $roleIds, Carbon $start, Carbon $end): int
    {
        return (int) AnalyticsDaily::forRoles($roleIds)
            ->inDateRange($start, $end)
            ->sum(DB::raw('desktop_views + mobile_views + tablet_views + unknown_views'));
    }

    /**
     * Get the role IDs that a user owns/manages (excluding followers)
     */
    protected function getUserRoleIds(User $user): Collection
    {
        return $user->roles()
            ->wherePivot('level', '!=', 'follower')
            ->pluck('roles.id');
    }

    /**
     * Return empty stats structure
     */
    protected function emptyStats(): array
    {
        return [
            'total_views' => 0,
            'period_views' => 0,
        ];
    }

    /**
     * Get top talents/venues by appearance views on a specific schedule
     */
    public function getTopAppearancesForSchedule(Role $schedule, int $limit, Carbon $start, Carbon $end): Collection
    {
        return AnalyticsAppearancesDaily::select(
            'role_id',
            DB::raw('SUM(desktop_views + mobile_views + tablet_views + unknown_views) as view_count')
        )
            ->forSchedule($schedule->id)
            ->inDateRange($start, $end)
            ->groupBy('role_id')
            ->orderByDesc('view_count')
            ->limit($limit)
            ->with('role')
            ->get()
            ->filter(fn ($item) => $item->role !== null)
            ->map(fn ($item) => [
                'role' => $item->role,
                'view_count' => (int) $item->view_count,
            ]);
    }

    /**
     * Get total appearance views on a schedule
     */
    public function getTotalAppearanceViewsForSchedule(Role $schedule, Carbon $start, Carbon $end): int
    {
        return (int) AnalyticsAppearancesDaily::forSchedule($schedule->id)
            ->inDateRange($start, $end)
            ->sum(DB::raw('desktop_views + mobile_views + tablet_views + unknown_views'));
    }

    /**
     * Get appearance views by period for a specific schedule
     */
    public function getAppearanceViewsByPeriod(Role $schedule, string $period, Carbon $start, Carbon $end): Collection
    {
        $dateFormat = match ($period) {
            'daily' => '%Y-%m-%d',
            'weekly' => '%x-%v',  // ISO year and week
            'monthly' => '%Y-%m',
            default => '%Y-%m-%d',
        };

        return AnalyticsAppearancesDaily::select(
            DB::raw("DATE_FORMAT(date, '{$dateFormat}') as period"),
            DB::raw('SUM(desktop_views + mobile_views + tablet_views + unknown_views) as view_count')
        )
            ->forSchedule($schedule->id)
            ->inDateRange($start, $end)
            ->groupBy('period')
            ->orderBy('period')
            ->get();
    }

    /**
     * Get total appearance views for a talent/venue (views from appearing on other schedules)
     */
    public function getTotalAppearanceViewsForRole(int $roleId, Carbon $start, Carbon $end): int
    {
        return (int) AnalyticsAppearancesDaily::forRole($roleId)
            ->inDateRange($start, $end)
            ->sum(DB::raw('desktop_views + mobile_views + tablet_views + unknown_views'));
    }

    /**
     * Get schedules where this talent/venue appeared and received views
     */
    public function getAppearancesByScheduleForRole(int $roleId, int $limit, Carbon $start, Carbon $end): Collection
    {
        return AnalyticsAppearancesDaily::select(
            'schedule_role_id',
            DB::raw('SUM(desktop_views + mobile_views + tablet_views + unknown_views) as view_count')
        )
            ->forRole($roleId)
            ->inDateRange($start, $end)
            ->groupBy('schedule_role_id')
            ->orderByDesc('view_count')
            ->limit($limit)
            ->with('scheduleRole')
            ->get()
            ->filter(fn ($item) => $item->scheduleRole !== null)
            ->map(fn ($item) => [
                'role' => $item->scheduleRole,
                'view_count' => (int) $item->view_count,
            ]);
    }

    /**
     * Get conversion statistics (views to sales)
     */
    public function getConversionStats(User $user, Carbon $start, Carbon $end, ?int $roleId = null): array
    {
        $roleIds = $roleId ? collect([$roleId]) : $this->getUserRoleIds($user);

        if ($roleIds->isEmpty()) {
            return [
                'total_views' => 0,
                'total_sales' => 0,
                'conversion_rate' => 0,
                'total_revenue' => 0,
                'revenue_per_view' => 0,
                'promo_sales' => 0,
                'promo_discounts' => 0,
            ];
        }

        // Get event IDs that belong to user's roles
        $eventIds = DB::table('event_role')
            ->whereIn('role_id', $roleIds)
            ->pluck('event_id')
            ->unique();

        if ($eventIds->isEmpty()) {
            return [
                'total_views' => 0,
                'total_sales' => 0,
                'conversion_rate' => 0,
                'total_revenue' => 0,
                'revenue_per_view' => 0,
                'promo_sales' => 0,
                'promo_discounts' => 0,
            ];
        }

        $stats = AnalyticsEventsDaily::select(
            DB::raw('SUM(desktop_views + mobile_views + tablet_views + unknown_views) as total_views'),
            DB::raw('SUM(sales_count) as total_sales'),
            DB::raw('SUM(revenue) as total_revenue'),
            DB::raw('SUM(promo_sales_count) as promo_sales'),
            DB::raw('SUM(promo_discount_total) as promo_discounts')
        )
            ->forEvents($eventIds)
            ->inDateRange($start, $end)
            ->first();

        $totalViews = (int) ($stats->total_views ?? 0);
        $totalSales = (int) ($stats->total_sales ?? 0);
        $totalRevenue = (float) ($stats->total_revenue ?? 0);
        $promoSales = (int) ($stats->promo_sales ?? 0);
        $promoDiscounts = (float) ($stats->promo_discounts ?? 0);

        return [
            'total_views' => $totalViews,
            'total_sales' => $totalSales,
            'conversion_rate' => $totalViews > 0 ? round(($totalSales / $totalViews) * 100, 2) : 0,
            'total_revenue' => $totalRevenue,
            'revenue_per_view' => $totalViews > 0 ? round($totalRevenue / $totalViews, 2) : 0,
            'promo_sales' => $promoSales,
            'promo_discounts' => $promoDiscounts,
        ];
    }

    /**
     * Get top events by revenue
     */
    public function getTopEventsByRevenue(User $user, int $limit, Carbon $start, Carbon $end): Collection
    {
        $roleIds = $this->getUserRoleIds($user);

        if ($roleIds->isEmpty()) {
            return collect();
        }

        // Get event IDs that belong to user's roles
        $eventIds = DB::table('event_role')
            ->whereIn('role_id', $roleIds)
            ->pluck('event_id')
            ->unique();

        if ($eventIds->isEmpty()) {
            return collect();
        }

        return AnalyticsEventsDaily::select(
            'event_id',
            DB::raw('SUM(desktop_views + mobile_views + tablet_views + unknown_views) as view_count'),
            DB::raw('SUM(sales_count) as sales_count'),
            DB::raw('SUM(revenue) as revenue')
        )
            ->forEvents($eventIds)
            ->inDateRange($start, $end)
            ->groupBy('event_id')
            ->having('revenue', '>', 0)
            ->orderByDesc('revenue')
            ->limit($limit)
            ->with('event')
            ->get()
            ->filter(fn ($item) => $item->event !== null)
            ->map(fn ($item) => [
                'event' => $item->event,
                'view_count' => (int) $item->view_count,
                'sales_count' => (int) $item->sales_count,
                'revenue' => (float) $item->revenue,
                'conversion_rate' => $item->view_count > 0 ? round(($item->sales_count / $item->view_count) * 100, 2) : 0,
            ]);
    }

    /**
     * Get traffic sources breakdown
     */
    public function getTrafficSources(User $user, Carbon $start, Carbon $end, ?int $roleId = null): Collection
    {
        $roleIds = $roleId ? collect([$roleId]) : $this->getUserRoleIds($user);

        if ($roleIds->isEmpty()) {
            return collect();
        }

        return AnalyticsReferrersDaily::select(
            'source',
            DB::raw('SUM(views) as view_count')
        )
            ->forRoles($roleIds)
            ->inDateRange($start, $end)
            ->groupBy('source')
            ->orderByDesc('view_count')
            ->get()
            ->map(fn ($item) => [
                'source' => $item->source,
                'view_count' => (int) $item->view_count,
            ]);
    }

    /**
     * Get top referrer domains
     */
    public function getTopReferrerDomains(User $user, int $limit, Carbon $start, Carbon $end, ?int $roleId = null): Collection
    {
        $roleIds = $roleId ? collect([$roleId]) : $this->getUserRoleIds($user);

        if ($roleIds->isEmpty()) {
            return collect();
        }

        return AnalyticsReferrersDaily::select(
            'domain',
            'source',
            DB::raw('SUM(views) as view_count')
        )
            ->forRoles($roleIds)
            ->inDateRange($start, $end)
            ->whereNotNull('domain')
            ->where('domain', '!=', '')
            ->groupBy('domain', 'source')
            ->orderByDesc('view_count')
            ->limit($limit)
            ->get()
            ->map(fn ($item) => [
                'domain' => $item->domain,
                'source' => $item->source,
                'view_count' => (int) $item->view_count,
            ]);
    }

    /**
     * Get boost-attributed page views for given roles in a date range
     */
    public function getBoostAttributedViews(Collection $roleIds, Carbon $start, Carbon $end): int
    {
        return (int) AnalyticsReferrersDaily::forRoles($roleIds)
            ->inDateRange($start, $end)
            ->bySource('boost')
            ->sum('views');
    }

    /**
     * Get boost-attributed sales and revenue for given roles in a date range
     */
    public function getBoostSalesStats(Collection $roleIds, Carbon $start, Carbon $end): array
    {
        $eventIds = DB::table('event_role')
            ->whereIn('role_id', $roleIds)
            ->pluck('event_id')
            ->unique();

        if ($eventIds->isEmpty()) {
            return ['sales' => 0, 'revenue' => 0];
        }

        $result = Sale::whereIn('event_id', $eventIds)
            ->whereNotNull('boost_campaign_id')
            ->where('status', 'paid')
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('COUNT(*) as sales_count, COALESCE(SUM(payment_amount), 0) as total_revenue')
            ->first();

        return [
            'sales' => (int) ($result->sales_count ?? 0),
            'revenue' => (float) ($result->total_revenue ?? 0),
        ];
    }

    /**
     * Get boost views grouped by period for chart overlay
     */
    public function getBoostViewsByPeriod(User $user, string $period, Carbon $start, Carbon $end, ?int $roleId = null): Collection
    {
        $roleIds = $roleId ? collect([$roleId]) : $this->getUserRoleIds($user);

        if ($roleIds->isEmpty()) {
            return collect();
        }

        $dateFormatExpr = match ($period) {
            'daily' => DB::raw("DATE_FORMAT(date, '%Y-%m-%d') as period"),
            'weekly' => DB::raw("DATE_FORMAT(date, '%x-%v') as period"),
            'monthly' => DB::raw("DATE_FORMAT(date, '%Y-%m') as period"),
            default => DB::raw("DATE_FORMAT(date, '%Y-%m-%d') as period"),
        };

        return AnalyticsReferrersDaily::select(
            $dateFormatExpr,
            DB::raw('SUM(views) as view_count')
        )
            ->forRoles($roleIds)
            ->inDateRange($start, $end)
            ->bySource('boost')
            ->groupBy('period')
            ->orderBy('period')
            ->get();
    }

    /**
     * Get boost campaign stats for the analytics dashboard
     */
    public function getBoostStats(User $user, Carbon $start, Carbon $end, ?int $roleId = null): array
    {
        $roleIds = $roleId ? collect([$roleId]) : $this->getUserRoleIds($user);

        if ($roleIds->isEmpty()) {
            return ['has_data' => false];
        }

        $campaigns = BoostCampaign::with('event:id,name')
            ->whereIn('role_id', $roleIds)
            ->whereIn('status', ['active', 'paused', 'completed'])
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('created_at', [$start, $end])
                    ->orWhere(function ($q2) use ($start, $end) {
                        $q2->where('scheduled_start', '<=', $end)
                            ->where(function ($q3) use ($start) {
                                $q3->whereNull('scheduled_end')
                                    ->orWhere('scheduled_end', '>=', $start);
                            });
                    });
            })
            ->orderByDesc('created_at')
            ->get();

        if ($campaigns->isEmpty()) {
            return ['has_data' => false];
        }

        $totalSpend = $campaigns->sum('actual_spend') ?? 0;
        $totalBudget = $campaigns->sum('user_budget') ?? 0;
        $totalImpressions = $campaigns->sum('impressions') ?? 0;
        $totalClicks = $campaigns->sum('clicks') ?? 0;
        $totalConversions = $campaigns->sum('conversions') ?? 0;

        $avgCtr = $totalImpressions > 0 ? round(($totalClicks / $totalImpressions) * 100, 2) : 0;
        $avgCpc = $totalClicks > 0 ? round($totalSpend / $totalClicks, 2) : 0;

        // Boost-attributed page views and sales
        $boostViews = $this->getBoostAttributedViews($roleIds, $start, $end);
        $boostSalesStats = $this->getBoostSalesStats($roleIds, $start, $end);

        $costPerView = $boostViews > 0 ? round((float) $totalSpend / $boostViews, 2) : 0;
        $costPerSale = $boostSalesStats['sales'] > 0 ? round((float) $totalSpend / $boostSalesStats['sales'], 2) : 0;
        $roas = (float) $totalSpend > 0 ? round($boostSalesStats['revenue'] / (float) $totalSpend, 2) : 0;

        return [
            'has_data' => true,
            'total_spend' => (float) $totalSpend,
            'total_budget' => (float) $totalBudget,
            'total_impressions' => (int) $totalImpressions,
            'total_clicks' => (int) $totalClicks,
            'total_conversions' => (int) $totalConversions,
            'avg_ctr' => $avgCtr,
            'avg_cpc' => $avgCpc,
            'boost_views' => $boostViews,
            'boost_sales' => $boostSalesStats['sales'],
            'boost_revenue' => $boostSalesStats['revenue'],
            'cost_per_view' => $costPerView,
            'cost_per_sale' => $costPerSale,
            'roas' => $roas,
            'campaigns' => $campaigns->map(fn ($c) => [
                'hash' => $c->hashedId(),
                'name' => $c->name,
                'event_name' => $c->event?->translatedName() ?? 'N/A',
                'status' => $c->status,
                'spend' => (float) ($c->actual_spend ?? 0),
                'impressions' => (int) ($c->impressions ?? 0),
                'clicks' => (int) ($c->clicks ?? 0),
            ])->toArray(),
        ];
    }

    /**
     * Get newsletter-attributed page views for given roles in a date range
     */
    public function getNewsletterAttributedViews(Collection $roleIds, Carbon $start, Carbon $end): int
    {
        return (int) AnalyticsReferrersDaily::forRoles($roleIds)
            ->inDateRange($start, $end)
            ->bySource('newsletter')
            ->sum('views');
    }

    /**
     * Get newsletter-attributed sales and revenue for given roles in a date range
     */
    public function getNewsletterSalesStats(Collection $roleIds, Carbon $start, Carbon $end): array
    {
        $eventIds = DB::table('event_role')
            ->whereIn('role_id', $roleIds)
            ->pluck('event_id')
            ->unique();

        if ($eventIds->isEmpty()) {
            return ['sales' => 0, 'revenue' => 0];
        }

        $result = Sale::whereIn('event_id', $eventIds)
            ->whereNotNull('newsletter_id')
            ->where('status', 'paid')
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('COUNT(*) as sales_count, COALESCE(SUM(payment_amount), 0) as total_revenue')
            ->first();

        return [
            'sales' => (int) ($result->sales_count ?? 0),
            'revenue' => (float) ($result->total_revenue ?? 0),
        ];
    }

    /**
     * Get newsletter views grouped by period for chart overlay
     */
    public function getNewsletterViewsByPeriod(User $user, string $period, Carbon $start, Carbon $end, ?int $roleId = null): Collection
    {
        $roleIds = $roleId ? collect([$roleId]) : $this->getUserRoleIds($user);

        if ($roleIds->isEmpty()) {
            return collect();
        }

        $dateFormatExpr = match ($period) {
            'daily' => DB::raw("DATE_FORMAT(date, '%Y-%m-%d') as period"),
            'weekly' => DB::raw("DATE_FORMAT(date, '%x-%v') as period"),
            'monthly' => DB::raw("DATE_FORMAT(date, '%Y-%m') as period"),
            default => DB::raw("DATE_FORMAT(date, '%Y-%m-%d') as period"),
        };

        return AnalyticsReferrersDaily::select(
            $dateFormatExpr,
            DB::raw('SUM(views) as view_count')
        )
            ->forRoles($roleIds)
            ->inDateRange($start, $end)
            ->bySource('newsletter')
            ->groupBy('period')
            ->orderBy('period')
            ->get();
    }

    /**
     * Get per-promo-code usage breakdown
     */
    public function getPromoCodeStats(User $user, Carbon $start, Carbon $end, ?int $roleId = null): Collection
    {
        $roleIds = $roleId ? collect([$roleId]) : $this->getUserRoleIds($user);

        if ($roleIds->isEmpty()) {
            return collect();
        }

        $eventIds = DB::table('event_role')
            ->whereIn('role_id', $roleIds)
            ->pluck('event_id')
            ->unique();

        if ($eventIds->isEmpty()) {
            return collect();
        }

        $stats = Sale::select(
            'promo_code_id',
            DB::raw('COUNT(*) as sales_count'),
            DB::raw('SUM(discount_amount) as total_discount')
        )
            ->whereIn('event_id', $eventIds)
            ->whereNotNull('promo_code_id')
            ->where('status', 'paid')
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('promo_code_id')
            ->orderByDesc('sales_count')
            ->get();

        if ($stats->isEmpty()) {
            return collect();
        }

        $promoCodes = PromoCode::whereIn('id', $stats->pluck('promo_code_id'))->get()->keyBy('id');

        return $stats->map(fn ($row) => [
            'code' => $promoCodes[$row->promo_code_id]->code ?? '—',
            'type' => $promoCodes[$row->promo_code_id]->type ?? null,
            'value' => $promoCodes[$row->promo_code_id]->value ?? null,
            'sales_count' => (int) $row->sales_count,
            'total_discount' => (float) $row->total_discount,
        ]);
    }

    /**
     * Get newsletter stats for the analytics dashboard
     */
    public function getNewsletterStats(User $user, Carbon $start, Carbon $end, ?int $roleId = null): array
    {
        $roleIds = $roleId ? collect([$roleId]) : $this->getUserRoleIds($user);

        if ($roleIds->isEmpty()) {
            return ['has_data' => false];
        }

        $newsletters = Newsletter::whereIn('role_id', $roleIds)
            ->where('status', 'sent')
            ->whereBetween('sent_at', [$start, $end])
            ->orderByDesc('sent_at')
            ->get();

        if ($newsletters->isEmpty()) {
            return ['has_data' => false];
        }

        $totalSent = $newsletters->sum('sent_count') ?? 0;
        $totalOpens = $newsletters->sum('open_count') ?? 0;
        $totalClicks = $newsletters->sum('click_count') ?? 0;

        $openRate = $totalSent > 0 ? round(($totalOpens / $totalSent) * 100, 1) : 0;
        $clickRate = $totalSent > 0 ? round(($totalClicks / $totalSent) * 100, 1) : 0;

        // Newsletter-attributed page views and sales
        $newsletterViews = $this->getNewsletterAttributedViews($roleIds, $start, $end);
        $newsletterSalesStats = $this->getNewsletterSalesStats($roleIds, $start, $end);

        return [
            'has_data' => true,
            'total_sent' => (int) $totalSent,
            'total_opens' => (int) $totalOpens,
            'total_clicks' => (int) $totalClicks,
            'open_rate' => $openRate,
            'click_rate' => $clickRate,
            'newsletter_views' => $newsletterViews,
            'newsletter_sales' => $newsletterSalesStats['sales'],
            'newsletter_revenue' => $newsletterSalesStats['revenue'],
            'campaigns' => $newsletters->map(fn ($n) => [
                'hash' => UrlUtils::encodeId($n->id),
                'subject' => $n->subject,
                'sent_at' => $n->sent_at,
                'sent_count' => (int) ($n->sent_count ?? 0),
                'open_count' => (int) ($n->open_count ?? 0),
                'click_count' => (int) ($n->click_count ?? 0),
            ])->toArray(),
        ];
    }
}
