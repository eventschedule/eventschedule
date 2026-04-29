<?php

namespace App\Jobs;

use App\Services\SmsService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendQueuedSms implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 60;

    public string $to;

    public string $message;

    /**
     * Create a new job instance.
     */
    public function __construct(string $to, string $message)
    {
        $this->to = $to;
        $this->message = $message;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if (! SmsService::isConfigured()) {
            return;
        }

        if (! SmsService::sendSms($this->to, $this->message)) {
            throw new \RuntimeException('Failed to send SMS');
        }
    }
}
