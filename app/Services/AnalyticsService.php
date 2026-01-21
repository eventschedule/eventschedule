<?php

namespace App\Services;

use App\Models\AnalyticsAppearancesDaily;
use App\Models\AnalyticsDaily;
use App\Models\AnalyticsEventsDaily;
use App\Models\AnalyticsReferrersDaily;
use App\Models\Event;
use App\Models\PageView;
use App\Models\Role;
use App\Models\User;
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

        $dateFormat = match ($period) {
            'daily' => '%Y-%m-%d',
            'weekly' => '%x-%v',  // ISO year and week
            'monthly' => '%Y-%m',
            default => '%Y-%m-%d',
        };

        return AnalyticsDaily::select(
            DB::raw("DATE_FORMAT(date, '{$dateFormat}') as period"),
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
            ];
        }

        $stats = AnalyticsEventsDaily::select(
            DB::raw('SUM(desktop_views + mobile_views + tablet_views + unknown_views) as total_views'),
            DB::raw('SUM(sales_count) as total_sales'),
            DB::raw('SUM(revenue) as total_revenue')
        )
            ->forEvents($eventIds)
            ->inDateRange($start, $end)
            ->first();

        $totalViews = (int) ($stats->total_views ?? 0);
        $totalSales = (int) ($stats->total_sales ?? 0);
        $totalRevenue = (float) ($stats->total_revenue ?? 0);

        return [
            'total_views' => $totalViews,
            'total_sales' => $totalSales,
            'conversion_rate' => $totalViews > 0 ? round(($totalSales / $totalViews) * 100, 2) : 0,
            'total_revenue' => $totalRevenue,
            'revenue_per_view' => $totalViews > 0 ? round(($totalRevenue / $totalViews) * 100, 2) : 0,
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
}
