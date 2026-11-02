<?php

namespace Tests\Feature;

use App\Jobs\SendQueuedSms;
use App\Services\SmsService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Twilio answering 400 with code 21408 ("Permission to send an SMS has not been enabled for the
 * region") is final for that number. The job threw on it, so the worker tried three times and
 * reported each attempt to Sentry. A 4xx refusal now ends the job; a 429, a 5xx or a timeout
 * still throws and is retried.
 */
class SendQueuedSmsRefusalTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.twilio.sid' => 'ACtest',
            'services.twilio.token' => 'test-token',
            'services.twilio.from' => '+15550000000',
        ]);
    }

    private function twilioAnswers(int $status, array $body = []): void
    {
        Http::fake(['api.twilio.com/*' => Http::response($body, $status)]);
    }

    public function test_a_region_refusal_ends_the_job_without_throwing(): void
    {
        $this->twilioAnswers(400, [
            'code' => 21408,
            'message' => 'Permission to send an SMS has not been enabled for the region indicated by the \'To\' number',
            'status' => 400,
        ]);

        (new SendQueuedSms('+923000000000', 'hello'))->handle();

        Http::assertSentCount(1);
        $this->assertSame(SmsService::REFUSED, SmsService::send('+923000000000', 'hello'));
    }

    public function test_a_server_error_still_throws_so_it_is_retried(): void
    {
        $this->twilioAnswers(503);

        $this->expectException(\RuntimeException::class);

        (new SendQueuedSms('+15551234567', 'hello'))->handle();
    }

    public function test_a_rate_limit_still_throws_so_it_is_retried(): void
    {
        $this->twilioAnswers(429, ['code' => 20429, 'status' => 429]);

        $this->expectException(\RuntimeException::class);

        (new SendQueuedSms('+15551234567', 'hello'))->handle();
    }

    public function test_a_sent_message_reports_sent(): void
    {
        $this->twilioAnswers(201, ['sid' => 'SM1']);

        $this->assertSame(SmsService::SENT, SmsService::send('+15551234567', 'hello'));
        $this->assertTrue(SmsService::sendSms('+15551234567', 'hello'));
    }
}
