<?php

namespace App\Services;

use App\Mail\EmailSettingsFailedMail;
use App\Models\Role;
use App\Models\User;
use Illuminate\Contracts\Mail\Mailable;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Exception\ExceptionInterface as MailerExceptionInterface;
use Throwable;

class RoleMailerService
{
    /**
     * Send a Mailable for a role, using its custom SMTP credentials when
     * available and healthy, otherwise falling back to the default mailer.
     *
     * On a transport-layer failure of the role's custom SMTP, the role is
     * marked as failed (so subsequent calls within 24h skip the broken
     * credentials) and the same message is immediately retried via the
     * default mailer so the recipient still receives it.
     *
     * @param  Role  $role  The schedule whose custom SMTP we may use.
     * @param  string|array  $recipients  Recipient address(es) for ->to(...).
     * @param  Mailable  $mailable  The message to send.
     */
    public function sendForRole(Role $role, string|array $recipients, Mailable $mailable): void
    {
        if (! $this->shouldUseRoleMailer($role)) {
            Mail::to($recipients)->send($mailable);

            return;
        }

        $mailerName = $this->configureRoleMailer($role);

        try {
            Mail::mailer($mailerName)->to($recipients)->send($mailable);

            // Self-heal: if a flag was set (e.g. expired past 24h and we just
            // succeeded on retry), clear it so the AP banner disappears.
            if ($role->email_settings_failed_at !== null) {
                $role->clearEmailSettingsFailure();
            }
        } catch (MailerExceptionInterface $e) {
            $this->markFailed($role, $e);
            Mail::to($recipients)->send($mailable);
        }
    }

    /**
     * Determine whether the role's custom SMTP should be used right now.
     */
    public function shouldUseRoleMailer(Role $role): bool
    {
        if (! config('app.hosted')) {
            return false;
        }

        if (! $role->hasEmailSettings()) {
            return false;
        }

        return ! $role->isEmailSettingsFailureActive();
    }

    /**
     * Register a mailer config entry for the role and return its name.
     * Idempotent: safe to call multiple times for the same role within a
     * request.
     */
    public function configureRoleMailer(Role $role): string
    {
        $mailerName = 'role_'.$role->id;
        $emailSettings = $role->getEmailSettings();

        if (empty($emailSettings)) {
            return $mailerName;
        }

        Config::set("mail.mailers.{$mailerName}", [
            'transport' => 'smtp',
            'host' => $emailSettings['host'] ?? config('mail.mailers.smtp.host'),
            'port' => $emailSettings['port'] ?? config('mail.mailers.smtp.port'),
            'encryption' => $emailSettings['encryption'] ?? config('mail.mailers.smtp.encryption'),
            'username' => $emailSettings['username'] ?? null,
            'password' => $emailSettings['password'] ?? null,
            'timeout' => null,
            'local_domain' => config('mail.mailers.smtp.local_domain'),
        ]);

        return $mailerName;
    }

    /**
     * Persist the failure on the role and notify editors at most once per
     * 24-hour window. Never throws: every step is wrapped so a failure here
     * cannot replace the original transport exception in the caller.
     */
    public function markFailed(Role $role, Throwable $e): void
    {
        try {
            $role->markEmailSettingsFailed($e->getMessage());
        } catch (Throwable $inner) {
            report($inner);

            return;
        }

        try {
            $this->notifyEditors($role, $e);
        } catch (Throwable $inner) {
            report($inner);
        }
    }

    /**
     * Send the failure notification to schedule editors via the default
     * mailer, deduping on email_settings_failure_notified_at.
     */
    protected function notifyEditors(Role $role, Throwable $e): void
    {
        if ($role->email_settings_failure_notified_at !== null
            && $role->email_settings_failure_notified_at->gt(now()->subDay())) {
            return;
        }

        $editors = $role->belongsToMany(User::class)
            ->withPivot('level')
            ->whereIn('level', ['owner', 'admin'])
            ->get()
            ->filter(fn ($user) => filter_var($user->email ?? '', FILTER_VALIDATE_EMAIL));

        if ($editors->isEmpty()) {
            $role->markEmailSettingsFailureNotified();

            return;
        }

        $originalLocale = app()->getLocale();
        $errorMessage = mb_substr($e->getMessage(), 0, 1000);

        foreach ($editors as $editor) {
            try {
                $locale = $editor->language_code ?? $originalLocale;
                app()->setLocale($locale);

                Mail::to($editor->email)->send(
                    new EmailSettingsFailedMail($role, $editor, $errorMessage, $role->email_settings_failed_at)
                );
            } catch (Throwable $inner) {
                Log::warning('Failed to notify editor of broken role SMTP', [
                    'role_id' => $role->id,
                    'editor_id' => $editor->id,
                    'error' => $inner->getMessage(),
                ]);
            } finally {
                app()->setLocale($originalLocale);
            }
        }

        $role->markEmailSettingsFailureNotified();
    }
}
