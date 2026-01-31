<?php

namespace App\Http\Controllers;

use App\Models\AnalyticsDaily;
use App\Models\AnalyticsEventsDaily;
use App\Models\AnalyticsReferrersDaily;
use App\Models\Event;
use App\Models\Role;
use App\Models\Sale;
use App\Models\User;
use App\Services\DemoService;
use App\Utils\UrlUtils;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Laravel\Cashier\Subscription;

class AdminController extends Controller
{
    /**
     * Display the admin dashboard (overview/highlights).
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

        // Key Metrics (only count confirmed users and claimed roles, excluding demo data)
        $totalUsers = User::whereNotNull('email_verified_at')
            ->where('email', '!=', DemoService::DEMO_EMAIL)
            ->count();
        $totalSchedules = Role::whereNotNull('user_id')
            ->where(function ($query) {
                $query->whereNotNull('email_verified_at')
                    ->orWhereNotNull('phone_verified_at');
            })
            ->where('subdomain', '!=', DemoService::DEMO_ROLE_SUBDOMAIN)
            ->where('subdomain', 'not like', 'demo-%')
            ->count();
        $totalEvents = Event::whereDoesntHave('roles', function ($query) {
            $query->where('subdomain', DemoService::DEMO_ROLE_SUBDOMAIN)
                ->orWhere('subdomain', 'like', 'demo-%');
        })->count();

        // Users in current period (only confirmed, excluding demo user)
        $usersInPeriod = User::whereNotNull('email_verified_at')
            ->where('email', '!=', DemoService::DEMO_EMAIL)
            ->whereBetween('created_at', [$startDate, $endDate])->count();
        $usersInPreviousPeriod = User::whereNotNull('email_verified_at')
            ->where('email', '!=', DemoService::DEMO_EMAIL)
            ->whereBetween('created_at', [$previousStartDate, $previousEndDate])->count();
        $usersChangePercent = $usersInPreviousPeriod > 0
            ? round((($usersInPeriod - $usersInPreviousPeriod) / $usersInPreviousPeriod) * 100, 1)
            : ($usersInPeriod > 0 ? 100 : 0);

        // Schedules in current period (only claimed, excluding demo roles)
        $schedulesInPeriod = Role::whereNotNull('user_id')
            ->where(function ($query) {
                $query->whereNotNull('email_verified_at')
                    ->orWhereNotNull('phone_verified_at');
            })
            ->where('subdomain', '!=', DemoService::DEMO_ROLE_SUBDOMAIN)
            ->where('subdomain', 'not like', 'demo-%')
            ->whereBetween('created_at', [$startDate, $endDate])->count();
        $schedulesInPreviousPeriod = Role::whereNotNull('user_id')
            ->where(function ($query) {
                $query->whereNotNull('email_verified_at')
                    ->orWhereNotNull('phone_verified_at');
            })
            ->where('subdomain', '!=', DemoService::DEMO_ROLE_SUBDOMAIN)
            ->where('subdomain', 'not like', 'demo-%')
            ->whereBetween('created_at', [$previousStartDate, $previousEndDate])->count();
        $schedulesChangePercent = $schedulesInPreviousPeriod > 0
            ? round((($schedulesInPeriod - $schedulesInPreviousPeriod) / $schedulesInPreviousPeriod) * 100, 1)
            : ($schedulesInPeriod > 0 ? 100 : 0);

        // Events in current period (excluding demo events)
        $eventsInPeriod = Event::whereDoesntHave('roles', function ($query) {
            $query->where('subdomain', DemoService::DEMO_ROLE_SUBDOMAIN)
                ->orWhere('subdomain', 'like', 'demo-%');
        })->whereBetween('created_at', [$startDate, $endDate])->count();
        $eventsInPreviousPeriod = Event::whereDoesntHave('roles', function ($query) {
            $query->where('subdomain', DemoService::DEMO_ROLE_SUBDOMAIN)
                ->orWhere('subdomain', 'like', 'demo-%');
        })->whereBetween('created_at', [$previousStartDate, $previousEndDate])->count();
        $eventsChangePercent = $eventsInPreviousPeriod > 0
            ? round((($eventsInPeriod - $eventsInPreviousPeriod) / $eventsInPreviousPeriod) * 100, 1)
            : ($eventsInPeriod > 0 ? 100 : 0);

        // Active users (confirmed users who logged in within the period, excluding demo user)
        $activeUsers7Days = User::whereNotNull('email_verified_at')
            ->where('email', '!=', DemoService::DEMO_EMAIL)
            ->where('updated_at', '>=', now()->subDays(7))->count();
        $activeUsers30Days = User::whereNotNull('email_verified_at')
            ->where('email', '!=', DemoService::DEMO_EMAIL)
            ->where('updated_at', '>=', now()->subDays(30))->count();

        // Average events per schedule
        $avgEventsPerSchedule = $totalSchedules > 0 ? round($totalEvents / $totalSchedules, 1) : 0;

        // Upcoming online events (events with event_url but no venue)
        $upcomingOnlineEvents = Event::whereNotNull('event_url')
            ->where('event_url', '!=', '')
            ->where('starts_at', '>', now())
            ->whereDoesntHave('roles', function ($query) {
                $query->where('roles.type', 'venue');
            })
            ->whereDoesntHave('roles', function ($query) {
                $query->where('subdomain', DemoService::DEMO_ROLE_SUBDOMAIN)
                    ->orWhere('subdomain', 'like', 'demo-%');
            })
            ->count();

        // Events by country (from venue's country_code)
        $eventsByCountry = Event::where('starts_at', '>', now())
            ->whereDoesntHave('roles', function ($query) {
                $query->where('subdomain', DemoService::DEMO_ROLE_SUBDOMAIN)
                    ->orWhere('subdomain', 'like', 'demo-%');
            })
            ->whereHas('roles', function ($query) {
                $query->where('roles.type', 'venue')
                    ->whereNotNull('country_code')
                    ->where('country_code', '!=', '');
            })
            ->join('event_role', 'events.id', '=', 'event_role.event_id')
            ->join('roles', 'event_role.role_id', '=', 'roles.id')
            ->where('roles.type', 'venue')
            ->whereNotNull('roles.country_code')
            ->select('roles.country_code', DB::raw('COUNT(DISTINCT events.id) as count'))
            ->groupBy('roles.country_code')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        // Recent schedules (only claimed, excluding demo roles) - reduced to 5
        $recentSchedules = Role::whereNotNull('user_id')
            ->where(function ($query) {
                $query->whereNotNull('email_verified_at')
                    ->orWhereNotNull('phone_verified_at');
            })
            ->where('subdomain', '!=', DemoService::DEMO_ROLE_SUBDOMAIN)
            ->where('subdomain', 'not like', 'demo-%')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Recent events (excluding demo events) - reduced to 5
        $recentEvents = Event::with('roles')
            ->whereDoesntHave('roles', function ($query) {
                $query->where('subdomain', DemoService::DEMO_ROLE_SUBDOMAIN)
                    ->orWhere('subdomain', 'like', 'demo-%');
            })
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Trends data - users, schedules, events over time
        $trendData = $this->getTrendData($startDate, $endDate);

        return view('admin.dashboard', compact(
            'totalUsers',
            'totalSchedules',
            'totalEvents',
            'usersInPeriod',
            'usersChangePercent',
            'schedulesInPeriod',
            'schedulesChangePercent',
            'eventsInPeriod',
            'eventsChangePercent',
            'activeUsers7Days',
            'activeUsers30Days',
            'avgEventsPerSchedule',
            'upcomingOnlineEvents',
            'eventsByCountry',
            'recentSchedules',
            'recentEvents',
            'trendData',
            'range'
        ));
    }

    /**
     * Display the admin users page.
     */
    public function users(Request $request)
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

