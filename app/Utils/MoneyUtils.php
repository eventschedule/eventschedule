<?php

namespace App\Utils;

class MoneyUtils
{
    public static function format($amount, $currencyCode)
    {
        return number_format($amount, 2, '.', ',').' '.$currencyCode;
    }

    public static function getCurrencyForCountry($countryCode)
    {
        $map = [
            'AU' => 'AUD',
            'BR' => 'BRL',
            'GB' => 'GBP',
            'CA' => 'CAD',
            'HK' => 'HKD',
            'IN' => 'INR',
            'MY' => 'MYR',
            'IL' => 'ILS',
            'NZ' => 'NZD',
            'SG' => 'SGD',
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
