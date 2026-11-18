<?php

namespace App\Http\Middleware;

use App\Support\HomeSchedule;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Serve a selfhosted install's home schedule at the site's own address (see HomeSchedule).
 *
 * A rewrite, before routing, of "/" to "/{subdomain}" with the query string kept, rather than a
 * route of its own: the schedule page is then answered by the same route (role.view_guest) and
 * passes the same middleware as at its own path, which read the route's name and its subdomain
 * parameter (the guest page's security headers, the promoter links, the demo sign-in). The
 * browser's address stays the root, which is the point.
 *
 * GET and HEAD only, and the root only: nothing else on the site moves.
 */
class ServeHomeSchedule
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! HomeSchedule::available()
            || ! in_array($request->getMethod(), ['GET', 'HEAD'], true)
            || $request->getPathInfo() !== '/') {
            return $next($request);
        }

        try {
            $role = HomeSchedule::role();
        } catch (\Throwable $e) {
            // A fresh install whose database is not set up yet: the root does what it always did.
            return $next($request);
        }

        if (! $role) {
            return $next($request);
        }

        $query = $request->getQueryString();
        $server = $request->server->all();
        // getBaseUrl() keeps an install that lives in a sub-folder in it.
        $server['REQUEST_URI'] = $request->getBaseUrl().'/'.rawurlencode($role->subdomain).($query !== null ? '?'.$query : '');

        // duplicate() rather than editing the server bag: the request has already worked out its
        // path, and only a fresh one works it out again from REQUEST_URI.
        $rewritten = $request->duplicate(null, null, null, null, null, $server);
        $rewritten->attributes->set('home_schedule', true);

        $response = $next($rewritten);

        // The page redirects to itself in places (a ?lang= that is the schedule's own language goes
        // to the clean address), and it builds that address from the request it was given, which
        // is the schedule's path. Point those back at the root, query and all.
        if ($response->isRedirect()) {
            $location = (string) $response->headers->get('Location');
            $root = $this->rootFor($location, $request, $role->subdomain);

            if ($root !== null) {
                $response->headers->set('Location', $root);
            }
        }

        return $response;
    }

    /**
     * $location rewritten to the site's root when it is this site's address of the home schedule's
     * own page (the schedule's path and nothing under it), or null to leave it alone.
     */
    private function rootFor(string $location, Request $request, string $subdomain): ?string
    {
        $parts = parse_url($location);

        if ($parts === false) {
            return null;
        }

        if (isset($parts['host']) && strtolower($parts['host']) !== strtolower($request->getHost())) {
            return null;
        }

        $schedulePath = $request->getBaseUrl().'/'.rawurlencode($subdomain);

        if (rtrim($parts['path'] ?? '', '/') !== $schedulePath) {
            return null;
        }

        return $request->getSchemeAndHttpHost().$request->getBaseUrl().'/'
            .(isset($parts['query']) && $parts['query'] !== '' ? '?'.$parts['query'] : '')
            .(isset($parts['fragment']) ? '#'.$parts['fragment'] : '');
    }
}
