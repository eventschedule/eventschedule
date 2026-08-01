<?php

namespace App\Utils;

use Illuminate\Http\Request;

class HoneypotUtils
{
    /**
     * Name of the hidden decoy field rendered by the <x-honeypot /> component.
     *
     * It deliberately matches the real roles.website column: a plausible name is what
     * makes a bot fill it. Never add the honeypot to a form that posts a genuine
     * website value (the schedule edit form, the schedules API).
     */
    public const FIELD = 'website';

    /**
     * Whether the honeypot was filled in, which means the submitter is a bot.
     *
     * filled(), never has(): the field is absent from every existing caller and from
     * third party API clients, so absence must pass. Laravel's blank() trims strings,
     * so a whitespace-only autofill does not trip it either.
     *
     * There is no config('app.is_testing') bypass on purpose. ThrottleRequests and
     * ValidTurnstile both short-circuit under it, and phpunit.xml sets APP_TESTING=true,
     * so copying that pattern would leave this untested and dead in CI.
     */
    public static function isTripped(Request $request): bool
    {
        return $request->filled(self::FIELD);
    }
}
