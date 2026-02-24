<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureEmailIsVerified
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->user() && ! $request->user()->hasVerifiedEmail() && ! $this->isVerificationRoute($request)) {
            return redirect(app_url(route('verification.notice', [], false)));
        }

        return $next($request);
    }

    protected function isVerificationRoute($request)
    {
        return $request->routeIs('verification.notice')
            || $request->routeIs('verification.verify')
            || $request->routeIs('verification.send')
            || $request->routeIs('logout');
    }
}
