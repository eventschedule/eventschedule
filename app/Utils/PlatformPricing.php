<?php

namespace App\Utils;

use App\Models\Setting;

/**
 * What this installation ADVERTISES its plans at.
 *
 * This is a label, not a price. What a customer is actually charged comes from the Stripe
 * Price object the STRIPE_PRICE_* IDs point at, and nothing in the app reconciles the two.
 *
 * That distinction is the whole reason this class exists apart from PlanPriceUtils. Ask the
 * two questions separately:
 *
 *   "what do we advertise Pro at today?"  -> here, admin-settable
 *   "what is THIS subscription billed?"   -> PlanPriceUtils, from the Stripe price ID, and
 *                                            never steerable from a form
 *
 * PlanPriceUtils::amountFor() stands in for a Stripe API call - SendSubscriptionReminders
 * prefers the live unit_amount and only falls back to it - and it feeds ARR and MRR. If a
 * super-admin could move those, a renewal email would quote a figure the card will never be
 * charged and historical revenue would restate itself. So the billing-fact readers stay on
 * config, permanently, and MarketingPriceTest pins the split in both directions.
 *
 * Setting first, config second, mirroring PlatformCurrency: the .env value is the starting
 * default and the admin panel is the source of truth.
 */
class PlatformPricing
{
    /**
     * setting key => config key, for the four amounts we quote.
     */
    private const KEYS = [
        'pro.monthly' => ['plan_price_pro_monthly', 'price_monthly_amount', '9'],
        'pro.yearly' => ['plan_price_pro_yearly', 'price_yearly_amount', '90'],
        'enterprise.monthly' => ['plan_price_enterprise_monthly', 'enterprise_price_monthly_amount', '29'],
        'enterprise.yearly' => ['plan_price_enterprise_yearly', 'enterprise_price_yearly_amount', '290'],
    ];

    /**
     * Per-request memo of all four, filled in one pass. Setting::get() reaches the cache store
     * on every call and /pricing renders ~40 prices in one response, so resolving once matters -
     * the same reasoning as PlatformCurrency::$code.
     *
     * @var array<string, float>|null
     */
    private static $amounts = null;

    /**
     * @param  string  $tier  'pro' or 'enterprise'
     * @param  string  $term  'monthly' or 'yearly'
     */
    public static function amount(string $tier, string $term): float
    {
        if (self::$amounts === null) {
            $resolved = [];

            foreach (self::KEYS as $slot => [$settingKey, $configKey, $default]) {
                // ?: at both hops, never ??. Setting::set($key, null) writes a row whose value
                // is NULL, so ?? would never reach config; and env() returns '' for the
                // present-but-empty vars .env.example ships, which (float) would price at 0.
                $resolved[$slot] = (float) (
                    Setting::get($settingKey)
                    ?: config('services.stripe_platform.'.$configKey)
                    ?: $default
                );
            }

            self::$amounts = $resolved;
        }

        return self::$amounts[$tier.'.'.$term] ?? 0.0;
    }

    public static function proMonthly(): float
    {
        return self::amount('pro', 'monthly');
    }

    public static function proYearly(): float
    {
        return self::amount('pro', 'yearly');
    }

    public static function enterpriseMonthly(): float
    {
        return self::amount('enterprise', 'monthly');
    }

    public static function enterpriseYearly(): float
    {
        return self::amount('enterprise', 'yearly');
    }

    /**
     * The four amounts under the names the marketing views already use, so the view composer
     * in AppServiceProvider is a single call.
     */
    public static function all(): array
    {
        return [
            'proMonthly' => self::proMonthly(),
            'proYearly' => self::proYearly(),
            'entMonthly' => self::enterpriseMonthly(),
            'entYearly' => self::enterpriseYearly(),
        ];
    }

    /**
     * A plan amount in a currency's smallest unit, for the one caller that turns an advertised
     * price into real money: the referral credit posted to a Stripe customer balance.
     *
     * The rounding order is the whole point. This lived inline as
     * `-$multiplier * (int) $amount`, which truncated BEFORE multiplying - once amounts became
     * settable to two decimal places, a plan advertised at 9.99 would have credited 900 minor
     * units instead of 999, on every referral, in silence. Multiply first, round last.
     *
     * The currency is passed in rather than read here: it must be Cashier's, not the platform's,
     * because the balance transaction is posted in Cashier's preferred currency. See the comment
     * at the call site in ReferralController.
     */
    public static function minorUnits(string $tier, string $term, ?string $currencyCode): int
    {
        return (int) round(
            MoneyUtils::getSmallestUnitMultiplier($currencyCode ?: 'usd') * self::amount($tier, $term)
        );
    }

    /**
     * The raw stored override, or null when there is none.
     *
     * For the admin form ONLY, so it can render an empty field with the effective value as a
     * placeholder - otherwise an operator cannot tell "unset, defaulting to 9" from "set to 9",
     * and so cannot tell whether clearing the field will change anything. Everything else must
     * call amount(), which is the number actually in force.
     */
    public static function stored(string $tier, string $term): ?string
    {
        [$settingKey] = self::KEYS[$tier.'.'.$term] ?? [null];

        if (! $settingKey) {
            return null;
        }

        $value = Setting::get($settingKey);

        return ($value === null || $value === '') ? null : (string) $value;
    }

    /** The setting key for a slot, so the controller and its tests name it in one place. */
    public static function settingKey(string $tier, string $term): ?string
    {
        return self::KEYS[$tier.'.'.$term][0] ?? null;
    }

    /** Every setting key this class owns, in display order. */
    public static function settingKeys(): array
    {
        return array_column(self::KEYS, 0);
    }

    /**
     * Drop the memo. Call after saving, so the redirect that follows renders the new prices
     * rather than the ones the request started with - and from the queue's looping hook, or a
     * worker alive for days keeps quoting its boot-time price in renewal emails.
     */
    public static function flush(): void
    {
        self::$amounts = null;
    }
}
