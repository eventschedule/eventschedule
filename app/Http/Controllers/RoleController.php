<?php

namespace App\Http\Controllers;

use App\Http\Requests\MemberAddRequest;
use App\Http\Requests\RoleCreateRequest;
use App\Http\Requests\RoleEmailVerificationRequest;
use App\Http\Requests\RoleUpdateRequest;
use App\Http\Requests\RoleVideoSaveRequest;
use App\Http\Requests\RoleVideosSaveRequest;
use App\Models\AnalyticsAppearancesDaily;
use App\Models\AnalyticsDaily;
use App\Models\AnalyticsReferrersDaily;
use App\Models\BoostCampaign;
use App\Models\Event;
use App\Models\Role;
use App\Models\RoleUser;
use App\Models\User;
use App\Notifications\AddedMemberNotification;
use App\Notifications\DeletedRoleNotification;
use App\Repos\EventRepo;
use App\Services\AnalyticsService;
use App\Services\AuditService;
use App\Services\BoostBillingService;
use App\Services\DemoService;
use App\Services\DigitalOceanService;
use App\Services\EmailService;
use App\Services\MetaAdsService;
use App\Services\SmsService;
use App\Services\UsageTrackingService;
use App\Utils\ColorUtils;
use App\Utils\GeminiUtils;
use App\Utils\ImageUtils;
use App\Utils\OpenAIUtils;
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
    use Traits\CalendarDataTrait;

    protected $eventRepo;

    public function __construct(EventRepo $eventRepo)
    {
        $this->eventRepo = $eventRepo;
    }

    public function deleteImage(Request $request, $subdomain)
    {
        if (is_demo_mode()) {
            return response()->json(['error' => __('messages.demo_mode_restriction')], 403);
        }

        $role = Role::subdomain($subdomain)->firstOrFail();

        $this->authorize('update', $role);

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

        if ($request->expectsJson()) {
            return response()->json(['message' => __('messages.deleted_image')]);
        }

        return redirect(route('role.edit', ['subdomain' => $subdomain]))
            ->with('message', __('messages.deleted_image'));
    }

    public function delete(Request $request, $subdomain)
    {
        $user = $request->user();
        $role = Role::subdomain($subdomain)->firstOrFail();
        $type = $role->type;

        if (is_demo_role($role)) {
            return redirect()->back()->with('error', __('messages.demo_mode_settings_disabled'));
        }

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

        if ($role->sponsor_logos) {
            $sponsors = json_decode($role->sponsor_logos, true) ?: [];
            foreach ($sponsors as $sponsor) {
                if (! empty($sponsor['logo'])) {
                    $path = $sponsor['logo'];
                    if (config('filesystems.default') == 'local') {
                        $path = 'public/'.$path;
                    }
                    Storage::delete($path);
                }
            }
        }

        $emails = $role->members()->pluck('email');

        // Clean up custom domain from DigitalOcean before deleting role
        if ($role->custom_domain_mode === 'direct' && $role->custom_domain_host) {
            try {
                $doService = app(DigitalOceanService::class);
                if ($doService->isConfigured()) {
                    $doService->removeDomain($role->custom_domain_host);
                    \Illuminate\Support\Facades\Cache::forget("custom_domain:{$role->custom_domain_host}");
                }
            } catch (\Exception $e) {
                \Log::warning('Failed to clean up custom domain during role deletion', [
                    'role_id' => $role->id,
                    'hostname' => $role->custom_domain_host,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Clean up Google Calendar webhook before deleting role
        if ($role->google_webhook_id && $role->google_webhook_resource_id) {
            try {
                $googleUser = $role->users()->first();
                if ($googleUser && $googleUser->google_token) {
                    $googleCalendarService = app(\App\Services\GoogleCalendarService::class);

                    // Ensure user has valid token before deleting webhook
                    if ($googleCalendarService->ensureValidToken($googleUser)) {
                        $googleCalendarService->deleteWebhook($role->google_webhook_id, $role->google_webhook_resource_id);
                    }
                }
            } catch (\Exception $e) {
                \Log::warning('Failed to clean up webhook during role deletion', [
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

        AuditService::log(AuditService::SCHEDULE_DELETE, $user->id, 'Role', $role->id, null, null, $role->name);

        // Cancel active boost campaigns before deletion (prevents orphaned Meta campaigns)
        $activeCampaigns = BoostCampaign::where('role_id', $role->id)
            ->whereIn('status', ['active', 'paused', 'pending_payment'])
            ->get();

        foreach ($activeCampaigns as $campaign) {
            try {
                $cancelled = \DB::transaction(function () use ($campaign) {
                    $campaign = BoostCampaign::lockForUpdate()->find($campaign->id);
                    if (! $campaign || ! $campaign->canBeCancelled()) {
                        return false;
                    }
                    $campaign->update([
                        'status' => 'cancelled',
                        'meta_status' => $campaign->meta_campaign_id ? 'DELETED' : null,
                    ]);

                    return true;
                });

                if ($cancelled) {
                    if ($campaign->meta_campaign_id) {
                        (new MetaAdsService)->deleteCampaign($campaign);
                    }

                    if (config('app.hosted') && ! config('app.is_testing')) {
                        $campaign->refresh();
                        if (! in_array($campaign->billing_status, ['refunded', 'partially_refunded'])) {
                            $billingService = new BoostBillingService;
                            if ($campaign->billing_status === 'pending') {
                                if ($campaign->stripe_payment_intent_id) {
                                    $billingService->cancelPaymentIntent($campaign);
                                }
                            } else {
                                $campaign->actual_spend && $campaign->actual_spend > 0
                                    ? $billingService->refundUnspent($campaign)
                                    : $billingService->refundFull($campaign);
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                \Log::warning('Failed to cancel boost campaign during schedule deletion', [
                    'campaign_id' => $campaign->id,
                    'role_id' => $role->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $role->delete();

        try {
            Notification::route('mail', $emails)->notify(new DeletedRoleNotification($role, $user));
        } catch (\Exception $e) {
            \Log::warning('Failed to send deletion notification', [
                'role_id' => $role->id,
                'error' => $e->getMessage(),
            ]);
        }

        return redirect(route('home'))
            ->with('message', __('messages.deleted_schedule'));
    }

    public function follow(Request $request, $subdomain)
    {
        $role = Role::subdomain($subdomain)->firstOrFail();

        if (! auth()->user()) {
            session(['pending_follow' => $subdomain]);
            $lang = session()->has('translate') ? 'en' : $role->language_code;

            return redirect(app_url(route('sign_up', ['lang' => $lang], false)));
        }

        $user = $request->user();

        // Prevent demo account from following other roles
        if (DemoService::isDemoUser($user)) {
            return redirect(app_url(route('following', [], false)))
                ->with('error', __('messages.demo_mode_restriction'));
        }

        if (! $user->isConnected($role->subdomain)) {
            $user->roles()->attach($role->id, ['level' => 'follower', 'created_at' => now()]);
        }

        session()->forget('pending_follow');

        if ($subdomain = session('pending_request')) {
            $pendingRole = Role::whereSubdomain($subdomain)->first();

            if ($pendingRole && $pendingRole->isTalent()) {
                // Requesting a talent - need a venue schedule
                if ($user->venues()->count() == 0) {
                    return redirect(app_url(route('new', ['type' => 'venue'], false)));
                }
                $redirectRole = $user->venues()->first();
            } else {
                // Requesting a venue/curator - need a talent schedule
                if ($user->talents()->count() == 0) {
                    return redirect(app_url(route('new', ['type' => 'talent'], false)));
                }
                $redirectRole = $user->talents()->first();
            }

            return redirect(app_url(route('event.create', ['subdomain' => $redirectRole->subdomain], false)));

        } else {
            return redirect(app_url(route('following', [], false)))
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

    public function viewGuest(Request $request, $subdomain, $slug = '', $id = null, $date = null)
    {
        $translation = null;
        $user = auth()->user();
        $curatorRoles = $user ? $user->curators() : collect();

        $role = Role::subdomain($subdomain)->with('groups')->first();

        if (! $role || ! $role->isClaimed()) {
            return redirect(app_url());
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
        // Support both path params and query params (backwards compatibility)
        $date = $date ?: ($request->date ? date('Y-m-d', strtotime($request->date)) : null);
        if ($date && ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $date = null;
        }
        $eventIdParam = $id ? UrlUtils::decodeId($id) : ($request->id ? UrlUtils::decodeId($request->id) : null);

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
                    $event = $this->eventRepo->getEvent($subdomain, $slug, $date, $eventIdParam, $role);
                }
            } else {
                // Try to find event by slug
                $event = $this->eventRepo->getEvent($subdomain, $slug, $date, $eventIdParam, $role);
            }

            // Fallback: allow schedule members to view pending (not yet accepted) events
            if (! $event && $eventIdParam && $user && $user->isMember($subdomain)) {
                $event = Event::with(['roles', 'parts.approvedVideos.user', 'parts.approvedComments.user', 'parts.approvedPhotos.user', 'tickets', 'user', 'approvedVideos.user', 'approvedComments.user', 'approvedPhotos.user', 'polls' => fn ($q) => $q->withCount('votes')])
                    ->where('id', $eventIdParam)
                    ->whereHas('roles', fn ($q) => $q->where('role_id', $role->id)->whereNull('is_accepted'))
                    ->first();
            }

            if ($event) {
                // Block direct URL access to private events for non-members
                // Only enforce if the schedule still has Enterprise access
                if ($event->is_private && $role->isEnterprise() && ! $event->isPasswordProtected() && (! $user || (! $user->isMember($subdomain) && ! $user->isAdmin()))) {
                    $event = null;
                }
            }

            if ($event) {
                // Handle direct registration redirect when URL has trailing slash
                if ($request->attributes->get('has_trailing_slash') && $event->registration_url) {
                    if (! auth()->user()?->isAdmin()) {
                        app(AnalyticsService::class)->recordView($role, $event, $request);
                    }

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
            if ($role->isCurator()) {
                // When viewing from curator, prioritize first claimed talent, fallback to claimed venue
                $talentRoles = $event->roles->filter(fn ($r) => $r->type === 'talent');
                $claimedTalent = $talentRoles->first(fn ($r) => $r->isClaimed());
                if ($claimedTalent) {
                    $otherRole = $claimedTalent;
                } elseif ($event->venue && $event->venue->isClaimed()) {
                    $otherRole = $event->venue;
                } else {
                    $otherRole = $role;
                }
            } elseif ($event->venue) {
                if ($event->venue->subdomain == $subdomain) {
                    // When viewing from venue, find a talent role (exclude venue from roles)
                    $talentRoles = $event->roles->filter(fn ($r) => $r->type === 'talent');
                    if ($talentRoles->count() > 0) {
                        // Prioritize the role matching the URL slug, otherwise use first talent
                        $otherRole = $talentRoles->firstWhere('subdomain', $slug) ?? $talentRoles->first();
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
                if (! $event->days_of_week) {
                    $date = $startAtDate->format('Y-m-d');
                }
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
        $timezone = $user?->timezone ?? $role->timezone ?? 'UTC';

        // Calculate month boundaries in user's/role's timezone, then convert to UTC for database query
        $startOfMonth = Carbon::create($year, $month, 1, 0, 0, 0, $timezone)->startOfMonth();

        // Convert to UTC for database query
        $startOfMonthUtc = $startOfMonth->copy()->setTimezone('UTC');

        $isMemberOrAdmin = $user && ($user->isMember($subdomain) || $user->isAdmin());
        $unlockedEventIds = ! $isMemberOrAdmin ? $this->getUnlockedEventIds() : [];

        if ($event && ! request()->graphic) {
            // For event detail view (non-graphic), only check if calendar has events
            // The calendar partial loads data via Ajax, so we just need existence
            if ($role->isCurator()) {
                $query = Event::where(function ($query) use ($startOfMonthUtc) {
                    $query->where('starts_at', '>=', $startOfMonthUtc)
                        ->orWhereNotNull('days_of_week');
                })
                    ->whereIn('id', function ($query) use ($role) {
                        $query->select('event_id')
                            ->from('event_role')
                            ->where('role_id', $role->id)
                            ->where('is_accepted', true);
                    });
                if (! $isMemberOrAdmin) {
                    $query->where(function ($q) use ($unlockedEventIds) {
                        $q->where('is_private', false);
                        if ($unlockedEventIds) {
                            $q->orWhereIn('id', $unlockedEventIds);
                        }
                    });
                }
                $hasCalendarEvents = $query->exists();
            } else {
                $query = Event::where(function ($query) use ($startOfMonthUtc) {
                    $query->where('starts_at', '>=', $startOfMonthUtc)
                        ->orWhereNotNull('days_of_week');
                })
                    ->whereHas('roles', fn ($q) => $q->where('role_id', $role->id)->where('is_accepted', true));
                if (! $isMemberOrAdmin) {
                    $query->where(function ($q) use ($unlockedEventIds) {
                        $q->where('is_private', false);
                        if ($unlockedEventIds) {
                            $q->orWhereIn('id', $unlockedEventIds);
                        }
                    });
                }
                $hasCalendarEvents = $query->exists();
            }
            $events = $hasCalendarEvents ? collect([true]) : collect();
        } elseif ($role->isCurator()) {
            $events = Event::with(['roles', 'parts', 'approvedVideos', 'approvedPhotos', 'approvedComments.user', 'polls' => fn ($q) => $q->withCount('votes')])->withCount(['approvedVideos', 'approvedComments', 'approvedPhotos', 'polls'])
                ->where(function ($query) use ($startOfMonthUtc) {
                    $query->where('starts_at', '>=', $startOfMonthUtc)
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
            $events = Event::with(['roles', 'parts', 'approvedVideos', 'approvedPhotos', 'approvedComments.user', 'polls' => fn ($q) => $q->withCount('votes')])->withCount(['approvedVideos', 'approvedComments', 'approvedPhotos', 'polls'])
                ->where(function ($query) use ($startOfMonthUtc) {
                    $query->where('starts_at', '>=', $startOfMonthUtc)
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

        // Fetch past non-recurring events only for graphic mode (otherwise loaded via Ajax)
        $pastEvents = collect();
        $hasMorePastEvents = false;
        if (request()->graphic && ! $role->hide_past_events) {
            if ($role->isCurator()) {
                $pastEvents = Event::with(['roles', 'parts', 'approvedVideos', 'approvedPhotos', 'approvedComments.user', 'polls' => fn ($q) => $q->withCount('votes')])->withCount(['approvedVideos', 'approvedComments', 'approvedPhotos', 'polls'])
                    ->where('starts_at', '<', Carbon::now('UTC'))
                    ->whereNull('days_of_week')
                    ->whereIn('id', function ($query) use ($role) {
                        $query->select('event_id')
                            ->from('event_role')
                            ->where('role_id', $role->id)
                            ->where('is_accepted', true);
                    })
                    ->orderByDesc('starts_at')
                    ->limit(51)
                    ->get();
            } else {
                $pastEvents = Event::with(['roles', 'parts', 'approvedVideos', 'approvedPhotos', 'approvedComments.user', 'polls' => fn ($q) => $q->withCount('votes')])->withCount(['approvedVideos', 'approvedComments', 'approvedPhotos', 'polls'])
                    ->where('starts_at', '<', Carbon::now('UTC'))
                    ->whereNull('days_of_week')
                    ->whereHas('roles', fn ($q) => $q->where('role_id', $role->id)->where('is_accepted', true))
                    ->orderByDesc('starts_at')
                    ->limit(51)
                    ->get();
            }
            $hasMorePastEvents = $pastEvents->count() > 50;
            if ($hasMorePastEvents) {
                $pastEvents = $pastEvents->take(50);
            }
        }

        // Filter private events from calendar listings for non-members
        if (! $isMemberOrAdmin && $events->first() instanceof Event) {
            $events = $events->filter(fn ($e) => ! $e->is_private || in_array($e->id, $unlockedEventIds));
            $pastEvents = $pastEvents->filter(fn ($e) => ! $e->is_private || in_array($e->id, $unlockedEventIds));
        }

        // Track view for analytics (non-member visits only, skip embeds)
        if (! request()->embed && (! $user || (! $user->isMember($subdomain) && ! $user->isAdmin()))) {
            app(AnalyticsService::class)->recordView($role, $event, $request);
        }

        $myPendingVideos = collect();
        $myPendingComments = collect();
        $myPendingPhotos = collect();
        $photoLimitReached = false;
        $userSale = null;

        $embed = request()->embed;
        $view = 'role/show-guest';

        if ($embed && $event && (request()->get('tickets') === 'true' || request()->get('rsvp') === 'true')) {
            // Password check for embed mode
            $bypassPassword = ($user && ($user->isAdmin() || $user->isMember($subdomain)))
                || session()->has('event_password_'.$event->id);
            if ($event->isPasswordProtected() && $role->isEnterprise() && ! $bypassPassword) {
                abort(404);
            }
            $view = 'event/show-guest-ticket-embed';
            $event->loadMissing(['tickets']);
        } elseif ($embed) {
            $view = 'role/show-guest-embed';
        } elseif ($event) {
            // Check if event requires a password and user hasn't provided it
            $bypassPassword = ($user && ($user->isAdmin() || $user->isMember($subdomain)))
                || session()->has('event_password_'.$event->id);

            if ($event->isPasswordProtected() && $role->isEnterprise() && ! $bypassPassword) {
                $fonts = [];
                if ($event->venue) {
                    $fonts[] = $event->venue->font_family;
                }
                foreach ($event->roles as $each) {
                    if ($each->isClaimed() && $each->isTalent()) {
                        $fonts[] = $each->font_family;
                    }
                }
                $fonts = array_unique($fonts);

                $passwordGate = true;

                return response()->view('event/password-prompt', compact(
                    'role', 'event', 'date', 'fonts', 'passwordGate'
                ));
            }

            $view = 'event/show-guest';
            $event->loadMissing(['approvedVideos.user', 'approvedComments.user', 'approvedPhotos.user', 'polls' => fn ($q) => $q->withCount('votes')]);
            $photoLimitReached = ! $role->canUploadPhoto();

            if (auth()->check()) {
                $myPendingVideos = \App\Models\EventVideo::where('event_id', $event->id)
                    ->where('user_id', auth()->id())
                    ->where('is_approved', false)
                    ->get();
                $myPendingComments = \App\Models\EventComment::where('event_id', $event->id)
                    ->where('user_id', auth()->id())
                    ->where('is_approved', false)
                    ->with('user')
                    ->get();
                $myPendingPhotos = \App\Models\EventPhoto::where('event_id', $event->id)
                    ->where('user_id', auth()->id())
                    ->where('is_approved', false)
                    ->get();

                $userSale = \App\Models\Sale::where('event_id', $event->id)
                    ->where('status', 'paid')
                    ->where('is_deleted', false)
                    ->where(function ($q) {
                        $q->where('user_id', auth()->id())
                            ->orWhere('email', auth()->user()->email);
                    })
                    ->when($date, fn ($q, $d) => $q->where('event_date', $d))
                    ->first();
            }
        }

        $fonts = [];
        if ($event) {
            if ($event->venue) {
                $fonts[] = $event->venue->font_family;
            }
            foreach ($event->roles as $each) {
                if ($each->isClaimed() && $each->isTalent()) {
                    $fonts[] = $each->font_family;
                }
            }
        } else {
            $fonts[] = $role->font_family;
        }

        $fonts = array_unique($fonts);

        $response = response()
            ->view($view, compact(
                'subdomain',
                'events',
                'role',
                'otherRole',
                'month',
                'year',
                'startOfMonth',
                'user',
                'event',
                'embed',
                'date',
                'curatorRoles',
                'fonts',
                'translation',
                'selectedGroup',
                'pastEvents',
                'hasMorePastEvents',
                'myPendingVideos',
                'myPendingComments',
                'myPendingPhotos',
                'photoLimitReached',
                'userSale',
            ));

        return $response;
    }

    public function checkEventPassword(Request $request, $subdomain)
    {
        $request->validate([
            'event_id' => 'required|string',
            'password' => 'required|string',
        ]);

        $eventId = UrlUtils::decodeId($request->event_id);
        $event = Event::whereHas('roles', fn ($q) => $q->where('subdomain', $subdomain))
            ->find($eventId);

        if (! $event) {
            abort(404);
        }

        // Construct redirect URL server-side from event's guest URL
        $redirectUrl = $event->getGuestUrl($subdomain);

        if ($event->event_password && hash_equals($event->event_password, $request->password)) {
            session()->put('event_password_'.$event->id, true);

            return redirect($redirectUrl);
        }

        return redirect($redirectUrl)->with('password_error', true);
    }

    public function listPastEvents(Request $request, $subdomain): JsonResponse
    {
        $role = Role::subdomain($subdomain)->with('groups')->first();

        if (! $role || ! $role->isClaimed()) {
            return response()->json(['events' => [], 'has_more' => false]);
        }

        if ($role->hide_past_events) {
            return response()->json(['events' => [], 'has_more' => false]);
        }

        $before = $request->input('before');
        if (! $before) {
            return response()->json(['events' => [], 'has_more' => false]);
        }

        try {
            $beforeDate = Carbon::parse($before)->setTimezone('UTC');
        } catch (\Exception $e) {
            return response()->json(['events' => [], 'has_more' => false]);
        }

        $user = auth()->user();
        $isMemberOrAdmin = $user && ($user->isMember($subdomain) || $user->isAdmin());
        $unlockedEventIds = ! $isMemberOrAdmin ? $this->getUnlockedEventIds() : [];

        if ($role->isCurator()) {
            $pastEvents = Event::with(['roles', 'parts', 'approvedVideos', 'approvedPhotos', 'approvedComments.user', 'polls' => fn ($q) => $q->withCount('votes')])->withCount(['approvedVideos', 'approvedComments', 'approvedPhotos', 'polls'])
                ->where('starts_at', '<', $beforeDate)
                ->whereNull('days_of_week')
                ->whereIn('id', function ($query) use ($role) {
                    $query->select('event_id')
                        ->from('event_role')
                        ->where('role_id', $role->id)
                        ->where('is_accepted', true);
                })
                ->when(! $isMemberOrAdmin, function ($q) use ($unlockedEventIds) {
                    $q->where(function ($q) use ($unlockedEventIds) {
                        $q->where('is_private', false);
                        if ($unlockedEventIds) {
                            $q->orWhereIn('id', $unlockedEventIds);
                        }
                    });
                })
                ->orderByDesc('starts_at')
                ->limit(51)
                ->get();
        } else {
            $pastEvents = Event::with(['roles', 'parts', 'approvedVideos', 'approvedPhotos', 'approvedComments.user', 'polls' => fn ($q) => $q->withCount('votes')])->withCount(['approvedVideos', 'approvedComments', 'approvedPhotos', 'polls'])
                ->where('starts_at', '<', $beforeDate)
                ->whereNull('days_of_week')
                ->whereHas('roles', fn ($q) => $q->where('role_id', $role->id)->where('is_accepted', true))
                ->when(! $isMemberOrAdmin, function ($q) use ($unlockedEventIds) {
                    $q->where(function ($q) use ($unlockedEventIds) {
                        $q->where('is_private', false);
                        if ($unlockedEventIds) {
                            $q->orWhereIn('id', $unlockedEventIds);
                        }
                    });
                })
                ->orderByDesc('starts_at')
                ->limit(51)
                ->get();
        }

        $hasMore = $pastEvents->count() > 50;
        if ($hasMore) {
            $pastEvents = $pastEvents->take(50);
        }

        $eventsData = $pastEvents->map(function ($event) use ($role, $subdomain) {
            return $this->eventToVueArray($event, $role, $subdomain);
        })->values()->toArray();

        return response()->json([
            'events' => $eventsData,
            'has_more' => $hasMore,
        ]);
    }

    private function eventToVueArray(Event $event, ?Role $role, ?string $subdomain): array
    {
        $groupId = $role ? $event->getGroupIdForSubdomain($role->subdomain) : null;

        $data = [
            'id' => UrlUtils::encodeId($event->id),
            'group_id' => $groupId ? UrlUtils::encodeId($groupId) : null,
            'category_id' => $event->category_id,
            'name' => $event->translatedName(),
            'venue_name' => $event->getVenueDisplayName(),
            'starts_at' => $event->starts_at,
            'days_of_week' => $event->days_of_week,
            'local_starts_at' => $event->localStartsAt(),
            'local_date' => $event->starts_at ? $event->getStartDateTime(null, true)->format('Y-m-d') : null,
            'utc_date' => $event->starts_at ? $event->getStartDateTime(null, false)->format('Y-m-d') : null,
            'guest_url' => $event->getGuestUrl($subdomain ?? '', ''),
            'image_url' => $event->getImageUrl(),
            'can_edit' => auth()->user() && auth()->user()->canEditEvent($event),
            'edit_url' => auth()->user() && auth()->user()->canEditEvent($event)
                ? ($role ? app_url(route('event.edit', ['subdomain' => $role->subdomain, 'hash' => UrlUtils::encodeId($event->id)], false)) : app_url(route('event.edit_admin', ['hash' => UrlUtils::encodeId($event->id)], false)))
                : null,
            'recurring_end_type' => $event->recurring_end_type ?? 'never',
            'recurring_end_value' => $event->recurring_end_value,
            'start_date' => $event->starts_at ? $event->getStartDateTime(null, true)->format('Y-m-d') : null,
            'is_online' => ! empty($event->event_url),
            'description_excerpt' => Str::words(html_entity_decode(strip_tags($event->translatedDescription())), 25, '...'),
            'duration' => $event->duration,
            'parts' => $event->parts->map(fn ($part) => [
                'name' => $part->name,
                'start_time' => $part->start_time,
                'end_time' => $part->end_time,
            ])->values()->toArray(),
            'video_count' => $event->approved_videos_count ?? 0,
            'comment_count' => $event->approved_comments_count ?? 0,
            'photo_count' => $event->approved_photos_count ?? 0,
            'venue_profile_image' => $event->venue?->profile_image_url ?: null,
            'venue_header_image' => ($event->venue && $event->venue->getAttributes()['header_image'] && $event->venue->getAttributes()['header_image'] !== 'none') ? $event->venue->getHeaderImageUrlAttribute($event->venue->getAttributes()['header_image']) : null,
            'talent' => $event->roles->filter(fn ($r) => $r->type === 'talent')->map(fn ($r) => [
                'name' => $r->name,
                'profile_image' => $r->profile_image_url ?: null,
                'header_image' => ($r->getAttributes()['header_image'] && $r->getAttributes()['header_image'] !== 'none') ? $r->getHeaderImageUrlAttribute($r->getAttributes()['header_image']) : null,
            ])->values()->toArray(),
            'videos' => $event->approvedVideos->take(3)->map(fn ($v) => [
                'youtube_url' => $v->youtube_url,
                'thumbnail_url' => UrlUtils::getYouTubeThumbnail($v->youtube_url),
                'embed_url' => UrlUtils::getYouTubeEmbed($v->youtube_url),
            ])->values()->toArray(),
            'recent_comments' => $event->approvedComments->take(2)->map(fn ($c) => [
                'author' => $c->user ? ($c->user->first_name ?: 'User') : 'User',
                'text' => Str::limit($c->comment, 80),
            ])->values()->toArray(),
            'occurrenceDate' => $event->starts_at ? $event->getStartDateTime(null, true)->format('Y-m-d') : null,
            'uniqueKey' => UrlUtils::encodeId($event->id),
            'is_password_protected' => $event->isPasswordProtected(),
        ];

        if ($event->isPasswordProtected()) {
            $data['description_excerpt'] = null;
            $data['venue_name'] = null;
            $data['venue_profile_image'] = null;
            $data['venue_header_image'] = null;
            $data['talent'] = [];
            $data['videos'] = [];
            $data['recent_comments'] = [];
            $data['parts'] = [];
            $data['image_url'] = null;
        }

        return $data;
    }

    public function calendarEvents(Request $request, $subdomain): JsonResponse
    {
        $role = Role::subdomain($subdomain)->with('groups')->first();

        if (! $role || ! $role->isClaimed()) {
            return response()->json(['events' => [], 'eventsMap' => (object) [], 'pastEvents' => [], 'hasMorePastEvents' => false, 'filterMeta' => ['uniqueCategoryIds' => [], 'hasOnlineEvents' => false]]);
        }

        $month = $request->month ?: now()->month;
        $year = $request->year ?: now()->year;

        $user = auth()->user();
        $timezone = ($user ? $user->timezone : null) ?? $role->timezone ?? 'UTC';

        $startOfMonth = Carbon::create($year, $month, 1, 0, 0, 0, $timezone)->startOfMonth();

        $startOfMonthUtc = $startOfMonth->copy()->setTimezone('UTC');

        $isMemberOrAdmin = $user && ($user->isMember($subdomain) || $user->isAdmin());
        $unlockedEventIds = ! $isMemberOrAdmin ? $this->getUnlockedEventIds() : [];

        if ($role->isCurator()) {
            $events = Event::with(['roles', 'parts', 'tickets', 'approvedVideos', 'approvedPhotos', 'approvedComments.user', 'polls' => fn ($q) => $q->withCount('votes')])->withCount(['approvedVideos', 'approvedComments', 'approvedPhotos', 'polls'])
                ->where(function ($query) use ($startOfMonthUtc) {
                    $query->where('starts_at', '>=', $startOfMonthUtc)
                        ->orWhereNotNull('days_of_week');
                })
                ->whereIn('id', function ($query) use ($role) {
                    $query->select('event_id')
                        ->from('event_role')
                        ->where('role_id', $role->id)
                        ->where('is_accepted', true);
                })
                ->when(! $isMemberOrAdmin, function ($q) use ($unlockedEventIds) {
                    $q->where(function ($q) use ($unlockedEventIds) {
                        $q->where('is_private', false);
                        if ($unlockedEventIds) {
                            $q->orWhereIn('id', $unlockedEventIds);
                        }
                    });
                })
                ->orderBy('starts_at')
                ->get();
        } else {
            $events = Event::with(['roles', 'parts', 'tickets', 'approvedVideos', 'approvedPhotos', 'approvedComments.user', 'polls' => fn ($q) => $q->withCount('votes')])->withCount(['approvedVideos', 'approvedComments', 'approvedPhotos', 'polls'])
                ->where(function ($query) use ($startOfMonthUtc) {
                    $query->where('starts_at', '>=', $startOfMonthUtc)
                        ->orWhereNotNull('days_of_week');
                })
                ->where(function ($query) use ($role) {
                    $query->whereHas('roles', function ($query) use ($role) {
                        $query->where('role_id', $role->id)
                            ->where('is_accepted', true);
                    });
                })
                ->when(! $isMemberOrAdmin, function ($q) use ($unlockedEventIds) {
                    $q->where(function ($q) use ($unlockedEventIds) {
                        $q->where('is_private', false);
                        if ($unlockedEventIds) {
                            $q->orWhereIn('id', $unlockedEventIds);
                        }
                    });
                })
                ->orderBy('starts_at')
                ->get();
        }

        $pastEvents = collect();
        $hasMorePastEvents = false;

        if (! $role->hide_past_events) {
            if ($role->isCurator()) {
                $pastEvents = Event::with(['roles', 'parts', 'tickets', 'approvedVideos', 'approvedPhotos', 'approvedComments.user', 'polls' => fn ($q) => $q->withCount('votes')])->withCount(['approvedVideos', 'approvedComments', 'approvedPhotos', 'polls'])
                    ->where('starts_at', '<', Carbon::now('UTC'))
                    ->whereNull('days_of_week')
                    ->whereIn('id', function ($query) use ($role) {
                        $query->select('event_id')
                            ->from('event_role')
                            ->where('role_id', $role->id)
                            ->where('is_accepted', true);
                    })
                    ->when(! $isMemberOrAdmin, function ($q) use ($unlockedEventIds) {
                        $q->where(function ($q) use ($unlockedEventIds) {
                            $q->where('is_private', false);
                            if ($unlockedEventIds) {
                                $q->orWhereIn('id', $unlockedEventIds);
                            }
                        });
                    })
                    ->orderByDesc('starts_at')
                    ->limit(51)
                    ->get();
            } else {
                $pastEvents = Event::with(['roles', 'parts', 'tickets', 'approvedVideos', 'approvedPhotos', 'approvedComments.user', 'polls' => fn ($q) => $q->withCount('votes')])->withCount(['approvedVideos', 'approvedComments', 'approvedPhotos', 'polls'])
                    ->where('starts_at', '<', Carbon::now('UTC'))
                    ->whereNull('days_of_week')
                    ->whereHas('roles', fn ($q) => $q->where('role_id', $role->id)->where('is_accepted', true))
                    ->when(! $isMemberOrAdmin, function ($q) use ($unlockedEventIds) {
                        $q->where(function ($q) use ($unlockedEventIds) {
                            $q->where('is_private', false);
                            if ($unlockedEventIds) {
                                $q->orWhereIn('id', $unlockedEventIds);
                            }
                        });
                    })
                    ->orderByDesc('starts_at')
                    ->limit(51)
                    ->get();
            }

            $hasMorePastEvents = $pastEvents->count() > 50;
            if ($hasMorePastEvents) {
                $pastEvents = $pastEvents->take(50);
            }
        }

        return $this->buildCalendarResponse($events, $pastEvents, $hasMorePastEvents, $role, $subdomain, (int) $month, (int) $year, $timezone, $role->first_day_of_week ?? 0);
    }

    public function adminCalendarEvents(Request $request, $subdomain): JsonResponse
    {
        if (! auth()->user()->isMember($subdomain)) {
            return response()->json(['error' => 'Not authorized'], 403);
        }

        $role = Role::subdomain($subdomain)->firstOrFail();

        $month = $request->month ?: now()->month;
        $year = $request->year ?: now()->year;

        $user = $request->user();
        $timezone = $user->timezone ?? $role->timezone ?? 'UTC';

        $startOfMonth = Carbon::create($year, $month, 1, 0, 0, 0, $timezone)->startOfMonth();

        $startOfMonthUtc = $startOfMonth->copy()->setTimezone('UTC');

        if ($role->isCurator()) {
            $events = Event::with('roles', 'parts', 'tickets')
                ->where(function ($query) use ($startOfMonthUtc) {
                    $query->where('starts_at', '>=', $startOfMonthUtc)
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
            $events = Event::with('roles', 'parts', 'tickets')
                ->where(function ($query) use ($role) {
                    $query->whereHas('roles', function ($query) use ($role) {
                        $query->where('role_id', $role->id)
                            ->where('is_accepted', true);
                    });
                })
                ->where(function ($query) use ($startOfMonthUtc) {
                    $query->where('starts_at', '>=', $startOfMonthUtc)
                        ->orWhereNotNull('days_of_week');
                })
                ->orderBy('starts_at')
                ->get();
        }

        return $this->buildCalendarResponse($events, collect(), false, $role, $subdomain, (int) $month, (int) $year, $timezone, $role->first_day_of_week ?? 0);
    }

    public function viewAdmin(Request $request, $subdomain, $tab = 'schedule')
    {
        if (! auth()->user()->isMember($subdomain)) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $role = Role::subdomain($subdomain)->firstOrFail();

        if (config('app.hosted') && auth()->user()->id != $role->user_id && ! $role->isEnterprise()) {
            return redirect()->route('home')->with('error', __('messages.enterprise_required_for_team_access'));
        }

        $teamSortBy = '';
        $teamSortDir = 'asc';
        $membersQuery = $role->members();
        if ($tab == 'team') {
            $teamSortBy = request()->get('sort_by', '');
            $teamSortDir = strtolower(request()->get('sort_dir', 'asc')) === 'desc' ? 'desc' : 'asc';
            $allowedTeamSortColumns = ['name', 'email'];
            if (! in_array($teamSortBy, $allowedTeamSortColumns)) {
                $teamSortBy = '';
            }
            if ($teamSortBy) {
                $membersQuery->orderBy($teamSortBy, $teamSortDir);
            }
        }
        $members = $membersQuery->get();
        $followers = $role->followers()->get();
        $followersWithRoles = [];

        $events = collect();
        $unscheduled = [];
        $requests = [];
        $month = $request->month;
        $year = $request->year;
        $startOfMonth = '';
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

            // Convert to UTC for database query
            $startOfMonthUtc = $startOfMonth->copy()->setTimezone('UTC');

            if ($tab == 'schedule') {
                if ($role->isCurator()) {
                    $events = Event::with('roles')
                        ->where(function ($query) use ($startOfMonthUtc) {
                            $query->where('starts_at', '>=', $startOfMonthUtc)
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
                        ->where(function ($query) use ($startOfMonthUtc) {
                            $query->where('starts_at', '>=', $startOfMonthUtc)
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

                // Events will be loaded via Ajax in the calendar partial
                if (! request()->graphic) {
                    $events = collect();
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
            $followerSortBy = request()->get('sort_by', 'pivot_created_at');
            $followerSortDir = strtolower(request()->get('sort_dir', 'desc')) === 'asc' ? 'asc' : 'desc';
            $allowedFollowerSortColumns = ['name', 'pivot_created_at'];
            if (! in_array($followerSortBy, $allowedFollowerSortColumns)) {
                $followerSortBy = 'pivot_created_at';
            }

            $followersWithRoles = $role->followers()
                ->with(['roles' => function ($query) {
                    $query->wherePivotIn('level', ['owner', 'admin'])
                        ->where('is_deleted', false)
                        ->orderBy('role_user.created_at', 'asc')
                        ->limit(1);
                }])
                ->orderBy($followerSortBy, $followerSortDir)
                ->paginate(10)
                ->withQueryString();
        }

        if (isset($followerSortBy)) {
            $sortBy = $followerSortBy;
            $sortDir = $followerSortDir;
        } elseif ($tab == 'team') {
            $sortBy = $teamSortBy;
            $sortDir = $teamSortDir;
        } else {
            $sortBy = '';
            $sortDir = 'desc';
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
            'unscheduled',
            'datesUnavailable',
            'sortBy',
            'sortDir',
        ));
    }

    public function createMember(Request $request, $subdomain)
    {
        $role = Role::subdomain($subdomain)->firstOrFail();

        $this->authorize('manageMembers', $role);

        if (! $role->isEnterprise()) {
            return redirect()->route('role.view_admin', ['subdomain' => $subdomain, 'tab' => 'team'])
                ->with('error', __('messages.upgrade_to_enterprise'));
        }

        if (config('app.hosted') && $role->members()->count() >= 5) {
            return redirect()->route('role.view_admin', ['subdomain' => $subdomain, 'tab' => 'team'])
                ->with('error', __('messages.team_member_limit'));
        }

        $data = [
            'role' => $role,
            'title' => __('messages.add_member'),
        ];

        return view('role/add-member', $data);
    }

    public function storeMember(MemberAddRequest $request, $subdomain)
    {
        $role = Role::subdomain($subdomain)->firstOrFail();

        $this->authorize('manageMembers', $role);

        if (! $role->isEnterprise()) {
            return redirect()->back()->with('error', __('messages.upgrade_to_enterprise'));
        } elseif (! $role->isClaimed()) {
            return redirect()->back()->with('error', __('messages.email_not_verified'));
        }

        if (config('app.hosted') && $role->members()->count() >= 5) {
            return redirect()->back()->with('error', __('messages.team_member_limit'));
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

        $level = in_array($request->input('level'), ['admin', 'viewer']) ? $request->input('level') : 'admin';

        if ($user->isFollowing($subdomain)) {
            $roleUser = RoleUser::where('user_id', $user->id)
                ->where('role_id', $role->id)
                ->first();

            $roleUser->level = $level;
            $roleUser->save();

        } else {
            $user->roles()->attach($role->id, ['level' => $level, 'created_at' => now()]);
        }

        Notification::send($user, new AddedMemberNotification($role, $user, $request->user()));

        AuditService::log(AuditService::SCHEDULE_MEMBER_ADD, auth()->id(), 'Role', $role->id, null, null, $user->email);

        return redirect(route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'team']))
            ->with('message', __('messages.member_added'));
    }

    public function removeMember(Request $request, $subdomain, $hash)
    {
        $userId = UrlUtils::decodeId($hash);
        $role = Role::subdomain($subdomain)->firstOrFail();

        // Allow members to remove themselves, but only the owner can remove others
        if ($userId == auth()->id()) {
            if (! auth()->user()->isMember($subdomain)) {
                return redirect()->back()->with('error', __('messages.not_authorized'));
            }
        } else {
            $this->authorize('manageMembers', $role);
        }

        if ($userId == $role->user_id) {
            return redirect()->back()->with('error', __('messages.cannot_remove_owner'));
        }

        $roleUser = RoleUser::where('user_id', $userId)
            ->where('role_id', $role->id)
            ->first();

        if (! $roleUser) {
            return redirect()->back()->with('error', __('messages.not_found'));
        }

        $roleUser->delete();

        AuditService::log(AuditService::SCHEDULE_MEMBER_REMOVE, auth()->id(), 'Role', $role->id, null, null, 'user_id:'.$userId);

        // If user removed themselves, redirect to home instead of team page
        if ($userId == auth()->id()) {
            return redirect(route('home'))
                ->with('message', __('messages.removed_member'));
        }

        return redirect(route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'team']))
            ->with('message', __('messages.removed_member'));
    }

    public function updateMemberLevel(Request $request, $subdomain, $hash)
    {
        $userId = UrlUtils::decodeId($hash);
        $role = Role::subdomain($subdomain)->firstOrFail();

        $this->authorize('manageMembers', $role);

        $request->validate([
            'level' => ['required', 'in:admin,viewer'],
        ]);

        if ($userId == $role->user_id) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $roleUser = RoleUser::where('user_id', $userId)
            ->where('role_id', $role->id)
            ->first();

        if (! $roleUser || $roleUser->level == 'owner') {
            return redirect()->back()->with('error', __('messages.not_found'));
        }

        $roleUser->level = $request->level;
        $roleUser->save();

        return redirect(route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'team']))
            ->with('message', __('messages.member_level_updated'));
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
        if (is_demo_mode()) {
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
        $role->use_24_hour_time = auth()->user()->use_24_hour_time;

        if (auth()->user()->phone && auth()->user()->hasVerifiedPhone()) {
            $role->phone = auth()->user()->phone;
        }

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
        if (is_demo_mode()) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $user = $request->user();

        $role = new Role;
        $role->fill($request->all());
        $role->subdomain = Role::generateSubdomain($request->name);
        $role->user_id = $user->id;

        if (! config('app.hosted')) {
            $role->email_verified_at = now();
            if ($role->phone) {
                $role->phone_verified_at = now();
            }
        } elseif ($role->email == $user->email) {
            $role->email_verified_at = $user->email_verified_at;
        }

        if (config('app.hosted') && $role->phone && $user->phone === $role->phone && $user->hasVerifiedPhone()) {
            $role->phone_verified_at = now();
        }

        if (! $request->background_colors) {
            $role->background_colors = $request->custom_color1.', '.$request->custom_color2;
        }

        if ($request->has('import_urls') || $request->has('import_cities')) {
            $importConfig = $role->import_config;
            $importUrls = array_map('strtolower', array_filter(array_map('trim', $request->input('import_urls', []))));
            foreach ($importUrls as $importUrl) {
                if (! UrlUtils::isUrlSafe($importUrl)) {
                    return redirect()->back()->withErrors(['import_urls' => __('messages.invalid_url')]);
                }
            }
            $importConfig['urls'] = $importUrls;
            $importConfig['cities'] = array_map('strtolower', array_filter(array_map('trim', $request->input('import_cities', []))));
            $role->import_config = $importConfig;
        }

        $role->save();

        AuditService::log(AuditService::SCHEDULE_CREATE, $user->id, 'Role', $role->id, null, null, $role->name);

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

        if (! $user->default_role_id) {
            $user->default_role_id = $role->id;
            $user->save();
        }

        if ($request->hasFile('profile_image')) {
            $file = $request->file('profile_image');

            // Validate file extension and MIME type
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $extension = strtolower($file->getClientOriginalExtension());
            if (! in_array($extension, $allowedExtensions) || ! in_array($file->getMimeType(), $allowedMimeTypes)) {
                return back()->withErrors(['profile_image' => 'Invalid file type. Allowed: jpg, jpeg, png, gif, webp']);
            }

            if ($role->profile_image_url) {
                $path = $role->getAttributes()['profile_image_url'];
                if (config('filesystems.default') == 'local') {
                    $path = 'public/'.$path;
                }
                Storage::delete($path);
            }

            $filename = strtolower('profile_'.Str::random(32).'.'.$extension);
            $path = $file->storeAs(config('filesystems.default') == 'local' ? '/public' : '/', $filename);

            $role->profile_image_url = $filename;

            $role->save();
        }

        if ($request->hasFile('header_image')) {
            $file = $request->file('header_image');

            // Validate file extension and MIME type
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $extension = strtolower($file->getClientOriginalExtension());
            if (! in_array($extension, $allowedExtensions) || ! in_array($file->getMimeType(), $allowedMimeTypes)) {
                return back()->withErrors(['header_image' => 'Invalid file type. Allowed: jpg, jpeg, png, gif, webp']);
            }

            if ($role->header_image_url) {
                $path = $role->getAttributes()['header_image_url'];
                if (config('filesystems.default') == 'local') {
                    $path = 'public/'.$path;
                }
                Storage::delete($path);
            }

            $filename = strtolower('header_'.Str::random(32).'.'.$extension);
            $path = $file->storeAs(config('filesystems.default') == 'local' ? '/public' : '/', $filename);

            $role->header_image_url = $filename;

            $role->save();
        }

        if ($role->background == 'image' && $request->background_image) {
            $role->background_image = $request->background_image;
            $role->save();
        } elseif ($role->background == 'image' && $request->hasFile('background_image_url')) {
            $file = $request->file('background_image_url');

            // Validate file extension and MIME type
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $extension = strtolower($file->getClientOriginalExtension());
            if (! in_array($extension, $allowedExtensions) || ! in_array($file->getMimeType(), $allowedMimeTypes)) {
                return back()->withErrors(['background_image_url' => 'Invalid file type. Allowed: jpg, jpeg, png, gif, webp']);
            }

            if ($role->background_image_url) {
                $path = $role->getAttributes()['background_image_url'];
                if (config('filesystems.default') == 'local') {
                    $path = 'public/'.$path;
                }
                Storage::delete($path);
            }

            $filename = strtolower('background_'.Str::random(32).'.'.$extension);
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
        if (! auth()->user()->isEditor($subdomain)) {
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

        $approvedSubdomainNames = [];
        if ($role->approved_subdomains) {
            $approvedSubdomainNames = Role::whereIn('subdomain', $role->approved_subdomains)
                ->pluck('name', 'subdomain')
                ->toArray();
        }

        $availableCurators = collect();
        if (! $role->isCurator()) {
            $availableCurators = auth()->user()->allCurators();
        }

        $pivot = $role->users()->where('user_id', auth()->id())->first()?->pivot;
        $notificationSettings = array_merge(
            ['new_sale' => false, 'new_request' => false, 'new_fan_content' => false, 'new_feedback' => false, 'new_poll_option' => false],
            json_decode($pivot?->notification_settings ?? '{}', true)
        );

        $data = [
            'user' => auth()->user(),
            'role' => $role,
            'title' => __('messages.edit_schedule'),
            'gradients' => $gradientOptions,
            'backgrounds' => $backgroundOptions,
            'headers' => $headerOptions,
            'fonts' => $fonts,
            'approvedSubdomainNames' => $approvedSubdomainNames,
            'availableCurators' => $availableCurators,
            'notificationSettings' => $notificationSettings,
        ];

        return view('role/edit', $data);
    }

    public function update(RoleUpdateRequest $request, $subdomain): RedirectResponse
    {
        if (! auth()->user()->isEditor($subdomain)) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $role = Role::subdomain($subdomain)->firstOrFail();

        // Demo mode restrictions - prevent changes to sensitive settings
        if (is_demo_mode()) {
            // Remove sensitive fields from the request
            $request->merge([
                'language_code' => $role->language_code,
                'timezone' => $role->timezone,
                'new_subdomain' => $role->subdomain,
                'custom_domain' => $role->custom_domain,
                'custom_css' => $role->custom_css,
                'google_calendar_id' => $role->google_calendar_id,
                'sync_direction' => $role->sync_direction,
                'caldav_sync_direction' => $role->caldav_sync_direction,
                'email_settings' => null,
            ]);
            // Remove image uploads
            $request->files->remove('profile_image');
            $request->files->remove('header_image');
            $request->files->remove('new_sponsor_logos');
        }

        // Guard custom_css behind Pro plan
        if (! $role->isPro()) {
            $request->merge(['custom_css' => $role->custom_css]);
        }

        // Guard sponsor logos behind Pro plan
        if (! $role->isPro()) {
            $request->merge([
                'sponsor_logos' => $role->sponsor_logos,
            ]);
            $request->files->remove('new_sponsor_logos');
        }

        // Guard carpool_enabled behind Pro plan
        if (! $role->isPro()) {
            $request->merge(['carpool_enabled' => $role->carpool_enabled]);
        }

        // Guard custom_domain behind Enterprise plan
        if (! $role->isEnterprise()) {
            $request->merge([
                'custom_domain' => $role->custom_domain,
                'custom_domain_mode' => $role->custom_domain_mode,
            ]);
        }

        // Track custom domain changes for DO API
        $oldCustomDomain = $role->custom_domain;
        $oldCustomDomainMode = $role->custom_domain_mode;
        $oldCustomDomainHost = $role->custom_domain_host;
        $oldCustomDomainStatus = $role->custom_domain_status;

        $existingSettings = $role->getEmailSettings();

        // Handle sync_direction and calendar changes and webhook management
        $oldSyncDirection = $role->sync_direction;
        $newSyncDirection = $request->input('sync_direction');
        $oldCalendarId = $role->google_calendar_id;
        $newCalendarId = $request->input('google_calendar_id');

        $role->fill($request->all());

        if ($request->has('youtube_links')) {
            $role->youtube_links = $request->input('youtube_links') ?: null;
        }
        if ($request->has('social_links')) {
            $role->social_links = $request->input('social_links') ?: null;
        }
        if ($request->has('payment_links')) {
            $role->payment_links = $request->input('payment_links') ?: null;
        }

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
            $importConfig = $role->import_config;
            $importUrls = array_map('strtolower', array_filter(array_map('trim', $request->input('import_urls', []))));
            foreach ($importUrls as $importUrl) {
                if (! UrlUtils::isUrlSafe($importUrl)) {
                    return redirect()->back()->withErrors(['import_urls' => __('messages.invalid_url')]);
                }
            }
            $importConfig['urls'] = $importUrls;
            $importConfig['cities'] = array_map('strtolower', array_filter(array_map('trim', $request->input('import_cities', []))));
            $role->import_config = $importConfig;
        }

        if ($request->has('import_fields')) {
            $importConfig = $role->import_config;
            $importConfig['fields'] = [
                'short_description' => (bool) $request->input('import_fields.short_description'),
                'description' => (bool) $request->input('import_fields.description'),
                'ticket_price' => (bool) $request->input('import_fields.ticket_price'),
                'coupon_code' => (bool) $request->input('import_fields.coupon_code'),
                'registration_url' => (bool) $request->input('import_fields.registration_url'),
                'category_id' => (bool) $request->input('import_fields.category_id'),
                'group_id' => (bool) $request->input('import_fields.group_id'),
            ];
            $importConfig['required_fields'] = [
                'short_description' => (bool) $request->input('required_fields.short_description'),
                'description' => (bool) $request->input('required_fields.description'),
                'ticket_price' => (bool) $request->input('required_fields.ticket_price'),
                'coupon_code' => (bool) $request->input('required_fields.coupon_code'),
                'registration_url' => (bool) $request->input('required_fields.registration_url'),
                'category_id' => (bool) $request->input('required_fields.category_id'),
                'group_id' => (bool) $request->input('required_fields.group_id'),
            ];
            $role->import_config = $importConfig;
        }

        // Handle event custom fields (Pro feature)
        if ($request->has('event_custom_fields_submitted') && $role->isPro()) {
            $submittedFields = $request->input('event_custom_fields', []);
            $existingCustomFields = $role->event_custom_fields ?? [];
            $eventCustomFields = [];
            $fieldsNeedingTranslation = [];

            // Collect indices already used by existing (persisted) fields
            $usedIndices = [];
            foreach ($submittedFields as $fieldKey => $fieldData) {
                if (! empty($fieldData['name'])) {
                    if (isset($existingCustomFields[$fieldKey]['index'])) {
                        $usedIndices[] = $existingCustomFields[$fieldKey]['index'];
                    }
                }
            }

            foreach ($submittedFields as $fieldKey => $fieldData) {
                // Skip fields without a name
                if (empty($fieldData['name'])) {
                    continue;
                }

                // Preserve existing index or assign next available (1-10)
                $fieldIndex = $existingCustomFields[$fieldKey]['index'] ?? null;
                if (! $fieldIndex) {
                    // Try client-submitted index
                    if (! empty($fieldData['index'])) {
                        $submittedIndex = (int) $fieldData['index'];
                        if ($submittedIndex >= 1 && $submittedIndex <= 10 && ! in_array($submittedIndex, $usedIndices)) {
                            $fieldIndex = $submittedIndex;
                            $usedIndices[] = $submittedIndex;
                        }
                    }
                    // Fall back to next available
                    if (! $fieldIndex) {
                        for ($i = 1; $i <= 10; $i++) {
                            if (! in_array($i, $usedIndices)) {
                                $fieldIndex = $i;
                                $usedIndices[] = $i;
                                break;
                            }
                        }
                    }
                }

                $eventCustomFields[$fieldKey] = [
                    'name' => $fieldData['name'],
                    'type' => $fieldData['type'] ?? 'string',
                    'required' => ! empty($fieldData['required']),
                    'options' => implode(',', array_map('trim', explode(',', $fieldData['options'] ?? ''))),
                    'ai_prompt' => $fieldData['ai_prompt'] ?? '',
                    'index' => $fieldIndex,
                ];

                // Preserve options_en if dropdown options haven't changed
                $existingField = $existingCustomFields[$fieldKey] ?? null;
                if ($existingField && ! empty($existingField['options_en'])
                    && implode(',', array_map('trim', explode(',', $existingField['options'] ?? ''))) === $eventCustomFields[$fieldKey]['options']) {
                    $eventCustomFields[$fieldKey]['options_en'] = $existingField['options_en'];
                }

                // Handle name_en - preserve manually entered value or check if translation needed
                if (! empty($fieldData['name_en'])) {
                    // User provided a manual English name
                    $eventCustomFields[$fieldKey]['name_en'] = $fieldData['name_en'];
                } elseif ($role->language_code !== 'en') {
                    // Check if name changed or name_en is missing
                    $existingField = $existingCustomFields[$fieldKey] ?? null;
                    $existingName = $existingField['name'] ?? null;
                    $existingNameEn = $existingField['name_en'] ?? null;

                    if ($existingNameEn && $existingName === $fieldData['name']) {
                        // Name unchanged, keep existing translation
                        $eventCustomFields[$fieldKey]['name_en'] = $existingNameEn;
                    } else {
                        // Name changed or no translation exists - mark for translation
                        $fieldsNeedingTranslation[$fieldKey] = $fieldData['name'];
                    }
                }
            }

            // Batch translate field names that need translation
            if (! empty($fieldsNeedingTranslation)) {
                $translations = [];
                try {
                    $translations = GeminiUtils::translateCustomFieldNames(
                        array_values($fieldsNeedingTranslation),
                        $role->language_code
                    );

                    foreach ($fieldsNeedingTranslation as $fieldKey => $fieldName) {
                        if (isset($translations[$fieldName])) {
                            $eventCustomFields[$fieldKey]['name_en'] = $translations[$fieldName];
                        }
                    }
                } catch (\Exception $e) {
                    \Log::error('Failed to translate custom field names: '.$e->getMessage());
                    // Continue without translations if API fails
                }

                // Warn user if translation failed
                if (empty($translations)) {
                    session()->flash('warning', __('messages.translation_failed_warning'));
                }
            }

            // De-duplicate indices as a safety net
            $usedIndices = [];
            foreach ($eventCustomFields as $fieldKey => $fieldData) {
                $index = $fieldData['index'] ?? null;
                if ($index && ! in_array($index, $usedIndices)) {
                    $usedIndices[] = $index;
                } elseif ($index) {
                    for ($i = 1; $i <= 10; $i++) {
                        if (! in_array($i, $usedIndices)) {
                            $eventCustomFields[$fieldKey]['index'] = $i;
                            $usedIndices[] = $i;
                            break;
                        }
                    }
                }
            }

            $role->event_custom_fields = ! empty($eventCustomFields) ? $eventCustomFields : null;
        }

        // Handle custom labels (Pro feature)
        if ($request->has('custom_labels_submitted') && $role->isPro()) {
            $submittedLabels = $request->input('custom_labels', []);
            $existingLabels = $role->custom_labels ?? [];
            $validKeys = array_keys(Role::getCustomizableLabels());
            $customLabels = [];

            foreach ($submittedLabels as $key => $labelData) {
                if (! in_array($key, $validKeys) || empty($labelData['value'])) {
                    continue;
                }

                $customLabels[$key] = ['value' => $labelData['value']];

                if (! empty($labelData['value_en'])) {
                    $customLabels[$key]['value_en'] = $labelData['value_en'];
                } elseif ($role->language_code !== 'en') {
                    // Preserve existing translation if value unchanged, otherwise null out for re-translation
                    $existingLabel = $existingLabels[$key] ?? null;
                    if ($existingLabel && ($existingLabel['value'] ?? null) === $labelData['value'] && ! empty($existingLabel['value_en'])) {
                        $customLabels[$key]['value_en'] = $existingLabel['value_en'];
                    }
                }
            }

            $role->custom_labels = ! empty($customLabels) ? $customLabels : null;
        }

        $approvedSubdomains = array_filter(array_map('trim', $request->input('approved_subdomains', [])));
        $role->approved_subdomains = ! empty($approvedSubdomains) ? array_values($approvedSubdomains) : null;

        // Handle default curator schedules
        if ($request->has('default_curator_ids') && ! $role->isCurator()) {
            $curatorIds = array_filter(array_map('intval', $request->input('default_curator_ids', [])));
            $validCuratorIds = [];
            $allowedCuratorIds = auth()->user()->allCurators()->pluck('id')->toArray();
            foreach ($curatorIds as $curatorId) {
                if (in_array($curatorId, $allowedCuratorIds)) {
                    $validCuratorIds[] = $curatorId;
                }
            }
            $role->default_curator_ids = ! empty($validCuratorIds) ? $validCuratorIds : null;
        }

        // Auto-verify phone for selfhost, or if it matches the user's verified phone
        if (! config('app.hosted') && $role->phone && $role->isDirty('phone')) {
            $role->phone_verified_at = now();
        }
        $user = auth()->user();
        if (config('app.hosted') && $role->phone && $user->phone === $role->phone && $user->hasVerifiedPhone()) {
            $role->phone_verified_at = now();
        }

        // Handle email settings
        if ($request->filled('email_settings')) {
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

        // Save notification preferences for the current user
        $notificationSettings = [
            'new_sale' => (bool) $request->input('notification_new_sale'),
            'new_request' => (bool) $request->input('notification_new_request'),
            'new_fan_content' => (bool) $request->input('notification_new_fan_content'),
            'new_feedback' => (bool) $request->input('notification_new_feedback'),
            'new_poll_option' => (bool) $request->input('notification_new_poll_option'),
        ];
        $role->users()->updateExistingPivot(auth()->id(), [
            'notification_settings' => json_encode($notificationSettings),
        ]);

        // Handle DigitalOcean custom domain provisioning
        $newCustomDomainHost = $role->custom_domain_host;
        $newCustomDomainMode = $role->custom_domain_mode;
        $doService = app(DigitalOceanService::class);

        if ($doService->isConfigured()) {
            try {
                // Remove old domain from DO if it was direct mode and domain/mode changed
                if ($oldCustomDomainMode === 'direct' && $oldCustomDomainHost &&
                    ($oldCustomDomainHost !== $newCustomDomainHost || $newCustomDomainMode !== 'direct')) {
                    $doService->removeDomain($oldCustomDomainHost);
                    \Illuminate\Support\Facades\Cache::forget("custom_domain:{$oldCustomDomainHost}");
                }

                // Add new domain to DO if direct mode
                if ($newCustomDomainMode === 'direct' && $newCustomDomainHost &&
                    ($oldCustomDomainHost !== $newCustomDomainHost || $oldCustomDomainMode !== 'direct'
                     || $oldCustomDomainStatus === 'failed')) {
                    // Clear any stale cache for the new domain (e.g., previously assigned to a different schedule)
                    \Illuminate\Support\Facades\Cache::forget("custom_domain:{$newCustomDomainHost}");
                    $success = $doService->addDomain($newCustomDomainHost);
                    $role->update(['custom_domain_status' => $success ? 'pending' : 'failed']);
                    \Illuminate\Support\Facades\Cache::forget("custom_domain:{$newCustomDomainHost}");
                }

                // Clear status and cache if switching away from direct mode
                if ($newCustomDomainMode !== 'direct' && $oldCustomDomainMode === 'direct') {
                    $role->update(['custom_domain_status' => null]);
                    if ($oldCustomDomainHost) {
                        \Illuminate\Support\Facades\Cache::forget("custom_domain:{$oldCustomDomainHost}");
                    }
                }
            } catch (\Exception $e) {
                \Log::error('Failed to manage custom domain in DO', [
                    'role_id' => $role->id,
                    'error' => $e->getMessage(),
                ]);
                $role->update(['custom_domain_status' => 'failed']);
                if ($newCustomDomainHost) {
                    \Illuminate\Support\Facades\Cache::forget("custom_domain:{$newCustomDomainHost}");
                }
            }
        }

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
                        'color' => $groupData['color'] ?? null,
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
                        'color' => $groupData['color'] ?? null,
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
            $file = $request->file('profile_image');

            // Validate file extension and MIME type
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $extension = strtolower($file->getClientOriginalExtension());
            if (! in_array($extension, $allowedExtensions) || ! in_array($file->getMimeType(), $allowedMimeTypes)) {
                return back()->withErrors(['profile_image' => 'Invalid file type. Allowed: jpg, jpeg, png, gif, webp']);
            }

            if ($role->profile_image_url) {
                $path = $role->getAttributes()['profile_image_url'];
                if (config('filesystems.default') == 'local') {
                    $path = 'public/'.$path;
                }
                Storage::delete($path);
            }

            $filename = strtolower('profile_'.Str::random(32).'.'.$extension);
            $path = $file->storeAs(config('filesystems.default') == 'local' ? '/public' : '/', $filename);

            $role->profile_image_url = $filename;

            $role->save();
        }

        if ($request->hasFile('header_image_url')) {
            $file = $request->file('header_image_url');

            // Validate file extension and MIME type
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $extension = strtolower($file->getClientOriginalExtension());
            if (! in_array($extension, $allowedExtensions) || ! in_array($file->getMimeType(), $allowedMimeTypes)) {
                return back()->withErrors(['header_image_url' => 'Invalid file type. Allowed: jpg, jpeg, png, gif, webp']);
            }

            if ($role->header_image_url) {
                $path = $role->getAttributes()['header_image_url'];
                if (config('filesystems.default') == 'local') {
                    $path = 'public/'.$path;
                }
                Storage::delete($path);
            }

            $filename = strtolower('header_'.Str::random(32).'.'.$extension);
            $path = $file->storeAs(config('filesystems.default') == 'local' ? '/public' : '/', $filename);

            $role->header_image_url = $filename;

            $role->save();
        }

        if ($role->background == 'image' && $request->background_image) {
            $role->background_image = $request->background_image;
            $role->save();
        } elseif ($role->background == 'image' && $request->hasFile('background_image_url')) {
            $file = $request->file('background_image_url');

            // Validate file extension and MIME type
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $extension = strtolower($file->getClientOriginalExtension());
            if (! in_array($extension, $allowedExtensions) || ! in_array($file->getMimeType(), $allowedMimeTypes)) {
                return back()->withErrors(['background_image_url' => 'Invalid file type. Allowed: jpg, jpeg, png, gif, webp']);
            }

            if ($role->background_image_url) {
                $path = $role->getAttributes()['background_image_url'];
                if (config('filesystems.default') == 'local') {
                    $path = 'public/'.$path;
                }
                Storage::delete($path);
            }

            $filename = strtolower('background_'.Str::random(32).'.'.$extension);
            $path = $file->storeAs(config('filesystems.default') == 'local' ? '/public' : '/', $filename);

            $role->background_image_url = $filename;

            $role->save();
        }

        // Handle AI-generated images (already saved to storage by generateStyle endpoint)
        if ($request->input('ai_profile_image') && ! $request->hasFile('profile_image')) {
            if ($role->profile_image_url) {
                $path = $role->getAttributes()['profile_image_url'];
                if (config('filesystems.default') == 'local') {
                    $path = 'public/'.$path;
                }
                Storage::delete($path);
            }
            $role->profile_image_url = $request->input('ai_profile_image');

            $role->save();
        }

        if ($request->input('ai_header_image') && ! $request->hasFile('header_image_url')) {
            if ($role->header_image_url) {
                $path = $role->getAttributes()['header_image_url'];
                if (config('filesystems.default') == 'local') {
                    $path = 'public/'.$path;
                }
                Storage::delete($path);
            }
            $role->header_image_url = $request->input('ai_header_image');

            $role->save();
        }

        if ($request->input('ai_background_image') && ! $request->hasFile('background_image_url')) {
            if ($role->background_image_url) {
                $path = $role->getAttributes()['background_image_url'];
                if (config('filesystems.default') == 'local') {
                    $path = 'public/'.$path;
                }
                Storage::delete($path);
            }
            $role->background_image_url = $request->input('ai_background_image');
            $role->background = 'image';
            $role->background_image = null;

            $role->save();
        }

        // Handle sponsor logos (Pro feature)
        if ($role->isPro() && ! is_demo_mode()) {
            $oldSponsors = json_decode($role->getAttributes()['sponsor_logos'] ?? '[]', true) ?: [];
            $oldLogoFiles = array_filter(array_column($oldSponsors, 'logo'));

            // Process existing sponsors (reordered via drag-and-drop)
            $existingSponsorsJson = $request->input('existing_sponsors', '[]');
            $sponsors = json_decode($existingSponsorsJson, true) ?: [];

            // Process new sponsor uploads
            $newFiles = $request->file('new_sponsor_logos', []);
            $newNames = $request->input('new_sponsor_names', []);
            $newUrls = $request->input('new_sponsor_urls', []);
            $newTiers = $request->input('new_sponsor_tiers', []);

            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
            $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'];

            foreach ($newFiles as $index => $file) {
                if (count($sponsors) >= 12) {
                    break;
                }

                $extension = strtolower($file->getClientOriginalExtension());
                if (! in_array($extension, $allowedExtensions) || ! in_array($file->getMimeType(), $allowedMimeTypes)) {
                    continue;
                }

                $filename = strtolower('sponsor_'.Str::random(32).'.'.$extension);
                $file->storeAs(config('filesystems.default') == 'local' ? '/public' : '/', $filename);

                $sponsors[] = [
                    'name' => $newNames[$index] ?? '',
                    'logo' => $filename,
                    'url' => ! empty($newUrls[$index]) ? $newUrls[$index] : null,
                    'tier' => $newTiers[$index] ?? '',
                ];
            }

            // Cap at 12
            $sponsors = array_slice($sponsors, 0, 12);

            // Delete orphaned logo files
            $currentLogoFiles = array_filter(array_column($sponsors, 'logo'));
            $orphanedFiles = array_diff($oldLogoFiles, $currentLogoFiles);
            foreach ($orphanedFiles as $orphanedFile) {
                $path = $orphanedFile;
                if (config('filesystems.default') == 'local') {
                    $path = 'public/'.$path;
                }
                Storage::delete($path);
            }

            $role->sponsor_logos = ! empty($sponsors) ? json_encode(array_values($sponsors)) : null;
            $role->save();
        }

        AuditService::log(AuditService::SCHEDULE_UPDATE, auth()->id(), 'Role', $role->id, null, null, $role->name);

        return redirect(route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'schedule']))
            ->with('message', __('messages.updated_schedule'));
    }

    public function generateStyle(Request $request, $subdomain)
    {
        set_time_limit(300);

        if (! auth()->user()->isEditor($subdomain)) {
            return response()->json(['error' => __('messages.not_authorized')], 403);
        }

        $role = Role::subdomain($subdomain)->firstOrFail();

        if (! $role->isEnterprise()) {
            return response()->json(['error' => __('messages.not_authorized')], 403);
        }

        if (is_demo_mode()) {
            return response()->json(['error' => __('messages.not_authorized')], 403);
        }

        $imageElements = array_intersect($request->input('elements', []), ['profile_image', 'header_image', 'background_image']);
        if (! empty($imageElements) && ! config('services.openai.api_key') && ! config('services.google.gemini_key')) {
            return response()->json(['error' => __('messages.openai_key_required')], 422);
        }

        $request->validate([
            'style_instructions' => 'nullable|string|max:500',
            'elements' => 'required|array|min:1',
            'elements.*' => 'in:profile_image,header_image,accent_color,font,background_image',
            'custom_prompt' => 'nullable|string|max:5000',
            'save_instructions' => 'nullable|boolean',
        ]);

        $currentValues = [
            'accent_color' => $request->input('accent_color'),
            'font_family' => $request->input('font_family'),
        ];

        try {
            $results = GeminiUtils::generateScheduleStyle(
                $role,
                $request->input('elements'),
                $request->input('style_instructions'),
                $currentValues,
                $request->input('custom_prompt')
            );

            if (empty($results) || (isset($results['text_error']) && isset($results['image_error']) && count($results) === 2)) {
                return response()->json(['error' => __('messages.ai_style_generation_failed')], 500);
            }

            UsageTrackingService::track(UsageTrackingService::GEMINI_GENERATE_STYLE, $role->id);

            if ($request->input('save_instructions')) {
                $role->update(['ai_style_instructions' => $request->input('style_instructions', '')]);
            }

            // Build full URLs for generated images
            $response = ['success' => true];

            foreach (['profile_image', 'header_image', 'background_image'] as $imageField) {
                if (isset($results[$imageField])) {
                    $filename = $results[$imageField];
                    if (config('app.hosted') && config('filesystems.default') == 'do_spaces') {
                        $response[$imageField.'_url'] = 'https://eventschedule.nyc3.cdn.digitaloceanspaces.com/'.$filename;
                    } elseif (config('filesystems.default') == 'local') {
                        $response[$imageField.'_url'] = url('/storage/'.$filename);
                    } else {
                        $response[$imageField.'_url'] = $filename;
                    }
                    $response[$imageField.'_filename'] = $filename;
                }
            }

            if (isset($results['accent_color'])) {
                $response['accent_color'] = $results['accent_color'];
            }
            if (isset($results['font_family'])) {
                $response['font_family'] = $results['font_family'];
            }
            if (isset($results['image_error'])) {
                $response['image_error'] = true;
            }
            if (isset($results['text_error'])) {
                $response['text_error'] = true;
            }

            return response()->json($response);
        } catch (\Illuminate\Database\QueryException $e) {
            report($e);

            return response()->json(['error' => __('messages.ai_style_generation_failed')], 500);
        } catch (\Exception $e) {
            \Log::error('AI style generation failed: '.$e->getMessage(), ['role_id' => $role->id]);

            return response()->json(['error' => __('messages.ai_style_generation_failed')], 500);
        }
    }

    public function getStylePrompt(Request $request, $subdomain)
    {
        if (! auth()->user()->isEditor($subdomain)) {
            return response()->json(['error' => __('messages.not_authorized')], 403);
        }

        $role = Role::subdomain($subdomain)->firstOrFail();

        if (! $role->isEnterprise()) {
            return response()->json(['error' => __('messages.not_authorized')], 403);
        }

        $request->validate([
            'elements' => 'present|array',
            'elements.*' => 'in:accent_color,font,profile_image,header_image,background_image',
        ]);

        $allElements = $request->input('elements');
        $textElements = array_values(array_filter($allElements, fn ($el) => ! in_array($el, ['profile_image', 'header_image', 'background_image'])));
        $imageElements = array_values(array_filter($allElements, fn ($el) => in_array($el, ['profile_image', 'header_image', 'background_image'])));

        $currentValues = [
            'accent_color' => $request->input('accent_color'),
            'font_family' => $request->input('font_family'),
        ];

        $prompt = GeminiUtils::buildScheduleStylePrompt(
            $role,
            $textElements,
            $request->input('style_instructions'),
            $currentValues
        );

        $imagePrompts = [];
        $accentColor = $request->input('accent_color', '#4E81FA');
        $styleInstructions = $request->input('style_instructions');

        foreach ($imageElements as $imageEl) {
            if ($imageEl === 'profile_image') {
                $imagePrompts['profile_image'] = GeminiUtils::buildProfileImagePrompt($role, $accentColor, $styleInstructions);
            } elseif ($imageEl === 'header_image') {
                $imagePrompts['header_image'] = GeminiUtils::buildHeaderImagePrompt($role, $accentColor, $styleInstructions);
            } elseif ($imageEl === 'background_image') {
                $imagePrompts['background_image'] = GeminiUtils::buildBackgroundImagePrompt($role, $accentColor, $styleInstructions);
            }
        }

        return response()->json(['success' => true, 'prompt' => $prompt, 'image_prompts' => $imagePrompts]);
    }

    public function getStylePromptNew(Request $request)
    {
        if (is_demo_mode()) {
            return response()->json(['error' => __('messages.not_authorized')], 403);
        }

        if (config('app.hosted') && ! auth()->user()->isAdmin()) {
            return response()->json(['error' => __('messages.not_authorized')], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:talent,venue,curator',
            'elements' => 'present|array',
            'elements.*' => 'in:accent_color,font,profile_image,header_image,background_image',
        ]);

        $tempRole = new Role;
        $tempRole->name = $request->input('name');
        $tempRole->type = $request->input('type');
        $tempRole->short_description = $request->input('short_description');

        $allElements = $request->input('elements');
        $textElements = array_values(array_filter($allElements, fn ($el) => ! in_array($el, ['profile_image', 'header_image', 'background_image'])));
        $imageElements = array_values(array_filter($allElements, fn ($el) => in_array($el, ['profile_image', 'header_image', 'background_image'])));

        $currentValues = [
            'accent_color' => $request->input('accent_color'),
            'font_family' => $request->input('font_family'),
        ];

        $prompt = GeminiUtils::buildScheduleStylePrompt(
            $tempRole,
            $textElements,
            $request->input('style_instructions'),
            $currentValues
        );

        $imagePrompts = [];
        $accentColor = $request->input('accent_color', '#4E81FA');
        $styleInstructions = $request->input('style_instructions');

        foreach ($imageElements as $imageEl) {
            if ($imageEl === 'profile_image') {
                $imagePrompts['profile_image'] = GeminiUtils::buildProfileImagePrompt($tempRole, $accentColor, $styleInstructions);
            } elseif ($imageEl === 'header_image') {
                $imagePrompts['header_image'] = GeminiUtils::buildHeaderImagePrompt($tempRole, $accentColor, $styleInstructions);
            } elseif ($imageEl === 'background_image') {
                $imagePrompts['background_image'] = GeminiUtils::buildBackgroundImagePrompt($tempRole, $accentColor, $styleInstructions);
            }
        }

        return response()->json(['success' => true, 'prompt' => $prompt, 'image_prompts' => $imagePrompts]);
    }

    public function getScheduleDetailsPrompt(Request $request, $subdomain)
    {
        if (! auth()->user()->isEditor($subdomain)) {
            return response()->json(['error' => __('messages.not_authorized')], 403);
        }

        $role = Role::subdomain($subdomain)->firstOrFail();

        if (! $role->isEnterprise()) {
            return response()->json(['error' => __('messages.not_authorized')], 403);
        }

        $request->validate([
            'elements' => 'required|array|min:1',
            'elements.*' => 'in:short_description,description',
            'name' => 'required|string',
        ]);

        $prompt = GeminiUtils::buildScheduleDetailsPrompt(
            $request->input('name'),
            $request->input('type') ?: $role->type,
            $request->input('short_description'),
            $request->input('elements'),
            $request->input('description')
        );

        return response()->json(['success' => true, 'prompt' => $prompt]);
    }

    public function getScheduleDetailsPromptNew(Request $request)
    {
        if (is_demo_mode()) {
            return response()->json(['error' => __('messages.not_authorized')], 403);
        }

        if (config('app.hosted') && ! auth()->user()->isAdmin()) {
            return response()->json(['error' => __('messages.not_authorized')], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'elements' => 'required|array|min:1',
            'elements.*' => 'in:short_description,description',
        ]);

        $prompt = GeminiUtils::buildScheduleDetailsPrompt(
            $request->input('name'),
            $request->input('type', 'talent'),
            $request->input('short_description'),
            $request->input('elements'),
            $request->input('description')
        );

        return response()->json(['success' => true, 'prompt' => $prompt]);
    }

    public function generateStyleImage(Request $request, $subdomain)
    {
        set_time_limit(300);

        if (! auth()->user()->isEditor($subdomain)) {
            return response()->json(['error' => __('messages.not_authorized')], 403);
        }

        $role = Role::subdomain($subdomain)->firstOrFail();

        if (! $role->isEnterprise()) {
            return response()->json(['error' => __('messages.not_authorized')], 403);
        }

        if (is_demo_mode()) {
            return response()->json(['error' => __('messages.not_authorized')], 403);
        }

        $request->validate([
            'image_type' => 'required|in:profile_image,header_image,background_image',
            'accent_color' => 'required|string|max:7',
            'style_instructions' => 'nullable|string|max:500',
            'custom_prompt' => 'nullable|string|max:5000',
        ]);

        $imageType = $request->input('image_type');
        $accentColor = $request->input('accent_color');
        $styleInstructions = $request->input('style_instructions');
        $customPrompt = $request->input('custom_prompt');

        if (! config('services.openai.api_key') && ! config('services.google.gemini_key')) {
            return response()->json(['error' => __('messages.openai_key_required')], 422);
        }

        try {
            if ($customPrompt) {
                $aspectRatio = $imageType === 'profile_image' ? '1:1' : '16:9';
                $imageData = OpenAIUtils::sendImageGenerationRequest($customPrompt, $aspectRatio);
            } elseif ($imageType === 'profile_image') {
                $imageData = GeminiUtils::generateScheduleProfileImage($role, $accentColor, $styleInstructions);
            } elseif ($imageType === 'header_image') {
                $imageData = GeminiUtils::generateScheduleHeaderImage($role, $accentColor, $styleInstructions);
            } else {
                $imageData = GeminiUtils::generateScheduleBackgroundImage($role, $accentColor, $styleInstructions);
            }

            if (! $imageData) {
                return response()->json(['error' => __('messages.ai_style_generation_failed')], 500);
            }

            $prefix = str_replace('_image', '_', $imageType);
            $filename = ImageUtils::saveImageData($imageData, 'generated_style.png', $prefix);

            $response = ['success' => true];
            if (config('app.hosted') && config('filesystems.default') == 'do_spaces') {
                $response[$imageType.'_url'] = 'https://eventschedule.nyc3.cdn.digitaloceanspaces.com/'.$filename;
            } elseif (config('filesystems.default') == 'local') {
                $response[$imageType.'_url'] = url('/storage/'.$filename);
            } else {
                $response[$imageType.'_url'] = $filename;
            }
            $response[$imageType.'_filename'] = $filename;

            return response()->json($response);
        } catch (\App\Exceptions\ContentModerationException $e) {
            return response()->json(['error' => __('messages.ai_content_moderation_blocked')], 422);
        } catch (\Illuminate\Database\QueryException $e) {
            report($e);

            return response()->json(['error' => __('messages.ai_style_generation_failed')], 500);
        } catch (\Exception $e) {
            \Log::error('AI style image generation failed: '.$e->getMessage(), ['role_id' => $role->id]);

            return response()->json(['error' => __('messages.ai_style_generation_failed')], 500);
        }
    }

    public function generateStyleImageNew(Request $request)
    {
        set_time_limit(300);

        if (is_demo_mode()) {
            return response()->json(['error' => __('messages.not_authorized')], 403);
        }

        if (config('app.hosted') && ! auth()->user()->isAdmin()) {
            return response()->json(['error' => __('messages.not_authorized')], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:talent,venue,curator',
            'image_type' => 'required|in:profile_image,header_image,background_image',
            'accent_color' => 'required|string|max:7',
            'style_instructions' => 'nullable|string|max:500',
            'custom_prompt' => 'nullable|string|max:5000',
        ]);

        $tempRole = new Role;
        $tempRole->name = $request->input('name');
        $tempRole->type = $request->input('type');
        $tempRole->short_description = $request->input('short_description');

        $imageType = $request->input('image_type');
        $accentColor = $request->input('accent_color');
        $styleInstructions = $request->input('style_instructions');
        $customPrompt = $request->input('custom_prompt');

        if (! config('services.openai.api_key') && ! config('services.google.gemini_key')) {
            return response()->json(['error' => __('messages.openai_key_required')], 422);
        }

        try {
            if ($customPrompt) {
                $aspectRatio = $imageType === 'profile_image' ? '1:1' : '16:9';
                $imageData = OpenAIUtils::sendImageGenerationRequest($customPrompt, $aspectRatio);
            } elseif ($imageType === 'profile_image') {
                $imageData = GeminiUtils::generateScheduleProfileImage($tempRole, $accentColor, $styleInstructions);
            } elseif ($imageType === 'header_image') {
                $imageData = GeminiUtils::generateScheduleHeaderImage($tempRole, $accentColor, $styleInstructions);
            } else {
                $imageData = GeminiUtils::generateScheduleBackgroundImage($tempRole, $accentColor, $styleInstructions);
            }

            if (! $imageData) {
                return response()->json(['error' => __('messages.ai_style_generation_failed')], 500);
            }

            $prefix = str_replace('_image', '_', $imageType);
            $filename = ImageUtils::saveImageData($imageData, 'generated_style.png', $prefix);

            $response = ['success' => true];
            if (config('app.hosted') && config('filesystems.default') == 'do_spaces') {
                $response[$imageType.'_url'] = 'https://eventschedule.nyc3.cdn.digitaloceanspaces.com/'.$filename;
            } elseif (config('filesystems.default') == 'local') {
                $response[$imageType.'_url'] = url('/storage/'.$filename);
            } else {
                $response[$imageType.'_url'] = $filename;
            }
            $response[$imageType.'_filename'] = $filename;

            return response()->json($response);
        } catch (\App\Exceptions\ContentModerationException $e) {
            return response()->json(['error' => __('messages.ai_content_moderation_blocked')], 422);
        } catch (\Illuminate\Database\QueryException $e) {
            report($e);

            return response()->json(['error' => __('messages.ai_style_generation_failed')], 500);
        } catch (\Exception $e) {
            \Log::error('AI style image generation failed: '.$e->getMessage());

            return response()->json(['error' => __('messages.ai_style_generation_failed')], 500);
        }
    }

    public function generateScheduleDetails(Request $request, $subdomain)
    {
        if (! auth()->user()->isEditor($subdomain)) {
            return response()->json(['error' => __('messages.not_authorized')], 403);
        }

        $role = Role::subdomain($subdomain)->firstOrFail();

        if (! $role->isEnterprise()) {
            return response()->json(['error' => __('messages.not_authorized')], 403);
        }

        if (is_demo_mode()) {
            return response()->json(['error' => __('messages.not_authorized')], 403);
        }

        $request->validate([
            'elements' => 'required|array|min:1',
            'elements.*' => 'in:short_description,description',
            'name' => 'required|string',
            'style_instructions' => 'nullable|string|max:500',
            'custom_prompt' => 'nullable|string|max:5000',
            'save_instructions' => 'nullable|boolean',
        ]);

        try {
            $results = GeminiUtils::generateScheduleDetails(
                $request->input('name'),
                $request->input('type') ?: $role->type,
                $request->input('short_description'),
                $request->input('elements'),
                $request->input('description'),
                $request->input('custom_prompt'),
                $request->input('style_instructions')
            );

            if (empty($results)) {
                return response()->json(['error' => __('messages.ai_details_generation_failed')], 500);
            }

            UsageTrackingService::track(UsageTrackingService::GEMINI_GENERATE_SCHEDULE_DETAILS, $role->id);

            if ($request->input('save_instructions')) {
                $role->update(['ai_content_instructions' => $request->input('style_instructions', '')]);
            }

            return response()->json(array_merge(['success' => true], $results));
        } catch (\Illuminate\Database\QueryException $e) {
            report($e);

            return response()->json(['error' => __('messages.ai_details_generation_failed')], 500);
        } catch (\Exception $e) {
            \Log::error('AI schedule details generation failed: '.$e->getMessage(), ['role_id' => $role->id]);

            return response()->json(['error' => __('messages.ai_details_generation_failed')], 500);
        }
    }

    public function generateStyleNew(Request $request)
    {
        set_time_limit(300);

        if (is_demo_mode()) {
            return response()->json(['error' => __('messages.not_authorized')], 403);
        }

        if (config('app.hosted') && ! auth()->user()->isAdmin()) {
            return response()->json(['error' => __('messages.not_authorized')], 403);
        }

        $imageElements = array_intersect($request->input('elements', []), ['profile_image', 'header_image', 'background_image']);
        if (! empty($imageElements) && ! config('services.openai.api_key') && ! config('services.google.gemini_key')) {
            return response()->json(['error' => __('messages.openai_key_required')], 422);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:talent,venue,curator',
            'style_instructions' => 'nullable|string|max:500',
            'elements' => 'required|array|min:1',
            'elements.*' => 'in:profile_image,header_image,accent_color,font,background_image',
            'custom_prompt' => 'nullable|string|max:5000',
        ]);

        $tempRole = new Role;
        $tempRole->name = $request->input('name');
        $tempRole->type = $request->input('type');
        $tempRole->short_description = $request->input('short_description');
        $tempRole->accent_color = $request->input('accent_color', '#007bff');

        $currentValues = [
            'accent_color' => $request->input('accent_color'),
            'font_family' => $request->input('font_family'),
        ];

        try {
            $results = GeminiUtils::generateScheduleStyle(
                $tempRole,
                $request->input('elements'),
                $request->input('style_instructions'),
                $currentValues,
                $request->input('custom_prompt')
            );

            if (empty($results) || (isset($results['text_error']) && isset($results['image_error']) && count($results) === 2)) {
                return response()->json(['error' => __('messages.ai_style_generation_failed')], 500);
            }

            $response = ['success' => true];

            foreach (['profile_image', 'header_image', 'background_image'] as $imageField) {
                if (isset($results[$imageField])) {
                    $filename = $results[$imageField];
                    if (config('app.hosted') && config('filesystems.default') == 'do_spaces') {
                        $response[$imageField.'_url'] = 'https://eventschedule.nyc3.cdn.digitaloceanspaces.com/'.$filename;
                    } elseif (config('filesystems.default') == 'local') {
                        $response[$imageField.'_url'] = url('/storage/'.$filename);
                    } else {
                        $response[$imageField.'_url'] = $filename;
                    }
                    $response[$imageField.'_filename'] = $filename;
                }
            }

            if (isset($results['accent_color'])) {
                $response['accent_color'] = $results['accent_color'];
            }
            if (isset($results['font_family'])) {
                $response['font_family'] = $results['font_family'];
            }
            if (isset($results['image_error'])) {
                $response['image_error'] = true;
            }
            if (isset($results['text_error'])) {
                $response['text_error'] = true;
            }

            return response()->json($response);
        } catch (\Illuminate\Database\QueryException $e) {
            report($e);

            return response()->json(['error' => __('messages.ai_style_generation_failed')], 500);
        } catch (\Exception $e) {
            \Log::error('AI style generation failed: '.$e->getMessage());

            return response()->json(['error' => __('messages.ai_style_generation_failed')], 500);
        }
    }

    public function generateScheduleDetailsNew(Request $request)
    {
        if (is_demo_mode()) {
            return response()->json(['error' => __('messages.not_authorized')], 403);
        }

        if (config('app.hosted') && ! auth()->user()->isAdmin()) {
            return response()->json(['error' => __('messages.not_authorized')], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'elements' => 'required|array|min:1',
            'elements.*' => 'in:short_description,description',
            'style_instructions' => 'nullable|string|max:500',
            'custom_prompt' => 'nullable|string|max:5000',
        ]);

        try {
            $results = GeminiUtils::generateScheduleDetails(
                $request->input('name'),
                $request->input('type', 'talent'),
                $request->input('short_description'),
                $request->input('elements'),
                $request->input('description'),
                $request->input('custom_prompt'),
                $request->input('style_instructions')
            );

            if (empty($results)) {
                return response()->json(['error' => __('messages.ai_details_generation_failed')], 500);
            }

            return response()->json(array_merge(['success' => true], $results));
        } catch (\Illuminate\Database\QueryException $e) {
            report($e);

            return response()->json(['error' => __('messages.ai_details_generation_failed')], 500);
        } catch (\Exception $e) {
            \Log::error('AI schedule details generation failed: '.$e->getMessage());

            return response()->json(['error' => __('messages.ai_details_generation_failed')], 500);
        }
    }

    public function previewLink(Request $request, $subdomain): JsonResponse
    {
        if (! auth()->user()->isEditor($subdomain)) {
            return response()->json(['error' => __('messages.not_authorized')], 403);
        }

        $request->validate(['url' => 'required|string|url|max:1000']);

        $urlInfo = UrlUtils::getUrlInfo($request->url);
        if ($urlInfo === null) {
            return response()->json(['error' => __('messages.invalid_link')], 422);
        }

        $urlInfo->brand = UrlUtils::getBrand($urlInfo->url);
        $urlInfo->clean_url = UrlUtils::clean($urlInfo->url);

        return response()->json($urlInfo);
    }

    public function qrCode($subdomain)
    {
        $role = Role::subdomain($subdomain)->firstOrFail();
        $url = $role->getGuestUrl(true) ?: $role->getGuestUrl();

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

        // Schedules using the booking form get the simplified booking request form
        if ($role->usesBookingForm()) {
            return redirect(app_url(route('event.booking_request', ['subdomain' => $role->subdomain], false)));
        }

        session(['pending_request' => $subdomain]);
        session(['pending_request_allow_guest' => ! $role->require_account]);
        session(['pending_request_form' => 'import']);

        if (! auth()->user()) {
            $lang = session()->has('translate') ? 'en' : $role->language_code;

            return redirect(app_url(route('sign_up', ['lang' => $lang], false)));
        }

        $user = auth()->user();

        // Prevent demo account from following other roles
        if (! DemoService::isDemoUser($user) && ! $user->isConnected($subdomain)) {
            $user->roles()->attach($role->id, ['level' => 'follower', 'created_at' => now()]);
        }

        // Requesting a venue/curator - need a talent schedule
        if ($user->talents()->count() == 0) {
            return redirect(app_url(route('new', ['type' => 'talent'], false)));
        }
        $redirectRole = $user->talents()->first();

        return redirect(app_url(route('event.create', ['subdomain' => $redirectRole->subdomain], false)));
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

        if (! $role->isEnterprise()) {
            return redirect(route('role.view_admin', ['subdomain' => $subdomain, 'tab' => 'schedule']));
        }

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
        if ($request->has('sig')) {
            // Link-based unsubscription (from email) - verify HMAC
            if (! UrlUtils::verifyEmailSignature($request->email, $request->sig)) {
                return redirect()->route('role.show_unsubscribe')->with('error', 'Invalid unsubscribe link.');
            }
            $email = base64_decode($request->email);
        } else {
            // Form-based unsubscription (manual entry, CSRF-protected)
            $request->validate(['email' => 'required|email']);
            $email = $request->email;
        }

        $roles = Role::where('email', $email)->get();

        foreach ($roles as $role) {
            $role->is_subscribed = false;
            $role->save();
        }

        return redirect()->route('role.show_unsubscribe', ['email' => base64_encode($email)]);
    }

    public function unsubscribeUser(Request $request)
    {
        if (! $request->has('email')) {
            return redirect()->route('role.show_unsubscribe')->with('error', 'Invalid unsubscribe link.');
        }

        // Verify HMAC signature to prevent unauthorized unsubscription
        if (! $request->has('sig') || ! UrlUtils::verifyEmailSignature($request->email, $request->sig)) {
            return redirect()->route('role.show_unsubscribe')->with('error', 'Invalid unsubscribe link.');
        }

        $email = base64_decode($request->email);

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
        if (! auth()->user()->isEditor($subdomain)) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $role = Role::subdomain($subdomain)->firstOrFail();
        $userId = UrlUtils::decodeId($hash);
        $user = User::findOrFail($userId);

        // Verify the user is actually a member of this schedule
        if (! $user->roles()->where('roles.id', $role->id)->exists()) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

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

    public function searchSubdomains(Request $request): JsonResponse
    {
        $q = trim($request->get('q', ''));
        $exclude = array_filter((array) $request->get('exclude', []));

        if (strlen($q) < 2) {
            return response()->json([]);
        }

        $roles = Role::where(function ($query) use ($q) {
            $query->where('subdomain', 'like', "{$q}%")
                ->orWhere('name', 'like', "%{$q}%");
        })
            ->when(! empty($exclude), fn ($query) => $query->whereNotIn('subdomain', $exclude))
            ->limit(10)
            ->get(['subdomain', 'name', 'city']);

        return response()->json($roles->map(fn ($role) => [
            'subdomain' => $role->subdomain,
            'name' => $role->name,
            'city' => $role->city,
        ]));
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

        // Filter private events for non-members
        $user = auth()->user();
        $isMemberOrAdmin = $user && ($user->isMember($subdomain) || $user->isAdmin());
        if (! $isMemberOrAdmin) {
            $unlockedEventIds = $this->getUnlockedEventIds();
            $events = $events->filter(fn ($e) => ! $e->is_private || in_array($e->id, $unlockedEventIds));
        }

        // Format events for frontend
        $eventsData = $events->map(function ($event) use ($subdomain, $role) {
            $groupId = $event->getGroupIdForSubdomain($role->subdomain);

            $data = [
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

            // Strip sensitive fields from password-protected events
            if ($event->isPasswordProtected()) {
                $data['description'] = null;
                $data['venue_name'] = null;
                $data['image_url'] = null;
            }

            return $data;
        });

        return response()->json($eventsData);
    }

    public function changePlan($subdomain, $plan_type)
    {
        if ($plan_type !== 'free') {
            abort(422);
        }

        if (is_demo_mode()) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $role = Role::subdomain($subdomain)->firstOrFail();

        if (auth()->user()->id != $role->user_id) {
            return redirect()->back()->with('error', __('messages.unauthorized'));
        }

        if ($role->hasActiveSubscription() || $role->onGracePeriod()) {
            return redirect()->back()->with('error', __('messages.cancel_subscription_first'));
        }

        $role->plan_type = $plan_type;
        $role->plan_expires = null;
        $role->save();

        return redirect()->back()->with('message', __('messages.plan_changed'));
    }

    public function testImport(Request $request, $subdomain)
    {
        if (! auth()->user()->isEditor($subdomain)) {
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

            // Validate URLs against SSRF
            foreach ($urls as $url) {
                if (! \App\Utils\UrlUtils::isUrlSafe($url)) {
                    return response()->json([
                        'success' => false,
                        'message' => __('messages.invalid_url'),
                    ]);
                }
            }

            if (empty($urls) && empty($cities)) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.please_enter_urls_or_cities'),
                ]);
            }

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

            // Strip file paths from output to avoid leaking server internals
            $outputText = preg_replace('#(/[a-zA-Z0-9._-]+){3,}#', '[path]', $outputText);

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
                'message' => __('messages.import_test_error'),
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

    public function saveVideo(RoleVideoSaveRequest $request, $subdomain)
    {
        if (! auth()->user()->isEditor($subdomain)) {
            return response()->json(['success' => false, 'message' => __('messages.not_authorized')], 403);
        }

        $role = Role::subdomain($subdomain)->firstOrFail();

        if (! $role->isCurator()) {
            return response()->json(['success' => false, 'message' => __('messages.not_authorized')], 403);
        }

        $talentRole = Role::find($request->role_id);

        if (! $talentRole || ! $talentRole->isTalent()) {
            return response()->json(['success' => false, 'message' => __('messages.talent_role_not_found')]);
        }

        // Verify this talent is in one of the curator's accepted events
        $sharedEvent = \DB::table('event_role as curator_er')
            ->join('event_role as talent_er', 'curator_er.event_id', '=', 'talent_er.event_id')
            ->where('curator_er.role_id', $role->id)
            ->where('curator_er.is_accepted', true)
            ->where('talent_er.role_id', $talentRole->id)
            ->exists();

        if (! $sharedEvent) {
            return response()->json(['success' => false, 'message' => __('messages.not_authorized')], 403);
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

    public function saveVideos(RoleVideosSaveRequest $request, $subdomain)
    {
        if (! auth()->user()->isEditor($subdomain)) {
            return response()->json(['success' => false, 'message' => __('messages.not_authorized')], 403);
        }

        $role = Role::subdomain($subdomain)->firstOrFail();

        if (! $role->isCurator()) {
            return response()->json(['success' => false, 'message' => __('messages.not_authorized')], 403);
        }

        $talentRole = Role::find($request->role_id);

        if (! $talentRole || ! $talentRole->isTalent()) {
            return response()->json(['success' => false, 'message' => __('messages.talent_role_not_found')]);
        }

        // Verify this talent is in one of the curator's accepted events
        $sharedEvent = \DB::table('event_role as curator_er')
            ->join('event_role as talent_er', 'curator_er.event_id', '=', 'talent_er.event_id')
            ->where('curator_er.role_id', $role->id)
            ->where('curator_er.is_accepted', true)
            ->where('talent_er.role_id', $talentRole->id)
            ->exists();

        if (! $sharedEvent) {
            return response()->json(['success' => false, 'message' => __('messages.not_authorized')], 403);
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

        return now()->diffInSeconds($expiresAt);
    }

    /**
     * Send test email to verify SMTP credentials
     */
    public function testEmail(Request $request, $subdomain): JsonResponse
    {
        if (! auth()->user()->isEditor($subdomain)) {
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
            // Log the full error server-side but return generic message to user
            \Log::error('Test email failed: '.$e->getMessage(), [
                'role_id' => $role->id,
                'email' => $email,
            ]);

            return response()->json([
                'error' => __('messages.failed_to_send_test_email'),
                'details' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Send a phone verification code for a role.
     */
    public function phoneSendCode(Request $request, $subdomain): JsonResponse
    {
        $role = Role::subdomain($subdomain)->firstOrFail();

        if (! auth()->user()->isEditor($subdomain)) {
            return response()->json(['success' => false, 'message' => __('messages.not_authorized')], 403);
        }

        $phone = $role->phone;
        if (! $phone) {
            return response()->json(['success' => false, 'message' => __('messages.phone_number_required')], 422);
        }

        if (! SmsService::isConfigured()) {
            return response()->json(['success' => false, 'message' => __('messages.failed_to_send_sms')], 500);
        }

        // Rate limiting: max 5 codes per hour per phone
        $attemptsKey = 'phone_verify_attempts_'.$phone;
        $attempts = \Illuminate\Support\Facades\Cache::get($attemptsKey, 0);

        if ($attempts >= 5) {
            return response()->json(['success' => false, 'message' => __('messages.code_rate_limit')], 429);
        }

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $sent = SmsService::sendSms($phone, __('messages.your_verification_code_is', ['code' => $code]));

        if (! $sent) {
            return response()->json(['success' => false, 'message' => __('messages.failed_to_send_sms')], 500);
        }

        \Illuminate\Support\Facades\Cache::put('role_phone_verify_code_'.$role->id, $code, now()->addMinutes(10));

        \Illuminate\Support\Facades\Cache::put($attemptsKey, $attempts + 1, now()->addHour());

        return response()->json(['success' => true, 'message' => __('messages.verification_code_sent_to_phone')]);
    }

    /**
     * Verify a phone code for a role.
     */
    public function phoneVerifyCode(Request $request, $subdomain): JsonResponse
    {
        $role = Role::subdomain($subdomain)->firstOrFail();

        if (! auth()->user()->isEditor($subdomain)) {
            return response()->json(['success' => false, 'message' => __('messages.not_authorized')], 403);
        }

        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $cacheKey = 'role_phone_verify_code_'.$role->id;
        $failedKey = 'role_phone_verify_failed_'.$role->id;

        $storedCode = \Illuminate\Support\Facades\Cache::get($cacheKey);

        if (! $storedCode || ! hash_equals($storedCode, $request->code)) {
            $failed = \Illuminate\Support\Facades\Cache::get($failedKey, 0) + 1;
            \Illuminate\Support\Facades\Cache::put($failedKey, $failed, now()->addMinutes(10));

            if ($failed >= 5) {
                \Illuminate\Support\Facades\Cache::forget($cacheKey);
                \Illuminate\Support\Facades\Cache::forget($failedKey);
            }

            return response()->json(['success' => false, 'message' => __('messages.phone_verification_code_invalid')], 422);
        }

        \Illuminate\Support\Facades\Cache::forget($cacheKey);
        \Illuminate\Support\Facades\Cache::forget($failedKey);

        $role->phone_verified_at = now();
        $role->save();

        return response()->json(['success' => true, 'message' => __('messages.phone_verified')]);
    }

    private function getUnlockedEventIds(): array
    {
        return collect(session()->all())
            ->filter(fn ($value, $key) => str_starts_with($key, 'event_password_') && $value === true)
            ->keys()
            ->map(fn ($key) => (int) str_replace('event_password_', '', $key))
            ->toArray();
    }
}
