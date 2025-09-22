<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Support\MailTemplateManager;
use App\Support\UpdateConfigManager;
use App\Support\WalletConfigManager;
use Codedge\Updater\UpdaterManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;
use App\Mail\TemplatePreview;

class SettingsController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorizeAdmin($request->user());

        return view('settings.index');
    }

    public function general(Request $request, UpdaterManager $updater): View
    {
        $this->authorizeAdmin($request->user());

        $versionInstalled = null;
        $versionAvailable = null;

        if (! config('app.hosted') && ! config('app.testing')) {
            $versionInstalled = $updater->source()->getVersionInstalled();

            try {
                if ($request->has('clear_cache')) {
                    cache()->forget('version_available');
                }

                $versionAvailable = cache()->remember('version_available', 3600, function () use ($updater) {
                    \Log::info('Checking for new version');

                    return $updater->source()->getVersionAvailable();
                });
            } catch (\Exception $e) {
                $versionAvailable = 'Error: failed to check version';
            }
        }

        return view('settings.general', [
            'generalSettings' => $this->getGeneralSettings(),
            'versionInstalled' => $versionInstalled,
            'versionAvailable' => $versionAvailable,
        ]);
    }

    public function integrations(Request $request): View
    {
        $this->authorizeAdmin($request->user());

        return view('settings.integrations', [
            'user' => $request->user(),
        ]);
    }

    public function wallet(Request $request): View
    {
        $this->authorizeAdmin($request->user());

        $appleStored = Setting::forGroup('wallet.apple');
        $googleStored = Setting::forGroup('wallet.google');

        return view('settings.wallet', [
            'appleSettings' => $this->buildAppleWalletFormValues($appleStored),
            'appleFiles' => [
                'certificate' => $this->buildFileInfo(
                    $appleStored['certificate_path'] ?? null,
                    config('wallet.apple.certificate_path')
                ),
                'wwdr' => $this->buildFileInfo(
                    $appleStored['wwdr_certificate_path'] ?? null,
                    config('wallet.apple.wwdr_certificate_path')
                ),
            ],
            'applePasswordStored' => array_key_exists('certificate_password', $appleStored)
                && $appleStored['certificate_password'] !== null,
            'googleSettings' => $this->buildGoogleWalletFormValues($googleStored),
            'googleFiles' => [
                'service_account' => $this->buildFileInfo(
                    $googleStored['service_account_json_path'] ?? null,
                    config('wallet.google.service_account_json_path')
                ),
            ],
            'googleInlineStatus' => [
                'stored' => !empty($googleStored['service_account_json']),
                'configured' => empty($googleStored['service_account_json'])
                    && !empty(config('wallet.google.service_account_json')),
            ],
        ]);
    }

    public function email(Request $request): View
    {
        $this->authorizeAdmin($request->user());

        $mailSettings = $this->getMailSettings();

        return view('settings.email', [
            'mailSettings' => $mailSettings,
            'availableMailers' => [
                'smtp' => 'SMTP',
                'log' => 'Log',
            ],
            'availableEncryptions' => [
                '' => __('messages.none'),
                'tls' => 'TLS',
                'ssl' => 'SSL',
            ],
        ]);
    }

    public function emailTemplates(Request $request, MailTemplateManager $mailTemplates): View
    {
        $this->authorizeAdmin($request->user());

        return view('settings.email-templates', [
            'mailTemplates' => $mailTemplates->all(),
        ]);
    }

    public function showEmailTemplate(Request $request, MailTemplateManager $mailTemplates, string $template): View
    {
        $this->authorizeAdmin($request->user());

        if (! $mailTemplates->exists($template)) {
            abort(404);
        }

        return view('settings.email-templates.show', [
            'template' => $mailTemplates->get($template),
        ]);
    }

    public function updateAppleWallet(Request $request): RedirectResponse
    {
        $this->authorizeAdmin($request->user());

        $stored = Setting::forGroup('wallet.apple');

        $request->validate([
            'apple_enabled' => ['required', 'boolean'],
            'apple_pass_type_identifier' => ['nullable', 'string', 'max:255'],
            'apple_team_identifier' => ['nullable', 'string', 'max:255'],
            'apple_organization_name' => ['nullable', 'string', 'max:255'],
            'apple_background_color' => ['nullable', 'string', 'max:50'],
            'apple_foreground_color' => ['nullable', 'string', 'max:50'],
            'apple_label_color' => ['nullable', 'string', 'max:50'],
            'apple_certificate' => ['nullable', 'file', 'max:10240'],
            'apple_certificate_password' => ['nullable', 'string', 'max:255'],
            'apple_clear_certificate_password' => ['nullable', 'boolean'],
            'apple_remove_certificate' => ['nullable', 'boolean'],
            'apple_wwdr_certificate' => ['nullable', 'file', 'max:10240'],
            'apple_remove_wwdr_certificate' => ['nullable', 'boolean'],
        ]);

        $certificatePath = $stored['certificate_path'] ?? null;

        if ($request->boolean('apple_remove_certificate')) {
            $this->deleteStoredFile($certificatePath);
            $certificatePath = null;
        }

        if ($request->file('apple_certificate')) {
            $certificatePath = $this->storeUploadedFile($request->file('apple_certificate'), 'wallet/apple', $certificatePath);
        }

        $wwdrPath = $stored['wwdr_certificate_path'] ?? null;

        if ($request->boolean('apple_remove_wwdr_certificate')) {
            $this->deleteStoredFile($wwdrPath);
            $wwdrPath = null;
        }

        if ($request->file('apple_wwdr_certificate')) {
            $wwdrPath = $this->storeUploadedFile($request->file('apple_wwdr_certificate'), 'wallet/apple', $wwdrPath);
        }

        $certificatePassword = $stored['certificate_password'] ?? null;

        if ($request->filled('apple_certificate_password')) {
            $certificatePassword = trim((string) $request->input('apple_certificate_password'));
        } elseif ($request->boolean('apple_clear_certificate_password')) {
            $certificatePassword = null;
        }

        if ($request->boolean('apple_enabled')) {
            $hasCertificate = !empty($certificatePath)
                || $request->hasFile('apple_certificate')
                || !empty(env('APPLE_WALLET_CERTIFICATE_PATH'));

            $hasWwdrCertificate = !empty($wwdrPath)
                || $request->hasFile('apple_wwdr_certificate')
                || !empty(env('APPLE_WALLET_WWDR_CERTIFICATE_PATH'));

            if (! $hasCertificate) {
                throw ValidationException::withMessages([
                    'apple_certificate' => __('messages.apple_wallet_certificate_required'),
                ]);
            }

            if (! $hasWwdrCertificate) {
                throw ValidationException::withMessages([
                    'apple_wwdr_certificate' => __('messages.apple_wallet_wwdr_certificate_required'),
                ]);
            }
        }

        $appleSettings = [
            'enabled' => $request->boolean('apple_enabled'),
            'pass_type_identifier' => $this->nullableTrim($request->input('apple_pass_type_identifier')),
            'team_identifier' => $this->nullableTrim($request->input('apple_team_identifier')),
            'organization_name' => $this->nullableTrim($request->input('apple_organization_name')),
            'background_color' => $this->nullableTrim($request->input('apple_background_color')),
            'foreground_color' => $this->nullableTrim($request->input('apple_foreground_color')),
            'label_color' => $this->nullableTrim($request->input('apple_label_color')),
            'certificate_path' => $certificatePath,
            'certificate_password' => $certificatePassword,
            'wwdr_certificate_path' => $wwdrPath,
        ];

        Setting::setGroup('wallet.apple', $appleSettings);

        WalletConfigManager::applyApple($appleSettings);

        return redirect()->route('settings.wallet')->with('status', 'apple-wallet-settings-updated');
    }

    public function updateGoogleWallet(Request $request): RedirectResponse
    {
        $this->authorizeAdmin($request->user());

        $stored = Setting::forGroup('wallet.google');

        $request->validate([
            'google_enabled' => ['required', 'boolean'],
            'google_issuer_id' => ['nullable', 'string', 'max:255'],
            'google_issuer_name' => ['nullable', 'string', 'max:255'],
            'google_class_suffix' => ['nullable', 'string', 'max:255'],
            'google_service_account_file' => ['nullable', 'file', 'max:10240'],
            'google_remove_service_account_file' => ['nullable', 'boolean'],
            'google_service_account_json' => ['nullable', 'string'],
            'google_clear_service_account_json' => ['nullable', 'boolean'],
        ]);

        $serviceAccountPath = $stored['service_account_json_path'] ?? null;

        if ($request->boolean('google_remove_service_account_file')) {
            $this->deleteStoredFile($serviceAccountPath);
            $serviceAccountPath = null;
        }

        if ($request->file('google_service_account_file')) {
            $serviceAccountPath = $this->storeUploadedFile(
                $request->file('google_service_account_file'),
                'wallet/google',
                $serviceAccountPath
            );
        }

        $serviceAccountJson = $stored['service_account_json'] ?? null;

        if ($request->filled('google_service_account_json')) {
            $serviceAccountJson = $this->nullableTrim($request->input('google_service_account_json'));
        } elseif ($request->boolean('google_clear_service_account_json')) {
            $serviceAccountJson = null;
        }

        $googleSettings = [
            'enabled' => $request->boolean('google_enabled'),
            'issuer_id' => $this->nullableTrim($request->input('google_issuer_id')),
            'issuer_name' => $this->nullableTrim($request->input('google_issuer_name')),
            'class_suffix' => $this->nullableTrim($request->input('google_class_suffix')),
            'service_account_json_path' => $serviceAccountPath,
            'service_account_json' => $serviceAccountJson,
        ];

        $finalIssuerId = $googleSettings['issuer_id']
            ?? $stored['issuer_id']
            ?? config('wallet.google.issuer_id');

        if ($request->boolean('google_enabled')) {
            if (empty($finalIssuerId)) {
                throw ValidationException::withMessages([
                    'google_issuer_id' => __('messages.google_wallet_issuer_id_required'),
                ]);
            }

            $hasCredentials = !empty($serviceAccountPath)
                || $request->hasFile('google_service_account_file')
                || !empty($serviceAccountJson)
                || !empty(env('GOOGLE_WALLET_SERVICE_ACCOUNT_JSON_PATH'))
                || !empty(env('GOOGLE_WALLET_SERVICE_ACCOUNT_JSON'));

            if (! $hasCredentials) {
                throw ValidationException::withMessages([
                    'google_service_account_file' => __('messages.google_wallet_credentials_required'),
                ]);
            }
        }

        Setting::setGroup('wallet.google', $googleSettings);

        WalletConfigManager::applyGoogle($googleSettings);

        return redirect()->route('settings.wallet')->with('status', 'google-wallet-settings-updated');
    }

    public function updateMail(Request $request): RedirectResponse
    {
        $this->authorizeAdmin($request->user());

        $validated = $this->validateMailSettings($request);

        $mailSettings = $this->buildMailSettings($request, $validated);

        Setting::setGroup('mail', $mailSettings);

        $this->applyMailConfig($mailSettings);

        return redirect()->route('settings.email')->with('status', 'mail-settings-updated');
    }

    public function testMail(Request $request): JsonResponse
    {
        $this->authorizeAdmin($request->user());

        $user = $request->user();

        if (!$user || empty($user->email)) {
            return response()->json([
                'status' => 'error',
                'message' => __('messages.test_email_failed'),
                'error' => __('messages.test_email_missing_user'),
                'logs' => [
                    'No authenticated user email address was available for the test message.',
                ],
            ], 422);
        }

        $validated = $this->validateMailSettings($request);

        $originalSettings = $this->getMailSettings();
        $testMailSettings = $this->buildMailSettings($request, $validated);

        $this->applyMailConfig($testMailSettings);

        $logOutput = [];
        $logOutput[] = 'Test email started at ' . now()->toDateTimeString();
        $logOutput[] = 'Resolved mailer: ' . ($testMailSettings['mailer'] ?? config('mail.default'));
        $logOutput[] = 'Target host: ' . ($testMailSettings['host'] ?: '(not configured)');
        $logOutput[] = 'Target port: ' . ($testMailSettings['port'] ?: '(not configured)');
        $logOutput[] = 'Encryption: ' . ($testMailSettings['encryption'] ?: '(none)');
        $logOutput[] = 'Authentication username ' . ($testMailSettings['username'] ? 'provided' : 'not provided');
        $logOutput[] = 'From address: ' . $testMailSettings['from_address'];
        $logOutput[] = 'From name: ' . $testMailSettings['from_name'];
        $logOutput[] = 'Attempting to send test message to: ' . $user->email;

        try {
            Mail::raw(__('messages.test_email_body'), function ($message) use ($user) {
                $message->to($user->email)->subject(__('messages.test_email_subject'));
            });

            $inspection = $this->inspectMailerFailures();
            $failures = $inspection['failures'];

            if (! empty($inspection['note'])) {
                $logOutput[] = $inspection['note'];
            }

            if ($inspection['inspected'] && empty($failures)) {
                $logOutput[] = 'Mail driver did not report any delivery failures.';
            }

            if (empty($failures)) {
                return response()->json([
                    'status' => 'success',
                    'message' => __('messages.test_email_sent'),
                    'logs' => $logOutput,
                ]);
            }

            $logOutput[] = 'Mail driver reported failures for the following recipients:';

            foreach ($failures as $failure) {
                $logOutput[] = ' - ' . $failure;
            }

            return response()->json([
                'status' => 'error',
                'message' => __('messages.test_email_failed'),
                'error' => __('messages.test_email_failures'),
                'failures' => $failures,
                'logs' => $logOutput,
            ], 500);
        } catch (Throwable $exception) {
            report($exception);

            $logOutput[] = 'Encountered exception while sending the test email: ' . $exception->getMessage();
            $logOutput[] = 'Exception class: ' . get_class($exception);
            $logOutput[] = 'Stack trace:';

            foreach (explode(PHP_EOL, $exception->getTraceAsString()) as $traceLine) {
                if ($traceLine !== '') {
                    $logOutput[] = $traceLine;
                }
            }

            return response()->json([
                'status' => 'error',
                'message' => __('messages.test_email_failed'),
                'error' => $exception->getMessage(),
                'logs' => $logOutput,
            ], 500);
        } finally {
            $this->applyMailConfig($originalSettings);
            $this->purgeResolvedMailer($originalSettings['mailer'] ?? null);
        }
    }

    public function updateGeneral(Request $request): RedirectResponse
    {
        $this->authorizeAdmin($request->user());

        $validated = $request->validate([
            'public_url' => ['required', 'string', 'max:255', 'url'],
            'update_repository_url' => ['nullable', 'string', 'max:255', 'url'],
        ]);

        $publicUrl = $this->sanitizeUrl($validated['public_url']);

        $updateRepositoryUrl = $this->nullableTrim($validated['update_repository_url'] ?? null);

        if ($updateRepositoryUrl !== null) {
            $updateRepositoryUrl = $this->sanitizeUrl($updateRepositoryUrl);
        }

        Setting::setGroup('general', [
            'public_url' => $publicUrl,
            'update_repository_url' => $updateRepositoryUrl,
        ]);

        $this->applyGeneralConfig($publicUrl);
        UpdateConfigManager::apply($updateRepositoryUrl);

        return redirect()->route('settings.general')->with('status', 'general-settings-updated');
    }

    public function updateMailTemplate(Request $request, MailTemplateManager $mailTemplates, string $template): RedirectResponse
    {
        $this->authorizeAdmin($request->user());

        if (! $mailTemplates->exists($template)) {
            abort(404);
        }

        $templateConfig = $mailTemplates->get($template);

        $rules = [
            'enabled' => ['required', 'boolean'],
        ];

        if (isset($templateConfig['subject'])) {
            $rules['subject'] = ['required', 'string', 'max:255'];
        }

        if (!empty($templateConfig['has_subject_curated']) && isset($templateConfig['subject_curated'])) {
            $rules['subject_curated'] = ['required', 'string', 'max:255'];
        }

        if (isset($templateConfig['body'])) {
            $rules['body'] = ['required', 'string'];
        }

        if (!empty($templateConfig['has_body_curated']) && isset($templateConfig['body_curated'])) {
            $rules['body_curated'] = ['required', 'string'];
        }

        $validated = $request->validate($rules);

        $mailTemplates->updateTemplate($template, $validated);

        return redirect()
            ->route('settings.email_templates.show', ['template' => $template])
            ->with('status', 'mail-template-updated');
    }

    public function testMailTemplate(Request $request, MailTemplateManager $mailTemplates, string $template): JsonResponse
    {
        $this->authorizeAdmin($request->user());

        if (! $mailTemplates->exists($template)) {
            abort(404);
        }

        $user = $request->user();

        if (! $user || empty($user->email)) {
            return response()->json([
                'status' => 'error',
                'message' => __('messages.test_email_failed'),
                'error' => __('messages.test_email_missing_user'),
                'failures' => [],
            ], 422);
        }

        $isCurated = $request->boolean('curated');

        $data = array_merge(
            $mailTemplates->sampleData($template, $isCurated),
            ['is_curated' => $isCurated]
        );

        $subject = $mailTemplates->renderSubject($template, $data);
        $body = $mailTemplates->renderBody($template, $data);

        try {
            Mail::to($user->email, $user->name ?? null)->send(new TemplatePreview($subject, $body));

            $inspection = $this->inspectMailerFailures(true);
            $failures = $inspection['failures'];

            if (! empty($failures)) {
                return response()->json([
                    'status' => 'error',
                    'message' => __('messages.test_email_failed'),
                    'error' => __('messages.test_email_failures'),
                    'failures' => $failures,
                ], 500);
            }

            return response()->json([
                'status' => 'success',
                'message' => __('messages.test_email_sent'),
                'failures' => [],
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'status' => 'error',
                'message' => __('messages.test_email_failed'),
                'error' => $exception->getMessage(),
                'failures' => [],
            ], 500);
        }
    }

    /**
     * Inspect the configured mailer for any reported delivery failures.
     *
     * @return array{failures: array<int, string>, inspected: bool, note: string|null}
     */
    protected function inspectMailerFailures(bool $throwOnError = false): array
    {
        $result = [
            'failures' => [],
            'inspected' => false,
            'note' => null,
        ];

        try {
            $mailer = Mail::mailer();

            if ($mailer === null) {
                $result['note'] = 'Mailer instance was unavailable for failure inspection; assuming success.';

                return $result;
            }

            if (! is_object($mailer)) {
                $result['note'] = 'Mailer does not support reporting failed recipients; assuming success.';

                return $result;
            }

            if (! method_exists($mailer, 'failures')) {
                $result['note'] = 'Mailer does not support reporting failed recipients; assuming success.';

                return $result;
            }

            $result['failures'] = array_values(array_filter((array) $mailer->failures(), function ($failure) {
                return $failure !== null && $failure !== '';
            }));
            $result['inspected'] = true;

            return $result;
        } catch (Throwable $exception) {
            if ($throwOnError) {
                throw $exception;
            }

            $result['note'] = 'Unable to inspect mailer for delivery failures: ' . $exception->getMessage();

            return $result;
        }
    }

    protected function buildAppleWalletFormValues(array $stored): array
    {
        $config = config('wallet.apple');

        return [
            'enabled' => array_key_exists('enabled', $stored)
                ? WalletConfigManager::toBool($stored['enabled'])
                : (bool) ($config['enabled'] ?? false),
            'pass_type_identifier' => $stored['pass_type_identifier'] ?? ($config['pass_type_identifier'] ?? ''),
            'team_identifier' => $stored['team_identifier'] ?? ($config['team_identifier'] ?? ''),
            'organization_name' => $stored['organization_name'] ?? ($config['organization_name'] ?? config('app.name')),
            'background_color' => $stored['background_color'] ?? ($config['background_color'] ?? 'rgb(78,129,250)'),
            'foreground_color' => $stored['foreground_color'] ?? ($config['foreground_color'] ?? 'rgb(255,255,255)'),
            'label_color' => $stored['label_color'] ?? ($config['label_color'] ?? 'rgb(255,255,255)'),
        ];
    }

    protected function buildGoogleWalletFormValues(array $stored): array
    {
        $config = config('wallet.google');

        return [
            'enabled' => array_key_exists('enabled', $stored)
                ? WalletConfigManager::toBool($stored['enabled'])
                : (bool) ($config['enabled'] ?? false),
            'issuer_id' => $stored['issuer_id'] ?? ($config['issuer_id'] ?? ''),
            'issuer_name' => $stored['issuer_name'] ?? ($config['issuer_name'] ?? config('app.name')),
            'class_suffix' => $stored['class_suffix'] ?? ($config['class_suffix'] ?? 'event'),
        ];
    }

    protected function buildFileInfo(?string $storedRelativePath, ?string $configuredPath): array
    {
        $storedRelativePath = $this->nullableTrim($storedRelativePath);
        $configuredPath = $this->nullableTrim($configuredPath);

        if ($storedRelativePath) {
            $resolvedPath = storage_path('app/' . ltrim($storedRelativePath, '/'));

            return [
                'source' => 'settings',
                'stored_relative' => $storedRelativePath,
                'resolved_path' => $resolvedPath,
                'display_name' => basename($storedRelativePath),
                'exists' => Storage::disk('local')->exists($storedRelativePath),
            ];
        }

        if ($configuredPath) {
            return [
                'source' => 'environment',
                'stored_relative' => null,
                'resolved_path' => $configuredPath,
                'display_name' => basename($configuredPath),
                'exists' => file_exists($configuredPath),
            ];
        }

        return [
            'source' => null,
            'stored_relative' => null,
            'resolved_path' => null,
            'display_name' => null,
            'exists' => false,
        ];
    }

    protected function storeUploadedFile(?UploadedFile $file, string $directory, ?string $existingRelativePath = null): ?string
    {
        if (! $file) {
            return $existingRelativePath;
        }

        if ($existingRelativePath) {
            $this->deleteStoredFile($existingRelativePath);
        }

        $path = $file->store($directory);

        return $path ?: $existingRelativePath;
    }

    protected function deleteStoredFile(?string $relativePath): void
    {
        if ($relativePath) {
            Storage::disk('local')->delete($relativePath);
        }
    }

    protected function getMailSettings(): array
    {
        $storedMailSettings = Setting::forGroup('mail');

        return [
            'mailer' => $storedMailSettings['mailer'] ?? config('mail.default'),
            'host' => $storedMailSettings['host'] ?? config('mail.mailers.smtp.host'),
            'port' => $storedMailSettings['port'] ?? config('mail.mailers.smtp.port'),
            'username' => $storedMailSettings['username'] ?? config('mail.mailers.smtp.username'),
            'password' => $storedMailSettings['password'] ?? config('mail.mailers.smtp.password'),
            'encryption' => $storedMailSettings['encryption'] ?? config('mail.mailers.smtp.encryption'),
            'from_address' => $storedMailSettings['from_address'] ?? config('mail.from.address'),
            'from_name' => $storedMailSettings['from_name'] ?? config('mail.from.name'),
        ];
    }

    protected function getGeneralSettings(): array
    {
        $storedGeneralSettings = Setting::forGroup('general');

        return [
            'public_url' => $storedGeneralSettings['public_url'] ?? config('app.url'),
            'update_repository_url' => $storedGeneralSettings['update_repository_url']
                ?? config('self-update.repository_types.github.repository_url'),
        ];
    }

    protected function authorizeAdmin($user): void
    {
        abort_unless($user && $user->isAdmin(), 403);
    }

    protected function applyGeneralConfig(?string $publicUrl): void
    {
        if (empty($publicUrl)) {
            return;
        }

        config(['app.url' => $publicUrl]);
        URL::forceRootUrl($publicUrl);
    }

    protected function applyMailConfig(array $settings): void
    {
        config(['mail.default' => $settings['mailer']]);

        if ($settings['host'] !== null) {
            config(['mail.mailers.smtp.host' => $settings['host']]);
        }

        if ($settings['port'] !== null) {
            config(['mail.mailers.smtp.port' => (int) $settings['port']]);
        }

        if ($settings['username'] !== null) {
            config(['mail.mailers.smtp.username' => $settings['username']]);
        }

        if ($settings['password'] !== null) {
            config(['mail.mailers.smtp.password' => $settings['password']]);
        }

        if ($settings['encryption'] !== null) {
            config(['mail.mailers.smtp.encryption' => $settings['encryption'] ?: null]);
        }

        config([
            'mail.from.address' => $settings['from_address'],
            'mail.from.name' => $settings['from_name'],
        ]);

        $this->purgeResolvedMailer($settings['mailer'] ?? null);
    }

    protected function purgeResolvedMailer(?string $mailer = null): void
    {
        if (app()->bound('mail.manager')) {
            app('mail.manager')->purge($mailer);
        }

        app()->forgetInstance('mailer');

        if (method_exists(app(), 'forgetResolvedInstance')) {
            app()->forgetResolvedInstance('mailer');
        }
    }

    protected function nullableTrim(?string $value): ?string
    {
        $value = is_string($value) ? trim($value) : $value;

        return $value === '' ? null : $value;
    }

    protected function sanitizeUrl(string $url): string
    {
        $trimmed = trim($url);

        return rtrim($trimmed, '/');
    }

    protected function validateMailSettings(Request $request): array
    {
        return $request->validate([
            'mail_mailer' => ['required', Rule::in(['smtp', 'log'])],
            'mail_host' => [Rule::requiredIf($request->mail_mailer === 'smtp'), 'nullable', 'string', 'max:255'],
            'mail_port' => [Rule::requiredIf($request->mail_mailer === 'smtp'), 'nullable', 'integer', 'between:1,65535'],
            'mail_username' => ['nullable', 'string', 'max:255'],
            'mail_password' => ['nullable', 'string', 'max:255'],
            'mail_encryption' => ['nullable', Rule::in(['tls', 'ssl', ''])],
            'mail_from_address' => ['required', 'email', 'max:255'],
            'mail_from_name' => ['required', 'string', 'max:255'],
        ]);
    }

    protected function buildMailSettings(Request $request, array $validated): array
    {
        $currentSettings = Setting::forGroup('mail');

        $password = $request->filled('mail_password')
            ? trim($validated['mail_password'])
            : ($currentSettings['password'] ?? config('mail.mailers.smtp.password'));

        return [
            'mailer' => $validated['mail_mailer'],
            'host' => $this->nullableTrim($validated['mail_host'] ?? null),
            'port' => isset($validated['mail_port']) ? (string) $validated['mail_port'] : null,
            'username' => $this->nullableTrim($validated['mail_username'] ?? null),
            'password' => $this->nullableTrim($password),
            'encryption' => $this->nullableTrim($validated['mail_encryption'] ?? null),
            'from_address' => trim($validated['mail_from_address']),
            'from_name' => trim($validated['mail_from_name']),
        ];
    }
}
