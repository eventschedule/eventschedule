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
     * Twilio turned this message down for good because of its RECIPIENT (REFUSING_CODES). Sending it
     * again gets the same answer.
     */
    public const REFUSED = 'refused';

    /**
     * Anything else: a 429, a 5xx, a timeout, SMS not configured, and every other 4xx. A 401 (a
     * rotated token), a 404 (a wrong account SID) or a From number that cannot send is wrong for
     * EVERY message on the install, so it must throw and reach Sentry, never end quietly as one
     * number's refusal.
     */
    public const FAILED = 'failed';

    /**
     * Twilio's codes that are about the number a message is for: 21211 not a valid number, 21265 a
     * short code, 21408 a region the account may not send to, 21610 a recipient who replied STOP,
     * 21612 a number Twilio cannot reach, 21614 not a mobile number.
     */
    private const REFUSING_CODES = [21211, 21265, 21408, 21610, 21612, 21614];

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

            $refused = $response->status() === 400
                && in_array((int) $response->json('code'), self::REFUSING_CODES, true);

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
