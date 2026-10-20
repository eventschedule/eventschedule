<?php

namespace App\Http\Controllers;

use App\Http\Requests\SubscriptionStoreRequest;
use App\Http\Requests\SubscriptionSwapRequest;
use App\Models\Event;
use App\Models\Referral;
use App\Models\Role;
use App\Models\SubscriptionCancellation;
use App\Services\AuditService;
use App\Services\UsageTrackingService;
use App\Utils\PlanPriceUtils;
use App\Utils\PlatformPricing;
use App\Utils\UrlUtils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Laravel\Cashier\Exceptions\IncompletePayment;

class SubscriptionController extends Controller
{
    /**
     * Where a checkout was started from, carried through the subscribe form into the
     * subscription.create audit row. Allow-listed, because it lands in the growth export.
     */
    public const CHECKOUT_SOURCES = ['tickets', 'feeds'];

    /**
     * Records that the viewer was shown the paid-ticket paywall in the event editor. Fired by
     * the editor's Vue app the first time its live banner shows, which covers both a banner
     * rendered on load and one that appeared because a price was just typed.
     *
     * Per USER and first-touch only, like subscribe_form_viewed_at. The stamp can only ever
     * be written on the caller's own row, so the check below is about keeping the number
     * honest (a schedule the caller actually edits), not about protecting anyone else.
     */
    public function paywallSeen(Request $request, $subdomain)
    {
        $user = auth()->user();

        if (! config('app.hosted') || ! $user->isEditor($subdomain)) {
            return response()->json(['ok' => false], 403);
        }

        // Base query builder + whereNull: writes at most once and leaves users.updated_at alone,
        // which the admin active-users metric keys off.
        DB::table('users')
            ->where('id', $user->id)
            ->whereNull('ticket_paywall_viewed_at')
            ->update(['ticket_paywall_viewed_at' => now()]);

        return response()->json(['ok' => true]);
    }

    /**
     * Show the subscription page.
     */
    public function show(Request $request, $subdomain)
    {
        $role = Role::subdomain($subdomain)->firstOrFail();

        if (auth()->user()->id != $role->user_id) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $requestedTier = $request->query('tier', 'pro');

        // Block if already on Enterprise, or if already on the requested tier
        if ($role->hasActiveEnterpriseSubscription()) {
            return redirect()
                ->route('role.view_admin', ['subdomain' => $subdomain, 'tab' => 'plan'])
                ->with('message', __('messages.subscription_already_active'));
        }

        // If they have an active Pro subscription and aren't requesting Enterprise, block
        // Also block trial subscriptions from upgrading to Enterprise
        if ($role->hasActiveSubscription() && ($requestedTier !== 'enterprise' || $role->subscription('default')?->onTrial())) {
            return redirect()
                ->route('role.view_admin', ['subdomain' => $subdomain, 'tab' => 'plan'])
                ->with('message', __('messages.subscription_already_active'));
        }

        // If requesting Enterprise, verify price IDs are configured
        $enterpriseConfigured = PlanPriceUtils::current('enterprise', 'monthly') && PlanPriceUtils::current('enterprise', 'yearly');
        if ($requestedTier === 'enterprise' && ! $enterpriseConfigured) {
            $requestedTier = 'pro';
        }

        $intent = $role->createSetupIntent();

        // Onboarding funnel: "reached checkout". First-touch stamp, deliberately placed after
        // every redirect above AND after createSetupIntent() - a user bounced back because they
        // are already subscribed never saw the form, and neither did one whose page 500'd
        // because the Stripe call threw. The stamp has to mean the form actually rendered, or
        // the stage it feeds measures something else.
        // Base query builder + whereNull writes at most once and does not bump users.updated_at
        // (which the admin active-users metric keys off).
        DB::table('users')
            ->where('id', auth()->id())
            ->whereNull('subscribe_form_viewed_at')
            ->update(['subscribe_form_viewed_at' => now()]);

        return view('subscription.show', [
            'role' => $role,
            'intent' => $intent,
            'monthlyPrice' => PlanPriceUtils::current('pro', 'monthly'),
            'yearlyPrice' => PlanPriceUtils::current('pro', 'yearly'),
            'selectedTier' => $requestedTier,
            'enterpriseConfigured' => $enterpriseConfigured,
            'checkoutSource' => in_array($request->query('source'), self::CHECKOUT_SOURCES, true) ? $request->query('source') : null,
            'trialEnd' => $role->checkoutTrialEnd(),
            'trialUsedHere' => ! $role->checkoutTrialEnd() && $this->hadAPlanHere($role),
            'trialUsedOn' => $role->checkoutTrialEnd() ? null : $this->scheduleThatHadTheTrial($role),
            'returnEvent' => $this->returnEvent($request->query('event'), $role),
        ]);
    }

