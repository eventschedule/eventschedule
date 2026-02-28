<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    public static function isConfigured(): bool
    {
        return SmsService::isConfigured();
    }

    public static function sendMessage(string $to, string $message): bool
    {
        if (! self::isConfigured()) {
            Log::warning('Twilio not configured, skipping WhatsApp send');

            return false;
        }

        try {
            $sid = config('services.twilio.sid');
            $token = config('services.twilio.token');
            $from = config('services.twilio.from');

            $response = Http::withBasicAuth($sid, $token)
                ->asForm()
                ->post("https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json", [
                    'From' => 'whatsapp:'.$from,
                    'To' => 'whatsapp:'.$to,
                    'Body' => $message,
                ]);

            if ($response->successful()) {
                return true;
            }

            Log::error('Twilio WhatsApp failed', [
                'to' => $to,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('Twilio WhatsApp exception', [
                'to' => $to,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public static function downloadMedia(string $mediaUrl): ?string
    {
        try {
            $sid = config('services.twilio.sid');
            $token = config('services.twilio.token');

            $response = Http::withBasicAuth($sid, $token)
                ->get($mediaUrl);

            if ($response->successful()) {
                return $response->body();
            }

            Log::error('Twilio media download failed', [
                'url' => $mediaUrl,
                'status' => $response->status(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Twilio media download exception', [
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    public static function verifySignature(Request $request): bool
    {
        $signature = $request->header('X-Twilio-Signature');
        if (! $signature) {
            return false;
        }

        $token = config('services.twilio.token');
        if (! $token) {
            return false;
        }

        $url = $request->fullUrl();

        // Build data string: URL + sorted POST params concatenated
        $data = $url;
        $params = $request->post() ?? [];
        ksort($params);
        foreach ($params as $key => $value) {
            $data .= $key.$value;
        }

        $expected = base64_encode(hash_hmac('sha1', $data, $token, true));

        return hash_equals($expected, $signature);
    }
}
