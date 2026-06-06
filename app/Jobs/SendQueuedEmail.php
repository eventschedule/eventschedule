<?php

namespace App\Jobs;

use App\Models\Role;
use App\Services\RoleMailerService;
use App\Services\UsageTrackingService;
use Illuminate\Contracts\Mail\Mailable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
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
     * Execute the job. Role-mailer failures are caught and recorded inside
     * RoleMailerService: when a schedule's custom SMTP is failing the message
     * is intentionally not sent (sendForRole returns false) rather than
     * falling back to the platform mailer, so we only track usage when the
     * message was actually sent. A bare exception escaping this method
     * therefore indicates the platform mailer itself failed.
     */
    public function handle(): void
    {
        $originalLocale = app()->getLocale();

        try {
            if ($this->locale) {
                app()->setLocale($this->locale);
            }

            $role = $this->roleId ? Role::find($this->roleId) : null;

            if ($role) {
                if (app(RoleMailerService::class)->sendForRole($role, $this->recipient, $this->mailable)) {
                    UsageTrackingService::track(UsageTrackingService::EMAIL_TICKET, $role->id);
                }

                return;
            }

            Mail::to($this->recipient)->send($this->mailable);
            UsageTrackingService::track(UsageTrackingService::EMAIL_TICKET, $this->roleId ?? 0);
        } finally {
            app()->setLocale($originalLocale);
        }
    }
}
