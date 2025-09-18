<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Support\MailTemplateManager;
use Codedge\Updater\UpdaterManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

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
            ], 422);
        }

        $validated = $this->validateMailSettings($request);

        $originalSettings = $this->getMailSettings();
        $testMailSettings = $this->buildMailSettings($request, $validated);

        $this->applyMailConfig($testMailSettings);

        try {
            Mail::raw(__('messages.test_email_body'), function ($message) use ($user) {
                $message->to($user->email)->subject(__('messages.test_email_subject'));
            });

            return response()->json([
                'status' => 'success',
                'message' => __('messages.test_email_sent'),
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'status' => 'error',
                'message' => __('messages.test_email_failed'),
                'error' => $exception->getMessage(),
            ], 500);
        } finally {
            $this->applyMailConfig($originalSettings);
        }
    }

    public function updateGeneral(Request $request): RedirectResponse
    {
        $this->authorizeAdmin($request->user());

        $validated = $request->validate([
            'public_url' => ['required', 'string', 'max:255', 'url'],
        ]);

        $publicUrl = $this->sanitizeUrl($validated['public_url']);

        Setting::setGroup('general', [
            'public_url' => $publicUrl,
        ]);

        $this->applyGeneralConfig($publicUrl);

        return redirect()->route('settings.general')->with('status', 'general-settings-updated');
    }

    public function updateMailTemplates(Request $request, MailTemplateManager $mailTemplates): RedirectResponse
    {
        $this->authorizeAdmin($request->user());

        $templates = $mailTemplates->all();

        $rules = [];

        foreach ($templates as $template) {
            $base = 'mail_templates.' . $template['key'];

            if (isset($template['subject'])) {
                $rules[$base . '.subject'] = ['required', 'string', 'max:255'];
            }

            if (!empty($template['has_subject_curated']) && isset($template['subject_curated'])) {
                $rules[$base . '.subject_curated'] = ['required', 'string', 'max:255'];
            }

            if (isset($template['body'])) {
                $rules[$base . '.body'] = ['required', 'string'];
            }

            if (!empty($template['has_body_curated']) && isset($template['body_curated'])) {
                $rules[$base . '.body_curated'] = ['required', 'string'];
            }
        }

        $validated = $request->validate($rules);

        $mailTemplates->updateFromArray($validated['mail_templates'] ?? []);

        return redirect()->route('settings.email_templates')->with('status', 'mail-templates-updated');
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
