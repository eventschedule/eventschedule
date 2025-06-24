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
use Codedge\Updater\UpdaterManager;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request, UpdaterManager $updater): View
    {
        $data = [
            'user' => $request->user(),
        ];

        if (! config('app.hosted')) {
            $data['version_installed'] = $updater->source()->getVersionInstalled();

            try {
                $data['version_available'] = cache()->remember('version_available', 3600, function () use ($updater) {
                    \Log::info('Checking for new version');
                    return $updater->source()->getVersionAvailable();
                });            
            } catch (\Exception $e) {
                $data['version_available'] = 'Error: failed to check version';
            }
        }

        return view('profile.edit', $data);
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
        $apiUrl = $request->invoiceninja_api_url;
        $paymentUrl = $request->payment_url;
        $name = '';

        if ($apiKey) {
            try {
                $invoiceNinja = new InvoiceNinja($apiKey, $apiUrl);
                $company = $invoiceNinja->getCompany();
                $name = $company['settings']['name'];

                $user->invoiceninja_api_key = $request->invoiceninja_api_key;
                $user->invoiceninja_api_url = $request->invoiceninja_api_url;
                $user->invoiceninja_company_name = $name;
                $user->invoiceninja_webhook_secret = strtolower(\Str::random(32));
                $user->save();

                $invoiceNinja->createWebhook(route('invoiceninja.webhook', ['secret' => $user->invoiceninja_webhook_secret]));

                return Redirect::route('profile.edit')->with('message', __('messages.invoiceninja_connected'));
            } catch (\Exception $e) {
                return Redirect::route('profile.edit')->with('error', __('messages.error_invoiceninja_connection'));
            }
        }

        if ($paymentUrl) {
            $user->payment_url = $paymentUrl;
            $user->payment_secret = strtolower(\Str::random(32));
            $user->save();

            return Redirect::route('profile.edit')->with('message', __('messages.payment_url_connected'));
        }

        return Redirect::route('profile.edit')->with('status', 'payments-updated');
    }

    public function unlinkPaymentUrl(Request $request): RedirectResponse
    {
        $user = $request->user();
        $user->payment_url = null;
        $user->payment_secret = null;
        $user->save();

        return Redirect::route('profile.edit')->with('message', __('messages.payment_url_unlinked'));
    }
}
