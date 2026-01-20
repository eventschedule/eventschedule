<?php

namespace App\Services;

use App\Models\PageView;
use App\Models\Role;
use App\Models\Event;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    /**
     * Record a page view (returns null if bot detected)
     */
    public function recordView(Role $role, ?Event $event, Request $request): ?PageView
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

        $totalViews = PageView::forRoles($roleIds)->count();
        $periodViews = PageView::forRoles($roleIds)->inDateRange($start, $end)->count();

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
        $totalViews = PageView::byRole($role->id)->count();
        $periodViews = PageView::byRole($role->id)->inDateRange($start, $end)->count();

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

        return PageView::select('event_id', DB::raw('COUNT(*) as view_count'))
            ->forRoles($roleIds)
            ->whereNotNull('event_id')
            ->inDateRange($start, $end)
            ->groupBy('event_id')
            ->orderByDesc('view_count')
            ->limit($limit)
            ->with('event')
            ->get()
            ->filter(fn($item) => $item->event !== null)
            ->map(fn($item) => [
                'event' => $item->event,
                'view_count' => $item->view_count,
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

        $query = PageView::select(
            DB::raw("DATE_FORMAT(viewed_at, '{$dateFormat}') as period"),
            DB::raw('COUNT(*) as view_count')
        )
            ->forRoles($roleIds)
            ->inDateRange($start, $end)
            ->groupBy('period')
            ->orderBy('period');

        return $query->get();
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

        $thisMonthViews = PageView::forRoles($roleIds)
            ->inDateRange($thisMonthStart, $thisMonthEnd)
            ->count();

        $lastMonthViews = PageView::forRoles($roleIds)
            ->inDateRange($lastMonthStart, $lastMonthEnd)
            ->count();

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

        return PageView::select('device_type', DB::raw('COUNT(*) as count'))
            ->forRoles($roleIds)
            ->inDateRange($start, $end)
            ->groupBy('device_type')
            ->get()
            ->pluck('count', 'device_type');
    }

    /**
     * Get views by schedule (role)
     */
    public function getViewsBySchedule(User $user, Carbon $start, Carbon $end): Collection
    {
        $roleIds = $this->getUserRoleIds($user);

        if ($roleIds->isEmpty()) {
            return collect();
        }

        return PageView::select('role_id', DB::raw('COUNT(*) as view_count'))
            ->forRoles($roleIds)
            ->inDateRange($start, $end)
            ->groupBy('role_id')
            ->with('role')
            ->get()
            ->filter(fn($item) => $item->role !== null)
            ->map(fn($item) => [
                'role' => $item->role,
                'view_count' => $item->view_count,
            ]);
    }

    /**
     * Get recent views
     */
    public function getRecentViews(User $user, int $limit = 50, ?int $roleId = null): Collection
    {
        $roleIds = $roleId ? collect([$roleId]) : $this->getUserRoleIds($user);

        if ($roleIds->isEmpty()) {
            return collect();
        }

        return PageView::forRoles($roleIds)
            ->with(['role', 'event'])
            ->orderByDesc('viewed_at')
            ->limit($limit)
            ->get();
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
}
