<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuditService;
use App\Utils\HoneypotUtils;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    /**
     * Display the password reset view.
     */
    public function create(Request $request): View
    {
        return view('auth.reset-password', ['request' => $request]);
    }

    /**
     * Handle an incoming new password request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        // Honeypot. A ValidationException rather than a flash error: x-auth-layout renders
        // only per-field errors, so with('error') would be swallowed by the layout.
        if (HoneypotUtils::isTripped($request)) {
            throw ValidationException::withMessages([
                'email' => __('messages.invalid_request'),
            ]);
        }

        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        // Carried out of the closure: Password::reset() hands back a status string and nothing
        // else, and the sign-in below needs the model. Captured by reference rather than re-queried
        // so it is the same instance the closure just wrote.
        $claimed = null;
        $resetUser = null;

        // Here we will attempt to reset the user's password. If it is successful we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise we will parse the error and return the response.
        $status = Password::reset(
            $request->only('email', 'password', 'token'),
            function ($user) use ($request, &$claimed, &$resetUser) {
                // Decided BEFORE the write below: afterwards the account has a password and is no
                // longer a stub, so the question cannot be asked any more.
                $claimed = $user->mayClaimByEmailLink();
                $resetUser = $user;

                $updateData = [
                    'password' => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ];

                // Only re-verify email if it was previously verified
                // This prevents users from bypassing email verification via password reset
                if ($user->email_verified_at !== null) {
                    $updateData['email_verified_at'] = now();
                } elseif ($claimed) {
                    // An account that has NEVER had a password, and holds nothing above a follower
                    // pivot. The link this request redeemed was mailed to this address, which is
                    // the same proof of mailbox possession RoleSubscriberController::claimAccount()
                    // accepts on /sub/done - so there is nothing left to verify and no bypass to
                    // guard against. Load-bearing, not cosmetic: EnsureEmailIsVerified is appended
                    // to the whole web middleware group, so without this they set a password and
                    // then bounce to verification.notice on every page. See
                    // User::mayClaimByEmailLink() for why a team invite at admin level does not
                    // qualify.
                    $updateData['email_verified_at'] = now();
                }

                $user->forceFill($updateData)->save();

                // Invalidate all existing sessions so a stolen cookie does not
                // survive a password reset. Only effective with the database
                // session driver (which we ship by default).
                if (config('session.driver') === 'database') {
                    DB::table(config('session.table', 'sessions'))
                        ->where('user_id', $user->id)
                        ->delete();
                }

                // AUTH_REGISTER for a first password, because that is what it is - the same event
                // claimAccount() logs for the same state transition through the other door. Logging
                // it as a reset would make the two doors disagree in the audit trail.
                AuditService::log(
                    $claimed ? AuditService::AUTH_REGISTER : AuditService::AUTH_PASSWORD_RESET,
                    $user->id
                );

                event(new PasswordReset($user));
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => __($status)]);
        }

        // An account that had no password has just got its first one, from a link mailed to its own
        // address. Sending it to /login to type the password it set ten seconds ago is a step with
        // nothing in it.
        //
        // Deliberately fires neither Registered nor Verified. claimAccount() fires neither either,
        // there is no EventServiceProvider and no listener for Registered anywhere in the repo, so
        // today it would be inert - and a landmine the day somebody registers
        // SendEmailVerificationNotification, which would mail a verification link for an account
        // this request has just verified.
        if ($claimed && $resetUser) {
            // fresh(): the closure wrote a new remember_token, and a stale instance would have
            // Auth::login() mint a recaller cookie that AuthenticateSession later rejects. The
            // session wipe above does not catch this request - it arrived as a guest, so its row
            // carries user_id null until DatabaseSessionHandler writes it at end of request.
            Auth::login($resetUser->fresh(), true);

            // Auth::login() already migrates the session id (SessionGuard::updateSession); this is
            // for the CSRF token, which migrate() does not rotate. Matches
            // AuthenticatedSessionController::store().
            $request->session()->regenerate();

            return redirect(app_url(route('following', [], false)));
        }

        return redirect()->route('login')->with('status', __($status));
    }
}
