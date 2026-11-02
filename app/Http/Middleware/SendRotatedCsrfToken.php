<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Tells a page that is still open which CSRF token its session holds now, when the request it
 * sent changed it.
 *
 * From Laravel 12, signing a person in (SessionGuard::login(), and so Auth::attempt()) gives the
 * session a new CSRF token. A page that signs somebody in without leaving (the guest submit
 * form, the guest import page, the booking request form) went on posting the token it was
 * rendered with, and every request after the first one that signed in answered 419: "Submit
 * another" and the next flyer upload among them. The page sends the token in its meta tag and
 * takes this header's value into it (partials/request-form-kit's post(), event/import).
 *
 * Asked after the request, outside the app's own middleware that signs a person in or out (and
 * inside Laravel's session middleware, which it needs), and only when the token changed, so an
 * ordinary response carries nothing new. Same-origin only: no CORS rule exposes it.
 */
class SendRotatedCsrfToken
{
    public const HEADER = 'X-Rotated-Csrf-Token';

    public function handle(Request $request, Closure $next): Response
    {
        $before = $request->hasSession() ? $request->session()->token() : null;

        $response = $next($request);

        if ($before !== null && $request->hasSession()) {
            $after = $request->session()->token();

            if (is_string($after) && $after !== '' && $after !== $before) {
                $response->headers->set(self::HEADER, $after);
            }
        }

        return $response;
    }
}
