<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Role;
use App\Models\User;
use App\Utils\UrlUtils;
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
        if (! auth()->user()->isAdmin()) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        // Get date range
        $range = $request->input('range', 'last_30_days');
        $dates = $this->getDateRange($range);
        $startDate = $dates['start'];
        $endDate = $dates['end'];
        $previousStartDate = $dates['previous_start'];
        $previousEndDate = $dates['previous_end'];

        // Key Metrics (only count confirmed users and claimed roles)
        $totalUsers = User::whereNotNull('email_verified_at')->count();
        $totalSchedules = Role::whereNotNull('user_id')
            ->where(function ($query) {
                $query->whereNotNull('email_verified_at')
                    ->orWhereNotNull('phone_verified_at');
            })
            ->count();
        $totalEvents = Event::count();

        // Users in current period (only confirmed)
        $usersInPeriod = User::whereNotNull('email_verified_at')
            ->whereBetween('created_at', [$startDate, $endDate])->count();
        $usersInPreviousPeriod = User::whereNotNull('email_verified_at')
            ->whereBetween('created_at', [$previousStartDate, $previousEndDate])->count();
        $usersChangePercent = $usersInPreviousPeriod > 0
            ? round((($usersInPeriod - $usersInPreviousPeriod) / $usersInPreviousPeriod) * 100, 1)
            : ($usersInPeriod > 0 ? 100 : 0);

        // Schedules in current period (only claimed)
        $schedulesInPeriod = Role::whereNotNull('user_id')
            ->where(function ($query) {
                $query->whereNotNull('email_verified_at')
                    ->orWhereNotNull('phone_verified_at');
            })
            ->whereBetween('created_at', [$startDate, $endDate])->count();
        $schedulesInPreviousPeriod = Role::whereNotNull('user_id')
            ->where(function ($query) {
                $query->whereNotNull('email_verified_at')
                    ->orWhereNotNull('phone_verified_at');
            })
            ->whereBetween('created_at', [$previousStartDate, $previousEndDate])->count();
        $schedulesChangePercent = $schedulesInPreviousPeriod > 0
            ? round((($schedulesInPeriod - $schedulesInPreviousPeriod) / $schedulesInPreviousPeriod) * 100, 1)
            : ($schedulesInPeriod > 0 ? 100 : 0);

        // Events in current period
        $eventsInPeriod = Event::whereBetween('created_at', [$startDate, $endDate])->count();
        $eventsInPreviousPeriod = Event::whereBetween('created_at', [$previousStartDate, $previousEndDate])->count();
        $eventsChangePercent = $eventsInPreviousPeriod > 0
            ? round((($eventsInPeriod - $eventsInPreviousPeriod) / $eventsInPreviousPeriod) * 100, 1)
            : ($eventsInPeriod > 0 ? 100 : 0);

        // Active users (confirmed users who logged in within the period)
        $activeUsers7Days = User::whereNotNull('email_verified_at')
            ->where('updated_at', '>=', now()->subDays(7))->count();
        $activeUsers30Days = User::whereNotNull('email_verified_at')
            ->where('updated_at', '>=', now()->subDays(30))->count();

        // Average events per schedule
        $avgEventsPerSchedule = $totalSchedules > 0 ? round($totalEvents / $totalSchedules, 1) : 0;

        // Top schedules by event count
        $topSchedulesByEvents = Role::withCount('events')
            ->orderBy('events_count', 'desc')
            ->limit(10)
            ->get();

        // Recent schedules (only claimed)
        $recentSchedules = Role::whereNotNull('user_id')
            ->where(function ($query) {
                $query->whereNotNull('email_verified_at')
                    ->orWhereNotNull('phone_verified_at');
            })
            ->orderBy('created_at', 'desc')
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

        // Users trend (only confirmed)
        $usersTrend = User::select(
            DB::raw("DATE_FORMAT(created_at, '$groupFormat') as period"),
            DB::raw('COUNT(*) as count')
        )
            ->whereNotNull('email_verified_at')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        // Schedules trend (only claimed)
        $schedulesTrend = Role::select(
            DB::raw("DATE_FORMAT(created_at, '$groupFormat') as period"),
            DB::raw('COUNT(*) as count')
        )
            ->whereNotNull('user_id')
            ->where(function ($query) {
                $query->whereNotNull('email_verified_at')
                    ->orWhereNotNull('phone_verified_at');
            })
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
                    return 'Week '.ltrim($parts[1], '0');
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

    /**
     * Display the admin plans management page.
     */
    public function plans(Request $request)
    {
        if (! auth()->user()->isAdmin()) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        // Plan statistics
        $planCounts = Role::select('plan_type', DB::raw('COUNT(*) as count'))
            ->whereNotNull('user_id')
            ->where(function ($query) {
                $query->whereNotNull('email_verified_at')
                    ->orWhereNotNull('phone_verified_at');
            })
            ->groupBy('plan_type')
            ->pluck('count', 'plan_type')
            ->toArray();

        $freeCount = $planCounts['free'] ?? 0;
        $proCount = $planCounts['pro'] ?? 0;
        $enterpriseCount = $planCounts['enterprise'] ?? 0;

        // Active Stripe subscriptions
        $activeSubscriptions = Role::whereHas('subscriptions', function ($query) {
            $query->where('stripe_status', 'active');
        })->count();

        // Expiring in 30 days
        $expiringSoon = Role::where('plan_type', '!=', 'free')
            ->whereNotNull('plan_expires')
            ->whereBetween('plan_expires', [now()->format('Y-m-d'), now()->addDays(30)->format('Y-m-d')])
            ->count();

        // Build query for role list
        $query = Role::whereNotNull('user_id')
            ->where(function ($q) {
                $q->whereNotNull('email_verified_at')
                    ->orWhereNotNull('phone_verified_at');
            })
            ->with('user');

        // Search filter
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('subdomain', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Plan type filter
        if ($planType = $request->input('plan_type')) {
            $query->where('plan_type', $planType);
        }

        // Status filter
        if ($status = $request->input('status')) {
            if ($status === 'active') {
                $query->where(function ($q) {
                    $q->where('plan_expires', '>=', now()->format('Y-m-d'))
                        ->orWhereHas('subscriptions', function ($sq) {
                            $sq->where('stripe_status', 'active');
                        });
                });
            } elseif ($status === 'expired') {
                $query->where(function ($q) {
                    $q->where('plan_expires', '<', now()->format('Y-m-d'))
                        ->orWhereNull('plan_expires');
                })->whereDoesntHave('subscriptions', function ($sq) {
                    $sq->where('stripe_status', 'active');
                });
            } elseif ($status === 'trial') {
                $query->whereNotNull('trial_ends_at')
                    ->where('trial_ends_at', '>', now());
            }
        }

        $roles = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();

        return view('admin.plans', compact(
            'roles',
            'freeCount',
            'proCount',
            'enterpriseCount',
            'activeSubscriptions',
            'expiringSoon'
        ));
    }

    /**
     * Show the edit form for a role's plan.
     */
    public function editPlan($roleId)
    {
        if (! auth()->user()->isAdmin()) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $decodedId = UrlUtils::decodeId($roleId);
        $role = Role::with('user', 'subscriptions')->findOrFail($decodedId);

        return view('admin.plans-edit', compact('role'));
    }

    /**
     * Update a role's plan.
     */
    public function updatePlan(Request $request, $roleId)
    {
        if (! auth()->user()->isAdmin()) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $decodedId = UrlUtils::decodeId($roleId);
        $role = Role::findOrFail($decodedId);

        $validated = $request->validate([
            'plan_type' => 'required|in:free,pro,enterprise',
            'plan_term' => 'nullable|in:monthly,yearly',
            'plan_expires' => 'nullable|date',
        ]);

        $role->plan_type = $validated['plan_type'];
        $role->plan_term = $validated['plan_term'] ?? 'yearly';
        $role->plan_expires = $validated['plan_expires'];
        $role->save();

        return redirect()->route('admin.plans')->with('success', 'Plan updated successfully for '.$role->name);
    }
}
