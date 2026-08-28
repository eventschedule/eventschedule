<?php

namespace App\Utils;

/**
 * The one place that maps a Stripe Price ID onto a plan tier and term.
 *
 * Nothing in this app asks Stripe what a subscription costs. Tier and term are decided by string
 * matching the locally stored `subscriptions.stripe_price` against the four configured price IDs.
 *
 * Stripe Prices are immutable, so changing what a plan costs means creating a NEW Price object and
 * pointing config at it, while existing subscriptions keep billing on the old one forever. This
 * class recognizes ONLY the four current IDs, so a price change has to be made by repointing
 * STRIPE_PRICE_* at the Price objects your subscribers are actually on - anything left behind
 * stops resolving, and tierFor()/termFor() start returning null for it.
 *
 * What that null costs is set out on tierFor(). It is a real cost, and it is why the resolution
 * here is deliberately strict rather than best-effort: a wrong tier is worse than no tier.
 */
class PlanPriceUtils
{
    /** @var array<string, string> tier+term => the config key holding the price ID */
    private const CURRENT_KEYS = [
        'pro.monthly' => 'price_monthly',
        'pro.yearly' => 'price_yearly',
        'enterprise.monthly' => 'enterprise_price_monthly',
        'enterprise.yearly' => 'enterprise_price_yearly',
    ];

    /**
     * The price ID for this tier and term - what a new subscription is created at, and the only
     * value that resolves back to a tier.
     *
     * @param  string  $tier  pro|enterprise
     * @param  string  $term  monthly|yearly
     */
    public static function current(string $tier, string $term): ?string
    {
        $key = self::CURRENT_KEYS[strtolower($tier).'.'.strtolower($term)] ?? null;

        if (! $key) {
            return null;
        }

        return config('services.stripe_platform.'.$key) ?: null;
    }

    /**
     * Every price ID that grants Enterprise, across both terms.
     *
     * @return array<int, string>
     */
    public static function enterpriseIds(): array
    {
        return array_values(array_filter([
            self::current('enterprise', 'monthly'),
            self::current('enterprise', 'yearly'),
        ]));
    }

    /**
     * Every price ID billed yearly, across both tiers.
     *
     * @return array<int, string>
     */
    public static function yearlyIds(): array
    {
        return array_values(array_filter([
            self::current('pro', 'yearly'),
            self::current('enterprise', 'yearly'),
        ]));
    }

    /**
     * The tier a stored price ID belongs to, or null if it is not one we know.
     *
     * Null is load-bearing: callers must decline to write rather than assume Pro. A price ID we
     * do not recognize means config is incomplete, and guessing "pro" there is exactly how a
     * paying Enterprise customer gets a downgrade persisted to their role row.
     *
     * What an unrecognized ID actually costs, so the strictness is a known trade and not a
     * surprise: hasActiveEnterpriseSubscription() goes false, so Enterprise features are withdrawn
     * while the card keeps being charged the Enterprise rate; both webhook handlers decline to
     * write, so the role row freezes out of sync with Stripe; ARR counts the subscriber at zero
     * while MRR books them at the Pro estimate; and the renewal email is either skipped or
     * labelled "Pro". Only a Log::warning announces any of it.
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

        $proIds = array_filter([self::current('pro', 'monthly'), self::current('pro', 'yearly')]);

        return in_array($priceId, $proIds, true) ? 'pro' : null;
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

        $monthlyIds = array_filter([
            self::current('pro', 'monthly'),
            self::current('enterprise', 'monthly'),
        ]);

        return in_array($priceId, $monthlyIds, true) ? 'month' : null;
    }

    /**
     * What a price ID charges per billing period, in dollars, or null if it is not one we know.
     *
     * Returns null rather than a guess: quoting a subscriber a price they are not being charged
     * is worse than quoting nothing.
     *
     * Reads config, NOT PlatformPricing, and must keep doing so. This stands in for a Stripe API
     * call - SendSubscriptionReminders prefers the live unit_amount and only falls back here -
     * and it feeds ARR and MRR. Wiring it to the admin-settable amounts would let a marketing
     * change quote a customer a renewal figure their card will never be charged, and restate
     * revenue that was already booked. MarketingPriceTest pins this in both directions.
     */
    public static function amountFor(?string $priceId): ?float
    {
        // Not redundant with the loop below, despite both only ever matching a current ID: this
        // is also what rejects a NULL price. Without it, `null === (config(...) ?: null)` is true
        // for any tier the install does not sell, and the FIRST unconfigured key in CURRENT_KEYS
        // wins - so a Pro-only install answers a null price with enterprise_price_monthly_amount,
        // which is 15 by default. Do not delete this on the grounds that the loop below already
        // filters to configured IDs; it does not filter null.
        if (! $priceId) {
            return null;
        }

        foreach (self::CURRENT_KEYS as $key) {
            if ($priceId === (config('services.stripe_platform.'.$key) ?: null)) {
                return (float) config('services.stripe_platform.'.$key.'_amount');
            }
        }

        return null;
    }
}
