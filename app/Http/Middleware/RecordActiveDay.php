<?php

namespace App\Http\Middleware;

use App\Services\ActiveDays;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Notes that a signed-in person used the app today (App\Services\ActiveDays).
 *
 * Only a page they opened counts. The app polls in the background while a tab is open - the
 * support chat every 30 seconds, /admin/realtime/data every ten - and a tab left open overnight
 * must not mark its owner active every day. So: a GET, answered 200, with an HTML body, that the
 * browser asked for as a document. Sec-Fetch-Dest is "document" for a navigation and "empty" for
 * fetch(); a client that sends no such header is given the benefit of the doubt.
 *
 * And a page the browser fetched on a guess is not one the person opened: Chrome prerenders what
 * the address bar is about to complete, signed in and marked as a document like any other. Every
 * browser that loads ahead says so in a header of its own.
 */
class RecordActiveDay
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        try {
            if ($this->isPageView($request, $response) && ($user = $request->user())) {
                ActiveDays::record($user, $request);
            }
        } catch (Throwable) {
            // Never the reason a page fails.
        }

        return $response;
    }

    private function isPageView(Request $request, Response $response): bool
    {
        if (! $request->isMethod('GET') || $response->getStatusCode() !== 200) {
            return false;
        }

        if (! str_contains(strtolower((string) $response->headers->get('Content-Type')), 'text/html')) {
            return false;
        }

        foreach (['Sec-Purpose', 'Purpose', 'X-Purpose', 'X-Moz'] as $header) {
            $purpose = strtolower((string) $request->headers->get($header));

            if (str_contains($purpose, 'prefetch') || str_contains($purpose, 'prerender') || str_contains($purpose, 'preview')) {
                return false;
            }
        }

        $destination = strtolower((string) $request->headers->get('Sec-Fetch-Dest'));

        return $destination === '' || $destination === 'document';
    }
}