    /**
     * The event a "Save and start free trial" in the event editor came from, so checkout can go
     * back to it. Resolved here from its hash, never from an address in the request: it must be
     * an event this schedule sells for, and one the owner can edit.
     */
    private function returnEvent($hash, Role $role): ?Event
    {
        if (! is_string($hash) || $hash === '') {
            return null;
        }

        $id = UrlUtils::decodeId($hash);
        $event = $id ? Event::find($id) : null;

        if (! $event || $event->ticketingRole()?->id !== $role->id || ! auth()->user()->canEditEvent($event)) {
            return null;
        }

        return $event;
    }

    /**
     * Whether this schedule's own history is why checkout charges today: a plan it had (a
     * subscription, a hand-granted or legacy plan, a comped runway). Then the page says so about
     * this schedule rather than naming another one or blaming the login.
     */
    private function hadAPlanHere(Role $role): bool
    {
        return (bool) ($role->trial_ends_at || $role->plan_expires
            || $role->subscriptions()->whereNotIn('stripe_status', ['incomplete', 'incomplete_expired'])->exists());
    }

    /**
     * Another schedule of the same owner that had a trial or a plan, for "you already used your
     * free trial on X". Null when none is left to name (the trial was used on a schedule since
     * deleted), and the page says it without a name.
     */
    private function scheduleThatHadTheTrial(Role $role): ?Role
    {
        if (! config('app.hosted') || is_demo_role($role)) {
            return null;
        }

        return Role::where('user_id', $role->user_id)
            ->where('id', '!=', $role->id)
            ->where('is_deleted', false)
            ->where(fn ($q) => $q->whereNotNull('ticket_trial_ends_at')
                ->orWhereHas('subscriptions', fn ($sub) => $sub->whereNotIn('stripe_status', ['incomplete', 'incomplete_expired'])))
            ->orderBy('id')
            ->first();
    }

