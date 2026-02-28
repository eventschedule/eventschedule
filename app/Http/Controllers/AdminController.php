<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminPlanUpdateRequest;
use App\Models\AnalyticsDaily;
use App\Models\AnalyticsEventsDaily;
use App\Models\AnalyticsReferrersDaily;
use App\Models\AuditLog;
use App\Models\BoostBillingRecord;
use App\Models\BoostCampaign;
use App\Models\Event;
use App\Models\EventPart;
use App\Models\EventRole;
use App\Models\Newsletter;
use App\Models\Role;
use App\Models\Sale;
use App\Models\UsageDaily;
use App\Models\User;
use App\Services\AuditService;
use App\Services\BoostBillingService;
use App\Services\DemoService;
use App\Services\DigitalOceanService;
use App\Utils\UrlUtils;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Laravel\Cashier\Subscription;

class AdminController extends Controller
{
    /**
     * Show the admin password confirmation form.
     */
    public function showConfirmPassword(Request $request): View|RedirectResponse
    {
        if (! $request->user()->isAdmin()) {
            return redirect()->route('home');
        }

        return view('admin.confirm-password');
    }

    /**
     * Confirm the admin's password and bind session to User Agent.
     */
    public function confirmPassword(Request $request): RedirectResponse
    {
        if (! $request->user()->isAdmin()) {
            return redirect()->route('home');
        }

        $request->validate([
            'password' => 'required|string',
        ]);

        if (! Auth::guard('web')->validate([
            'email' => $request->user()->email,
            'password' => $request->password,
        ])) {
            AuditService::log(
                AuditService::ADMIN_PASSWORD_FAILED,
                $request->user()->id,
            );

            throw ValidationException::withMessages([
                'password' => __('auth.password'),
            ]);
        }

        $request->session()->put('admin_password_confirmed_at', time());

        AuditService::log(
            AuditService::ADMIN_PASSWORD_CONFIRMED,
            $request->user()->id,
        );

        return redirect()->intended(route('admin.dashboard', absolute: false));
    }

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

        // Recent schedules (only claimed, excluding demo roles)
        $recentSchedules = Role::with('users')
            ->whereNotNull('user_id')
            ->where(function ($query) {
                $query->whereNotNull('email_verified_at')
                    ->orWhereNotNull('phone_verified_at');
            })
            ->where('subdomain', '!=', DemoService::DEMO_ROLE_SUBDOMAIN)
            ->where('subdomain', 'not like', 'demo-%')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        // Recent events (excluding demo and private events)
        $recentEvents = Event::with('roles')
            ->where('is_private', false)
            ->whereDoesntHave('roles', function ($query) {
                $query->where('subdomain', DemoService::DEMO_ROLE_SUBDOMAIN)
                    ->orWhere('subdomain', 'like', 'demo-%');
            })
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        // Trends data - users, schedules, events over time
        $trendData = $this->getTrendData($startDate, $endDate);

        // Private event counts
        $privateEvents = Event::where('is_private', true)
            ->whereDoesntHave('roles', function ($query) {
                $query->where('subdomain', DemoService::DEMO_ROLE_SUBDOMAIN)
                    ->orWhere('subdomain', 'like', 'demo-%');
            })->count();
        $passwordProtectedEvents = Event::where('is_private', true)
            ->whereNotNull('event_password')
            ->where('event_password', '!=', '')
            ->whereDoesntHave('roles', function ($query) {
                $query->where('subdomain', DemoService::DEMO_ROLE_SUBDOMAIN)
                    ->orWhere('subdomain', 'like', 'demo-%');
            })->count();

        // Boost & Newsletter stats
        $activeBoostCampaigns = BoostCampaign::where('status', 'active')->count();
        $boostMarkupRevenue = BoostBillingRecord::where('type', 'charge')
            ->where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('markup_amount');
        $adminNewslettersSent = Newsletter::admin()->where('status', 'sent')->count();
        $newsletterSubscribers = User::whereNotNull('email_verified_at')
            ->where('email', '!=', DemoService::DEMO_EMAIL)
            ->whereNull('admin_newsletter_unsubscribed_at')
            ->count();

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
            'privateEvents',
            'passwordProtectedEvents',
            'trendData',
            'range',
            'activeBoostCampaigns',
            'boostMarkupRevenue',
            'adminNewslettersSent',
            'newsletterSubscribers',
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

        // Newsletter subscriber stats
        $newsletterSubscribed = User::whereNotNull('email_verified_at')
            ->where('email', '!=', DemoService::DEMO_EMAIL)
            ->whereNull('admin_newsletter_unsubscribed_at')
            ->count();
        $newsletterUnsubscribed = User::whereNotNull('email_verified_at')
            ->where('email', '!=', DemoService::DEMO_EMAIL)
            ->whereNotNull('admin_newsletter_unsubscribed_at')
            ->count();

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

        // Top referrer domains (all time)
        $topReferrerDomains = User::whereNotNull('email_verified_at')
            ->where('email', '!=', DemoService::DEMO_EMAIL)
            ->whereNotNull('referrer_url')
            ->where('referrer_url', '!=', '')
            ->select(DB::raw("SUBSTRING_INDEX(SUBSTRING_INDEX(REPLACE(REPLACE(referrer_url, 'https://', ''), 'http://', ''), '/', 1), '?', 1) as domain"), DB::raw('COUNT(*) as count'))
            ->groupBy('domain')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        // Recent signups with UTM data (paginated)
        $recentSignups = User::whereNotNull('email_verified_at')
            ->where('email', '!=', DemoService::DEMO_EMAIL)
            ->orderByDesc('created_at')
            ->paginate(20, ['name', 'email', 'created_at', 'utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term', 'referrer_url', 'landing_page'])
            ->withQueryString();

