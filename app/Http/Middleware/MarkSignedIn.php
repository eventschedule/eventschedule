<?php

namespace App\Http\Middleware;

use App\Services\DemoService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Response;

/**
 * Tells the marketing pages, which the edge serves to everyone from one stored copy, that this
 * browser is signed in, so their header can offer Dashboard instead of Sign In and Get Started.
 *
 * The header's own @auth branch only runs when the page is rendered for this request, and on
 * the nexus a signed-in visitor is handed the anonymous copy anyway: the Cloudflare rule cannot
 * bypass on laravel_session on our plan (docs/CACHING.md). So the browser is told instead, by a
 * cookie script can read (`es_signed_in=1`, unencrypted, not HttpOnly) on the session's domain,
 * and the page reads it before it paints (layouts/marketing.blade.php). It says nothing the
 * session cookie beside it does not: it carries no id, and a stale one costs a click to the
 * sign-in page.
 *
 * Asked after the request, outside every middleware that signs a person in or out (the blocked
 * account check, the demo login), so it reflects how the request left. The demo account is not
 * a sign-in to offer a dashboard for: its header keeps Sign In, as the server-side branch does.
 */
class MarkSignedIn
{
    public const COOKIE = 'es_signed_in';

    /** Laravel's remember-me cookie lasts 400 days (SessionGuard::$rememberDuration). */
    private const REMEMBER_MINUTES = 576000;

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $user = Auth::guard('web')->user();

        if ($user !== null && ! DemoService::isDemoUser($user)) {
            $response->headers->setCookie($this->cookie('1', $this->minutes($request)));
        } elseif ($request->cookies->has(self::COOKIE)) {
            $response->headers->setCookie($this->cookie('', -2628000));
        }

        return $response;
    }

    /**
     * As long as the session, or as long as the remember-me cookie when there is one, so a
     * remembered visitor whose session lapsed is still offered the dashboard it will reopen.
     */
    private function minutes(Request $request): int
    {
        $recaller = Auth::guard('web')->getRecallerName();

        if ($request->cookies->has($recaller) || Cookie::hasQueued($recaller)) {
            return self::REMEMBER_MINUTES;
        }

        return config('session.expire_on_close') ? 0 : (int) config('session.lifetime');
    }

    private function cookie(string $value, int $minutes): \Symfony\Component\HttpFoundation\Cookie
    {
        return Cookie::make(
            self::COOKIE,
            $value,
            $minutes,
            '/',
            config('session.domain'),
            config('session.secure'),
            false,
            false,
            'lax',
        );
    }
}
