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

                // A repeating newsletter sends a copy of itself and moves on to its next run.
                if ($newsletter->isRepeating()) {
                    $service->runRepeat($newsletter);

                    continue;
                }

                $result = $service->send($newsletter);
                if ($result === false) {
                    Log::warning('Scheduled newsletter could not be sent', [
                        'newsletter_id' => $newsletter->id,
                        'role_id' => $newsletter->role_id,
                    ]);
                } elseif (is_array($result) && $result[0] === 'no_recipients') {
                    Log::warning('Scheduled newsletter has no recipients', [
                        'newsletter_id' => $newsletter->id,
                        'role_id' => $newsletter->role_id,
                    ]);
                } elseif (is_array($result) && $result[0] === 'limit_exceeded') {
                    Log::warning('Scheduled newsletter exceeded email limit', [
                        'newsletter_id' => $newsletter->id,
                        'role_id' => $newsletter->role_id,
                    ]);
                } elseif (is_array($result) && $result[0] === 'requires_verification') {
                    // send() now drops the row to 'draft' and clears scheduled_at, so it leaves
                    // this queue for good rather than being re-picked and re-refused every tick.
                    // This arm is the operator's record of why it moved.
                    Log::warning('Scheduled newsletter blocked: schedule cannot send audience mail', [
                        'newsletter_id' => $newsletter->id,
                        'role_id' => $newsletter->role_id,
                        'recipients' => $result[1] ?? null,
                    ]);
                }
            } catch (\Throwable $e) {
                // \Throwable so one newsletter cannot abort the loop - and, more importantly, so an
                // \Error from NewsletterService reaches a handler at all rather than unwinding
                // past both cron rails with a transaction still open.
                Log::error('Failed to process scheduled newsletter: '.$e->getMessage(), [
                    'newsletter_id' => $newsletter->id,
                    'error' => $e->getMessage(),
                ]);

                // report() as well as Log: this runs on both cron rails every minute, and at
                // Log::error alone a newsletter failing in a loop never reached the issue tracker.
                report($e);
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

            if ($totalRecipients === 0 && $stuck->repeat_parent_id) {
                // A repeating newsletter's copy. It has no scheduled_at, so the reset below would
                // leave a draft nobody wrote; its run is reported as skipped instead.
                Log::warning('Recovering stuck repeat copy (no recipients created)', [
                    'newsletter_id' => $stuck->id,
                    'role_id' => $stuck->role_id,
                ]);
                app(NewsletterService::class)->abandonStuckCopy($stuck);
            } elseif ($totalRecipients === 0) {
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
                // Its next send reads its events as of then, not as of this crashed one.
                $stuck->forceFill(['events_as_of' => null])->save();
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
                    SendNewsletterBatch::dispatch($stuck->id, $chunk)
                        ->delay(now()->addSeconds($index * 15));
                }
                $stuck->touch(); // Reset updated_at so recovery doesn't re-trigger for 1 hour
            }
        }
    }
}
