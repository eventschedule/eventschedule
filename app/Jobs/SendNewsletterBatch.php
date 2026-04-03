<?php

namespace App\Jobs;

use App\Models\Newsletter;
use App\Models\NewsletterRecipient;
use App\Services\NewsletterService;
use App\Services\UsageTrackingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendNewsletterBatch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    public $backoff = 60;

    public $timeout = 300;

    protected $newsletterId;

    protected $recipientIds;

    public function __construct(int $newsletterId, array $recipientIds)
    {
        $this->newsletterId = $newsletterId;
        $this->recipientIds = $recipientIds;
    }

    public function handle(NewsletterService $service): void
    {
        $newsletter = Newsletter::with('role')->find($this->newsletterId);
        if (! $newsletter || $newsletter->status === 'cancelled') {
            return;
        }

        $originalLocale = app()->getLocale();

        try {
            $role = $newsletter->role;
            if ($role && is_valid_language_code($role->language_code)) {
                app()->setLocale($role->language_code);
            } elseif (! $role) {
                app()->setLocale('en');
            }

            $recipients = NewsletterRecipient::whereIn('id', $this->recipientIds)
                ->where('status', 'pending')
                ->get();

            foreach ($recipients as $recipient) {
                try {
                    $service->sendToRecipient($newsletter, $recipient);
                    if ($newsletter->role_id) {
                        UsageTrackingService::track(UsageTrackingService::EMAIL_NEWSLETTER, $newsletter->role_id);
                    }
                } catch (\Exception $e) {
                    Log::error('Newsletter batch send error: '.$e->getMessage(), [
                        'newsletter_id' => $this->newsletterId,
                        'recipient_id' => $recipient->id,
                    ]);
                    if ($recipient->status === 'pending') {
                        $recipient->update([
                            'status' => 'failed',
                            'error_message' => substr($e->getMessage(), 0, 500),
                        ]);
                    }
                }
                usleep(200000); // 200ms throttle between sends
            }
        } finally {
            app()->setLocale($originalLocale);
        }

        // Update denormalized counts
        $this->updateNewsletterCounts($newsletter);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('SendNewsletterBatch permanently failed', [
            'newsletter_id' => $this->newsletterId,
            'recipient_ids' => $this->recipientIds,
            'error' => $exception->getMessage(),
        ]);

        NewsletterRecipient::whereIn('id', $this->recipientIds)
            ->where('status', 'pending')
            ->update([
                'status' => 'failed',
                'error_message' => substr($exception->getMessage(), 0, 500),
            ]);

        $newsletter = Newsletter::find($this->newsletterId);
        if ($newsletter) {
            $this->updateNewsletterCounts($newsletter);
        }
    }

    protected function updateNewsletterCounts(Newsletter $newsletter): void
    {
        $pendingCount = $newsletter->recipients()->where('status', 'pending')->count();
        $sentCount = $newsletter->recipients()->where('status', 'sent')->count();

        if ($pendingCount === 0) {
            // Final update: set sent_count + status in one query to avoid race
            Newsletter::where('id', $newsletter->id)
                ->where('status', 'sending')
                ->update(['status' => 'sent', 'sent_at' => now(), 'sent_count' => $sentCount]);
        } else {
            $newsletter->update(['sent_count' => $sentCount]);
        }
    }
}
