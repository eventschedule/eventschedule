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

        $result = SmsService::send($this->to, $this->message);

        // A refusal (a region the Twilio account may not send to, a number that is not one, a
        // recipient who replied STOP) is Twilio's final answer for this number: thrown, it was
        // tried three times and reported to Sentry each time for an SMS that could never go.
        // SmsService has logged it with Twilio's code; the job is done.
        if ($result === SmsService::FAILED) {
            throw new \RuntimeException('Failed to send SMS');
        }
    }
}
