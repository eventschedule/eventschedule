<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function dashboard(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        // Get date range
        $range = $request->input('range', 'last_30_days');
        $dates = $this->getDateRange($range);
        $startDate = $dates['start'];
        $endDate = $dates['end'];
        $previousStartDate = $dates['previous_start'];
        $previousEndDate = $dates['previous_end'];

        // Key Metrics
        $totalUsers = User::count();
        $totalSchedules = Role::count();
        $totalEvents = Event::count();

        // Users in current period
        $usersInPeriod = User::whereBetween('created_at', [$startDate, $endDate])->count();
        $usersInPreviousPeriod = User::whereBetween('created_at', [$previousStartDate, $previousEndDate])->count();
        $usersChangePercent = $usersInPreviousPeriod > 0
            ? round((($usersInPeriod - $usersInPreviousPeriod) / $usersInPreviousPeriod) * 100, 1)
            : ($usersInPeriod > 0 ? 100 : 0);

        // Schedules in current period
        $schedulesInPeriod = Role::whereBetween('created_at', [$startDate, $endDate])->count();
        $schedulesInPreviousPeriod = Role::whereBetween('created_at', [$previousStartDate, $previousEndDate])->count();
        $schedulesChangePercent = $schedulesInPreviousPeriod > 0
            ? round((($schedulesInPeriod - $schedulesInPreviousPeriod) / $schedulesInPreviousPeriod) * 100, 1)
            : ($schedulesInPeriod > 0 ? 100 : 0);

        // Events in current period
        $eventsInPeriod = Event::whereBetween('created_at', [$startDate, $endDate])->count();
        $eventsInPreviousPeriod = Event::whereBetween('created_at', [$previousStartDate, $previousEndDate])->count();
        $eventsChangePercent = $eventsInPreviousPeriod > 0
            ? round((($eventsInPeriod - $eventsInPreviousPeriod) / $eventsInPreviousPeriod) * 100, 1)
            : ($eventsInPeriod > 0 ? 100 : 0);

        // Active users (users who logged in within the period)
        $activeUsers7Days = User::where('updated_at', '>=', now()->subDays(7))->count();
        $activeUsers30Days = User::where('updated_at', '>=', now()->subDays(30))->count();

        // Average events per schedule
        $avgEventsPerSchedule = $totalSchedules > 0 ? round($totalEvents / $totalSchedules, 1) : 0;

        // Top schedules by event count
        $topSchedulesByEvents = Role::withCount('events')
            ->orderBy('events_count', 'desc')
            ->limit(10)
            ->get();

        // Recent schedules
        $recentSchedules = Role::orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Recent events
        $recentEvents = Event::with('roles')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Trends data - users, schedules, events over time
        $trendData = $this->getTrendData($startDate, $endDate);

        // Last updated timestamp
        $lastUpdated = now();

        return view('admin.dashboard', compact(
            'totalUsers',
            'totalSchedules',
            'totalEvents',
            'usersInPeriod',
            'usersInPreviousPeriod',
            'usersChangePercent',
            'schedulesInPeriod',
            'schedulesInPreviousPeriod',
            'schedulesChangePercent',
            'eventsInPeriod',
            'eventsInPreviousPeriod',
            'eventsChangePercent',
            'activeUsers7Days',
            'activeUsers30Days',
            'avgEventsPerSchedule',
            'topSchedulesByEvents',
            'recentSchedules',
            'recentEvents',
            'trendData',
            'range',
            'lastUpdated'
        ));
    }

    /**
     * Get date range based on selection.
     */
    private function getDateRange(string $range): array
    {
        $now = now();

        switch ($range) {
            case 'last_7_days':
                $start = $now->copy()->subDays(7)->startOfDay();
                $end = $now->copy()->endOfDay();
                $previousStart = $start->copy()->subDays(7);
                $previousEnd = $start->copy()->subSecond();
                break;
            case 'last_30_days':
                $start = $now->copy()->subDays(30)->startOfDay();
                $end = $now->copy()->endOfDay();
                $previousStart = $start->copy()->subDays(30);
                $previousEnd = $start->copy()->subSecond();
                break;
            case 'last_90_days':
                $start = $now->copy()->subDays(90)->startOfDay();
                $end = $now->copy()->endOfDay();
                $previousStart = $start->copy()->subDays(90);
                $previousEnd = $start->copy()->subSecond();
                break;
            case 'all_time':
            default:
                $start = Carbon::createFromDate(2020, 1, 1)->startOfDay();
                $end = $now->copy()->endOfDay();
                $previousStart = $start->copy();
                $previousEnd = $start->copy();
                break;
        }

        return [
            'start' => $start,
            'end' => $end,
            'previous_start' => $previousStart,
            'previous_end' => $previousEnd,
        ];
    }

    /**
     * Get trend data for charts.
     */
    private function getTrendData(Carbon $startDate, Carbon $endDate): array
    {
        $daysDiff = $startDate->diffInDays($endDate);

        // Determine grouping based on date range
        if ($daysDiff <= 31) {
            $groupFormat = '%Y-%m-%d';
            $labelFormat = 'M d';
        } elseif ($daysDiff <= 90) {
            $groupFormat = '%Y-%u'; // Week
            $labelFormat = 'W';
        } else {
            $groupFormat = '%Y-%m';
            $labelFormat = 'M Y';
        }

        // Users trend
        $usersTrend = User::select(
            DB::raw("DATE_FORMAT(created_at, '$groupFormat') as period"),
            DB::raw('COUNT(*) as count')
        )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        // Schedules trend
        $schedulesTrend = Role::select(
            DB::raw("DATE_FORMAT(created_at, '$groupFormat') as period"),
            DB::raw('COUNT(*) as count')
        )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        // Events trend
        $eventsTrend = Event::select(
            DB::raw("DATE_FORMAT(created_at, '$groupFormat') as period"),
            DB::raw('COUNT(*) as count')
        )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        // Build unified labels
        $allPeriods = collect()
            ->merge($usersTrend->pluck('period'))
            ->merge($schedulesTrend->pluck('period'))
            ->merge($eventsTrend->pluck('period'))
            ->unique()
            ->sort()
            ->values();

        $labels = $allPeriods->map(function ($period) use ($labelFormat, $groupFormat) {
            if ($groupFormat === '%Y-%u') {
                // Parse week format
                $parts = explode('-', $period);
                if (count($parts) === 2) {
                    return 'Week ' . ltrim($parts[1], '0');
                }
            }
            try {
                return Carbon::parse($period)->format($labelFormat);
            } catch (\Exception $e) {
                return $period;
            }
        })->toArray();

        $usersData = $allPeriods->map(fn ($period) => $usersTrend->firstWhere('period', $period)?->count ?? 0)->toArray();
        $schedulesData = $allPeriods->map(fn ($period) => $schedulesTrend->firstWhere('period', $period)?->count ?? 0)->toArray();
        $eventsData = $allPeriods->map(fn ($period) => $eventsTrend->firstWhere('period', $period)?->count ?? 0)->toArray();

        return [
            'labels' => $labels,
            'users' => $usersData,
            'schedules' => $schedulesData,
            'events' => $eventsData,
        ];
    }
}
