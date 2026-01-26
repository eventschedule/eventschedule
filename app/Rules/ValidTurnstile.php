<?php

namespace App\Rules;

use App\Utils\TurnstileUtils;
use Illuminate\Contracts\Validation\Rule;

class ValidTurnstile implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        // Skip validation if Turnstile is not enabled
        if (! TurnstileUtils::isEnabled()) {
            return true;
        }

        // Skip validation in testing environment
        if (config('app.is_testing')) {
            return true;
        }

        return TurnstileUtils::verify($value, request()->ip());
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('messages.turnstile_verification_failed');
    }
}
