<?php

namespace App\Http\Controllers;

use App\Http\Requests\MemberAddRequest;
use App\Http\Requests\RoleCreateRequest;
use App\Http\Requests\RoleEmailVerificationRequest;
use App\Http\Requests\RoleUpdateRequest;
use App\Http\Requests\RoleVideoSaveRequest;
use App\Http\Requests\RoleVideosSaveRequest;
use App\Jobs\SendQueuedSms;
use App\Mail\FeedbackRequest;
use App\Models\AnalyticsAppearancesDaily;
use App\Models\AnalyticsDaily;
use App\Models\AnalyticsReferrersDaily;
use App\Models\AnalyticsSocialClicksDaily;
use App\Models\AnalyticsUtmDaily;
use App\Models\AuditLog;
use App\Models\BoostCampaign;
use App\Models\DismissedTimezoneWarning;
use App\Models\DismissedVenueMergeSuggestion;
use App\Models\Event;
use App\Models\PageView;
use App\Models\Role;
use App\Models\RoleUser;
use App\Models\Sale;
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
use App\Services\OneSignalService;
use App\Services\SmsService;
use App\Services\UsageTrackingService;
use App\Utils\ColorUtils;
use App\Utils\DateUtils;
use App\Utils\GeminiUtils;
use App\Utils\ImageUtils;
use App\Utils\OpenAIUtils;
use App\Utils\PhoneUtils;
use App\Utils\QrCodeUtils;
use App\Utils\SlugPatternUtils;
use App\Utils\UrlUtils;
use Carbon\Carbon;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RoleController extends Controller
{
    use Traits\CalendarDataTrait;

    // Max events loaded for the guest list layout (event_layout = 'list'). The list only displays
    // up to 100 upcoming events client-side, so the nearest 200 upcoming rows are a safe superset
    // while keeping the query bounded (prevents hydrating the full event table on large schedules).
    private const LIST_EVENT_CAP = 200;

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

        // Clean up the Outlook / Microsoft Graph subscription before deleting role
        if ($role->microsoft_webhook_id) {
            try {
                $microsoftUser = $role->user;
                if ($microsoftUser && $microsoftUser->microsoft_token) {
                    app(\App\Services\MicrosoftCalendarService::class)
                        ->deleteSubscription($microsoftUser, $role->microsoft_webhook_id);
                }
            } catch (\Exception $e) {
                \Log::warning('Failed to clean up Outlook subscription during role deletion', [
                    'role_id' => $role->id,
                    'subscription_id' => $role->microsoft_webhook_id,
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

        OneSignalService::pushToUser($user, [
            'title_key' => 'messages.push_role_deleted_title',
            'body_key' => 'messages.push_role_deleted_body',
            'body_params' => ['name' => $role->name],
            'url' => route('home'),
            'options' => ['icon' => $role->profile_image_url],
        ], $role);

        return redirect(route('home'))
            ->with('message', __('messages.deleted_schedule'));
    }

    public function follow(Request $request, $subdomain)
    {
        $role = Role::subdomain($subdomain)->firstOrFail();

        if (! auth()->user()) {
            $lang = session()->has('translate') ? $role->translation_language_code : $role->language_code;

            return redirect_with_pending_action(
                app_url(route('sign_up', ['lang' => $lang], false)),
                [
                    'pending_follow' => $subdomain,
                    'pending_follow_consent_dismissed' => $request->boolean('follow_consent_dismissed'),
                ]
            );
        }

        $user = $request->user();

        // Prevent demo account from following other roles
        if (DemoService::isDemoUser($user)) {
            return redirect(app_url(route('following', [], false)))
                ->with('error', __('messages.demo_mode_restriction'));
        }

        if ($request->boolean('follow_consent_dismissed') && ! $user->follow_consent_dismissed) {
            $user->update(['follow_consent_dismissed' => true]);
        }

        if (! $user->isConnected($role->subdomain)) {
            $user->roles()->attach($role->id, ['level' => 'follower', 'created_at' => now()]);
        }

        session()->forget('pending_follow');

        if ($subdomain = session('pending_request')) {
            $pendingRole = Role::whereSubdomain($subdomain)->first();

            $pendingData = [
                'pending_request' => $subdomain,
                'pending_request_allow_guest' => session('pending_request_allow_guest', $pendingRole ? ! $pendingRole->require_account : false),
                'pending_request_form' => session('pending_request_form', 'import'),
            ];

            if ($pendingRole && $pendingRole->isTalent()) {
                // Requesting a talent - need a venue schedule
                if ($user->venues()->count() == 0) {
                    return redirect_with_pending_action(
                        app_url(route('new', ['type' => 'venue'], false)),
                        $pendingData
                    );
                }
                $redirectRole = $user->venues()->first();
            } else {
                // Requesting a venue/curator - need a talent schedule
                if ($user->talents()->count() == 0) {
                    return redirect_with_pending_action(
                        app_url(route('new', ['type' => 'talent'], false)),
                        $pendingData
                    );
                }
                $redirectRole = $user->talents()->first();
            }

            return redirect_with_pending_action(
                app_url(route('event.create', ['subdomain' => $redirectRole->subdomain], false)),
                $pendingData
            );

        } else {
            return redirect(app_url(route('following', [], false)))
                ->with('message', str_replace(':name', $role->name, __('messages.followed_role')));
        }
    }

    public function unfollow(Request $request, $subdomain)
    {
        $role = Role::subdomain($subdomain)->firstOrFail();
        $user = $request->user();

        if ($user->isConnected($role->subdomain)) {
            $user->roles()->detach($role->id);

            if (! $role->email && $role->users()->count() === 0) {
                $role->is_deleted = true;
                $role->save();
            }
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
            if ($role && $user->isConnected($role->subdomain)) {
                $user->roles()->detach($role->id);

                if (! $role->email && $role->users()->count() === 0) {
                    $role->is_deleted = true;
                    $role->save();
                }

                $count++;
            }
        }

        return redirect(route('following'))
            ->with('message', str_replace(':count', $count, __('messages.unfollowed_roles_count')));
    }

    public function mergePreview(Request $request, $subdomain)
    {
        $source = Role::subdomain($subdomain)->where('is_deleted', false)->firstOrFail();
        $targetSubdomain = $request->input('target_subdomain');

        if (! $targetSubdomain) {
            return response()->json(['error' => __('messages.not_authorized')], 422);
        }

        $target = Role::subdomain($targetSubdomain)->where('is_deleted', false)->firstOrFail();
        $user = auth()->user();

        if (! $this->canMergeRoles($source, $target, $user)) {
            return response()->json(['error' => __('messages.not_authorized')], 403);
        }

        $totalEvents = DB::table('event_role')->where('role_id', $source->id)->count();
        $targetEventIds = DB::table('event_role')->where('role_id', $target->id)->pluck('event_id');
        $overlapCount = $targetEventIds->isEmpty()
            ? 0
            : DB::table('event_role')
                ->where('role_id', $source->id)
                ->whereIn('event_id', $targetEventIds)
                ->count();

        return response()->json([
            'source_name' => $source->getDisplayName(false),
            'target_name' => $target->getDisplayName(false),
            'event_count' => $totalEvents,
            'overlap_count' => $overlapCount,
        ]);
    }

    public function mergeInto(Request $request, $subdomain)
    {
        $source = Role::subdomain($subdomain)->where('is_deleted', false)->firstOrFail();
        $targetSubdomain = $request->input('target_subdomain');

        if (! $targetSubdomain) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $target = Role::subdomain($targetSubdomain)->where('is_deleted', false)->firstOrFail();
        $user = auth()->user();

        if (! $this->canMergeRoles($source, $target, $user)) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $eventCount = 0;
        DB::transaction(function () use ($source, $target, &$eventCount) {
            $eventCount = $this->performMerge($source, $target);
        });

        $message = str_replace(
            [':count', ':target'],
            [$eventCount, $target->getDisplayName(false)],
            __('messages.merged_into_count')
        );

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'event_count' => $eventCount, 'message' => $message]);
        }

        return redirect(route('following'))->with('message', $message);
    }

    /**
     * Merge source role into target. Caller must wrap in a DB::transaction.
     * Returns the number of event_role rows moved (excludes overlap-only events).
     */
    private function performMerge(Role $source, Role $target): int
    {
        // (Re-)read existing target event ids inside the helper so when this
        // is called repeatedly in a loop (group merge), each call sees the
        // state left by the previous call and respects the (event_id, role_id)
        // unique on event_role.
        $existingEventIds = DB::table('event_role')
            ->where('role_id', $target->id)
            ->pluck('event_id')
            ->all();

        $movedQuery = DB::table('event_role')->where('role_id', $source->id);

        if (! empty($existingEventIds)) {
            $movedQuery->whereNotIn('event_id', $existingEventIds);
        }

        $eventCount = $movedQuery->update(['role_id' => $target->id]);

        // For overlapping (event_id) rows on source and target, backfill
        // non-empty source pivot fields onto the target row when the
        // target's field is empty. Prevents silent data loss on merge.
        if (! empty($existingEventIds)) {
            $overlappingSourceRows = DB::table('event_role')
                ->where('role_id', $source->id)
                ->whereIn('event_id', $existingEventIds)
                ->get();

            $pivotFields = [
                'name_translated',
                'description_translated',
                'description_html_translated',
                'is_accepted',
                'group_id',
                'google_event_id',
                'caldav_event_uid',
                'caldav_event_etag',
            ];

            foreach ($overlappingSourceRows as $sourceRow) {
                $targetRow = DB::table('event_role')
                    ->where('role_id', $target->id)
                    ->where('event_id', $sourceRow->event_id)
                    ->first();

                if (! $targetRow) {
                    continue;
                }

                $updates = [];
                foreach ($pivotFields as $field) {
                    if ($field === 'is_accepted') {
                        // Tri-state: null=pending, true=accepted, false=declined.
                        // Promote to accepted if either side was accepted; never
                        // silently demote a target that was accepted.
                        $merged = $targetRow->is_accepted || $sourceRow->is_accepted;
                        if ((bool) $merged !== (bool) $targetRow->is_accepted) {
                            $updates['is_accepted'] = (bool) $merged;
                        }

                        continue;
                    }
                    if (empty($targetRow->{$field}) && ! empty($sourceRow->{$field})) {
                        $updates[$field] = $sourceRow->{$field};
                    }
                }

                if (! empty($updates)) {
                    DB::table('event_role')
                        ->where('role_id', $target->id)
                        ->where('event_id', $sourceRow->event_id)
                        ->update($updates);
                }
            }
        }

        // Drop the now-merged source pivot rows for overlapping events.
        DB::table('event_role')->where('role_id', $source->id)->delete();

        // Re-point calendar_syncs.role_id from source to target so future
        // Google/CalDAV sync doesn't lose state or PATCH/DELETE the wrong
        // calendar event. Drop source rows that would collide with an existing
        // (user_id, event_id, target_id) row first.
        $collisionIds = DB::table('calendar_syncs as src')
            ->join('calendar_syncs as tgt', function ($j) {
                $j->on('src.user_id', '=', 'tgt.user_id')
                    ->on('src.event_id', '=', 'tgt.event_id');
            })
            ->where('src.role_id', $source->id)
            ->where('tgt.role_id', $target->id)
            ->pluck('src.id');

        if ($collisionIds->isNotEmpty()) {
            DB::table('calendar_syncs')->whereIn('id', $collisionIds)->delete();
        }

        DB::table('calendar_syncs')
            ->where('role_id', $source->id)
            ->update(['role_id' => $target->id]);

        // Re-point events.creator_role_id so CheckData doesn't later "auto-fix"
        // by picking an arbitrary role on the event.
        DB::table('events')
            ->where('creator_role_id', $source->id)
            ->update(['creator_role_id' => $target->id]);

        // Move role_user (followers/owners) that don't already exist on target.
        $targetUserIds = DB::table('role_user')
            ->where('role_id', $target->id)
            ->pluck('user_id')
            ->all();

        DB::table('role_user')
            ->where('role_id', $source->id)
            ->when(! empty($targetUserIds), function ($q) use ($targetUserIds) {
                $q->whereNotIn('user_id', $targetUserIds);
            })
            ->update(['role_id' => $target->id]);

        DB::table('role_user')->where('role_id', $source->id)->delete();

        // Re-point carpool_offers.role_id so offers attached to the source venue
        // continue to surface against the merged-into venue. Source is only
        // soft-deleted, so the FK's nullOnDelete wouldn't fire on its own.
        DB::table('carpool_offers')
            ->where('role_id', $source->id)
            ->update(['role_id' => $target->id]);

        // Soft-delete the source.
        $source->is_deleted = true;
        $source->save();

        return $eventCount;
    }

    private function canMergeRoles(Role $source, Role $target, $user, bool $allowDeletedSource = false, bool $allowDeletedTarget = false): bool
    {
        if (! $user) {
            return false;
        }

        if ($source->id === $target->id) {
            return false;
        }

        if ($source->type !== $target->type) {
            return false;
        }

        if ($allowDeletedSource && $source->is_deleted) {
            // User::roles() filters is_deleted=false, so isEditableBy returns false
            // for any soft-deleted role. Check role_user directly so the original
            // owner can still merge a venue they (or another session) soft-deleted.
            // Mirror isEditableBy: followers can edit unclaimed venues, so allow them
            // to merge unclaimed soft-deleted ones too.
            $allowedLevels = $source->isClaimed()
                ? ['owner', 'admin']
                : ['owner', 'admin', 'follower'];

            $hasDirectEditor = DB::table('role_user')
                ->where('role_id', $source->id)
                ->where('user_id', $user->id)
                ->whereIn('level', $allowedLevels)
                ->exists();

            if (! $hasDirectEditor) {
                return false;
            }
        } elseif (! $source->isEditableBy($user)) {
            return false;
        }

        // Mirror isEditableBy(): owner/admin always, plus follower on an unclaimed
        // target. An unclaimed venue has no verified operator to protect from a
        // curator consolidating their own duplicates.
        if ($allowDeletedTarget && $target->is_deleted) {
            $allowedLevels = $target->isClaimed()
                ? ['owner', 'admin']
                : ['owner', 'admin', 'follower'];

            $hasDirectTargetEditor = DB::table('role_user')
                ->where('role_id', $target->id)
                ->where('user_id', $user->id)
                ->whereIn('level', $allowedLevels)
                ->exists();

            if (! $hasDirectTargetEditor) {
                return false;
            }
        } elseif (! $target->isEditableBy($user)) {
            return false;
        }

        // Source must be unclaimed to prevent destroying someone else's verified record.
        if ($source->isClaimed()) {
            return false;
        }

        return true;
    }

    /**
     * True when both roles share at least one owner/admin user, i.e. they are managed by
     * the same person. Used to decide whether a curator's acceptance should carry onto a
     * co-owned venue/talent.
     */
    private function rolesShareOwner(Role $a, Role $b): bool
    {
        return DB::table('role_user as a')
            ->join('role_user as b', 'a.user_id', '=', 'b.user_id')
            ->where('a.role_id', $a->id)
            ->where('b.role_id', $b->id)
            ->whereIn('a.level', ['owner', 'admin'])
            ->whereIn('b.level', ['owner', 'admin'])
            ->exists();
    }

    /**
     * Return duplicate-venue groups across a curator schedule's future, accepted
     * events. Each group is an array of Role models sharing a normalized
     * name|city|country key, with $venue->future_event_count and
     * $venue->ids_hash populated. Soft-deleted venues are included (the whole
     * point of the tool). Groups dismissed by $userId are filtered out.
     */
    private function venueDuplicateGroups(Role $schedule, ?int $userId = null): array
    {
        $eventIds = DB::table('event_role')
            ->where('role_id', $schedule->id)
            ->where('is_accepted', true)
            ->pluck('event_id');

        if ($eventIds->isEmpty()) {
            return [];
        }

        $futureEventIds = Event::whereIn('id', $eventIds)
            ->upcomingOrOngoing()
            ->pluck('id');

        if ($futureEventIds->isEmpty()) {
            return [];
        }

        // Per-venue future-event count on this schedule. Use DISTINCT event_id
        // because event_role has one row per (event, venue, group), so an event
        // associated with multiple sub-schedules would otherwise be counted twice.
        $countsByVenue = DB::table('event_role')
            ->whereIn('event_id', $futureEventIds)
            ->select('role_id', DB::raw('COUNT(DISTINCT event_id) as future_event_count'))
            ->groupBy('role_id')
            ->pluck('future_event_count', 'role_id');

        $venueIds = $countsByVenue->keys()->all();

        if (empty($venueIds)) {
            return [];
        }

        $venues = Role::whereIn('id', $venueIds)
            ->where('type', 'venue')
            ->get(['id', 'subdomain', 'name', 'city', 'country_code', 'email_verified_at', 'phone_verified_at', 'user_id', 'is_deleted']);

        $grouped = [];
        foreach ($venues as $venue) {
            $normName = GeminiUtils::normalizeForMatch($venue->name);
            if ($normName === '') {
                continue;
            }
            $normCity = GeminiUtils::normalizeForMatch($venue->city);
            $country = $venue->country_code ? strtolower($venue->country_code) : '';
            $venue->future_event_count = (int) ($countsByVenue[$venue->id] ?? 0);
            $key = $normName.'|'.$normCity.'|'.$country;
            $grouped[$key][] = $venue;
        }

        $dismissedHashes = [];
        if ($userId) {
            $dismissedHashes = DismissedVenueMergeSuggestion::where('user_id', $userId)
                ->where('role_id', $schedule->id)
                ->pluck('venue_ids_hash')
                ->all();
        }

        $groups = [];
        foreach ($grouped as $group) {
            if (count($group) < 2) {
                continue;
            }
            $ids = array_map(fn ($v) => $v->id, $group);
            $hash = DismissedVenueMergeSuggestion::hashForVenueIds($ids);
            if (in_array($hash, $dismissedHashes, true)) {
                continue;
            }
            foreach ($group as $venue) {
                $venue->ids_hash = $hash;
            }
            $groups[] = $group;
        }

        return $groups;
    }

    /**
     * Upcoming, accepted events on this schedule whose effective timezone differs from the
     * schedule's timezone (so they may publish at the wrong time in graphics/emails). Returns an
     * empty collection when the warning has been dismissed for the current off-timezone set
     * (passing $userId), or pass no $userId to get the raw set regardless of dismissal.
     */
    private function offTimezoneEvents(Role $schedule, ?int $userId = null): \Illuminate\Support\Collection
    {
        if (! $schedule->timezone) {
            return collect();
        }

        $eventIds = DB::table('event_role')
            ->where('role_id', $schedule->id)
            ->where('is_accepted', true)
            ->pluck('event_id');

        if ($eventIds->isEmpty()) {
            return collect();
        }

        $events = Event::whereIn('id', $eventIds)
            ->upcomingOrOngoing()
            ->where('is_private', false)
            ->where('is_draft', false)
            ->whereNull('event_password')
            ->with('user:id,timezone')
            ->get(['id', 'name', 'starts_at', 'timezone', 'user_id'])
            ->filter(fn (Event $event) => $event->isOffTimezoneFor($schedule))
            ->values();

        if ($userId && $events->isNotEmpty()) {
            $hash = DismissedTimezoneWarning::hashForEventIds($events->pluck('id')->all());
            $dismissed = DismissedTimezoneWarning::where('user_id', $userId)
                ->where('role_id', $schedule->id)
                ->where('events_hash', $hash)
                ->exists();

            if ($dismissed) {
                return collect();
            }
        }

        return $events;
    }

    public function dismissTimezoneWarning(Request $request, $subdomain)
    {
        if (is_demo_mode()) {
            return redirect()->route('role.view_admin', ['subdomain' => $subdomain, 'tab' => 'schedule'])
                ->with('error', __('messages.demo_mode_restriction'));
        }

        $role = Role::subdomain($subdomain)->firstOrFail();

        if (! auth()->user()->isEditor($subdomain) || ! $role->isCurator()) {
            return redirect()->route('role.view_admin', ['subdomain' => $subdomain, 'tab' => 'schedule'])
                ->with('error', __('messages.not_authorized'));
        }

        // Recompute the current off-timezone set server-side and dismiss exactly that set, so the
        // banner re-appears if a new off-timezone event later changes the set (and hash).
        $events = $this->offTimezoneEvents($role);

        if ($events->isNotEmpty()) {
            DismissedTimezoneWarning::firstOrCreate([
                'user_id' => auth()->user()->id,
                'role_id' => $role->id,
                'events_hash' => DismissedTimezoneWarning::hashForEventIds($events->pluck('id')->all()),
            ]);
        }

        return redirect()->route('role.view_admin', ['subdomain' => $subdomain, 'tab' => 'schedule']);
    }

    public function fixEventsTimezone(Request $request, $subdomain)
    {
        if (is_demo_mode()) {
            return redirect()->route('role.view_admin', ['subdomain' => $subdomain, 'tab' => 'schedule'])
                ->with('error', __('messages.demo_mode_restriction'));
        }

        $role = Role::subdomain($subdomain)->firstOrFail();

        if (! auth()->user()->isEditor($subdomain) || ! $role->isCurator()) {
            return redirect()->route('role.view_admin', ['subdomain' => $subdomain, 'tab' => 'schedule'])
                ->with('error', __('messages.not_authorized'));
        }

        $events = $this->offTimezoneEvents($role);

        // Nothing left to fix (a double submit, or another editor got here first). The banner is
        // already gone, so say nothing rather than report a failure.
        if ($events->isEmpty()) {
            return redirect()->route('role.view_admin', ['subdomain' => $subdomain, 'tab' => 'schedule']);
        }

        // The button always fixes the events onto the schedule's own timezone. Require the posted
        // value to still match it, so a schedule-timezone change since the banner rendered can't
        // relabel every event to a zone the curator never saw in the confirmation dialog.
        if (! $role->timezone || $request->input('timezone') !== $role->timezone) {
            return redirect()->route('role.view_admin', ['subdomain' => $subdomain, 'tab' => 'schedule']);
        }

        // Re-fetch full rows: offTimezoneEvents() selects only a few columns, and saving a partially
        // loaded event would let the saving hook re-derive the *_html columns from unloaded (null)
        // source columns and wipe them.
        $events = Event::whereIn('id', $events->pluck('id'))->get();

        foreach ($events as $event) {
            // Relabel only: keep the wall-clock starts_at and change the timezone it was captured in,
            // so 20:00 America/New_York becomes 20:00 Asia/Jerusalem (the event was really in the
            // schedule's timezone; only its label was wrong).
            $event->timezone = $role->timezone;
            $event->save();

            // The relabel moves the event's absolute instant, so push it to any external calendars the
            // event's roles sync to (both no-op when nothing is synced).
            $event->syncToGoogleCalendar('update');
            $event->syncToMicrosoftCalendar('update');
            $event->syncToCalDAV('update');

            AuditService::log(AuditService::EVENT_UPDATE, auth()->id(), 'Event', $event->id, null, null, $event->name);
        }

        return redirect()->route('role.view_admin', ['subdomain' => $subdomain, 'tab' => 'schedule'])
            ->with('message', __('messages.timezone_updated'));
    }

    public function mergeVenues($subdomain)
    {
        if (is_demo_mode()) {
            return redirect()->route('home')->with('error', __('messages.demo_mode_restriction'));
        }

        $role = Role::subdomain($subdomain)->firstOrFail();

        if (! auth()->user()->isEditor($subdomain) || ! $role->isCurator()) {
            return redirect()->route('role.view_admin', ['subdomain' => $subdomain, 'tab' => 'schedule'])
                ->with('error', __('messages.not_authorized'));
        }

        $groups = $this->venueDuplicateGroups($role, auth()->user()->id);

        return view('role/merge_venues', [
            'role' => $role,
            'groups' => $groups,
        ]);
    }

    /**
     * True iff every id in $ids comes from a single duplicate group. Prevents
     * crafted requests from mixing venues that happen to each be in some
     * duplicate group but not the same one.
     */
    private function idsBelongToSingleGroup(array $groups, array $ids): bool
    {
        foreach ($groups as $group) {
            $groupIds = array_map(fn ($v) => $v->id, $group);
            if (! array_diff($ids, $groupIds)) {
                return true;
            }
        }

        return false;
    }

    public function mergeVenuesGroupPreview(Request $request, $subdomain)
    {
        if (is_demo_mode()) {
            return response()->json(['error' => __('messages.demo_mode_restriction')], 403);
        }

        $role = Role::subdomain($subdomain)->firstOrFail();

        if (! auth()->user()->isEditor($subdomain) || ! $role->isCurator()) {
            return response()->json(['error' => __('messages.not_authorized')], 403);
        }

        $targetId = (int) $request->input('target_id');
        $sourceIds = array_filter(array_map('intval', (array) $request->input('source_ids', [])));

        if (! $targetId || empty($sourceIds) || in_array($targetId, $sourceIds, true)) {
            return response()->json(['error' => __('messages.not_authorized')], 422);
        }

        $allIds = array_merge([$targetId], $sourceIds);
        $groups = $this->venueDuplicateGroups($role, auth()->user()->id);
        if (! $this->idsBelongToSingleGroup($groups, $allIds)) {
            return response()->json(['error' => __('messages.not_authorized')], 403);
        }

        $target = Role::where('id', $targetId)->where('type', 'venue')->firstOrFail();

        // Scope counts to this curator's future, accepted events so the dialog
        // matches the per-venue totals shown on the page. The merge itself still
        // re-points every event_role row (past + future); this only changes what
        // the preview reports to the user.
        $curatorEventIds = DB::table('event_role')
            ->where('role_id', $role->id)
            ->where('is_accepted', true)
            ->pluck('event_id');

        $futureCuratorEventIds = Event::whereIn('id', $curatorEventIds)
            ->upcomingOrOngoing()
            ->pluck('id');

        $sourceEventIds = DB::table('event_role')
            ->whereIn('role_id', $sourceIds)
            ->whereIn('event_id', $futureCuratorEventIds)
            ->pluck('event_id')
            ->unique();
        $totalEvents = $sourceEventIds->count();

        $targetEventIds = DB::table('event_role')
            ->where('role_id', $target->id)
            ->whereIn('event_id', $futureCuratorEventIds)
            ->pluck('event_id')
            ->unique();
        $overlapEvents = $sourceEventIds->intersect($targetEventIds)->count();

        return response()->json([
            'source_count' => count($sourceIds),
            'total_events' => $totalEvents,
            'overlap_events' => $overlapEvents,
            'target_name' => $target->getDisplayName(false),
            'target_is_deleted' => (bool) $target->is_deleted,
        ]);
    }

    public function mergeVenuesGroup(Request $request, $subdomain)
    {
        if (is_demo_mode()) {
            return redirect()->route('role.merge_venues', ['subdomain' => $subdomain])
                ->with('error', __('messages.demo_mode_restriction'));
        }

        $role = Role::subdomain($subdomain)->firstOrFail();

        if (! auth()->user()->isEditor($subdomain) || ! $role->isCurator()) {
            return redirect()->route('role.view_admin', ['subdomain' => $subdomain, 'tab' => 'schedule'])
                ->with('error', __('messages.not_authorized'));
        }

        $user = auth()->user();
        $targetId = (int) $request->input('target_id');
        $sourceIds = array_filter(array_map('intval', (array) $request->input('source_ids', [])));

        if (! $targetId || empty($sourceIds) || in_array($targetId, $sourceIds, true)) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $allIds = array_merge([$targetId], $sourceIds);
        $groups = $this->venueDuplicateGroups($role, $user->id);
        if (! $this->idsBelongToSingleGroup($groups, $allIds)) {
            return redirect()->route('role.merge_venues', ['subdomain' => $subdomain])
                ->with('error', __('messages.not_authorized'));
        }

        $target = Role::where('id', $targetId)->where('type', 'venue')->firstOrFail();
        $sources = Role::whereIn('id', $sourceIds)->where('type', 'venue')->get();

        // Curator-driven merge: the screen guard above requires the user to be
        // an editor of the curator schedule, and idsBelongToSingleGroup() above
        // proves every venue in this request is part of this curator's duplicate
        // set. Venue-level access (canMergeRoles -> isEditableBy) is the wrong
        // gate here because curator-imported venues frequently have no role_user
        // row for the curator's editors - EventRepo::saveEvent's followNewRoles
        // flag is false for Eventbrite, WhatsApp, and guest-submission paths.
        // Structural checks only; partition so a single bad row (e.g. someone
        // claimed a venue between page render and submit) doesn't abort the batch.
        [$validated, $skipped] = $sources->partition(function ($source) use ($target) {
            if ($source->id === $target->id) {
                return false;
            }
            if ($source->type !== $target->type) {
                return false;
            }

            // Never destroy a claimed venue's verified record.
            if ($source->isClaimed()) {
                return false;
            }

            return true;
        });

        if ($validated->isEmpty()) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $eventCount = 0;
        DB::transaction(function () use ($role, $validated, $target, &$eventCount) {
            foreach ($validated as $source) {
                $eventCount += $this->performMerge($source, $target);
            }

            // Auto-revive the target if it was soft-deleted.
            if ($target->is_deleted) {
                $target->is_deleted = false;
                $target->save();
            }

            // When the curator and the target venue are owned by the same user, the merge
            // is a self-consolidation: the events are already accepted on the curator, so
            // accept them on the venue too. Otherwise the venue's own page keeps hiding them
            // (its is_accepted stays false/null from the importer). Gate on co-ownership so a
            // merge into a venue claimed by someone else never force-accepts onto their page.
            if ($this->rolesShareOwner($role, $target)) {
                // Events sitting on the target venue but not yet accepted there...
                $pendingEventIds = DB::table('event_role')
                    ->where('role_id', $target->id)
                    ->where(fn ($q) => $q->whereNull('is_accepted')->orWhere('is_accepted', false))
                    ->pluck('event_id');

                // ...that the curator has already accepted. Materialised (no event_role subquery inside
                // the UPDATE) to avoid MySQL error 1093 (can't update a table referenced by a subquery
                // in its own WHERE).
                $acceptedEventIds = DB::table('event_role')
                    ->where('role_id', $role->id)
                    ->where('is_accepted', true)
                    ->whereIn('event_id', $pendingEventIds)
                    ->pluck('event_id');

                if ($acceptedEventIds->isNotEmpty()) {
                    DB::table('event_role')
                        ->where('role_id', $target->id)
                        ->whereIn('event_id', $acceptedEventIds)
                        ->update(['is_accepted' => true]);
                }
            }
        });

        if ($skipped->isNotEmpty()) {
            $message = str_replace(
                [':done', ':total', ':skipped', ':target'],
                [$validated->count(), $sources->count(), $skipped->count(), $target->getDisplayName(false)],
                __('messages.merge_venues_partial_result')
            );
        } else {
            $message = str_replace(
                [':count', ':target'],
                [$eventCount, $target->getDisplayName(false)],
                __('messages.merged_into_count')
            );
        }

        $remaining = count($this->venueDuplicateGroups($role, $user->id));
        $next = $remaining > 0
            ? route('role.merge_venues', ['subdomain' => $subdomain])
            : route('role.view_admin', ['subdomain' => $subdomain, 'tab' => 'schedule']);

        return redirect($next)->with('message', $message);
    }

    public function dismissVenueMergeSuggestion(Request $request, $subdomain)
    {
        if (is_demo_mode()) {
            return redirect()->route('role.merge_venues', ['subdomain' => $subdomain])
                ->with('error', __('messages.demo_mode_restriction'));
        }

        $role = Role::subdomain($subdomain)->firstOrFail();

        if (! auth()->user()->isEditor($subdomain) || ! $role->isCurator()) {
            return redirect()->route('role.view_admin', ['subdomain' => $subdomain, 'tab' => 'schedule'])
                ->with('error', __('messages.not_authorized'));
        }

        $venueIds = array_filter(array_map('intval', (array) $request->input('venue_ids', [])));
        if (count($venueIds) < 2) {
            return redirect()->back();
        }

        $groups = $this->venueDuplicateGroups($role, auth()->user()->id);
        if (! $this->idsBelongToSingleGroup($groups, $venueIds)) {
            return redirect()->route('role.merge_venues', ['subdomain' => $subdomain])
                ->with('error', __('messages.not_authorized'));
        }

        $hash = DismissedVenueMergeSuggestion::hashForVenueIds($venueIds);

        DismissedVenueMergeSuggestion::firstOrCreate([
            'user_id' => auth()->user()->id,
            'role_id' => $role->id,
            'venue_ids_hash' => $hash,
        ]);

        return redirect()->route('role.merge_venues', ['subdomain' => $subdomain]);
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

        // Query params can arrive as arrays (?lang[]=en); is_valid_language_code() is typed
        // ?string, so only honor a string value (matches the sibling submission methods).
        $lang = is_string($request->lang) ? $request->lang : null;

        if ($lang) {
            // Validate the language code before setting it
            if (is_valid_language_code($lang)) {
                app()->setLocale($lang);

                if ($lang == $role->translation_language_code && $lang != $role->language_code) {
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
            app()->setLocale($role->translation_language_code);
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
            $month = DateUtils::normalizeMonth($request->month);
            $year = DateUtils::normalizeYear($request->year);
        }

        if ($slug) {
            // Check if slug is a social platform vanity URL. An event id in the URL means the
            // slug names an event, even one named after a platform, so don't redirect off-site.
            if (! $eventIdParam && in_array($slug, UrlUtils::getUniquePlatforms())) {
                return $this->handleSocialRedirect($role, $slug, $request);
            }

            // An event id identifies an event, so resolve it before treating the slug as a group
            // slug. Otherwise an event sharing its sub-schedule's slug is shadowed by the group.
            if ($eventIdParam) {
                $event = $this->eventRepo->getEvent($subdomain, $slug, $date, $eventIdParam, $role);

                // Fallback: allow schedule members to view pending (not yet accepted) or draft events
                if (! $event && $user && $user->isMember($subdomain)) {
                    $event = Event::with(['roles', 'parts.approvedVideos.user', 'parts.approvedComments.user', 'parts.approvedPhotos.user', 'tickets', 'addons', 'user', 'approvedVideos.user', 'approvedComments.user', 'approvedPhotos.user', 'polls' => fn ($q) => $q->withCount('votes')])
                        ->where('id', $eventIdParam)
                        ->where(function ($q) use ($role) {
                            $q->whereHas('roles', fn ($q) => $q->where('role_id', $role->id)->whereNull('is_accepted'))
                                ->orWhere(function ($q) use ($role) {
                                    $q->where('is_draft', true)
                                        ->whereHas('roles', fn ($q) => $q->where('role_id', $role->id));
                                });
                        })
                        ->first();
                }
            }

            // No event resolved, so check if slug is a group slug
            if (! $event) {
                $group = $role->groups ? $role->groups->where('slug', $slug)->first() : null;

                if ($group) {
                    $selectedGroup = $group;
                    $slug = ''; // Clear slug since it's a group, not an event
                } elseif (! $eventIdParam) {
                    // Try to find event by slug
                    $event = $this->eventRepo->getEvent($subdomain, $slug, $date, $eventIdParam, $role);
                }
            }

            if ($event) {
                // Block direct URL access to draft events for non-members
                if ($event->is_draft && (! $user || (! $user->isMember($subdomain) && ! $user->isAdmin()))) {
                    $event = null;
                }
            }

            // Unlisted (is_private) events are reachable by direct link - "not listed, anyone with the
            // link can view". They are excluded from every listing, feed and search, but the direct URL
            // renders for anyone holding it. Password-protected private events still fall through to the
            // password gate below; Draft/Internal (is_draft) remain members-only via their own guard.

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

        // Calculate calendar grid start (including overflow days from previous month)
        $firstDayOfWeek = $role->first_day_of_week ?? 0;
        $startOfMonth = Carbon::create($year, $month, 1, 0, 0, 0, $timezone)->startOfMonth();
        $startOfGrid = $startOfMonth->copy()->startOfWeek($firstDayOfWeek);
        $startOfGridUtc = $startOfGrid->copy()->setTimezone('UTC');

        // Calendar grid end (including overflow days from the next month), used to upper-bound the
        // event query so it only loads the visible window rather than every future event. The
        // 2-day buffer absorbs timezone skew: matchesDate() places events by the EVENT's own
        // timezone, so an event on the last grid day in a zone behind the schedule's could sit
        // just past a tight bound. Extra loaded rows never render (buildEventsMap does exact
        // per-day placement), so the query stays a safe superset of the visible window.
        $lastDayOfWeek = ($firstDayOfWeek + 6) % 7;
        $endOfGridUtc = $startOfMonth->copy()->endOfMonth()->endOfWeek($lastDayOfWeek)->addDays(2)->setTimezone('UTC');

        $isMemberOrAdmin = $user && ($user->isMember($subdomain) || $user->isAdmin());
        $unlockedEventIds = ! $isMemberOrAdmin ? $this->getUnlockedEventIds() : [];

        if ($event && ! request()->graphic) {
            // For event detail view (non-graphic), only check if calendar has events
            // The calendar partial loads data via Ajax, so we just need existence
            if ($role->isCurator()) {
                $query = Event::inMonth($startOfGridUtc, $endOfGridUtc)
                    ->whereIn('id', function ($query) use ($role) {
                        $query->select('event_id')
                            ->from('event_role')
                            ->where('role_id', $role->id)
                            ->where('is_accepted', true);
                    });
                if (! $isMemberOrAdmin) {
                    $query->where('is_draft', false);
                    $query->where('is_cancelled', false);
                    $query->where(function ($q) use ($unlockedEventIds) {
                        $q->where('is_private', false);
                        if ($unlockedEventIds) {
                            $q->orWhereIn('id', $unlockedEventIds);
                        }
                    });
                }
                $hasCalendarEvents = $query->exists();
            } else {
                $query = Event::inMonth($startOfGridUtc, $endOfGridUtc)
                    ->whereHas('roles', fn ($q) => $q->where('role_id', $role->id)->where('is_accepted', true));
                if (! $isMemberOrAdmin) {
                    $query->where('is_draft', false);
                    $query->where('is_cancelled', false);
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
                ->inMonth($startOfGridUtc, $endOfGridUtc)
                ->whereIn('id', function ($query) use ($role) {
                    $query->select('event_id')
                        ->from('event_role')
                        ->where('role_id', $role->id)
                        ->where('is_accepted', true);
                })
                ->when(! $isMemberOrAdmin, fn ($q) => $q->where('is_draft', false)->where('is_cancelled', false))
                ->orderBy('starts_at')
                ->get();
        } else {
            $events = Event::with(['roles', 'parts', 'approvedVideos', 'approvedPhotos', 'approvedComments.user', 'polls' => fn ($q) => $q->withCount('votes')])->withCount(['approvedVideos', 'approvedComments', 'approvedPhotos', 'polls'])
                ->inMonth($startOfGridUtc, $endOfGridUtc)
                ->where(function ($query) use ($role) {
                    $query->whereHas('roles', function ($query) use ($role) {
                        $query->where('role_id', $role->id)
                            ->where('is_accepted', true);
                    });
                })
                ->when(! $isMemberOrAdmin, fn ($q) => $q->where('is_draft', false)->where('is_cancelled', false));

            $events = $events->orderBy('starts_at')->get();
        }

        // Fetch past non-recurring events only for graphic mode (otherwise loaded via Ajax)
        $pastEvents = collect();
        $hasMorePastEvents = false;
        if (request()->graphic && ! $role->hide_past_events) {
            if ($role->isCurator()) {
                $pastEvents = Event::with(['roles', 'parts', 'approvedVideos', 'approvedPhotos', 'approvedComments.user', 'polls' => fn ($q) => $q->withCount('votes')])->withCount(['approvedVideos', 'approvedComments', 'approvedPhotos', 'polls'])
                    ->fullyPast(Carbon::now('UTC'))
                    ->whereNull('days_of_week')
                    ->whereIn('id', function ($query) use ($role) {
                        $query->select('event_id')
                            ->from('event_role')
                            ->where('role_id', $role->id)
                            ->where('is_accepted', true);
                    })
                    ->when(! $isMemberOrAdmin, fn ($q) => $q->where('is_draft', false)->where('is_cancelled', false))
                    ->orderByDesc('starts_at')
                    ->limit(51)
                    ->get();
            } else {
                $pastEvents = Event::with(['roles', 'parts', 'approvedVideos', 'approvedPhotos', 'approvedComments.user', 'polls' => fn ($q) => $q->withCount('votes')])->withCount(['approvedVideos', 'approvedComments', 'approvedPhotos', 'polls'])
                    ->fullyPast(Carbon::now('UTC'))
                    ->whereNull('days_of_week')
                    ->whereHas('roles', fn ($q) => $q->where('role_id', $role->id)->where('is_accepted', true))
                    ->when(! $isMemberOrAdmin, fn ($q) => $q->where('is_draft', false)->where('is_cancelled', false))
                    ->orderByDesc('starts_at')
                    ->limit(51)
                    ->get();
            }
            $hasMorePastEvents = $pastEvents->count() > 50;
            if ($hasMorePastEvents) {
                $pastEvents = $pastEvents->take(50);
            }
        }

        // Filter draft and private events from calendar listings for non-members. Past events are
        // filtered UNCONDITIONALLY: gating this on the current month having events meant an eventless
        // month skipped the filter entirely, leaking Unlisted (is_private) past events into the public
        // ?graphic=1 view (their full data is serialized into pastEventsForVue).
        if (! $isMemberOrAdmin) {
            $keep = fn ($e) => ! $e->is_draft && (! $e->is_private || in_array($e->id, $unlockedEventIds));
            if ($events->first() instanceof Event) {
                $events = $events->filter($keep);
            }
            $pastEvents = $pastEvents->filter($keep);
        }

        // Dedicated bounded query for the homepage "upcoming events with videos" carousel. Kept
        // separate from the calendar's month-windowed $events so it can promote the next videos
        // across months without loading the entire event table (which OOMs large schedules). The
        // blade re-filters this set with getFirstVideoUrl(), so the coarse youtube_links filter
        // here just needs to be a superset.
        $carouselEvents = collect();
        if (! $event && ! $role->isTalent() && ! $role->hide_videos) {
            $carouselEvents = Event::with('roles')
                ->upcomingOrOngoing(Carbon::now('UTC'))
                ->whereHas('roles', fn ($q) => $q->where('role_id', $role->id)->where('is_accepted', true))
                ->whereHas('roles', fn ($q) => $q->where('type', 'talent')
                    ->whereNotNull('youtube_links')
                    ->where('youtube_links', '!=', '[]'))
                ->when(! $isMemberOrAdmin, fn ($q) => $q->where('is_draft', false)->where('is_cancelled', false)->where('is_private', false))
                ->orderBy('starts_at')
                ->limit(20)
                ->get();
        }

        // Track view for analytics (non-member visits only, skip embeds)
        if (! request()->embed && (! $user || (! $user->isMember($subdomain) && ! $user->isAdmin()))) {
            app(AnalyticsService::class)->recordView($role, $event, $request);
        }

        $myPendingVideos = collect();
        $myPendingComments = collect();
        $myPendingPhotos = collect();
        $publicFeedbacks = collect();
        $feedbackCount = 0;
        $avgRating = 0;
        $photoLimitReached = false;
        $userSale = null;

        $embed = request()->embed;
        $view = 'role/show-guest';

        if ($embed && $event && (request()->get('tickets') === 'true' || request()->get('rsvp') === 'true')) {
            // Password check for embed mode
            $bypassPassword = ($user && ($user->isAdmin() || $user->isMember($subdomain)))
                || session()->has('event_password_'.$event->id);
            if ($event->isPasswordProtected() && ! $bypassPassword) {
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

            if ($event->isPasswordProtected() && ! $bypassPassword) {
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

            if ($role->isPro() && $role->feedback_enabled && $role->feedback_public && $event->isFeedbackEnabled($role)) {
                $query = $event->feedbacks()
                    ->whereHas('sale', fn ($q) => $q->where('is_deleted', false)->where('status', 'paid'));
                if ($date) {
                    $query->where('event_date', $date);
                }
                $stats = (clone $query)->selectRaw('COUNT(*) as count, ROUND(AVG(rating), 1) as avg_rating')->first();
                $feedbackCount = (int) $stats->count;
                $avgRating = (float) ($stats->avg_rating ?? 0);
                $publicFeedbacks = $query->with('sale')->latest()->limit(20)->get();
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
                'carouselEvents',
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
                'publicFeedbacks',
                'feedbackCount',
                'avgRating',
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
            ->where('is_draft', false)
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
                ->fullyPast($beforeDate)
                ->whereNull('days_of_week')
                ->whereIn('id', function ($query) use ($role) {
                    $query->select('event_id')
                        ->from('event_role')
                        ->where('role_id', $role->id)
                        ->where('is_accepted', true);
                })
                ->when(! $isMemberOrAdmin, function ($q) use ($unlockedEventIds) {
                    $q->where('is_draft', false);
                    $q->where('is_cancelled', false);
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
                ->fullyPast($beforeDate)
                ->whereNull('days_of_week')
                ->whereHas('roles', fn ($q) => $q->where('role_id', $role->id)->where('is_accepted', true))
                ->when(! $isMemberOrAdmin, function ($q) use ($unlockedEventIds) {
                    $q->where('is_draft', false);
                    $q->where('is_cancelled', false);
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

    private function handleSocialRedirect(Role $role, string $platform, Request $request)
    {
        $socialLinks = is_string($role->social_links)
            ? json_decode($role->social_links, true)
            : $role->social_links;

        if (is_array($socialLinks)) {
            foreach ($socialLinks as $link) {
                $url = $link['url'] ?? '';
                if ($url && UrlUtils::detectPlatform($url) === $platform) {
                    $user = auth()->user();
                    if (! $user || (! $user->isMember($role->subdomain) && ! $user->isAdmin())) {
                        $this->recordSocialClick($role, $platform, $request);
                    }

                    return redirect($url, 302);
                }
            }
        }

        return redirect($role->getGuestUrl());
    }

    private function recordSocialClick(Role $role, string $platform, Request $request): void
    {
        $userAgent = $request->userAgent();

        if (PageView::isBot($userAgent)) {
            return;
        }

        if (PageView::isSuspiciousRequest($request)) {
            return;
        }

        $ip = $request->ip();
        if ($ip) {
            $dailySalt = config('app.key').now()->format('Y-m-d');
            $ipHash = hash('sha256', $ip.$dailySalt);
            $cacheKey = "social_click:{$role->id}:{$ipHash}";
            $secondsUntilMidnight = now()->endOfDay()->diffInSeconds(now());
            Cache::add($cacheKey, 0, $secondsUntilMidnight);
            if (Cache::increment($cacheKey) > 10) {
                return;
            }
        }

        AnalyticsSocialClicksDaily::incrementClick($role->id, $platform);

        // Track referrer source
        $referrer = $request->header('referer');
        $utmSource = $request->query('utm_source');
        $sourceOverride = match ($utmSource) {
            'boost' => 'boost',
            'newsletter' => 'newsletter',
            default => null,
        };
        if (! $sourceOverride && $request->query('promo')) {
            $sourceOverride = 'promo';
        }
        AnalyticsReferrersDaily::incrementView($role->id, $referrer, $role->custom_domain, $sourceOverride);

        // Track UTM parameters
        $utmParams = [
            'source' => $request->query('utm_source'),
            'medium' => $request->query('utm_medium'),
            'campaign' => $request->query('utm_campaign'),
            'content' => $request->query('utm_content'),
            'term' => $request->query('utm_term'),
        ];
        foreach ($utmParams as $paramType => $paramValue) {
            if ($paramValue !== null && $paramValue !== '') {
                $paramValue = mb_substr(trim($paramValue), 0, 255);
                AnalyticsUtmDaily::incrementView($role->id, $paramType, $paramValue);
            }
        }
    }

    private function eventToVueArray(Event $event, ?Role $role, ?string $subdomain): array
    {
        $groupId = $role ? $event->getGroupIdForSubdomain($role->subdomain) : null;

        $data = [
            'id' => UrlUtils::encodeId($event->id),
            'group_id' => $groupId ? UrlUtils::encodeId($groupId) : null,
            'category_id' => $event->category_id,
            'category_name' => $event->resolveCategoryName(),
            'category_color' => $event->resolveCategoryColor(),
            'name' => $event->translatedName(),
            'dir' => content_dir($role, showing_translation($role) && (bool) $event->name_en),
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
            'talent' => $event->roles->filter(fn ($r) => $r->type === 'talent' && $r->isClaimed())->map(fn ($r) => [
                'name' => $r->name,
                'profile_image' => $r->profile_image_url ?: null,
                'header_image' => ($r->getAttributes()['header_image'] && $r->getAttributes()['header_image'] !== 'none') ? $r->getHeaderImageUrlAttribute($r->getAttributes()['header_image']) : null,
                'guest_url' => ($role && $r->subdomain === $role->subdomain) ? null : ($r->getGuestUrl() ?: null),
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

        $month = DateUtils::normalizeMonth($request->month);
        $year = DateUtils::normalizeYear($request->year);

        $user = auth()->user();
        $timezone = ($user ? $user->timezone : null) ?? $role->timezone ?? 'UTC';

        $firstDayOfWeek = $role->first_day_of_week ?? 0;
        $startOfGrid = Carbon::create($year, $month, 1, 0, 0, 0, $timezone)->startOfMonth()->startOfWeek($firstDayOfWeek);
        $startOfGridUtc = $startOfGrid->copy()->setTimezone('UTC');
        $endOfGridUtc = Carbon::create($year, $month, 1, 0, 0, 0, $timezone)->endOfMonth()->endOfWeek(($firstDayOfWeek + 6) % 7)->addDays(2)->setTimezone('UTC');

        $isMemberOrAdmin = $user && ($user->isMember($subdomain) || $user->isAdmin());
        $unlockedEventIds = ! $isMemberOrAdmin ? $this->getUnlockedEventIds() : [];

        // The list layout renders all upcoming events in one flat list, so it needs future events
        // beyond the current month grid. Drop the upper-bound month window and cap the row count
        // instead: bounded memory (this is what prevents the original OOM) and a safe superset of
        // the events the list actually shows (it displays at most 100 upcoming events client-side).
        $isListView = $request->boolean('list');
        $queryEndUtc = $isListView ? null : $endOfGridUtc;

        if ($role->isCurator()) {
            $events = Event::with(['roles', 'parts', 'tickets', 'approvedVideos', 'approvedPhotos', 'approvedComments.user', 'polls' => fn ($q) => $q->withCount('votes')])->withCount(['approvedVideos', 'approvedComments', 'approvedPhotos', 'polls'])
                ->inMonth($startOfGridUtc, $queryEndUtc)
                ->whereIn('id', function ($query) use ($role) {
                    $query->select('event_id')
                        ->from('event_role')
                        ->where('role_id', $role->id)
                        ->where('is_accepted', true);
                })
                ->when(! $isMemberOrAdmin, function ($q) use ($unlockedEventIds) {
                    $q->where('is_draft', false);
                    $q->where('is_cancelled', false);
                    $q->where(function ($q) use ($unlockedEventIds) {
                        $q->where('is_private', false);
                        if ($unlockedEventIds) {
                            $q->orWhereIn('id', $unlockedEventIds);
                        }
                    });
                })
                ->orderBy('starts_at')
                ->when($isListView, fn ($q) => $q->limit(self::LIST_EVENT_CAP))
                ->get();
        } else {
            $events = Event::with(['roles', 'parts', 'tickets', 'approvedVideos', 'approvedPhotos', 'approvedComments.user', 'polls' => fn ($q) => $q->withCount('votes')])->withCount(['approvedVideos', 'approvedComments', 'approvedPhotos', 'polls'])
                ->inMonth($startOfGridUtc, $queryEndUtc)
                ->where(function ($query) use ($role) {
                    $query->whereHas('roles', function ($query) use ($role) {
                        $query->where('role_id', $role->id)
                            ->where('is_accepted', true);
                    });
                })
                ->when(! $isMemberOrAdmin, function ($q) use ($unlockedEventIds) {
                    $q->where('is_draft', false);
                    $q->where('is_cancelled', false);
                    $q->where(function ($q) use ($unlockedEventIds) {
                        $q->where('is_private', false);
                        if ($unlockedEventIds) {
                            $q->orWhereIn('id', $unlockedEventIds);
                        }
                    });
                })
                ->orderBy('starts_at')
                ->when($isListView, fn ($q) => $q->limit(self::LIST_EVENT_CAP))
                ->get();
        }

        $pastEvents = collect();
        $hasMorePastEvents = false;

        if (! $role->hide_past_events) {
            if ($role->isCurator()) {
                $pastEvents = Event::with(['roles', 'parts', 'tickets', 'approvedVideos', 'approvedPhotos', 'approvedComments.user', 'polls' => fn ($q) => $q->withCount('votes')])->withCount(['approvedVideos', 'approvedComments', 'approvedPhotos', 'polls'])
                    ->fullyPast(Carbon::now('UTC'))
                    ->whereNull('days_of_week')
                    ->whereIn('id', function ($query) use ($role) {
                        $query->select('event_id')
                            ->from('event_role')
                            ->where('role_id', $role->id)
                            ->where('is_accepted', true);
                    })
                    ->when(! $isMemberOrAdmin, function ($q) use ($unlockedEventIds) {
                        $q->where('is_draft', false);
                        $q->where('is_cancelled', false);
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
                    ->fullyPast(Carbon::now('UTC'))
                    ->whereNull('days_of_week')
                    ->whereHas('roles', fn ($q) => $q->where('role_id', $role->id)->where('is_accepted', true))
                    ->when(! $isMemberOrAdmin, function ($q) use ($unlockedEventIds) {
                        $q->where('is_draft', false);
                        $q->where('is_cancelled', false);
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

        return $this->buildCalendarResponse($events, $pastEvents, $hasMorePastEvents, $role, $subdomain, (int) $month, (int) $year, $timezone, $firstDayOfWeek, true);
    }

    public function adminCalendarEvents(Request $request, $subdomain): JsonResponse
    {
        if (! auth()->user()->isMember($subdomain)) {
            return response()->json(['error' => 'Not authorized'], 403);
        }

        $role = Role::subdomain($subdomain)->firstOrFail();

        $month = DateUtils::normalizeMonth($request->month);
        $year = DateUtils::normalizeYear($request->year);

        $user = $request->user();
        $timezone = $user->timezone ?? $role->timezone ?? 'UTC';

        $firstDayOfWeek = $role->first_day_of_week ?? 0;
        $startOfGrid = Carbon::create($year, $month, 1, 0, 0, 0, $timezone)->startOfMonth()->startOfWeek($firstDayOfWeek);
        $startOfGridUtc = $startOfGrid->copy()->setTimezone('UTC');
        $endOfGridUtc = Carbon::create($year, $month, 1, 0, 0, 0, $timezone)->endOfMonth()->endOfWeek(($firstDayOfWeek + 6) % 7)->addDays(2)->setTimezone('UTC');

        if ($role->isCurator()) {
            $events = Event::with('roles', 'parts', 'tickets')
                ->inMonth($startOfGridUtc, $endOfGridUtc)
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
                ->inMonth($startOfGridUtc, $endOfGridUtc)
                ->orderBy('starts_at')
                ->get();
        }

        return $this->buildCalendarResponse($events, collect(), false, $role, $subdomain, (int) $month, (int) $year, $timezone, $firstDayOfWeek);
    }

    public function auditLog(Request $request, $subdomain)
    {
        if (! auth()->user()->isEditor($subdomain)) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $role = Role::subdomain($subdomain)->firstOrFail();

        $request->validate([
            'from' => 'nullable|date',
            'to' => 'nullable|date',
            'category' => 'nullable|string',
            'search' => 'nullable|string|max:200',
        ]);

        $sortBy = $request->input('sort_by', 'created_at');
        $sortDir = strtolower($request->input('sort_dir', 'desc')) === 'asc' ? 'asc' : 'desc';
        if (! in_array($sortBy, ['created_at', 'action', 'metadata', 'user_id'])) {
            $sortBy = 'created_at';
        }

        $eventIds = $role->events()->pluck('events.id');

        $query = AuditLog::with('user')
            ->where(function ($q) use ($eventIds, $role) {
                // Schedule and subscription entries for this role
                $q->where(function ($sq) use ($role) {
                    $sq->where(function ($pq) {
                        $pq->where('action', 'like', 'schedule.%')
                            ->orWhere('action', 'like', 'subscription.%');
                    })
                        ->where('model_type', 'Role')
                        ->where('model_id', $role->id);
                });

                // Boost entries for this role's campaigns
                $q->orWhere(function ($sq) use ($role) {
                    $sq->where('action', 'like', 'boost.%')
                        ->where('metadata', 'like', '%role_id:'.$role->id);
                });

                if ($eventIds->isNotEmpty()) {
                    // Event entries for this schedule's events
                    $q->orWhere(function ($sq) use ($eventIds) {
                        $sq->where('action', 'like', 'event.%')
                            ->where('model_type', 'Event')
                            ->whereIn('model_id', $eventIds);
                    });

                    // Sale entries scoped by event_id in metadata
                    $q->orWhere(function ($sq) use ($eventIds) {
                        $sq->where('action', 'like', 'sale.%')
                            ->where(function ($ssq) use ($eventIds) {
                                foreach ($eventIds as $eventId) {
                                    $ssq->orWhere('metadata', 'like', '%event_id:'.$eventId);
                                }
                            });
                    });
                }
            })
            ->orderBy($sortBy, $sortDir);

        if ($request->filled('category')) {
            $category = str_replace(['%', '_'], ['\\%', '\\_'], $request->input('category'));
            $query->where('action', 'like', $category.'.%');
        }

        if ($request->filled('from')) {
            $query->where('created_at', '>=', $request->input('from'));
        }
        if ($request->filled('to')) {
            $query->where('created_at', '<=', Carbon::parse($request->input('to'))->endOfDay());
        }

        if ($request->filled('search')) {
            $search = str_replace(['%', '_'], ['\\%', '\\_'], $request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('action', 'like', "%{$search}%")
                    ->orWhere('metadata', 'like', "%{$search}%");
            });
        }

        $logs = $query->paginate(50)->withQueryString();

        return view('role.audit-log', compact('role', 'logs', 'sortBy', 'sortDir'));
    }

    public function viewAdmin(Request $request, $subdomain, $tab = 'schedule')
    {
        if (! auth()->user()->isMember($subdomain)) {
            return redirect()->route('home')->with('error', __('messages.not_authorized'));
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
        $eventTemplates = collect();
        $unscheduled = [];
        $requests = [];
        $month = DateUtils::normalizeMonth($request->month);
        $year = DateUtils::normalizeYear($request->year);
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
            // Get timezone from user or role
            $user = $request->user();
            $timezone = $user->timezone ?? $role->timezone ?? 'UTC';

            // Calculate calendar grid start (including overflow days from previous month)
            $firstDayOfWeek = $role->first_day_of_week ?? 0;
            $startOfGrid = Carbon::create($year, $month, 1, 0, 0, 0, $timezone)->startOfMonth()->startOfWeek($firstDayOfWeek);
            $startOfGridUtc = $startOfGrid->copy()->setTimezone('UTC');
            $endOfGridUtc = Carbon::create($year, $month, 1, 0, 0, 0, $timezone)->endOfMonth()->endOfWeek(($firstDayOfWeek + 6) % 7)->addDays(2)->setTimezone('UTC');

            if ($tab == 'schedule') {
                if ($role->isCurator()) {
                    $events = Event::with('roles')
                        ->inMonth($startOfGridUtc, $endOfGridUtc)
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
                        ->inMonth($startOfGridUtc, $endOfGridUtc)
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
            $allowedFollowerSortColumns = ['name', 'email', 'pivot_created_at'];
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
        } elseif ($tab == 'templates') {
            $eventTemplates = $role->eventTemplates;
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

        $venueDuplicateGroupCount = 0;
        $timezoneMismatchEvents = collect();
        if ($tab === 'schedule' && $role->isCurator() && auth()->user()->isEditor($subdomain)) {
            $venueDuplicateGroupCount = count($this->venueDuplicateGroups($role, auth()->user()->id));
            $timezoneMismatchEvents = $this->offTimezoneEvents($role, auth()->user()->id);
        }

        return view('role/show-admin', compact(
            'subdomain',
            'role',
            'tab',
            'events',
            'eventTemplates',
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
            'venueDuplicateGroupCount',
            'timezoneMismatchEvents',
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

        $userFoundByPhone = false;
        if (! $user && ! empty($data['phone'])) {
            $user = User::where('phone', PhoneUtils::normalize($data['phone']))->first();
            if ($user) {
                $userFoundByPhone = true;
            }
        }

        if ($user && $user->isMember($subdomain)) {
            return redirect()->back()->with('error', __('messages.member_already_exists'));
        }

        // Update stub's email to match admin input so the invite goes to the right address
        // (after membership check to avoid side effects on early return)
        // Only update if the stub has no other team memberships (besides this schedule) to avoid corrupting another invite
        $emailUpdateFailed = false;
        if ($user && $userFoundByPhone && ! empty($data['phone']) && $user->isStub() && $user->email !== strtolower($data['email'])) {
            if ($user->roles()->where('roles.id', '!=', $role->id)->count() === 0) {
                try {
                    $user->email = strtolower($data['email']);
                    $user->saveQuietly();
                } catch (\Illuminate\Database\QueryException $e) {
                    report($e);
                    $user->refresh();
                    $emailUpdateFailed = true;
                }
            } else {
                $emailUpdateFailed = true;
            }
        }

        $isNewStub = false;
        $phoneAssignFailed = false;
        if (! $user) {
            $isNewStub = true;
            $user = new User;
            $user->email = strtolower($data['email']);
            $user->name = $data['name'];
            $user->password = null;
            $user->timezone = $request->user()->timezone;
            $user->language_code = $request->user()->language_code;
            // Invited members run someone else's schedule; keep them out of the
            // organizer onboarding funnel (selfhost verifies these immediately)
            $user->signup_intent = 'team';

            if (! config('app.hosted')) {
                $user->email_verified_at = now();
            }

            if (! empty($data['phone'])) {
                $user->phone = $data['phone'];
            }

            try {
                $user->save();
            } catch (\Illuminate\Database\QueryException $e) {
                if (! empty($data['phone'])) {
                    report($e);
                    $user->phone = null;
                    try {
                        $user->save();
                    } catch (\Illuminate\Database\QueryException $e2) {
                        throw $e;
                    }
                    $phoneAssignFailed = true;
                } else {
                    throw $e;
                }
            }
        } elseif (! $user->phone && ! empty($data['phone'])) {
            if (! User::where('phone', PhoneUtils::normalize($data['phone']))->where('id', '!=', $user->id)->exists()) {
                try {
                    $user->phone = $data['phone'];
                    $user->save();
                } catch (\Illuminate\Database\QueryException $e) {
                    report($e);
                    $user->refresh();
                    $phoneAssignFailed = true;
                }
            } else {
                $phoneAssignFailed = true;
            }
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

        $sendSms = $user->phone && SmsService::isConfigured() && config('app.hosted')
            && ($isNewStub || $userFoundByPhone)
            && ($isNewStub || (! $user->email_verified_at && ! $user->hasVerifiedPhone()));
        if ($sendSms) {
            $token = Str::random(40);
            Cache::put('sms_signup_'.$token, $user->phone, now()->addDays(30));
            $signupUrl = route('sign_up', ['sms_token' => $token]);
            $safeName = str_replace(["\r", "\n"], ' ', Str::limit($role->name, 60));
            $message = __('messages.sms_member_invite', ['name' => $safeName, 'url' => $signupUrl]);
            SendQueuedSms::dispatch($user->phone, $message);
        } else {
            Notification::send($user, new AddedMemberNotification($role, $user, $request->user()));
        }

        AuditService::log(AuditService::SCHEDULE_MEMBER_ADD, auth()->id(), 'Role', $role->id, null, null, $user->name);

        $redirect = redirect(route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'team']));

        if ($phoneAssignFailed) {
            return $redirect->with('warning', __('messages.member_added_phone_failed'));
        }

        if ($sendSms) {
            if ($emailUpdateFailed) {
                return $redirect->with('warning', __('messages.member_added_sms_email_not_updated'));
            }

            return $redirect->with('message', __('messages.member_added_sms_sent'));
        }

        if ($emailUpdateFailed) {
            return $redirect->with('warning', __('messages.member_added_email_failed'));
        }

        if ($userFoundByPhone && ! $user->isStub() && $user->email !== strtolower($data['email'])) {
            return $redirect->with('warning', __('messages.member_added_different_email', ['email' => $user->email]));
        }

        return $redirect->with('message', __('messages.member_added'));
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

        // Clean up member calendar sync records
        \App\Models\CalendarSync::where('user_id', $userId)
            ->where('role_id', $role->id)
            ->delete();

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

        // Get member calendar sync status for followed roles
        $memberSyncCalendarIds = \App\Models\RoleUser::where('user_id', $user->id)
            ->whereIn('role_id', $roleIds)
            ->whereNotNull('google_calendar_id')
            ->pluck('google_calendar_id', 'role_id');

        // Detect possible duplicate venues among followed roles. Group by
        // normalized name+city+country; any group with >1 unclaimed entry is
        // a candidate for the merge tool.
        $duplicateVenueCount = 0;
        $allFollowedVenues = Role::whereIn('id', $roleIds)
            ->where('type', 'venue')
            ->where('is_deleted', false)
            ->get(['id', 'name', 'city', 'country_code', 'email_verified_at', 'phone_verified_at', 'user_id']);

        $grouped = [];
        foreach ($allFollowedVenues as $v) {
            $normName = \App\Utils\GeminiUtils::normalizeForMatch($v->name);
            if ($normName === '') {
                continue;
            }
            $normCity = \App\Utils\GeminiUtils::normalizeForMatch($v->city);
            $country = $v->country_code ? strtolower($v->country_code) : '';
            $key = $normName.'|'.$normCity.'|'.$country;
            $grouped[$key][] = $v;
        }
        foreach ($grouped as $group) {
            if (count($group) > 1) {
                $duplicateVenueCount += count($group);
            }
        }

        $data = [
            'roles' => $roles,
            'sortBy' => $sortBy,
            'sortDir' => $sortDir,
            'memberSyncCalendarIds' => $memberSyncCalendarIds,
            'duplicateVenueCount' => $duplicateVenueCount,
        ];

        if (request()->ajax()) {
            return view('role/following_table', $data);
        }

        return view('role/index', $data);
    }

    public function create($type)
    {
        restore_pending_action();

        if (is_demo_mode()) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        if (config('app.hosted') && auth()->user()->owner()->count() >= 50) {
            return redirect()->route('home')->with('error', __('messages.schedule_limit'));
        }

        // Onboarding funnel stage 4 ("reached the new-schedule step"). First-touch stamp.
        // Base query builder + whereNull writes at most once and does not bump users.updated_at
        // (which the admin active-users metric keys off).
        DB::table('users')
            ->where('id', auth()->id())
            ->whereNull('schedule_form_viewed_at')
            ->update(['schedule_form_viewed_at' => now()]);

        $role = new Role;
        $role->type = $type;
        $role->require_account = $type === 'curator';
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

        if (config('app.is_testing')) {
            $role->plan_type = 'enterprise';
            $role->plan_expires = '2099-01-01';
        }

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

        if (config('app.hosted') && $user->owner()->count() >= 50) {
            return redirect()->back()->with('error', __('messages.schedule_limit'));
        }

        $role = new Role;
        $role->fill($request->all());

        // The "offer a second language" toggle maps onto the target column: when it is off (or the
        // submitted target is blank), the target equals the authored language = "no translation".
        // Mirrors update(); without it the create form's <select> submits a bogus first option.
        // Fall back to 'en' so a request that also omits language_code can't null this NOT NULL column.
        if (! $request->boolean('translation_enabled') || ! $role->translation_language_code) {
            $role->translation_language_code = $role->language_code ?: 'en';
        }

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

        if (config('app.is_testing')) {
            $role->plan_type = 'enterprise';
            $role->plan_expires = '2099-01-01';
        }

        $role->save();

        // Attach owner with calendar ID before handling sync
        $user->roles()->attach($role->id, [
            'created_at' => now(),
            'level' => 'owner',
            'google_calendar_id' => $request->input('google_calendar_id'),
            'microsoft_calendar_id' => $request->input('microsoft_calendar_id'),
        ]);

        AuditService::log(AuditService::SCHEDULE_CREATE, $user->id, 'Role', $role->id, null, null, $role->name);

        // Handle sync direction and calendar setup for new role
        $syncDirection = $request->input('sync_direction');
        $calendarId = $request->input('google_calendar_id');
        if ($syncDirection || $calendarId) {
            $this->handleSyncAndCalendarChanges($role, $syncDirection, null, $calendarId, null);
        }

        // Handle Outlook / Microsoft sync direction and calendar setup for new role
        $microsoftSyncDirection = $request->input('microsoft_sync_direction');
        $microsoftCalendarId = $request->input('microsoft_calendar_id');
        if ($microsoftSyncDirection || $microsoftCalendarId) {
            $this->handleMicrosoftSyncAndCalendarChanges($role, $microsoftSyncDirection, null, $microsoftCalendarId, null);
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
            if (! empty($groupNames) && $role->language_code !== $role->translation_language_code) {
                try {
                    $translations = GeminiUtils::translateGroupNames($groupNames, $role->language_code, $role->translation_language_code);
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
            ['new_sale' => false, 'new_request' => true, 'new_fan_content' => false, 'new_feedback' => false, 'new_poll_option' => false],
            json_decode($pivot?->notification_settings ?? '{}', true)
        );

        $eventCategoryCounts = $role->events()
            ->select('category_id', DB::raw('COUNT(*) as cnt'))
            ->whereNotNull('category_id')
            ->groupBy('category_id')
            ->pluck('cnt', 'category_id')
            ->all();

        // Merge candidates: other venues the user is an editor of, surfaced
        // when the source venue is mergeable (unclaimed). canMergeRoles also
        // accepts follower-of-unclaimed targets, but the venue-settings picker
        // stays editor-only to keep the list focused on venues the user owns.
        $mergeCandidates = collect();
        $mergeSuggestion = null;
        if ($role->isVenue() && ! $role->isClaimed()) {
            $candidateIds = auth()->user()
                ->editor()
                ->where('roles.id', '!=', $role->id)
                ->where('roles.type', 'venue')
                ->where('roles.is_deleted', false)
                ->pluck('roles.id');

            $mergeCandidates = Role::whereIn('id', $candidateIds)
                ->orderBy('name')
                ->get(['id', 'subdomain', 'name', 'city', 'country_code']);

            $normName = \App\Utils\GeminiUtils::normalizeForMatch($role->name);
            $normCity = \App\Utils\GeminiUtils::normalizeForMatch($role->city);
            $roleCountry = $role->country_code ? strtolower($role->country_code) : null;

            if ($normName !== '') {
                $mergeSuggestion = $mergeCandidates->first(function ($candidate) use ($normName, $normCity, $roleCountry) {
                    if (\App\Utils\GeminiUtils::normalizeForMatch($candidate->name) !== $normName) {
                        return false;
                    }
                    if ($normCity !== '' && \App\Utils\GeminiUtils::normalizeForMatch($candidate->city) !== $normCity) {
                        return false;
                    }
                    if ($roleCountry && $candidate->country_code && strtolower($candidate->country_code) !== $roleCountry) {
                        return false;
                    }

                    return true;
                });
            }
        }

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
            'userCalendarId' => $pivot?->google_calendar_id,
            'userMicrosoftCalendarId' => $pivot?->microsoft_calendar_id,
            'event_category_counts' => $eventCategoryCounts,
            'mergeCandidates' => $mergeCandidates,
            'mergeSuggestion' => $mergeSuggestion,
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
                'translation_language_code' => $role->translation_language_code,
                'timezone' => $role->timezone,
                'new_subdomain' => $role->subdomain,
                'custom_domain' => $role->custom_domain,
                'custom_css' => $role->custom_css,
                'google_calendar_id' => RoleUser::where('role_id', $role->id)->where('user_id', $role->user_id)->first()?->google_calendar_id,
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

        // Guard feedback settings behind Pro plan
        if (! $role->isPro()) {
            $request->merge([
                'feedback_enabled' => $role->feedback_enabled,
                'feedback_delay_hours' => $role->feedback_delay_hours,
                'feedback_public' => $role->feedback_public,
            ]);
        }

        // Guard carpool_enabled behind Pro plan
        if (! $role->isPro()) {
            $request->merge(['carpool_enabled' => $role->carpool_enabled]);
        }

        // Normalize gift card denominations (unique, positive, sorted)
        if ($request->has('gift_card_amounts')) {
            $request->merge([
                'gift_card_amounts' => collect($request->input('gift_card_amounts', []))
                    ->map(fn ($amount) => round((float) $amount, 2))
                    ->filter(fn ($amount) => $amount > 0)
                    ->unique()
                    ->sort()
                    ->values()
                    ->all(),
            ]);
        }

        // Guard gift card settings behind Pro plan
        if (! $role->isPro()) {
            $request->merge([
                'gift_cards_enabled' => $role->gift_cards_enabled,
                'gift_card_amounts' => $role->gift_card_amounts,
                'gift_card_currency_code' => $role->gift_card_currency_code,
                'gift_card_valid_days' => $role->gift_card_valid_days,
                'gift_card_payment_method' => $role->gift_card_payment_method,
            ]);
        }

        // Guard banner behind Pro plan
        if (! $role->isPro()) {
            $request->merge([
                'banner_enabled' => $role->banner_enabled,
                'banner_on_event_pages' => $role->banner_on_event_pages,
                'banner_message' => $role->banner_message,
                'banner_message_en' => $role->banner_message_en,
            ]);
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
        $ownerPivot = RoleUser::where('role_id', $role->id)->where('user_id', $role->user_id)->first();
        $oldCalendarId = $ownerPivot?->google_calendar_id;
        $newCalendarId = $request->input('google_calendar_id');

        // Outlook / Microsoft sync direction + calendar changes. The new direction is read from
        // the model AFTER fill() (below), not the raw request, so a hand-crafted POST that sets
        // the marker but omits the direction radio can't spuriously null it and tear the sub down.
        $oldMicrosoftSyncDirection = $role->microsoft_sync_direction;
        $oldMicrosoftCalendarId = $ownerPivot?->microsoft_calendar_id;
        $newMicrosoftCalendarId = $request->input('microsoft_calendar_id');

        // Capture old category state for rename detection.
        $oldEventCategories = $role->event_categories;
        $eventCategoriesSubmitted = $request->boolean('event_categories_submitted');

        $role->fill($request->all());

        // The "offer a second language" toggle maps onto the target column: when it is off (or the
        // submitted target is blank), the target equals the authored language = "no translation".
        // Skipped in demo mode, where the controls are disabled and frozen to current values above.
        // Fall back to 'en' so a request that also omits language_code can't null this NOT NULL column.
        if (! is_demo_mode() && (! $request->boolean('translation_enabled') || ! $role->translation_language_code)) {
            $role->translation_language_code = $role->language_code ?: 'en';
        }

        if ($request->has('youtube_links')) {
            $role->youtube_links = $this->sanitizeLinksJson($request->input('youtube_links'));
        }
        if ($request->has('social_links')) {
            $role->social_links = $this->sanitizeLinksJson($request->input('social_links'));
        }
        if ($request->has('payment_links')) {
            $role->payment_links = $this->sanitizeLinksJson($request->input('payment_links'));
        }

        // Save calendar ID to owner's pivot
        if ($oldCalendarId !== $newCalendarId) {
            $ownerPivot?->update(['google_calendar_id' => $newCalendarId ?: null]);
        }

        // If sync_direction or calendar changed, handle webhook management
        if (($newSyncDirection && $oldSyncDirection !== $newSyncDirection) ||
            ($oldCalendarId !== $newCalendarId)) {
            $this->handleSyncAndCalendarChanges($role, $newSyncDirection, $oldSyncDirection, $newCalendarId, $oldCalendarId);
        }

        // The whole Outlook tab (Teams toggle, calendar select, direction radios) only renders
        // for the connected owner. Gate every Outlook write on the hidden marker so a save that
        // did NOT render the tab (a non-owner editor, or the owner while disconnected) cannot
        // clobber the owner's calendar selection or tear down their Graph subscription with the
        // wrong account's token. NOTE: microsoft_sync_direction is fillable, so when the tab is
        // absent fill() simply leaves it unchanged.
        if ($request->has('microsoft_integration_submitted')) {
            $role->microsoft_create_teams_meetings = $request->boolean('microsoft_create_teams_meetings');

            // Read the new direction from the model post-fill (fillable), not the raw request.
            $newMicrosoftSyncDirection = $role->microsoft_sync_direction;

            // Save Outlook calendar ID to owner's pivot
            if ($oldMicrosoftCalendarId !== $newMicrosoftCalendarId) {
                $ownerPivot?->update(['microsoft_calendar_id' => $newMicrosoftCalendarId ?: null]);
            }

            // Handle subscription management whenever the direction (incl. to "no sync") or the
            // calendar changed. The direction condition must NOT require a truthy new value, or
            // switching to "no sync" would leave the subscription live until it auto-expires.
            if (($oldMicrosoftSyncDirection !== $newMicrosoftSyncDirection) ||
                ($oldMicrosoftCalendarId !== $newMicrosoftCalendarId)) {
                $this->handleMicrosoftSyncAndCalendarChanges($role, $newMicrosoftSyncDirection, $oldMicrosoftSyncDirection, $newMicrosoftCalendarId, $oldMicrosoftCalendarId);
            }
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
                    'private' => ! empty($fieldData['private']),
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
                } elseif ($role->offersTranslation()) {
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
                        $role->language_code,
                        $role->translation_language_code
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
                } elseif ($role->offersTranslation()) {
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

        // Normalise event_categories: ensure stable structure, drop legacy `disabled`
        // entries from the v1 phase, sort alphabetically by name, auto-null orphaned default_category_id.
        if ($eventCategoriesSubmitted) {
            $submitted = $request->input('event_categories', []);
            if (! is_array($submitted)) {
                $submitted = [];
            }

            // Belt-and-braces: prevent a submitted custom id (>= 100) from colliding with an id
            // that was previously used under this role but is no longer in the saved JSON. The
            // Blade `data-next-id` is set from `nextCustomCategoryId()` which already considers
            // history, but a stale client tab or hand-crafted POST could still send a colliding id.
            $previousJsonIds = collect($oldEventCategories ?? [])->pluck('id')->filter()->all();
            $historicalCustomIds = \App\Models\Event::where('creator_role_id', $role->id)
                ->where('category_id', '>=', 100)
                ->distinct()
                ->pluck('category_id')
                ->all();
            $reservedIds = array_unique(array_merge($previousJsonIds, $historicalCustomIds));
            $nextSafeId = (int) max([99, ...$reservedIds]) + 1;
            $usedInPayload = [];

            // Lookup of the OLD entries by id, used to preserve `name_en` across saves when the
            // source `name` is unchanged. Skip any legacy `disabled` entries.
            $oldEntriesById = [];
            foreach (($oldEventCategories ?? []) as $old) {
                if (is_array($old) && isset($old['id']) && empty($old['disabled'])) {
                    $oldEntriesById[(int) $old['id']] = $old;
                }
            }

            $normalised = [];
            foreach ($submitted as $entry) {
                if (! is_array($entry) || ! isset($entry['id'], $entry['name'])) {
                    continue;
                }
                // Backward-compat: legacy v1 entries marked disabled are treated as removed.
                if (! empty($entry['disabled'])) {
                    continue;
                }
                $id = (int) $entry['id'];
                $name = trim((string) $entry['name']);
                if ($name === '') {
                    continue;
                }

                // Reassign any custom id that would collide with a historical id not in the payload's
                // previously-saved list, or with another payload entry's id.
                if ($id >= 100 && ! in_array($id, $previousJsonIds, true)) {
                    while (in_array($nextSafeId, $usedInPayload, true) || in_array($nextSafeId, $historicalCustomIds, true)) {
                        $nextSafeId++;
                    }
                    if (in_array($id, $historicalCustomIds, true)) {
                        $id = $nextSafeId;
                        $nextSafeId++;
                    }
                }
                $usedInPayload[] = $id;

                $row = ['id' => $id, 'name' => $name];
                if (array_key_exists('name_en', $entry)) {
                    // Form submitted name_en (possibly empty) — user has explicit control.
                    // Empty = clear so Translate.php regenerates next nightly run.
                    $nameEn = trim((string) $entry['name_en']);
                    if ($nameEn !== '') {
                        $row['name_en'] = $nameEn;
                    }
                } elseif (isset($oldEntriesById[$id]) && ($oldEntriesById[$id]['name'] ?? null) === $name) {
                    // No name_en key submitted (e.g. English-only schedule whose UI hides the field, or API client).
                    // Source name unchanged — preserve the existing English translation.
                    if (! empty($oldEntriesById[$id]['name_en'])) {
                        $row['name_en'] = $oldEntriesById[$id]['name_en'];
                    }
                }
                // else: rename or brand-new with no submitted name_en — leave absent so Translate regenerates it.

                // Color is independent of name_en: an empty/missing color in the submitted payload
                // means the user cleared it, so do NOT inherit from $oldEntriesById.
                $color = trim((string) ($entry['color'] ?? ''));
                if ($color !== '' && preg_match('/^#[0-9A-Fa-f]{6}$/', $color)) {
                    $row['color'] = $color;
                }

                $normalised[] = $row;
            }
            usort($normalised, fn ($a, $b) => strcasecmp($a['name'], $b['name']));
            $role->event_categories = $normalised ?: null;

            // If the default_category_id is no longer present in the list, auto-null with a flash.
            if ($role->default_category_id) {
                $enabledIds = collect($role->getEventCategories())->pluck('id')->all();
                if (! in_array((int) $role->default_category_id, $enabledIds, true)) {
                    $role->default_category_id = null;
                    session()->flash('warning', __('messages.default_category_disabled_warning'));
                }
            }
        }

        $role->save();

        // Bulk-update events.category_name for renames (always — no toggle) and
        // emit audit log entries for adds / renames / removes.
        if ($eventCategoriesSubmitted) {
            $oldMap = [];
            foreach (($oldEventCategories ?? []) as $entry) {
                if (is_array($entry) && isset($entry['id']) && empty($entry['disabled'])) {
                    $oldMap[(int) $entry['id']] = $entry;
                }
            }
            $newMap = [];
            foreach (($role->event_categories ?? []) as $entry) {
                if (is_array($entry) && isset($entry['id'])) {
                    $newMap[(int) $entry['id']] = $entry;
                }
            }

            $renamedEventCount = 0;
            foreach ($newMap as $id => $entry) {
                $old = $oldMap[$id] ?? null;
                $name = $entry['name'] ?? null;
                if (! $old) {
                    \App\Services\AuditService::log('category.added', auth()->id(), 'Role', $role->id, null, ['id' => $id, 'name' => $name]);
                } elseif (($old['name'] ?? null) !== $name) {
                    $renamedEventCount += \App\Models\Event::where('creator_role_id', $role->id)
                        ->where('category_id', $id)
                        ->update(['category_name' => $name]);
                    \App\Services\AuditService::log('category.renamed', auth()->id(), 'Role', $role->id, ['name' => $old['name'] ?? null], ['name' => $name]);
                }
            }
            foreach ($oldMap as $id => $entry) {
                if (! isset($newMap[$id])) {
                    \App\Services\AuditService::log('category.removed', auth()->id(), 'Role', $role->id, ['id' => $id, 'name' => $entry['name'] ?? null], null);
                }
            }

            if ($renamedEventCount > 0) {
                session()->flash('message', __('messages.categories_updated_with_events', ['count' => $renamedEventCount]));
            }
        }

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
        if (! empty($newGroupNames) && $role->language_code !== $role->translation_language_code) {
            try {
                $translations = GeminiUtils::translateGroupNames($newGroupNames, $role->language_code, $role->translation_language_code);
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

            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

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
        $textElements = array_diff($request->input('elements', []), $imageElements);

        if (! empty($textElements) && ! $role->canMakeAiContentRequest()) {
            return response()->json(['error' => __('messages.ai_text_daily_limit_reached', ['limit' => $role->aiContentDailyLimit()])], 422);
        }

        if (! empty($imageElements) && ! $role->canGenerateAiImage()) {
            return response()->json(['error' => __('messages.ai_daily_limit_reached', ['limit' => $role->aiImageDailyLimit()])], 422);
        }

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

            // Track image generations separately for daily limit enforcement
            if (! empty($imageElements) && (isset($results['profile_image']) || isset($results['header_image']) || isset($results['background_image']))) {
                UsageTrackingService::track(UsageTrackingService::GEMINI_GENERATE_STYLE_IMAGE, $role->id);
            }

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
                    } elseif (in_array(config('filesystems.default'), ['local', 'public'])) {
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
        if (! auth()->user()->isEditor($subdomain)) {
            return response()->json(['error' => __('messages.not_authorized')], 403);
        }

        $role = Role::subdomain($subdomain)->firstOrFail();

        if (! $role->isEnterprise()) {
            return response()->json(['error' => __('messages.not_authorized')], 403);
        }

        if (! $role->canGenerateAiImage()) {
            return response()->json(['error' => __('messages.ai_daily_limit_reached', ['limit' => $role->aiImageDailyLimit()])], 422);
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

        $requestId = Str::uuid()->toString();
        Cache::put("ai_style_image_{$requestId}", ['status' => 'processing'], 300);

        $roleId = $role->id;

        dispatch(function () use ($requestId, $role, $imageType, $accentColor, $styleInstructions, $customPrompt, $roleId) {
            set_time_limit(120);

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
                    Cache::put("ai_style_image_{$requestId}", ['status' => 'failed'], 300);

                    return;
                }

                $prefix = str_replace('_image', '_', $imageType);
                $filename = ImageUtils::saveImageData($imageData, 'generated_style.png', $prefix);

                $result = ['status' => 'completed', 'success' => true];
                if (config('app.hosted') && config('filesystems.default') == 'do_spaces') {
                    $result[$imageType.'_url'] = 'https://eventschedule.nyc3.cdn.digitaloceanspaces.com/'.$filename;
                } elseif (in_array(config('filesystems.default'), ['local', 'public'])) {
                    $result[$imageType.'_url'] = url('/storage/'.$filename);
                } else {
                    $result[$imageType.'_url'] = $filename;
                }
                $result[$imageType.'_filename'] = $filename;

                UsageTrackingService::track(UsageTrackingService::GEMINI_GENERATE_STYLE_IMAGE, $roleId);

                Cache::put("ai_style_image_{$requestId}", $result, 300);
            } catch (\App\Exceptions\ContentModerationException $e) {
                Cache::put("ai_style_image_{$requestId}", ['status' => 'failed', 'error' => __('messages.ai_content_moderation_blocked')], 300);
            } catch (\Exception $e) {
                \Log::error('AI style image generation failed: '.$e->getMessage(), ['role_id' => $roleId]);
                report($e);
                Cache::put("ai_style_image_{$requestId}", ['status' => 'failed'], 300);
            }
        })->afterResponse();

        return response()->json(['request_id' => $requestId]);
    }

    public function pollStyleImage($subdomain, $requestId)
    {
        if (! auth()->check() || ! auth()->user()->isEditor($subdomain)) {
            return response()->json(['error' => __('messages.not_authorized')], 403);
        }

        $data = Cache::get("ai_style_image_{$requestId}");

        if (! $data) {
            return response()->json(['status' => 'not_found'], 404);
        }

        return response()->json($data);
    }

    public function generateStyleImageNew(Request $request)
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

        $requestId = Str::uuid()->toString();
        Cache::put("ai_style_image_{$requestId}", ['status' => 'processing'], 300);

        dispatch(function () use ($requestId, $tempRole, $imageType, $accentColor, $styleInstructions, $customPrompt) {
            set_time_limit(120);

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
                    Cache::put("ai_style_image_{$requestId}", ['status' => 'failed'], 300);

                    return;
                }

                $prefix = str_replace('_image', '_', $imageType);
                $filename = ImageUtils::saveImageData($imageData, 'generated_style.png', $prefix);

                $result = ['status' => 'completed', 'success' => true];
                if (config('app.hosted') && config('filesystems.default') == 'do_spaces') {
                    $result[$imageType.'_url'] = 'https://eventschedule.nyc3.cdn.digitaloceanspaces.com/'.$filename;
                } elseif (in_array(config('filesystems.default'), ['local', 'public'])) {
                    $result[$imageType.'_url'] = url('/storage/'.$filename);
                } else {
                    $result[$imageType.'_url'] = $filename;
                }
                $result[$imageType.'_filename'] = $filename;

                Cache::put("ai_style_image_{$requestId}", $result, 300);
            } catch (\App\Exceptions\ContentModerationException $e) {
                Cache::put("ai_style_image_{$requestId}", ['status' => 'failed', 'error' => __('messages.ai_content_moderation_blocked')], 300);
            } catch (\Exception $e) {
                \Log::error('AI style image generation failed: '.$e->getMessage());
                report($e);
                Cache::put("ai_style_image_{$requestId}", ['status' => 'failed'], 300);
            }
        })->afterResponse();

        return response()->json(['request_id' => $requestId]);
    }

    public function pollStyleImageNew($requestId)
    {
        if (! auth()->check()) {
            return response()->json(['error' => __('messages.not_authorized')], 403);
        }

        $data = Cache::get("ai_style_image_{$requestId}");

        if (! $data) {
            return response()->json(['status' => 'not_found'], 404);
        }

        return response()->json($data);
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

        if (! $role->canMakeAiContentRequest()) {
            return response()->json(['error' => __('messages.ai_text_daily_limit_reached', ['limit' => $role->aiContentDailyLimit()])], 422);
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
                    } elseif (in_array(config('filesystems.default'), ['local', 'public'])) {
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

    private function sanitizeLinksJson($value)
    {
        $links = json_decode($value ?? '');

        if (! is_array($links)) {
            return null;
        }

        $links = array_values(array_filter($links, function ($link) {
            return $link && isset($link->url) && $link->url !== '';
        }));

        return $links ? json_encode($links) : null;
    }

    public function previewLink(Request $request, $subdomain): JsonResponse
    {
        if (! auth()->user()->isEditor($subdomain)) {
            return response()->json(['error' => __('messages.not_authorized')], 403);
        }

        $request->validate(['url' => 'required|string|url|max:1000']);

        $urlInfo = UrlUtils::getUrlInfo($request->url);
        if ($urlInfo === null) {
            return response()->json(['error' => __('messages.invalid_url')], 422);
        }

        $urlInfo->brand = UrlUtils::getBrand($urlInfo->url);
        $urlInfo->clean_url = UrlUtils::clean($urlInfo->url);
        $urlInfo->platform = UrlUtils::detectPlatform($urlInfo->url);

        return response()->json($urlInfo);
    }

    public function qrCode($subdomain)
    {
        $role = Role::subdomain($subdomain)->firstOrFail();
        $url = $role->getGuestUrl(true) ?: $role->getGuestUrl();

        return response(QrCodeUtils::png($url, 300))
            ->header('Content-Type', 'image/png')
            ->header('Content-Disposition', 'attachment; filename="qr-code.png"');
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

        // Talent schedules always use the booking form (require_account does not apply)
        if ($role->isTalent()) {
            return redirect(route('event.booking_request', ['subdomain' => $role->subdomain]));
        }

        // require_account=true → structured single-page submission (redirected to the canonical
        // subdomain on custom domains) or the bridged 3-step path (booking-form curators).
        if ($role->require_account) {
            $user = auth()->user();

            // Editors of this schedule go straight to AP add-event for this schedule
            if ($user && $user->isEditor($subdomain)) {
                return redirect(app_url(route('event.create', ['subdomain' => $role->subdomain], false)));
            }

            // Structured single page: collect account + schedule + event together. On a custom
            // domain, redirect to the schedule's canonical {subdomain}.eventschedule.com page so the
            // inline login and the post-submit dashboard share the .eventschedule.com cookie (an
            // in-place login on the custom domain can't set a cookie the app subdomain reads).
            // Booking-form curators use a different form and fall through to the bridged path below.
            if (! $role->usesBookingForm()) {
                if ($request->attributes->get('custom_domain_host')) {
                    // Skip ResolveCustomDomain's Location rewrite so the redirect actually leaves the
                    // custom domain, and forward the chosen language (the host-only custom-domain
                    // session cookie won't carry to the subdomain).
                    $request->attributes->set('skip_location_rewrite', true);

                    // Honor an explicit valid ?lang= (e.g. a shared localized link) so it is not
                    // dropped on the hop to the subdomain; otherwise fall back to the session
                    // translate flag or the schedule's own language. Matches the plain-subdomain
                    // branch below, which also forwards the requested language.
                    $lang = is_string($request->lang) && is_valid_language_code($request->lang)
                        ? $request->lang
                        : (session()->has('translate') ? $role->translation_language_code : $role->language_code);

                    return redirect(route('event.guest_submit', [
                        'subdomain' => $role->subdomain,
                        'lang' => $lang,
                    ]));
                }

                if (! $user) {
                    session()->put('pending_request', $subdomain);
                }

                // Forward a valid ?lang= (e.g. from a shared localized guest-add link bounced
                // through here) so the submit page renders in the language the link promised.
                $lang = is_string($request->lang) && is_valid_language_code($request->lang)
                    ? $request->lang
                    : null;

                return redirect(route('event.guest_submit', array_filter([
                    'subdomain' => $role->subdomain,
                    'lang' => $lang,
                ])));
            }

            if (! $user) {
                $lang = session()->has('translate') ? $role->translation_language_code : $role->language_code;

                return redirect_with_pending_action(
                    app_url(route('sign_up', ['lang' => $lang], false)),
                    [
                        'pending_request' => $subdomain,
                        'pending_request_allow_guest' => false,
                        'pending_request_form' => 'import',
                    ]
                );
            }

            // Prevent demo account from following other roles
            if (! DemoService::isDemoUser($user) && ! $user->isConnected($subdomain)) {
                $user->roles()->attach($role->id, ['level' => 'follower', 'created_at' => now()]);
            }

            $pendingData = [
                'pending_request' => $subdomain,
                'pending_request_allow_guest' => false,
                'pending_request_form' => 'import',
            ];

            // Requesting a venue/curator - need a talent schedule
            if ($user->talents()->count() == 0) {
                return redirect_with_pending_action(
                    app_url(route('new', ['type' => 'talent'], false)),
                    $pendingData
                );
            }
            $redirectRole = $user->talents()->first();

            return redirect_with_pending_action(
                app_url(route('event.create', ['subdomain' => $redirectRole->subdomain], false)),
                $pendingData
            );
        }

        // require_account=false → guest booking form OR guest AI-import form
        if ($role->usesBookingForm()) {
            return redirect(route('event.booking_request', ['subdomain' => $role->subdomain]));
        }

        return redirect(route('event.guest_import', ['subdomain' => $role->subdomain]));
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
        $role = Role::subdomain($subdomain)->firstOrFail();

        $this->authorize('manageMembers', $role);
        $userId = UrlUtils::decodeId($hash);
        $user = User::findOrFail($userId);

        // Verify the user is actually a member of this schedule
        if (! $user->roles()->where('roles.id', $role->id)->exists()) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        // Don't resend if user has already signed up
        if (! $user->isStub()) {
            return redirect()->back()->with('error', __('messages.member_already_signed_up'));
        }

        // Send SMS if user has phone and SMS is configured, otherwise send email
        if ($request->input('via') === 'sms' && $user->phone && SmsService::isConfigured() && config('app.hosted')) {
            $token = Str::random(40);
            Cache::put('sms_signup_'.$token, $user->phone, now()->addDays(30));
            $signupUrl = route('sign_up', ['sms_token' => $token]);
            $safeName = str_replace(["\r", "\n"], ' ', Str::limit($role->name, 60));
            $message = __('messages.sms_member_invite', ['name' => $safeName, 'url' => $signupUrl]);
            SendQueuedSms::dispatch($user->phone, $message);

            return redirect()->back()->with('message', __('messages.invite_resent'));
        } else {
            Notification::send($user, new AddedMemberNotification($role, $user, $request->user()));
        }

        return redirect()->back()->with('message', __('messages.invite_resent'));
    }

    public function search(Request $request)
    {
        $type = $request->type;
        $search = $request->search;

        $normalizedPhone = PhoneUtils::normalize($search);

        $roles = Role::whereIn('type', $type == 'venue' ? ['venue'] : ['talent'])
            ->where(function ($query) use ($search, $normalizedPhone) {
                $query->where('email', '=', $search)
                    ->orWhere('phone', '=', $normalizedPhone ?: $search);
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
                'phone',
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

        // Filter draft and private events for non-members
        $user = auth()->user();
        $isMemberOrAdmin = $user && ($user->isMember($subdomain) || $user->isAdmin());
        if (! $isMemberOrAdmin) {
            $unlockedEventIds = $this->getUnlockedEventIds();
            $events = $events->filter(fn ($e) => ! $e->is_draft && (! $e->is_private || in_array($e->id, $unlockedEventIds)));
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
                'category_name' => $event->resolveCategoryName(),
                'category_color' => $event->resolveCategoryColor(),
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

    public function updateAllSlugs(Request $request, $subdomain)
    {
        if (! auth()->user()->isEditor($subdomain)) {
            return response()->json(['success' => false, 'message' => __('messages.not_authorized')], 403);
        }

        $role = Role::subdomain($subdomain)->firstOrFail();
        $pattern = $request->input('slug_pattern', '');

        // Save the pattern to the role so future events use it too
        $role->slug_pattern = $pattern;
        $role->save();

        $events = $role->events()->with('roles')->get();
        $count = 0;
        $usedSlugs = [];

        foreach ($events as $event) {
            $venue = $event->venue;
            $newSlug = SlugPatternUtils::generateSlug(
                $pattern,
                $event->name,
                $event->name_en,
                $event,
                $role,
                $venue
            );

            // Ensure slug uniqueness within this role
            if (isset($usedSlugs[$newSlug])) {
                $suffix = 2;
                while (isset($usedSlugs[$newSlug.'-'.$suffix])) {
                    $suffix++;
                }
                $newSlug = $newSlug.'-'.$suffix;
            }
            $usedSlugs[$newSlug] = true;

            if ($newSlug !== $event->slug) {
                $event->slug = $newSlug;
                $event->save();
                $count++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => __('messages.events_slugs_updated', ['count' => $count]),
        ]);
    }

    public function updateAllCategories(Request $request, $subdomain)
    {
        if (! auth()->user()->isEditor($subdomain)) {
            return response()->json(['success' => false, 'message' => __('messages.not_authorized')], 403);
        }

        $role = Role::subdomain($subdomain)->firstOrFail();

        $categoryId = (int) $request->input('category_id');
        $validCategories = collect($role->getEventCategories())->pluck('id')->all();

        if (! in_array($categoryId, $validCategories, true)) {
            return response()->json(['success' => false, 'message' => __('messages.invalid_value')], 422);
        }

        $categoryName = $role->getCategoryName($categoryId);
        $eventIds = $role->events()->pluck('events.id');
        $count = Event::whereIn('id', $eventIds)->update([
            'category_id' => $categoryId,
            'category_name' => $categoryName,
        ]);

        return response()->json([
            'success' => true,
            'message' => __('messages.events_category_updated', ['count' => $count]),
        ]);
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
            ->upcomingOrOngoing()
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
                'message' => __('messages.error_searching_videos'),
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
                    'message' => __('messages.no_videos_found'),
                ]);
            }

            return response()->json([
                'success' => true,
                'videos' => $videos,
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => __('messages.error_searching_videos'),
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
     * Handle Outlook / Microsoft sync direction and calendar changes and Graph subscription management
     */
    private function handleMicrosoftSyncAndCalendarChanges($role, $newSyncDirection, $oldSyncDirection, $newCalendarId = null, $oldCalendarId = null)
    {
        $user = auth()->user();

        if (! $user->microsoft_token) {
            return; // No Outlook Calendar connected, skip subscription management
        }

        try {
            $microsoftCalendarService = app(\App\Services\MicrosoftCalendarService::class);

            // A deltaLink is scoped to a calendar, so a calendar switch invalidates the stored token.
            // forceFill: system-managed columns kept out of $fillable.
            if ($oldCalendarId !== $newCalendarId) {
                $role->forceFill(['microsoft_sync_token' => null, 'microsoft_last_sync_at' => null])->save();
            }

            $shouldRemoveOldSubscription = ($oldCalendarId !== $newCalendarId) ||
                                    ($oldSyncDirection !== $newSyncDirection &&
                                     ($oldSyncDirection === 'from' || $oldSyncDirection === 'both'));

            if ($shouldRemoveOldSubscription && $role->microsoft_webhook_id) {
                if ($microsoftCalendarService->ensureValidToken($user)) {
                    $microsoftCalendarService->deleteSubscription($user, $role->microsoft_webhook_id);
                }

                $role->forceFill([
                    'microsoft_webhook_id' => null,
                    'microsoft_webhook_expires_at' => null,
                ])->save();
            }

            if ($newSyncDirection === 'from' || $newSyncDirection === 'both') {
                if (! $role->hasActiveMicrosoftWebhook()) {
                    if (! $microsoftCalendarService->ensureValidToken($user)) {
                        \Log::warning('Outlook Calendar token invalid and refresh failed during sync direction change', [
                            'user_id' => $user->id,
                            'role_id' => $role->id,
                        ]);

                        return;
                    }

                    $webhook = $microsoftCalendarService->createSubscription(
                        $user,
                        $role->getMicrosoftCalendarId(),
                        route('microsoft.calendar.webhook.handle')
                    );

                    $role->forceFill([
                        'microsoft_webhook_id' => $webhook['id'],
                        'microsoft_webhook_expires_at' => $webhook['expiration'] ? \Carbon\Carbon::parse($webhook['expiration']) : null,
                    ])->save();
                }
            }

        } catch (\Exception $e) {
            \Log::error('Failed to handle Outlook sync and calendar changes', [
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
        } catch (\Symfony\Component\Mailer\Exception\ExceptionInterface $e) {
            // The SMTP provider rejected the connection, credentials, or sender. Its message is the
            // actionable content (e.g. "Permission denied", authentication failed, unverified
            // sender), and we already surface this same message in the AP failure banner and the
            // owner notification (see RoleMailerService::markFailed), so show it here too. This is
            // an expected user-config failure, so it is not reported to Sentry.
            return response()->json([
                'error' => __('messages.failed_to_send_test_email'),
                'details' => mb_substr($e->getMessage(), 0, 1000),
            ], 500);
        } catch (\Exception $e) {
            // Log the full error server-side but return generic message to user
            \Log::error('Test email failed: '.$e->getMessage(), [
                'role_id' => $role->id,
                'email' => $email,
            ]);

            return response()->json([
                'error' => __('messages.failed_to_send_test_email'),
            ], 500);
        }
    }

    public function testFeedbackEmail(Request $request, $subdomain): JsonResponse
    {
        $user = auth()->user();

        if (! $user->isEditor($subdomain)) {
            return response()->json(['error' => __('messages.not_authorized')], 403);
        }

        $role = Role::subdomain($subdomain)->firstOrFail();

        if (! $role->isPro() || ! $role->feedback_enabled) {
            return response()->json(['error' => __('messages.not_authorized')], 403);
        }

        if (empty($user->email)) {
            return response()->json(['error' => __('messages.email_required')], 400);
        }

        $rateLimitKey = 'test-feedback-'.$role->id;
        if (RateLimiter::tooManyAttempts($rateLimitKey, 3)) {
            return response()->json(['error' => __('messages.please_wait').'...'], 429);
        }

        $event = Event::where('creator_role_id', $role->id)->orderByDesc('id')->first();
        if (! $event) {
            return response()->json(['error' => __('messages.feedback_test_no_events')], 400);
        }

        $fakeSale = new Sale([
            'name' => $user->name ?? 'Test Attendee',
            'email' => $user->email,
            'secret' => Str::random(32),
            'event_id' => $event->id,
            'event_date' => $event->saleEventDateFromStartsAt() ?? $event->scheduleToday(),
            'subdomain' => $role->subdomain,
            'status' => 'paid',
        ]);

        try {
            $mailable = new FeedbackRequest($fakeSale, $event, $role);

            if (config('app.hosted') && $role->hasEmailSettings()) {
                $emailSettings = $role->getEmailSettings();
                $mailerName = 'role_'.$role->id;
                Config::set("mail.mailers.{$mailerName}", [
                    'transport' => 'smtp',
                    'host' => $emailSettings['host'] ?? config('mail.mailers.smtp.host'),
                    'port' => $emailSettings['port'] ?? config('mail.mailers.smtp.port'),
                    'encryption' => $emailSettings['encryption'] ?? config('mail.mailers.smtp.encryption'),
                    'username' => $emailSettings['username'] ?? null,
                    'password' => $emailSettings['password'] ?? null,
                    'timeout' => null,
                    'local_domain' => config('mail.mailers.smtp.local_domain'),
                ]);
                Mail::mailer($mailerName)->to($user->email)->send($mailable);
            } else {
                Mail::to($user->email)->send($mailable);
            }

            RateLimiter::hit($rateLimitKey, 60);

            return response()->json(['success' => true, 'message' => __('messages.test_email_sent_to', ['email' => $user->email])]);
        } catch (\Symfony\Component\Mailer\Exception\ExceptionInterface $e) {
            // The SMTP provider rejected the message; surface its actionable message just like the
            // Email Settings test button. Expected user-config failure, so we log at warning level
            // for visibility but do not report() it to Sentry.
            \Log::warning('Test feedback email failed: '.$e->getMessage(), ['role_id' => $role->id]);
            RateLimiter::hit($rateLimitKey, 60);

            return response()->json([
                'error' => __('messages.test_email_failed'),
                'details' => mb_substr($e->getMessage(), 0, 1000),
            ], 500);
        } catch (\Exception $e) {
            report($e);
            RateLimiter::hit($rateLimitKey, 60);

            return response()->json(['error' => __('messages.test_email_failed')], 500);
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
