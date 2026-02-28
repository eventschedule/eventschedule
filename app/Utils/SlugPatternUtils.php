<?php

namespace App\Utils;

use App\Models\Event;
use App\Models\Role;
use Illuminate\Support\Str;

class SlugPatternUtils
{
    /**
     * Generate a slug for an event based on a pattern or the event name.
     *
     * @param  string|null  $pattern  The slug pattern template (e.g., "{event_name}-{date_dmy}")
     * @param  string  $eventName  The event name
     * @param  string|null  $eventNameEn  The English event name (optional)
     * @param  Event|null  $event  The event object (for date/venue/custom fields)
     * @param  Role|null  $role  The role object (for custom field definitions)
     * @param  Role|null  $venue  The venue object (optional, used when venue relationship isn't loaded yet)
     * @return string The generated slug
     */
    public static function generateSlug(?string $pattern, string $eventName, ?string $eventNameEn, ?Event $event, ?Role $role, ?Role $venue = null): string
    {
        // If no pattern, use default behavior (slug from event name)
        if (empty($pattern)) {
            return self::defaultSlug($eventName, $eventNameEn);
        }

        // Parse the pattern
        $slug = self::parsePattern($pattern, $eventName, $eventNameEn, $event, $role, $venue);

        // Apply Str::slug to ensure URL-safe output
        $slug = Str::slug($slug);

        // If result is empty, fall back to default behavior
        if (empty($slug)) {
            return self::defaultSlug($eventName, $eventNameEn);
        }

        return $slug;
    }

    /**
     * Generate a default slug from the event name.
     */
    private static function defaultSlug(string $eventName, ?string $eventNameEn): string
    {
        // Prefer English name if provided
        if ($eventNameEn) {
            $slug = Str::slug($eventNameEn);
            if ($slug) {
                return $slug;
            }
        }

        // Try the original name
        $slug = Str::slug($eventName);

        if ($slug) {
            return $slug;
        }

        // Try translating to English
        $translated = GeminiUtils::translate($eventName, 'auto', 'en');
        if ($translated) {
            $slug = Str::slug($translated);
            if ($slug) {
                return $slug;
            }
        }

        // Final fallback: random string
        return strtolower(Str::random(5));
    }

    /**
     * Parse a pattern template and replace variables with values.
     */
    private static function parsePattern(string $pattern, string $eventName, ?string $eventNameEn, ?Event $event, ?Role $role, ?Role $venue = null): string
    {
        // Build replacements array
        $replacements = [
            '{event_name}' => $eventNameEn ?: $eventName,
        ];

        // Use provided venue or fall back to event relationship
        $venue = $venue ?? $event?->venue;

        // Add date/time variables if event has a start date
        if ($event && $event->starts_at) {
            $startDate = $event->getStartDateTime(null, true);
            $endDate = $event->getEndDateTime(null, true);

            // Set locale for translated date formats
            $locale = $role?->language_code ?? 'en';
            \Carbon\Carbon::setLocale($locale);

            // Determine time format based on role's 24h setting
            $timeFormat = $role?->use_24_hour_time ? 'H-i' : 'g-i-A';

            $replacements = array_merge($replacements, [
                // Date/Time variables (use - instead of / or : for URL safety)
                '{day_name}' => $startDate->translatedFormat('l'),
                '{day_short}' => $startDate->translatedFormat('D'),
                '{date_dmy}' => $startDate->format('j-n'),
                '{date_mdy}' => $startDate->format('n-j'),
                '{date_full_dmy}' => $startDate->format('d-m-Y'),
                '{date_full_mdy}' => $startDate->format('m-d-Y'),
                '{month}' => $startDate->format('n'),
                '{month_name}' => $startDate->translatedFormat('F'),
                '{month_short}' => $startDate->translatedFormat('M'),
                '{day}' => $startDate->format('j'),
                '{year}' => $startDate->format('Y'),
                '{time}' => $startDate->format($timeFormat),
                '{end_time}' => $endDate ? $endDate->format($timeFormat) : '',
                '{duration}' => $event->duration ?? '',
            ]);
        } else {
            // Fill date variables with empty strings if no event date
            $replacements = array_merge($replacements, [
                '{day_name}' => '',
                '{day_short}' => '',
                '{date_dmy}' => '',
                '{date_mdy}' => '',
                '{date_full_dmy}' => '',
                '{date_full_mdy}' => '',
                '{month}' => '',
                '{month_name}' => '',
                '{month_short}' => '',
                '{day}' => '',
                '{year}' => '',
                '{time}' => '',
                '{end_time}' => '',
                '{duration}' => '',
            ]);
        }

        // Add venue variables
        $replacements = array_merge($replacements, [
            '{venue}' => $venue ? ($venue->translatedName() ?? '') : '',
            '{city}' => $venue ? ($venue->translatedCity() ?? '') : '',
            '{address}' => $venue ? ($venue->address1 ?? '') : '',
            '{state}' => $venue ? ($venue->state ?? '') : '',
            '{country}' => $venue ? ($venue->country ?? '') : '',
        ]);

        // Add ticket variables
        $replacements = array_merge($replacements, [
            '{currency}' => $event?->ticket_currency_code ?? '',
            '{price}' => self::getPrice($event),
            '{coupon_code}' => $event?->coupon_code ?? '',
        ]);

        // Add custom field replacements using stable indices
        $customFieldValues = $event?->custom_field_values ?? [];
        $roleCustomFields = $role?->event_custom_fields ?? [];
        // Initialize all custom fields to empty
        for ($i = 1; $i <= 10; $i++) {
            $replacements['{custom_'.$i.'}'] = '';
        }
        // Fill in values using stored index, or fallback to iteration order for backward compatibility
        $fallbackIndex = 1;
        foreach ($roleCustomFields as $fieldKey => $fieldConfig) {
            $index = $fieldConfig['index'] ?? $fallbackIndex;
            $fallbackIndex++;
            if ($index >= 1 && $index <= 10) {
                $value = $customFieldValues[$fieldKey] ?? '';
                // Convert boolean values to Yes/No for switch type
                if (($fieldConfig['type'] ?? '') === 'switch') {
                    $value = ($value === '1' || $value === 1 || $value === true) ? 'yes' : 'no';
                }
                $replacements['{custom_'.$index.'}'] = $value;
            }
        }

        return str_replace(array_keys($replacements), array_values($replacements), $pattern);
    }

    /**
     * Get the price from an event's tickets.
     */
    private static function getPrice(?Event $event): string
    {
        if (! $event) {
            return '';
        }

        // First check for internal tickets
        if ($event->tickets && ! $event->tickets->isEmpty()) {
            $prices = $event->tickets->pluck('price')->filter(function ($price) {
                return $price !== null;
            });

            if (! $prices->isEmpty()) {
                $min = $prices->min();
                $max = $prices->max();

                if ($min === $max) {
                    return (string) $min;
                }

                return $min.'-'.$max;
            }
        }

        // Check for external event price (when tickets are disabled)
        if (! $event->tickets_enabled && $event->ticket_price !== null) {
            return (string) $event->ticket_price;
        }

        return '';
    }
}
