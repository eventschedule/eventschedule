<?php

namespace App\Http\Controllers;

use App\Mail\SupportEmail;
use App\Models\BlogPost;
use App\Models\BoostCampaign;
use App\Models\Event;
use App\Models\EventComment;
use App\Models\EventPhoto;
use App\Models\EventVideo;
use App\Models\Newsletter;
use App\Models\Role;
use App\Models\Sale;
use App\Services\AnalyticsService;
use App\Utils\UrlUtils;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class HomeController extends Controller
{
    use Traits\CalendarDataTrait;

    public function landing($slug = null)
    {
        if ($slug && $role = Role::whereSubdomain($slug)->first()) {
            return redirect()->route('role.view_guest', ['subdomain' => $role->subdomain]);
        }

        return redirect(route('home'));
    }

    public function home(Request $request)
    {
        if ($pending = session()->pull('pending_fan_content')) {
            $returnUrl = $this->processPendingFanContent($pending);
            if ($returnUrl) {
                return redirect($returnUrl);
            }
        }

        $subdomain = session('pending_follow');

        if (! $subdomain) {
            $subdomain = session('pending_request');
        }

        if ($subdomain) {
            $role = Role::whereSubdomain($subdomain)->firstOrFail();

            return redirect()->route('role.follow', ['subdomain' => $subdomain]);
        }

        $events = [];
        $month = $request->month;
        $year = $request->year;
        $startOfMonth = '';

        if (! $month) {
            $month = now()->month;
        }
        if (! $year) {
            $year = now()->year;
        }

        $user = $request->user();
        $timezone = $user->timezone ?? 'UTC';

        // Calculate month boundaries in user's timezone, then convert to UTC for database query
        $startOfMonth = Carbon::create($year, $month, 1, 0, 0, 0, $timezone)->startOfMonth();

        // Convert to UTC for database query
        $startOfMonthUtc = $startOfMonth->copy()->setTimezone('UTC');

        $roleIds = $user->roles()->pluck('roles.id');

        // Events will be loaded via Ajax in the calendar partial
        if (request()->graphic) {
            $events = Event::with('roles')
                ->where(function ($query) use ($roleIds, $user) {
                    $query->where(function ($query) use ($roleIds) {
                        $query->whereIn('id', function ($query) use ($roleIds) {
                            $query->select('event_id')
                                ->from('event_role')
                                ->whereIn('role_id', $roleIds)
                                ->where('is_accepted', true);
                        });
                    })->orWhere(function ($query) use ($user) {
                        $query->where('user_id', $user->id);
                    });
                })
                ->where(function ($query) use ($startOfMonthUtc) {
                    $query->where('starts_at', '>=', $startOfMonthUtc)
                        ->orWhereNotNull('days_of_week');
                })
                ->orderBy('starts_at')
                ->get();
        } else {
            $events = collect();
        }

        // Dashboard config
        $dashboardConfig = $this->getDashboardConfig($user);
        $visiblePanels = collect($dashboardConfig['panels'])->where('visible', true)->pluck('id')->toArray();
        $panelSettings = collect($dashboardConfig['panels'])->keyBy('id')->toArray();

        // Dashboard data - skip queries for hidden panels
        $upcomingCount = 0;
        $views30d = 0;
        $viewsChange = 0;
        $sparklineData = [];
        $followersCount = 0;
        $totalEventsCount = 0;
        $upcomingEvents = collect();
        $recentActivity = collect();
        $revenueStats = null;
        $topEvents = collect();
        $latestNewsletters = collect();
        $boostCampaigns = collect();
        $trafficSources = collect();

        $analyticsService = app(AnalyticsService::class);
        $now = now()->endOfDay();

        if (in_array('upcoming_count', $visiblePanels)) {
            $upcomingCount = Event::whereIn('id', function ($query) use ($roleIds) {
                $query->select('event_id')
                    ->from('event_role')
                    ->whereIn('role_id', $roleIds)
                    ->where('is_accepted', true);
            })->where('starts_at', '>', now())->count();
        }
        if (in_array('views', $visiblePanels)) {
            $viewsPeriod = $panelSettings['views']['period'] ?? 30;
            $viewsStart = now()->subDays($viewsPeriod)->startOfDay();
            $periodStats = $analyticsService->getStatsForUser($user, $viewsStart, $now);
            $momComparison = $analyticsService->getMonthOverMonthComparison($user);
            $views30d = $periodStats['period_views'] ?? 0;
            $viewsChange = $momComparison['percentage_change'] ?? 0;
            $sparklineData = $this->getSparklineData($user, $viewsPeriod);
        }
        if (in_array('followers', $visiblePanels)) {
            $followersCount = DB::table('role_user')
                ->whereIn('role_id', $roleIds)
                ->where('level', 'follower')
                ->count();
            $totalEventsCount = Event::whereIn('id', function ($query) use ($roleIds) {
                $query->select('event_id')
                    ->from('event_role')
                    ->whereIn('role_id', $roleIds)
                    ->where('is_accepted', true);
            })->count();
        }
        if (in_array('upcoming_events', $visiblePanels)) {
            $upcomingEventsCount = $panelSettings['upcoming_events']['count'] ?? 3;
            $upcomingEvents = $this->getUpcomingEvents($roleIds, $upcomingEventsCount);
        }
        if (in_array('recent_activity', $visiblePanels)) {
            $recentActivityCount = $panelSettings['recent_activity']['count'] ?? 5;
            $recentActivity = $this->getRecentActivity($roleIds, $recentActivityCount);
        }
        if (in_array('revenue', $visiblePanels)) {
            $revenuePeriod = $panelSettings['revenue']['period'] ?? 30;
            $revenueStart = now()->subDays($revenuePeriod)->startOfDay();
            $revenueStats = $analyticsService->getConversionStats($user, $revenueStart, $now);
        }
        if (in_array('top_events', $visiblePanels)) {
            $topEventsCount = $panelSettings['top_events']['count'] ?? 3;
            $topEventsPeriod = $panelSettings['top_events']['period'] ?? 30;
            $topEventsStart = now()->subDays($topEventsPeriod)->startOfDay();
            $topEvents = $analyticsService->getTopEvents($user, $topEventsCount, $topEventsStart, $now);
        }
        if (in_array('newsletters', $visiblePanels)) {
            $newslettersCount = $panelSettings['newsletters']['count'] ?? 3;
            $latestNewsletters = Newsletter::whereIn('role_id', $roleIds)
                ->where('status', 'sent')
                ->orderByDesc('sent_at')
                ->limit($newslettersCount)
                ->get();
        }
        if (in_array('boosts', $visiblePanels)) {
            $boostsCount = $panelSettings['boosts']['count'] ?? 3;
            $boostCampaigns = BoostCampaign::whereIn('role_id', $roleIds)
                ->whereIn('status', ['active', 'paused'])
                ->latest()
                ->limit($boostsCount)
                ->get();
        }
        if (in_array('traffic_sources', $visiblePanels)) {
            $trafficCount = $panelSettings['traffic_sources']['count'] ?? 5;
            $trafficPeriod = $panelSettings['traffic_sources']['period'] ?? 30;
            $trafficStart = now()->subDays($trafficPeriod)->startOfDay();
            $trafficSources = $analyticsService->getTopReferrerDomains($user, $trafficCount, $trafficStart, $now);
        }

        return view('home', compact(
            'events',
            'month',
            'year',
            'startOfMonth',
            'roleIds',
            'upcomingCount',
            'views30d',
            'viewsChange',
            'sparklineData',
            'followersCount',
            'totalEventsCount',
            'upcomingEvents',
            'recentActivity',
            'dashboardConfig',
            'panelSettings',
            'revenueStats',
            'topEvents',
            'latestNewsletters',
            'boostCampaigns',
            'trafficSources',
        ));
    }

    public function calendarEvents(Request $request): JsonResponse
    {
        $month = $request->month ?: now()->month;
        $year = $request->year ?: now()->year;

        $user = $request->user();
        $timezone = $user->timezone ?? 'UTC';

        $startOfMonth = Carbon::create($year, $month, 1, 0, 0, 0, $timezone)->startOfMonth();

        $startOfMonthUtc = $startOfMonth->copy()->setTimezone('UTC');

        $roleIds = $user->roles()->pluck('roles.id');

        $events = Event::with('roles', 'parts', 'tickets')
            ->where(function ($query) use ($roleIds, $user) {
                $query->where(function ($query) use ($roleIds) {
                    $query->whereIn('id', function ($query) use ($roleIds) {
                        $query->select('event_id')
                            ->from('event_role')
                            ->whereIn('role_id', $roleIds)
                            ->where('is_accepted', true);
                    });
                })->orWhere(function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                });
            })
            ->where(function ($query) use ($startOfMonthUtc) {
                $query->where('starts_at', '>=', $startOfMonthUtc)
                    ->orWhereNotNull('days_of_week');
            })
            ->orderBy('starts_at')
            ->get();

        return $this->buildCalendarResponse($events, collect(), false, null, null, (int) $month, (int) $year, $timezone, 0);
    }

    public function sitemap()
    {
        $roles = Role::with('groups')
            ->where(function ($query) {
                $query->where(function ($q) {
                    $q->whereNotNull('email')
                        ->whereNotNull('email_verified_at');
                })->orWhere(function ($q) {
                    $q->whereNotNull('phone')
                        ->whereNotNull('phone_verified_at');
                });
            })
            ->where('is_deleted', false)
            ->orderBy(request()->has('roles') ? 'id' : 'subdomain', request()->has('roles') ? 'desc' : 'asc')
            ->get();

        $events = Event::with(['roles'])
            ->whereNotNull('starts_at')
            ->where('is_private', false)
            ->whereNull('event_password')
            ->whereHas('roles', fn ($q) => $q->where('is_accepted', true))
            ->orderBy(request()->has('events') ? 'id' : 'starts_at', 'desc')
            ->get();

        $blogPosts = BlogPost::published()
            ->orderBy('published_at', 'desc')
            ->get();

        $hasQueryFilter = request()->has('events') || request()->has('roles');

        $sitemapView = view('sitemap', [
            'roles' => ! request()->has('events') ? $roles : [],
            'events' => ! request()->has('roles') ? $events : [],
            'blogPosts' => $hasQueryFilter ? [] : $blogPosts,
            'showMarketingLinks' => ! $hasQueryFilter,
            'lastmod' => now()->toIso8601String(),
        ]);

        // Check if the request is for the gzipped version
        $isGzipped = str_ends_with(request()->path(), '.gz');

        if ($isGzipped) {
            $content = $sitemapView->render();
            $gzipped = gzencode($content, 9);

            return response($gzipped)
                ->header('Content-Type', 'application/xml')
                ->header('Content-Encoding', 'gzip');
        }

        return response($sitemapView)
            ->header('Content-Type', 'application/xml');
    }

    public function submitFeedback(Request $request)
    {
        $request->validate([
            'feedback' => 'required|string|max:5000',
        ]);

        // Block feedback submission in demo mode
        if (is_demo_mode()) {
            return response()->json([
                'success' => false,
                'message' => __('messages.demo_mode_restriction'),
            ], 403);
        }

        $user = $request->user();
        $feedback = $request->input('feedback');

        try {
            Mail::to('contact@eventschedule.com')->send(new SupportEmail(
                $user->name ?? $user->email,
                $user->email,
                $feedback
            ));

            return response()->json([
                'success' => true,
                'message' => __('messages.feedback_submitted'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.feedback_failed'),
            ], 500);
        }
    }

    public function saveDashboardConfig(Request $request): JsonResponse
    {
        $request->validate([
            'panels' => 'required|array|max:10',
            'panels.*.id' => 'required|string|in:upcoming_count,views,followers,upcoming_events,recent_activity,revenue,top_events,newsletters,boosts,traffic_sources',
            'panels.*.visible' => 'required|boolean',
            'panels.*.size' => 'sometimes|integer|in:1,2',
            'panels.*.period' => 'sometimes|integer|in:7,14,30',
            'panels.*.count' => 'sometimes|integer|in:3,5,10',
        ]);

        $panels = collect($request->input('panels'))->map(function ($panel) {
            $item = [
                'id' => $panel['id'],
                'visible' => (bool) $panel['visible'],
            ];
            if (isset($panel['size'])) {
                $item['size'] = (int) $panel['size'];
            }
            if (isset($panel['period'])) {
                $item['period'] = (int) $panel['period'];
            }
            if (isset($panel['count'])) {
                $item['count'] = (int) $panel['count'];
            }

            return $item;
        })->values()->toArray();

        $user = $request->user();
        $user->dashboard_config = ['panels' => $panels];
        $user->save();

        return response()->json([
            'success' => true,
            'message' => __('messages.dashboard_config_saved'),
        ]);
    }

    private function getDashboardConfig($user): array
    {
        $defaults = [
            ['id' => 'upcoming_count', 'visible' => true, 'size' => 1],
            ['id' => 'views', 'visible' => true, 'size' => 1, 'period' => 30],
            ['id' => 'followers', 'visible' => true, 'size' => 1],
            ['id' => 'revenue', 'visible' => true, 'size' => 1, 'period' => 30],
            ['id' => 'upcoming_events', 'visible' => true, 'size' => 2, 'count' => 3],
            ['id' => 'recent_activity', 'visible' => true, 'size' => 2, 'count' => 5],
            ['id' => 'top_events', 'visible' => false, 'size' => 2, 'count' => 3, 'period' => 30],
            ['id' => 'newsletters', 'visible' => false, 'size' => 2, 'count' => 3],
            ['id' => 'boosts', 'visible' => false, 'size' => 2, 'count' => 3],
            ['id' => 'traffic_sources', 'visible' => false, 'size' => 2, 'count' => 5, 'period' => 30],
        ];

        $defaultsMap = collect($defaults)->keyBy('id')->toArray();

        $config = $user->dashboard_config;

        if (! $config || ! isset($config['panels'])) {
            return ['panels' => $defaults, 'defaultPanels' => $defaults];
        }

        $validIds = array_keys($defaultsMap);
        $configuredIds = [];

        // Keep only valid panels from config, merging missing keys from defaults
        $panels = [];
        foreach ($config['panels'] as $panel) {
            if (! isset($panel['id']) || ! in_array($panel['id'], $validIds)) {
                continue;
            }
            if (in_array($panel['id'], $configuredIds)) {
                continue;
            }
            $configuredIds[] = $panel['id'];
            $merged = array_merge($defaultsMap[$panel['id']], [
                'id' => $panel['id'],
                'visible' => (bool) ($panel['visible'] ?? true),
            ]);
            if (isset($panel['size'])) {
                $merged['size'] = (int) $panel['size'];
            }
            if (isset($panel['period']) && isset($defaultsMap[$panel['id']]['period'])) {
                $merged['period'] = (int) $panel['period'];
            }
            if (isset($panel['count']) && isset($defaultsMap[$panel['id']]['count'])) {
                $merged['count'] = (int) $panel['count'];
            }
            $panels[] = $merged;
        }

        // Add any missing panels at the end (future-proofing)
        foreach ($defaults as $default) {
            if (! in_array($default['id'], $configuredIds)) {
                $panels[] = $default;
            }
        }

        return ['panels' => $panels, 'defaultPanels' => $defaults];
    }

    private function getUpcomingEvents($roleIds, int $count = 3)
    {
        return Event::whereIn('id', function ($query) use ($roleIds) {
            $query->select('event_id')
                ->from('event_role')
                ->whereIn('role_id', $roleIds)
                ->where('is_accepted', true);
        })
            ->where('starts_at', '>', now())
            ->orderBy('starts_at')
            ->limit($count)
            ->with(['roles', 'tickets'])
            ->get();
    }

    private function getRecentActivity($roleIds, int $count = 5)
    {
        $eventIds = DB::table('event_role')
            ->whereIn('role_id', $roleIds)
            ->where('is_accepted', true)
            ->pluck('event_id')
            ->unique();

        $activities = collect();

        // Recent sales
        if ($eventIds->isNotEmpty()) {
            $sales = Sale::whereIn('event_id', $eventIds)
                ->where('status', 'paid')
                ->latest()
                ->limit(10)
                ->with('event')
                ->get()
                ->map(function ($sale) {
                    return [
                        'type' => 'sale',
                        'description' => $sale->event ? $sale->event->name : '',
                        'date' => $sale->created_at,
                        'amount' => $sale->payment_amount,
                    ];
                });
            $activities = $activities->merge($sales);
        }

        // Recent followers
        $followers = DB::table('role_user')
            ->whereIn('role_id', $roleIds)
            ->where('level', 'follower')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $followerUserIds = $followers->pluck('user_id')->unique();
        $followerUsers = DB::table('users')->whereIn('id', $followerUserIds)->get()->keyBy('id');

        $followers = $followers->map(function ($follow) use ($followerUsers) {
                $user = $followerUsers[$follow->user_id] ?? null;

                return [
                    'type' => 'follower',
                    'description' => $user ? trim(($user->first_name ?? '').' '.($user->last_name ?? '')) : '',
                    'date' => Carbon::parse($follow->created_at),
                ];
            });
        $activities = $activities->merge($followers);

        // Recent newsletters sent
        $newsletters = Newsletter::whereIn('role_id', $roleIds)
            ->where('status', 'sent')
            ->whereNotNull('sent_at')
            ->orderByDesc('sent_at')
            ->limit(5)
            ->get()
            ->map(function ($newsletter) {
                return [
                    'type' => 'newsletter',
                    'description' => $newsletter->subject,
                    'date' => $newsletter->sent_at,
                    'sent_count' => $newsletter->sent_count,
                ];
            });
        $activities = $activities->merge($newsletters);

        // Sort by date descending
        return $activities->filter(fn($a) => $a['date'] !== null)->sortByDesc('date')->take($count)->values();
    }

    private function getSparklineData($user, int $days = 30): array
    {
        $analyticsService = app(AnalyticsService::class);
        $start = now()->subDays($days)->startOfDay();
        $now = now()->endOfDay();

        $viewsByPeriod = $analyticsService->getViewsByPeriod($user, 'daily', $start, $now);

        // Fill in missing days with 0
        $data = [];
        $current = $start->copy();
        $viewsMap = $viewsByPeriod->pluck('view_count', 'period')->toArray();

        while ($current->lte($now)) {
            $key = $current->format('Y-m-d');
            $data[] = (int) ($viewsMap[$key] ?? 0);
            $current->addDay();
        }

        return $data;
    }

    private function processPendingFanContent(array $pending): ?string
    {
        $eventId = UrlUtils::decodeId($pending['event_hash'] ?? '');
        if (! $eventId) {
            return null;
        }

        $event = Event::with(['parts', 'roles'])->find($eventId);
        if (! $event) {
            return null;
        }

        $eventPartId = $pending['event_part_id'] ?? null;
        if ($eventPartId) {
            $eventPartId = UrlUtils::decodeId($eventPartId);
            $part = $event->parts->firstWhere('id', $eventPartId);
            if (! $part) {
                $eventPartId = null;
            }
        }

        $eventDate = $event->days_of_week ? ($pending['event_date'] ?? null) : null;
        $returnUrl = $pending['return_url'] ?? null;
        if ($returnUrl) {
            $parsedUrl = parse_url($returnUrl);
            $appHost = parse_url(config('app.url'), PHP_URL_HOST);
            if (isset($parsedUrl['host']) && $parsedUrl['host'] !== $appHost && ! str_ends_with($parsedUrl['host'], '.'.$appHost)) {
                $returnUrl = null;
            }
            $lowerUrl = strtolower(trim($returnUrl ?? ''));
            if (str_starts_with($lowerUrl, 'javascript:') || str_starts_with($lowerUrl, 'data:')) {
                $returnUrl = null;
            }
        }

        if ($pending['type'] === 'video') {
            $youtubeUrl = $pending['youtube_url'] ?? '';
            $embedUrl = UrlUtils::getYouTubeEmbed($youtubeUrl);
            if (! $embedUrl) {
                return $returnUrl;
            }

            // Check for duplicate
            $submittedVideoId = basename(parse_url($embedUrl, PHP_URL_PATH));
            $query = EventVideo::where('event_id', $event->id);
            if ($eventPartId) {
                $query->where('event_part_id', $eventPartId);
            } else {
                $query->whereNull('event_part_id');
            }
            if ($eventDate) {
                $query->where('event_date', $eventDate);
            }
            $exists = $query->get()->contains(function ($video) use ($submittedVideoId) {
                $existingEmbed = UrlUtils::getYouTubeEmbed($video->youtube_url);

                return $existingEmbed && basename(parse_url($existingEmbed, PHP_URL_PATH)) === $submittedVideoId;
            });

            if (! $exists) {
                $video = EventVideo::create([
                    'event_id' => $event->id,
                    'event_part_id' => $eventPartId ?: null,
                    'event_date' => $eventDate,
                    'user_id' => auth()->id(),
                    'youtube_url' => $youtubeUrl,
                    'is_approved' => false,
                ]);
                $returnUrl = $event->getGuestUrl($pending['subdomain']);
                session()->flash('scroll_to', 'pending-video-'.$video->id);
            }

            session()->flash('message', __('messages.video_submitted'));
        } elseif ($pending['type'] === 'comment') {
            $commentText = $pending['comment'] ?? '';
            if (! $commentText) {
                return $returnUrl;
            }

            $comment = EventComment::create([
                'event_id' => $event->id,
                'event_part_id' => $eventPartId ?: null,
                'event_date' => $eventDate,
                'user_id' => auth()->id(),
                'comment' => $commentText,
                'is_approved' => false,
            ]);
            $returnUrl = $event->getGuestUrl($pending['subdomain']);
            session()->flash('scroll_to', 'pending-comment-'.$comment->id);

            session()->flash('message', __('messages.comment_submitted'));
        } elseif ($pending['type'] === 'photo') {
            $role = Role::where('subdomain', $pending['subdomain'] ?? '')->first();
            if ($role && ! $role->canUploadPhoto()) {
                $tempFilename = $pending['temp_filename'] ?? '';
                if ($tempFilename) {
                    \Illuminate\Support\Facades\Storage::delete('temp/'.$tempFilename);
                }
                session()->flash('error', __('messages.photo_limit_reached'));

                return $returnUrl;
            }

            $tempFilename = $pending['temp_filename'] ?? '';
            $extension = $pending['extension'] ?? '';
            if (! $tempFilename || ! $extension) {
                if ($tempFilename) {
                    \Illuminate\Support\Facades\Storage::delete('temp/'.$tempFilename);
                }

                return $returnUrl;
            }

            if (! \Illuminate\Support\Facades\Storage::exists('temp/'.$tempFilename)) {
                return $returnUrl;
            }

            $filename = 'photo_'.\Illuminate\Support\Str::random(32).'.'.$extension;
            if (config('filesystems.default') == 'local') {
                \Illuminate\Support\Facades\Storage::move('temp/'.$tempFilename, 'public/'.$filename);
            } else {
                \Illuminate\Support\Facades\Storage::move('temp/'.$tempFilename, $filename);
            }

            $photo = EventPhoto::create([
                'event_id' => $event->id,
                'event_part_id' => $eventPartId ?: null,
                'event_date' => $eventDate,
                'user_id' => auth()->id(),
                'photo_url' => $filename,
                'is_approved' => false,
            ]);
            if (($pending['return_to'] ?? null) === 'gallery') {
                $returnUrl = $event->getPhotoGalleryUrl($pending['subdomain']);
            } else {
                $returnUrl = $event->getGuestUrl($pending['subdomain']);
                session()->flash('scroll_to', 'pending-photo-'.$photo->id);
            }

            session()->flash('message', __('messages.photo_submitted'));
        }

        return $returnUrl;
    }
}
