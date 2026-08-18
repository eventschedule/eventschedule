<?php

namespace App\Http\Controllers;

use App\Models\AnalyticsPromotionsDaily;
use App\Models\BoostAd;
use App\Models\BoostBillingRecord;
use App\Models\BoostCampaign;
use App\Models\Event;
use App\Models\PageView;
use App\Models\PromotionLocationsDaily;
use App\Models\Role;
use App\Services\AdsService;
use App\Services\AuditService;
use App\Services\BoostBillingService;
use App\Services\GeoIpService;
use App\Services\PromotionBillingService;
use App\Services\PromotionInventoryService;
use App\Services\PromotionModerationService;
use App\Services\PromotionService;
use App\Utils\CountryUtils;
use App\Utils\MoneyUtils;
use App\Utils\UrlUtils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Guest-facing endpoints for on-network promotions.
 *
 * Currently the click redirect: the link on every promo card points here rather than
 * straight at the advertiser, so the click can be counted, billed and attributed before the
 * visitor is forwarded.
 */
class PromotionController extends Controller
{
    /**
     * The purchase form for an on-network promotion.
     */
    public function create(Request $request)
    {
        $this->assertNetworkAvailable();

        $event = Event::with(['roles'])->findOrFail(UrlUtils::decodeId($request->event_id));

        if (! auth()->user()->canEditEvent($event)) {
            abort(403);
        }

        // Promoting an event nobody can open would just burn the advertiser's money.
        if ($event->is_draft || $event->is_private) {
            return redirect()->route('boost.index')->with('error', __('messages.promotion_event_not_public'));
        }

        // Mirrors BoostController::create(). store() enforces this too, but the form confirms
        // the card BEFORE it submits - so without the gate here the advertiser pays, and only
        // then gets bounced to the profile page with the money captured and no campaign.
        if (config('app.hosted') && ! config('app.is_testing') && ! auth()->user()->hasVerifiedPhone()) {
            return redirect()->to(route('profile.edit').'?highlight=phone#section-profile')
                ->with('error', __('messages.phone_required_for_boost'));
        }

        $role = $this->resolveAdvertiserRole($request, $event);

        if (! $role) {
            abort(403);
        }

        return view('boost.create-network', [
            'event' => $event,
            'role' => $role,
            'minBudget' => (float) config('ads.native_min_budget', 5),
            'maxBudget' => min((float) config('ads.native_max_budget', 1000), (float) $role->getBoostMaxBudget()),
            'cpm' => (float) AdsService::setting('native_cpm', 2.00),
            'cpc' => (float) AdsService::setting('native_cpc', 0.25),
            'currency' => $currency = AdsService::nativeCurrency(),
            // PROMOTIONS_CURRENCY is configurable, so every amount on this form has to carry a
            // symbol - a non-USD operator's buyers would otherwise see bare numbers.
            'currencySymbol' => BoostCampaign::currencySymbol($currency),
            'inventory' => app(PromotionInventoryService::class)->dailyImpressions(),
            'stripeKey' => config('services.stripe_platform.key'),
            'isHosted' => config('app.hosted'),
            'isTesting' => config('app.is_testing'),
            'pmLastFour' => $role->pm_last_four,
            'pmType' => $role->pm_type,
            // Drives whether the card step is needed at all: settlePayment() takes the wallet
            // branch first, so a schedule with enough credit never touches Stripe.
            'boostCredit' => (float) $role->boost_credit,
            'countries' => CountryUtils::getCountries(),
        ]);
    }

