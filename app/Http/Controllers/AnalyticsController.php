<?php

namespace App\Http\Controllers;

use App\Services\AnalyticsService;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function index(Request $request, AnalyticsService $analytics)
    {
        $user = auth()->user();
        $roleIds = $user->roles()->wherePivot('level', '!=', 'follower')->pluck('roles.id');
        $roles = $user->roles()->wherePivot('level', '!=', 'follower')->get();

        // Get selected role for filtering
        $selectedRoleId = $request->role_id ? (int) $request->role_id : null;

        // Date range - default to last 30 days
        $period = $request->period ?? 'daily';
        $daysBack = match ($period) {
            'daily' => 30,
            'weekly' => 90,
            'monthly' => 365,
            default => 30,
        };
        $start = now()->subDays($daysBack)->startOfDay();
        $end = now()->endOfDay();

        // Get month-over-month comparison
        $momComparison = $analytics->getMonthOverMonthComparison($user, $selectedRoleId);

        // Get total views (all time)
        $totalViews = $analytics->getTotalViewsForRoles(
            $selectedRoleId ? collect([$selectedRoleId]) : $roleIds
        );

        // Get top events
        $topEvents = $analytics->getTopEvents($user, 10, $start, $end);

        // Get views by period for chart
        $viewsByPeriod = $analytics->getViewsByPeriod($user, $period, $start, $end, $selectedRoleId);

        // Get device breakdown
        $deviceBreakdown = $analytics->getDeviceBreakdown($user, $start, $end, $selectedRoleId);

        // Get views by schedule
        $viewsBySchedule = $analytics->getViewsBySchedule($user, $start, $end);

        // Get top associated talents/venues (appearance views)
        $topAppearances = collect();
        if ($selectedRoleId) {
            $selectedRole = $roles->firstWhere('id', $selectedRoleId);
            if ($selectedRole) {
                $topAppearances = $analytics->getTopAppearancesForSchedule($selectedRole, 10, $start, $end);
            }
        }

        return view('analytics.index', compact(
            'roles',
            'selectedRoleId',
            'totalViews',
            'momComparison',
            'topEvents',
            'viewsByPeriod',
            'deviceBreakdown',
            'viewsBySchedule',
            'topAppearances',
            'period'
        ));
    }
}
