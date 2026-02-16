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
            // In hosted mode, redirect trailing slashes on the main/marketing domain
            if (config('app.hosted')) {
                $appHost = parse_url(config('app.url'), PHP_URL_HOST);
                $requestHost = $request->getHost();

                if ($requestHost === $appHost || $requestHost === 'www.'.$appHost || str_starts_with($requestHost, 'blog.')) {
                    $cleanPath = rtrim($path, '/');
                    $query = parse_url($uri, PHP_URL_QUERY);

                    return redirect($cleanPath.($query ? '?'.$query : ''), 301);
                }
            }

            // Guest portal subdomain (or selfhosted): set flag for registration redirect feature
            $request->attributes->set('has_trailing_slash', true);
        }

        return $next($request);
    }
}
