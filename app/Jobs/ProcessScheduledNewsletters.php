<?php

namespace App\Jobs;

use App\Models\Newsletter;
use App\Services\NewsletterService;
use Illuminate\Support\Facades\Log;

class ProcessScheduledNewsletters
{
    public function __invoke(): void
    {
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
                $service->send($newsletter);
            } catch (\Exception $e) {
                Log::error('Failed to process scheduled newsletter: '.$e->getMessage(), [
                    'newsletter_id' => $newsletter->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
