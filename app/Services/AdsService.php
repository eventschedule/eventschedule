<?php

namespace App\Services;

use App\Models\Event;
use App\Models\PageView;
use App\Models\Role;
use App\Models\Setting;
use Illuminate\Http\Request;

/**
 * Monetization gate and slot resolver.
 *
 * Answers three questions:
 *   1. Is monetization switched on for this instance at all?  (isEnabled)
 *   2. Which AdSense identifiers should the unit use?         (setting / adSenseConfigured)
 *   3. What, if anything, should this specific request see?   (resolveSlot)
 *
 * Tier logic lives on Role::showAds(); everything request-scoped lives here, so the model
 * stays free of request state and remains safe to call from a queued context.
 */
class AdsService
{
    /**
     * The instance-wide kill switch. Env only - see config/ads.php for why.
     */
    public static function isEnabled(): bool
    {
        return (bool) config('ads.enabled');
    }

    /**
     * Resolve a monetization setting: database first, config/env as the fallback.
     *
     * Booleans are stored as the strings '1' and '0' rather than '1' and null, because a
     * null would fall through to the env default and make "off" silently mean "on" on any
     * install that set the corresponding env var.
     */
    public static function setting(string $key, $default = null)
    {
        if (! self::isEnabled()) {
            return $default;
        }

        $stored = Setting::get('ads_'.$key);

        if ($stored !== null && $stored !== '') {
            return $stored;
        }

        return config('ads.'.$key, $default);
    }

    /**
     * The currency on-network promotions are priced and charged in.
     *
     * PROMOTIONS_CURRENCY only, never the platform currency: this denominates a real Stripe
     * charge (PromotionController::intent), and the platform currency is an admin dropdown
     * documented as display-only.
     */
    public static function nativeCurrency(): string
    {
        return config('ads.native_currency') ?: 'USD';
    }

    public static function boolSetting(string $key): bool
    {
        $value = self::setting($key);

        return $value === true || $value === '1' || $value === 1;
    }

    public static function adSenseConfigured(): bool
    {
        return self::isEnabled()
            && self::boolSetting('adsense_enabled')
            && ! empty(self::setting('adsense_client_id'))
            && ! empty(self::setting('adsense_slot_id'));
    }

    public static function nativeConfigured(): bool
    {
        return self::isEnabled() && self::boolSetting('native_enabled');
    }

    public static function nativePriority(): bool
    {
        return self::boolSetting('native_priority');
    }

    /**
     * Whether ads must be non-personalized for this request.
     *
     * True unless the operator explicitly opted into personalized ads, and always true
     * when the visitor sends Global Privacy Control - honouring Sec-GPC is two lines and
     * carries real legal weight in several US states.
     */
    public static function requestNonPersonalizedAds(Request $request): bool
    {
        if ($request->header('Sec-GPC') === '1') {
            return true;
        }

        return ! self::boolSetting('personalized');
    }

    /**
     * Decide what this request should be shown, if anything.
     *
     * Returns null (render nothing), ['type' => 'native', 'promo' => [...]], or
     * ['type' => 'adsense']. Any impression counting happens here rather than in Blade.
     *
     * @return array{type: string, promo?: array}|null
     */
    public function resolveSlot(Role $role, ?Event $event, Request $request, bool $passwordGate = false): ?array
    {
        if (! $this->isEligible($role, $request, $passwordGate)) {
            return null;
        }

        // Never on a page that is actively selling tickets. The event page carries the inline ticket
        // form, so an ad here - and worse, a paid promotion for another schedule's event - would sit
        // beside the organizer's own buy button and compete with it. Free schedules are the only ones
        // that carry ads and, since the free plan can sell, also the only ones this can happen to.
        // Extends the checkout/booking exclusion already promised in docs/FEATURES.md.
        // The occurrence date is a ROUTE parameter (/{slug}/{id}/{date}), not a query string, so
        // query('date') was always null and this judged the recurrence anchor instead.
        if ($event && $event->tickets_enabled && $event->canSellTickets($request->route('date') ?? $request->query('date'))) {
            return null;
        }

        $native = null;

        // Google's ad crawlers are exempted from the bot filter so AdSense can read the page for
        // contextual targeting - but they are still robots, so they must never be billed as a
        // native impression. Without this, spoofing `Mediapartners-Google` buys an attacker
        // billable impressions with every invalid-traffic check bypassed.
        $isAdsCrawler = PageView::isGoogleAdsCrawler($request->userAgent());

        if (! $isAdsCrawler && self::nativeConfigured()) {
            $native = app(PromotionService::class)->pick($role, $event, $request);
        }

        // Priority 1: a matching paid native promotion.
        if ($native && self::nativePriority()) {
            return ['type' => 'native', 'promo' => $native];
        }

        // Priority 2: programmatic fallback.
        if (self::adSenseConfigured()) {
            return ['type' => 'adsense'];
        }

        // Native still fills when it is not prioritized but nothing else can serve.
        if ($native) {
            return ['type' => 'native', 'promo' => $native];
        }

        return null;
    }

