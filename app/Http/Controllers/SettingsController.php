<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorizeAdmin($request->user());

        $storedMailSettings = Setting::forGroup('mail');

        $mailSettings = [
            'mailer' => $storedMailSettings['mailer'] ?? config('mail.default'),
            'host' => $storedMailSettings['host'] ?? config('mail.mailers.smtp.host'),
            'port' => $storedMailSettings['port'] ?? config('mail.mailers.smtp.port'),
            'username' => $storedMailSettings['username'] ?? config('mail.mailers.smtp.username'),
            'password' => $storedMailSettings['password'] ?? config('mail.mailers.smtp.password'),
            'encryption' => $storedMailSettings['encryption'] ?? config('mail.mailers.smtp.encryption'),
            'from_address' => $storedMailSettings['from_address'] ?? config('mail.from.address'),
            'from_name' => $storedMailSettings['from_name'] ?? config('mail.from.name'),
        ];

        return view('settings.index', [
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
            'buildNumber' => config('app.build_number'),
        ]);
    }

    public function updateMail(Request $request): RedirectResponse
    {
        $this->authorizeAdmin($request->user());

        $validated = $request->validate([
            'mail_mailer' => ['required', Rule::in(['smtp', 'log'])],
            'mail_host' => [Rule::requiredIf($request->mail_mailer === 'smtp'), 'nullable', 'string', 'max:255'],
            'mail_port' => [Rule::requiredIf($request->mail_mailer === 'smtp'), 'nullable', 'integer', 'between:1,65535'],
            'mail_username' => ['nullable', 'string', 'max:255'],
            'mail_password' => ['nullable', 'string', 'max:255'],
            'mail_encryption' => ['nullable', Rule::in(['tls', 'ssl', ''])],
            'mail_from_address' => ['required', 'email', 'max:255'],
            'mail_from_name' => ['required', 'string', 'max:255'],
        ]);

        $currentSettings = Setting::forGroup('mail');

        $password = $request->filled('mail_password')
            ? trim($validated['mail_password'])
            : ($currentSettings['password'] ?? config('mail.mailers.smtp.password'));

        $mailSettings = [
            'mailer' => $validated['mail_mailer'],
            'host' => $this->nullableTrim($validated['mail_host'] ?? null),
            'port' => isset($validated['mail_port']) ? (string) $validated['mail_port'] : null,
            'username' => $this->nullableTrim($validated['mail_username'] ?? null),
            'password' => $this->nullableTrim($password),
            'encryption' => $this->nullableTrim($validated['mail_encryption'] ?? null),
            'from_address' => trim($validated['mail_from_address']),
            'from_name' => trim($validated['mail_from_name']),
        ];

        Setting::setGroup('mail', $mailSettings);

        $this->applyMailConfig($mailSettings);

        return back()->with('status', 'mail-settings-updated');
    }

    protected function authorizeAdmin($user): void
    {
        abort_unless($user && $user->isAdmin(), 403);
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
}
