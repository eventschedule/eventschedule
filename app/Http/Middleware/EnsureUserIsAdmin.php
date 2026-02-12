<?php

namespace App\Http\Middleware;

use App\Services\AuditService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Handle an incoming request.
     *
     * Ensures the authenticated user has admin privileges,
     * binds admin sessions to IP + User Agent, and requires
     * password confirmation before accessing admin pages.
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
        $currentIp = $this->getClientIp($request);
        $currentUserAgent = (string) $request->userAgent();

        // Check 2: IP + User Agent binding - if either doesn't match, force re-auth
        $storedIp = $session->get('admin_ip');
        $storedUserAgent = $session->get('admin_user_agent');
        if ($storedIp !== null && ($storedIp !== $currentIp || $storedUserAgent !== $currentUserAgent)) {
            AuditService::log(
                AuditService::ADMIN_SESSION_CHANGED,
                $request->user()->id,
                null, null, null, null,
                json_encode([
                    'old_ip' => $storedIp,
                    'new_ip' => $currentIp,
                    'ip_changed' => $storedIp !== $currentIp,
                    'ua_changed' => $storedUserAgent !== $currentUserAgent,
                ])
            );
            $session->forget(['admin_ip', 'admin_user_agent', 'admin_password_confirmed_at']);

            if ($request->expectsJson()) {
                return response()->json(['error' => __('messages.admin_session_changed')], 403);
            }

            return redirect()->guest(route('admin.password.confirm.show'))
                ->with('warning', __('messages.admin_session_changed'));
        }

        // Check 3: Re-auth gate - must have confirmed password this session
        if (! $session->has('admin_password_confirmed_at')) {
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

        // Store IP + UA on first confirmed admin access
        if (! $session->has('admin_ip')) {
            $session->put('admin_ip', $currentIp);
            $session->put('admin_user_agent', $currentUserAgent);
        }

        return $next($request);
    }

    private function getClientIp(Request $request): string
    {
        return $request->header('CF-Connecting-IP') ?? $request->ip();
    }
}
