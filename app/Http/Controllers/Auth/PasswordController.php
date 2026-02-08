<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    /**
     * Update the user's password.
     */
    public function update(Request $request): RedirectResponse
    {
        // Demo mode: prevent password changes
        if (is_demo_mode()) {
            return redirect()->to(route('profile.edit').'#section-password')
                ->with('error', __('messages.demo_mode_restriction'));
        }

        $user = $request->user();

        // Build validation rules based on whether user has a password
        $rules = [
            'password' => ['required', 'string', 'min:8'],
        ];

        if ($user->hasPassword()) {
            // User has existing password - require current password
            $rules['current_password'] = ['required', 'current_password'];
        } else {
            // User doesn't have a password (Google-only) - require re-authentication
            $canSetPasswordTimestamp = session('can_set_password');
            $fiveMinutesAgo = now()->subMinutes(5)->timestamp;

            if (! $canSetPasswordTimestamp || $canSetPasswordTimestamp < $fiveMinutesAgo) {
                // Clear expired session flag
                session()->forget('can_set_password');

                return redirect()->to(route('profile.edit').'#section-password')
                    ->withErrors(['password' => __('messages.google_reauth_required')], 'updatePassword');
            }
            // Clear the session flag after use
            session()->forget('can_set_password');
        }

        $validated = $request->validateWithBag('updatePassword', $rules);

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        AuditService::log(AuditService::AUTH_PASSWORD_CHANGE, $user->id);

        return redirect()->to(route('profile.edit').'#section-password')->with('status', 'password-updated');
    }
}