        // Total users
        $totalUsers = User::whereNotNull('email_verified_at')
            ->where('email', '!=', DemoService::DEMO_EMAIL)
            ->count();

        // Users in current period
        $usersInPeriod = User::whereNotNull('email_verified_at')
            ->where('email', '!=', DemoService::DEMO_EMAIL)
            ->whereBetween('created_at', [$startDate, $endDate])->count();
        $usersInPreviousPeriod = User::whereNotNull('email_verified_at')
            ->where('email', '!=', DemoService::DEMO_EMAIL)
            ->whereBetween('created_at', [$previousStartDate, $previousEndDate])->count();
        $usersChangePercent = $usersInPreviousPeriod > 0
            ? round((($usersInPeriod - $usersInPreviousPeriod) / $usersInPreviousPeriod) * 100, 1)
            : ($usersInPeriod > 0 ? 100 : 0);

        // Active users
        $activeUsers7Days = User::whereNotNull('email_verified_at')
            ->where('email', '!=', DemoService::DEMO_EMAIL)
            ->where('updated_at', '>=', now()->subDays(7))->count();
        $activeUsers30Days = User::whereNotNull('email_verified_at')
            ->where('email', '!=', DemoService::DEMO_EMAIL)
            ->where('updated_at', '>=', now()->subDays(30))->count();

