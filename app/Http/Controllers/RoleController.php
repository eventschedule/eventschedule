<?php

namespace App\Http\Controllers;

use App\Http\Requests\MemberAddRequest;
use App\Http\Requests\RoleCreateRequest;
use App\Http\Requests\RoleEmailVerificationRequest;
use App\Http\Requests\RoleUpdateRequest;
use App\Models\AnalyticsAppearancesDaily;
use App\Models\AnalyticsDaily;
use App\Models\AnalyticsReferrersDaily;
use App\Models\Event;
use App\Models\Role;
use App\Models\RoleUser;
use App\Models\User;
use App\Notifications\AddedMemberNotification;
use App\Notifications\DeletedRoleNotification;
use App\Repos\EventRepo;
use App\Services\AnalyticsService;
use App\Services\EmailService;
use App\Utils\ColorUtils;
use App\Utils\GeminiUtils;
use App\Utils\UrlUtils;
use Carbon\Carbon;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RoleController extends Controller
{
    protected $eventRepo;

    public function __construct(EventRepo $eventRepo)
    {
        $this->eventRepo = $eventRepo;
    }

    public function deleteImage(Request $request, $subdomain)
    {
        $role = Role::subdomain($subdomain)->firstOrFail();

        if (auth()->user()->id != $role->user_id) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        if ($request->image_type == 'profile') {
            if ($role->profile_image_url) {
                $path = $role->getAttributes()['profile_image_url'];
                if (config('filesystems.default') == 'local') {
                    $path = 'public/'.$path;
                }
                Storage::delete($path);

                $role->profile_image_url = null;
                $role->save();
            }
        } elseif ($request->image_type == 'background') {
            if ($role->background_image_url) {
                $path = $role->getAttributes()['background_image_url'];
                if (config('filesystems.default') == 'local') {
                    $path = 'public/'.$path;
                }
                Storage::delete($path);

                $role->background_image_url = null;
                $role->save();
            }
        } elseif ($request->image_type == 'header') {
            if ($role->header_image_url) {
                $path = $role->getAttributes()['header_image_url'];
                if (config('filesystems.default') == 'local') {
                    $path = 'public/'.$path;
                }
                Storage::delete($path);

                $role->header_image_url = null;
                $role->save();
            }
        }

        return redirect(route('role.edit', ['subdomain' => $subdomain]))
            ->with('message', __('messages.deleted_image'));
    }

    public function delete(Request $request, $subdomain)
    {
        $user = $request->user();
        $role = Role::subdomain($subdomain)->firstOrFail();
        $type = $role->type;

        if ($user->id != $role->user_id) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        if ($role->profile_image_url) {
            $path = $role->getAttributes()['profile_image_url'];
            if (config('filesystems.default') == 'local') {
                $path = 'public/'.$path;
            }
            Storage::delete($path);
        }

        if ($role->header_image_url) {
            $path = $role->getAttributes()['header_image_url'];
            if (config('filesystems.default') == 'local') {
                $path = 'public/'.$path;
            }
            Storage::delete($path);
        }

        if ($role->background_image_url) {
            $path = $role->getAttributes()['background_image_url'];
            if (config('filesystems.default') == 'local') {
                $path = 'public/'.$path;
            }
            Storage::delete($path);
        }

        $emails = $role->members()->pluck('email');

        // Clean up Google Calendar webhook before deleting role
        if ($role->google_webhook_id && $role->google_webhook_resource_id) {
            try {
                $user = $role->users()->first();
                if ($user && $user->google_token) {
                    $googleCalendarService = app(\App\Services\GoogleCalendarService::class);

                    // Ensure user has valid token before deleting webhook
                    if ($googleCalendarService->ensureValidToken($user)) {
                        $googleCalendarService->deleteWebhook($role->google_webhook_id, $role->google_webhook_resource_id);
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Failed to clean up webhook during role deletion', [
                    'role_id' => $role->id,
                    'webhook_id' => $role->google_webhook_id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Prevent orphaned events
        if ($role->isTalent()) {
            $events = $role->events()->get();
            foreach ($events as $event) {
                if ($event->members()->count() == 1) {
                    $event->delete();
                }
            }
        }

        // Delete analytics data
        AnalyticsDaily::where('role_id', $role->id)->delete();
        AnalyticsReferrersDaily::where('role_id', $role->id)->delete();
        AnalyticsAppearancesDaily::where('role_id', $role->id)->delete();

        $role->delete();

        Notification::route('mail', $emails)->notify(new DeletedRoleNotification($role, $user));

        return redirect(route('home'))
            ->with('message', __('messages.deleted_schedule'));
    }

    public function follow(Request $request, $subdomain)
    {
        $role = Role::subdomain($subdomain)->firstOrFail();

        $mainDomain = config('app.url');

        if (! auth()->user()) {
            session(['pending_follow' => $subdomain]);
            $lang = session()->has('translate') ? 'en' : $role->language_code;

            return redirect($mainDomain.route('sign_up', ['lang' => $lang], false));
        }

        $user = $request->user();

        if (! $user->isConnected($role->subdomain)) {
            $user->roles()->attach($role->id, ['level' => 'follower', 'created_at' => now()]);
        }

        session()->forget('pending_follow');

        if ($subdomain = session('pending_request')) {
            if ($user->talents()->count() == 0) {
                $redirectUrl = $mainDomain.route('new', ['type' => 'talent'], false);

                return redirect($redirectUrl);
            }

            $role = $user->talents()->first();
            $redirectUrl = $mainDomain.route('event.create', ['subdomain' => $role->subdomain], false);

            return redirect($redirectUrl);

        } else {
            $redirectUrl = $mainDomain.route('following', [], false);

            return redirect($redirectUrl)
                ->with('message', str_replace(':name', $role->name, __('messages.followed_role')));
        }
    }

    public function unfollow(Request $request, $subdomain)
    {
        $role = Role::subdomain($subdomain)->firstOrFail();
        $user = $request->user();

        if (! $role->email) {
            $role->is_deleted = true;
            $role->save();
        }

        if ($user->isConnected($role->subdomain)) {
            $user->roles()->detach($role->id);
        }

        return redirect(route('following'))
            ->with('message', str_replace(':name', $role->name, __('messages.unfollowed_role')));
    }

    public function bulkUnfollow(Request $request)
    {
        $subdomains = json_decode($request->input('subdomains', '[]'), true);
        $user = $request->user();
        $count = 0;

        foreach ($subdomains as $subdomain) {
            $role = Role::subdomain($subdomain)->first();
            if ($role) {
                if (! $role->email) {
                    $role->is_deleted = true;
                    $role->save();
                }
                if ($user->isConnected($role->subdomain)) {
                    $user->roles()->detach($role->id);
                }
                $count++;
            }
        }

        return redirect(route('following'))
            ->with('message', str_replace(':count', $count, __('messages.unfollowed_roles_count')));
    }

    public function viewGuest(Request $request, $subdomain, $slug = '')
    {
        if (config('app.hosted') && env('APP_REDIRECT_SUBDOMAIN') == $subdomain) {
            return redirect(env('APP_REDIRECT_URL'), 302);
        }

        $translation = null;
        $user = auth()->user();
        $curatorRoles = $user ? $user->curators() : collect();

        $role = Role::subdomain($subdomain)->first();

        if (! $role || ! $role->isClaimed()) {
            return redirect(config('app.url'));
        }

        if ($request->lang) {
            // Validate the language code before setting it
            if (is_valid_language_code($request->lang)) {
                app()->setLocale($request->lang);

                if ($request->lang == 'en') {
                    session()->put('translate', true);
                } else {
                    session()->forget('translate');

                    return redirect(request()->url());
                }
            } else {
                // If invalid language code, redirect to the same URL without the lang parameter
                return redirect(request()->url());
            }
        } elseif (session()->has('translate')) {
            app()->setLocale('en');
        } else {
            // Validate the language code from database before setting it
            if (is_valid_language_code($role->language_code)) {
                app()->setLocale($role->language_code);
            }
        }

        $otherRole = null;
        $event = null;
        $selectedGroup = null;
        $date = $request->date ? date('Y-m-d', strtotime($request->date)) : null;

        if ($date && $date != '1970-01-01') {
            $dateObj = Carbon::parse($date);
            $month = $dateObj->month;
            $year = $dateObj->year;
        } else {
            $month = is_numeric($request->month) ? (int) $request->month : now()->month;
            $year = is_numeric($request->year) ? (int) $request->year : now()->year;
        }

        if ($slug) {
            // Check if slug is a group slug first
            if ($role->groups) {
                $group = $role->groups->where('slug', $slug)->first();
                if ($group) {
                    $selectedGroup = $group;
                    $slug = ''; // Clear slug since it's a group, not an event
                } else {
                    // Try to find event by slug
                    $event = $this->eventRepo->getEvent($subdomain, $slug, $date);
                }
            } else {
                // Try to find event by slug
                $event = $this->eventRepo->getEvent($subdomain, $slug, $date);
            }

            if ($event) {
                // Handle direct registration redirect when URL has trailing slash
                if ($request->attributes->get('has_trailing_slash')) {
                    app(AnalyticsService::class)->recordView($role, $event, $request);

                    return redirect($event->registration_url ?: $event->getGuestUrl($subdomain));
                }

                if (! $date && $event->days_of_week) {
                    $nextDate = now();
                    $daysOfWeek = str_split($event->days_of_week);
                    while (true) {
                        if ($daysOfWeek[$nextDate->dayOfWeek] == '1' && $nextDate >= now()->format('Y-m-d')) {
                            break;
                        }
                        $nextDate->addDay();
                    }
                    $date = $nextDate->format('Y-m-d');
                }
            } elseif (! $selectedGroup) {
                return redirect($role->getGuestUrl());
            }
        }

        // Also check for schedule parameter in query string
        if (! $selectedGroup && $request->has('schedule')) {
            $scheduleSlug = $request->input('schedule');
            if ($role->groups) {
                $group = $role->groups->where('slug', $scheduleSlug)->first();
                if ($group) {
                    $selectedGroup = $group;
                }
            }
        }

        if ($event) {
            if ($event->venue) {
                if ($event->venue->subdomain == $subdomain) {
                    if ($event->roles->count() > 0) {
                        $otherRole = $event->roles[0];
                    } else {
                        $otherRole = $role;
                    }
                } else {
                    $otherRole = $event->venue;
                }
            } else {
                $otherRole = $role;
            }

            if ($event->starts_at && ! $date) {
                $startAtDate = Carbon::createFromFormat('Y-m-d H:i:s', $event->starts_at);
                $month = $startAtDate->month;
                $year = $startAtDate->year;
            }
        }

        // Get translation data for curator events
        if ($event && $role->isCurator()) {
            $eventRole = $event->roles->where('id', $role->id)->first();
            if ($eventRole && $eventRole->pivot && $eventRole->pivot->name_translated) {
                $translation = $eventRole->pivot;
            }
        }

        if (! $month) {
            $month = now()->month;
        }

        if (! $year) {
            $year = now()->year;
        }

        // Get timezone from user or role
        $timezone = $user->timezone ?? $role->timezone ?? 'UTC';

        // Calculate month boundaries in user's/role's timezone, then convert to UTC for database query
        $startOfMonth = Carbon::create($year, $month, 1, 0, 0, 0, $timezone)->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->addMonths(3)->endOfMonth()->endOfDay();

        // Convert to UTC for database query
        $startOfMonthUtc = $startOfMonth->copy()->setTimezone('UTC');
        $endOfMonthUtc = $endOfMonth->copy()->setTimezone('UTC');

        if ($role->isCurator()) {
            $events = Event::with('roles')
                ->where(function ($query) use ($startOfMonthUtc, $endOfMonthUtc) {
                    $query->whereBetween('starts_at', [$startOfMonthUtc, $endOfMonthUtc])
                        ->orWhereNotNull('days_of_week');
                })
                ->whereIn('id', function ($query) use ($role) {
                    $query->select('event_id')
                        ->from('event_role')
                        ->where('role_id', $role->id)
                        ->where('is_accepted', true);
                })
                ->orderBy('starts_at')
                ->get();
        } else {
            $events = Event::with('roles')
                ->where(function ($query) use ($startOfMonthUtc, $endOfMonthUtc) {
                    $query->whereBetween('starts_at', [$startOfMonthUtc, $endOfMonthUtc])
                        ->orWhereNotNull('days_of_week');
                })
                ->where(function ($query) use ($role) {
                    $query->whereHas('roles', function ($query) use ($role) {
                        $query->where('role_id', $role->id)
                            ->where('is_accepted', true);
                    });
                });

            $events = $events->orderBy('starts_at')->get();
        }

        // Track view for analytics (non-member visits only, skip embeds)
        if (! request()->embed && (! $user || ! $user->isMember($subdomain))) {
            app(AnalyticsService::class)->recordView($role, $event, $request);
        }

        $embed = request()->embed;
        $view = 'role/show-guest';

        if ($embed) {
            $view = 'role/show-guest-embed';
        } elseif ($event) {
            $view = 'event/show-guest';
        }

        $fonts = [];
        /*
        if ($event) {
            $fonts[] = $event->venue->font_family;
            foreach ($event->roles as $each) {
                if ($each->isClaimed() && $each->isTalent()) {
                    $fonts[] = $each->font_family;
                }
            }
        } else {
            $fonts[] = $role->font_family;
        }

        $fonts = array_unique($fonts);
        */

        $response = response()
            ->view($view, compact(
                'subdomain',
                'events',
                'role',
                'otherRole',
                'month',
                'year',
                'startOfMonth',
                'endOfMonth',
                'user',
                'event',
                'embed',
                'date',
                'curatorRoles',
                'fonts',
                'translation',
                'selectedGroup',
            ));

        // Allow embedding when embed parameter is present
        if ($embed && $role->isPro()) {
            $response->header('X-Frame-Options', 'ALLOW-FROM *');
        }

        return $response;
    }

    public function viewAdmin(Request $request, $subdomain, $tab = 'schedule')
    {
        if (! auth()->user()->isMember($subdomain)) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $role = Role::subdomain($subdomain)->firstOrFail();
        $members = $role->members()->get();
        $followers = $role->followers()->get();
        $followersWithRoles = [];

        $events = [];
        $unscheduled = [];
        $requests = [];
        $month = $request->month;
        $year = $request->year;
        $startOfMonth = '';
        $endOfMonth = '';
        $datesUnavailable = [];

        $requests = Event::with('roles')
            ->where(function ($query) use ($role) {
                $query->whereHas('roles', function ($query) use ($role) {
                    $query->where('role_id', $role->id)
                        ->whereNull('is_accepted');
                });
            })
            ->orderBy('created_at', 'desc')
            ->get();

        if ($tab == 'requests' && ! count($requests)) {
            return redirect(route('role.view_admin', ['subdomain' => $subdomain, 'tab' => 'schedule']));
        } elseif ($tab == 'availability' && $role->isCurator()) {
            return redirect(route('role.view_admin', ['subdomain' => $subdomain, 'tab' => 'schedule']));
        } elseif ($tab == 'videos' && ! $role->isCurator()) {
            return redirect(route('role.view_admin', ['subdomain' => $subdomain, 'tab' => 'schedule']));
        }

        if ($tab == 'schedule' || $tab == 'availability') {
            if (! $month) {
                $month = now()->month;
            }
            if (! $year) {
                $year = now()->year;
            }

            // Get timezone from user or role
            $user = $request->user();
            $timezone = $user->timezone ?? $role->timezone ?? 'UTC';

            // Calculate month boundaries in user's/role's timezone, then convert to UTC for database query
            $startOfMonth = Carbon::create($year, $month, 1, 0, 0, 0, $timezone)->startOfMonth();
            $endOfMonth = $startOfMonth->copy()->endOfMonth()->endOfDay();

            // Convert to UTC for database query
            $startOfMonthUtc = $startOfMonth->copy()->setTimezone('UTC');
            $endOfMonthUtc = $endOfMonth->copy()->setTimezone('UTC');

            if ($tab == 'schedule') {
                if ($role->isCurator()) {
                    $events = Event::with('roles')
                        ->where(function ($query) use ($startOfMonthUtc, $endOfMonthUtc) {
                            $query->whereBetween('starts_at', [$startOfMonthUtc, $endOfMonthUtc])
                                ->orWhereNotNull('days_of_week');
                        })
                        ->whereIn('id', function ($query) use ($role) {
                            $query->select('event_id')
                                ->from('event_role')
                                ->where('role_id', $role->id)
                                ->where('is_accepted', true);
                        })
                        ->orderBy('starts_at')
                        ->get();
                } else {
                    $events = Event::with('roles')
                        ->where(function ($query) use ($role) {
                            $query->whereHas('roles', function ($query) use ($role) {
                                $query->where('role_id', $role->id)
                                    ->where('is_accepted', true);
                            });
                        })
                        ->where(function ($query) use ($startOfMonthUtc, $endOfMonthUtc) {
                            $query->whereBetween('starts_at', [$startOfMonthUtc, $endOfMonthUtc])
                                ->orWhereNotNull('days_of_week');
                        })
                        ->orderBy('starts_at')
                        ->get();

                    $unscheduled = Event::with('roles')
                        ->where(function ($query) use ($role) {
                            $query->whereHas('roles', function ($query) use ($role) {
                                $query->where('role_id', $role->id)
                                    ->where('is_accepted', true);
                            });
                        })
                        ->whereNull('starts_at')
                        ->orderBy('created_at', 'desc')
                        ->get();

                    foreach ($members as $member) {
                        if ($member->pivot->dates_unavailable) {
                            $datesUnavailable[e($member->name)] = json_decode($member->pivot->dates_unavailable);
                        }
                    }
                }
            } elseif ($tab == 'availability') {
                $user = $request->user();
                $roleUser = RoleUser::where('user_id', $user->id)
                    ->where('role_id', $role->id)
                    ->first();

                if ($roleUser && $roleUser->dates_unavailable) {
                    $datesUnavailable = json_decode($roleUser->dates_unavailable);
                }
            }
        } elseif ($tab == 'followers') {
            $followersWithRoles = $role->followers()
                ->with(['roles' => function ($query) {
                    $query->wherePivotIn('level', ['owner', 'admin'])
                        ->where('is_deleted', false)
                        ->orderBy('role_user.created_at', 'asc')
                        ->limit(1);
                }])
                ->orderBy('pivot_created_at', 'desc')
                ->paginate(10);
        }

        return view('role/show-admin', compact(
            'subdomain',
            'role',
            'tab',
            'events',
            'members',
            'followers',
            'followersWithRoles',
            'requests',
            'month',
            'year',
            'startOfMonth',
            'endOfMonth',
            'unscheduled',
            'datesUnavailable',
        ));
    }

    public function createMember(Request $request, $subdomain)
    {
        if (! auth()->user()->isMember($subdomain)) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $role = Role::subdomain($subdomain)->firstOrFail();

        $data = [
            'role' => $role,
            'title' => __('messages.add_member'),
        ];

        return view('role/add-member', $data);
    }

    public function storeMember(MemberAddRequest $request, $subdomain)
    {
        if (! auth()->user()->isMember($subdomain)) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $role = Role::subdomain($subdomain)->firstOrFail();

        if (! $role->isPro()) {
            return redirect()->back()->with('error', __('messages.upgrade_to_pro'));
        } elseif (! $role->email_verified_at) {
            return redirect()->back()->with('error', __('messages.email_not_verified'));
        }

        $data = $request->validated();
        $user = User::whereEmail($data['email'])->first();

        if ($user && $user->isMember($subdomain)) {
            return redirect()->back()->with('error', __('messages.member_already_exists'));
        }

        if (! $user) {
            $user = new User;
            $user->email = $data['email'];
            $user->name = $data['name'];
            $user->password = bcrypt(Str::random(32));
            $user->timezone = $request->user()->timezone;
            $user->language_code = $request->user()->language_code;

            if (! config('app.hosted')) {
                $user->email_verified_at = now();
            }

            $user->save();
        }

        if ($user->isFollowing($subdomain)) {
            $roleUser = RoleUser::where('user_id', $user->id)
                ->where('role_id', $role->id)
                ->first();

            $roleUser->level = 'admin';
            $roleUser->save();

        } else {
            $user->roles()->attach($role->id, ['level' => 'admin', 'created_at' => now()]);
        }

        Notification::send($user, new AddedMemberNotification($role, $user, $request->user()));

        return redirect(route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'team']))
            ->with('message', __('messages.member_added'));
    }

    public function removeMember(Request $request, $subdomain, $hash)
    {
        if (! auth()->user()->isMember($subdomain)) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $userId = UrlUtils::decodeId($hash);
        $role = Role::subdomain($subdomain)->firstOrFail();

        if ($userId == $role->user_id) {
            return redirect()->back()->with('error', __('messages.cannot_remove_owner'));
        }

        $roleUser = RoleUser::where('user_id', $userId)
            ->where('role_id', $role->id)
            ->first();
        $roleUser->delete();

        // If user removed themselves, redirect to dashboard instead of team page
        if ($userId == auth()->id()) {
            return redirect(route('dashboard'))
                ->with('message', __('messages.removed_member'));
        }

        return redirect(route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'team']))
            ->with('message', __('messages.removed_member'));
    }

    public function following()
    {
        $user = auth()->user();
        $filter = strtolower(request()->filter);
        $sortBy = request()->get('sort_by', 'name');
        $sortDir = request()->get('sort_dir', 'asc');

        // Whitelist allowed sort columns
        $allowedSortColumns = ['name', 'type', 'email', 'phone', 'website'];
        if (! in_array($sortBy, $allowedSortColumns)) {
            $sortBy = 'name';
        }
        $sortDir = strtolower($sortDir) === 'desc' ? 'desc' : 'asc';

        $roleIds = $user->following()->pluck('roles.id');
        $query = Role::whereIn('id', $roleIds);

        if ($filter) {
            $query->where(function ($q) use ($filter) {
                $q->where('name', 'LIKE', "%{$filter}%")
                    ->orWhere('type', 'LIKE', "%{$filter}%")
                    ->orWhere('email', 'LIKE', "%{$filter}%")
                    ->orWhere('phone', 'LIKE', "%{$filter}%")
                    ->orWhere('website', 'LIKE', "%{$filter}%");
            });
        }

        $roles = $query->orderBy($sortBy, $sortDir)->get();

        $data = [
            'roles' => $roles,
            'sortBy' => $sortBy,
            'sortDir' => $sortDir,
        ];

        if (request()->ajax()) {
            return view('role/following_table', $data);
        }

        return view('role/index', $data);
    }

    public function create($type)
    {
        if (! is_hosted_or_admin()) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $role = new Role;
        $role->type = $type;
        $role->font_family = 'Roboto';
        $role->font_color = '#ffffff';
        $role->accent_color = '#007BFF';
        $role->background = 'image';
        $role->background_color = '#888888';
        $role->background_colors = ColorUtils::randomGradient();
        $role->background_image = ColorUtils::randomBackgroundImage();
        $role->background_rotation = rand(0, 359);
        $role->timezone = auth()->user()->timezone;
        $role->language_code = auth()->user()->language_code;

        if ($role->type == 'talent') {
            $role->name = auth()->user()->name;
        }

        // Header images
        $headers = file_get_contents(base_path('storage/headers.json'));
        $headers = json_decode($headers);

        $headerOptions = [];
        foreach ($headers as $header) {
            $headerOptions[$header->name] = str_replace('_', ' ', $header->name);
        }

        asort($headerOptions);

        $headerOptions = [
            '' => __('messages.custom'),
        ] + $headerOptions;

        // Background images
        $backgrounds = file_get_contents(base_path('storage/backgrounds.json'));
        $backgrounds = json_decode($backgrounds);

        $backgroundOptions = [];
        foreach ($backgrounds as $background) {
            $backgroundOptions[$background->name] = str_replace('_', ' ', $background->name);
        }

        asort($backgroundOptions);

        $backgroundOptions = [
            '' => __('messages.custom'),
        ] + $backgroundOptions;

        // Background gradients
        $gradients = file_get_contents(base_path('storage/gradients.json'));
        $gradients = json_decode($gradients);

        $gradientOptions = [];
        foreach ($gradients as $gradient) {
            $gradientOptions[implode(', ', $gradient->colors)] = $gradient->name;
        }

        asort($gradientOptions);

        $gradientOptions = [
            '' => __('messages.custom'),
        ] + $gradientOptions;

        $fonts = file_get_contents(base_path('storage/fonts.json'));
        $fonts = json_decode($fonts);

        $data = [
            'role' => $role,
            'user' => auth()->user(),
            'title' => __('messages.new_schedule'),
            'gradients' => $gradientOptions,
            'backgrounds' => $backgroundOptions,
            'headers' => $headerOptions,
            'fonts' => $fonts,
        ];

        return view('role/edit', $data);
    }

    public function store(RoleCreateRequest $request): RedirectResponse
    {
        if (! is_hosted_or_admin()) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $user = $request->user();

        $role = new Role;
        $role->fill($request->all());
        $role->subdomain = Role::generateSubdomain($request->name);
        $role->user_id = $user->id;

        // TODO remove this
        if (config('app.hosted')) {
            $role->plan_expires = now()->addYear()->format('Y-m-d');
            $role->plan_type = 'pro';
            $role->plan_term = 'year';
        }

        if (! config('app.hosted')) {
            $role->email_verified_at = now();
        } elseif ($role->email == $user->email) {
            $role->email_verified_at = $user->email_verified_at;
        }

        if (! $request->background_colors) {
            $role->background_colors = $request->custom_color1.', '.$request->custom_color2;
        }

        if ($request->has('import_urls') || $request->has('import_cities')) {
            $importConfig = [
                'urls' => array_map('strtolower', array_filter(array_map('trim', $request->input('import_urls', [])))),
                'cities' => array_map('strtolower', array_filter(array_map('trim', $request->input('import_cities', [])))),
            ];
            $role->import_config = $importConfig;
        }

        $role->save();

        // Handle sync direction and calendar setup for new role
        $syncDirection = $request->input('sync_direction');
        $calendarId = $request->input('google_calendar_id');
        if ($syncDirection || $calendarId) {
            $this->handleSyncAndCalendarChanges($role, $syncDirection, null, $calendarId, null);
        }

        // Save groups
        if ($request->has('groups')) {
            $groupsData = $request->input('groups', []);
            $groupNames = [];

            // Collect all group names for translation if needed
            foreach ($groupsData as $groupData) {
                if (! empty($groupData['name'])) {
                    $groupNames[] = $groupData['name'];
                }
            }

            // Translate names if role is not in English
            $translations = [];
            if (! empty($groupNames) && $role->language_code !== 'en') {
                try {
                    $translations = GeminiUtils::translateGroupNames($groupNames, $role->language_code);
                } catch (\Exception $e) {
                    \Log::error('Failed to translate group names: '.$e->getMessage());
                    // Continue without translations if API fails
                }
            }

            // Create groups with translations
            foreach ($groupsData as $groupData) {
                if (! empty($groupData['name'])) {
                    $groupCreateData = [
                        'name' => $groupData['name'],
                        'slug' => Str::slug($groupData['name']),
                    ];

                    // Preserve manually entered English name or add automatic translation
                    if (isset($groupData['name_en'])) {
                        $groupCreateData['name_en'] = $groupData['name_en'];
                        $groupCreateData['slug'] = Str::slug($groupData['name_en']);
                    } elseif (isset($translations[$groupData['name']])) {
                        $groupCreateData['name_en'] = $translations[$groupData['name']];
                        $groupCreateData['slug'] = Str::slug($translations[$groupData['name']]);
                    }

                    $role->groups()->create($groupCreateData);
                }
            }
        }

        $user->roles()->attach($role->id, ['created_at' => now(), 'level' => 'owner']);

        if ($request->hasFile('profile_image')) {
            if ($role->profile_image_url) {
                $path = $role->getAttributes()['profile_image_url'];
                if (config('filesystems.default') == 'local') {
                    $path = 'public/'.$path;
                }
                Storage::delete($path);
            }

            $file = $request->file('profile_image');
            $filename = strtolower('profile_'.Str::random(32).'.'.$file->getClientOriginalExtension());
            $path = $file->storeAs(config('filesystems.default') == 'local' ? '/public' : '/', $filename);

            $role->profile_image_url = $filename;
            $role->save();
        }

        if ($request->hasFile('header_image')) {
            if ($role->header_image_url) {
                $path = $role->getAttributes()['header_image_url'];
                if (config('filesystems.default') == 'local') {
                    $path = 'public/'.$path;
                }
                Storage::delete($path);
            }

            $file = $request->file('header_image');
            $filename = strtolower('header_'.Str::random(32).'.'.$file->getClientOriginalExtension());
            $path = $file->storeAs(config('filesystems.default') == 'local' ? '/public' : '/', $filename);

            $role->header_image_url = $filename;
            $role->save();
        }

        if ($role->background == 'image' && $request->background_image) {
            $role->background_image = $request->background_image;
            $role->save();
        } elseif ($role->background == 'image' && $request->hasFile('background_image_url')) {
            if ($role->background_image_url) {
                $path = $role->getAttributes()['background_image_url'];
                if (config('filesystems.default') == 'local') {
                    $path = 'public/'.$path;
                }
                Storage::delete($path);
            }

            $file = $request->file('background_image_url');
            $filename = strtolower('background_'.Str::random(32).'.'.$file->getClientOriginalExtension());
            $path = $file->storeAs(config('filesystems.default') == 'local' ? '/public' : '/', $filename);

            $role->background_image_url = $filename;
            $role->save();
        }

        if (! $role->email_verified_at) {
            $role->sendEmailVerificationNotification();
        }

        $message = __('messages.created_schedule');

        if ($subdomain = session('pending_request')) {
            return redirect(route('event.create', ['subdomain' => $role->subdomain]))->with('message', $message);
        } else {
            return redirect(route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'schedule']))->with('message', $message);
        }
    }

    public function edit($subdomain)
    {
        if (! is_hosted_or_admin()) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $role = Role::with('groups')->subdomain($subdomain)->firstOrFail();

        // Header images
        $headers = file_get_contents(base_path('storage/headers.json'));
        $headers = json_decode($headers);

        $headerOptions = [];
        foreach ($headers as $header) {
            $headerOptions[$header->name] = str_replace('_', ' ', $header->name);
        }

        asort($headerOptions);

        $headerOptions = [
            '' => __('messages.custom'),
        ] + $headerOptions;

        // Background images
        $backgrounds = file_get_contents(base_path('storage/backgrounds.json'));
        $backgrounds = json_decode($backgrounds);

        $backgroundOptions = [];
        foreach ($backgrounds as $background) {
            $backgroundOptions[$background->name] = str_replace('_', ' ', $background->name);
        }

        asort($backgroundOptions);

        $backgroundOptions = [
            '' => __('messages.custom'),
        ] + $backgroundOptions;

        // Background gradients
        $gradients = file_get_contents(base_path('storage/gradients.json'));
        $gradients = json_decode($gradients);

        $gradientOptions = [];
        foreach ($gradients as $gradient) {
            $gradientOptions[implode(', ', $gradient->colors)] = $gradient->name;
        }

        asort($gradientOptions);

        $gradientOptions = [
            '' => __('messages.custom'),
        ] + $gradientOptions;

        $fonts = file_get_contents(base_path('storage/fonts.json'));
        $fonts = json_decode($fonts);

        $data = [
            'user' => auth()->user(),
            'role' => $role,
            'title' => __('messages.edit_schedule'),
            'gradients' => $gradientOptions,
            'backgrounds' => $backgroundOptions,
            'headers' => $headerOptions,
            'fonts' => $fonts,
        ];

        return view('role/edit', $data);
    }

    public function update(RoleUpdateRequest $request, $subdomain): RedirectResponse
    {
        if (! is_hosted_or_admin()) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        if (! auth()->user()->isMember($subdomain)) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $role = Role::subdomain($subdomain)->firstOrFail();
        $existingSettings = $role->getEmailSettings();

        // Handle sync_direction and calendar changes and webhook management
        $oldSyncDirection = $role->sync_direction;
        $newSyncDirection = $request->input('sync_direction');
        $oldCalendarId = $role->google_calendar_id;
        $newCalendarId = $request->input('google_calendar_id');

        $role->fill($request->all());

        // If sync_direction or calendar changed, handle webhook management
        if (($newSyncDirection && $oldSyncDirection !== $newSyncDirection) ||
            ($oldCalendarId !== $newCalendarId)) {
            $this->handleSyncAndCalendarChanges($role, $newSyncDirection, $oldSyncDirection, $newCalendarId, $oldCalendarId);
        }

        $newSubdomain = Role::cleanSubdomain($request->new_subdomain);
        if ($newSubdomain != $subdomain) {
            if (Role::subdomain($newSubdomain)->first()) {
                return redirect()->back()->withErrors(['new_subdomain' => __('messages.subdomain_taken')]);
            }
            $role->subdomain = $newSubdomain;
        }

        if (! $request->background_colors) {
            $role->background_colors = $request->custom_color1.', '.$request->custom_color2;
        }

        if ($request->has('import_urls') || $request->has('import_cities')) {
            $importConfig = [
                'urls' => array_map('strtolower', array_filter(array_map('trim', $request->input('import_urls', [])))),
                'cities' => array_map('strtolower', array_filter(array_map('trim', $request->input('import_cities', [])))),
            ];
            $role->import_config = $importConfig;
        }

        // Handle email settings
        if ($request->has('email_settings')) {
            $submittedSettings = $request->input('email_settings', []);

            // If password is all bullets, use the old value
            if (isset($submittedSettings['password'])) {
                $passwordValue = trim($submittedSettings['password']);
                if ($passwordValue === '••••••••••' || $passwordValue === str_repeat('•', 10)) {
                    // Use existing password instead
                    if (isset($existingSettings['password'])) {
                        $submittedSettings['password'] = $existingSettings['password'];
                    } else {
                        // No existing password, remove it
                        unset($submittedSettings['password']);
                    }
                }
            }

            // Merge with existing settings to preserve values not being updated
            $emailSettings = array_merge($existingSettings, $submittedSettings);
            $role->setEmailSettings($emailSettings);
        }

        $role->save();

        // Sync groups
        $existingGroupIds = $role->groups()->pluck('id')->toArray();
        $submittedGroups = $request->input('groups', []);
        $submittedIds = [];

        // Collect new group names for translation
        $newGroupNames = [];
        foreach ($submittedGroups as $key => $groupData) {
            if (isset($groupData['name']) && $groupData['name'] && ! is_numeric($key) && empty($groupData['name_en'])) {
                $newGroupNames[] = $groupData['name'];
            }
        }

        // Translate new group names if role is not in English
        $translations = [];
        if (! empty($newGroupNames) && $role->language_code !== 'en') {
            try {
                $translations = GeminiUtils::translateGroupNames($newGroupNames, $role->language_code);
            } catch (\Exception $e) {
                \Log::error('Failed to translate group names: '.$e->getMessage());
                // Continue without translations if API fails
            }
        }

        foreach ($submittedGroups as $key => $groupData) {
            if (isset($groupData['name']) && $groupData['name']) {
                if (is_numeric($key) && in_array($key, $existingGroupIds)) {
                    // Update existing
                    $updateData = [
                        'name' => $groupData['name'],
                        'slug' => $groupData['slug'] ?? Str::slug($groupData['name']),
                    ];

                    // Preserve manually entered English name or add automatic translation
                    if (isset($groupData['name_en'])) {
                        $updateData['name_en'] = $groupData['name_en'];
                    } elseif (isset($translations[$groupData['name']])) {
                        $updateData['name_en'] = $translations[$groupData['name']];
                        // Use English name for slug if translation is available
                        $updateData['slug'] = Str::slug($translations[$groupData['name']]);
                    }

                    $role->groups()->where('id', $key)->update($updateData);
                    $submittedIds[] = $key;
                } else {
                    // New group
                    $createData = [
                        'name' => $groupData['name'],
                        'slug' => Str::slug($groupData['name']),
                    ];

                    // Preserve manually entered English name or add automatic translation
                    if (isset($groupData['name_en'])) {
                        $createData['name_en'] = $groupData['name_en'];
                        $createData['slug'] = Str::slug($groupData['name_en']);
                    } elseif (isset($translations[$groupData['name']])) {
                        $createData['name_en'] = $translations[$groupData['name']];
                        $createData['slug'] = Str::slug($translations[$groupData['name']]);
                    }

                    $newGroup = $role->groups()->create($createData);
                    $submittedIds[] = $newGroup->id;
                }
            }
        }

        // Delete removed groups
        $toDelete = array_diff($existingGroupIds, $submittedIds);
        if (! empty($toDelete)) {
            $role->groups()->whereIn('id', $toDelete)->delete();
        }

        if ($request->hasFile('profile_image')) {
            if ($role->profile_image_url) {
                $path = $role->getAttributes()['profile_image_url'];
                if (config('filesystems.default') == 'local') {
                    $path = 'public/'.$path;
                }
                Storage::delete($path);
            }

            $file = $request->file('profile_image');
            $filename = strtolower('profile_'.Str::random(32).'.'.$file->getClientOriginalExtension());
            $path = $file->storeAs(config('filesystems.default') == 'local' ? '/public' : '/', $filename);

            $role->profile_image_url = $filename;
            $role->save();
        }

        if ($request->hasFile('header_image_url')) {
            if ($role->header_image_url) {
                $path = $role->getAttributes()['header_image_url'];
                if (config('filesystems.default') == 'local') {
                    $path = 'public/'.$path;
                }
                Storage::delete($path);
            }

            $file = $request->file('header_image_url');
            $filename = strtolower('header_'.Str::random(32).'.'.$file->getClientOriginalExtension());
            $path = $file->storeAs(config('filesystems.default') == 'local' ? '/public' : '/', $filename);

            $role->header_image_url = $filename;
            $role->save();
        }

        if ($role->background == 'image' && $request->background_image) {
            $role->background_image = $request->background_image;
            $role->save();
        } elseif ($role->background == 'image' && $request->hasFile('background_image_url')) {
            if ($role->background_image_url) {
                $path = $role->getAttributes()['background_image_url'];
                if (config('filesystems.default') == 'local') {
                    $path = 'public/'.$path;
                }
                Storage::delete($path);
            }

            $file = $request->file('background_image_url');
            $filename = strtolower('background_'.Str::random(32).'.'.$file->getClientOriginalExtension());
            $path = $file->storeAs(config('filesystems.default') == 'local' ? '/public' : '/', $filename);

            $role->background_image_url = $filename;
            $role->save();
        }

        return redirect(route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'schedule']))
            ->with('message', __('messages.updated_schedule'));
    }

    public function updateLinks(Request $request, $subdomain): RedirectResponse
    {
        if (! auth()->user()->isMember($subdomain)) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $request->validate([
            'link_type' => 'required|in:social_links,payment_links,youtube_links',
            'link' => 'required|string|max:1000',
        ]);

        $role = Role::subdomain($subdomain)->firstOrFail();

        if ($request->link_type == 'social_links') {
            $links = $role->social_links;
        } elseif ($request->link_type == 'payment_links') {
            $links = $role->payment_links;
        } else {
            $links = $role->youtube_links;
        }

        if (! $links) {
            $links = '[]';
        }

        $links = json_decode($links);

        foreach (explode(',', $request->link) as $link) {
            $link = trim($link);

            if (! $link) {
                continue;
            }

            $urlInfo = UrlUtils::getUrlInfo($link);
            if ($urlInfo !== null) {
                $links[] = $urlInfo;
            }
        }

        $links = json_encode($links);

        if ($request->link_type == 'social_links') {
            $role->social_links = $links;
        } elseif ($request->link_type == 'payment_links') {
            $role->payment_links = $links;
        } else {
            $role->youtube_links = $links;
        }

        $role->save();

        return redirect(route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'profile']))
            ->with('message', __('messages.added_link'));
    }

    public function removeLinks(Request $request, $subdomain): RedirectResponse
    {
        if (! auth()->user()->isMember($subdomain)) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $request->validate([
            'remove_link_type' => 'required|in:social_links,payment_links,youtube_links',
            'remove_link' => 'required|string|max:1000',
        ]);

        $role = Role::subdomain($subdomain)->firstOrFail();
        if ($request->remove_link_type == 'social_links') {
            $links = $role->social_links;
        } elseif ($request->remove_link_type == 'payment_links') {
            $links = $role->payment_links;
        } else {
            $links = $role->youtube_links;
        }

        if (! $links) {
            $links = '[]';
        }

        $links = json_decode($links);
        $new_links = [];

        foreach ($links as $link) {
            if ($link->url != $request->remove_link) {
                $new_links[] = $link;
            }
        }

        $new_links = json_encode($new_links);

        if ($request->remove_link_type == 'social_links') {
            $role->social_links = $new_links;
        } elseif ($request->remove_link_type == 'payment_links') {
            $role->payment_links = $new_links;
        } else {
            $role->youtube_links = $new_links;
        }

        $role->save();

        return redirect(route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'profile']))
            ->with('message', __('messages.removed_link'));
    }

    public function qrCode($subdomain)
    {
        $role = Role::subdomain($subdomain)->firstOrFail();
        $url = $role->custom_domain ? 'https://'.$role->custom_domain : $role->getGuestUrl();

        $qrCode = QrCode::create($url)
            ->setSize(300)
            ->setMargin(10);

        $writer = new PngWriter;
        $result = $writer->write($qrCode);

        header('Content-Type: '.$result->getMimeType());
        header('Content-Disposition: attachment; filename="qr-code.png"');

        echo $result->getString();

        exit;
    }

    public function verify(RoleEmailVerificationRequest $request, $subdomain)
    {
        $role = Role::whereSubdomain($subdomain)->firstOrFail();

        if ($role->hasVerifiedEmail()) {
            return redirect()->route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'schedule']);
        }

        if ($role->markEmailAsVerified()) {
            event(new Verified($role));
        }

        return redirect()
            ->route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'schedule'])
            ->with('message', __('messages.confirmed_email'));
    }

    public function resendVerify(Request $request, $subdomain)
    {
        $role = Role::whereSubdomain($subdomain)->firstOrFail();

        if ($role->hasVerifiedEmail()) {
            return redirect()->route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'schedule']);
        }

        $role->sendEmailVerificationNotification();

        return redirect()
            ->route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'schedule'])
            ->with('message', __('messages.sent_confirmation_email'));
    }

    public function request(Request $request, $subdomain)
    {
        $role = Role::whereSubdomain($subdomain)->firstOrFail();

        session(['pending_request' => $subdomain]);

        $mainDomain = config('app.url');

        if (! auth()->user()) {
            $lang = session()->has('translate') ? 'en' : $role->language_code;
            $redirectUrl = $mainDomain.route('sign_up', ['lang' => $lang], false);

            return redirect($redirectUrl);
        }

        $user = auth()->user();

        if (! $user->isConnected($subdomain)) {
            $user->roles()->attach($role->id, ['level' => 'follower', 'created_at' => now()]);
        }

        if ($user->talents()->count() == 0) {
            $redirectUrl = $mainDomain.route('new', ['type' => 'talent'], false);

            return redirect($redirectUrl);
        }

        $role = $user->talents()->first();
        $redirectUrl = $mainDomain.route('event.create', ['subdomain' => $role->subdomain], false);

        return redirect($redirectUrl);
    }

    public function validateAddress(Request $request)
    {
        $role = new Role;
        $role->fill($request->all());

        if ($address = $role->fullAddress()) {
            $urlAddress = urlencode($address);

            // Validate Google API configuration
            $apiKey = config('services.google.backend');
            if (! $apiKey) {
                return response()->json(['error' => 'Geocoding service not configured'], 500);
            }

            $url = 'https://maps.googleapis.com/maps/api/geocode/json?key='.$apiKey."&address={$urlAddress}";

            // Use secure cURL instead of file_get_contents
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_CONNECTTIMEOUT => 5,
                CURLOPT_USERAGENT => 'EventSchedule/1.0',
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
                CURLOPT_PROTOCOLS => CURLPROTO_HTTPS, // Only HTTPS for Google API
                CURLOPT_MAXFILESIZE => 1048576, // 1MB limit
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($response === false || $httpCode !== 200) {
                return response()->json(['error' => 'Failed to validate address'], 500);
            }

            $responseData = json_decode($response, true);

            if (! $responseData || $responseData['status'] !== 'OK') {
                return response()->json(['error' => 'Address validation failed'], 400);
            }

            $result = $responseData['results'][0];
            $addressComponents = $result['address_components'];

            $addressParts = [
                'street_number' => '',
                'route' => '',
                'sublocality_level_1' => '',
                'locality' => '',
                'administrative_area_level_1' => '',
                'postal_code' => '',
                'country' => '',
            ];

            foreach ($addressComponents as $component) {
                foreach ($component['types'] as $type) {
                    if (array_key_exists($type, $addressParts)) {
                        $addressParts[$type] = $type == 'country' ? $component['short_name'] : $component['long_name'];
                    }
                }
            }

            $address1 = trim($addressParts['street_number'].' '.$addressParts['route']);
            $address2 = $addressParts['sublocality_level_1'];
            $city = $addressParts['locality'] ?: $addressParts['sublocality_level_1'];
            $state = $addressParts['administrative_area_level_1'];
            $postal_code = $addressParts['postal_code'];
            $country = $addressParts['country'];

            return response()->json([
                'success' => true,
                'data' => [
                    'address1' => $address1,
                    'address2' => $address2,
                    'city' => $city,
                    'state' => $state,
                    'postal_code' => $postal_code,
                    'country' => $country,
                    'formatted_address' => $address1.' '.$city.' '.$state.' '.$postal_code,
                    'lat' => $result['geometry']['location']['lat'],
                    'lng' => $result['geometry']['location']['lng'],
                ],
            ]);
        }

        return response()->json(['error' => 'No address provided'], 400);
    }

    public function availability(Request $request, $subdomain)
    {
        $role = Role::subdomain($subdomain)->firstOrFail();
        $user = $request->user();

        $roleUser = RoleUser::where('user_id', $user->id)
            ->where('role_id', $role->id)
            ->first();

        $dates = json_decode($roleUser->dates_unavailable);
        $available = json_decode($request->available_days);
        $unavailable = json_decode($request->unavailable_days);

        if ($dates) {
            $dates = array_diff($dates, $available);
        } else {
            $dates = [];
        }

        foreach ($unavailable as $date) {
            $dates[] = $date;
        }

        $dates = array_unique($dates);
        asort($dates);
        $dates = array_values($dates);

        $roleUser->dates_unavailable = json_encode($dates);
        $roleUser->save();

        $data = [
            'subdomain' => $subdomain,
            'tab' => 'availability',
            'month' => $request->month,
            'year' => $request->year,
        ];

        return redirect(route('role.view_admin', $data))
            ->with('message', __('messages.updated_availability'));
    }

    public function showUnsubscribe(Request $request)
    {
        return view('role/unsubscribe');
    }

    public function unsubscribe(Request $request)
    {
        $roles = Role::where('email', base64_decode($request->email))->get();

        foreach ($roles as $role) {
            $role->is_subscribed = false;
            $role->save();
        }

        return redirect()->route('role.show_unsubscribe', ['email' => $request->email]);
    }

    public function unsubscribeUser(Request $request)
    {
        $email = null;

        if ($request->has('email')) {
            $email = base64_decode($request->email);
        }

        if (! $email) {
            return redirect()->route('role.show_unsubscribe')->with('error', 'Invalid unsubscribe link.');
        }

        $users = User::where('email', $email)->get();

        foreach ($users as $user) {
            $user->is_subscribed = false;
            $user->save();
        }

        return redirect()->route('role.show_unsubscribe', ['email' => base64_encode($email)])->with('status', __('messages.unsubscribed'));
    }

    public function subscribe(Request $request, $subdomain)
    {
        $role = Role::subdomain($subdomain)->firstOrFail();

        $role->is_subscribed = true;
        $role->save();

        return redirect()
            ->back()
            ->with('message', __('messages.subscribed'));
    }

    public function resendInvite(Request $request, $subdomain, $hash)
    {
        if (! auth()->user()->isMember($subdomain)) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $role = Role::subdomain($subdomain)->firstOrFail();
        $userId = UrlUtils::decodeId($hash);
        $user = User::findOrFail($userId);

        Notification::send($user, new AddedMemberNotification($role, $user, $request->user()));

        return redirect()->back()->with('message', __('messages.invite_resent'));
    }

    public function search(Request $request)
    {
        $type = $request->type;
        $search = $request->search;

        $roles = Role::whereIn('type', $type == 'venue' ? ['venue'] : ['talent'])
            ->where(function ($query) use ($search) {
                $query->where('email', '=', $search);
                // ->orWhere('phone', '=', $search)
                // ->orWhere('name', 'like', "%{$search}%");
            })
            ->get([
                'id',
                'subdomain',
                'name',
                'address1',
                'address2',
                'city',
                'state',
                'postal_code',
                'email',
                'user_id',
                'profile_image_url',
                'country_code',
                'youtube_links',
            ]);

        $roles = $roles->map(function ($role) {
            return $role->toData();
        });

        $roles = array_values($roles->toArray());

        return response()->json($roles);
    }

    public function searchEvents(Request $request, $subdomain)
    {
        $role = Role::subdomain($subdomain)->firstOrFail();
        $search = $request->get('q', '');
        $groupSlug = $request->get('group', '');

        if (empty($search)) {
            return response()->json([]);
        }

        // Get the group ID if a group slug is provided
        $groupId = null;
        if (! empty($groupSlug)) {
            $group = $role->groups()->where('slug', $groupSlug)->first();
            $groupId = $group ? $group->id : null;
        }

        // First, let's try a very basic search to see if we can get any events at all
        if ($role->isCurator()) {
            // For curators, get events they're associated with
            $events = Event::with('roles')
                ->whereIn('id', function ($query) use ($role) {
                    $query->select('event_id')
                        ->from('event_role')
                        ->where('role_id', $role->id)
                        ->where('is_accepted', true);
                })
                ->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                })
                ->when($groupId, function ($query) use ($groupId) {
                    $query->whereIn('id', function ($subQuery) use ($groupId) {
                        $subQuery->select('event_id')
                            ->from('event_role')
                            ->where('group_id', $groupId);
                    });
                })
                ->orderBy('starts_at')
                ->limit(10)
                ->get();
        } else {
            // For venues/talents
            $baseQuery = Event::with('roles');

            $baseQuery->whereHas('roles', function ($query) use ($role) {
                $query->where('role_id', $role->id)
                    ->where('is_accepted', true);
            });

            $events = $baseQuery
                ->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                })
                ->when($groupId, function ($query) use ($groupId) {
                    $query->whereIn('id', function ($subQuery) use ($groupId) {
                        $subQuery->select('event_id')
                            ->from('event_role')
                            ->where('group_id', $groupId);
                    });
                })
                ->orderBy('starts_at')
                ->limit(10)
                ->get();
        }

        // Format events for frontend
        $eventsData = $events->map(function ($event) use ($subdomain, $role) {
            $groupId = $event->getGroupIdForSubdomain($role->subdomain);

            return [
                'id' => $event->id,
                'name' => $event->translatedName(),
                'description' => $event->translatedDescription(),
                'venue_name' => $event->getVenueDisplayName(),
                'local_starts_at' => $event->localStartsAt(),
                'image_url' => $event->getImageUrl(),
                'guest_url' => $event->getGuestUrl($subdomain, ''),
                'group_id' => $groupId ? \App\Utils\UrlUtils::encodeId($groupId) : null,
                'category_id' => $event->category_id,
            ];
        });

        return response()->json($eventsData);
    }

    public function changePlan($subdomain, $plan_type)
    {
        $role = Role::subdomain($subdomain)->firstOrFail();

        if (auth()->user()->id != $role->user_id) {
            return redirect()->back()->with('error', __('messages.unauthorized'));
        }

        $role->plan_type = $plan_type;
        $role->plan_expires = null;
        $role->save();

        return redirect()->back()->with('message', __('messages.plan_changed'));
    }

    public function testImport(Request $request, $subdomain)
    {
        if (! auth()->user()->isMember($subdomain)) {
            return response()->json(['success' => false, 'message' => __('messages.not_authorized')], 403);
        }

        $role = Role::subdomain($subdomain)->firstOrFail();
        $roleId = $role->id;

        if (! $roleId) {
            return response()->json(['success' => false, 'message' => __('messages.role_id_required')]);
        }

        // Ensure roleId is properly formatted
        if (! is_numeric($roleId)) {
            $roleId = \App\Utils\UrlUtils::decodeId($roleId);
        }

        try {
            // Get URLs and cities from the request
            $urls = $request->input('urls', []);
            $cities = $request->input('cities', []);

            // Filter out empty values
            $urls = array_filter(array_map('trim', $urls));
            $cities = array_filter(array_map('trim', $cities));

            if (empty($urls) && empty($cities)) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.please_enter_urls_or_cities'),
                ]);
            }

            // Use shell_exec as a fallback if Artisan call fails
            try {
                // Capture output using BufferedOutput
                $output = new \Symfony\Component\Console\Output\BufferedOutput;

                $artisanParams = [
                    '--role_id' => (string) $roleId,
                    '--test' => null,
                ];

                // Add URLs and cities as command line arguments
                foreach ($urls as $url) {
                    $artisanParams['--urls'][] = $url;
                }
                foreach ($cities as $city) {
                    $artisanParams['--cities'][] = $city;
                }

                $exitCode = \Artisan::call('app:import-curator-events', $artisanParams, $output);

                $outputText = $output->fetch();
            } catch (\Exception $e) {
                // Fallback to shell_exec if Artisan call fails
                $urlArgs = '';
                $cityArgs = '';

                if (! empty($urls)) {
                    $urlArgs = ' --urls='.implode(' --urls=', array_map('escapeshellarg', $urls));
                }
                if (! empty($cities)) {
                    $cityArgs = ' --cities='.implode(' --cities=', array_map('escapeshellarg', $cities));
                }

                $command = "php artisan app:import-curator-events --role_id={$roleId} --test{$urlArgs}{$cityArgs} 2>&1";
                $outputText = shell_exec($command);
                $exitCode = 0; // Assume success for shell_exec
            }

            // Check if the command was successful - look for completion message or successful processing
            $isSuccessful = strpos($outputText, 'Import completed') !== false ||
                           strpos($outputText, 'Import test successful') !== false ||
                           $exitCode === 0;

            // If no events were processed but the command ran successfully, that's still a success
            if ($isSuccessful) {
                return response()->json([
                    'success' => true,
                    'message' => __('messages.import_test_success'),
                    'output' => $outputText,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.import_test_error'),
                    'output' => $outputText,
                ]);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.import_test_error').': '.$e->getMessage(),
            ]);
        }
    }

    public function getTalentRolesWithoutVideos(Request $request, $subdomain)
    {
        if (! auth()->user()->isMember($subdomain)) {
            return response()->json(['success' => false, 'message' => __('messages.not_authorized')], 403);
        }

        $role = Role::subdomain($subdomain)->firstOrFail();

        if (! $role->isCurator()) {
            return response()->json(['success' => false, 'message' => __('messages.not_authorized')], 403);
        }

        // Get upcoming events for this curator
        $upcomingEvents = Event::with('roles')
            ->where('starts_at', '>=', now()->subDay())
            ->whereIn('id', function ($query) use ($role) {
                $query->select('event_id')
                    ->from('event_role')
                    ->where('role_id', $role->id)
                    ->where('is_accepted', true);
            })
            ->orderBy('starts_at')
            ->get();

        // Get talent roles from these events that don't have YouTube videos
        $talentRoles = collect();

        foreach ($upcomingEvents as $event) {
            foreach ($event->roles as $eventRole) {
                if ($eventRole->isTalent() && (! $eventRole->youtube_links && $eventRole->youtube_links != '[]')) {
                    // Check if we already have this role
                    if (! $talentRoles->contains('id', $eventRole->id)) {
                        $talentRoles->push([
                            'id' => $eventRole->id,
                            'name' => $eventRole->name,
                            'description' => $eventRole->description,
                            'profile_image_url' => $eventRole->profile_image_url,
                            'event_name' => $event->name,
                            'event_date' => $event->localStartsAt(),
                        ]);
                    }
                }
            }
        }

        return response()->json($talentRoles->values());
    }

    public function searchYouTube(Request $request, $subdomain)
    {
        if (! auth()->user()->isMember($subdomain)) {
            return response()->json(['success' => false, 'message' => __('messages.not_authorized')], 403);
        }

        $role = Role::subdomain($subdomain)->firstOrFail();

        if (! $role->isCurator()) {
            return response()->json(['success' => false, 'message' => __('messages.not_authorized')], 403);
        }

        $query = $request->get('q');

        if (empty($query)) {
            return response()->json(['success' => false, 'message' => __('messages.query_required')]);
        }

        try {
            $videos = \App\Utils\GeminiUtils::searchYouTube($query);

            return response()->json([
                'success' => true,
                'videos' => $videos,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('messages.error_searching_videos').': '.$e->getMessage(),
            ]);
        }
    }

    public function guestSearchYouTube(Request $request, $subdomain)
    {
        // For guest users, we don't require authentication but we do validate the subdomain
        $role = Role::subdomain($subdomain)->firstOrFail();

        $query = $request->get('q');

        if (empty($query)) {
            return response()->json(['success' => false, 'message' => __('messages.query_required')]);
        }

        // Check if YouTube API key is configured
        $apiKey = config('services.google.backend');
        if (! $apiKey) {
            return response()->json([
                'success' => false,
                'message' => 'YouTube API key not configured. Please contact the administrator.',
            ]);
        }

        try {
            $videos = \App\Utils\GeminiUtils::searchYouTube($query);

            if (empty($videos)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No videos found for "'.$query.'". Please try a different search term.',
                ]);
            }

            return response()->json([
                'success' => true,
                'videos' => $videos,
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => __('messages.error_searching_videos').': '.$e->getMessage(),
            ]);
        }
    }

    public function saveVideo(Request $request, $subdomain)
    {
        if (! auth()->user()->isMember($subdomain)) {
            return response()->json(['success' => false, 'message' => __('messages.not_authorized')], 403);
        }

        $role = Role::subdomain($subdomain)->firstOrFail();

        if (! $role->isCurator()) {
            return response()->json(['success' => false, 'message' => __('messages.not_authorized')], 403);
        }

        $request->validate([
            'role_id' => 'required|integer',
            'video_url' => 'required|url',
            'video_title' => 'required|string',
        ]);

        $talentRole = Role::find($request->role_id);

        if (! $talentRole || ! $talentRole->isTalent()) {
            return response()->json(['success' => false, 'message' => __('messages.talent_role_not_found')]);
        }

        // Add the video to the talent role's YouTube links
        $existingLinks = $talentRole->youtube_links ? json_decode($talentRole->youtube_links, true) : [];

        $newLink = [
            'url' => $request->video_url,
            'title' => $request->video_title,
            'type' => 'youtube',
        ];

        $existingLinks[] = $newLink;

        $talentRole->youtube_links = json_encode($existingLinks);
        $talentRole->save();

        return response()->json([
            'success' => true,
            'message' => __('messages.video_saved_successfully'),
        ]);
    }

    public function saveVideos(Request $request, $subdomain)
    {
        if (! auth()->user()->isMember($subdomain)) {
            return response()->json(['success' => false, 'message' => __('messages.not_authorized')], 403);
        }

        $role = Role::subdomain($subdomain)->firstOrFail();

        if (! $role->isCurator()) {
            return response()->json(['success' => false, 'message' => __('messages.not_authorized')], 403);
        }

        $request->validate([
            'role_id' => 'required|integer',
            'videos' => 'array|max:2',
        ]);

        // Only validate video details if videos are provided
        if (! empty($request->videos)) {
            $request->validate([
                'videos.*.url' => 'required|url',
                'videos.*.title' => 'required|string',
            ]);
        }

        $talentRole = Role::find($request->role_id);

        if (! $talentRole || ! $talentRole->isTalent()) {
            return response()->json(['success' => false, 'message' => __('messages.talent_role_not_found')]);
        }

        // Add the videos to the talent role's YouTube links
        $existingLinks = $talentRole->youtube_links ? json_decode($talentRole->youtube_links, true) : [];

        foreach ($request->videos as $video) {
            $newLink = [
                'url' => $video['url'],
                'title' => $video['title'],
                'type' => 'youtube',
            ];

            $existingLinks[] = $newLink;
        }

        $talentRole->youtube_links = json_encode($existingLinks);
        $talentRole->save();

        return response()->json([
            'success' => true,
            'message' => __('messages.videos_saved_successfully'),
        ]);
    }

    /**
     * Handle sync direction and calendar changes and webhook management
     */
    private function handleSyncAndCalendarChanges($role, $newSyncDirection, $oldSyncDirection, $newCalendarId = null, $oldCalendarId = null)
    {
        $user = auth()->user();

        if (! $user->google_token) {
            return; // No Google Calendar connected, skip webhook management
        }

        try {
            $googleCalendarService = app(\App\Services\GoogleCalendarService::class);

            // Check if we need to remove old webhook (calendar changed or sync direction changed)
            $shouldRemoveOldWebhook = ($oldCalendarId !== $newCalendarId) ||
                                    ($oldSyncDirection !== $newSyncDirection &&
                                     ($oldSyncDirection === 'from' || $oldSyncDirection === 'both'));

            if ($shouldRemoveOldWebhook && $role->google_webhook_id) {
                // Delete existing webhook
                if ($googleCalendarService->ensureValidToken($user)) {
                    $googleCalendarService->setAccessToken([
                        'access_token' => $user->fresh()->google_token,
                        'refresh_token' => $user->fresh()->google_refresh_token,
                        'expires_in' => $this->calculateExpiresIn($user->fresh()->google_token_expires_at),
                    ]);

                    $googleCalendarService->deleteWebhook($role->google_webhook_id, $role->google_webhook_resource_id);
                }

                $role->update([
                    'google_webhook_id' => null,
                    'google_webhook_resource_id' => null,
                    'google_webhook_expires_at' => null,
                ]);
            }

            // Handle webhook management based on sync direction
            if ($newSyncDirection === 'from' || $newSyncDirection === 'both') {
                // Need webhook for syncing from Google
                if (! $role->hasActiveWebhook()) {
                    // Ensure user has valid token before creating webhook
                    if (! $googleCalendarService->ensureValidToken($user)) {
                        \Log::warning('Google Calendar token invalid and refresh failed during sync direction change', [
                            'user_id' => $user->id,
                            'role_id' => $role->id,
                        ]);

                        return;
                    }

                    $googleCalendarService->setAccessToken([
                        'access_token' => $user->fresh()->google_token,
                        'refresh_token' => $user->fresh()->google_refresh_token,
                        'expires_in' => $this->calculateExpiresIn($user->fresh()->google_token_expires_at),
                    ]);

                    $calendarId = $role->getGoogleCalendarId();
                    $webhookUrl = route('google.calendar.webhook.handle');

                    $webhook = $googleCalendarService->createWebhook($calendarId, $webhookUrl);

                    $role->update([
                        'google_webhook_id' => $webhook['id'],
                        'google_webhook_resource_id' => $webhook['resourceId'],
                        'google_webhook_expires_at' => \Carbon\Carbon::createFromTimestamp($webhook['expiration'] / 1000),
                    ]);
                }
            }

        } catch (\Exception $e) {
            \Log::error('Failed to handle sync and calendar changes', [
                'user_id' => $user->id,
                'role_id' => $role->id,
                'old_sync_direction' => $oldSyncDirection,
                'new_sync_direction' => $newSyncDirection,
                'old_calendar_id' => $oldCalendarId,
                'new_calendar_id' => $newCalendarId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Safely calculate expires_in seconds from google_token_expires_at
     */
    private function calculateExpiresIn($expiresAt): int
    {
        if (! $expiresAt) {
            return 3600; // Default to 1 hour
        }

        if (is_string($expiresAt)) {
            $expiresAt = \Carbon\Carbon::parse($expiresAt);
        }

        return $expiresAt->diffInSeconds(now());
    }

    /**
     * Send test email to verify SMTP credentials
     */
    public function testEmail(Request $request, $subdomain): JsonResponse
    {
        if (! is_hosted_or_admin()) {
            return response()->json(['error' => __('messages.not_authorized')], 403);
        }

        if (! auth()->user()->isMember($subdomain)) {
            return response()->json(['error' => __('messages.not_authorized')], 403);
        }

        $role = Role::subdomain($subdomain)->firstOrFail();

        // Get email from request or from role's email settings
        $email = $request->input('email');
        if (! $email) {
            $emailSettings = $role->getEmailSettings();
            $email = $emailSettings['from_address'] ?? null;
        }

        if (! $email) {
            return response()->json(['error' => __('messages.please_enter_from_address')], 400);
        }

        // Validate email format
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json(['error' => __('messages.invalid_email_address')], 400);
        }

        // If email_settings are provided in the request (from form), temporarily apply them to the role
        // This allows testing before saving the form
        if ($request->has('email_settings')) {
            $submittedSettings = $request->input('email_settings', []);
            $existingSettings = $role->getEmailSettings();

            // Convert port to integer if provided
            if (isset($submittedSettings['port']) && $submittedSettings['port'] !== '') {
                $submittedSettings['port'] = (int) $submittedSettings['port'];
            }

            // If password is all bullets, use the old value
            if (isset($submittedSettings['password'])) {
                $passwordValue = trim($submittedSettings['password']);
                if ($passwordValue === '••••••••••' || $passwordValue === str_repeat('•', 10)) {
                    // Use existing password instead
                    if (isset($existingSettings['password'])) {
                        $submittedSettings['password'] = $existingSettings['password'];
                    } else {
                        // No existing password, remove it
                        unset($submittedSettings['password']);
                    }
                }
            }

            // Merge with existing settings to preserve values not being updated
            $emailSettings = array_merge($existingSettings, $submittedSettings);

            // Temporarily set email settings on the role for testing
            $role->setEmailSettings($emailSettings);
        }

        $emailService = new EmailService;

        try {
            $emailService->sendTestEmail($role, $email);

            return response()->json(['success' => true, 'message' => __('messages.test_email_sent')]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
