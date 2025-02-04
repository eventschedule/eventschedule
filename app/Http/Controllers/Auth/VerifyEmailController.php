<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use App\Http\Requests\UserEmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use App\Models\Role;
use App\Models\Sale;

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
            $roles = Role::whereEmail($user->email)
                            ->whereNull('user_id')
                            ->get();

            foreach ($roles as $role) {
                $role->user_id = $user->id;
                $role->save();

                if ($role->markEmailAsVerified()) {
                    event(new Verified($role));
                }    

                $user->roles()->attach($role->id, ['level' => 'owner', 'created_at' => now()]);
            }

            $tickets = Sale::whereEmail($user->email)
                            ->whereNull('user_id')
                            ->get();

            foreach ($tickets as $ticket) {
                $ticket->user_id = $user->id;
                $ticket->save();
            }

            event(new Verified($request->user()));
        }
        
        return redirect(route('home'))
                ->with('message', __('messages.confirmed_email'));
    }
}
