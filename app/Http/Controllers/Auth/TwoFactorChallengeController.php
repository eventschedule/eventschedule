<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorChallengeController extends Controller
{
    /**
     * Show the two-factor challenge form.
     */
    public function create(Request $request): View|RedirectResponse
    {
        $loginId = $request->session()->get('login.id');
        $loginExpires = $request->session()->get('login.expires');

        // Redirect to login if no pending 2FA challenge or if expired
        if (! $loginId || ($loginExpires && now()->timestamp > $loginExpires)) {
            $request->session()->forget(['login.id', 'login.remember', 'login.expires']);

            return redirect()->route('login');
        }

        return view('auth.two-factor-challenge');
    }

    /**
     * Verify the two-factor authentication code.
     */
    public function store(Request $request): RedirectResponse
    {
        $loginId = $request->session()->get('login.id');
        $loginExpires = $request->session()->get('login.expires');

        if (! $loginId || ($loginExpires && now()->timestamp > $loginExpires)) {
            $request->session()->forget(['login.id', 'login.remember', 'login.expires']);

            return redirect()->route('login');
        }

        // Validate that user still exists
        $user = User::find($loginId);
        if (! $user) {
            $request->session()->forget(['login.id', 'login.remember', 'login.expires']);

            return redirect()->route('login');
        }

        $request->validate([
            'code' => ['nullable', 'string', 'digits:6'],
            'recovery_code' => ['nullable', 'string', 'max:25'],
        ]);

        // Try TOTP code first
        if ($request->filled('code')) {
            $google2fa = new Google2FA;
            $google2fa->setWindow(1);

            if (! $google2fa->verifyKey($user->two_factor_secret, $request->code)) {
                return back()->withErrors(['code' => __('messages.two_factor_invalid_code')]);
            }
        }
        // Try recovery code
        elseif ($request->filled('recovery_code')) {
            $codes = $user->two_factor_recovery_codes;
            if (! $codes) {
                return back()->withErrors(['recovery_code' => __('messages.two_factor_invalid_recovery_code')]);
            }

            $recoveryCodes = json_decode($codes, true);

            if (! is_array($recoveryCodes)) {
                return back()->withErrors(['recovery_code' => __('messages.two_factor_invalid_recovery_code')]);
            }

            $hashedInput = hash('sha256', $request->recovery_code);
            $index = array_search($hashedInput, $recoveryCodes);

            if ($index === false) {
                return back()->withErrors(['recovery_code' => __('messages.two_factor_invalid_recovery_code')]);
            }

            // Remove used recovery code (one-time use)
            unset($recoveryCodes[$index]);
            $user->two_factor_recovery_codes = json_encode(array_values($recoveryCodes));
            $user->save();
        } else {
            return back()->withErrors(['code' => __('messages.two_factor_code_required')]);
        }

        // Clear 2FA session data
        $remember = $request->session()->get('login.remember', false);
        $request->session()->forget(['login.id', 'login.remember', 'login.expires']);

        // Complete authentication
        Auth::login($user, $remember);
        $request->session()->regenerate();

        AuditService::log(AuditService::AUTH_LOGIN, $user->id);

        return redirect()->intended(route('home', absolute: false));
    }
}
