<?php

namespace App\Utils;

use App\Models\Role;

class EventTextGenerator
{
    /**
     * Generate event text content for a collection of events
     */
    public static function generate($role, $events, $directRegistration = false, $template = null, $urlSettings = [], $forceEnglish = false)
    {
        $text = '';

        if (empty($template)) {
            $template = self::getDefaultTemplate();
        }

        // RTL-language schedules get bidi treatment so the text displays
        // right-to-left when pasted into apps like WhatsApp. Based on the
        // schedule's language_code, not Role::isRtl() (which is viewer-
        // dependent); forced-English text stays LTR.
        $isRtlLang = ! $forceEnglish && ($role->language_code == 'he' || $role->language_code == 'ar');

        // Normalize to 0-based sequential keys so {number} reflects position regardless of how the caller built the collection.
        $events = collect($events)->values();

        foreach ($events as $i => $event) {
            $text .= self::parseTemplate($template, $event, $role, $directRegistration, $urlSettings, $i + 1, $forceEnglish, $isRtlLang);
            $text .= "\n\n";
        }

        // Append "Want to see your event here?" message if schedule accepts event requests
        if ($role->acceptEventRequests()) {
            $lang = $forceEnglish ? 'en' : strtolower($role->language_code ?? 'en');
            $message = trans('messages.want_to_see_your_event_here', [], $lang);

            if ($role->custom_domain && ($role->custom_domain_mode !== 'direct' || $role->custom_domain_status === 'active')) {
                $url = $role->custom_domain.'/request';
            } else {
                $url = route('role.request', ['subdomain' => $role->subdomain]);
            }
            $url = preg_replace('#^https?://#', '', $url);

            // Mark the URL with a Left-to-Right Isolate (U+2066 ... U+2069) so the
            // per-line pass below can recognize this as a URL line. On its own line
            // the isolate is stripped back off (leaving a bare, link-detectable URL);
            // it only survives when a URL sits inline with other text.
            if ($isRtlLang) {
                $url = "\u{2066}".$url."\u{2069}";
            }

            $text .= $message."\n".$url."\n";
        }

        // For RTL languages, prepend a Right-to-Left Mark (U+200F) to every
        // non-empty text line. This forces each line - and, because the very first
        // character of the message is then an RLM, the whole message bubble - to
        // display right-to-left when pasted into apps like WhatsApp (which resolves
        // direction from the first strong directional character).
        //
        // A line that is nothing but a URL is the exception: it must be left
        // completely free of invisible bidi characters. WhatsApp's link
        // auto-detection is unreliable when any invisible char sits next to a URL -
        // a leading mark can stop the match from starting at the URL, and a trailing
        // one can be swallowed into the tapped href and corrupt it. So the isolate
        // added around the {url} token (parseTemplate) and the request URL above is
        // treated as a transient marker here: on a URL-only line it is stripped back
        // off and NO RLM is added, leaving a bare URL that resolves LTR and renders
        // left-aligned inside the RTL bubble (expected for a link). The isolate only
        // survives when a URL sits inline with other text (rare custom templates).
        // LTR-language and forced-English text get no marks at all.
        if ($isRtlLang) {
            $lines = array_map(function ($line) {
                if ($line === '') {
                    return $line;
                }

                // URL-only line: one isolate run alone on the line. Strip the
                // isolate and add no RLM so the URL stays clickable in WhatsApp.
                if (preg_match('/^\x{2066}[^\x{2066}\x{2069}]*\x{2069}$/u', trim($line)) === 1) {
                    return trim(str_replace(["\u{2066}", "\u{2069}"], '', $line));
                }

                // Text line (may contain an inline URL): keep the RLM so it stays
                // right-aligned; any inline isolate is preserved for display.
                return "\u{200F}".$line;
            }, explode("\n", $text));
            $text = implode("\n", $lines);
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
    public static function parseTemplate($template, $event, $role, $directRegistration, $urlSettings = [], $eventNumber = null, $forceEnglish = false, $isolateUrl = false)
    {
        $replacements = self::buildReplacements($event, $role, $directRegistration, $urlSettings, $eventNumber, $forceEnglish);

        // Mark the event URL with a Left-to-Right Isolate (U+2066 ... U+2069). For a
        // URL on its own line, generate() strips this back off to leave a bare,
        // link-detectable URL; when the URL sits inline with other text the isolate
        // survives and keeps it a clean LTR run inside the RTL line.
        if ($isolateUrl && ($replacements['{url}'] ?? '') !== '') {
            $replacements['{url}'] = "\u{2066}".$replacements['{url}']."\u{2069}";
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
     * Build the {token} => value replacement map shared by parseTemplate() and
     * parseInlineVariables(). Pass $date to target a specific occurrence and
     * $timezone to override the role timezone for date/time tokens.
     */
    private static function buildReplacements($event, $role, $directRegistration, $urlSettings = [], $eventNumber = null, $forceEnglish = false, $date = null, $timezone = null)
    {
        // Set Carbon locale for translated date formats
        $locale = $forceEnglish ? 'en' : ($role->language_code ?? 'en');
        \Carbon\Carbon::setLocale($locale);

        $tz = $timezone ?? $role->timezone ?? 'UTC';
        $startDate = $event->getStartDateTime($date, true, $tz);
        $endDate = $event->getEndDateTime($date, true, $tz);

        // Determine time format based on role's 24h setting
        $timeFormat = $role->use_24_hour_time ? 'H:i' : 'g:i A';

        // The compact date tokens omit the year to stay short, but an event in a
        // different year than "now" then looks out of chronological order in the
        // list (e.g. a Jul 2027 event renders as "11/7" and appears to precede a
        // Jul 2026 "19/7"). Append a 2-digit year only when the event's year
        // differs from the current year in the schedule timezone.
        $showYear = $startDate->year !== \Carbon\Carbon::now($tz)->year;
        $dmyFormat = $showYear ? 'j/n/y' : 'j/n';
        $mdyFormat = $showYear ? 'n/j/y' : 'n/j';

        // Get URL formatting settings
        $urlIncludeHttps = $urlSettings['url_include_https'] ?? false;
        $urlIncludeId = $urlSettings['url_include_id'] ?? false;

        // Build the URL. Passing $urlIncludeId omits the encoded event id (and,
        // for recurring events, the date segment) up front instead of stripping
        // it after, producing a clean slug-only link when the option is unchecked.
        $eventUrl = $event->getGuestUrl($role->subdomain, null, true, $urlIncludeId);
        if ($directRegistration && $event->registration_url) {
            if (str_contains($eventUrl, '?')) {
                $eventUrl = str_replace('?', '/?', $eventUrl);
            } else {
                $eventUrl .= '/';
            }
        }

        // Remove HTTPS if not wanted
        if (! $urlIncludeHttps) {
            $eventUrl = preg_replace('#^https?://#', '', $eventUrl);
        }

        // Build replacements array
        $replacements = [
            // Date/Time variables
            '{day_name}' => $startDate->translatedFormat('l'),
            '{day_short}' => $startDate->translatedFormat('D'),
            '{date_dmy}' => $startDate->format($dmyFormat),
            '{date_mdy}' => $startDate->format($mdyFormat),
            '{date_full_dmy}' => $startDate->format('d/m/Y'),
            '{date_full_mdy}' => $startDate->format('m/d/Y'),
            '{month}' => $startDate->format('n'),
            '{month_pad}' => $startDate->format('m'),
            '{month_name}' => $startDate->translatedFormat('F'),
            '{month_short}' => $startDate->translatedFormat('M'),
            '{day}' => $startDate->format('j'),
            '{day_pad}' => $startDate->format('d'),
            '{year}' => $startDate->format('Y'),
            '{time}' => $startDate->format($timeFormat),
            '{end_time}' => $endDate ? $endDate->format($timeFormat) : '',
            '{duration}' => $event->duration ?? '',

            // Event variables
            '{number}' => $eventNumber !== null ? (string) $eventNumber : '',
            '{event_name}' => $forceEnglish ? $event->englishName() : $event->name,
            '{short_description}' => ($forceEnglish ? $event->englishShortDescription() : $event->short_description) ?? '',
            '{description}' => self::htmlToPlainText($forceEnglish ? $event->englishDescriptionHtml() : $event->description_html),
            '{url}' => $eventUrl,

            // Venue variables
            '{venue}' => $event->venue ? (($forceEnglish ? $event->venue->englishName() : $event->venue->name) ?? '') : '',
            '{city}' => $event->venue ? (($forceEnglish ? $event->venue->englishCity() : $event->venue->city) ?? '') : '',
            '{address}' => $event->venue ? (($forceEnglish ? $event->venue->englishAddress1() : $event->venue->address1) ?? '') : '',
            '{state}' => $event->venue ? (($forceEnglish ? $event->venue->englishState() : $event->venue->state) ?? '') : '',
            '{country}' => $event->venue ? ($event->venue->country ?? '') : '',

            // Ticket variables
            '{currency}' => $event->ticket_currency_code ?? '',
            '{price}' => self::getPrice($event),
            '{coupon_code}' => $event->coupon_code ?? '',
        ];

        // Add custom field replacements using stable indices
        $customFieldValues = $event->custom_field_values ?? [];
        $roleCustomFields = $role->event_custom_fields ?? [];
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
                    $isOn = $value === '1' || $value === 1 || $value === true;
                    $value = trans($isOn ? 'messages.yes' : 'messages.no', [], $forceEnglish ? 'en' : null);
                }
                $replacements['{custom_'.$index.'}'] = $value;
            }
        }

        return $replacements;
    }

    /**
     * Substitute {tokens} in free-form text (e.g. event ticket notes) using the
     * same variable vocabulary as parseTemplate(), but WITHOUT its line-level
     * cleanup (which is tailored to the schedule-list format and would mangle
     * prose paragraphs and blank lines). Unknown tokens are left untouched.
     *
     * Options: 'date' (occurrence date), 'forceEnglish', 'urlSettings', and
     * 'escapeHtml' (HTML-escape each substituted value for safe HTML output).
     */
    public static function parseInlineVariables(string $template, $event, $role, array $opts = [])
    {
        if ($template === '' || strpos($template, '{') === false) {
            return $template;
        }

        $replacements = self::buildReplacements(
            $event,
            $role,
            false,
            $opts['urlSettings'] ?? [],
            null,
            $opts['forceEnglish'] ?? false,
            $opts['date'] ?? null,
            $event->timezone ?? $role->timezone ?? null,
        );

        if (! empty($opts['escapeHtml'])) {
            $replacements = array_map(
                fn ($value) => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'),
                $replacements
            );
        }

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }

    /**
     * Parse schedule-level variables for header/footer text.
     *
     * Unlike parseTemplate(), these tokens are context-free (no specific event).
     * Date tokens reflect "now" in the schedule's timezone; {first_event_date}
     * and {last_event_date} use the supplied events collection if provided.
     */
    public static function parseScheduleVariables(Role $role, string $text, $events = null, bool $forceEnglish = false): string
    {
        if ($text === '' || strpos($text, '{') === false) {
            return $text;
        }

        $locale = $forceEnglish ? 'en' : ($role->language_code ?? 'en');
        \Carbon\Carbon::setLocale($locale);

        $tz = $role->timezone ?? 'UTC';
        $now = \Carbon\Carbon::now($tz);

        $firstEventDate = '';
        $lastEventDate = '';
        if ($events !== null) {
            $collection = collect($events);
            if ($collection->isNotEmpty()) {
                $first = $collection->first();
                $last = $collection->last();
                if ($first && method_exists($first, 'getStartDateTime')) {
                    $firstEventDate = $first->getStartDateTime(null, true, $tz)->translatedFormat('M j');
                }
                if ($last && method_exists($last, 'getStartDateTime')) {
                    $lastEventDate = $last->getStartDateTime(null, true, $tz)->translatedFormat('M j');
                }
            }
        }

        $replacements = [
            '{schedule_name}' => ($forceEnglish ? $role->englishName() : $role->name) ?? '',
            '{year}' => $now->format('Y'),
            '{month}' => $now->format('n'),
            '{month_pad}' => $now->format('m'),
            '{month_name}' => $now->translatedFormat('F'),
            '{month_short}' => $now->translatedFormat('M'),
            '{day_name}' => $now->translatedFormat('l'),
            '{day_short}' => $now->translatedFormat('D'),
            '{first_event_date}' => $firstEventDate,
            '{last_event_date}' => $lastEventDate,
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $text);
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
                    return '';
                }

                // Return lowest price
                return floatval($prices->min());
            }
        }

        // Check for external event price (when tickets are disabled)
        if (! $event->tickets_enabled && $event->ticket_price !== null) {
            if ($event->ticket_price == 0) {
                return '';
            }

            return floatval($event->ticket_price);
        }

        return '';
    }

    /**
     * Convert rendered description HTML to clean plain text for text-only template
     * channels (iCal/CalDAV, graphics text layers, SMS). Preserves line/paragraph
     * breaks, strips tags, and decodes entities so markup never appears literally.
     */
    private static function htmlToPlainText(?string $html): string
    {
        if (! $html) {
            return '';
        }

        // Turn line breaks / block boundaries into newlines before stripping so
        // paragraphs don't run together.
        $text = preg_replace('#<br\s*/?>#i', "\n", $html);
        $text = preg_replace('#</(?:p|div|li|h[1-6])>#i', "\n", $text);
        $text = strip_tags($text);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = str_replace("\u{00A0}", ' ', $text); // nbsp (incl. blank-line spacers) -> space
        $text = preg_replace("/[ \t]+\n/", "\n", $text);
        $text = preg_replace("/\n{3,}/", "\n\n", $text);

        return trim($text);
    }
}
