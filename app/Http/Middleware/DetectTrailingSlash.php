<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DetectTrailingSlash
{
    /**
     * Handle an incoming request.
     *
     * Detects if the original request URL had a trailing slash and sets a flag on the request.
     * This is needed because Laravel normalizes URLs and strips trailing slashes.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if the original request URI ends with a trailing slash
        $uri = $request->getRequestUri();
        $path = parse_url($uri, PHP_URL_PATH);

        // Set a flag if the path ends with a trailing slash (but is not just "/")
        if ($path && strlen($path) > 1 && str_ends_with($path, '/')) {
            $request->attributes->set('has_trailing_slash', true);
        }

        return $next($request);
    }
}
