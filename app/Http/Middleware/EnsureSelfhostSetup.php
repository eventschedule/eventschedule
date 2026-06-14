<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * On a selfhosted install that has not finished first-run setup (no APP_URL, or the
 * database has no `users` table), redirect every web request to the setup wizard
 * (the sign-up page) so the user can never get stranded on a 500 with no way back.
 *
 * No-op in hosted/testing mode and once the install is configured (selfhost_needs_setup()
 * short-circuits there). The wizard's own endpoints are excluded so we never loop.
 */
class EnsureSelfhostSetup
{
    public function handle(Request $request, Closure $next): Response
    {
        if (selfhost_needs_setup() && ! $request->is('sign_up', 'sign_up/*', 'test_database')) {
            return redirect()->route('sign_up');
        }

        return $next($request);
    }
}