        // User signup method breakdown (all time)
        $emailUsers = User::whereNotNull('email_verified_at')
            ->where('email', '!=', DemoService::DEMO_EMAIL)
            ->whereNotNull('password')
            ->whereNull('google_oauth_id')
            ->count();

        $googleUsers = User::whereNotNull('email_verified_at')
            ->where('email', '!=', DemoService::DEMO_EMAIL)
            ->whereNotNull('google_oauth_id')
            ->whereNull('password')
            ->count();

        $hybridUsers = User::whereNotNull('email_verified_at')
            ->where('email', '!=', DemoService::DEMO_EMAIL)
            ->whereNotNull('password')
            ->whereNotNull('google_oauth_id')
            ->count();

        // User signup method breakdown for selected period
        $emailUsersInPeriod = User::whereNotNull('email_verified_at')
            ->where('email', '!=', DemoService::DEMO_EMAIL)
            ->whereNotNull('password')
            ->whereNull('google_oauth_id')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        $googleUsersInPeriod = User::whereNotNull('email_verified_at')
            ->where('email', '!=', DemoService::DEMO_EMAIL)
            ->whereNotNull('google_oauth_id')
            ->whereNull('password')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        $hybridUsersInPeriod = User::whereNotNull('email_verified_at')
            ->where('email', '!=', DemoService::DEMO_EMAIL)
            ->whereNotNull('password')
            ->whereNotNull('google_oauth_id')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        // Trends data for signup methods
        $trendData = $this->getTrendData($startDate, $endDate);

        // UTM Attribution - Top 10 sources (all time)
        $topUtmSources = User::whereNotNull('email_verified_at')
            ->where('email', '!=', DemoService::DEMO_EMAIL)
            ->whereNotNull('utm_source')
            ->select('utm_source', DB::raw('COUNT(*) as count'))
            ->groupBy('utm_source')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        // UTM Attribution - Top 10 campaigns (all time)
        $topUtmCampaigns = User::whereNotNull('email_verified_at')
            ->where('email', '!=', DemoService::DEMO_EMAIL)
            ->whereNotNull('utm_campaign')
            ->select('utm_source', 'utm_medium', 'utm_campaign', DB::raw('COUNT(*) as count'))
            ->groupBy('utm_source', 'utm_medium', 'utm_campaign')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        // UTM source breakdown for selected period
        $utmSourcesInPeriod = User::whereNotNull('email_verified_at')
            ->where('email', '!=', DemoService::DEMO_EMAIL)
            ->whereNotNull('utm_source')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select('utm_source', DB::raw('COUNT(*) as count'))
            ->groupBy('utm_source')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        // Users with vs without UTM data in period
        $usersWithUtmInPeriod = User::whereNotNull('email_verified_at')
            ->where('email', '!=', DemoService::DEMO_EMAIL)
            ->whereNotNull('utm_source')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        $usersWithoutUtmInPeriod = User::whereNotNull('email_verified_at')
            ->where('email', '!=', DemoService::DEMO_EMAIL)
            ->whereNull('utm_source')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        // Recent signups with UTM data
        $recentSignups = User::whereNotNull('email_verified_at')
            ->where('email', '!=', DemoService::DEMO_EMAIL)
            ->orderByDesc('created_at')
            ->limit(20)
            ->get(['name', 'email', 'created_at', 'utm_source', 'utm_medium', 'utm_campaign']);

