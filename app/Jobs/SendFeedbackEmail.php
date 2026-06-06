<?php

namespace App\Jobs;

use App\Mail\FeedbackRequest;
use App\Models\Event;
use App\Models\Role;
use App\Models\Sale;
use App\Services\RoleMailerService;
use App\Services\UsageTrackingService;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendFeedbackEmail implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 60;

    public int $uniqueFor = 300;

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

    public function uniqueId(): string
    {
        return 'feedback-'.$this->saleId;
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
            $sale->feedback_sent_at = null;
            $sale->save();

            return;
        }

        $originalLocale = app()->getLocale();

        try {
            app()->setLocale($this->locale);

            $mailable = new FeedbackRequest($sale, $event, $role);

            if (app(RoleMailerService::class)->sendForRole($role, $sale->email, $mailable)) {
                UsageTrackingService::track(UsageTrackingService::EMAIL_TICKET, $role->id);
            }
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
}
