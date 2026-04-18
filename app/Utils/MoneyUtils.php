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

    private static $displayCodeOverrides = [
        'ILS' => 'NIS',
    ];

    public static function format($amount, $currencyCode)
    {
        $upper = strtoupper($currencyCode);
        $decimals = in_array($upper, self::$zeroDecimalCurrencies) ? 0 : 2;

        $formatted = number_format($amount, $decimals, '.', ',');

        if ($decimals === 2 && str_ends_with($formatted, '.00')) {
            $formatted = substr($formatted, 0, -3);
        }

        $displayCode = self::$displayCodeOverrides[$upper] ?? $currencyCode;

        return $formatted.' '.$displayCode;
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

        return $map[strtoupper($countryCode ?? '')] ?? 'USD';
    }
}
