<?php

namespace App\Http\Middleware;

use App\Services\AuditService;
use App\Utils\AdminReauthUtils;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Handle an incoming request.
     *
     * Ensures the authenticated user has admin privileges,
     * binds admin sessions to User Agent, and requires
     * password confirmation before accessing admin pages.
     *
     * The confirmation is good for auth.admin_reauth_timeout of idle (sliding - any admin request
     * restarts it), and for auth.admin_reauth_max_lifetime in total. Both are bounded above by
     * session.lifetime, because the keys live in the session: a window longer than the session it
     * rides in is just the session's lifetime wearing a different number.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check 1: Must be authenticated admin (existing logic)
        if (! $request->user() || ! $request->user()->isAdmin()) {
            if ($request->expectsJson()) {
                return response()->json(['error' => __('messages.not_authorized')], 403);
            }

            return redirect()->route('home')->with('error', __('messages.not_authorized'));
        }

        $session = $request->session();
        $currentUserAgent = (string) $request->userAgent();

        // Check 2: User Agent binding - if it doesn't match, force re-auth
        $storedUserAgent = $session->get(AdminReauthUtils::USER_AGENT_KEY);
        if ($storedUserAgent !== null && $storedUserAgent !== $currentUserAgent) {
            AuditService::log(
                AuditService::ADMIN_SESSION_CHANGED,
                $request->user()->id,
                null, null, null, null,
                json_encode([
                    'old_ua' => $storedUserAgent,
                    'new_ua' => $currentUserAgent,
                ])
            );
            AdminReauthUtils::clear($session);

            if ($request->expectsJson()) {
                return response()->json(['error' => __('messages.admin_session_changed')], 403);
            }

            return redirect()->guest(route('admin.password.confirm.show'))
                ->with('warning', __('messages.admin_session_changed'));
        }

        // Check 3: Re-auth gate - must have confirmed the password within the window.
        //
        // An expired window falls into the same branch as "never confirmed", which is deliberate:
        // that branch already carries the hasPassword() handling, the 423 for JSON, and
        // confirm-password's own copy, so no new strings are needed for a timeout.
        if (! AdminReauthUtils::isCurrent($session)) {
            // Drop the UA binding along with the timestamps, so re-confirming rebinds - the same
            // shape as the UA-mismatch branch above.
            AdminReauthUtils::clear($session);

            if (! $request->user()->hasPassword()) {
                if ($request->expectsJson()) {
                    return response()->json(['error' => __('messages.admin_password_required')], 403);
                }

                return redirect()->to(route('profile.edit').'#section-password')
                    ->with('error', __('messages.admin_password_required'));
            }

            if ($request->expectsJson()) {
                return response()->json(['message' => __('messages.admin_confirm_password')], 423);
            }

            return redirect()->guest(route('admin.password.confirm.show'));
        }

        // Store UA on first confirmed admin access
        if (! $session->has(AdminReauthUtils::USER_AGENT_KEY)) {
            $session->put(AdminReauthUtils::USER_AGENT_KEY, $currentUserAgent);
        }

        // Restart the idle window. This is what makes refreshing the page reset the timer, and it
        // deliberately leaves the ceiling alone.
        AdminReauthUtils::slide($session);

        return $next($request);
    }
}
