<?php

namespace App\Utils;

use App\Models\Setting;

/**
 * The currency this installation quotes ITS OWN prices in: plan amounts on the
 * marketing site and the upgrade prompts, and the fallback default for a new
 * event that has nothing better to go on.
 *
 * This is not the currency a ticket is sold in. That lives on the row
 * (events.ticket_currency_code) and is read with MoneyUtils::format() - a sale
 * must always render the currency it was actually taken in, whatever an
 * operator later sets here.
 *
 * Setting first, config second, mirroring AdsService::setting() and
 * Stay22Service (see the note in config/ads.php): the .env value is the
 * starting default and the admin panel is the source of truth.
 */
class PlatformCurrency
{
    /**
     * Per-request memo. Setting::get() reaches the cache store on every call and
     * /pricing renders ~40 prices in one response, so resolving once matters.
     *
     * @var string|null
     */
    private static $code = null;

    public static function code(): string
    {
        if (self::$code === null) {
            self::$code = strtoupper(
                Setting::get('platform_currency') ?: config('app.platform_currency', 'USD')
            );
        }

        return self::$code;
    }

    public static function symbol(): string
    {
        return MoneyUtils::symbol(self::code());
    }

    public static function format($amount): string
    {
        return MoneyUtils::format($amount, self::code());
    }

    /**
     * Drop the memo. Call after saving the setting, so the redirect that follows
     * renders the new currency rather than the one the request started with.
     */
    public static function flush(): void
    {
        self::$code = null;
    }
}
