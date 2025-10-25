<?php

namespace App\Http\Controllers;

use App\Enums\ReleaseChannel;
use App\Models\MediaAsset;
use App\Models\MediaAssetVariant;
use App\Models\Setting;
use App\Services\ReleaseChannelService;
use App\Rules\AccessibleColor;
use App\Support\BrandingManager;
use App\Support\ColorUtils;
use App\Support\Logging\LogLevelNormalizer;
use App\Support\Logging\LoggingConfigManager;
use App\Support\MailConfigManager;
use App\Support\MailTemplateManager;
use App\Support\ReleaseChannelManager;
use App\Support\UpdateConfigManager;
use App\Support\WalletConfigManager;
use App\Utils\MarkdownUtils;
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

    public function general(Request $request, UpdaterManager $updater, ReleaseChannelService $releaseChannels): View
    {
        $this->authorizeAdmin($request->user());

        $generalSettings = $this->getGeneralSettings();
        $selectedChannel = ReleaseChannel::fromString($generalSettings['update_release_channel'] ?? null);

        $versionInstalled = null;
        $versionAvailable = null;

        if (! config('app.hosted') && ! config('app.testing')) {
            $versionInstalled = $updater->source()->getVersionInstalled();

            try {
                if ($request->has('clear_cache')) {
                    $releaseChannels->forgetCached($selectedChannel);
                }

                $versionAvailable = $releaseChannels->getLatestVersion($selectedChannel);
            } catch (Throwable $e) {
                $versionAvailable = 'Error: failed to check version';
            }
        }

        return view('settings.general', [
            'generalSettings' => $generalSettings,
            'availableLogLevels' => LoggingConfigManager::availableLevels(),
            'versionInstalled' => $versionInstalled,
            'versionAvailable' => $versionAvailable,
            'availableUpdateChannels' => ReleaseChannel::options(),
            'selectedUpdateChannel' => $selectedChannel->value,
        ]);
    }

    public function branding(Request $request): View
    {
        $this->authorizeAdmin($request->user());

        return view('settings.branding', [
            'brandingSettings' => $this->getBrandingSettings(),
            'languageOptions' => $this->getSupportedLanguageOptions(),
            'colorPalettes' => $this->getBrandingPalettes(),
        ]);
    }

    public function terms(Request $request): View
    {
        $this->authorizeAdmin($request->user());

        return view('settings.terms', [
            'termsSettings' => $this->getTermsSettings(),
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

        $this->applyMailConfig($testMailSettings, force: true);

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
            MailConfigManager::purgeResolvedMailer($originalSettings['mailer'] ?? null);
        }
    }

    public function updateGeneral(Request $request, ReleaseChannelService $releaseChannels): RedirectResponse
    {
        $this->authorizeAdmin($request->user());

        $storedGeneralSettings = Setting::forGroup('general');
        $previousChannel = ReleaseChannel::fromString($storedGeneralSettings['update_release_channel'] ?? null);

        $logLevelKeys = array_keys(LoggingConfigManager::availableLevels());

        $validated = $request->validate([
            'public_url' => ['required', 'string', 'max:255', 'url'],
            'update_repository_url' => ['nullable', 'string', 'max:255', 'url'],
            'log_syslog_host' => ['required', 'string', 'max:255'],
            'log_syslog_port' => ['required', 'integer', 'min:1', 'max:65535'],
            'log_level' => ['required', 'string', Rule::in($logLevelKeys)],
            'log_disabled' => ['nullable', 'boolean'],
            'update_release_channel' => ['required', 'string', Rule::in(ReleaseChannel::values())],
        ]);

        $publicUrl = $this->sanitizeUrl($validated['public_url']);

        $updateRepositoryUrl = $this->nullableTrim($validated['update_repository_url'] ?? null);

        if ($updateRepositoryUrl !== null) {
            $updateRepositoryUrl = $this->sanitizeUrl($updateRepositoryUrl);
        }

        $previousRepositoryUrl = $storedGeneralSettings['update_repository_url'] ?? null;
        $normalizedPreviousRepositoryUrl = $this->normalizeRepositoryUrl($previousRepositoryUrl);
        $normalizedNewRepositoryUrl = $this->normalizeRepositoryUrl($updateRepositoryUrl);

        $channel = ReleaseChannel::fromString($validated['update_release_channel'] ?? null);

        Setting::setGroup('general', [
            'public_url' => $publicUrl,
            'update_repository_url' => $updateRepositoryUrl,
            'update_release_channel' => $channel->value,
        ]);

        $syslogHost = $this->sanitizeHost($validated['log_syslog_host']);
        $syslogPort = (int) $validated['log_syslog_port'];
        $logLevel = LogLevelNormalizer::normalize(
            $validated['log_level'],
            config('logging.channels.single.level', 'debug')
        );

        $loggingSettings = [
            'syslog_host' => $syslogHost,
            'syslog_port' => (string) $syslogPort,
            'level' => $logLevel,
            'disabled' => $request->boolean('log_disabled') ? '1' : '0',
        ];

        Setting::setGroup('logging', $loggingSettings);

        if ($normalizedPreviousRepositoryUrl !== $normalizedNewRepositoryUrl || $previousChannel !== $channel) {
            $releaseChannels->forgetAll();
        }

        $this->applyGeneralConfig($publicUrl);
        UpdateConfigManager::apply($updateRepositoryUrl);
        ReleaseChannelManager::apply($channel);
        LoggingConfigManager::apply($loggingSettings);

        return redirect()->route('settings.general')->with('status', 'general-settings-updated');
    }

    public function updateBranding(Request $request): RedirectResponse
    {
        $this->authorizeAdmin($request->user());

        $storedBrandingSettings = Setting::forGroup('branding');

        if ($request->boolean('reset_branding')) {
            $previousLogoPath = $storedBrandingSettings['logo_path'] ?? null;
            $previousLogoDisk = $storedBrandingSettings['logo_disk'] ?? null;
            $previousMediaAssetId = $storedBrandingSettings['logo_media_asset_id'] ?? null;

            if ($previousLogoPath && empty($previousMediaAssetId)) {
                $this->deleteStoredFile($previousLogoPath, $previousLogoDisk);
            }

            Setting::clearGroup('branding');
            BrandingManager::apply();

            return redirect()->route('settings.branding')->with('status', 'branding-settings-reset');
        }

        $languageOptions = $this->getSupportedLanguageOptions();
        $supportedLanguageCodes = array_keys($languageOptions);

        $validated = $request->validate([
            'branding_logo_media_asset_id' => ['nullable', 'integer', 'exists:media_assets,id'],
            'branding_logo_media_variant_id' => ['nullable', 'integer', 'exists:media_asset_variants,id'],
            'branding_logo_alt' => ['nullable', 'string', 'max:255'],
            'branding_primary_color' => ['required', new AccessibleColor(__('messages.branding_primary_color'))],
            'branding_secondary_color' => ['required', new AccessibleColor(__('messages.branding_secondary_color'))],
            'branding_tertiary_color' => ['required', new AccessibleColor(__('messages.branding_tertiary_color'))],
            'branding_default_language' => ['required', 'string', Rule::in($supportedLanguageCodes)],
        ]);

        $logoAssetId = $request->input('branding_logo_media_asset_id');
        $logoVariantId = $request->input('branding_logo_media_variant_id');

        if ($logoVariantId && ! $logoAssetId) {
            throw ValidationException::withMessages([
                'branding_logo_media_variant_id' => __('messages.branding_logo_variant_mismatch'),
            ]);
        }

        $previousLogoPath = $storedBrandingSettings['logo_path'] ?? null;
        $previousLogoDisk = $storedBrandingSettings['logo_disk'] ?? null;
        $previousMediaAssetId = $storedBrandingSettings['logo_media_asset_id'] ?? null;

        $logoPath = $previousLogoPath;
        $logoDisk = $previousLogoDisk;
        $logoMediaAssetId = $storedBrandingSettings['logo_media_asset_id'] ?? null;
        $logoMediaVariantId = $storedBrandingSettings['logo_media_variant_id'] ?? null;

        if ($logoAssetId) {
            $asset = MediaAsset::find((int) $logoAssetId);

            if (! $asset) {
                throw ValidationException::withMessages([
                    'branding_logo_media_asset_id' => __('messages.branding_logo_missing'),
                ]);
            }

            if ($previousLogoPath && empty($previousMediaAssetId)) {
                $this->deleteStoredFile($previousLogoPath, $previousLogoDisk);
            }

            $logoMediaAssetId = $asset->id;
            $logoMediaVariantId = null;
            $logoPath = $asset->path;
            $logoDisk = $asset->disk ?: storage_public_disk();

            if ($logoVariantId) {
                $variant = MediaAssetVariant::where('media_asset_id', $asset->id)
                    ->find((int) $logoVariantId);

                if (! $variant) {
                    throw ValidationException::withMessages([
                        'branding_logo_media_variant_id' => __('messages.branding_logo_variant_mismatch'),
                    ]);
                }

                $logoMediaVariantId = $variant->id;
                $logoPath = $variant->path;
                $logoDisk = $variant->disk ?: $logoDisk;
            }
        } elseif ($request->exists('branding_logo_media_asset_id')) {
            if ($previousLogoPath && empty($previousMediaAssetId)) {
                $this->deleteStoredFile($previousLogoPath, $previousLogoDisk);
            }

            $logoPath = null;
            $logoDisk = null;
            $logoMediaAssetId = null;
            $logoMediaVariantId = null;
        }

        $logoAlt = $this->nullableTrim($validated['branding_logo_alt'] ?? null);
        $primaryColor = ColorUtils::normalizeHexColor($validated['branding_primary_color']) ?? '#1F2937';
        $secondaryColor = ColorUtils::normalizeHexColor($validated['branding_secondary_color']) ?? '#111827';
        $tertiaryColor = ColorUtils::normalizeHexColor($validated['branding_tertiary_color']) ?? '#374151';

        $defaultLanguage = $validated['branding_default_language'] ?? config('app.fallback_locale', 'en');

        if (! is_valid_language_code($defaultLanguage)) {
            $defaultLanguage = config('app.fallback_locale', 'en');
        }

        $brandingSettings = [
            'logo_path' => $logoPath,
            'logo_disk' => $logoPath ? ($logoDisk ?: storage_public_disk()) : null,
            'logo_alt' => $logoAlt,
            'logo_media_asset_id' => $logoMediaAssetId ? (int) $logoMediaAssetId : null,
            'logo_media_variant_id' => $logoMediaVariantId ? (int) $logoMediaVariantId : null,
            'primary_color' => $primaryColor,
            'secondary_color' => $secondaryColor,
            'tertiary_color' => $tertiaryColor,
            'default_language' => $defaultLanguage,
        ];

        Setting::setGroup('branding', $brandingSettings);
        BrandingManager::apply($brandingSettings);

        return redirect()->route('settings.branding')->with('status', 'branding-settings-updated');
    }

    public function updateTerms(Request $request): RedirectResponse
    {
        $this->authorizeAdmin($request->user());

        $validated = $request->validate([
            'terms_markdown' => ['nullable', 'string', 'max:65000'],
        ]);

        $termsMarkdown = $this->nullableTrim($validated['terms_markdown'] ?? null);
        $termsHtml = $termsMarkdown ? MarkdownUtils::convertToHtml($termsMarkdown) : null;

        $storedGeneralSettings = Setting::forGroup('general');

        Setting::setGroup('general', [
            'terms_markdown' => $termsMarkdown,
            'terms_html' => $termsHtml,
            'terms_updated_at' => (($storedGeneralSettings['terms_markdown'] ?? null) !== $termsMarkdown)
                ? now()->toIso8601String()
                : ($storedGeneralSettings['terms_updated_at'] ?? null),
        ]);

        return redirect()->route('settings.terms')->with('status', 'terms-settings-updated');
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

        $originalMailConfig = $this->getCurrentMailConfig();
        $mailSettings = $this->getMailSettings();

        $this->applyMailConfig($mailSettings, force: true);

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
        } finally {
            $this->applyMailConfig($originalMailConfig);
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

    protected function storeUploadedFile(
        ?UploadedFile $file,
        string $directory,
        ?string $existingRelativePath = null,
        ?string $disk = null
    ): ?string
    {
        if (! $file) {
            return $existingRelativePath;
        }

        $diskName = $disk ?: 'local';

        if ($existingRelativePath) {
            $this->deleteStoredFile($existingRelativePath, $diskName);
        }

        $path = $file->store($directory, $diskName);

        return $path ?: $existingRelativePath;
    }

    protected function deleteStoredFile(?string $relativePath, ?string $disk = null): void
    {
        if ($relativePath) {
            $diskName = $disk ?: 'local';
            Storage::disk($diskName)->delete($relativePath);
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
            'disable_delivery' => array_key_exists('disable_delivery', $storedMailSettings)
                ? $this->toBoolean($storedMailSettings['disable_delivery'])
                : $this->toBoolean(config('mail.disable_delivery')),
            'smtp_url' => config('mail.mailers.smtp.url'),
        ];
    }

    protected function getCurrentMailConfig(): array
    {
        return [
            'mailer' => config('mail.default'),
            'host' => config('mail.mailers.smtp.host'),
            'port' => config('mail.mailers.smtp.port'),
            'username' => config('mail.mailers.smtp.username'),
            'password' => config('mail.mailers.smtp.password'),
            'encryption' => config('mail.mailers.smtp.encryption'),
            'from_address' => config('mail.from.address'),
            'from_name' => config('mail.from.name'),
            'disable_delivery' => $this->toBoolean(config('mail.disable_delivery')),
            'smtp_url' => config('mail.mailers.smtp.url'),
        ];
    }

    protected function getGeneralSettings(): array
    {
        $storedGeneralSettings = Setting::forGroup('general');
        $storedLoggingSettings = Setting::forGroup('logging');

        $syslogHandlerConfig = config('logging.channels.syslog_server.handler_with', []);
        $defaultSyslogHost = is_array($syslogHandlerConfig)
            ? ($syslogHandlerConfig['host'] ?? '127.0.0.1')
            : '127.0.0.1';
        $defaultSyslogPort = is_array($syslogHandlerConfig)
            ? ($syslogHandlerConfig['port'] ?? 514)
            : 514;

        $defaultLogLevel = config('logging.channels.single.level', 'debug');

        return [
            'public_url' => $storedGeneralSettings['public_url'] ?? config('app.url'),
            'update_repository_url' => $storedGeneralSettings['update_repository_url']
                ?? config('self-update.repository_types.github.repository_url'),
            'update_release_channel' => ReleaseChannel::fromString(
                $storedGeneralSettings['update_release_channel'] ?? config('self-update.release_channel')
            )->value,
            'log_syslog_host' => $storedLoggingSettings['syslog_host'] ?? $defaultSyslogHost,
            'log_syslog_port' => $storedLoggingSettings['syslog_port'] ?? $defaultSyslogPort,
            'log_level' => $storedLoggingSettings['level'] ?? $defaultLogLevel,
            'log_disabled' => array_key_exists('disabled', $storedLoggingSettings)
                ? $this->toBoolean($storedLoggingSettings['disabled'])
                : false,
        ];
    }

    protected function getBrandingSettings(): array
    {
        $storedBrandingSettings = Setting::forGroup('branding');
        $resolvedBranding = config('branding', []);

        $logoPath = $storedBrandingSettings['logo_path'] ?? null;
        $logoDisk = $storedBrandingSettings['logo_disk'] ?? null;
        $logoUrl = data_get($resolvedBranding, 'logo_url');
        $logoMediaAssetId = $storedBrandingSettings['logo_media_asset_id']
            ?? data_get($resolvedBranding, 'logo_media_asset_id');
        $logoMediaVariantId = $storedBrandingSettings['logo_media_variant_id']
            ?? data_get($resolvedBranding, 'logo_media_variant_id');

        if (! $logoUrl) {
            if ($logoPath) {
                $diskName = $logoDisk ?: storage_public_disk();

                if ($diskName === storage_public_disk()) {
                    $logoUrl = storage_asset_url($logoPath);
                } else {
                    try {
                        $logoUrl = Storage::disk($diskName)->url($logoPath);
                    } catch (\Throwable $exception) {
                        $logoUrl = null;
                    }
                }
            }

            if (! $logoUrl) {
                $logoUrl = branding_logo_url();
            }
        }

        return [
            'logo_path' => $logoPath,
            'logo_disk' => $logoDisk,
            'logo_url' => $logoUrl,
            'logo_media_asset_id' => $logoMediaAssetId ? (int) $logoMediaAssetId : null,
            'logo_media_variant_id' => $logoMediaVariantId ? (int) $logoMediaVariantId : null,
            'logo_alt' => $storedBrandingSettings['logo_alt']
                ?? data_get($resolvedBranding, 'logo_alt', branding_logo_alt()),
            'primary_color' => $storedBrandingSettings['primary_color']
                ?? data_get($resolvedBranding, 'colors.primary', '#1F2937'),
            'secondary_color' => $storedBrandingSettings['secondary_color']
                ?? data_get($resolvedBranding, 'colors.secondary', '#111827'),
            'tertiary_color' => $storedBrandingSettings['tertiary_color']
                ?? data_get($resolvedBranding, 'colors.tertiary', '#374151'),
            'default_language' => $storedBrandingSettings['default_language']
                ?? data_get($resolvedBranding, 'default_language', config('app.locale', 'en')),
        ];
    }

    protected function getBrandingPalettes(): array
    {
        $palettes = [
            [
                'key' => 'monochrome',
                'label' => __('messages.branding_palette_monochrome'),
                'description' => __('messages.branding_palette_monochrome_description'),
                'colors' => [
                    'primary' => '#1F2937',
                    'secondary' => '#111827',
                    'tertiary' => '#374151',
                ],
            ],
            [
                'key' => 'red',
                'label' => __('messages.branding_palette_red'),
                'description' => __('messages.branding_palette_red_description'),
                'colors' => [
                    'primary' => '#B91C1C',
                    'secondary' => '#991B1B',
                    'tertiary' => '#DC2626',
                ],
            ],
            [
                'key' => 'orange',
                'label' => __('messages.branding_palette_orange'),
                'description' => __('messages.branding_palette_orange_description'),
                'colors' => [
                    'primary' => '#C2410C',
                    'secondary' => '#9A3412',
                    'tertiary' => '#BB4F06',
                ],
            ],
            [
                'key' => 'yellow',
                'label' => __('messages.branding_palette_yellow'),
                'description' => __('messages.branding_palette_yellow_description'),
                'colors' => [
                    'primary' => '#B45309',
                    'secondary' => '#92400E',
                    'tertiary' => '#A16207',
                ],
            ],
            [
                'key' => 'green',
                'label' => __('messages.branding_palette_green'),
                'description' => __('messages.branding_palette_green_description'),
                'colors' => [
                    'primary' => '#15803D',
                    'secondary' => '#166534',
                    'tertiary' => '#0B7A34',
                ],
            ],
            [
                'key' => 'blue',
                'label' => __('messages.branding_palette_blue'),
                'description' => __('messages.branding_palette_blue_description'),
                'colors' => [
                    'primary' => '#1D4ED8',
                    'secondary' => '#1E40AF',
                    'tertiary' => '#2563EB',
                ],
            ],
        ];

        foreach ($palettes as &$palette) {
            $primary = $palette['colors']['primary'];

            $palette['colors']['primary_rgb'] = ColorUtils::hexToRgbString($primary) ?? '31, 41, 55';
            $palette['colors']['primary_light'] = ColorUtils::mix($primary, '#FFFFFF', 0.55) ?? '#848991';
        }

        unset($palette);

        return array_values($palettes);
    }

    protected function getTermsSettings(): array
    {
        $storedGeneralSettings = Setting::forGroup('general');

        return [
            'terms_markdown' => $storedGeneralSettings['terms_markdown']
                ?? config('terms.default_markdown'),
        ];
    }

    protected function getSupportedLanguageOptions(): array
    {
        return collect(config('app.supported_languages', ['en']))
            ->filter()
            ->mapWithKeys(function ($code) {
                $code = is_string($code) ? strtolower(trim($code)) : null;

                if (! $code) {
                    return [];
                }

                $label = trans("messages.language_name_{$code}");

                if ($label === "messages.language_name_{$code}") {
                    $label = strtoupper($code);
                }

                return [$code => $label];
            })
            ->toArray();
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

    protected function applyMailConfig(array $settings, bool $force = false): void
    {
        MailConfigManager::apply($settings, $force);
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

    protected function sanitizeHost(string $host): string
    {
        return trim($host);
    }

    protected function normalizeRepositoryUrl(?string $url): ?string
    {
        if (! is_string($url)) {
            return null;
        }

        $trimmed = trim($url);

        if ($trimmed === '') {
            return null;
        }

        return $this->sanitizeUrl($trimmed);
    }

    protected function toBoolean(mixed $value): bool
    {
        if ($value === null) {
            return false;
        }

        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (bool) $value;
        }

        $normalized = strtolower(trim((string) $value));

        return in_array($normalized, ['1', 'true', 'yes', 'on'], true);
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
            'mail_disable_delivery' => ['nullable', 'boolean'],
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
            'disable_delivery' => $request->boolean('mail_disable_delivery') ? '1' : '0',
        ];
    }
}
