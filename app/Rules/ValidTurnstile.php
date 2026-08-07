<?php

namespace App\Rules;

use App\Utils\TurnstileUtils;
use Illuminate\Contracts\Validation\ImplicitRule;

/**
 * ImplicitRule, not Rule: a plain Rule is SKIPPED when the attribute is absent or an empty string
 * (Validator::presentOrRuleIsImplicit), so simply omitting cf-turnstile-response bypassed
 * verification entirely on every form using this. The check has to run precisely when the token is
 * missing, which is what implicit means.
 *
 * Safe for all six call sites: each has a form that renders the widget, and passes() still returns
 * true up front whenever Turnstile is not in play (no keys, custom domain, testing), so a null
 * token only fails where a real token was genuinely expected. TurnstileUtils::verify() is
 * null-safe and treats an empty token as invalid.
 */
class ValidTurnstile implements ImplicitRule
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

        // Skip validation on custom domains (site key only works on eventschedule.com)
        if (request()->attributes->get('custom_domain_host')) {
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
