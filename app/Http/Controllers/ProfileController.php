<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use App\Utils\InvoiceNinja;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        if ($request->hasFile('profile_image')) {
            $user = $request->user();
            
            if ($user->profile_image_url) {
                $path = $user->getAttributes()['profile_image_url'];
                if (config('filesystems.default') == 'local') {
                    $path = 'public/' . $path;
                }
                Storage::delete($path);
            }

            $file = $request->file('profile_image');
            $filename = strtolower('profile_' . Str::random(32) . '.' . $file->getClientOriginalExtension());
            $path = $file->storeAs(config('filesystems.default') == 'local' ? '/public' : '/', $filename);
            
            $user->profile_image_url = $filename;
            $user->save();
        }


        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        if ($user->profile_image_url) {
            $path = $user->getAttributes()['profile_image_url'];
            if (config('filesystems.default') == 'local') {
                $path = 'public/' . $path;
            }
            Storage::delete($path);
        }

        $roles = $user->owner()->get();

        foreach ($roles as $role) {
            if ($role->profile_image_url) {
                $path = $role->getAttributes()['profile_image_url'];
                if (config('filesystems.default') == 'local') {
                    $path = 'public/' . $path;
                }
                Storage::delete($path);
            }
    
            if ($role->header_image_url) {
                $path = $role->getAttributes()['header_image_url'];
                if (config('filesystems.default') == 'local') {
                    $path = 'public/' . $path;
                }
                Storage::delete($path);
            }
    
            if ($role->background_image_url) {
                $path = $role->getAttributes()['background_image_url'];
                if (config('filesystems.default') == 'local') {
                    $path = 'public/' . $path;
                }
                Storage::delete($path);
            }    
        }

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    public function updatePayments(Request $request): RedirectResponse
    {
        $user = $request->user();
        $apiKey = $request->invoiceninja_api_key;
        $name = '';

        if ($apiKey) {
            try {
                $invoiceNinja = new InvoiceNinja($apiKey);
                $company = $invoiceNinja->getCompany();
                $name = $company['settings']['name'];
            } catch (\Exception $e) {
                //
            }
            
            if ($name) {
                $user->invoiceninja_api_key = $request->invoiceninja_api_key;
                $user->invoiceninja_company_name = $name;
                $user->save();

                $invoiceNinja->createWebhook(route('invoiceninja.webhook'));
            }            
        }

        return Redirect::route('profile.edit')->with('status', 'payments-updated');
    }
}
