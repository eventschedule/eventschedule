<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Spells a hand-typed ?lang= the way the app does before anything reads it: ?lang=pt-pt or
 * ?lang=pt_PT becomes ?lang=pt-PT. Some sixty places read ?lang= and pass what is_valid_language_code()
 * accepts straight to setLocale(), a URL or a column, so the one fix is to correct the request
 * itself, here, rather than loosen that check (see normalize_language_code()).
 */
class NormalizeLanguageQuery
{
    public function handle(Request $request, Closure $next): Response
    {
        $lang = $request->query->get('lang');

        if (is_string($lang) && $lang !== '' && ! is_valid_language_code($lang) && ($canonical = normalize_language_code($lang)) !== null) {
            $request->query->set('lang', $canonical);
            // fullUrl() and the request's own address read the raw query string, not the bag.
            $request->server->set('QUERY_STRING', http_build_query($request->query->all(), '', '&', PHP_QUERY_RFC3986));
        }

        return $next($request);
    }
}
