<?php

namespace App\Http\Controllers;

use App\Jobs\CreateBoostCampaign;
use App\Models\BoostAd;
use App\Models\BoostCampaign;
use App\Models\Event;
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
        if (config('app.is_testing') || empty(config('services.meta.access_token'))) {
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

        // Check concurrent boost limit
        $activeCampaigns = BoostCampaign::where('role_id', $role->id)
            ->whereIn('status', ['active', 'pending_payment'])
            ->count();

        $maxConcurrent = config('services.meta.max_concurrent_boosts', 3);

        // Generate defaults
        $metaService = $this->getMetaService();
        $defaults = $metaService->generateQuickBoostDefaults($event);

        $isAdvanced = $request->boolean('advanced');
        $isFirstTime = ! BoostCampaign::where('user_id', auth()->id())->exists();

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
            'markupRate' => config('services.meta.markup_rate', 0.20),
            'minBudget' => config('services.meta.min_budget', 10),
            'maxBudget' => config('services.meta.max_budget', 5000),
            'currencySymbol' => $currencySymbol,
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
            'payment_intent_id' => 'required|string',
            'headline' => 'nullable|string|max:40',
            'primary_text' => 'nullable|string|max:125',
            'description' => 'nullable|string|max:30',
            'call_to_action' => 'nullable|string|max:30',
            'budget_type' => 'required|in:daily,lifetime',
            'objective' => 'nullable|in:OUTCOME_AWARENESS,OUTCOME_TRAFFIC,OUTCOME_ENGAGEMENT',
            'targeting' => 'nullable|json',
            'placements' => 'nullable|json',
            'scheduled_start' => 'nullable|date',
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

        // Check concurrent boost limit
        $activeCampaigns = BoostCampaign::where('role_id', $roleId)
            ->whereIn('status', ['active', 'pending_payment'])
            ->count();

        if ($activeCampaigns >= config('services.meta.max_concurrent_boosts', 3)) {
            if ($isAjax) {
                return response()->json(['error' => __('messages.boost_max_concurrent')], 422);
            }

            return back()->with('error', __('messages.boost_max_concurrent'));
        }

        // Prevent duplicate campaigns from the same payment
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

        // Generate defaults for fields not provided
        $metaService = $this->getMetaService();
        $defaults = $metaService->generateQuickBoostDefaults($event);

        $budget = (float) $request->budget;
        $budgetType = $request->budget_type ?? 'lifetime';
        $markupRate = config('services.meta.markup_rate', 0.20);

        // Create campaign record
        try {
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
                'markup_rate' => $markupRate,
                'billing_status' => 'pending',
                'stripe_payment_intent_id' => $request->payment_intent_id,
            ]);
        } catch (QueryException $e) {
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
            $billingService = new BoostBillingService;
            $billingService->cancelPaymentIntent($campaign->fresh());

            if ($isAjax) {
                return response()->json(['error' => __('messages.boost_payment_failed')], 422);
            }

            return back()->with('error', __('messages.boost_payment_failed'));
        }

        // Confirm Stripe payment
        $billingService = new BoostBillingService;
        $paymentConfirmed = $billingService->confirmPayment($campaign, $request->payment_intent_id);

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

        if ($campaign->event) {
            if (! auth()->user()->canEditEvent($campaign->event)) {
                abort(403);
            }
        } elseif ($campaign->user_id !== auth()->id()) {
            abort(403);
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

        if ($campaign->event) {
            if (! auth()->user()->canEditEvent($campaign->event)) {
                abort(403);
            }
        } elseif ($campaign->user_id !== auth()->id()) {
            abort(403);
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

        if ($campaign->event) {
            if (! auth()->user()->canEditEvent($campaign->event)) {
                abort(403);
            }
        } elseif ($campaign->user_id !== auth()->id()) {
            abort(403);
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
        $markupRate = config('services.meta.markup_rate', 0.20);
        $totalAmount = round($budget * (1 + $markupRate), 2);
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

            $paymentIntent = $stripe->paymentIntents->create([
                'amount' => $amountInCents,
                'currency' => strtolower(config('services.meta.default_currency', 'USD')),
                'metadata' => [
                    'type' => 'boost',
                    'event_id' => $eventId,
                    'user_budget' => $budget,
                    'markup_rate' => $markupRate,
                ],
                'description' => "Boost: {$event->translatedName()}",
            ]);

            return response()->json([
                'client_secret' => $paymentIntent->client_secret,
                'payment_intent_id' => $paymentIntent->id,
                'total_amount' => $totalAmount,
            ]);
        } catch (\Exception $e) {
            Log::error('Stripe payment intent creation failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json(['error' => __('messages.payment_error')], 500);
        }
    }
}
