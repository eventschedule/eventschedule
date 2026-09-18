<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Routing\Middleware\ThrottleRequests as BaseThrottleRequests;

class ThrottleRequests extends BaseThrottleRequests
{
    public function handle($request, Closure $next, $maxAttempts = 60, $decayMinutes = 1, $prefix = '')
    {
        if (config('app.is_testing')) {
            return $next($request);
        }

        // Forward the arguments we were ACTUALLY given, never the filled-in defaults.
        //
        // The parent chooses between its two modes with `func_num_args() === 3`: exactly three means
        // `throttle:some_name` and it resolves a NAMED limiter; anything else means `throttle:60,1`
        // and the third argument has to be a number. Passing all five positionally made the parent
        // see five every time, so the named branch was unreachable, resolveMaxAttempts() was handed
        // a non-numeric name, and it threw MissingRateLimiterException - an uncaught 500 on a public
        // route.
        //
        // That was live for every `throttle:<name>` route, which at the time meant
        // audience_unsubscribe and newsletter_unsubscribe: the RFC 8058 one-click unsubscribe
        // endpoints, whose route comments exist precisely to keep them from returning an error,
        // because a failed unsubscribe is what earns a spam complaint.
        //
        // No feature test can catch a regression here - the early return above is the flag every
        // test in the suite runs under. tests/Unit/ThrottleRequestsTest.php drives this directly
        // with the flag off instead.
        return parent::handle($request, $next, ...array_slice(func_get_args(), 2));
    }

    protected function resolveRequestSignature($request)
    {
        if ($user = $request->user()) {
            $routeName = $request->route()?->getName() ?? $request->path();

            return sha1($routeName.'|'.$user->getAuthIdentifier());
        }

        return parent::resolveRequestSignature($request);
    }
}
