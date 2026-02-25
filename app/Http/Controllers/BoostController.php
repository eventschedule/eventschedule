<?php

namespace App\Http\Controllers;

use App\Jobs\CreateBoostCampaign;
use App\Models\BoostAd;
use App\Models\BoostBillingRecord;
use App\Models\BoostCampaign;
use App\Models\Event;
use App\Models\Role;
use App\Services\BoostBillingService;
use App\Services\MetaAdsService;
use App\Utils\UrlUtils;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BoostController extends Controller
{
    protected function getMetaService(): MetaAdsService
    {
        if (! MetaAdsService::isBoostConfigured()) {
            return new \App\Services\MetaAdsServiceFake;
        }

        return new MetaAdsService;
    }

    protected function getRoles()
    {
        return auth()->user()->roles()->wherePivot('level', '!=', 'follower')->get();
    }

    protected function getRole(Request $request)
    {
        $roleId = $request->role_id ? UrlUtils::decodeId($request->role_id) : null;
        if (! $roleId) {
            return null;
        }

        $role = auth()->user()->roles()->where('roles.id', $roleId)->first();
        if (! $role || $role->pivot->level === 'follower') {
            return null;
        }

        return $role;
    }

    /**
     * Campaign list dashboard
     */
    public function index(Request $request)
    {
        $roles = $this->getRoles();
        $role = $this->getRole($request);

        $query = BoostCampaign::with(['event', 'role'])
            ->where('user_id', auth()->id());

        if ($role) {
            $query->where('role_id', $role->id);
        }

        $campaigns = $query->latest()->paginate(20);

        return view('boost.index', [
            'campaigns' => $campaigns,
            'roles' => $roles,
            'selectedRole' => $role,
        ]);
    }

    /**
     * Quick Boost / Advanced Boost creation page
     */
    public function create(Request $request)
    {
        $eventId = UrlUtils::decodeId($request->event_id);
        $event = Event::with(['roles', 'tickets'])->findOrFail($eventId);

        if (! auth()->user()->canEditEvent($event)) {
            abort(403);
        }

        // Get the role for this boost
        $role = null;
        if ($request->role_id) {
            $roleId = UrlUtils::decodeId($request->role_id);
            $role = $event->roles->firstWhere('id', $roleId);
        }
        if (! $role) {
            $role = $event->getViewableRole();
        }

        if (! $role) {
            abort(404);
        }

        // Require Pro plan for boost
        if (! $role->isPro()) {
            return redirect()->back()->with('error', __('messages.pro_feature_required'));
        }

        // Require verified phone for boost (hosted mode only)
        if (config('app.hosted') && ! auth()->user()->hasVerifiedPhone()) {
            return redirect()->back()->with('error', __('messages.phone_required_for_boost'));
        }

        // Check concurrent boost limit (trust-based)
        $completedCampaigns = BoostCampaign::where('role_id', $role->id)
            ->where('status', 'completed')
            ->count();

        $activeCampaigns = BoostCampaign::where('role_id', $role->id)
            ->whereIn('status', ['active', 'pending_payment'])
            ->count();

        $maxConcurrent = config('app.hosted')
            ? Role::calculateBoostMaxConcurrentForCompletedCount($completedCampaigns)
            : (int) config('services.meta.max_concurrent_boosts', 3);

        // Generate defaults
        $metaService = $this->getMetaService();
        $defaults = $metaService->generateQuickBoostDefaults($event);

        $isAdvanced = $request->boolean('advanced');
        $isFirstTime = ! BoostCampaign::where('user_id', auth()->id())->exists();

        // Cap default budget to role's max
        $maxBudget = $role->getBoostMaxBudget();
        $defaults['budget'] = min($defaults['budget'], $maxBudget);

        $view = $isAdvanced ? 'boost.create-advanced' : 'boost.create';

        $currencyCode = config('services.meta.default_currency', 'USD');
        $currencySymbol = match ($currencyCode) {
            'EUR' => "\u{20AC}",
            'GBP' => "\u{00A3}",
            default => '$',
        };

        return view($view, [
            'event' => $event,
            'role' => $role,
            'defaults' => $defaults,
            'activeCampaigns' => $activeCampaigns,
            'maxConcurrent' => $maxConcurrent,
            'isFirstTime' => $isFirstTime,
            'stripeKey' => config('services.stripe_platform.key'),
            'markupRate' => config('app.hosted') ? config('services.meta.markup_rate', 0.20) : 0,
            'minBudget' => config('services.meta.min_budget', 10),
            'maxBudget' => $maxBudget,
            'currencySymbol' => $currencySymbol,
            'isTesting' => config('app.is_testing'),
            'isHosted' => config('app.hosted'),
            'boostCredit' => $role->boost_credit,
            'pmLastFour' => $role->pm_last_four,
            'pmType' => $role->pm_type,
        ]);
    }

    /**
     * Store a new boost campaign
     */
    public function store(Request $request)
    {
        $isAjax = $request->ajax() || $request->wantsJson();

        $request->validate([
            'event_id' => 'required|string',
            'role_id' => 'required|string',
            'budget' => 'required|numeric|min:'.config('services.meta.min_budget').'|max:'.config('services.meta.max_budget'),
            'payment_intent_id' => 'nullable|string',
            'headline' => 'nullable|string|max:40',
            'primary_text' => 'nullable|string|max:125',
            'description' => 'nullable|string|max:30',
            'call_to_action' => 'nullable|string|max:30',
            'budget_type' => 'required|in:daily,lifetime',
            'objective' => 'nullable|in:OUTCOME_AWARENESS,OUTCOME_TRAFFIC,OUTCOME_ENGAGEMENT',
            'targeting' => 'nullable|json',
            'placements' => 'nullable|json',
            'scheduled_start' => 'nullable|date|after_or_equal:now',
            'scheduled_end' => 'nullable|date|after:scheduled_start|after:now',
        ]);

        $eventId = UrlUtils::decodeId($request->event_id);
        $roleId = UrlUtils::decodeId($request->role_id);

        $event = Event::with(['roles', 'tickets'])->findOrFail($eventId);

        if (! auth()->user()->canEditEvent($event)) {
            abort(403);
        }

        $role = $event->roles->firstWhere('id', $roleId);
        if (! $role) {
            abort(403);
        }

        // Require Pro plan for boost
        if (! $role->isPro()) {
            if ($isAjax) {
                return response()->json(['error' => __('messages.pro_feature_required')], 403);
            }

            return redirect()->back()->with('error', __('messages.pro_feature_required'));
        }

        // Check budget against trust-based limit
        $boostMaxBudget = $role->getBoostMaxBudget();
        if ((float) $request->budget > $boostMaxBudget) {
            if ($isAjax) {
                return response()->json(['error' => __('messages.boost_exceeds_limit', ['limit' => number_format($boostMaxBudget, 0)])], 422);
            }

            return back()->with('error', __('messages.boost_exceeds_limit', ['limit' => number_format($boostMaxBudget, 0)]));
        }

        // Require verified phone for boost (hosted mode only)
        if (config('app.hosted') && ! auth()->user()->hasVerifiedPhone()) {
            if ($isAjax) {
                return response()->json(['error' => __('messages.phone_required_for_boost')], 403);
            }

            return redirect()->back()->with('error', __('messages.phone_required_for_boost'));
        }

        // Prevent duplicate campaigns from the same payment
        if ($request->payment_intent_id) {
            $existingCampaign = BoostCampaign::where('stripe_payment_intent_id', $request->payment_intent_id)->first();
            if ($existingCampaign) {
                if ($existingCampaign->user_id === auth()->id()) {
                    $url = route('boost.show', ['hash' => $existingCampaign->hashedId()]);
                    if ($isAjax) {
                        return response()->json(['redirect' => $url]);
                    }

                    return redirect($url)->with('success', __('messages.boost_created'));
                }

                if ($isAjax) {
                    return response()->json(['error' => __('messages.boost_payment_failed')], 422);
                }

                return back()->with('error', __('messages.boost_payment_failed'));
            }
        }

        // Generate defaults for fields not provided
        $metaService = $this->getMetaService();
        $defaults = $metaService->generateQuickBoostDefaults($event);

        $budget = (float) $request->budget;
        $budgetType = $request->budget_type ?? 'lifetime';
        $markupRate = config('app.hosted') ? config('services.meta.markup_rate', 0.20) : 0;

        // Check concurrent boost limit and create campaign atomically
        DB::beginTransaction();
        try {
            DB::table('roles')->where('id', $roleId)->lockForUpdate()->first();

            $activeCampaigns = BoostCampaign::where('role_id', $roleId)
                ->whereIn('status', ['active', 'pending_payment'])
                ->count();

            $completedCampaigns = BoostCampaign::where('role_id', $roleId)
                ->where('status', 'completed')
                ->count();

            $maxConcurrent = config('app.hosted')
                ? Role::calculateBoostMaxConcurrentForCompletedCount($completedCampaigns)
                : (int) config('services.meta.max_concurrent_boosts', 3);

            if ($activeCampaigns >= $maxConcurrent) {
                DB::rollBack();
                if ($isAjax) {
                    return response()->json(['error' => __('messages.boost_max_concurrent')], 422);
                }

                return back()->with('error', __('messages.boost_max_concurrent'));
            }

            $campaign = BoostCampaign::create([
                'event_id' => $eventId,
                'role_id' => $roleId,
                'user_id' => auth()->id(),
                'name' => 'Boost: '.$event->translatedName(),
                'objective' => $request->objective ?? 'OUTCOME_AWARENESS',
                'status' => 'pending_payment',
                'daily_budget' => $budgetType === 'daily' ? $budget : null,
                'lifetime_budget' => $budgetType === 'lifetime' ? $budget : null,
                'budget_type' => $budgetType,
                'currency_code' => config('services.meta.default_currency', 'USD'),
                'scheduled_start' => $request->scheduled_start ?? $defaults['scheduled_start'],
                'scheduled_end' => $request->scheduled_end ?? $defaults['scheduled_end'],
                'targeting' => $request->targeting ? json_decode($request->targeting, true) : $defaults['targeting'],
                'placements' => $request->placements ? json_decode($request->placements, true) : null,
                'user_budget' => $budget,
                'billing_status' => config('app.hosted') ? 'pending' : 'charged',
                'stripe_payment_intent_id' => $request->payment_intent_id ?? (config('app.is_testing') ? 'test_pi_'.\Illuminate\Support\Str::random(24) : null),
            ]);

            // Set markup_rate explicitly (not via $fillable to prevent mass-assignment)
            $campaign->markup_rate = $markupRate;
            $campaign->save();

            DB::commit();
        } catch (QueryException $e) {
            DB::rollBack();
            // Duplicate payment intent - look up existing campaign
            $existingCampaign = BoostCampaign::where('stripe_payment_intent_id', $request->payment_intent_id)->first();
            if ($existingCampaign && $existingCampaign->user_id === auth()->id()) {
                $url = route('boost.show', ['hash' => $existingCampaign->hashedId()]);
                if ($isAjax) {
                    return response()->json(['redirect' => $url]);
                }

                return redirect($url)->with('success', __('messages.boost_created'));
            }

            if ($isAjax) {
                return response()->json(['error' => __('messages.boost_payment_failed')], 422);
            }

            return back()->with('error', __('messages.boost_payment_failed'));
        }

        // Create ad
        $destinationUrl = $event->getGuestUrl(false, null, true);
        $utmSeparator = str_contains($destinationUrl, '?') ? '&' : '?';
        $destinationUrl .= $utmSeparator.http_build_query([
            'utm_source' => 'boost',
            'utm_medium' => 'paid_social',
            'utm_campaign' => $campaign->hashedId(),
        ]);
        try {
            BoostAd::create([
                'boost_campaign_id' => $campaign->id,
                'headline' => $request->headline ?? $defaults['headline'],
                'primary_text' => $request->primary_text ?? $defaults['primary_text'],
                'description' => $request->description ?? $defaults['description'],
                'call_to_action' => $request->call_to_action ?? $defaults['call_to_action'],
                'image_url' => $defaults['image_url'],
                'destination_url' => $destinationUrl,
                'variant' => 'A',
                'status' => 'pending',
            ]);
        } catch (\Exception $e) {
            Log::error('Boost ad creation failed', [
                'campaign_id' => $campaign->id,
                'error' => $e->getMessage(),
            ]);
            $campaign->update(['status' => 'failed']);
            if (config('app.hosted') && ! config('app.is_testing')) {
                $billingService = new BoostBillingService;
                $billingService->cancelPaymentIntent($campaign->fresh());
            }

            if ($isAjax) {
                return response()->json(['error' => __('messages.boost_payment_failed')], 422);
            }

            return back()->with('error', __('messages.boost_payment_failed'));
        }

        // Confirm payment (credit, testing/selfhosted, or Stripe)
        $billingService = new BoostBillingService;
        $totalCost = $campaign->getTotalCost();
        $role = Role::find($roleId);

        if ($role && $role->boost_credit >= $totalCost) {
            // Pay with boost credit (lock role to prevent race condition)
            $creditPaid = DB::transaction(function () use ($roleId, $campaign, $totalCost) {
                $role = Role::lockForUpdate()->find($roleId);
                if (! $role || $role->boost_credit < $totalCost) {
                    return false;
                }
                $role->decrement('boost_credit', $totalCost);
                $campaign->update([
                    'total_charged' => $totalCost,
                    'billing_status' => 'charged',
                    'stripe_payment_intent_id' => null,
                ]);
                BoostBillingRecord::create([
                    'boost_campaign_id' => $campaign->id,
                    'type' => 'charge',
                    'amount' => $totalCost,
                    'meta_spend' => $campaign->user_budget,
                    'markup_amount' => $campaign->getMarkupAmount(),
                    'status' => 'completed',
                    'notes' => 'Paid with boost credit',
                ]);

                return true;
            });
            if ($creditPaid) {
                $paymentConfirmed = true;
            } elseif (! config('app.hosted') || config('app.is_testing')) {
                $campaign->update([
                    'total_charged' => config('app.hosted') ? $totalCost : 0,
                    'billing_status' => 'charged',
                ]);
                $paymentConfirmed = true;
            } else {
                if (! $request->payment_intent_id) {
                    $campaign->update(['status' => 'failed']);

                    if ($isAjax) {
                        return response()->json(['error' => __('messages.boost_payment_failed')], 422);
                    }

                    return back()->with('error', __('messages.boost_payment_failed'));
                }
                $paymentConfirmed = $billingService->confirmPayment($campaign, $request->payment_intent_id);
            }
        } elseif (! config('app.hosted') || config('app.is_testing')) {
            $campaign->update([
                'total_charged' => config('app.hosted') ? $totalCost : 0,
                'billing_status' => 'charged',
            ]);
            $paymentConfirmed = true;
        } else {
            if (! $request->payment_intent_id) {
                $campaign->update(['status' => 'failed']);

                if ($isAjax) {
                    return response()->json(['error' => __('messages.boost_payment_failed')], 422);
                }

                return back()->with('error', __('messages.boost_payment_failed'));
            }
            $paymentConfirmed = $billingService->confirmPayment($campaign, $request->payment_intent_id);
        }

        if (! $paymentConfirmed) {
            $campaign->update(['status' => 'failed']);
            $billingService->cancelPaymentIntent($campaign->fresh());

            if ($isAjax) {
                return response()->json(['error' => __('messages.boost_payment_failed')], 422);
            }

            return back()->with('error', __('messages.boost_payment_failed'));
        }

        // Update status and dispatch campaign creation job
        $campaign->update(['status' => 'active']);

        CreateBoostCampaign::dispatch($campaign);

        $url = route('boost.show', ['hash' => $campaign->hashedId()]);
        if ($isAjax) {
            return response()->json(['redirect' => $url]);
        }

        return redirect($url)->with('success', __('messages.boost_created'));
    }

    /**
     * Campaign detail + analytics
     */
    public function show(string $hash)
    {
        $id = UrlUtils::decodeIdOrFail($hash);
        $campaign = BoostCampaign::with(['event', 'role', 'ads', 'billingRecords'])
            ->findOrFail($id);

        if ($campaign->user_id !== auth()->id()) {
            if (! $campaign->event || ! auth()->user()->canEditEvent($campaign->event)) {
                abort(403);
            }
        }

        return view('boost.show', [
            'campaign' => $campaign,
        ]);
    }

    /**
     * Pause/resume a campaign
     */
    public function togglePause(string $hash)
    {
        $id = UrlUtils::decodeIdOrFail($hash);
        $campaign = BoostCampaign::with('event')->findOrFail($id);

        if ($campaign->user_id !== auth()->id()) {
            if (! $campaign->event || ! auth()->user()->canEditEvent($campaign->event)) {
                abort(403);
            }
        }

        if (! $campaign->meta_campaign_id) {
            return back()->with('error', __('messages.boost_not_ready'));
        }

        $metaService = $this->getMetaService();

        if ($campaign->isActive()) {
            $success = $metaService->pauseCampaign($campaign);
            if ($success) {
                DB::transaction(function () use ($campaign) {
                    $campaign = BoostCampaign::lockForUpdate()->find($campaign->id);
                    if ($campaign->isActive()) {
                        $campaign->update(['status' => 'paused', 'meta_status' => 'PAUSED']);
                    }
                });
            }

            return back()->with($success ? 'success' : 'error',
                $success ? __('messages.boost_paused') : __('messages.boost_pause_failed'));
        }

        if ($campaign->isPaused()) {
            $success = $metaService->resumeCampaign($campaign);
            if ($success) {
                DB::transaction(function () use ($campaign) {
                    $campaign = BoostCampaign::lockForUpdate()->find($campaign->id);
                    if ($campaign->isPaused()) {
                        $campaign->update(['status' => 'active', 'meta_status' => 'ACTIVE', 'budget_alert_sent_at' => null]);
                    }
                });
            }

            return back()->with($success ? 'success' : 'error',
                $success ? __('messages.boost_resumed') : __('messages.boost_resume_failed'));
        }

        return back()->with('error', __('messages.boost_cannot_toggle'));
    }

    /**
     * Cancel a campaign
     */
    public function cancel(string $hash)
    {
        $id = UrlUtils::decodeIdOrFail($hash);
        $campaign = BoostCampaign::with('event')->findOrFail($id);

        if ($campaign->user_id !== auth()->id()) {
            if (! $campaign->event || ! auth()->user()->canEditEvent($campaign->event)) {
                abort(403);
            }
        }

        if (! $campaign->canBeCancelled()) {
            return back()->with('error', __('messages.boost_cannot_cancel'));
        }

        // Update status in a transaction with locking (before Meta deletion, which is irreversible)
        $cancelled = DB::transaction(function () use ($campaign) {
            $campaign = BoostCampaign::lockForUpdate()->find($campaign->id);
            if (! $campaign->canBeCancelled()) {
                return false;
            }
            $campaign->update([
                'status' => 'cancelled',
                'meta_status' => $campaign->meta_campaign_id ? 'DELETED' : null,
            ]);

            return true;
        });

        if (! $cancelled) {
            return back()->with('error', __('messages.boost_cannot_cancel'));
        }

        if ($campaign->meta_campaign_id) {
            $metaService = $this->getMetaService();
            $deleted = $metaService->deleteCampaign($campaign);
            if (! $deleted) {
                Log::warning('Boost Meta campaign deletion failed after local cancellation', [
                    'campaign_id' => $campaign->id,
                    'meta_campaign_id' => $campaign->meta_campaign_id,
                ]);
            }
        }

        // Attempt refund outside the transaction (billing methods handle their own locking)
        $campaign->refresh();
        if (! in_array($campaign->billing_status, ['refunded', 'partially_refunded'])) {
            if (! $campaign->stripe_payment_intent_id && $campaign->billing_status === 'charged') {
                // Credit-paid campaign - return credit to role
                DB::transaction(function () use ($campaign) {
                    $role = Role::lockForUpdate()->find($campaign->role_id);
                    if (! $role) {
                        return;
                    }
                    $refundAmount = $campaign->total_charged;
                    $role->increment('boost_credit', $refundAmount);
                    BoostBillingRecord::create([
                        'boost_campaign_id' => $campaign->id,
                        'type' => 'refund',
                        'amount' => $refundAmount,
                        'status' => 'completed',
                        'notes' => 'Credit returned - campaign cancelled',
                    ]);
                    $campaign->update(['billing_status' => 'refunded']);
                });
            } elseif (config('app.hosted') && ! config('app.is_testing')) {
                $billingService = new BoostBillingService;

                if ($campaign->billing_status === 'pending') {
                    // Payment may not have been confirmed yet - cancel the intent
                    if ($campaign->stripe_payment_intent_id) {
                        $billingService->cancelPaymentIntent($campaign);
                    }
                } else {
                    $refunded = $campaign->actual_spend && $campaign->actual_spend > 0
                        ? $billingService->refundUnspent($campaign)
                        : $billingService->refundFull($campaign);

                    if (! $refunded) {
                        Log::warning('Boost cancellation refund failed', [
                            'campaign_id' => $campaign->id,
                        ]);
                    }
                }
            }
        }

        return back()->with('success', __('messages.boost_cancelled'));
    }

    /**
     * AJAX: Generate AI ad copy from event data
     */
    public function generateContent(Request $request)
    {
        $eventId = UrlUtils::decodeId($request->event_id);
        $event = Event::with(['roles', 'tickets'])->findOrFail($eventId);

        if (! auth()->user()->canEditEvent($event)) {
            abort(403);
        }

        $metaService = $this->getMetaService();
        $defaults = $metaService->generateQuickBoostDefaults($event);

        return response()->json([
            'headline' => $defaults['headline'],
            'primary_text' => $defaults['primary_text'],
            'description' => $defaults['description'],
            'call_to_action' => $defaults['call_to_action'],
        ]);
    }

    /**
     * AJAX: Search Meta interests
     */
    public function searchInterests(Request $request)
    {
        $request->validate(['q' => 'required|string|min:2|max:100']);

        $metaService = $this->getMetaService();
        $results = $metaService->searchInterests($request->q);

        return response()->json($results);
    }

    /**
     * AJAX: Estimate reach
     */
    public function estimateReach(Request $request)
    {
        $request->validate(['targeting' => 'required|json']);

        $metaService = $this->getMetaService();
        $targeting = json_decode($request->targeting, true);
        if (! is_array($targeting)) {
            return response()->json(['error' => 'Invalid targeting data'], 422);
        }
        $result = $metaService->estimateReach($targeting);

        return response()->json($result);
    }

    /**
     * Create a Stripe Payment Intent for the boost (AJAX)
     */
    public function createPaymentIntent(Request $request)
    {
        $request->validate([
            'event_id' => 'required|string',
            'budget' => 'required|numeric|min:'.config('services.meta.min_budget').'|max:'.config('services.meta.max_budget'),
            'previous_payment_intent_id' => 'nullable|string',
        ]);

        $eventId = UrlUtils::decodeId($request->event_id);
        $event = Event::findOrFail($eventId);

        if (! auth()->user()->canEditEvent($event)) {
            abort(403);
        }

        $budget = (float) $request->budget;
        $markupRate = config('app.hosted') ? config('services.meta.markup_rate', 0.20) : 0;
        $totalAmount = round($budget * (1 + $markupRate), 2);

        // Check if role has enough boost credit to cover the total
        $event->loadMissing('roles');
        $role = $request->role_id
            ? $event->roles->firstWhere('id', UrlUtils::decodeId($request->role_id))
            : $event->getViewableRole();

        // Check budget against trust-based limit
        if ($role) {
            $boostMaxBudget = $role->getBoostMaxBudget();
            if ($budget > $boostMaxBudget) {
                return response()->json(['error' => __('messages.boost_exceeds_limit', ['limit' => number_format($boostMaxBudget, 0)])], 422);
            }
        }

        if ($role && $role->boost_credit >= $totalAmount) {
            return response()->json([
                'use_credit' => true,
                'credit_balance' => $role->boost_credit,
                'total_amount' => $totalAmount,
            ]);
        }

        if (config('app.is_testing')) {
            $fakeId = 'test_pi_'.\Illuminate\Support\Str::random(24);

            return response()->json([
                'client_secret' => $fakeId.'_secret_test',
                'payment_intent_id' => $fakeId,
                'total_amount' => $totalAmount,
            ]);
        }

        $amountInCents = (int) round($totalAmount * 100);

        try {
            $stripe = new \Stripe\StripeClient(config('services.stripe_platform.secret'));

            // Cancel previous payment intent if provided
            if ($request->previous_payment_intent_id) {
                try {
                    $previous = $stripe->paymentIntents->retrieve($request->previous_payment_intent_id);
                    if (in_array($previous->status, ['requires_payment_method', 'requires_confirmation', 'requires_action'])) {
                        $stripe->paymentIntents->cancel($request->previous_payment_intent_id);
                    }
                } catch (\Exception $e) {
                    // Non-critical - previous intent may already be cancelled or expired
                    Log::info('Could not cancel previous payment intent', [
                        'previous_id' => $request->previous_payment_intent_id,
                        'reason' => $e->getMessage(),
                    ]);
                }
            }

            $params = [
                'amount' => $amountInCents,
                'currency' => strtolower(config('services.meta.default_currency', 'USD')),
                'payment_method_types' => ['card'],
                'metadata' => [
                    'type' => 'boost',
                    'event_id' => $eventId,
                    'user_budget' => $budget,
                    'markup_rate' => $markupRate,
                ],
                'description' => "Boost: {$event->translatedName()}",
            ];

            // Attach Stripe customer so saved payment methods are available
            $customerSessionClientSecret = null;
            if ($role && $role->stripe_id) {
                $params['customer'] = $role->stripe_id;

                try {
                    $customerSession = $stripe->customerSessions->create([
                        'customer' => $role->stripe_id,
                        'components' => [
                            'payment_element' => [
                                'enabled' => true,
                                'features' => [
                                    'payment_method_redisplay' => 'enabled',
                                ],
                            ],
                        ],
                    ]);
                    $customerSessionClientSecret = $customerSession->client_secret;
                } catch (\Exception $e) {
                    // Non-critical - fall back to manual card entry
                    Log::info('Could not create customer session for boost', [
                        'role_id' => $role->id,
                        'reason' => $e->getMessage(),
                    ]);
                }
            }

            $paymentIntent = $stripe->paymentIntents->create($params);

            return response()->json([
                'client_secret' => $paymentIntent->client_secret,
                'payment_intent_id' => $paymentIntent->id,
                'total_amount' => $totalAmount,
                'has_saved_card' => $role && ! empty($role->pm_last_four),
                'pm_last_four' => $role?->pm_last_four,
                'customer_session_client_secret' => $customerSessionClientSecret,
            ]);
        } catch (\Exception $e) {
            Log::error('Stripe payment intent creation failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json(['error' => __('messages.payment_error')], 500);
        }
    }
}
