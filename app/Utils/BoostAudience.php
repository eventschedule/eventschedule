<?php

namespace App\Utils;

use App\Models\Event;

/**
 * A boost's audience in words ("Within 25 miles of Harbor Cafe, ages 25 to 65"), for the
 * purchase form, a partner's request card and the API's `audience.summary`. One place, so the
 * three never describe the same targeting differently.
 */
class BoostAudience
{
    public static function geo(?array $targeting, ?Event $event = null): string
    {
        $geoLocations = $targeting['geo_locations'] ?? [];

        if (! empty($geoLocations['custom_locations'])) {
            $loc = $geoLocations['custom_locations'][0];
            $venue = $event?->venue;
            $locationName = $venue ? ($venue->shortAddress() ?: $venue->translatedName()) : '';
            $unit = $loc['distance_unit'] ?? 'mile';

            return __('messages.geo_radius_description', [
                'radius' => $loc['radius'] ?? 25,
                'unit' => $unit === 'mile' ? __('messages.miles') : __('messages.kilometers'),
                'location' => $locationName,
            ]);
        }

        if (! empty($geoLocations['countries'])) {
            return collect($geoLocations['countries'])->map(fn ($code) => self::countryName((string) $code))->implode(', ');
        }

        return '';
    }

    public static function summary(?array $targeting, ?Event $event = null): string
    {
        $parts = array_filter([self::geo($targeting, $event)]);

        if (! empty($targeting['age_min']) || ! empty($targeting['age_max'])) {
            $parts[] = __('messages.boost_audience_ages', [
                'min' => $targeting['age_min'] ?? 18,
                'max' => $targeting['age_max'] ?? 65,
            ]);
        }

        return implode(', ', $parts);
    }

    public static function countryName(string $code): string
    {
        $map = [
            'US' => 'United States', 'GB' => 'United Kingdom', 'CA' => 'Canada',
            'AU' => 'Australia', 'DE' => 'Germany', 'FR' => 'France',
            'ES' => 'Spain', 'IT' => 'Italy', 'NL' => 'Netherlands',
            'BR' => 'Brazil', 'MX' => 'Mexico', 'IL' => 'Israel',
            'PT' => 'Portugal', 'IE' => 'Ireland', 'NZ' => 'New Zealand',
            'AT' => 'Austria', 'CH' => 'Switzerland', 'BE' => 'Belgium',
            'SE' => 'Sweden', 'NO' => 'Norway', 'DK' => 'Denmark',
            'FI' => 'Finland', 'JP' => 'Japan', 'KR' => 'South Korea',
            'IN' => 'India', 'AR' => 'Argentina', 'CL' => 'Chile',
            'CO' => 'Colombia', 'PL' => 'Poland', 'RU' => 'Russia',
            'ZA' => 'South Africa', 'EE' => 'Estonia', 'SA' => 'Saudi Arabia',
            'AE' => 'United Arab Emirates', 'EG' => 'Egypt',
        ];

        return $map[strtoupper($code)] ?? strtoupper($code);
    }
}
