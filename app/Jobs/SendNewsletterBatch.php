<?php

namespace App\Jobs;

use App\Models\Newsletter;
use App\Models\NewsletterRecipient;
use App\Services\NewsletterService;
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
            }

            $recipients = NewsletterRecipient::whereIn('id', $this->recipientIds)
                ->where('status', 'pending')
                ->get();

            foreach ($recipients as $recipient) {
                try {
                    $service->sendToRecipient($newsletter, $recipient);
                } catch (\Exception $e) {
                    Log::error('Newsletter batch send error: '.$e->getMessage(), [
                        'newsletter_id' => $this->newsletterId,
                        'recipient_id' => $recipient->id,
                    ]);
                }
            }
        } finally {
            app()->setLocale($originalLocale);
        }

        // Update denormalized counts
        $this->updateNewsletterCounts($newsletter);
    }

    protected function updateNewsletterCounts(Newsletter $newsletter): void
    {
        $newsletter->update([
            'sent_count' => $newsletter->recipients()->where('status', 'sent')->count(),
            'open_count' => $newsletter->recipients()->where('open_count', '>', 0)->count(),
            'click_count' => $newsletter->recipients()->where('click_count', '>', 0)->count(),
        ]);

        // Check if all recipients have been processed
        $pendingCount = $newsletter->recipients()->where('status', 'pending')->count();
        if ($pendingCount === 0 && $newsletter->status === 'sending') {
            $newsletter->update([
                'status' => 'sent',
                'sent_at' => now(),
            ]);
        }
    }
}
