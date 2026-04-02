<?php

namespace App\Jobs;

use App\Models\Newsletter;
use App\Services\NewsletterService;
use Illuminate\Support\Facades\Log;

class ProcessScheduledNewsletters
{
    public function __invoke(): void
    {
        $this->recoverStuckNewsletters();

        $newsletters = Newsletter::where('status', 'scheduled')
            ->where('scheduled_at', '<=', now())
            ->get();

        if ($newsletters->isEmpty()) {
            return;
        }

        $service = app(NewsletterService::class);

        foreach ($newsletters as $newsletter) {
            try {
                Log::info('Processing scheduled newsletter', ['newsletter_id' => $newsletter->id]);
                $result = $service->send($newsletter);
                if ($result === false) {
                    Log::warning('Scheduled newsletter could not be sent', [
                        'newsletter_id' => $newsletter->id,
                        'role_id' => $newsletter->role_id,
                    ]);
                } elseif (is_array($result) && $result[0] === 'limit_exceeded') {
                    Log::warning('Scheduled newsletter exceeded email limit', [
                        'newsletter_id' => $newsletter->id,
                        'role_id' => $newsletter->role_id,
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Failed to process scheduled newsletter: '.$e->getMessage(), [
                    'newsletter_id' => $newsletter->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    protected function recoverStuckNewsletters(): void
    {
        $stuckNewsletters = Newsletter::where('status', 'sending')
            ->where('updated_at', '<=', now()->subHour())
            ->get();

        foreach ($stuckNewsletters as $stuck) {
            $totalRecipients = $stuck->recipients()->count();
            $pendingIds = $stuck->recipients()->where('status', 'pending')->pluck('id')->toArray();

            if ($totalRecipients === 0) {
                // Crashed before recipients were created - reset for retry
                Log::warning('Recovering stuck newsletter (no recipients created)', [
                    'newsletter_id' => $stuck->id,
                    'role_id' => $stuck->role_id,
                ]);
                // Use 'draft' for manual sends (no scheduled_at), 'scheduled' for scheduled ones
                $stuck->update([
                    'status' => $stuck->scheduled_at ? 'scheduled' : 'draft',
                    'send_token' => null,
                ]);
            } elseif (empty($pendingIds)) {
                // All recipients processed - mark as sent
                Log::warning('Recovering stuck newsletter (all recipients processed)', [
                    'newsletter_id' => $stuck->id,
                    'role_id' => $stuck->role_id,
                ]);
                $stuck->update([
                    'status' => 'sent',
                    'sent_at' => now(),
                    'sent_count' => $stuck->recipients()->where('status', 'sent')->count(),
                ]);
            } else {
                // Re-dispatch remaining pending recipients (avoids duplicate sends)
                Log::warning('Recovering stuck newsletter (re-dispatching pending recipients)', [
                    'newsletter_id' => $stuck->id,
                    'role_id' => $stuck->role_id,
                    'pending_count' => count($pendingIds),
                ]);
                $chunks = array_chunk($pendingIds, 50);
                foreach ($chunks as $index => $chunk) {
                    SendNewsletterBatch::dispatch($stuck->id, $chunk);
                }
                $stuck->touch(); // Reset updated_at so recovery doesn't re-trigger for 1 hour
            }
        }
    }
}
