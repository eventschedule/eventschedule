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

    public static function sendSms(string $to, string $message): bool
    {
        if (! self::isConfigured()) {
            Log::warning('Twilio SMS not configured, skipping SMS send');

            return false;
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
                return true;
            }

            Log::error('Twilio SMS failed', [
                'to' => self::maskPhone($to),
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('Twilio SMS exception', [
                'to' => self::maskPhone($to),
                'error' => $e->getMessage(),
            ]);

            return false;
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
