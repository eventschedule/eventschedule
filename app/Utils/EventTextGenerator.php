<?php

namespace App\Utils;

use App\Models\Role;

class EventTextGenerator
{
    /**
     * Generate event text content for a collection of events
     */
    public static function generate($role, $events, $directRegistration = false, $template = null, $urlSettings = [])
    {
        $text = '';

        if (empty($template)) {
            $template = self::getDefaultTemplate();
        }

        foreach ($events as $event) {
            $text .= self::parseTemplate($template, $event, $role, $directRegistration, $urlSettings);
            $text .= "\n\n";
        }

        return $text;
    }

    /**
     * Get the default text template
     */
    public static function getDefaultTemplate()
    {
        return "*{day_name}* {date_dmy} | {time}\n*{event_name}*:\n{venue} | {city}\n{url}";
    }

    /**
     * Parse a template string with event data
     */
    public static function parseTemplate($template, $event, $role, $directRegistration, $urlSettings = [])
    {
        // Set Carbon locale for translated date formats
        $locale = $role->language_code ?? 'en';
        \Carbon\Carbon::setLocale($locale);

        $startDate = $event->getStartDateTime(null, true);
        $endDate = $event->getEndDateTime(null, true);

        // Determine time format based on role's 24h setting
        $timeFormat = $role->use_24_hour_time ? 'H:i' : 'g:i A';

        // Build the URL
        $eventUrl = $event->getGuestUrl($role->subdomain, null, true);
        if ($directRegistration && $event->registration_url) {
            if (str_contains($eventUrl, '?')) {
                $eventUrl = str_replace('?', '/?', $eventUrl);
            } else {
                $eventUrl .= '/';
            }
        }

        // Get URL formatting settings
        $urlIncludeHttps = $urlSettings['url_include_https'] ?? false;
        $urlIncludeId = $urlSettings['url_include_id'] ?? false;
        $isRecurring = ! empty($event->days_of_week);

        // Remove HTTPS if not wanted
        if (! $urlIncludeHttps) {
            $eventUrl = preg_replace('#^https?://#', '', $eventUrl);
        }

        // Remove event ID from URL if not wanted (but always keep for recurring events)
        if (! $urlIncludeId && ! $isRecurring) {
            $eventUrl = preg_replace('#/[A-Za-z0-9+/=]+(\?|$)#', '$1', $eventUrl);
        }

        // Build replacements array
        $replacements = [
            // Date/Time variables
            '{day_name}' => $startDate->translatedFormat('l'),
            '{day_short}' => $startDate->translatedFormat('D'),
            '{date_dmy}' => $startDate->format('j/n'),
            '{date_mdy}' => $startDate->format('n/j'),
            '{date_full_dmy}' => $startDate->format('d/m/Y'),
            '{date_full_mdy}' => $startDate->format('m/d/Y'),
            '{month}' => $startDate->format('n'),
            '{month_name}' => $startDate->translatedFormat('F'),
            '{month_short}' => $startDate->translatedFormat('M'),
            '{day}' => $startDate->format('j'),
            '{year}' => $startDate->format('Y'),
            '{time}' => $startDate->format($timeFormat),
            '{end_time}' => $endDate ? $endDate->format($timeFormat) : '',
            '{duration}' => $event->duration ?? '',

            // Event variables
            '{event_name}' => $event->translatedName(),
            '{short_description}' => $event->translatedShortDescription() ?? '',
            '{description}' => $event->translatedDescription() ?? '',
            '{url}' => $eventUrl,

            // Venue variables
            '{venue}' => $event->venue ? ($event->venue->translatedName() ?? '') : '',
            '{city}' => $event->venue ? ($event->venue->translatedCity() ?? '') : '',
            '{address}' => $event->venue ? ($event->venue->address1 ?? '') : '',
            '{state}' => $event->venue ? ($event->venue->state ?? '') : '',
            '{country}' => $event->venue ? ($event->venue->country ?? '') : '',

            // Ticket variables
            '{currency}' => $event->ticket_currency_code ?? '',
            '{price}' => self::getPrice($event),
        ];

        // Add custom field replacements using stable indices
        $customFieldValues = $event->custom_field_values ?? [];
        $roleCustomFields = $role->event_custom_fields ?? [];
        // Initialize all custom fields to empty
        for ($i = 1; $i <= 8; $i++) {
            $replacements['{custom_'.$i.'}'] = '';
        }
        // Fill in values using stored index, or fallback to iteration order for backward compatibility
        $fallbackIndex = 1;
        foreach ($roleCustomFields as $fieldKey => $fieldConfig) {
            $index = $fieldConfig['index'] ?? $fallbackIndex;
            $fallbackIndex++;
            if ($index >= 1 && $index <= 8) {
                $value = $customFieldValues[$fieldKey] ?? '';
                // Convert boolean values to Yes/No for switch type
                if (($fieldConfig['type'] ?? '') === 'switch') {
                    $value = ($value === '1' || $value === 1 || $value === true) ? __('messages.yes') : __('messages.no');
                }
                $replacements['{custom_'.$index.'}'] = $value;
            }
        }

        $result = str_replace(array_keys($replacements), array_values($replacements), $template);

        // Clean up orphaned | separators when venue or city (or other fields) are blank
        $resultLines = explode("\n", $result);
        $resultLines = array_map(function ($line) {
            $line = preg_replace('/\s*\|\s*\|\s*/', ' | ', $line); // collapse "| |" into single "|"
            $line = preg_replace('/^\s*\|\s*/', '', $line);        // trim leading "|"
            $line = preg_replace('/\s*\|\s*$/', '', $line);        // trim trailing "|"

            return $line;
        }, $resultLines);
        $result = implode("\n", $resultLines);

        // Remove lines where all variables were blank (only separators/formatting remain)
        $lines = explode("\n", $result);
        $filteredLines = array_filter($lines, function ($line) {
            // Remove formatting characters and whitespace to check if line has real content
            $stripped = preg_replace('/[\s\*\|\:\-\,\.]+/', '', $line);

            return $stripped !== '';
        });

        return implode("\n", $filteredLines);
    }

    /**
     * Get the formatted price for an event
     */
    public static function getPrice($event)
    {
        // First check for internal tickets
        if ($event->tickets && ! $event->tickets->isEmpty()) {
            $prices = $event->tickets->pluck('price')->filter(function ($price) {
                return $price !== null;
            });

            if (! $prices->isEmpty()) {
                // Check if all tickets are free
                $allFree = $prices->every(function ($price) {
                    return $price === 0;
                });

                if ($allFree) {
                    return __('messages.free');
                }

                // Return lowest price
                return $prices->min();
            }
        }

        // Check for external event price (when tickets are disabled)
        if (! $event->tickets_enabled && $event->ticket_price !== null) {
            if ($event->ticket_price == 0) {
                return __('messages.free');
            }

            return $event->ticket_price;
        }

        return '';
    }
}
