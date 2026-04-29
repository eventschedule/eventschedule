<?php

namespace App\Utils;

class CountryUtils
{
    protected static ?array $countries = null;

    /**
     * Get English country name from ISO 3166-1 alpha-2 code
     */
    public static function getName(string $code): string
    {
        return static::getCountries()[strtoupper($code)] ?? strtoupper($code);
    }

    /**
     * Get all country code to name mappings
     */
    public static function getCountries(): array
    {
        if (static::$countries === null) {
            static::$countries = json_decode(file_get_contents(database_path('geoip/countries.json')), true) ?? [];
        }

        return static::$countries;
    }
}
