<?php

namespace App\Rules;

use App\Utils\TimezoneUtils;
use Illuminate\Contracts\Validation\Rule;

/**
 * Accepts exactly what TimezoneUtils::canonicalize() accepts: a listed zone, a known alias, or any
 * other value DateTimeZone can construct (+05:30, Etc/GMT-3).
 *
 * Laravel's `timezone:all_with_bc` is narrower than that. canonicalize() deliberately keeps a usable
 * but unlisted value rather than nulling it (every caller falls back to America/New_York on null),
 * and x-timezone-options renders such a stored value as its own selected option - so with the
 * framework rule the form posted it straight back and failed, and API clients that had been
 * sending +05:30 under `nullable|string` started getting a 422.
 */
class UsableTimezone implements Rule
{
    /**
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return TimezoneUtils::canonicalize($value) !== null;
    }

    /**
     * @return string
     */
    public function message()
    {
        return __('validation.timezone');
    }
}
