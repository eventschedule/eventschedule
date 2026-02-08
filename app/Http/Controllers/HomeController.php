<?php

namespace App\Http\Controllers;

use App\Mail\SupportEmail;
use App\Models\BlogPost;
use App\Models\Event;
use App\Models\EventComment;
use App\Models\EventVideo;
use App\Models\Role;
use App\Utils\UrlUtils;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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

            $user = auth()->user();
            $user->language_code = $role->language_code;
            $user->save();

            return redirect()->route('role.follow', ['subdomain' => $subdomain]);
        }

        $events = [];
        $month = $request->month;
        $year = $request->year;
        $startOfMonth = '';
        $endOfMonth = '';

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
        $endOfMonth = $startOfMonth->copy()->endOfMonth()->endOfDay();

        // Convert to UTC for database query
        $startOfMonthUtc = $startOfMonth->copy()->setTimezone('UTC');
        $endOfMonthUtc = $endOfMonth->copy()->setTimezone('UTC');

        $roleIds = $user->roles()->pluck('roles.id');

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
            ->where(function ($query) use ($startOfMonthUtc, $endOfMonthUtc) {
                $query->whereBetween('starts_at', [$startOfMonthUtc, $endOfMonthUtc])
                    ->orWhereNotNull('days_of_week');
            })
            ->orderBy('starts_at')
            ->get();

        // Events will be loaded via Ajax in the calendar partial
        if (! request()->graphic) {
            $events = collect();
        }

        return view('home', compact(
            'events',
            'month',
            'year',
            'startOfMonth',
            'endOfMonth',
        ));
    }

    public function calendarEvents(Request $request): JsonResponse
    {
        $month = $request->month ?: now()->month;
        $year = $request->year ?: now()->year;

        $user = $request->user();
        $timezone = $user->timezone ?? 'UTC';

        $startOfMonth = Carbon::create($year, $month, 1, 0, 0, 0, $timezone)->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth()->endOfDay();

        $startOfMonthUtc = $startOfMonth->copy()->setTimezone('UTC');
        $endOfMonthUtc = $endOfMonth->copy()->setTimezone('UTC');

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
            ->where(function ($query) use ($startOfMonthUtc, $endOfMonthUtc) {
                $query->whereBetween('starts_at', [$startOfMonthUtc, $endOfMonthUtc])
                    ->orWhereNotNull('days_of_week');
            })
            ->orderBy('starts_at')
            ->get();

        return $this->buildCalendarResponse($events, collect(), false, null, null, (int) $month, (int) $year, $timezone);
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

    private function processPendingFanContent(array $pending): ?string
    {
        $eventId = UrlUtils::decodeId($pending['event_hash'] ?? '');
        if (! $eventId) {
            return null;
        }

        $event = Event::with('parts')->find($eventId);
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
                EventVideo::create([
                    'event_id' => $event->id,
                    'event_part_id' => $eventPartId ?: null,
                    'event_date' => $eventDate,
                    'user_id' => auth()->id(),
                    'youtube_url' => $youtubeUrl,
                    'is_approved' => false,
                ]);
            }

            session()->flash('message', __('messages.video_submitted'));
        } elseif ($pending['type'] === 'comment') {
            $comment = $pending['comment'] ?? '';
            if (! $comment) {
                return $returnUrl;
            }

            EventComment::create([
                'event_id' => $event->id,
                'event_part_id' => $eventPartId ?: null,
                'event_date' => $eventDate,
                'user_id' => auth()->id(),
                'comment' => $comment,
                'is_approved' => false,
            ]);

            session()->flash('message', __('messages.comment_submitted'));
        }

        return $returnUrl;
    }
}