    /**
     * Create a new subscription.
     */
    public function store(SubscriptionStoreRequest $request, $subdomain)
    {
        $role = Role::subdomain($subdomain)->firstOrFail();

        if (auth()->user()->id != $role->user_id) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $tier = $request->input('tier', 'pro');

        // Validate Enterprise price IDs are configured
        if ($tier === 'enterprise' && (! PlanPriceUtils::current('enterprise', 'monthly') || ! PlanPriceUtils::current('enterprise', 'yearly'))) {
            return redirect()->back()->with('error', __('messages.subscription_error'));
        }

        // If upgrading from Pro to Enterprise with existing subscription, use swap
        if ($tier === 'enterprise' && $role->hasActiveSubscription() && ! $role->hasActiveEnterpriseSubscription()) {
            // A new or swapped subscription is always created at what we sell today.
            $priceId = PlanPriceUtils::current('enterprise', $request->plan);

            try {
                $subscription = $role->subscription('default');
                $role->createOrGetStripeCustomer();
                $role->updateDefaultPaymentMethod($request->payment_method);
                $subscription->swap($priceId);

                // Update plan info with lock to prevent race with webhook
                \DB::transaction(function () use ($role, $request) {
                    $role = \App\Models\Role::lockForUpdate()->find($role->id);
                    $role->plan_type = 'enterprise';
                    $role->plan_term = $request->plan === 'yearly' ? 'year' : 'month';
                    $role->plan_source = null;
                    $role->save();
                });

                return redirect()
                    ->route('role.view_admin', ['subdomain' => $subdomain, 'tab' => 'plan'])
                    ->with('message', __('messages.subscription_updated'));
            } catch (IncompletePayment $exception) {
                return redirect()->route(
                    'cashier.payment',
                    [$exception->payment->id, 'redirect' => route('role.view_admin', ['subdomain' => $subdomain, 'tab' => 'plan'])]
                );
            } catch (\Exception $e) {
                UsageTrackingService::track(UsageTrackingService::STRIPE_SUBSCRIPTION_FAILED, $role->id);
                \Log::error('Subscription upgrade failed', ['error' => $e->getMessage(), 'role' => $role->id]);

                return redirect()->back()->with('error', __('messages.subscription_error'));
            }
        }

        if ($role->hasActiveSubscription()) {
            return redirect()
                ->route('role.view_admin', ['subdomain' => $subdomain, 'tab' => 'plan'])
                ->with('message', __('messages.subscription_already_active'));
        }

        $priceId = PlanPriceUtils::current($tier, $request->plan);

        // Back to the event a "Save and start free trial" came from, or the Plan tab.
        $returnEvent = $this->returnEvent($request->input('event'), $role);
        $after = $returnEvent
            ? route('event.edit', ['subdomain' => $role->subdomain, 'hash' => UrlUtils::encodeId($returnEvent->id)])
            : route('role.view_admin', ['subdomain' => $subdomain, 'tab' => 'plan']);

        try {
            // The same answer the checkout page printed (RoleBillable::checkoutTrialEnd()).
            $trialEnd = $role->checkoutTrialEnd();

            // Create the subscription
            $subscriptionBuilder = $role->newSubscription('default', $priceId);

            if ($trialEnd) {
                $subscriptionBuilder->trialUntil($trialEnd);
            }

            $subscriptionBuilder->create($request->payment_method);

            // Only once the subscription exists: a declined card or an abandoned 3DS step
            // (IncompletePayment, below) must not spend the login's one trial. Stamped for a
            // charge-today checkout too, since paying is having had the plan.
            $this->markTrialUsed($role);

            // Update the role's plan info with lock to prevent race with webhook
            // and clear legacy plan_expires to prevent dual-path access via legacy fields
            \DB::transaction(function () use ($role, $tier, $request) {
                $role = Role::lockForUpdate()->find($role->id);
                $role->plan_type = $tier;
                $role->plan_term = $request->plan === 'yearly' ? 'year' : 'month';
                $role->plan_expires = null;
                // Paying now, so drop any hand-granted or referral provenance along with the
                // legacy expiry - both are what the guest-footer credit keys off.
                $role->plan_source = null;
                $role->save();
            });

            UsageTrackingService::track(UsageTrackingService::STRIPE_SUBSCRIPTION, $role->id);

            AuditService::log(AuditService::SUBSCRIPTION_CREATE, auth()->id(), 'Role', $role->id,
                null, array_filter([
                    'plan_type' => $tier,
                    'plan_term' => $request->plan,
                    // Validated against CHECKOUT_SOURCES by SubscriptionStoreRequest.
                    'source' => $request->input('source'),
                ]), $role->subdomain);

            // Track referral subscription
            if (config('app.hosted')) {
                $referral = Referral::where('referred_user_id', $role->user_id)
                    ->where('status', 'pending')
                    ->first();

                if ($referral) {
                    $referral->update([
                        'referred_role_id' => $role->id,
                        'plan_type' => $tier,
                        'subscribed_at' => now(),
                        'status' => 'subscribed',
                    ]);
                }
            }

            return redirect($after)->with('message', $this->subscribedMessage($tier, $request->plan, $trialEnd, (bool) $returnEvent));

        } catch (IncompletePayment $exception) {
            $this->markTrialUsedIfStarted($role);

            return redirect()->route(
                'cashier.payment',
                [$exception->payment->id, 'redirect' => $after]
            );
        } catch (\Exception $e) {
            UsageTrackingService::track(UsageTrackingService::STRIPE_SUBSCRIPTION_FAILED, $role->id);
            \Log::error('Subscription creation failed', ['error' => $e->getMessage(), 'role' => $role->id]);

            return redirect()->back()->with('error', __('messages.subscription_error'));
        }
    }