    /**
     * Every request-scoped reason not to monetize this page.
     *
     * Each guard is here for a concrete reason, not defensively - see the table in the
     * feature docs. The order is cheapest-first so bots and members never reach a query.
     */
    public function isEligible(Role $role, Request $request, bool $passwordGate = false): bool
    {
        if (! $role->showAds()) {
            return false;
        }

        // The opt-out covers BOTH halves. It was only read in PromotionService, so a schedule
        // that switched it off still carried Google ads - and the toggle itself is rendered
        // only when the promotions network is on, so on an AdSense-only instance there was no
        // control anywhere and no way to decline.
        if ($role->promotions_opt_out) {
            return false;
        }

        // Embeds render inside a third party's iframe, and ?graphic=1 renders a shareable
        // image - an ad baked into a downloaded PNG is not acceptable. The embed guard
        // mirrors the one the "Powered by" footer already uses.
        if ($request->boolean('embed') || $request->has('graphic')) {
            return false;
        }

        // No ads in front of a password prompt.
        if ($passwordGate) {
            return false;
        }

        // A lapsed Enterprise schedule keeps its custom domain: RoleController only blocks
        // CHANGING custom_domain, not holding one after a downgrade. Serving AdSense on a
        // domain the operator does not own would breach AdSense policy, so key off the
        // attribute ResolveCustomDomain sets rather than the schedule's stored columns.
        if ($request->attributes->has('custom_domain_host')) {
            return false;
        }

        // AdSense policy prohibits publishers viewing their own ads, and owners reload
        // their own guest page constantly. Mirrors the analytics guard in viewGuest().
        $user = auth()->user();
        if ($user && ($user->isMember($role->subdomain) || $user->isAdmin())) {
            // ...but let them see what visitors see when they explicitly ask.
            // PromotionService does not count an impression in preview mode.
            return $request->boolean('preview_ads');
        }

        // Invalid-traffic hygiene. Google's own ad crawlers are exempt from the bot filter
        // because Mediapartners-Google is how AdSense reads the page to choose contextually
        // relevant ads, and hiding the unit from it quietly degrades relevance and revenue.
        //
        // This exemption is ONLY about rendering. resolveSlot() separately refuses to let a
        // crawler take the native branch, because the User-Agent is attacker-controlled and this
        // gate sits in front of code that bills a real advertiser.
        $userAgent = $request->userAgent();
        if (PageView::isGoogleAdsCrawler($userAgent)) {
            return true;
        }

        if (PageView::isBot($userAgent) || PageView::isSuspiciousRequest($request)) {
            return false;
        }

        return true;
    }

    /**
     * Whether this request is a member/admin previewing the slot rather than a real visitor.
     * Previews render but must never be counted or billed.
     */
    public static function isPreview(Request $request, ?Role $role = null): bool
    {
        if (! $request->boolean('preview_ads')) {
            return false;
        }

        $user = auth()->user();

        if (! $user) {
            return false;
        }

        // Mirrors the member/admin test in isEligible(). Accepting any signed-in account here
        // let a logged-in stranger append ?preview_ads=1 and browse the network without their
        // impressions being counted or the advertiser billed.
        if ($user->isAdmin()) {
            return true;
        }

        return $role ? $user->isMember($role->subdomain) : false;
    }
}
