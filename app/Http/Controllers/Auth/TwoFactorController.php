<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorController extends Controller
{
    /**
     * Enable two-factor authentication (generates secret + recovery codes).
     */
    public function enable(Request $request): RedirectResponse
    {
        if (is_demo_mode()) {
            return redirect()->to(route('profile.edit').'#section-two-factor')
                ->with('error', __('messages.demo_mode_restriction'));
        }

        $user = $request->user();

        // Require current password if user has one
        if ($user->hasPassword()) {
            $request->validate([
                'current_password' => ['required', 'current_password'],
            ]);
        }

        $google2fa = new Google2FA;
        $secret = $google2fa->generateSecretKey();

        // Generate 8 recovery codes
        $recoveryCodes = Collection::times(8, fn () => Str::random(10).'-'.Str::random(10))->all();

        $user->two_factor_secret = $secret;
        $user->two_factor_recovery_codes = json_encode(
            array_map(fn ($code) => hash('sha256', $code), $recoveryCodes)
        );
        $user->two_factor_confirmed_at = null;
        $user->save();

        return redirect()->to(route('profile.edit').'#section-two-factor')
            ->with('two_factor_recovery_codes', $recoveryCodes)
            ->with('status', 'two-factor-enabled');
    }

    /**
     * Confirm two-factor authentication by verifying a TOTP code.
     */
    public function confirm(Request $request): RedirectResponse
    {
        if (is_demo_mode()) {
            return redirect()->to(route('profile.edit').'#section-two-factor')
                ->with('error', __('messages.demo_mode_restriction'));
        }

        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $user = $request->user();

        if (! $user->two_factor_secret) {
            return redirect()->to(route('profile.edit').'#section-two-factor')
                ->with('error', __('messages.two_factor_not_enabled'));
        }

        $google2fa = new Google2FA;
        $google2fa->setWindow(1); // 90-second tolerance

        $valid = $google2fa->verifyKey($user->two_factor_secret, $request->code);

        if (! $valid) {
            return redirect()->to(route('profile.edit').'#section-two-factor')
                ->withErrors(['code' => __('messages.two_factor_invalid_code')]);
        }

        $user->two_factor_confirmed_at = now();
        $user->save();

        AuditService::log(AuditService::AUTH_2FA_ENABLED, $user->id);

        return redirect()->to(route('profile.edit').'#section-two-factor')
            ->with('status', 'two-factor-confirmed');
    }

    /**
     * Disable two-factor authentication.
     */
    public function disable(Request $request): RedirectResponse
    {
        if (is_demo_mode()) {
            return redirect()->to(route('profile.edit').'#section-two-factor')
                ->with('error', __('messages.demo_mode_restriction'));
        }

        $user = $request->user();

        // Require current password if user has one
        if ($user->hasPassword()) {
            $request->validate([
                'current_password' => ['required', 'current_password'],
            ]);
        }

        $user->two_factor_secret = null;
        $user->two_factor_recovery_codes = null;
        $user->two_factor_confirmed_at = null;
        $user->save();

        AuditService::log(AuditService::AUTH_2FA_DISABLED, $user->id);

        return redirect()->to(route('profile.edit').'#section-two-factor')
            ->with('status', 'two-factor-disabled');
    }

    /**
     * Regenerate recovery codes.
     */
    public function regenerateRecoveryCodes(Request $request): RedirectResponse
    {
        if (is_demo_mode()) {
            return redirect()->to(route('profile.edit').'#section-two-factor')
                ->with('error', __('messages.demo_mode_restriction'));
        }

        $user = $request->user();

        if (! $user->hasTwoFactorEnabled()) {
            return redirect()->to(route('profile.edit').'#section-two-factor')
                ->with('error', __('messages.two_factor_not_enabled'));
        }

        $recoveryCodes = Collection::times(8, fn () => Str::random(10).'-'.Str::random(10))->all();

        $user->two_factor_recovery_codes = json_encode(
            array_map(fn ($code) => hash('sha256', $code), $recoveryCodes)
        );
        $user->save();

        return redirect()->to(route('profile.edit').'#section-two-factor')
            ->with('two_factor_recovery_codes', $recoveryCodes)
            ->with('status', 'recovery-codes-regenerated');
    }
}
