<?php

namespace App\Http\Middleware;

use App\Services\DemoService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                // Log out demo users so they can access login/signup
                if (DemoService::isDemoUser(Auth::user())) {
                    Auth::guard($guard)->logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();

                    return $next($request);
                }

                // Process SMS claim for logged-in users clicking invite links
                $smsToken = $request->query('sms_token');
                if (is_string($smsToken) && strlen($smsToken) > 0 && strlen($smsToken) <= 60) {
                    $smsPhone = Cache::get('sms_signup_'.$smsToken);
                    if ($smsPhone) {
                        $user = Auth::user();
                        $normalizedPhone = \App\Utils\PhoneUtils::normalize($smsPhone);

                        // Only allow claim if user has no phone or their phone matches the invite
                        if ($user->phone && $user->phone !== $normalizedPhone) {
                            return redirect(route('home'))->with('warning', __('messages.invite_phone_mismatch'));
                        }

                        $user->claimRolesByPhone($smsPhone);
                        Cache::forget('sms_signup_'.$smsToken);

                        return redirect(route('home'))->with('message', __('messages.phone_verified'));
                    }

                    return redirect(route('home'))->with('warning', __('messages.invite_link_expired'));
                }

                // Custom-domain bounce: a guest hit /request on a custom domain (where
                // the .eventschedule.com session cookie did not apply), got redirected
                // here via redirect_with_pending_action(). Now that the cookie applies
                // and we can see the user is already authenticated, restore the cached
                // pending action and bounce to the subdomain /request URL so
                // RoleController::request can route them correctly.
                if ($request->query('pa')) {
                    restore_pending_action();
                    $pendingRequest = session('pending_request');
                    if ($pendingRequest) {
                        return redirect(route('role.request', ['subdomain' => $pendingRequest]));
                    }
                }

                return redirect(route('home'));
            }
        }

        return $next($request);
    }
}