    /**
     * What the owner is told after checkout: whether anything was charged, how much and when.
     * Entering a card read as paying, so a trial that charged nothing has to say so.
     */
    private function subscribedMessage(string $tier, string $plan, ?\Carbon\Carbon $trialEnd, bool $forEvent): string
    {
        $amount = plan_price(PlatformPricing::amount($tier === 'enterprise' ? 'enterprise' : 'pro', $plan === 'yearly' ? 'yearly' : 'monthly'));

        $message = $trialEnd
            ? __('messages.subscription_trial_started', ['amount' => $amount, 'date' => $trialEnd->translatedFormat('F j, Y')])
            : __('messages.subscription_charged_today', [
                'plan' => $tier === 'enterprise' ? __('messages.enterprise_plan') : __('messages.pro_plan'),
                'amount' => $amount,
            ]);

        return $forEvent ? $message.' '.__('messages.subscription_event_can_sell') : $message;
    }

    /** users.trial_used_at: one trial per login (RoleBillable::isEligibleForTrial()). */
    private function markTrialUsed(Role $role): void
    {
        if (! $role->user_id) {
            return;
        }

        // Base query builder + whereNull: written once, and users.updated_at is left alone.
        DB::table('users')
            ->where('id', $role->user_id)
            ->whereNull('trial_used_at')
            ->update(['trial_used_at' => now()]);
    }

    /**
     * A subscription that needs a 3DS step exists in Stripe already, and a trial one has
     * started: it is trialing, not incomplete. Only an incomplete one leaves the trial unspent.
     */
    private function markTrialUsedIfStarted(Role $role): void
    {
        // Read afresh: the subscriptions relation was loaded (empty) before create().
        $subscription = $role->subscriptions()->where('type', 'default')->latest('id')->first();

        if ($subscription && ! in_array($subscription->stripe_status, ['incomplete', 'incomplete_expired'], true)) {
            $this->markTrialUsed($role);
        }
    }

    /**
     * Redirect to Stripe Customer Portal.
     */
    public function portal(Request $request, $subdomain)
    {
        $role = Role::subdomain($subdomain)->firstOrFail();

        if (auth()->user()->id != $role->user_id) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        if (! $role->hasStripeId()) {
            return redirect()->back()->with('error', __('messages.no_active_subscription'));
        }

        return $role->redirectToBillingPortal(
            route('role.view_admin', ['subdomain' => $subdomain, 'tab' => 'plan'])
        );
    }