    /**
     * Create the Stripe PaymentIntent for a network promotion.
     *
     * Deliberately NOT reusing BoostController::createPaymentIntent(): that method hardcodes
     * config('services.meta.markup_rate'), so it would charge the card 1.2x while
     * getTotalCost() returned 1.0x - and confirmPayment() would then flag EVERY network
     * purchase as an amount_mismatch. Network promotions carry no external ad spend, so the
     * markup is zero and the advertiser's whole budget is the operator's revenue.
     */
    public function createPaymentIntent(Request $request)
    {
        $this->assertNetworkAvailable();

        $request->validate([
            'event_id' => 'required|string',
            'role_id' => 'required|string',
            'budget' => 'required|numeric|min:'.config('ads.native_min_budget', 5).'|max:'.config('ads.native_max_budget', 1000),
        ]);

        $event = Event::with('roles')->findOrFail(UrlUtils::decodeId($request->event_id));

        if (! auth()->user()->canEditEvent($event)) {
            abort(403);
        }

        $role = $this->resolveAdvertiserRole($request, $event);

        if (! $role) {
            abort(403);
        }

        $budget = round((float) $request->budget, 2);

        // The per-schedule trust limit, which the form only uses to set the input's max attribute.
        // Enforcing it here as well is what makes it an actual limit rather than a suggestion.
        if ($budget > (float) $role->getBoostMaxBudget()) {
            return response()->json(['error' => __('messages.boost_exceeds_limit', ['limit' => MoneyUtils::format($role->getBoostMaxBudget(), AdsService::nativeCurrency())])], 422);
        }

        try {
            $stripe = new \Stripe\StripeClient(config('services.stripe_platform.secret'));

            $currency = AdsService::nativeCurrency();

            $params = [
                // The multiplier is looked up rather than a literal 100, and from the SAME
                // currency the charge is denominated in on the next line. JPY and KRW have no
                // minor unit, so *100 there would bill a hundred times the budget.
                'amount' => (int) round($budget * MoneyUtils::getSmallestUnitMultiplier($currency)),
                'currency' => strtolower($currency),
                'automatic_payment_methods' => ['enabled' => true],
                'metadata' => [
                    'type' => 'promo',
                    'event_id' => (string) $event->id,
                    'user_budget' => (string) $budget,
                    'markup_rate' => '0',
                ],
            ];

            if ($role->stripe_id) {
                $params['customer'] = $role->stripe_id;
            }

            $intent = $stripe->paymentIntents->create($params);

            return response()->json([
                'client_secret' => $intent->client_secret,
                'payment_intent_id' => $intent->id,
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['error' => __('messages.boost_payment_failed')], 422);
        }
    }

    /**
     * Buy an on-network promotion.
     *
     * Mirrors the shape of BoostController::store() - the same three settlement branches, the
     * same duplicate-intent guard, the same role lock - without touching that method, which
     * is the untested Meta money path.
     */
    public function store(Request $request)
    {
        $this->assertNetworkAvailable();

        // The card is confirmed CLIENT-side before this request is sent, so by the time any of
        // the guards below run the money is already captured. Validation and the event lookup
        // are no exception - `scheduled_end after:scheduled_start` in particular has no
        // client-side counterpart, so picking an end date before the start date was a routine
        // way to be charged and left with no campaign and nothing for cleanup to find.
        try {
            $request->validate([
                'event_id' => 'required|string',
                'role_id' => 'required|string',
                'budget' => 'required|numeric|min:'.config('ads.native_min_budget', 5).'|max:'.config('ads.native_max_budget', 1000),
                'pricing_model' => 'required|in:cpm,cpc',
                'payment_intent_id' => 'nullable|string',
                'headline' => 'nullable|string|max:80',
                'primary_text' => 'nullable|string|max:180',
                'scheduled_start' => 'nullable|date',
                'scheduled_end' => 'nullable|date|after:scheduled_start',
                // Constrained to real ISO codes: a typo'd code is not a harmless no-op, it makes
                // the campaign undeliverable (matchesTargeting() excludes anything unmatched) and
                // the advertiser would be billed nothing while wondering why nothing ran.
                'visitor_countries' => 'nullable|array|max:280',
                'visitor_countries.*' => ['string', 'size:2', Rule::in(array_keys(CountryUtils::getCountries()))],
                'schedule_types' => 'nullable|array',
                'schedule_types.*' => 'in:talent,venue,curator',
            ]);

            $event = Event::with('roles')->findOrFail(UrlUtils::decodeId($request->event_id));
        } catch (\Throwable $e) {
            $this->abandonIntent($request);

            throw $e;
        }

        // Every exit from here on can strand a CAPTURED payment: the form confirms the card
        // before it submits, so bailing without releasing the intent leaves the buyer charged
        // with no campaign and nothing for the expiry commands to find (they look for campaign
        // rows, and none exists yet).
        if (! auth()->user()->canEditEvent($event)) {
            $this->abandonIntent($request);
            abort(403);
        }

        if ($event->is_draft || $event->is_private) {
            $this->abandonIntent($request);

            return back()->with('error', __('messages.promotion_event_not_public'));
        }

        $role = $this->resolveAdvertiserRole($request, $event);

        if (! $role) {
            $this->abandonIntent($request);
            abort(403);
        }

        // The per-schedule trust limit. `create()` only uses it for the input's max attribute, so
        // without this check a direct POST is bounded solely by the global config maximum.
        if (round((float) $request->budget, 2) > (float) $role->getBoostMaxBudget()) {
            $this->abandonIntent($request);

            return back()->with('error', __('messages.boost_exceeds_limit', ['limit' => MoneyUtils::format($role->getBoostMaxBudget(), AdsService::nativeCurrency())]));
        }

        // create() gates on this too, so reaching it here means a direct POST or a phone that
        // was unverified between loading the form and submitting it.
        if (config('app.hosted') && ! config('app.is_testing') && ! auth()->user()->hasVerifiedPhone()) {
            $this->abandonIntent($request);

            return redirect()->to(route('profile.edit').'?highlight=phone#section-profile')
                ->with('error', __('messages.phone_required_for_boost'));
        }

        // Same duplicate-payment guard as the Meta flow: stripe_payment_intent_id is UNIQUE,
        // so a double submit would otherwise hit a constraint violation instead of landing
        // the buyer on their campaign.
        if ($request->payment_intent_id) {
            $existing = BoostCampaign::where('stripe_payment_intent_id', $request->payment_intent_id)->first();

            if ($existing) {
                return $existing->user_id === auth()->id()
                    ? redirect()->route('boost.show', ['hash' => $existing->hashedId()])
                    : back()->with('error', __('messages.boost_payment_failed'));
            }
        }

        $budget = round((float) $request->budget, 2);
        $pricingModel = $request->pricing_model;
        $rate = (float) AdsService::setting($pricingModel === 'cpm' ? 'native_cpm' : 'native_cpc',
            $pricingModel === 'cpm' ? 2.00 : 0.25);

        // A zero rate would make both chargeImpression() and chargeClick() free, so the campaign
        // would deliver forever without ever exhausting its budget. Refuse to sell it rather than
        // create something that can never complete.
        //
        // CPM needs a floor rather than just "> 0": impressionCostMicros() is
        // intdiv($unit_rate_micros, 1000), and toMicros() scales dollars by 1e6, so any CPM
        // below $0.001 truncates to a cost of 0 and behaves exactly like a zero rate. CPC uses
        // the rate directly (clickCostMicros), so a small CPC is harmless.
        if ($rate <= 0 || ($pricingModel === 'cpm' && $rate < 0.001)) {
            $this->abandonIntent($request);

            return back()->with('error', __('messages.promotion_rate_not_configured'));
        }

        $campaign = null;

        // Concurrency cap, counted only against network campaigns so the two channels never
        // starve each other.
        $capReached = DB::transaction(function () use ($role, &$campaign, $event, $budget, $pricingModel, $rate, $request) {
            DB::table('roles')->where('id', $role->id)->lockForUpdate()->first();

            $active = BoostCampaign::network()
                ->where('role_id', $role->id)
                ->whereIn('status', ['active', 'paused', 'pending_payment', 'pending_review'])
                ->count();

            if ($active >= (int) config('ads.native_max_concurrent', 2)) {
                return true;
            }

            $campaign = BoostCampaign::create([
                'event_id' => $event->id,
                'role_id' => $role->id,
                'user_id' => auth()->id(),
                'channel' => 'network',
                'name' => mb_substr($event->name ?: __('messages.promotion'), 0, 191),
                'status' => 'pending_payment',
                'moderation_status' => 'pending',
                'billing_status' => 'pending',
                'user_budget' => $budget,
                'budget_type' => 'lifetime',
                'lifetime_budget' => $budget,
                'currency_code' => AdsService::nativeCurrency(),
                'pricing_model' => $pricingModel,
                'unit_rate_micros' => PromotionBillingService::toMicros($rate),
                'budget_micros' => PromotionBillingService::toMicros($budget),
                'scheduled_start' => $request->scheduled_start,
                'scheduled_end' => $request->scheduled_end,
                // Claim the intent AT INSERT so the UNIQUE index on this column is what
                // arbitrates concurrent submits. Checking for an existing campaign earlier and
                // then not writing the column left a window where two requests both passed the
                // check, both inserted, and the loser only discovered the collision later -
                // inside confirmPayment(), which swallows it and reports "payment failed" to a
                // customer whose card was charged. settlePayment() nulls this again if the
                // purchase ends up being covered by credit.
                'stripe_payment_intent_id' => $request->payment_intent_id ?: null,
                'network_targeting' => array_filter([
                    'visitor_countries' => $request->input('visitor_countries', []),
                    'schedule_types' => $request->input('schedule_types', []),
                ]),
            ]);

            // Not fillable, so it cannot be mass-assigned from the request: a network
            // promotion has no external ad spend to mark up, so the whole charge is revenue.
            $campaign->markup_rate = 0;
            $campaign->save();

            return false;
        });

        if ($capReached) {
            // Nothing was charged yet - the client confirms payment before POSTing - but any
            // intent it created must not be left hanging on the Stripe account.
            $this->abandonIntent($request);

            return back()->with('error', __('messages.promotion_max_concurrent'));
        }

        try {
            BoostAd::create([
                'boost_campaign_id' => $campaign->id,
                'headline' => mb_substr($request->headline ?: $event->translatedName(), 0, 255),
                'primary_text' => $request->primary_text,
                'image_url' => $event->flyer_image_url,
                // NOT the custom-domain variant: buildGuestUrl() emits an unverified custom
                // domain for any non-'direct' mode, and this value becomes a redirect target.
                'destination_url' => $event->getGuestUrl(false, null, false),
                'variant' => 'A',
                'status' => 'pending',
            ]);
        } catch (\Throwable $e) {
            // Without this the campaign is stranded in pending_payment with a live intent.
            report($e);
            $campaign->update(['status' => 'failed']);
            (new BoostBillingService)->cancelPaymentIntent($campaign->fresh());

            return back()->with('error', __('messages.boost_payment_failed'));
        }

        if (! $this->settlePayment($campaign, $role, $request)) {
            return back()->with('error', __('messages.boost_payment_failed'));
        }

        $campaign->update([
            'status' => PromotionModerationService::activationStatusFor($campaign),
            'moderation_status' => PromotionModerationService::moderationStatusFor($campaign),
        ]);

        app(PromotionService::class)->forgetCandidates();

        AuditService::log(
            AuditService::PROMO_CREATE, auth()->id(), 'BoostCampaign', $campaign->id,
            null, null, 'role_id:'.$campaign->role_id
        );

        return redirect()->route('boost.show', ['hash' => $campaign->hashedId()])
            ->with('success', $campaign->isAwaitingReview()
                ? __('messages.promotion_submitted_for_review')
                : __('messages.promotion_created'));
    }

    /**
     * Settle a network purchase: credit balance, free (selfhost/testing), or Stripe.
     */
    protected function settlePayment(BoostCampaign $campaign, Role $role, Request $request): bool
    {
        $billing = new BoostBillingService;
        $total = $campaign->getTotalCost();

        if ($role->boost_credit >= $total) {
            $paid = DB::transaction(function () use ($role, $campaign, $total) {
                $locked = Role::lockForUpdate()->find($role->id);

                if (! $locked || $locked->boost_credit < $total) {
                    return false;
                }

                $locked->decrement('boost_credit', $total);
                $campaign->update([
                    'total_charged' => $total,
                    'billing_status' => 'charged',
                    'stripe_payment_intent_id' => null,
                ]);

                BoostBillingRecord::create([
                    'boost_campaign_id' => $campaign->id,
                    'type' => 'charge',
                    'amount' => $total,
                    // No external spend on this channel, so the entire charge is margin -
                    // which keeps AdminController::boost()'s SUM(markup_amount) correct
                    // across both channels with no change there.
                    'meta_spend' => 0,
                    'markup_amount' => $total,
                    'status' => 'completed',
                    'notes' => 'Paid with promotion credit',
                ]);

                return true;
            });

            if ($paid) {
                return true;
            }
        }

        if (! config('app.hosted') || config('app.is_testing')) {
            $campaign->update([
                'total_charged' => config('app.hosted') ? $total : 0,
                'billing_status' => 'charged',
            ]);

            return true;
        }

        if (! $request->payment_intent_id) {
            $campaign->update(['status' => 'failed']);

            return false;
        }

        if (! $billing->confirmPayment($campaign, $request->payment_intent_id)) {
            $campaign->update(['status' => 'failed']);
            // Otherwise an uncharged intent is left dangling on the Stripe account.
            $billing->cancelPaymentIntent($campaign->fresh());

            return false;
        }

        return true;
    }

    protected function assertNetworkAvailable(): void
    {
        if (! PromotionService::isEnabled()) {
            abort(404);
        }
    }

    /**
     * Release a PaymentIntent for a purchase that never produced a campaign.
     *
     * The client confirms payment before POSTing, so a request that bails after that point
     * leaves a real charge with nothing attached to it.
     */
    protected function abandonIntent(Request $request): void
    {
        if (! $request->payment_intent_id || ! config('app.hosted') || config('app.is_testing')) {
            return;
        }

        try {
            $stripe = new \Stripe\StripeClient(config('services.stripe_platform.secret'));
            $intent = $stripe->paymentIntents->retrieve($request->payment_intent_id);

            if ($intent->status === 'succeeded') {
                $stripe->refunds->create(
                    ['payment_intent' => $intent->id, 'metadata' => ['reason' => 'promotion_not_created']],
                    ['idempotency_key' => 'promo_abandon_'.$intent->id],
                );
            } elseif (in_array($intent->status, ['requires_payment_method', 'requires_confirmation', 'requires_action', 'processing'])) {
                $stripe->paymentIntents->cancel($intent->id);
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /**
     * The schedule that will be billed for, and credited as the advertiser on, this campaign.
     *
     * Three constraints, and all three matter:
     *
     *  - the role must be one of the EVENT's roles. `canEditEvent()` authorizes the event, not the
     *    role, so without this a caller can pair their own event with any role id on the instance.
     *    Mirrors BoostController::store().
     *  - the caller must be a member of it. `role_id` arrives as a `UrlUtils::encodeId()` hash,
     *    which is reversible obfuscation and not a secret - every schedule's id is derivable from
     *    its own public pages.
     *  - it must be Pro, which is the plan gate for buying promotion at all.
     *
     *  - its pivot on this event must be ACCEPTED. PromotionService::candidates() requires
     *    `event_role.is_accepted = true` for the advertiser's own role, and nothing else on the
     *    way in checked it: Event::roles() does not filter the pivot and canEditEvent() ignores
     *    it. A schedule added to someone else's event but not yet accepted could pay, pass
     *    review, go active - and never appear in a single slot.
     *
     * Returns null rather than aborting so callers choose their own failure response. Every caller
     * MUST treat null as a hard 403 - settlePayment() debits this role's boost_credit.
     */
    protected function resolveAdvertiserRole(Request $request, Event $event): ?Role
    {
        $candidates = $event->roles->filter(
            fn ($r) => $r->isPro()
                && auth()->user()->isMember($r->subdomain)
                && (bool) $r->pivot?->is_accepted
        );

        if ($request->role_id) {
            return $candidates->firstWhere('id', UrlUtils::decodeId($request->role_id));
        }

        return $candidates->first();
    }

    /**
     * Count a promotion click and forward the visitor to the advertised event.
     */
    public function click(Request $request, string $subdomain, string $hash)
    {
        $hostRole = Role::where('subdomain', $subdomain)->firstOrFail();

        // Only a live, approved campaign gets a redirect. Without the moderation and status
        // predicates the hash keeps working after a campaign is rejected, paused or refunded -
        // which turns "approve before serve" into a formality and leaves a permanent redirect
        // off a trusted domain that costs the advertiser nothing.
        $campaign = BoostCampaign::network()
            ->where('status', 'active')
            ->where('moderation_status', 'approved')
            ->with(['event', 'ads', 'role'])
            ->find(UrlUtils::decodeId($hash));

        if (! $campaign) {
            return redirect($hostRole->getGuestUrl());
        }

        $destination = $this->destinationUrl($campaign);

        if (! $destination) {
            return redirect($hostRole->getGuestUrl());
        }

        // Members and admins of the host schedule can preview their own pages; their clicks
        // are not real demand and must not be billed to the advertiser.
        $user = auth()->user();
        $isInsider = $user && ($user->isMember($subdomain) || $user->isAdmin());

        if (! $isInsider && ! AdsService::isPreview($request, $hostRole)) {
            $this->recordClick($campaign, $hostRole, $request);
        }

        return redirect($this->withAttribution($destination, $campaign, $hostRole), 302);
    }

    /**
     * Where this promotion actually points.
     *
     * Derived entirely server-side. BoostAd.destination_url is advertiser-supplied and 2048
     * characters wide, so it is validated against the hosts this install controls before
     * being used - otherwise every free-tier schedule becomes an open-redirect surface.
     * There is deliberately no `url` request parameter to honour.
     */
    protected function destinationUrl(BoostCampaign $campaign): ?string
    {
        $candidate = $campaign->ads->first()?->destination_url;

        if ($candidate && $this->isSafeDestination($candidate, $campaign)) {
            return $candidate;
        }

        // Fall back to the event's page on OUR domain. Passing true for $useCustomDomain here
        // would reintroduce the very host this method is trying to validate: buildGuestUrl()
        // emits the custom domain for any non-'direct' mode without checking it was ever verified.
        return $campaign->event?->getGuestUrl(false, null, false) ?: null;
    }

    protected function isSafeDestination(string $url, BoostCampaign $campaign): bool
    {
        $parts = parse_url($url);

        if (! $parts || ($parts['scheme'] ?? '') !== 'https' || empty($parts['host'])) {
            return false;
        }

        $host = strtolower($parts['host']);
        $base = strtolower((string) _base_domain());

        if ($base && ($host === $base || str_ends_with($host, '.'.$base))) {
            return true;
        }

        // A custom domain counts only once it has actually been provisioned and verified.
        // roles.custom_domain_host is set by a mutator from whatever string the owner typed
        // (Role::setCustomDomainAttribute), and custom_domain_status is only ever written on the
        // 'direct' provisioning branch - so without both checks an owner could point this at any
        // host they like and get an open redirect off a trusted domain.
        $role = $campaign->role;

        return $role
            && $role->custom_domain_host
            && $role->custom_domain_mode === 'direct'
            && $role->custom_domain_status === 'active'
            && $host === strtolower($role->custom_domain_host);
    }

    /**
     * Append the attribution parameters, preserving anything already on the URL.
     *
     * utm_source is deliberately 'boost' rather than a new value: TicketController already
     * maps that source onto sales.boost_campaign_id, so conversion attribution works with no
     * changes there. utm_medium is what separates on-network from Meta placements, and
     * utm_content records which host schedule earned the click.
     *
     * Merged rather than concatenated because the destination may already carry a recurring
     * event's date or a ?lang= override.
     */
    protected function withAttribution(string $url, BoostCampaign $campaign, Role $hostRole): string
    {
        $parts = parse_url($url);
        parse_str($parts['query'] ?? '', $query);

        $query = array_merge($query, [
            'utm_source' => 'boost',
            'utm_medium' => 'network',
            'utm_campaign' => UrlUtils::encodeId($campaign->id),
            'utm_content' => UrlUtils::encodeId($hostRole->id),
            // Proves the visit came through this redirect. CaptureUtmParameters only lets a paid
            // placement jump the first-touch queue when this validates, so an advertiser cannot
            // self-attribute conversions by pasting the UTM params onto their own links.
            'utm_token' => PromotionService::clickToken($campaign->id),
        ]);

        $rebuilt = ($parts['scheme'] ?? 'https').'://'.($parts['host'] ?? '');

        if (! empty($parts['port'])) {
            $rebuilt .= ':'.$parts['port'];
        }

        $rebuilt .= $parts['path'] ?? '';
        $rebuilt .= '?'.http_build_query($query);

        if (! empty($parts['fragment'])) {
            $rebuilt .= '#'.$parts['fragment'];
        }

        return $rebuilt;
    }

    /**
     * Count and bill one click.
     *
     * Mirrors RoleController::recordSocialClick(), but goes through
     * PageView::incrementDailyCounter() rather than rebuilding the midnight TTL by hand -
     * that open-coded version had its Carbon operands reversed and silently never capped.
     */
    protected function recordClick(BoostCampaign $campaign, Role $hostRole, Request $request): void
    {
        $userAgent = $request->userAgent();

        if (PageView::isBot($userAgent) || PageView::isSuspiciousRequest($request)) {
            return;
        }

        $ip = PageView::clientIp($request);

        // No resolvable IP means no way to rate-limit, so there is nothing to bill against.
        if (! $ip) {
            return;
        }

        // Keyed on the IP hash ALONE, deliberately. visitorHash() mixes in the User-Agent, which
        // the client chooses freely - rotating it mints a fresh bucket on every request and the
        // cap stops existing. At the route's 60/min throttle that is enough to drain a $1,000 CPC
        // budget in about an hour from a single address. PageView::hasExceededViewLimit() already
        // keys its analytics cap on getIpHash($ip) for exactly this reason.
        // The click cost is fixed per campaign, so a count cap IS a spend cap: at most
        // 10 x clickCostMicros per address per campaign per day.
        $ipHash = PageView::ipHash($ip);

        if (PageView::incrementDailyCounter("promo_click:{$campaign->id}:{$ipHash}") > 10) {
            return;
        }

        // Bill before counting: a charge without a count is caught by the spend-drift check,
        // whereas a count without a charge is revenue quietly given away.
        if (! app(PromotionBillingService::class)->chargeClick($campaign)) {
            app(PromotionService::class)->forgetCandidates();

            return;
        }

        AnalyticsPromotionsDaily::recordClick($campaign->id, $hostRole->id);
        PromotionLocationsDaily::record($campaign->id, $ip ? app(GeoIpService::class)->lookup($ip) : null, 'click');
    }
}