        return view('admin.users', compact(
            'totalUsers',
            'usersInPeriod',
            'usersChangePercent',
            'activeUsers7Days',
            'activeUsers30Days',
            'newsletterSubscribed',
            'newsletterUnsubscribed',
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
            'topReferrerDomains',
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
        $refundRate = ($totalSales + $refundedSales) > 0 ? round(($refundedSales / ($totalSales + $refundedSales)) * 100, 1) : 0;

        $pendingSales = Sale::where('status', 'pending')->count();
        $pendingRevenue = Sale::where('status', 'pending')->sum('payment_amount');

        // Boost markup revenue
        $boostMarkupTotal = BoostBillingRecord::where('type', 'charge')
            ->where('status', 'completed')
            ->sum('markup_amount');
        $boostMarkupInPeriod = BoostBillingRecord::where('type', 'charge')
            ->where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('markup_amount');

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

        // Amount mismatch sales and boosts
        $mismatchSales = Sale::with('event:id,name')
            ->where('status', 'amount_mismatch')
            ->orderByDesc('created_at')
            ->get();

        $mismatchBoosts = BoostCampaign::with('event:id,name', 'user:id,name,email')
            ->where('billing_status', 'amount_mismatch')
            ->orderByDesc('created_at')
            ->get();

        // Recent sales for detailed table
        $recentSales = Sale::with('event:id,name')
            ->where('subdomain', '!=', DemoService::DEMO_ROLE_SUBDOMAIN)
            ->where('subdomain', 'not like', 'demo-%')
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        return view('admin.revenue', compact(
            'totalRevenue',
            'revenueInPeriod',
            'totalSales',
            'salesInPeriod',
            'refundRate',
            'pendingSales',
            'pendingRevenue',
            'boostMarkupTotal',
            'boostMarkupInPeriod',
            'activeSubscriptions',
            'trialingSubscriptions',
            'canceledSubscriptions',
            'pastDueSubscriptions',
            'rolesOnTrial',
            'convertedFromTrial',
            'expiredTrialsNoSub',
            'trendData',
            'range',
            'recentSales',
            'mismatchSales',
            'mismatchBoosts'
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
            ->whereDoesntHave('role', fn ($q) => $q->where('subdomain', DemoService::DEMO_ROLE_SUBDOMAIN)->orWhere('subdomain', 'like', 'demo-%'))
            ->selectRaw('SUM(desktop_views) as desktop, SUM(mobile_views) as mobile, SUM(tablet_views) as tablet, SUM(unknown_views) as unknown')
            ->first();

        $desktopViews = $totalViews->desktop ?? 0;
        $mobileViews = $totalViews->mobile ?? 0;
        $tabletViews = $totalViews->tablet ?? 0;
        $totalPageViews = $desktopViews + $mobileViews + $tabletViews + ($totalViews->unknown ?? 0);

        $trafficSources = AnalyticsReferrersDaily::whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->whereDoesntHave('role', fn ($q) => $q->where('subdomain', DemoService::DEMO_ROLE_SUBDOMAIN)->orWhere('subdomain', 'like', 'demo-%'))
            ->selectRaw('source, SUM(views) as views')
            ->groupBy('source')
            ->pluck('views', 'source')
            ->toArray();

        $directViews = $trafficSources['direct'] ?? 0;
        $searchViews = $trafficSources['search'] ?? 0;
        $socialViews = $trafficSources['social'] ?? 0;
        $emailViews = $trafficSources['email'] ?? 0;
        $newsletterViews = $trafficSources['newsletter'] ?? 0;
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
        $stripeConnected = (clone $baseRoleQuery)->whereHas('user', function ($q) {
            $q->whereNotNull('stripe_account_id');
        })->count();
        $stripeOnboarded = (clone $baseRoleQuery)->whereHas('user', function ($q) {
            $q->whereNotNull('stripe_completed_at');
        })->count();
        $stripeEvents = (clone $baseRoleQuery)->whereHas('events', function ($q) {
            $q->where('payment_method', 'stripe');
        })->count();
        $customDomainEnabled = (clone $baseRoleQuery)->whereNotNull('custom_domain')->count();
        $customCssEnabled = (clone $baseRoleQuery)->whereNotNull('custom_css')->where('custom_css', '!=', '')->count();
        $newsletterEnabled = (clone $baseRoleQuery)->whereHas('newsletters', fn ($q) => $q->where('status', 'sent'))->count();
        $boostEnabled = (clone $baseRoleQuery)->whereHas('boostCampaigns')->count();

        $googleCalendarPercent = $totalSchedules > 0 ? round(($googleCalendarEnabled / $totalSchedules) * 100, 1) : 0;
        $stripeConnectedPercent = $totalSchedules > 0 ? round(($stripeConnected / $totalSchedules) * 100, 1) : 0;
        $stripeOnboardedPercent = $totalSchedules > 0 ? round(($stripeOnboarded / $totalSchedules) * 100, 1) : 0;
        $stripeEventsPercent = $totalSchedules > 0 ? round(($stripeEvents / $totalSchedules) * 100, 1) : 0;
        $customDomainPercent = $totalSchedules > 0 ? round(($customDomainEnabled / $totalSchedules) * 100, 1) : 0;
        $customCssPercent = $totalSchedules > 0 ? round(($customCssEnabled / $totalSchedules) * 100, 1) : 0;
        $newsletterPercent = $totalSchedules > 0 ? round(($newsletterEnabled / $totalSchedules) * 100, 1) : 0;
        $boostPercent = $totalSchedules > 0 ? round(($boostEnabled / $totalSchedules) * 100, 1) : 0;

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
            'newsletterViews',
            'otherViews',
            'googleCalendarEnabled',
            'stripeConnected',
            'stripeOnboarded',
            'stripeEvents',
            'customDomainEnabled',
            'customCssEnabled',
            'newsletterEnabled',
            'boostEnabled',
            'googleCalendarPercent',
            'stripeConnectedPercent',
            'stripeOnboardedPercent',
            'stripeEventsPercent',
            'customDomainPercent',
            'customCssPercent',
            'newsletterPercent',
            'boostPercent',
            'topSchedulesByEvents',
            'totalSchedules',
            'range'
        ));
    }

    /**
     * Display the admin usage page.
     */
    public function usage(Request $request)
    {
        if (! auth()->user()->isAdmin()) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        // Get date range
        $range = $request->input('range', 'last_30_days');
        $dates = $this->getDateRange($range);
        $startDate = $dates['start'];
        $endDate = $dates['end'];

        $today = now()->toDateString();
        $daysInRange = max(1, $startDate->diffInDays($endDate));

        // Category prefixes for grouping
        $categories = [
            'email' => ['prefix' => 'email_', 'label' => 'Emails', 'limit_key' => 'email_daily_limit'],
            'gemini' => ['prefix' => 'gemini_', 'label' => 'AI / Gemini', 'limit_key' => 'ai_daily_limit'],
            'gcal' => ['prefix' => 'gcal_', 'label' => 'Google Calendar', 'limit_key' => 'gcal_daily_limit'],
            'stripe' => ['prefix' => 'stripe_', 'label' => 'Stripe', 'limit_key' => 'stripe_daily_limit'],
            'invoiceninja' => ['prefix' => 'invoiceninja_', 'label' => 'Invoice Ninja', 'limit_key' => 'invoice_ninja_daily_limit'],
            'caldav' => ['prefix' => 'caldav_', 'label' => 'CalDAV', 'limit_key' => 'caldav_daily_limit'],
            'youtube' => ['prefix' => 'youtube_', 'label' => 'YouTube', 'limit_key' => null],
        ];

        // Summary totals per category for selected period
        $allUsage = UsageDaily::inDateRange($startDate, $endDate)
            ->select('operation', DB::raw('SUM(`count`) as total'))
            ->groupBy('operation')
            ->pluck('total', 'operation')
            ->toArray();

        // Today's totals per operation
        $todayUsage = UsageDaily::where('date', $today)
            ->select('operation', DB::raw('SUM(`count`) as total'))
            ->groupBy('operation')
            ->pluck('total', 'operation')
            ->toArray();

        // Build category summaries
        $categorySummaries = [];
        foreach ($categories as $key => $cat) {
            $periodTotal = 0;
            $todayTotal = 0;
            foreach ($allUsage as $op => $total) {
                if (str_starts_with($op, $cat['prefix'])) {
                    $periodTotal += $total;
                }
            }
            foreach ($todayUsage as $op => $total) {
                if (str_starts_with($op, $cat['prefix'])) {
                    $todayTotal += $total;
                }
            }
            $categorySummaries[$key] = [
                'label' => $cat['label'],
                'period_total' => $periodTotal,
                'today_total' => $todayTotal,
                'daily_avg' => round($periodTotal / $daysInRange, 1),
                'limit' => $cat['limit_key'] ? config('usage.'.$cat['limit_key'], 0) : null,
            ];
        }

        // Operation breakdown table
        $operationBreakdown = [];
        $allOperations = array_unique(array_merge(array_keys($allUsage), array_keys($todayUsage)));
        sort($allOperations);
        foreach ($allOperations as $op) {
            $operationBreakdown[] = [
                'operation' => $op,
                'today' => $todayUsage[$op] ?? 0,
                'period_total' => $allUsage[$op] ?? 0,
                'daily_avg' => round(($allUsage[$op] ?? 0) / $daysInRange, 1),
            ];
        }

        // Top roles by usage
        $topRoles = UsageDaily::inDateRange($startDate, $endDate)
            ->where('role_id', '>', 0)
            ->whereNotIn('role_id', function ($q) {
                $q->select('id')->from('roles')
                    ->where('subdomain', DemoService::DEMO_ROLE_SUBDOMAIN)
                    ->orWhere('subdomain', 'like', 'demo-%');
            })
            ->select('role_id', DB::raw('SUM(`count`) as total'))
            ->groupBy('role_id')
            ->orderByDesc('total')
            ->limit(20)
            ->get();

        // Load role details and per-category breakdown
        $roleIds = $topRoles->pluck('role_id')->toArray();
        $roleMap = Role::whereIn('id', $roleIds)->pluck('subdomain', 'id')->toArray();

        $roleBreakdowns = [];
        if (! empty($roleIds)) {
            $roleUsageRows = UsageDaily::inDateRange($startDate, $endDate)
                ->whereIn('role_id', $roleIds)
                ->select('role_id', 'operation', DB::raw('SUM(`count`) as total'))
                ->groupBy('role_id', 'operation')
                ->get();

            foreach ($roleUsageRows as $row) {
                if (! isset($roleBreakdowns[$row->role_id])) {
                    $roleBreakdowns[$row->role_id] = [];
                }
                $roleBreakdowns[$row->role_id][$row->operation] = $row->total;
            }
        }

        $topRolesData = $topRoles->map(function ($row) use ($roleMap, $roleBreakdowns, $categories) {
            $breakdown = $roleBreakdowns[$row->role_id] ?? [];
            $catTotals = [];
            foreach ($categories as $key => $cat) {
                $catTotal = 0;
                foreach ($breakdown as $op => $total) {
                    if (str_starts_with($op, $cat['prefix'])) {
                        $catTotal += $total;
                    }
                }
                $catTotals[$key] = $catTotal;
            }

            return [
                'role_id' => $row->role_id,
                'subdomain' => $roleMap[$row->role_id] ?? 'unknown',
                'total' => $row->total,
                'categories' => $catTotals,
            ];
        });

        // Today's anomalies
        $anomalies = [];
        foreach ($categorySummaries as $key => $cat) {
            if ($cat['limit'] && $cat['today_total'] > $cat['limit']) {
                $anomalies[] = [
                    'category' => $cat['label'],
                    'today' => $cat['today_total'],
                    'limit' => $cat['limit'],
                ];
            }
        }

        // Stuck translation records
        $stuckThreshold = config('usage.stuck_translation_attempts', 3);

        $stuckRoles = Role::where('translation_attempts', '>=', $stuckThreshold)
            ->where(function ($q) {
                $q->where(function ($sub) {
                    $sub->whereNotNull('name')->where('name', '!=', '')->whereNull('name_en');
                })
                    ->orWhere(function ($sub) {
                        $sub->whereNotNull('description')->where('description', '!=', '')->whereNull('description_en');
                    })
                    ->orWhere(function ($sub) {
                        $sub->whereNotNull('address1')->where('address1', '!=', '')->whereNull('address1_en');
                    })
                    ->orWhere(function ($sub) {
                        $sub->whereNotNull('city')->where('city', '!=', '')->whereNull('city_en');
                    })
                    ->orWhere(function ($sub) {
                        $sub->whereNotNull('state')->where('state', '!=', '')->whereNull('state_en');
                    })
                    ->orWhere(function ($sub) {
                        $sub->whereNotNull('request_terms')->where('request_terms', '!=', '')->whereNull('request_terms_en');
                    });
            })
            ->where('subdomain', '!=', DemoService::DEMO_ROLE_SUBDOMAIN)
            ->where('subdomain', 'not like', 'demo-%')
            ->orderByDesc('translation_attempts')
            ->limit(20)
            ->get(['id', 'name', 'subdomain', 'translation_attempts', 'last_translated_at', 'language_code', 'description', 'name_en', 'description_en', 'address1', 'address1_en', 'city', 'city_en', 'state', 'state_en', 'request_terms', 'request_terms_en']);

        $stuckEvents = Event::with('venue:id,language_code')
            ->where('translation_attempts', '>=', $stuckThreshold)
            ->where('is_private', false)
            ->where(function ($q) {
                $q->where(function ($sub) {
                    $sub->whereNotNull('name')->where('name', '!=', '')->whereNull('name_en');
                })
                    ->orWhere(function ($sub) {
                        $sub->whereNotNull('description')->where('description', '!=', '')->whereNull('description_en');
                    });
            })
            ->whereDoesntHave('roles', fn ($q) => $q->where('subdomain', DemoService::DEMO_ROLE_SUBDOMAIN)->orWhere('subdomain', 'like', 'demo-%'))
            ->orderByDesc('translation_attempts')
            ->limit(20)
            ->get(['id', 'name', 'description', 'translation_attempts', 'last_translated_at', 'name_en', 'description_en']);

        $stuckEventParts = EventPart::with('event.venue:id,language_code')
            ->where('translation_attempts', '>=', $stuckThreshold)
            ->where(function ($q) {
                $q->where(function ($sub) {
                    $sub->whereNotNull('name')->where('name', '!=', '')->whereNull('name_en');
                })
                    ->orWhere(function ($sub) {
                        $sub->whereNotNull('description')->where('description', '!=', '')->whereNull('description_en');
                    });
            })
            ->whereHas('event', fn ($q) => $q->where('is_private', false)->whereDoesntHave('roles', fn ($r) => $r->where('subdomain', DemoService::DEMO_ROLE_SUBDOMAIN)->orWhere('subdomain', 'like', 'demo-%')))
            ->orderByDesc('translation_attempts')
            ->limit(20)
            ->get(['id', 'name', 'description', 'event_id', 'translation_attempts', 'last_translated_at', 'name_en', 'description_en']);

        $stuckEventRoles = EventRole::with(['event:id,name,description', 'role:id,subdomain,language_code'])
            ->where('translation_attempts', '>=', $stuckThreshold)
            ->where(function ($q) {
                $q->where(function ($sub) {
                    $sub->whereNull('name_translated')
                        ->whereHas('event', fn ($e) => $e->where('is_private', false)->whereNotNull('name')->where('name', '!=', ''));
                })
                    ->orWhere(function ($sub) {
                        $sub->whereNull('description_translated')
                            ->whereHas('event', fn ($e) => $e->where('is_private', false)->whereNotNull('description')->where('description', '!=', ''));
                    });
            })
            ->whereHas('role', fn ($q) => $q->where('subdomain', '!=', DemoService::DEMO_ROLE_SUBDOMAIN)->where('subdomain', 'not like', 'demo-%'))
            ->orderByDesc('translation_attempts')
            ->limit(20)
            ->get(['id', 'event_id', 'role_id', 'translation_attempts', 'last_translated_at', 'name_translated', 'description_translated']);

        return view('admin.usage', compact(
            'categorySummaries',
            'operationBreakdown',
            'topRolesData',
            'anomalies',
            'stuckRoles',
            'stuckEvents',
            'stuckEventParts',
            'stuckEventRoles',
            'stuckThreshold',
            'range',
            'categories'
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
        $stripePaidCount = Role::whereHas('subscriptions', function ($query) {
            $query->where('stripe_status', 'active');
        })
            ->where('subdomain', '!=', DemoService::DEMO_ROLE_SUBDOMAIN)
            ->where('subdomain', 'not like', 'demo-%')
            ->count();

        // Manually assigned paid plans (have plan_expires, not free, no active Stripe subscription)
        $manualPlanCount = Role::where('plan_type', '!=', 'free')
            ->whereNotNull('plan_expires')
            ->where('plan_expires', '>=', now()->format('Y-m-d'))
            ->whereDoesntHave('subscriptions', function ($query) {
                $query->where('stripe_status', 'active');
            })
            ->whereNull('trial_ends_at')
            ->where('subdomain', '!=', DemoService::DEMO_ROLE_SUBDOMAIN)
            ->where('subdomain', 'not like', 'demo-%')
            ->count();

        // Schedules currently on trial (excluding demo roles)
        $trialCount = Role::whereNotNull('trial_ends_at')
            ->where('trial_ends_at', '>', now())
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
            ->with(['user', 'subscriptions']);

        // Search filter
        if ($search = $request->input('search')) {
            $search = str_replace(['%', '_'], ['\\%', '\\_'], $search);
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

        // Source filter
        if ($source = $request->input('source')) {
            if ($source === 'stripe') {
                $query->whereHas('subscriptions', function ($sq) {
                    $sq->where('stripe_status', 'active');
                });
            } elseif ($source === 'manual') {
                $query->where('plan_type', '!=', 'free')
                    ->whereNotNull('plan_expires')
                    ->where('plan_expires', '>=', now()->format('Y-m-d'))
                    ->whereDoesntHave('subscriptions', function ($sq) {
                        $sq->where('stripe_status', 'active');
                    })
                    ->whereNull('trial_ends_at');
            } elseif ($source === 'trial') {
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
            'stripePaidCount',
            'manualPlanCount',
            'trialCount',
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
    public function updatePlan(AdminPlanUpdateRequest $request, $roleId)
    {
        if (! auth()->user()->isAdmin()) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $decodedId = UrlUtils::decodeId($roleId);
        $role = Role::findOrFail($decodedId);

        $validated = $request->validated();

        $oldValues = [
            'plan_type' => $role->plan_type,
            'plan_term' => $role->plan_term,
            'plan_expires' => $role->plan_expires,
        ];

        $role->plan_type = $validated['plan_type'];
        $role->plan_term = $validated['plan_term'] ?? 'year';
        $role->plan_expires = $validated['plan_expires'];
        $role->save();

        AuditService::log(
            AuditService::ADMIN_PLAN_UPDATE,
            auth()->id(),
            'App\\Models\\Role',
            $role->id,
            $oldValues,
            [
                'plan_type' => $role->plan_type,
                'plan_term' => $role->plan_term,
                'plan_expires' => $role->plan_expires,
            ],
            "Plan updated for {$role->subdomain}",
        );

        return redirect()->route('admin.plans')->with('success', 'Plan updated successfully for '.$role->name.'.');
    }

    /**
     * Retry translation for a stuck record by resetting translation_attempts to 0.
     */
    public function retryTranslation(Request $request)
    {
        if (! auth()->user()->isAdmin()) {
            return response()->json(['error' => 'Not authorized'], 403);
        }

        $type = $request->input('type');
        $id = $request->input('id');

        $model = match ($type) {
            'role' => Role::find($id),
            'event' => Event::find($id),
            'event_part' => EventPart::find($id),
            'event_role' => EventRole::find($id),
            default => null,
        };

        if (! $model) {
            return response()->json(['error' => 'Record not found'], 404);
        }

        $model->translation_attempts = 0;
        $model->save();

        return response()->json(['success' => true]);
    }

    /**
     * Display the admin queue management page.
     */
    public function queue()
    {
        if (! auth()->user()->isAdmin()) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        // Count totals
        $pendingJobsCount = DB::table('jobs')->count();
        $failedJobsCount = DB::table('failed_jobs')->count();
        $jobBatchesCount = DB::table('job_batches')->count();

        // Oldest pending job age
        $oldestPendingJob = DB::table('jobs')->orderBy('created_at')->first();
        $oldestJobAge = $oldestPendingJob ? Carbon::createFromTimestamp($oldestPendingJob->created_at) : null;

        // Group pending jobs by queue
        $pendingByQueue = DB::table('jobs')
            ->select('queue', DB::raw('COUNT(*) as count'))
            ->groupBy('queue')
            ->orderByDesc('count')
            ->get();

        // Group pending jobs by class name
        $pendingJobs = DB::table('jobs')->orderByDesc('id')->limit(100)->get();
        $pendingByClass = $pendingJobs->groupBy(function ($job) {
            return $this->extractJobClassName($job->payload);
        })->map->count()->sortDesc();

        // Parse pending jobs for table
        $pendingJobsTable = $pendingJobs->map(function ($job) {
            return (object) [
                'id' => $job->id,
                'class_name' => $this->extractJobClassName($job->payload),
                'queue' => $job->queue,
                'attempts' => $job->attempts,
                'created_at' => Carbon::createFromTimestamp($job->created_at),
                'available_at' => Carbon::createFromTimestamp($job->available_at),
            ];
        });

        // Failed jobs for table
        $failedJobs = DB::table('failed_jobs')->orderByDesc('failed_at')->limit(100)->get()->map(function ($job) {
            return (object) [
                'id' => $job->id,
                'uuid' => $job->uuid,
                'class_name' => $this->extractJobClassName($job->payload),
                'queue' => $job->queue,
                'exception' => $job->exception,
                'exception_excerpt' => \Illuminate\Support\Str::limit($job->exception, 200),
                'failed_at' => Carbon::parse($job->failed_at),
            ];
        });

        // Job batches
        $jobBatches = DB::table('job_batches')->orderByDesc('created_at')->limit(50)->get()->map(function ($batch) {
            $progress = $batch->total_jobs > 0
                ? round((($batch->total_jobs - $batch->pending_jobs) / $batch->total_jobs) * 100, 1)
                : 0;

            return (object) [
                'id' => $batch->id,
                'name' => $batch->name,
                'total_jobs' => $batch->total_jobs,
                'pending_jobs' => $batch->pending_jobs,
                'failed_jobs' => $batch->failed_jobs,
                'progress' => $progress,
                'created_at' => Carbon::createFromTimestamp($batch->created_at),
                'finished_at' => $batch->finished_at ? Carbon::createFromTimestamp($batch->finished_at) : null,
            ];
        });

        return view('admin.queue', compact(
            'pendingJobsCount',
            'failedJobsCount',
            'jobBatchesCount',
            'oldestJobAge',
            'pendingByQueue',
            'pendingByClass',
            'pendingJobsTable',
            'failedJobs',
            'jobBatches'
        ));
    }

    /**
     * Extract the short class name from a job payload.
     */
    private function extractJobClassName(string $payload): string
    {
        $data = json_decode($payload, true);

        $className = $data['displayName'] ?? $data['data']['commandName'] ?? 'Unknown';

        return class_basename($className);
    }

    /**
     * Retry a single failed job.
     */
    public function queueRetry($id)
    {
        if (! auth()->user()->isAdmin()) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        try {
            Artisan::call('queue:retry', ['id' => [$id]]);
        } catch (\Exception $e) {
            Log::error('Failed to retry job', ['id' => $id, 'error' => $e->getMessage()]);

            return redirect()->route('admin.queue')->with('error', 'Failed to retry job.');
        }

        return redirect()->route('admin.queue')->with('success', 'Job queued for retry.');
    }

    /**
     * Delete a single failed job.
     */
    public function queueDelete($id)
    {
        if (! auth()->user()->isAdmin()) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        try {
            Artisan::call('queue:forget', ['id' => $id]);
        } catch (\Exception $e) {
            Log::error('Failed to delete job', ['id' => $id, 'error' => $e->getMessage()]);

            return redirect()->route('admin.queue')->with('error', 'Failed to delete job.');
        }

        return redirect()->route('admin.queue')->with('success', 'Failed job deleted.');
    }

    /**
     * Retry all failed jobs.
     */
    public function queueRetryAll()
    {
        if (! auth()->user()->isAdmin()) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        try {
            Artisan::call('queue:retry', ['id' => ['all']]);
        } catch (\Exception $e) {
            Log::error('Failed to retry all jobs', ['error' => $e->getMessage()]);

            return redirect()->route('admin.queue')->with('error', 'Failed to retry jobs.');
        }

        return redirect()->route('admin.queue')->with('success', 'All failed jobs queued for retry.');
    }

    /**
     * Clear all failed jobs.
     */
    public function queueClearFailed()
    {
        if (! auth()->user()->isAdmin()) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        try {
            Artisan::call('queue:flush');
        } catch (\Exception $e) {
            Log::error('Failed to clear failed jobs', ['error' => $e->getMessage()]);

            return redirect()->route('admin.queue')->with('error', 'Failed to clear failed jobs.');
        }

        return redirect()->route('admin.queue')->with('success', 'All failed jobs cleared.');
    }

    /**
     * Flush all pending jobs.
     */
    public function queueFlushPending()
    {
        if (! auth()->user()->isAdmin()) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        try {
            DB::table('jobs')->truncate();
        } catch (\Exception $e) {
            Log::error('Failed to flush pending jobs', ['error' => $e->getMessage()]);

            return redirect()->route('admin.queue')->with('error', 'Failed to flush pending jobs.');
        }

        return redirect()->route('admin.queue')->with('success', 'All pending jobs flushed.');
    }

    /**
     * Display the log viewer page.
     */
    public function logs(Request $request)
    {
        if (! auth()->user()->isAdmin()) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $logPath = storage_path('logs/laravel.log');
        $fileExists = file_exists($logPath);
        $fileSize = $fileExists ? filesize($logPath) : 0;

        $allEntries = collect();

        if ($fileExists && $fileSize > 0) {
            $allEntries = $this->parseLogEntries($logPath, 5 * 1024 * 1024);
        }

        // Count levels
        $levelCounts = $allEntries->groupBy('level')->map->count();

        // Build repeated errors grouping
        $repeatedErrors = $allEntries
            ->filter(fn ($e) => in_array($e['level'], ['ERROR', 'CRITICAL', 'EMERGENCY', 'ALERT']))
            ->groupBy(fn ($e) => $this->normalizeErrorMessage($e['message']))
            ->map(fn ($group) => [
                'count' => $group->count(),
                'message' => $group->first()['message'],
                'last_seen' => $group->first()['timestamp'],
                'first_seen' => $group->last()['timestamp'],
                'stack_trace' => $group->first()['stack_trace'],
                'level' => $group->first()['level'],
            ])
            ->sortByDesc('count')
            ->filter(fn ($group) => $group['count'] >= 2);

        // Apply filters for entries table
        $entries = $allEntries;

        if ($request->filled('level')) {
            $entries = $entries->filter(fn ($e) => $e['level'] === $request->input('level'));
        }

        if ($request->filled('search')) {
            $search = strtolower($request->input('search'));
            $entries = $entries->filter(fn ($e) => str_contains(strtolower($e['message']), $search)
                || str_contains(strtolower($e['stack_trace'] ?? ''), $search));
        }

        $totalCount = $allEntries->count();
        $filteredCount = $entries->count();
        $entries = $entries->take(200);

        $levels = ['EMERGENCY', 'ALERT', 'CRITICAL', 'ERROR', 'WARNING', 'NOTICE', 'INFO', 'DEBUG'];

        return view('admin.logs', compact(
            'entries',
            'repeatedErrors',
            'fileSize',
            'fileExists',
            'levels',
            'levelCounts',
            'totalCount',
            'filteredCount',
        ));
    }

    /**
     * Clear the log file.
     */
    public function logsClear()
    {
        if (! auth()->user()->isAdmin()) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $logPath = storage_path('logs/laravel.log');

        if (file_exists($logPath)) {
            file_put_contents($logPath, '');
        }

        return redirect()->route('admin.logs')->with('success', 'Log file cleared.');
    }

    /**
     * Download the log file.
     */
    public function logsDownload()
    {
        if (! auth()->user()->isAdmin()) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $logPath = storage_path('logs/laravel.log');

        if (! file_exists($logPath)) {
            return redirect()->route('admin.logs')->with('error', 'Log file not found.');
        }

        $filename = 'laravel-'.date('Y-m-d-His').'.log';

        return response()->download($logPath, $filename);
    }

    /**
     * Parse log entries from the end of the file up to $maxBytes.
     */
    private function parseLogEntries(string $logPath, int $maxBytes): \Illuminate\Support\Collection
    {
        $fileSize = filesize($logPath);
        $readFrom = max(0, $fileSize - $maxBytes);

        $handle = fopen($logPath, 'r');
        if (! $handle) {
            return collect();
        }

        fseek($handle, $readFrom);

        // If we're not at the start, skip the first partial line
        if ($readFrom > 0) {
            fgets($handle);
        }

        $content = fread($handle, $maxBytes);
        fclose($handle);

        if (! $content) {
            return collect();
        }

        // Split on log entry boundaries: [YYYY-MM-DD HH:MM:SS]
        $pattern = '/(?=\[\d{4}-\d{2}-\d{2}[T ]\d{2}:\d{2}:\d{2})/';
        $rawEntries = preg_split($pattern, $content, -1, PREG_SPLIT_NO_EMPTY);

        $entries = collect();

        foreach ($rawEntries as $raw) {
            $entry = $this->parseLogEntry(trim($raw));
            if ($entry) {
                $entries->push($entry);
            }
        }

        // Return newest first
        return $entries->reverse()->values();
    }

    /**
     * Parse a single log entry string into a structured array.
     */
    private function parseLogEntry(string $raw): ?array
    {
        // Match: [2024-01-15 10:30:45] production.ERROR: Message here
        if (! preg_match('/^\[(\d{4}-\d{2}-\d{2}[T ]\d{2}:\d{2}:\d{2})[^\]]*\]\s+(\S+)\.(\w+):\s*(.*)/s', $raw, $matches)) {
            return null;
        }

        $timestamp = $matches[1];
        $environment = $matches[2];
        $level = strtoupper($matches[3]);
        $body = $matches[4];

        // Separate message from stack trace
        $message = $body;
        $stackTrace = '';
        $context = '';

        // Look for stack trace starting with #0 or [stacktrace]
        if (preg_match('/^(.*?)(\[stacktrace\].*|#0\s.*)/s', $body, $bodyParts)) {
            $message = trim($bodyParts[1]);
            $stackTrace = trim($bodyParts[2]);
        }

        // Try to extract JSON context from end of message
        if (preg_match('/^(.*?)\s+(\{.*\})\s*$/s', $message, $ctxParts)) {
            $decoded = json_decode($ctxParts[2], true);
            if ($decoded !== null) {
                $message = trim($ctxParts[1]);
                $context = $ctxParts[2];
            }
        }

        return [
            'timestamp' => str_replace('T', ' ', $timestamp),
            'environment' => $environment,
            'level' => $level,
            'message' => $message,
            'context' => $context,
            'stack_trace' => $stackTrace,
        ];
    }

    /**
     * Normalize an error message for grouping repeated errors.
     */
    private function normalizeErrorMessage(string $message): string
    {
        // Extract exception class if present
        if (preg_match('/^([\w\\\\]+Exception|[\w\\\\]+Error):\s*(.*)$/s', $message, $matches)) {
            $class = $matches[1];
            $firstLine = strtok($matches[2], "\n");

            // Strip variable parts (IDs, hashes, timestamps, numbers in specific patterns)
            $firstLine = preg_replace('/\b[0-9a-f]{8,}\b/', '{hash}', $firstLine);
            $firstLine = preg_replace('/\b\d{4,}\b/', '{id}', $firstLine);

            return $class.': '.\Illuminate\Support\Str::limit($firstLine, 120, '');
        }

        // No exception class - use first ~120 chars
        $firstLine = strtok($message, "\n");
        $firstLine = preg_replace('/\b[0-9a-f]{8,}\b/', '{hash}', $firstLine);
        $firstLine = preg_replace('/\b\d{4,}\b/', '{id}', $firstLine);

        return \Illuminate\Support\Str::limit($firstLine, 120, '');
    }

    /**
     * Display the audit log.
     */
    public function auditLog(Request $request)
    {
        if (! auth()->user()->isAdmin()) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $request->validate([
            'from' => 'nullable|date',
            'to' => 'nullable|date',
            'category' => 'nullable|string',
            'search' => 'nullable|string|max:200',
        ]);

        // Summary stats
        $totalEntries = AuditLog::count();
        $entriesToday = AuditLog::whereDate('created_at', today())->count();
        $failedAuthToday = AuditLog::where('action', 'like', 'auth.%fail%')->whereDate('created_at', today())->count();
        $uniqueIpsToday = AuditLog::whereDate('created_at', today())->distinct('ip_address')->count('ip_address');

        $query = AuditLog::with('user')->orderBy('created_at', 'desc');

        // Filter by action category
        if ($request->filled('category')) {
            $category = str_replace(['%', '_'], ['\\%', '\\_'], $request->input('category'));
            $query->where('action', 'like', $category.'.%');
        }

        // Filter by specific user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        // Filter by date range
        if ($request->filled('from')) {
            $query->where('created_at', '>=', $request->input('from'));
        }
        if ($request->filled('to')) {
            $query->where('created_at', '<=', Carbon::parse($request->input('to'))->endOfDay());
        }

        // Search metadata/action
        if ($request->filled('search')) {
            $search = str_replace(['%', '_'], ['\\%', '\\_'], $request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('action', 'like', "%{$search}%")
                    ->orWhere('metadata', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%");
            });
        }

        $logs = $query->paginate(50)->withQueryString();

        $categories = ['auth', 'profile', 'api', 'schedule', 'event', 'sale', 'admin', 'stripe'];

        return view('admin.audit-log', compact('logs', 'categories', 'totalEntries', 'entriesToday', 'failedAuthToday', 'uniqueIpsToday'));
    }

    /**
     * Display the admin boost dashboard.
     */
    public function boost(Request $request)
    {
        if (! auth()->user()->isAdmin()) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $range = $request->input('range', 'last_30_days');
        $dates = $this->getDateRange($range);
        $startDate = $dates['start'];
        $endDate = $dates['end'];

        // Summary metrics
        $totalCampaignsAllTime = BoostCampaign::count();
        $totalCampaignsInPeriod = BoostCampaign::whereBetween('created_at', [$startDate, $endDate])->count();
        $activeCampaigns = BoostCampaign::where('status', 'active')->count();

        $markupRevenue = BoostBillingRecord::where('type', 'charge')
            ->where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('markup_amount');

        $totalAdSpend = BoostCampaign::whereBetween('created_at', [$startDate, $endDate])
            ->sum('actual_spend');

        $totalRefunds = BoostBillingRecord::where('type', 'refund')
            ->where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('amount');

        // Average performance (from campaigns with meaningful statuses)
        $performanceCampaigns = BoostCampaign::whereIn('status', ['active', 'paused', 'completed'])
            ->whereBetween('created_at', [$startDate, $endDate]);

        $avgCtr = (clone $performanceCampaigns)->where('impressions', '>', 0)->avg(DB::raw('clicks / impressions * 100')) ?? 0;
        $avgCpc = (clone $performanceCampaigns)->where('clicks', '>', 0)->avg(DB::raw('actual_spend / clicks')) ?? 0;
        $avgCpm = (clone $performanceCampaigns)->where('impressions', '>', 0)->avg(DB::raw('actual_spend / impressions * 1000')) ?? 0;

        $totalWithStatus = (clone $performanceCampaigns)->count() + BoostCampaign::where('status', 'rejected')->whereBetween('created_at', [$startDate, $endDate])->count();
        $rejectedCount = BoostCampaign::where('status', 'rejected')->whereBetween('created_at', [$startDate, $endDate])->count();
        $rejectionRate = $totalWithStatus > 0 ? ($rejectedCount / $totalWithStatus * 100) : 0;

        // Alerts (not date-filtered)
        $stuckPending = BoostCampaign::with(['event:id,name', 'user:id,name,email', 'role:id,subdomain,name'])
            ->where('status', 'pending_payment')
            ->where('created_at', '<', now()->subMinutes(30))
            ->get();

        $failedCampaigns = BoostCampaign::with(['event:id,name', 'user:id,name,email'])
            ->where('status', 'failed')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $disapprovedCampaigns = BoostCampaign::with(['event:id,name', 'user:id,name,email'])
            ->whereHas('ads', function ($q) {
                $q->where('status', 'DISAPPROVED');
            })
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        // Status distribution
        $statusDistribution = BoostCampaign::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Performance trend data
        $daysDiff = $startDate->diffInDays($endDate);
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

        $dateFormatExpr = match ($formatKey) {
            'daily' => DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d') as period"),
            'weekly' => DB::raw("DATE_FORMAT(created_at, '%Y-%u') as period"),
            'monthly' => DB::raw("DATE_FORMAT(created_at, '%Y-%m') as period"),
        };

        $adSpendTrend = BoostBillingRecord::select(
            $dateFormatExpr,
            DB::raw('SUM(meta_spend) as total')
        )
            ->where('type', 'charge')
            ->where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        $markupTrend = BoostBillingRecord::select(
            match ($formatKey) {
                'daily' => DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d') as period"),
                'weekly' => DB::raw("DATE_FORMAT(created_at, '%Y-%u') as period"),
                'monthly' => DB::raw("DATE_FORMAT(created_at, '%Y-%m') as period"),
            },
            DB::raw('SUM(markup_amount) as total')
        )
            ->where('type', 'charge')
            ->where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        $allPeriods = collect()
            ->merge($adSpendTrend->pluck('period'))
            ->merge($markupTrend->pluck('period'))
            ->unique()
            ->sort()
            ->values();

        $trendLabels = $allPeriods->map(function ($period) use ($labelFormat) {
            if (str_contains($labelFormat, 'W')) {
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

        $adSpendData = $allPeriods->map(fn ($period) => (float) ($adSpendTrend->firstWhere('period', $period)?->total ?? 0))->toArray();
        $markupData = $allPeriods->map(fn ($period) => (float) ($markupTrend->firstWhere('period', $period)?->total ?? 0))->toArray();

        // Top boosters
        $topBoosters = BoostCampaign::select(
            'role_id',
            DB::raw('COUNT(*) as campaign_count'),
            DB::raw('SUM(user_budget) as total_budget'),
            DB::raw('SUM(actual_spend) as total_spend'),
            DB::raw('SUM(impressions) as total_impressions'),
            DB::raw('SUM(clicks) as total_clicks')
        )
            ->groupBy('role_id')
            ->orderByDesc('total_budget')
            ->limit(10)
            ->get();

        $topBoosterRoleIds = $topBoosters->pluck('role_id')->toArray();
        $topBoosterRoles = Role::whereIn('id', $topBoosterRoleIds)->get()->keyBy('id');

        // Grant credit section
        $rolesWithCredit = Role::where('boost_credit', '>', 0)
            ->select('subdomain', 'boost_credit')
            ->orderByDesc('boost_credit')
            ->get();

        // Spending limit section
        $rolesWithLimit = Role::whereNotNull('boost_max_budget')
            ->select('subdomain', 'boost_max_budget')
            ->orderByDesc('boost_max_budget')
            ->get();

        // Campaigns table (paginated, filterable)
        $statusFilter = $request->input('status');
        $campaignsQuery = BoostCampaign::with(['event:id,name', 'user:id,name,email', 'role:id,subdomain,name'])
            ->orderByDesc('created_at');

        if ($statusFilter) {
            $campaignsQuery->where('status', $statusFilter);
        }

        $campaigns = $campaignsQuery->paginate(20)->withQueryString();

        // Recent billing records
        $recentBilling = BoostBillingRecord::with(['campaign:id,name,currency_code'])
            ->orderByDesc('created_at')
            ->limit(30)
            ->get();

        return view('admin.boost', compact(
            'range',
            'totalCampaignsAllTime',
            'totalCampaignsInPeriod',
            'activeCampaigns',
            'markupRevenue',
            'totalAdSpend',
            'totalRefunds',
            'avgCtr',
            'avgCpc',
            'avgCpm',
            'rejectionRate',
            'stuckPending',
            'failedCampaigns',
            'disapprovedCampaigns',
            'statusDistribution',
            'trendLabels',
            'adSpendData',
            'markupData',
            'topBoosters',
            'topBoosterRoles',
            'rolesWithCredit',
            'rolesWithLimit',
            'campaigns',
            'statusFilter',
            'recentBilling',
        ));
    }

    /**
     * Grant boost credit to a schedule.
     */
    public function boostGrantCredit(Request $request): RedirectResponse
    {
        if (! auth()->user()->isAdmin()) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $request->validate([
            'subdomain' => 'required|exists:roles,subdomain',
            'amount' => 'required|numeric|min:1|max:5000',
        ]);

        $role = Role::where('subdomain', $request->subdomain)->firstOrFail();
        $oldCredit = $role->boost_credit;
        $amount = (float) $request->amount;
        $role->increment('boost_credit', $amount);

        AuditService::log(
            AuditService::ADMIN_BOOST_CREDIT,
            auth()->id(),
            'App\\Models\\Role',
            $role->id,
            ['boost_credit' => $oldCredit],
            ['boost_credit' => $oldCredit + $amount],
            "Granted \${$amount} boost credit to {$role->subdomain}",
        );

        return redirect()->back()->with('success', "Granted \${$request->amount} boost credit to {$role->subdomain}");
    }

    /**
     * Set boost spending limit for a schedule.
     */
    public function boostSetLimit(Request $request): RedirectResponse
    {
        if (! auth()->user()->isAdmin()) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $request->validate([
            'subdomain' => 'required|exists:roles,subdomain',
            'amount' => 'required|numeric|min:1|max:'.config('services.meta.max_budget', 1000),
        ]);

        $role = Role::where('subdomain', $request->subdomain)->firstOrFail();
        $oldLimit = $role->boost_max_budget;
        $amount = (float) $request->amount;
        $role->update(['boost_max_budget' => $amount]);

        AuditService::log(
            AuditService::ADMIN_BOOST_LIMIT,
            auth()->id(),
            'App\\Models\\Role',
            $role->id,
            ['boost_max_budget' => $oldLimit],
            ['boost_max_budget' => $amount],
            "Set boost spending limit to \${$amount} for {$role->subdomain}",
        );

        return redirect()->back()->with('success', "Set boost spending limit to \${$request->amount} for {$role->subdomain}");
    }

    /**
     * Approve an amount_mismatch sale (mark as paid).
     */
    public function approveSale(Request $request, $saleId)
    {
        if (! auth()->user()->isAdmin()) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $sale = Sale::find($saleId);

        if (! $sale || $sale->status !== 'amount_mismatch') {
            return redirect()->back()->with('error', __('messages.sale_not_found'));
        }

        DB::transaction(function () use ($sale) {
            $sale = Sale::lockForUpdate()->find($sale->id);
            $sale->status = 'paid';
            $sale->save();

            AnalyticsEventsDaily::incrementSale($sale->event_id, $sale->payment_amount);
            if ($sale->discount_amount > 0) {
                AnalyticsEventsDaily::incrementPromoSale($sale->event_id, $sale->discount_amount);
            }
        });

        AuditService::log(AuditService::ADMIN_UPDATE, auth()->id(), 'Sale', $sale->id, null, null, 'Approved amount_mismatch sale');

        return redirect()->back()->with('success', __('messages.sale_approved'));
    }

    /**
     * Refund an amount_mismatch sale via Stripe.
     */
    public function refundSale(Request $request, $saleId)
    {
        if (! auth()->user()->isAdmin()) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $sale = Sale::find($saleId);

        if (! $sale || $sale->status !== 'amount_mismatch' || ! $sale->transaction_reference) {
            return redirect()->back()->with('error', __('messages.sale_not_found'));
        }

        try {
            $stripe = new \Stripe\StripeClient(config('services.stripe.key'));
            $stripe->refunds->create([
                'payment_intent' => $sale->transaction_reference,
            ]);
        } catch (\Exception $e) {
            // Try platform key if Connect key fails
            try {
                $stripe = new \Stripe\StripeClient(config('services.stripe_platform.secret'));
                $stripe->refunds->create([
                    'payment_intent' => $sale->transaction_reference,
                ]);
            } catch (\Exception $e2) {
                Log::error('Failed to refund amount_mismatch sale', [
                    'sale_id' => $sale->id,
                    'error' => $e2->getMessage(),
                ]);

                return redirect()->back()->with('error', 'Refund failed. Check logs for details.');
            }
        }

        $sale->status = 'refunded';
        $sale->save();

        AuditService::log(AuditService::ADMIN_UPDATE, auth()->id(), 'Sale', $sale->id, null, null, 'Refunded amount_mismatch sale');

        return redirect()->back()->with('success', __('messages.sale_refunded'));
    }

    /**
     * Approve an amount_mismatch boost campaign.
     */
    public function approveBoost(Request $request, $campaignId)
    {
        if (! auth()->user()->isAdmin()) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $campaign = BoostCampaign::find($campaignId);

        if (! $campaign || $campaign->billing_status !== 'amount_mismatch') {
            return redirect()->back()->with('error', __('messages.boost_not_found'));
        }

        $campaign->update(['billing_status' => 'charged']);

        AuditService::log(AuditService::ADMIN_UPDATE, auth()->id(), 'BoostCampaign', $campaign->id, null, null, 'Approved amount_mismatch boost');

        return redirect()->back()->with('success', __('messages.boost_approved'));
    }

    /**
     * Refund an amount_mismatch boost campaign.
     */
    public function refundBoost(Request $request, $campaignId)
    {
        if (! auth()->user()->isAdmin()) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $campaign = BoostCampaign::find($campaignId);

        if (! $campaign || $campaign->billing_status !== 'amount_mismatch') {
            return redirect()->back()->with('error', __('messages.boost_not_found'));
        }

        $billingService = new BoostBillingService;
        $result = $billingService->refundFull($campaign);

        if (! $result) {
            return redirect()->back()->with('error', 'Refund failed. Check logs for details.');
        }

        AuditService::log(AuditService::ADMIN_UPDATE, auth()->id(), 'BoostCampaign', $campaign->id, null, null, 'Refunded amount_mismatch boost');

        return redirect()->back()->with('success', __('messages.boost_refunded'));
    }

    /**
     * Admin domains management page.
     */
    public function domains(Request $request)
    {
        if (! auth()->user()->isAdmin()) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $query = Role::whereNotNull('custom_domain');

        if ($search = $request->input('search')) {
            $search = str_replace(['%', '_'], ['\\%', '\\_'], $search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('subdomain', 'like', "%{$search}%")
                    ->orWhere('custom_domain', 'like', "%{$search}%")
                    ->orWhere('custom_domain_host', 'like', "%{$search}%");
            });
        }

        if ($mode = $request->input('mode')) {
            $query->where('custom_domain_mode', $mode);
        }

        if ($status = $request->input('status')) {
            $query->where('custom_domain_status', $status);
        }

        $roles = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();

        // Statistics
        $totalCustomDomains = Role::whereNotNull('custom_domain')->count();
        $directCount = Role::where('custom_domain_mode', 'direct')->count();
        $activeCount = Role::where('custom_domain_mode', 'direct')->where('custom_domain_status', 'active')->count();
        $pendingCount = Role::where('custom_domain_mode', 'direct')->where('custom_domain_status', 'pending')->count();

        // Fetch live DO statuses for direct-mode domains on this page
        $doStatuses = [];
        $doService = app(DigitalOceanService::class);
        if ($doService->isConfigured()) {
            try {
                $doStatuses = $doService->getAppDomains();
            } catch (\Exception $e) {
                Log::error('Failed to fetch DO domain statuses', ['error' => $e->getMessage()]);
            }
        }

        return view('admin.domains', compact(
            'roles',
            'totalCustomDomains',
            'directCount',
            'activeCount',
            'pendingCount',
            'doStatuses',
        ));
    }

    /**
     * Re-provision a custom domain in DigitalOcean.
     */
    public function domainReprovision(Role $role)
    {
        if (! auth()->user()->isAdmin()) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        if (! $role->custom_domain_host || $role->custom_domain_mode !== 'direct') {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $doService = app(DigitalOceanService::class);
        if (! $doService->isConfigured()) {
            return redirect()->back()->with('error', 'DigitalOcean service not configured.');
        }

        try {
            $doService->removeDomain($role->custom_domain_host);
            $doService->addDomain($role->custom_domain_host);
            $role->update(['custom_domain_status' => 'pending']);
            Cache::forget("custom_domain:{$role->custom_domain_host}");
        } catch (\Exception $e) {
            Log::error('Failed to re-provision domain', [
                'role_id' => $role->id,
                'hostname' => $role->custom_domain_host,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', 'Re-provision failed. Check logs for details.');
        }

        AuditService::log(AuditService::ADMIN_UPDATE, auth()->id(), 'Role', $role->id, null, null, "Re-provisioned domain: {$role->custom_domain_host}");

        return redirect()->back()->with('success', __('messages.reprovision_success'));
    }

    /**
     * Remove a custom domain from DigitalOcean.
     */
    public function domainRemove(Role $role)
    {
        if (! auth()->user()->isAdmin()) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        if (! $role->custom_domain_host) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $hostname = $role->custom_domain_host;

        $doService = app(DigitalOceanService::class);
        if ($doService->isConfigured() && $role->custom_domain_mode === 'direct') {
            try {
                $doService->removeDomain($hostname);
            } catch (\Exception $e) {
                Log::error('Failed to remove domain from DO', [
                    'role_id' => $role->id,
                    'hostname' => $hostname,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $role->update([
            'custom_domain' => null,
            'custom_domain_mode' => null,
            'custom_domain_host' => null,
            'custom_domain_status' => null,
        ]);

        Cache::forget("custom_domain:{$hostname}");

        AuditService::log(AuditService::ADMIN_UPDATE, auth()->id(), 'Role', $role->id, null, null, "Removed domain: {$hostname}");

        return redirect()->back()->with('success', __('messages.domain_removed'));
    }
}