    /**
     * Cancel the subscription.
     */
    public function cancel(Request $request, $subdomain)
    {
        $role = Role::subdomain($subdomain)->firstOrFail();

        if (auth()->user()->id != $role->user_id) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $subscription = $role->subscription('default');

        if (! $subscription || ! $subscription->active()) {
            return redirect()->back()->with('error', __('messages.no_active_subscription'));
        }

        // Optional on purpose: asking why must never stand between someone and cancelling.
        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'in:'.implode(',', SubscriptionCancellation::REASONS)],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $subscription->cancel();
        } catch (\Exception $e) {
            \Log::error('Subscription cancellation failed', ['error' => $e->getMessage(), 'role' => $role->id]);

            return redirect()->back()->with('error', __('messages.subscription_error'));
        }

        SubscriptionCancellation::record([
            'role_id' => $role->id,
            'user_id' => auth()->id(),
            'stripe_subscription_id' => $subscription->stripe_id,
            'source' => 'app',
            'reason' => $validated['reason'] ?? null,
            'comment' => $validated['comment'] ?? null,
            'plan_type' => $role->plan_type,
            'plan_term' => $role->plan_term,
        ]);

        AuditService::log(AuditService::SUBSCRIPTION_CANCEL, auth()->id(), 'Role', $role->id, null,
            array_filter(['reason' => $validated['reason'] ?? null]) ?: null, $role->subdomain);

        return redirect()
            ->route('role.view_admin', ['subdomain' => $subdomain, 'tab' => 'plan'])
            ->with('message', __('messages.subscription_cancelled'));
    }

    /**
     * Resume a cancelled subscription.
     */
    public function resume(Request $request, $subdomain)
    {
        $role = Role::subdomain($subdomain)->firstOrFail();

        if (auth()->user()->id != $role->user_id) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $subscription = $role->subscription('default');

        if (! $subscription || ! $subscription->onGracePeriod()) {
            return redirect()->back()->with('error', __('messages.subscription_not_resumable'));
        }

        try {
            $subscription->resume();
        } catch (\Exception $e) {
            \Log::error('Subscription resume failed', ['error' => $e->getMessage(), 'role' => $role->id]);

            return redirect()->back()->with('error', __('messages.subscription_error'));
        }

        SubscriptionCancellation::markResumed($subscription->stripe_id);

        AuditService::log(AuditService::SUBSCRIPTION_RESUME, auth()->id(), 'Role', $role->id, null, null, $role->subdomain);

        return redirect()
            ->route('role.view_admin', ['subdomain' => $subdomain, 'tab' => 'plan'])
            ->with('message', __('messages.subscription_resumed'));
    }

    /**
     * Swap between monthly/yearly plans and/or upgrade tier.
     */
    public function swap(SubscriptionSwapRequest $request, $subdomain)
    {
        $role = Role::subdomain($subdomain)->firstOrFail();

        if (auth()->user()->id != $role->user_id) {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        $tier = $request->input('tier', $role->plan_type ?: 'pro');

        if ($role->plan_type === 'enterprise' && $tier !== 'enterprise') {
            return redirect()->back()->with('error', __('messages.not_authorized'));
        }

        // Validate Enterprise price IDs are configured
        if ($tier === 'enterprise' && (! PlanPriceUtils::current('enterprise', 'monthly') || ! PlanPriceUtils::current('enterprise', 'yearly'))) {
            return redirect()->back()->with('error', __('messages.subscription_error'));
        }

        $priceId = PlanPriceUtils::current($tier, $request->plan);

        $subscription = $role->subscription('default');

        if (! $subscription || ! $subscription->active() || $subscription->onGracePeriod()) {
            return redirect()->back()->with('error', __('messages.no_active_subscription'));
        }

        try {
            $subscription->swap($priceId);

            // Update plan info with lock to prevent race with webhook
            \DB::transaction(function () use ($role, $tier, $request) {
                $role = Role::lockForUpdate()->find($role->id);
                $role->plan_type = $tier;
                $role->plan_term = $request->plan === 'yearly' ? 'year' : 'month';
                $role->plan_source = null;
                $role->save();
            });
        } catch (IncompletePayment $exception) {
            return redirect()->route(
                'cashier.payment',
                [$exception->payment->id, 'redirect' => route('role.view_admin', ['subdomain' => $subdomain, 'tab' => 'plan'])]
            );
        } catch (\Exception $e) {
            \Log::error('Subscription swap failed', ['error' => $e->getMessage(), 'role' => $role->id]);

            return redirect()->back()->with('error', __('messages.subscription_error'));
        }

        AuditService::log(AuditService::SUBSCRIPTION_SWAP, auth()->id(), 'Role', $role->id,
            null, ['plan_type' => $tier, 'plan_term' => $request->plan], $role->subdomain);

        return redirect()
            ->route('role.view_admin', ['subdomain' => $subdomain, 'tab' => 'plan'])
            ->with('message', __('messages.subscription_updated'));
    }
}
