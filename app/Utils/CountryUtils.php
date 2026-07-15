<?php

namespace App\Utils;

class CountryUtils
{
    protected static ?array $countries = null;

    protected static ?array $alpha3 = null;

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

    /**
     * Get the ISO 3166-1 alpha-3 (lowercase) => alpha-2 (lowercase) map.
     */
    public static function getAlpha3Map(): array
    {
        if (static::$alpha3 === null) {
            static::$alpha3 = json_decode(file_get_contents(database_path('geoip/country_alpha3.json')), true) ?? [];
        }

        return static::$alpha3;
    }

    /**
     * Normalize a country code to lowercase ISO 3166-1 alpha-2.
     *
     * Converts a 3-letter alpha-3 code (e.g. "ISR") to its alpha-2 equivalent
     * ("il"). Non-destructive: an already-alpha-2 value is lowercased and
     * passed through, and an unrecognized value is returned lowercased and
     * unchanged rather than dropped. Empty/null input returns null.
     */
    public static function normalizeCountryCode(?string $code): ?string
    {
        if ($code === null) {
            return null;
        }

        $code = strtolower(trim($code));

        if ($code === '') {
            return null;
        }

        if (strlen($code) === 3) {
            $map = static::getAlpha3Map();
            if (isset($map[$code])) {
                return $map[$code];
            }
        }

        return $code;
    }
}
