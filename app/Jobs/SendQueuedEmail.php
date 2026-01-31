<?php

namespace App\Jobs;

use App\Models\Role;
use Illuminate\Contracts\Mail\Mailable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

class SendQueuedEmail implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 60;

    protected Mailable $mailable;

    protected string $recipient;

    protected ?int $roleId;

    protected ?string $locale;

    /**
     * Create a new job instance.
     */
    public function __construct(Mailable $mailable, string $recipient, ?int $roleId = null, ?string $locale = null)
    {
        $this->mailable = $mailable;
        $this->recipient = $recipient;
        $this->roleId = $roleId;
        $this->locale = $locale;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $originalLocale = app()->getLocale();

        try {
            if ($this->locale) {
                app()->setLocale($this->locale);
            }

            if ($this->roleId && config('app.hosted')) {
                $role = Role::find($this->roleId);

                if ($role && $role->hasEmailSettings()) {
                    $this->configureRoleMailer($role);
                    $mailerName = 'role_'.$role->id;
                    Mail::mailer($mailerName)->to($this->recipient)->send($this->mailable);

                    return;
                }
            }

            Mail::to($this->recipient)->send($this->mailable);
        } finally {
            app()->setLocale($originalLocale);
        }
    }

    /**
     * Configure mailer with role-specific SMTP settings.
     */
    protected function configureRoleMailer(Role $role): void
    {
        $emailSettings = $role->getEmailSettings();

        if (empty($emailSettings)) {
            return;
        }

        $mailerName = 'role_'.$role->id;

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
    }
}