        return view('admin.users', compact(
            'totalUsers',
            'usersInPeriod',
            'usersChangePercent',
            'activeUsers7Days',
            'activeUsers30Days',
            'emailUsers',
            'googleUsers',
            'hybridUsers',
            'emailUsersInPeriod',
            'googleUsersInPeriod',
            'hybridUsersInPeriod',
            'trendData',
            'range',
            'topUtmSources',
            'topUtmCampaigns',
            'utmSourcesInPeriod',
            'usersWithUtmInPeriod',
            'usersWithoutUtmInPeriod',
            'recentSignups'
        ));
    }

    /**
     * Display the admin revenue page.
     */
    public function revenue(Request $request)
    {
        if (! auth()->user()->isAdmin()) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        // Get date range
        $range = $request->input('range', 'last_30_days');
        $dates = $this->getDateRange($range);
        $startDate = $dates['start'];
        $endDate = $dates['end'];

        // Revenue & Sales Metrics
        $totalRevenue = Sale::where('status', 'completed')->sum('payment_amount');
        $revenueInPeriod = Sale::where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('payment_amount');

        $totalSales = Sale::where('status', 'completed')->count();
        $salesInPeriod = Sale::where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        $refundedSales = Sale::where('status', 'refunded')->count();
        $refundRate = $totalSales > 0 ? round(($refundedSales / ($totalSales + $refundedSales)) * 100, 1) : 0;

        $pendingSales = Sale::where('status', 'pending')->count();
        $pendingRevenue = Sale::where('status', 'pending')->sum('payment_amount');

        // Subscription Health (for hosted mode)
        $activeSubscriptions = 0;
        $trialingSubscriptions = 0;
        $canceledSubscriptions = 0;
        $pastDueSubscriptions = 0;
        $rolesOnTrial = 0;
        $convertedFromTrial = 0;
        $expiredTrialsNoSub = 0;

        if (config('app.hosted')) {
            $subscriptionStats = Subscription::selectRaw('stripe_status, COUNT(*) as count')
                ->groupBy('stripe_status')
                ->pluck('count', 'stripe_status')
                ->toArray();

            $activeSubscriptions = $subscriptionStats['active'] ?? 0;
            $trialingSubscriptions = $subscriptionStats['trialing'] ?? 0;
            $canceledSubscriptions = $subscriptionStats['canceled'] ?? 0;
            $pastDueSubscriptions = $subscriptionStats['past_due'] ?? 0;

            $rolesOnTrial = Role::whereNotNull('user_id')
                ->whereNotNull('trial_ends_at')
                ->where('trial_ends_at', '>', now())
                ->where('subdomain', '!=', DemoService::DEMO_ROLE_SUBDOMAIN)
                ->where('subdomain', 'not like', 'demo-%')
                ->count();

            $convertedFromTrial = Role::whereNotNull('user_id')
                ->whereNotNull('trial_ends_at')
                ->whereHas('subscriptions', function ($q) {
                    $q->where('stripe_status', 'active');
                })
                ->where('subdomain', '!=', DemoService::DEMO_ROLE_SUBDOMAIN)
                ->where('subdomain', 'not like', 'demo-%')
                ->count();

            $expiredTrialsNoSub = Role::whereNotNull('user_id')
                ->whereNotNull('trial_ends_at')
                ->where('trial_ends_at', '<', now())
                ->whereDoesntHave('subscriptions', function ($q) {
                    $q->where('stripe_status', 'active');
                })
                ->where('subdomain', '!=', DemoService::DEMO_ROLE_SUBDOMAIN)
                ->where('subdomain', 'not like', 'demo-%')
                ->count();
        }

        // Revenue trends
        $trendData = $this->getTrendData($startDate, $endDate);

        return view('admin.revenue', compact(
            'totalRevenue',
            'revenueInPeriod',
            'totalSales',
            'salesInPeriod',
            'refundRate',
            'pendingSales',
            'pendingRevenue',
            'activeSubscriptions',
            'trialingSubscriptions',
            'canceledSubscriptions',
            'pastDueSubscriptions',
            'rolesOnTrial',
            'convertedFromTrial',
            'expiredTrialsNoSub',
            'trendData',
            'range'
        ));
    }

    /**
     * Display the admin analytics page.
     */
    public function analytics(Request $request)
    {
        if (! auth()->user()->isAdmin()) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        // Get date range
        $range = $request->input('range', 'last_30_days');
        $dates = $this->getDateRange($range);
        $startDate = $dates['start'];
        $endDate = $dates['end'];

        // Traffic & Analytics
        $totalViews = AnalyticsDaily::whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->selectRaw('SUM(desktop_views) as desktop, SUM(mobile_views) as mobile, SUM(tablet_views) as tablet, SUM(unknown_views) as unknown')
            ->first();

        $desktopViews = $totalViews->desktop ?? 0;
        $mobileViews = $totalViews->mobile ?? 0;
        $tabletViews = $totalViews->tablet ?? 0;
        $totalPageViews = $desktopViews + $mobileViews + $tabletViews + ($totalViews->unknown ?? 0);

        $trafficSources = AnalyticsReferrersDaily::whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->selectRaw('source, SUM(views) as views')
            ->groupBy('source')
            ->pluck('views', 'source')
            ->toArray();

        $directViews = $trafficSources['direct'] ?? 0;
        $searchViews = $trafficSources['search'] ?? 0;
        $socialViews = $trafficSources['social'] ?? 0;
        $emailViews = $trafficSources['email'] ?? 0;
        $otherViews = $trafficSources['other'] ?? 0;

        // Feature Adoption
        $totalSchedules = Role::whereNotNull('user_id')
            ->where(function ($query) {
                $query->whereNotNull('email_verified_at')
                    ->orWhereNotNull('phone_verified_at');
            })
            ->where('subdomain', '!=', DemoService::DEMO_ROLE_SUBDOMAIN)
            ->where('subdomain', 'not like', 'demo-%')
            ->count();

        $baseRoleQuery = Role::whereNotNull('user_id')
            ->where(function ($query) {
                $query->whereNotNull('email_verified_at')
                    ->orWhereNotNull('phone_verified_at');
            })
            ->where('subdomain', '!=', DemoService::DEMO_ROLE_SUBDOMAIN)
            ->where('subdomain', 'not like', 'demo-%');

        $googleCalendarEnabled = (clone $baseRoleQuery)->whereNotNull('google_calendar_id')->count();
        $stripeEnabled = (clone $baseRoleQuery)->whereNotNull('stripe_id')->count();
        $customDomainEnabled = (clone $baseRoleQuery)->whereNotNull('custom_domain')->count();
        $customCssEnabled = (clone $baseRoleQuery)->whereNotNull('custom_css')->where('custom_css', '!=', '')->count();

        $googleCalendarPercent = $totalSchedules > 0 ? round(($googleCalendarEnabled / $totalSchedules) * 100, 1) : 0;
        $stripePercent = $totalSchedules > 0 ? round(($stripeEnabled / $totalSchedules) * 100, 1) : 0;
        $customDomainPercent = $totalSchedules > 0 ? round(($customDomainEnabled / $totalSchedules) * 100, 1) : 0;
        $customCssPercent = $totalSchedules > 0 ? round(($customCssEnabled / $totalSchedules) * 100, 1) : 0;

        // Top schedules by event count (excluding demo roles)
        $topSchedulesByEvents = Role::withCount('events')
            ->where('subdomain', '!=', DemoService::DEMO_ROLE_SUBDOMAIN)
            ->where('subdomain', 'not like', 'demo-%')
            ->orderBy('events_count', 'desc')
            ->limit(10)
            ->get();

        return view('admin.analytics', compact(
            'totalPageViews',
            'desktopViews',
            'mobileViews',
            'tabletViews',
            'directViews',
            'searchViews',
            'socialViews',
            'emailViews',
            'otherViews',
            'googleCalendarEnabled',
            'stripeEnabled',
            'customDomainEnabled',
            'customCssEnabled',
            'googleCalendarPercent',
            'stripePercent',
            'customDomainPercent',
            'customCssPercent',
            'topSchedulesByEvents',
            'totalSchedules',
            'range'
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
        // Using whitelisted format strings to prevent SQL injection
        $allowedFormats = [
            'daily' => '%Y-%m-%d',
            'weekly' => '%Y-%u',
            'monthly' => '%Y-%m',
        ];

        if ($daysDiff <= 31) {
            $formatKey = 'daily';
            $labelFormat = 'M d';
        } elseif ($daysDiff <= 90) {
            $formatKey = 'weekly';
            $labelFormat = 'W';
        } else {
            $formatKey = 'monthly';
            $labelFormat = 'M Y';
        }

        $groupFormat = $allowedFormats[$formatKey];

        // Build the DATE_FORMAT expression safely using whitelisted format
        $dateFormatExpr = match ($formatKey) {
            'daily' => DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d') as period"),
            'weekly' => DB::raw("DATE_FORMAT(created_at, '%Y-%u') as period"),
            'monthly' => DB::raw("DATE_FORMAT(created_at, '%Y-%m') as period"),
        };

        // Users trend (only confirmed, excluding demo user)
        $usersTrend = User::select(
            $dateFormatExpr,
            DB::raw('COUNT(*) as count')
        )
            ->whereNotNull('email_verified_at')
            ->where('email', '!=', DemoService::DEMO_EMAIL)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        // Email users trend (users with password, no google_oauth_id)
        $emailUsersTrend = User::select(
            $dateFormatExpr,
            DB::raw('COUNT(*) as count')
        )
            ->whereNotNull('email_verified_at')
            ->where('email', '!=', DemoService::DEMO_EMAIL)
            ->whereNotNull('password')
            ->whereNull('google_oauth_id')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        // Google users trend (users with google_oauth_id, no password)
        $googleUsersTrend = User::select(
            match ($formatKey) {
                'daily' => DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d') as period"),
                'weekly' => DB::raw("DATE_FORMAT(created_at, '%Y-%u') as period"),
                'monthly' => DB::raw("DATE_FORMAT(created_at, '%Y-%m') as period"),
            },
            DB::raw('COUNT(*) as count')
        )
            ->whereNotNull('email_verified_at')
            ->where('email', '!=', DemoService::DEMO_EMAIL)
            ->whereNotNull('google_oauth_id')
            ->whereNull('password')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        // Hybrid users trend (users with both password and google_oauth_id)
        $hybridUsersTrend = User::select(
            match ($formatKey) {
                'daily' => DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d') as period"),
                'weekly' => DB::raw("DATE_FORMAT(created_at, '%Y-%u') as period"),
                'monthly' => DB::raw("DATE_FORMAT(created_at, '%Y-%m') as period"),
            },
            DB::raw('COUNT(*) as count')
        )
            ->whereNotNull('email_verified_at')
            ->where('email', '!=', DemoService::DEMO_EMAIL)
            ->whereNotNull('password')
            ->whereNotNull('google_oauth_id')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        // Schedules trend (only claimed, excluding demo roles)
        $schedulesTrend = Role::select(
            match ($formatKey) {
                'daily' => DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d') as period"),
                'weekly' => DB::raw("DATE_FORMAT(created_at, '%Y-%u') as period"),
                'monthly' => DB::raw("DATE_FORMAT(created_at, '%Y-%m') as period"),
            },
            DB::raw('COUNT(*) as count')
        )
            ->whereNotNull('user_id')
            ->where(function ($query) {
                $query->whereNotNull('email_verified_at')
                    ->orWhereNotNull('phone_verified_at');
            })
            ->where('subdomain', '!=', DemoService::DEMO_ROLE_SUBDOMAIN)
            ->where('subdomain', 'not like', 'demo-%')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        // Events trend (excluding demo events)
        $eventsTrend = Event::select(
            match ($formatKey) {
                'daily' => DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d') as period"),
                'weekly' => DB::raw("DATE_FORMAT(created_at, '%Y-%u') as period"),
                'monthly' => DB::raw("DATE_FORMAT(created_at, '%Y-%m') as period"),
            },
            DB::raw('COUNT(*) as count')
        )
            ->whereDoesntHave('roles', function ($query) {
                $query->where('subdomain', DemoService::DEMO_ROLE_SUBDOMAIN)
                    ->orWhere('subdomain', 'like', 'demo-%');
            })
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        // Revenue trend (from analytics_events_daily)
        $revenueTrend = AnalyticsEventsDaily::select(
            match ($formatKey) {
                'daily' => DB::raw("DATE_FORMAT(date, '%Y-%m-%d') as period"),
                'weekly' => DB::raw("DATE_FORMAT(date, '%Y-%u') as period"),
                'monthly' => DB::raw("DATE_FORMAT(date, '%Y-%m') as period"),
            },
            DB::raw('SUM(revenue) as total')
        )
            ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
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
        $emailUsersData = $allPeriods->map(fn ($period) => $emailUsersTrend->firstWhere('period', $period)?->count ?? 0)->toArray();
        $googleUsersData = $allPeriods->map(fn ($period) => $googleUsersTrend->firstWhere('period', $period)?->count ?? 0)->toArray();
        $hybridUsersData = $allPeriods->map(fn ($period) => $hybridUsersTrend->firstWhere('period', $period)?->count ?? 0)->toArray();

        $revenueData = $allPeriods->map(fn ($period) => (float) ($revenueTrend->firstWhere('period', $period)?->total ?? 0))->toArray();

        return [
            'labels' => $labels,
            'users' => $usersData,
            'schedules' => $schedulesData,
            'events' => $eventsData,
            'emailUsers' => $emailUsersData,
            'googleUsers' => $googleUsersData,
            'hybridUsers' => $hybridUsersData,
            'revenue' => $revenueData,
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

        // Plan statistics (excluding demo roles)
        $planCounts = Role::select('plan_type', DB::raw('COUNT(*) as count'))
            ->whereNotNull('user_id')
            ->where(function ($query) {
                $query->whereNotNull('email_verified_at')
                    ->orWhereNotNull('phone_verified_at');
            })
            ->where('subdomain', '!=', DemoService::DEMO_ROLE_SUBDOMAIN)
            ->where('subdomain', 'not like', 'demo-%')
            ->groupBy('plan_type')
            ->pluck('count', 'plan_type')
            ->toArray();

        $freeCount = $planCounts['free'] ?? 0;
        $proCount = $planCounts['pro'] ?? 0;
        $enterpriseCount = $planCounts['enterprise'] ?? 0;

        // Active Stripe subscriptions (excluding demo roles)
        $activeSubscriptions = Role::whereHas('subscriptions', function ($query) {
            $query->where('stripe_status', 'active');
        })
            ->where('subdomain', '!=', DemoService::DEMO_ROLE_SUBDOMAIN)
            ->where('subdomain', 'not like', 'demo-%')
            ->count();

        // Expiring in 30 days (excluding demo roles)
        $expiringSoon = Role::where('plan_type', '!=', 'free')
            ->whereNotNull('plan_expires')
            ->whereBetween('plan_expires', [now()->format('Y-m-d'), now()->addDays(30)->format('Y-m-d')])
            ->where('subdomain', '!=', DemoService::DEMO_ROLE_SUBDOMAIN)
            ->where('subdomain', 'not like', 'demo-%')
            ->count();

        // Build query for role list (excluding demo roles)
        $query = Role::whereNotNull('user_id')
            ->where(function ($q) {
                $q->whereNotNull('email_verified_at')
                    ->orWhereNotNull('phone_verified_at');
            })
            ->where('subdomain', '!=', DemoService::DEMO_ROLE_SUBDOMAIN)
            ->where('subdomain', 'not like', 'demo-%')
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
            'plan_term' => 'nullable|in:month,year',
            'plan_expires' => 'nullable|date',
        ]);

        $role->plan_type = $validated['plan_type'];
        $role->plan_term = $validated['plan_term'] ?? 'year';
        $role->plan_expires = $validated['plan_expires'];
        $role->save();

        return redirect()->route('admin.plans')->with('success', 'Plan updated successfully for '.$role->name);
    }
}
