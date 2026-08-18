<?php

namespace App\Utils;

class MoneyUtils
{
    /**
     * Zero-decimal currencies where Stripe amounts are in whole units (not cents).
     */
    private static $zeroDecimalCurrencies = [
        'BIF', 'CLP', 'DJF', 'GNF', 'JPY', 'KMF', 'KRW', 'MGA',
        'PYG', 'RWF', 'UGX', 'VND', 'VUV', 'XAF', 'XOF', 'XPF',
    ];

    private static $displayCodeOverrides = [];

    private static $displaySymbols = [
        'ILS' => '₪',
        'USD' => '$',
        'EUR' => '€',
        'GBP' => '£',
        'JPY' => '¥',
        'ZAR' => 'R',
        'INR' => '₹',
        'AUD' => 'A$',
        'CAD' => 'C$',
        'BRL' => 'R$',
    ];

    public static function decimalsFor($currencyCode): int
    {
        if (! $currencyCode) {
            return 2;
        }

        return in_array(strtoupper($currencyCode), self::$zeroDecimalCurrencies) ? 0 : 2;
    }

    public static function format($amount, $currencyCode)
    {
        // Null-safe, because plenty of callers read the code off a nullable relation
        // ($sale->event?->ticket_currency_code). Without this, strtoupper(null) is deprecated
        // on PHP 8.1 and the amount renders as a bare "120.50 " with no currency at all -
        // worse than the wrong symbol. USD matches what the columns default to.
        $upper = strtoupper((string) $currencyCode);

        if ($upper === '') {
            $upper = 'USD';
            $currencyCode = 'USD';
        }
        $decimals = self::decimalsFor($upper);

        $formatted = number_format($amount, $decimals, '.', ',');

        if ($decimals === 2 && str_ends_with($formatted, '.00')) {
            $formatted = substr($formatted, 0, -3);
        }

        if (isset(self::$displaySymbols[$upper])) {
            return self::$displaySymbols[$upper].$formatted;
        }

        $displayCode = self::$displayCodeOverrides[$upper] ?? $currencyCode;

        return $formatted.' '.$displayCode;
    }

    /**
     * The glyph for a currency, for the few places that need a bare symbol next to a
     * number they format themselves (a budget input's label, a JS-driven counter).
     * Prefer format(), which puts the two together correctly.
     *
     * Falls back to the code itself, matching what format() does for a currency with
     * no glyph: "CHF", not a dollar sign that would name the wrong money.
     */
    public static function symbol(?string $currencyCode): string
    {
        $upper = strtoupper($currencyCode ?? '');

        if ($upper === '') {
            $upper = 'USD';
        }

        return self::$displaySymbols[$upper] ?? self::$displayCodeOverrides[$upper] ?? $upper;
    }

    /**
     * The currencies an owner may pick from, as {value, label} objects.
     *
     * Memoized: this file is otherwise re-read with a raw file_get_contents at every
     * call site, one of them inside a Blade view.
     */
    public static function currencies(): array
    {
        static $currencies = null;

        if ($currencies === null) {
            $json = @file_get_contents(base_path('storage/currencies.json'));
            $currencies = $json ? (json_decode($json) ?: []) : [];
        }

        return $currencies;
    }

    /** Just the codes, for validating a submitted currency. */
    public static function currencyCodes(): array
    {
        return array_column(self::currencies(), 'value');
    }

    public static function getSmallestUnitMultiplier($currencyCode)
    {
        return in_array(strtoupper($currencyCode), self::$zeroDecimalCurrencies) ? 1 : 100;
    }

    public static function getCurrencyForCountry($countryCode)
    {
        $map = [
            'AU' => 'AUD',
            'BR' => 'BRL',
            'GB' => 'GBP',
            'CA' => 'CAD',
            'CH' => 'CHF',
            'CZ' => 'CZK',
            'DK' => 'DKK',
            'HK' => 'HKD',
            'HU' => 'HUF',
            'IN' => 'INR',
            'JP' => 'JPY',
            'KR' => 'KRW',
            'MX' => 'MXN',
            'MY' => 'MYR',
            'IL' => 'ILS',
            'NO' => 'NOK',
            'NZ' => 'NZD',
            'PH' => 'PHP',
            'PL' => 'PLN',
            'RO' => 'RON',
            'SE' => 'SEK',
            'SG' => 'SGD',
            'TH' => 'THB',
            'TR' => 'TRY',
            'ZA' => 'ZAR',
            'ID' => 'IDR',
            // Eurozone
            'DE' => 'EUR',
            'FR' => 'EUR',
            'IT' => 'EUR',
            'ES' => 'EUR',
            'PT' => 'EUR',
            'NL' => 'EUR',
            'AT' => 'EUR',
            'BE' => 'EUR',
            'FI' => 'EUR',
            'IE' => 'EUR',
            'GR' => 'EUR',
            'LU' => 'EUR',
            'MT' => 'EUR',
            'CY' => 'EUR',
            'SK' => 'EUR',
            'SI' => 'EUR',
            'LV' => 'EUR',
            'LT' => 'EUR',
            'EE' => 'EUR',
            'HR' => 'EUR',
        ];

        // The installation's own currency, not a hardcoded USD: an operator whose schedules
        // never fill in a country still gets their money, and setting nothing keeps USD.
        return $map[strtoupper($countryCode ?? '')] ?? PlatformCurrency::code();
    }
}
