<?php

namespace App\Utils;

/**
 * The one place that maps a Stripe Price ID onto a plan tier and term.
 *
 * Stripe Prices are immutable: changing what a plan costs means creating a NEW Price object and
 * pointing config at it. Existing subscriptions keep billing on the old one forever (archiving a
 * Price in Stripe only blocks NEW use of it), so at any moment several generations of price ID
 * can be live at once.
 *
 * Nothing in this app asks Stripe what a subscription costs - tier and term are decided by string
 * matching the locally stored `subscriptions.stripe_price` against config. So every one of those
 * comparisons has to know about the retired IDs too, or a grandfathered Enterprise customer stops
 * matching, drops to Pro, and keeps paying the Enterprise rate for it.
 *
 * The split that matters: current() is what a NEW subscription is created at, and is the only
 * thing checkout and swap may use. Everything that RECOGNIZES an existing subscription must go
 * through the plural accessors, which include the legacy IDs.
 */
class PlanPriceUtils
{
    /** @var array<string, string> tier+term => the config key holding the current price ID */
    private const CURRENT_KEYS = [
        'pro.monthly' => 'price_monthly',
        'pro.yearly' => 'price_yearly',
        'enterprise.monthly' => 'enterprise_price_monthly',
        'enterprise.yearly' => 'enterprise_price_yearly',
    ];

    /** @var array<string, string> tier+term => the config key holding retired price IDs */
    private const LEGACY_KEYS = [
        'pro.monthly' => 'legacy_price_monthly',
        'pro.yearly' => 'legacy_price_yearly',
        'enterprise.monthly' => 'legacy_enterprise_price_monthly',
        'enterprise.yearly' => 'legacy_enterprise_price_yearly',
    ];

    /**
     * The price ID a NEW subscription at this tier and term is created at.
     *
     * @param  string  $tier  pro|enterprise
     * @param  string  $term  monthly|yearly
     */
    public static function current(string $tier, string $term): ?string
    {
        $key = self::CURRENT_KEYS[self::slot($tier, $term)] ?? null;

        if (! $key) {
            return null;
        }

        return config('services.stripe_platform.'.$key) ?: null;
    }

    /**
     * Every price ID that counts as this tier and term: the current one plus any retired ones.
     * The current ID is first, so callers that want a canonical value can take the head.
     *
     * @return array<int, string>
     */
    public static function all(string $tier, string $term): array
    {
        $slot = self::slot($tier, $term);

        $ids = [self::current($tier, $term)];

        if ($key = self::LEGACY_KEYS[$slot] ?? null) {
            $ids = array_merge($ids, self::split(config('services.stripe_platform.'.$key)));
        }

        return array_values(array_unique(array_filter($ids)));
    }

    /**
     * Every price ID that grants Enterprise, across both terms and every generation.
     *
     * @return array<int, string>
     */
    public static function enterpriseIds(): array
    {
        return array_values(array_unique(array_merge(
            self::all('enterprise', 'monthly'),
            self::all('enterprise', 'yearly'),
        )));
    }

    /**
     * Every price ID billed yearly, across both tiers and every generation.
     *
     * @return array<int, string>
     */
    public static function yearlyIds(): array
    {
        return array_values(array_unique(array_merge(
            self::all('pro', 'yearly'),
            self::all('enterprise', 'yearly'),
        )));
    }

    /**
     * The tier a stored price ID belongs to, or null if it is not one we know.
     *
     * Null is load-bearing: callers must decline to write rather than assume Pro. A price ID we
     * do not recognize means config is incomplete, and guessing "pro" there is exactly how a
     * paying Enterprise customer gets a downgrade persisted to their role row.
     *
     * @return string|null enterprise|pro|null
     */
    public static function tierFor(?string $priceId): ?string
    {
        if (! $priceId) {
            return null;
        }

        if (in_array($priceId, self::enterpriseIds(), true)) {
            return 'enterprise';
        }

        if (in_array($priceId, array_merge(self::all('pro', 'monthly'), self::all('pro', 'yearly')), true)) {
            return 'pro';
        }

        return null;
    }

    /**
     * The billing term a stored price ID represents, or null if it is not one we know.
     * Matches the `roles.plan_term` enum, so month|year rather than monthly|yearly.
     *
     * @return string|null year|month|null
     */
    public static function termFor(?string $priceId): ?string
    {
        if (! $priceId) {
            return null;
        }

        if (in_array($priceId, self::yearlyIds(), true)) {
            return 'year';
        }

        if (in_array($priceId, array_merge(self::all('pro', 'monthly'), self::all('enterprise', 'monthly')), true)) {
            return 'month';
        }

        return null;
    }

    /**
     * What a price ID charges per billing period, in dollars. Current prices come from the
     * display amounts; retired ones from the legacy amount map, since their real figure is not
     * recoverable from config any other way.
     *
     * Returns null rather than a guess: quoting a grandfathered subscriber the current price
     * would state a number they are not being charged.
     */
    public static function amountFor(?string $priceId): ?float
    {
        // The amount map is deliberately subordinate to the ID lists. It is a free-form env var,
        // so an ID could appear there and nowhere else - and a caller that got an amount but no
        // term would have to assume one. Revenue reporting assumes monthly, which turns a
        // half-configured YEARLY price into a silent 12x overcount. Refusing the amount makes
        // that config mistake an undercount instead, which is visible.
        if (! self::tierFor($priceId)) {
            return null;
        }

        foreach (self::CURRENT_KEYS as $key) {
            if ($priceId === (config('services.stripe_platform.'.$key) ?: null)) {
                return (float) config('services.stripe_platform.'.$key.'_amount');
            }
        }

        foreach (self::split(config('services.stripe_platform.legacy_price_amounts')) as $pair) {
            $parts = explode(':', $pair, 2);

            if (count($parts) === 2 && trim($parts[0]) === $priceId && is_numeric(trim($parts[1]))) {
                return (float) trim($parts[1]);
            }
        }

        return null;
    }

    private static function slot(string $tier, string $term): string
    {
        return strtolower($tier).'.'.strtolower($term);
    }

    /**
     * Split a comma-separated env value into trimmed, non-empty pieces.
     *
     * @return array<int, string>
     */
    private static function split(?string $value): array
    {
        if (! $value) {
            return [];
        }

        return array_values(array_filter(array_map('trim', explode(',', $value)), fn ($v) => $v !== ''));
    }
}
