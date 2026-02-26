<?php

namespace App\Http\Controllers;

use App\Services\AnalyticsService;
use App\Utils\UrlUtils;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function index(Request $request, AnalyticsService $analytics)
    {
        $user = auth()->user();
        $roleIds = $user->roles()->wherePivot('level', '!=', 'follower')->pluck('roles.id');
        $roles = $user->roles()->wherePivot('level', '!=', 'follower')->get();

        // Get selected role for filtering (decode from URL-safe format)
        $selectedRoleId = $request->role_id ? UrlUtils::decodeId($request->role_id) : null;

        // Security: Validate that the selected role ID belongs to the authenticated user
        // This prevents enumeration attacks where attackers try to view analytics for other users' roles
        if ($selectedRoleId && ! $roleIds->contains($selectedRoleId)) {
            abort(403, 'Unauthorized access to analytics');
        }

        // Date range filter
        $range = $request->range ?? 'last_30_days';
        [$start, $end] = match ($range) {
            'last_7_days' => [now()->subDays(7)->startOfDay(), now()->endOfDay()],
            'last_30_days' => [now()->subDays(30)->startOfDay(), now()->endOfDay()],
            'last_90_days' => [now()->subDays(90)->startOfDay(), now()->endOfDay()],
            'this_month' => [now()->startOfMonth(), now()->endOfDay()],
            'last_month' => [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()],
            'this_year' => [now()->startOfYear(), now()->endOfDay()],
            'all_time' => [now()->subYears(10)->startOfDay(), now()->endOfDay()],
            default => [now()->subDays(30)->startOfDay(), now()->endOfDay()],
        };

        // Period determines chart grouping
        $period = $request->period ?? 'daily';

        // Get month-over-month comparison (for fixed stats cards)
        $momComparison = $analytics->getMonthOverMonthComparison($user, $selectedRoleId);

        // Get period comparison based on selected range (for dynamic comparison card)
        $periodComparison = $range !== 'all_time'
            ? $analytics->getPeriodComparison($user, $range, $start, $end, $selectedRoleId)
            : null;

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
        $viewsBySchedule = $analytics->getViewsBySchedule($user, $start, $end, $selectedRoleId);

        // Get top associated talents/venues (appearance views on this schedule)
        $topAppearances = collect();
        if ($selectedRoleId) {
            $selectedRole = $roles->firstWhere('id', $selectedRoleId);
            if ($selectedRole) {
                $topAppearances = $analytics->getTopAppearancesForSchedule($selectedRole, 10, $start, $end);
            }
        }

        // Get appearance views for talents/venues (views from appearing on other schedules)
        $appearanceViews = 0;
        $topSchedulesAppearedOn = collect();
        if ($selectedRoleId) {
            $selectedRole = $roles->firstWhere('id', $selectedRoleId);
            if ($selectedRole && ($selectedRole->isTalent() || $selectedRole->isVenue())) {
                $appearanceViews = $analytics->getTotalAppearanceViewsForRole($selectedRoleId, $start, $end);
                $topSchedulesAppearedOn = $analytics->getAppearancesByScheduleForRole($selectedRoleId, 10, $start, $end);
            }
        }

        // Get conversion stats
        $conversionStats = $analytics->getConversionStats($user, $start, $end, $selectedRoleId);

        // Get per-promo-code breakdown
        $promoCodeStats = $conversionStats['promo_sales'] > 0
            ? $analytics->getPromoCodeStats($user, $start, $end, $selectedRoleId)
            : collect();

        // Get top events by revenue
        $topEventsByRevenue = $analytics->getTopEventsByRevenue($user, 10, $start, $end);

        // Get traffic sources
        $trafficSources = $analytics->getTrafficSources($user, $start, $end, $selectedRoleId);

        // Get top referrer domains
        $topReferrers = $analytics->getTopReferrerDomains($user, 10, $start, $end, $selectedRoleId);

        // Get boost stats
        $boostStats = $analytics->getBoostStats($user, $start, $end, $selectedRoleId);

        // Get boost views by period for chart overlay (only if boost data exists)
        $boostViewsByPeriod = $boostStats['has_data']
            ? $analytics->getBoostViewsByPeriod($user, $period, $start, $end, $selectedRoleId)
            : collect();

        // Get newsletter stats
        $newsletterStats = $analytics->getNewsletterStats($user, $start, $end, $selectedRoleId);

        // Get newsletter views by period for chart overlay (only if newsletter data exists)
        $newsletterViewsByPeriod = $newsletterStats['has_data']
            ? $analytics->getNewsletterViewsByPeriod($user, $period, $start, $end, $selectedRoleId)
            : collect();

        return view('analytics.index', compact(
            'roles',
            'selectedRoleId',
            'totalViews',
            'momComparison',
            'periodComparison',
            'topEvents',
            'viewsByPeriod',
            'deviceBreakdown',
            'viewsBySchedule',
            'topAppearances',
            'appearanceViews',
            'topSchedulesAppearedOn',
            'period',
            'range',
            'conversionStats',
            'promoCodeStats',
            'topEventsByRevenue',
            'trafficSources',
            'topReferrers',
            'boostStats',
            'boostViewsByPeriod',
            'newsletterStats',
            'newsletterViewsByPeriod'
        ));
    }
}
