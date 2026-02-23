<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AnalyticsAppearancesDaily;
use App\Models\AnalyticsDaily;
use App\Models\AnalyticsReferrersDaily;
use App\Models\BoostCampaign;
use App\Models\Role;
use App\Notifications\DeletedRoleNotification;
use App\Services\AuditService;
use App\Services\BoostBillingService;
use App\Services\MetaAdsService;
use App\Utils\ColorUtils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ApiScheduleController extends Controller
{
    protected const MAX_PER_PAGE = 500;

    protected const DEFAULT_PER_PAGE = 100;

    public function index(Request $request)
    {
        $perPage = min(
            (int) $request->input('per_page', self::DEFAULT_PER_PAGE),
            self::MAX_PER_PAGE
        );

        $schedules = auth()->user()->roles()
            ->with('groups')
            ->wherePivotIn('level', ['owner', 'admin'])
            ->where('is_deleted', false)
            ->wherePro();

        if ($request->has('subdomain')) {
            $schedules->where('subdomain', $request->subdomain);
        }

        if ($request->has('name')) {
            $name = str_replace(['%', '_'], ['\\%', '\\_'], $request->name);
            $schedules->where('name', 'like', '%'.$name.'%');
        }

        if ($request->has('type')) {
            $schedules->where('type', $request->type);
        }

        $schedules = $schedules->paginate($perPage);

        return response()->json([
            'data' => collect($schedules->items())->map(function ($schedule) {
                return $schedule->toApiData();
            })->values(),
            'meta' => [
                'current_page' => $schedules->currentPage(),
                'from' => $schedules->firstItem(),
                'last_page' => $schedules->lastPage(),
                'per_page' => $schedules->perPage(),
                'to' => $schedules->lastItem(),
                'total' => $schedules->total(),
                'path' => $request->url(),
            ],
        ], 200, [], JSON_PRETTY_PRINT);
    }

    public function show(Request $request, $subdomain)
    {
        $role = auth()->user()->roles()
            ->with('groups')
            ->where('subdomain', $subdomain)
            ->where('is_deleted', false)
            ->wherePivotIn('level', ['owner', 'admin'])
            ->first();

        if (! $role) {
            return response()->json(['error' => 'Schedule not found'], 404);
        }

        if (! $role->isPro()) {
            return response()->json(['error' => 'API usage is limited to Pro accounts'], 403);
        }

        return response()->json([
            'data' => $role->toApiData(),
        ], 200, [], JSON_PRETTY_PRINT);
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'type' => 'required|string|in:venue,talent,curator',
                'email' => 'nullable|email|max:255',
                'description' => 'nullable|string|max:10000',
                'short_description' => 'nullable|string|max:200',
                'timezone' => 'nullable|string|max:100',
                'language_code' => 'nullable|string|in:'.implode(',', config('app.supported_languages', ['en'])),
                'website' => 'nullable|string|max:255',
                'address1' => 'nullable|string|max:255',
                'city' => 'nullable|string|max:255',
                'state' => 'nullable|string|max:255',
                'postal_code' => 'nullable|string|max:20',
                'country_code' => 'nullable|string|max:10',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }

        $user = auth()->user();

        if (config('app.hosted') && $user->roles()->where('is_deleted', false)->count() >= 100) {
            return response()->json(['error' => 'Maximum number of schedules reached'], 422);
        }

        $role = new Role;
        $role->fill($request->only([
            'name', 'type', 'email', 'description', 'short_description',
            'timezone', 'language_code', 'website', 'address1', 'city',
            'state', 'postal_code', 'country_code',
        ]));

        if (! $role->timezone) {
            $role->timezone = $user->timezone;
        }
        if (! $role->language_code) {
            $role->language_code = $user->language_code ?? 'en';
        }

        $role->subdomain = Role::generateSubdomain($request->name);
        $role->user_id = $user->id;
        $role->background_colors = ColorUtils::randomGradient();

        // Handle email verification
        if (! config('app.hosted')) {
            $role->email_verified_at = now();
        } elseif ($role->email == $user->email) {
            $role->email_verified_at = $user->email_verified_at;
        }

        $role->save();

        $user->roles()->attach($role->id, ['created_at' => now(), 'level' => 'owner']);

        AuditService::log(AuditService::SCHEDULE_CREATE, $user->id, 'Role', $role->id, null, null, $role->name);

        $role->load('groups');

        return response()->json([
            'data' => $role->toApiData(),
        ], 201, [], JSON_PRETTY_PRINT);
    }

    public function update(Request $request, $subdomain)
    {
        $role = auth()->user()->roles()
            ->where('subdomain', $subdomain)
            ->where('is_deleted', false)
            ->wherePivotIn('level', ['owner', 'admin'])
            ->first();

        if (! $role) {
            return response()->json(['error' => 'Schedule not found'], 404);
        }

        if (! $role->isPro()) {
            return response()->json(['error' => 'API usage is limited to Pro accounts'], 403);
        }

        try {
            $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'email' => 'nullable|email|max:255',
                'description' => 'nullable|string|max:10000',
                'short_description' => 'nullable|string|max:200',
                'timezone' => 'nullable|string|max:100',
                'language_code' => 'nullable|string|in:'.implode(',', config('app.supported_languages', ['en'])),
                'website' => 'nullable|string|max:255',
                'address1' => 'nullable|string|max:255',
                'city' => 'nullable|string|max:255',
                'state' => 'nullable|string|max:255',
                'postal_code' => 'nullable|string|max:20',
                'country_code' => 'nullable|string|max:10',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }

        $role->fill($request->only([
            'name', 'email', 'description', 'short_description',
            'timezone', 'language_code', 'website', 'address1', 'city',
            'state', 'postal_code', 'country_code',
        ]));
        $role->save();

        AuditService::log(AuditService::SCHEDULE_UPDATE, auth()->id(), 'Role', $role->id, null, null, $role->name);

        $role->load('groups');

        return response()->json([
            'data' => $role->toApiData(),
        ], 200, [], JSON_PRETTY_PRINT);
    }

    public function destroy(Request $request, $subdomain)
    {
        $user = auth()->user();

        $role = $user->roles()
            ->where('subdomain', $subdomain)
            ->where('is_deleted', false)
            ->wherePivotIn('level', ['owner'])
            ->first();

        if (! $role) {
            return response()->json(['error' => 'Schedule not found'], 404);
        }

        // Clean up images
        foreach (['profile_image_url', 'header_image_url', 'background_image_url'] as $imageField) {
            if ($role->$imageField) {
                $path = $role->getAttributes()[$imageField];
                if (config('filesystems.default') == 'local') {
                    $path = 'public/'.$path;
                }
                Storage::delete($path);
            }
        }

        $emails = $role->members()->pluck('email');

        // Clean up Google Calendar webhook
        if ($role->google_webhook_id && $role->google_webhook_resource_id) {
            try {
                $webhookUser = $role->users()->first();
                if ($webhookUser && $webhookUser->google_token) {
                    $googleCalendarService = app(\App\Services\GoogleCalendarService::class);
                    if ($googleCalendarService->ensureValidToken($webhookUser)) {
                        $googleCalendarService->deleteWebhook($role->google_webhook_id, $role->google_webhook_resource_id);
                    }
                }
            } catch (\Exception $e) {
                \Log::warning('Failed to clean up webhook during API role deletion', [
                    'role_id' => $role->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Prevent orphaned events for talent schedules
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

        try {
            Notification::route('mail', $emails)->notify(new DeletedRoleNotification($role, $user));
        } catch (\Exception $e) {
            \Log::warning('Failed to send deletion notification', [
                'role_id' => $role->id,
                'error' => $e->getMessage(),
            ]);
        }

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
                \Log::warning('Failed to cancel boost campaign during API schedule deletion', [
                    'campaign_id' => $campaign->id,
                    'role_id' => $role->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $role->is_deleted = true;
        $role->save();

        return response()->json([
            'data' => [
                'message' => 'Schedule deleted successfully',
            ],
        ], 200, [], JSON_PRETTY_PRINT);
    }
}
