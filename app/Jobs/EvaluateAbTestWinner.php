<?php

namespace App\Jobs;

use App\Models\NewsletterAbTest;
use App\Services\NewsletterService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class EvaluateAbTestWinner implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    public $backoff = 300;

    protected $abTestId;

    public function __construct(int $abTestId)
    {
        $this->abTestId = $abTestId;
    }

    public function handle(NewsletterService $service): void
    {
        $abTest = NewsletterAbTest::find($this->abTestId);

        if (! $abTest || $abTest->status === 'completed') {
            return;
        }

        try {
            $service->selectAbTestWinner($abTest);
        } catch (\Exception $e) {
            Log::error('Failed to evaluate A/B test winner: '.$e->getMessage(), [
                'ab_test_id' => $this->abTestId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
