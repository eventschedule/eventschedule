<?php

namespace App\Utils;

use App\Models\Event;
use App\Models\Role;
use Illuminate\Support\Str;

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
     * The submitted field list with every NEW field under a key no event still answers.
     *
     * The editor numbers a new field from the highest key on the page (new_0, new_1, ...), so
     * removing the last field and adding another hands the new one the old one's key, and with it
     * every answer an event still holds under that key: nothing prunes events.custom_field_values
     * when a field goes. A removed private "Contact phone" then filters, and since 2026-10 prints
     * on the event page, as the new "Room". A field already stored keeps its key; a new one keeps
     * the key it was posted under unless answers were left behind under it.
     *
     * A key is also replaced when it is not plain letters, digits and underscores: it is typed by
     * nobody, and it is about to be searched for.
     *
     * @param  array<string, mixed>  $submitted  key => posted field, in the owner's order
     * @param  array<string, mixed>  $stored  the schedule's fields as they are saved now
     * @return array<string, mixed>
     */
    public static function withUnusedKeys(Role $role, array $submitted, array $stored): array
    {
        $taken = array_map('strval', array_merge(array_keys($stored), array_keys($submitted)));
        $fields = [];

        foreach ($submitted as $key => $field) {
            $key = (string) $key;

            if (! array_key_exists($key, $stored)
                && (! preg_match('/^[A-Za-z0-9_]{1,64}$/', $key) || self::answersLeftUnder($role, $key))) {
                $key = self::unusedKey($role, $taken);
                $taken[] = $key;
            }

            $fields[$key] = $field;
        }

        return $fields;
    }

    /** Whether any event whose answers are keyed by $role's fields still holds one under $key. */
    private static function answersLeftUnder(Role $role, string $key): bool
    {
        // Two reads, not one OR: each has an index of its own (the answers' schedule; for rows
        // from before that column, the schedule that made the event).
        $scopes = [
            fn ($query) => $query->where('custom_field_values_role_id', $role->id),
            fn ($query) => $query->whereNull('custom_field_values_role_id')->where('creator_role_id', $role->id),
        ];

        foreach ($scopes as $scope) {
            // The column is TEXT, not JSON, so this reads rather than asks MySQL to parse: one
            // row of text that is not JSON would fail a json_extract() for the whole save. LIKE
            // narrows the read to rows that mention the key at all (its "_" matches any
            // character there, which only lets a few more rows through to be looked at
            // properly), and the read stops at the first row that really holds it: a schedule
            // can have tens of thousands of answered events, and one is enough.
            $held = Event::query()
                ->where($scope)
                ->where('custom_field_values', 'like', '%"'.$key.'"%')
                ->select(['id', 'custom_field_values'])
                ->lazyById(200)
                ->contains(fn (Event $event) => array_key_exists($key, (array) $event->custom_field_values));

            if ($held) {
                return true;
            }
        }

        return false;
    }

    /** @param  list<string>  $taken */
    private static function unusedKey(Role $role, array $taken): string
    {
        $next = 0;
        foreach ($taken as $key) {
            if (preg_match('/^new_(\d{1,6})$/', $key, $match)) {
                $next = max($next, (int) $match[1] + 1);
            }
        }

        for ($tries = 0; $tries < 100; $tries++, $next++) {
            if (! in_array('new_'.$next, $taken, true) && ! self::answersLeftUnder($role, 'new_'.$next)) {
                return 'new_'.$next;
            }
        }

        return 'new_'.strtolower(Str::random(10));
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
