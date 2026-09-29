<?php

namespace App\Utils;

class CustomFieldUtils
{
    /**
     * Compile a schedule-authored validation pattern into a PCRE pattern usable with Laravel's
     * `regex:` rule, or null when it is empty or does not compile.
     *
     * The owner writes the pattern body only (no delimiters, no modifiers), the same way the HTML
     * `pattern` attribute is written, so the two stay interchangeable. Wrapping the body in
     * `^(?:...)$` anchors it like the browser does and stops an owner from closing the delimiter
     * early to append their own modifiers.
     *
     * Two things worth knowing about the call sites:
     * - Laravel's ValidationRuleParser special-cases `regex` and does NOT split its parameter on
     *   commas, so `'regex:'.compilePattern($p)` is safe for patterns containing `,` (e.g. `\d{2,4}`).
     * - A catastrophic pattern cannot hang a request: PCRE gives up at `pcre.backtrack_limit` and
     *   preg_match() returns false, which Laravel treats as a failed field.
     */
    public static function compilePattern(?string $pattern): ?string
    {
        $pattern = trim((string) $pattern);

        if ($pattern === '') {
            return null;
        }

        $compiled = '/^(?:'.self::escapeDelimiter($pattern).')$/u';

        // An owner can type anything here, so confirm PCRE accepts it before it reaches a rule.
        return @preg_match($compiled, '') === false ? null : $compiled;
    }

    /**
     * Longest custom field filter value read from a URL. The text field's own validation max
     * (Role::getEventCustomFieldValidationRules()), so any value an event can hold still matches
     * after a reload - a shorter cap would truncate it into a value no event has.
     */
    public const FILTER_PARAM_MAX_LENGTH = 5000;

    /**
     * The `?custom_N=value` filter params in a query string (N is a field's stable index, 1-10,
     * the same number as its {custom_N} variable), as a param => trimmed value map. Anything that
     * is not a non-empty string is dropped, so `?custom_1[]=x` cannot reach a view as an array.
     *
     * Only the SHAPE is checked: whether custom_1 is a field that filters, on a Pro schedule, is
     * up to the calendar, which drops the rest. That makes this the right test for "a custom field
     * filter may be active", which is all the callers ask.
     *
     * @return array<string,string>
     */
    public static function filterParams(array $query): array
    {
        $params = [];

        foreach ($query as $key => $value) {
            if (! is_string($value) || ! preg_match('/^custom_(10|[1-9])$/', (string) $key)) {
                continue;
            }

            $value = trim(mb_scrub(mb_substr($value, 0, self::FILTER_PARAM_MAX_LENGTH)));
            if ($value !== '') {
                $params[$key] = $value;
            }
        }

        return $params;
    }

    /**
     * Ready-made patterns offered in the schedule editor, so an owner never has to write a regex by
     * hand. Bodies only (no delimiters) - the same form the HTML `pattern` attribute takes.
     *
     * @return array<string,string> translated label => pattern
     */
    public static function regexPresets(): array
    {
        return [
            __('messages.field_regex_preset_email') => '[^@\s]+@[^@\s]+\.[A-Za-z]{2,}',
            __('messages.field_regex_preset_phone') => '\+?[0-9 ()\-]{6,20}',
            __('messages.field_regex_preset_url') => 'https?://\S+',
            __('messages.field_regex_preset_digits') => '[0-9]+',
            __('messages.field_regex_preset_alphanumeric') => '[A-Za-z0-9 ]+',
        ];
    }

    /**
     * Whether a pattern is empty (nothing to validate) or compiles.
     */
    public static function isValidPattern(?string $pattern): bool
    {
        return trim((string) $pattern) === '' || self::compilePattern($pattern) !== null;
    }

    /**
     * Escape unescaped `/` so it cannot terminate the delimiter, leaving existing backslash escapes
     * intact. A str_replace() would corrupt `\/` into `\\/` (an escaped backslash followed by the
     * delimiter), so walk the string instead.
     */
    private static function escapeDelimiter(string $pattern): string
    {
        $escaped = '';
        $length = strlen($pattern);

        for ($i = 0; $i < $length; $i++) {
            $char = $pattern[$i];

            if ($char === '\\' && $i + 1 < $length) {
                $escaped .= $char.$pattern[++$i];

                continue;
            }

            $escaped .= $char === '/' ? '\\/' : $char;
        }

        return $escaped;
    }
}
