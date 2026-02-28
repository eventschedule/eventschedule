<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Mail\SupportEmail;
use App\Models\BoostCampaign;
use App\Notifications\DeletedUserNotification;
use App\Services\AuditService;
use App\Services\BoostBillingService;
use App\Services\MetaAdsService;
use App\Utils\InvoiceNinja;
use Codedge\Updater\UpdaterManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request, UpdaterManager $updater): View
    {
        $data = [
            'user' => $request->user(),
            'editorRoles' => $request->user()->editor()->get(),
        ];

        if (! config('app.hosted') && ! config('app.is_testing')) {
            $data['version_installed'] = $updater->source()->getVersionInstalled();

            try {
                if ($request->has('clear_cache')) {
                    cache()->forget('version_available');
                }

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
        // Demo mode: prevent all profile changes
        if (is_demo_mode()) {
            return Redirect::to(route('profile.edit').'#section-profile')
                ->with('error', __('messages.demo_mode_restriction'));
        }

        $validated = $request->validated();
        $validated['use_24_hour_time'] = $request->input('use_24_hour_time') ? true : null;

        // Validate default_role_id - user must be editor of the selected role
        if (! empty($validated['default_role_id'])) {
            $role = \App\Models\Role::where('id', $validated['default_role_id'])->where('is_deleted', false)->first();
            if (! $role || ! $request->user()->isEditor($role->subdomain)) {
                unset($validated['default_role_id']);
            }
        }

        $request->user()->fill($validated);

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        if ($request->hasFile('profile_image')) {
            $user = $request->user();
            $file = $request->file('profile_image');

            // Validate file extension and MIME type
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $extension = strtolower($file->getClientOriginalExtension());
            if (! in_array($extension, $allowedExtensions) || ! in_array($file->getMimeType(), $allowedMimeTypes)) {
                return Redirect::to(route('profile.edit').'#section-profile')
                    ->withErrors(['profile_image' => 'Invalid file type. Allowed: jpg, jpeg, png, gif, webp']);
            }

            if ($user->profile_image_url) {
                $rawPath = $user->getAttributes()['profile_image_url'];
                if (! str_starts_with($rawPath, 'http')) {
                    $path = $rawPath;
                    if (config('filesystems.default') == 'local') {
                        $path = 'public/'.$path;
                    }
                    Storage::delete($path);
                }
            }

            $filename = strtolower('profile_'.Str::random(32).'.'.$extension);
            $path = $file->storeAs(config('filesystems.default') == 'local' ? '/public' : '/', $filename);

            $user->profile_image_url = $filename;
            $user->save();
        }

        AuditService::log(AuditService::PROFILE_UPDATE, $request->user()->id, 'User', $request->user()->id);

        return Redirect::to(route('profile.edit').'#section-profile')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        // Demo mode: prevent account deletion
        if (is_demo_mode()) {
            return Redirect::to(route('profile.edit').'#section-delete-account')
                ->with('error', __('messages.demo_mode_restriction'));
        }

        $user = $request->user();

        // Only require password validation if user has a password set
        $rules = [
            'feedback' => ['nullable', 'string', 'max:2000'],
        ];

        if ($user->hasPassword()) {
            $rules['password'] = ['required', 'current_password'];
        }

        $request->validateWithBag('userDeletion', $rules);

        // Send feedback email if provided (before logout so we have user data)
        // Skip for demo mode to prevent spam
        if ($request->filled('feedback') && ! is_demo_mode()) {
            Mail::to('contact@eventschedule.com')->send(new SupportEmail(
                $user->name ?? $user->email,
                $user->email,
                $request->feedback,
                'Account Deletion Feedback'
            ));
        }

        AuditService::log(AuditService::PROFILE_DELETE, $user->id, 'User', $user->id);

        Auth::logout();

        if ($user->profile_image_url) {
            $rawPath = $user->getAttributes()['profile_image_url'];
            if (! str_starts_with($rawPath, 'http')) {
                $path = $rawPath;
                if (config('filesystems.default') == 'local') {
                    $path = 'public/'.$path;
                }
                Storage::delete($path);
            }
        }

        $roles = $user->owner()->get();

        foreach ($roles as $role) {
            // Clean up Google Calendar webhook before deleting role
            if ($role->google_webhook_id && $role->google_webhook_resource_id) {
                try {
                    if ($user->google_token) {
                        $googleCalendarService = app(\App\Services\GoogleCalendarService::class);

                        // Ensure user has valid token before deleting webhook
                        if ($googleCalendarService->ensureValidToken($user)) {
                            $googleCalendarService->deleteWebhook($role->google_webhook_id, $role->google_webhook_resource_id);
                        }
                    }
                } catch (\Exception $e) {
                    \Log::warning('Failed to clean up webhook during user deletion', [
                        'user_id' => $user->id,
                        'role_id' => $role->id,
                        'webhook_id' => $role->google_webhook_id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            if ($role->profile_image_url) {
                $path = $role->getAttributes()['profile_image_url'];
                if (config('filesystems.default') == 'local') {
                    $path = 'public/'.$path;
                }
                Storage::delete($path);
            }

            if ($role->header_image_url) {
                $path = $role->getAttributes()['header_image_url'];
                if (config('filesystems.default') == 'local') {
                    $path = 'public/'.$path;
                }
                Storage::delete($path);
            }

            if ($role->background_image_url) {
                $path = $role->getAttributes()['background_image_url'];
                if (config('filesystems.default') == 'local') {
                    $path = 'public/'.$path;
                }
                Storage::delete($path);
            }
        }

        // Cancel active boost campaigns before deletion (prevents orphaned Meta campaigns)
        $activeCampaigns = BoostCampaign::where('user_id', $user->id)
            ->whereIn('status', ['active', 'paused', 'pending_payment'])
            ->get();

        foreach ($activeCampaigns as $campaign) {
            try {
                $cancelled = \DB::transaction(function () use ($campaign) {
                    $campaign = BoostCampaign::lockForUpdate()->find($campaign->id);
                    if (! $campaign || ! $campaign->canBeCancelled()) {
                        return false;
                    }
                    $campaign->update([
                        'status' => 'cancelled',
                        'meta_status' => $campaign->meta_campaign_id ? 'DELETED' : null,
                    ]);

                    return true;
                });

                if ($cancelled) {
                    if ($campaign->meta_campaign_id) {
                        (new MetaAdsService)->deleteCampaign($campaign);
                    }

                    if (config('app.hosted') && ! config('app.is_testing')) {
                        $campaign->refresh();
                        if (! in_array($campaign->billing_status, ['refunded', 'partially_refunded'])) {
                            $billingService = new BoostBillingService;
                            if ($campaign->billing_status === 'pending') {
                                if ($campaign->stripe_payment_intent_id) {
                                    $billingService->cancelPaymentIntent($campaign);
                                }
                            } else {
                                $campaign->actual_spend && $campaign->actual_spend > 0
                                    ? $billingService->refundUnspent($campaign)
                                    : $billingService->refundFull($campaign);
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                \Log::warning('Failed to cancel boost campaign during user deletion', [
                    'campaign_id' => $campaign->id,
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Send notification to the deleted user
        Notification::route('mail', $user->email)->notify(new DeletedUserNotification($user));

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    public function deleteImage(Request $request)
    {
        if (is_demo_mode()) {
            return response()->json(['error' => __('messages.demo_mode_restriction')], 403);
        }

        $user = $request->user();

        if ($user->profile_image_url) {
            $rawPath = $user->getAttributes()['profile_image_url'];
            if (! str_starts_with($rawPath, 'http')) {
                $path = $rawPath;
                if (config('filesystems.default') == 'local') {
                    $path = 'public/'.$path;
                }
                Storage::delete($path);
            }

            $user->profile_image_url = null;
            $user->save();
        }

        return response()->json(['message' => __('messages.deleted_image')]);
    }

    public function updatePayments(Request $request): RedirectResponse
    {
        // Demo mode: prevent payment settings changes
        if (is_demo_mode()) {
            return Redirect::to(route('profile.edit').'#section-payment-methods')
                ->with('error', __('messages.demo_mode_restriction'));
        }

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

                return Redirect::to(route('profile.edit').'#section-payment-methods')->with('message', __('messages.invoiceninja_connected'));
            } catch (\Exception $e) {
                return Redirect::to(route('profile.edit').'#section-payment-methods')->with('error', __('messages.error_invoiceninja_connection'));
            }
        }

        if ($paymentUrl) {
            $parsed = parse_url($paymentUrl);
            if (! $parsed || ! isset($parsed['scheme']) || ! in_array($parsed['scheme'], ['http', 'https'])) {
                return Redirect::to(route('profile.edit').'#section-payment-methods')
                    ->with('error', __('messages.invalid_url'));
            }
            $user->payment_url = $paymentUrl;
            $user->payment_secret = strtolower(\Str::random(32));
            $user->save();

            return Redirect::to(route('profile.edit').'#section-payment-methods')->with('message', __('messages.payment_url_connected'));
        }

        return Redirect::to(route('profile.edit').'#section-payment-methods')->with('status', 'payments-updated');
    }

    public function updateInvoiceninjaMode(Request $request): RedirectResponse
    {
        if (is_demo_mode()) {
            return Redirect::to(route('profile.edit').'#section-payment-methods')
                ->with('error', __('messages.demo_mode_restriction'));
        }

        $request->validate([
            'invoiceninja_mode' => ['required', 'in:invoice,payment_link'],
        ]);

        $user = $request->user();
        $user->invoiceninja_mode = $request->invoiceninja_mode;
        $user->save();

        return Redirect::to(route('profile.edit').'#section-payment-methods')->with('status', 'payments-updated');
    }

    public function unlinkPaymentUrl(Request $request): RedirectResponse
    {
        // Demo mode: prevent payment settings changes
        if (is_demo_mode()) {
            return Redirect::to(route('profile.edit').'#section-payment-methods')
                ->with('error', __('messages.demo_mode_restriction'));
        }

        $user = $request->user();
        $user->payment_url = null;
        $user->payment_secret = null;
        $user->save();

        return Redirect::to(route('profile.edit').'#section-payment-methods')->with('message', __('messages.payment_url_unlinked'));
    }
}
