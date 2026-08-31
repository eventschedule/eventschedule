<?php

namespace App\Http\Controllers;

use App\Exceptions\InvoiceNinjaException;
use App\Http\Requests\ProfileUpdateRequest;
use App\Mail\SupportEmail;
use App\Models\BackupJob;
use App\Models\BoostCampaign;
use App\Notifications\DeletedUserNotification;
use App\Services\AppUpdateService;
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
        $activeImportJob = BackupJob::where('user_id', $request->user()->id)
            ->where('type', 'import')
            ->whereIn('status', ['pending', 'processing'])
            ->latest()
            ->first();

        $activeExportJob = BackupJob::where('user_id', $request->user()->id)
            ->where('type', 'export')
            ->whereIn('status', ['pending', 'processing'])
            ->latest()
            ->first();

        // Fallback for sync queue: job completes during dispatch, so check session
        if (! $activeExportJob && session('backup_job_id')) {
            $activeExportJob = BackupJob::where('id', session('backup_job_id'))
                ->where('user_id', $request->user()->id)
                ->where('type', 'export')
                ->first();
        }

        $data = [
            'user' => $request->user(),
            'editorRoles' => $request->user()->editor()->get(),
            'activeImportJobId' => $activeImportJob?->id,
            'activeExportJobId' => $activeExportJob?->id,
        ];

        if (can_self_update($request->user())) {
            $appUpdate = app(AppUpdateService::class);

            $data['version_installed'] = $appUpdate->versionInstalled();
            // null when the lookup failed, which the partial renders as "unknown" rather
            // than as a version that differs from the installed one.
            $data['version_available'] = $appUpdate->versionAvailable($updater, $request->has('clear_cache'));
            $data['update_available'] = $appUpdate->isUpdateAvailable();
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
        if ($request->has('ask_before_following')) {
            $validated['follow_consent_dismissed'] = ! $request->boolean('ask_before_following');
        }

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
            return Redirect::to(route('profile.edit').'#section-delete')
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

            // Clean up the Outlook / Microsoft Graph subscription before deleting role
            if ($role->microsoft_webhook_id && $user->microsoft_token) {
                try {
                    app(\App\Services\MicrosoftCalendarService::class)
                        ->deleteSubscription($user, $role->microsoft_webhook_id);
                } catch (\Exception $e) {
                    \Log::warning('Failed to clean up Outlook subscription during user deletion', [
                        'user_id' => $user->id,
                        'role_id' => $role->id,
                        'subscription_id' => $role->microsoft_webhook_id,
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
            ->unsettled()
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

                    // Gate the STRIPE call, not the refund. settlePayment()'s credit branch
                    // debits boost_credit regardless of mode, so gating the whole block meant
                    // deleting on selfhost destroyed the advertiser's wallet balance outright.
                    // refundOnCancellation() reaches Stripe only when there is an intent, which
                    // a selfhost campaign never has.
                    $campaign->refresh();
                    if (! in_array($campaign->billing_status, ['refunded', 'partially_refunded'])) {
                        $billingService = new BoostBillingService;
                        if ($campaign->billing_status === 'pending') {
                            if (config('app.hosted') && ! config('app.is_testing') && $campaign->stripe_payment_intent_id) {
                                $billingService->cancelPaymentIntent($campaign);
                            }
                        } else {
                            $billingService->refundOnCancellation($campaign);
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

        // Account-less audience rows are keyed on the EMAIL, not on a user id, so no foreign key
        // takes them with the account. The privacy policy's erasure section promises deletion of
        // "your account and all associated data" and calls it final, total and irreversible, and
        // there is no separate erasure flow in the app - so leaving the address behind in a table
        // the user never knew existed would break that promise, and they would keep being mailed.
        \App\Models\RoleSubscriber::where('email', strtolower($user->email))->delete();

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
        $apiUrl = trim((string) $request->invoiceninja_api_url);
        $paymentUrl = $request->payment_url;

        // Only treat this as an Invoice Ninja submission when the form actually carried the
        // field. The Payment URL form posts to this same route without it, and the blank
        // token fallback below would otherwise hijack every Payment URL save by a
        // connected user.
        $apiKey = $request->has('invoiceninja_api_key')
            ? trim((string) $request->invoiceninja_api_key)
            : '';

        // "Change credentials" leaves the token blank (or as the bullet placeholder) to
        // mean "unchanged", so the owner can correct just the URL. Same idea as the
        // password sentinel in RoleController::testEmail().
        if ($apiKey === '' || $apiKey === str_repeat('•', 10)) {
            $apiKey = $request->has('invoiceninja_api_key') ? (string) $user->invoiceninja_api_key : '';
        }

        if ($apiKey) {
            try {
                $invoiceNinja = new InvoiceNinja($apiKey, $apiUrl);
                $company = $invoiceNinja->getCompany();

                // Create the webhook before persisting anything. Previously the save ran
                // first, so a webhook failure left the credentials stored while the UI
                // reported "failed to connect", and the connected state's only exit was an
                // Unlink that also failed. This ordering also means a failed re-connect
                // never wipes a working configuration.
                $webhookSecret = strtolower(Str::random(32));

                // Runs before the new secret is assigned below, so this still passes the
                // previous one, which is what identifies the webhook to replace.
                $this->pruneInvoiceNinjaWebhooks($invoiceNinja, $company, $user->invoiceninja_webhook_secret);

                $invoiceNinja->createWebhook(route('invoiceninja.webhook', ['secret' => $webhookSecret]));

                $user->invoiceninja_api_key = $apiKey;
                $user->invoiceninja_api_url = $apiUrl;
                // Null coalesce rather than a bare index: Laravel promotes the "undefined
                // key" warning to an ErrorException, which the catch below would then
                // report as a connection failure even though the connection worked.
                $user->invoiceninja_company_name = $company['settings']['name'] ?? '';
                $user->invoiceninja_webhook_secret = $webhookSecret;
                $user->save();

                return Redirect::to(route('profile.edit').'#section-payment-methods')->with('message', __('messages.invoiceninja_connected'));
            } catch (InvoiceNinjaException $e) {
                // Invoice Ninja, or a proxy in front of it, rejected the request (bad token,
                // unreachable host, WAF challenge, wrong API URL). Its message is the
                // actionable content for the account owner configuring their own server,
                // exactly like the SMTP case in RoleController::testEmail(). Expected
                // user-config failure, so it is logged but not reported to Sentry.
                \Log::error('Invoice Ninja connection failed: '.$e->getMessage(), [
                    'user_id' => $user->id,
                    'api_url' => $apiUrl,
                ]);

                return Redirect::to(route('profile.edit').'#section-payment-methods')
                    ->withInput($request->except('invoiceninja_api_key'))
                    ->with('error', __('messages.error_invoiceninja_connection'))
                    ->with('invoiceninja_error', mb_substr($e->getMessage(), 0, 500))
                    ->with('invoiceninja_reason', $e->reasonKey());
            } catch (\Exception $e) {
                report($e);

                return Redirect::to(route('profile.edit').'#section-payment-methods')
                    ->withInput($request->except('invoiceninja_api_key'))
                    ->with('error', __('messages.error_invoiceninja_connection'));
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

    /**
     * Delete this user's previous webhook in Invoice Ninja before registering a new one.
     *
     * Every connect mints a fresh invoiceninja_webhook_secret, so without this a reconnect
     * leaves a dead webhook behind each time.
     *
     * Matched on the user's own previous secret, exactly as unlink() does, NOT on the
     * shared "/invoiceninja/webhook/" URL prefix. That prefix belongs to every user on the
     * installation (see InvoiceNinjaController::webhook(), which resolves the caller by
     * scanning all users with a secret), so a prefix match would delete another user's
     * webhook whenever two accounts share one Invoice Ninja company, silently stopping
     * their sales from being marked paid.
     *
     * Failures here must never block the connection itself.
     */
    private function pruneInvoiceNinjaWebhooks(InvoiceNinja $invoiceNinja, $company, ?string $previousSecret): void
    {
        if (empty($previousSecret)) {
            return;
        }

        try {
            $previousUrl = route('invoiceninja.webhook', ['secret' => $previousSecret]);

            foreach ($company['webhooks'] ?? [] as $webhook) {
                if (! isset($webhook['id'], $webhook['target_url'])) {
                    continue;
                }

                if ($webhook['target_url'] === $previousUrl) {
                    $invoiceNinja->deleteWebhook($webhook['id']);
                }
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to prune stale Invoice Ninja webhooks: '.$e->getMessage());
        }
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
