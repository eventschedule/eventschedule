<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ApplyBrowserTestingOverrides
{
    /**
     * Ensure browser-testing configuration overrides are applied for each HTTP request.
     */
    public function handle(Request $request, Closure $next)
    {
        $isBrowserTesting = function_exists('is_browser_testing') && is_browser_testing();

        if (! $isBrowserTesting) {
            $cookie = $request->cookies->get('browser_testing');

            $isBrowserTesting = filter_var($cookie, FILTER_VALIDATE_BOOLEAN) || $cookie === '1';
        }

        if ($isBrowserTesting) {
            config([
                'app.is_testing' => true,
                'app.browser_testing' => true,
                'app.debug' => true,
                'app.load_vite_assets' => false,
            ]);
        }

        return $next($request);
    }
}
