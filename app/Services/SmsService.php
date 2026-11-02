<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    public static function isConfigured(): bool
    {
        return ! empty(config('services.twilio.sid'))
            && ! empty(config('services.twilio.token'))
            && ! empty(config('services.twilio.from'));
    }

    /** Twilio took the message. */
    public const SENT = 'sent';

    /**
     * Twilio turned this message down for good: a 4xx other than 429 (21408 a region the account
     * may not send to, 21211 a number that is not one, 21610 a recipient who replied STOP).
     * Sending it again gets the same answer.
     */
    public const REFUSED = 'refused';

    /** It may go through later: a 429, a 5xx, a timeout, or SMS is not configured. */
    public const FAILED = 'failed';

    public static function sendSms(string $to, string $message): bool
    {
        return self::send($to, $message) === self::SENT;
    }

    /**
     * @return self::SENT|self::REFUSED|self::FAILED
     */
    public static function send(string $to, string $message): string
    {
        if (! self::isConfigured()) {
            Log::warning('Twilio SMS not configured, skipping SMS send');

            return self::FAILED;
        }

        try {
            $sid = config('services.twilio.sid');
            $token = config('services.twilio.token');
            $from = config('services.twilio.from');

            $response = Http::withBasicAuth($sid, $token)
                ->asForm()
                ->post("https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json", [
                    'From' => $from,
                    'To' => $to,
                    'Body' => $message,
                ]);

            if ($response->successful()) {
                return self::SENT;
            }

            $refused = $response->clientError() && $response->status() !== 429;

            Log::log($refused ? 'warning' : 'error', $refused ? 'Twilio SMS refused' : 'Twilio SMS failed', [
                'to' => self::maskPhone($to),
                'status' => $response->status(),
                'code' => $response->json('code'),
                'body' => $response->body(),
            ]);

            return $refused ? self::REFUSED : self::FAILED;
        } catch (\Exception $e) {
            Log::error('Twilio SMS exception', [
                'to' => self::maskPhone($to),
                'error' => $e->getMessage(),
            ]);

            return self::FAILED;
        }
    }

    /**
     * The last three digits only. Log lines become Sentry breadcrumbs, and a full number is
     * personal data that nobody debugging a Twilio failure needs.
     */
    private static function maskPhone(?string $phone): string
    {
        $digits = preg_replace('/\D/', '', (string) $phone);

        return strlen($digits) > 3 ? str_repeat('*', strlen($digits) - 3).substr($digits, -3) : '***';
    }
}
