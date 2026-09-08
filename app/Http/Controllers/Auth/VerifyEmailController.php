<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserEmailVerificationRequest;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(UserEmailVerificationRequest $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return redirect(route('home'))
                ->with('message', __('messages.confirmed_email'));
        }

        if ($user->markEmailAsVerified()) {
            // Moved onto the model so RegisteredUserController can run it too. It never could
            // before: that controller marks a new account verified inline once the six-digit code
            // checks out, so this controller's own hasVerifiedEmail() guard above short-circuits
            // and a schedule waiting for the person we invited was never handed over.
            $user->claimRolesByEmail();
            $user->claimSalesByEmail();

            event(new Verified($request->user()));
        }

        return redirect(route('home'))
            ->with('message', __('messages.confirmed_email'));
    }
}
