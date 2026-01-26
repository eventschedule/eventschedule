<?php

namespace App\Utils;

class TurnstileUtils
{
    /**
     * Check if Turnstile is enabled (both site_key and secret_key are set)
     */
    public static function isEnabled(): bool
    {
        $siteKey = config('services.turnstile.site_key');
        $secretKey = config('services.turnstile.secret_key');

        return ! empty($siteKey) && ! empty($secretKey);
    }

    /**
     * Get the site key for frontend use
     */
    public static function getSiteKey(): ?string
    {
        if (! self::isEnabled()) {
            return null;
        }

        return config('services.turnstile.site_key');
    }

    /**
     * Verify a Turnstile token with Cloudflare API
     *
     * @param  string|null  $token  The token from cf-turnstile-response
     * @param  string|null  $remoteIp  The user's IP address
     * @return bool True if verification passed, false otherwise
     */
    public static function verify(?string $token, ?string $remoteIp = null): bool
    {
        // If Turnstile is not enabled, allow the request through
        if (! self::isEnabled()) {
            return true;
        }

        // Empty token is invalid
        if (empty($token)) {
            return false;
        }

        $secretKey = config('services.turnstile.secret_key');
        $url = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

        $data = [
            'secret' => $secretKey,
            'response' => $token,
        ];

        if ($remoteIp) {
            $data['remoteip'] = $remoteIp;
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($data),
            CURLOPT_TIMEOUT => 10,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        // Handle cURL errors
        if ($response === false || $curlError) {
            \Log::warning('Turnstile API connection error', [
                'error' => $curlError,
                'http_code' => $httpCode,
            ]);

            return false;
        }

        // Parse response
        $result = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            \Log::warning('Turnstile API invalid JSON response', [
                'http_code' => $httpCode,
            ]);

            return false;
        }

        // Check if verification succeeded
        if (isset($result['success']) && $result['success'] === true) {
            return true;
        }

        // Log error codes for debugging (without sensitive data)
        if (isset($result['error-codes']) && ! empty($result['error-codes'])) {
            \Log::info('Turnstile verification failed', [
                'error_codes' => $result['error-codes'],
            ]);
        }

        return false;
    }
}
