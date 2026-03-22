<?php

namespace App\Jobs;

use App\Mail\FeedbackRequest;
use App\Models\Event;
use App\Models\Role;
use App\Models\Sale;
use App\Services\UsageTrackingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendFeedbackEmail implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 60;

    protected int $saleId;

    protected int $eventId;

    protected int $roleId;

    protected string $locale;

    public function __construct(int $saleId, int $eventId, int $roleId, string $locale)
    {
        $this->saleId = $saleId;
        $this->eventId = $eventId;
        $this->roleId = $roleId;
        $this->locale = $locale;
    }

    public function handle(): void
    {
        $sale = Sale::find($this->saleId);
        $event = Event::find($this->eventId);
        $role = Role::find($this->roleId);

        if (! $sale || ! $event || ! $role) {
            return;
        }

        if ($sale->is_deleted || $sale->status !== 'paid') {
            return;
        }

        $originalLocale = app()->getLocale();

        try {
            app()->setLocale($this->locale);

            $mailable = new FeedbackRequest($sale, $event, $role);

            if (config('app.hosted') && $role->hasEmailSettings()) {
                $this->configureRoleMailer($role);
                $mailerName = 'role_'.$role->id;
                Mail::mailer($mailerName)->to($sale->email)->send($mailable);
            } else {
                Mail::to($sale->email)->send($mailable);
            }

            UsageTrackingService::track(UsageTrackingService::EMAIL_TICKET, $role->id);

            $sale->feedback_sent_at = now();
            $sale->save();
        } finally {
            app()->setLocale($originalLocale);
        }
    }

    public function failed(\Throwable $exception): void
    {
        $sale = Sale::find($this->saleId);

        if ($sale) {
            $sale->feedback_sent_at = null;
            $sale->save();
        }

        Log::error('Failed to send feedback request: '.$exception->getMessage(), [
            'sale_id' => $this->saleId,
            'event_id' => $this->eventId,
        ]);
    }

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
