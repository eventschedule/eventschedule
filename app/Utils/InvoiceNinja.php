<?php

namespace App\Utils;

use App\Exceptions\InvoiceNinjaException;
use App\Services\UsageTrackingService;

class InvoiceNinja
{
    /** Seconds to wait for the connection, and for the request overall. */
    private const CONNECT_TIMEOUT = 10;

    private const TIMEOUT = 30;

    /** curl error codes that mean we never reached the host. */
    private const CURL_UNREACHABLE_ERRORS = [6, 7, 28];

    /** curl error codes that mean the TLS handshake or certificate was rejected. */
    private const CURL_TLS_ERRORS = [35, 51, 58, 59, 60, 77, 83];

    private $apiKey;

    private $apiUrl;

    public function __construct($apiKey, $apiUrl)
    {
        // Trim both: a token pasted with a trailing newline makes curl reject the whole
        // header set, which surfaced as an unexplained connection failure.
        $this->apiKey = is_string($apiKey) ? trim($apiKey) : $apiKey;
        $this->apiUrl = is_string($apiUrl) ? trim($apiUrl) : $apiUrl;
    }

    public function getCompany()
    {
        $company = $this->sendRequest('companies/current?include=webhooks', 'POST');

        return $company;
    }

    public function createWebhook($url)
    {
        $webhook = $this->sendRequest('webhooks', 'POST', [
            'rest_method' => 'post',
            'format' => 'json',
            'event_id' => '4',
            'target_url' => $url,
        ]);

        return $webhook;
    }

    public function deleteWebhook($id)
    {
        $this->sendRequest('webhooks/bulk', 'POST', [
            'ids' => [$id],
            'action' => 'delete',
        ]);
    }

    public function createClient($name, $email, $currencyCode)
    {
        $parts = explode(' ', $name);
        $lastName = array_pop($parts);
        $firstName = implode(' ', $parts);

        $client = $this->sendRequest('clients', 'POST', [
            'currency_code' => $currencyCode,
            'contacts' => [
                [
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'email' => $email,
                ],
            ],
        ]);

        UsageTrackingService::track(UsageTrackingService::INVOICENINJA_CLIENT);

        return $client;
    }

    public function findClient($email, $currencyCode)
    {
        $clients = $this->sendRequest('clients?is_deleted=false&email='.urlencode($email), 'GET');

        if (! is_array($clients)) {
            return null;
        }

        if (count($clients) > 0) {
            foreach ($clients as $client) {
                if ($client['settings']['currency_id'] == InvoiceNinja::convertCodeToId($currencyCode)) {
                    return $client;
                }
            }
        }

        return null;
    }

    public function createProduct($productKey, $notes, $price)
    {
        $product = $this->sendRequest('products', 'POST', [
            'product_key' => $productKey,
            'notes' => $notes,
            'price' => $price,
        ]);

        return $product;
    }

    public function createSubscription($name, $optionalProductIds, $webhookConfig, $steps = 'auth.login-or-register,cart', $promoCode = null, $promoDiscount = 0, $isAmountDiscount = true)
    {
        $data = [
            'name' => $name,
            'steps' => $steps,
            'optional_product_ids' => implode(',', $optionalProductIds),
            'allow_query_overrides' => true,
            'webhook_configuration' => $webhookConfig,
        ];

        if ($promoCode) {
            $data['promo_code'] = $promoCode;
            $data['promo_discount'] = $promoDiscount;
            $data['is_amount_discount'] = $isAmountDiscount;
        }

        $subscription = $this->sendRequest('subscriptions', 'POST', $data);

        UsageTrackingService::track(UsageTrackingService::INVOICENINJA_PAYMENT_LINK);

        return $subscription;
    }

    public function deleteSubscription($id)
    {
        $this->sendRequest('subscriptions/bulk', 'POST', [
            'ids' => [$id],
            'action' => 'delete',
        ]);
    }

    public function createInvoice($clientId, $lineItems, $qrCodeUrl, $sendEmail = false, $publicNotes = null)
    {
        $url = 'invoices';

        if ($sendEmail) {
            $url .= '?send_email=true';
        } else {
            $url .= '?mark_sent=true';
        }

        if ($publicNotes === null) {
            $publicNotes = __('messages.qr_code_is_your_ticket').'<br><br><img src="'.$qrCodeUrl.'" />';
        }

        $invoice = $this->sendRequest($url, 'POST', [
            'client_id' => $clientId,
            'public_notes' => $publicNotes,
            'line_items' => $lineItems,
        ]);

        UsageTrackingService::track(UsageTrackingService::INVOICENINJA_INVOICE);

        return $invoice;
    }

    /**
     * Build the API base URL from the user-supplied installation URL.
     *
     * This previously used rtrim($url, 'api/v1'), whose second argument is a character
     * list, not a suffix: any host ending in a, p, i, v, 1 or / was silently truncated,
     * so "https://books.example.ai" became "https://books.example." and every request
     * failed. Strip the suffix explicitly instead, and preserve any sub-path the install
     * is mounted under. See GitHub issue #110.
     *
     * @throws \App\Exceptions\InvoiceNinjaException when the URL is unusable
     */
    public static function normalizeApiUrl($apiUrl): string
    {
        $url = is_string($apiUrl) ? trim($apiUrl) : '';

        if ($url === '') {
            return 'https://invoicing.co/api/v1/';
        }

        // Require "://" rather than a bare colon, so a "host:8080" form is not mistaken
        // for a scheme. A bare host parses with no scheme key, so supply one.
        if (! preg_match('#^[a-z][a-z0-9+.-]*://#i', $url)) {
            $url = 'https://'.$url;
        }

        $parts = parse_url($url);

        if (! $parts || empty($parts['scheme']) || empty($parts['host'])) {
            throw new InvoiceNinjaException(__('messages.invalid_url'));
        }

        $scheme = strtolower($parts['scheme']);

        if (! in_array($scheme, ['http', 'https'], true)) {
            throw new InvoiceNinjaException(__('messages.invalid_url'));
        }

        $base = $scheme.'://'.strtolower($parts['host']);

        if (! empty($parts['port'])) {
            $base .= ':'.$parts['port'];
        }

        // Keep any sub-path (installs mounted at /ninja), drop a trailing /api/v1.
        $path = rtrim($parts['path'] ?? '', '/');
        $path = preg_replace('#/api/v1$#i', '', $path);
        $path = rtrim($path, '/');

        return $base.$path.'/api/v1/';
    }

    public function sendRequest($route, $method = 'GET', $data = false)
    {
        if (! $this->apiKey) {
            return null;
        }

        $url = self::normalizeApiUrl($this->apiUrl).ltrim($route, '/');

        $options = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => self::CONNECT_TIMEOUT,
            CURLOPT_TIMEOUT => self::TIMEOUT,
            // PHP's curl sends no User-Agent at all, and Cloudflare Bot Fight Mode (plus
            // most managed WAF rulesets) answer an empty-UA request with an HTML 403 that
            // is indistinguishable here from a bad token. This is the difference between
            // our request and the plain "curl" that works from the same server in #110.
            CURLOPT_USERAGENT => 'EventSchedule/1.0',
            CURLOPT_ENCODING => '',
            CURLOPT_FOLLOWLOCATION => false,
            // The API URL is user supplied, so never let it reach file://, gopher:// or
            // dict://. This limit applies on every deployment, hosted and selfhosted.
            CURLOPT_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
            CURLOPT_REDIR_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
            CURLOPT_HTTPHEADER => [
                'X-API-TOKEN: '.$this->apiKey,
                'X-CLIENT-PLATFORM: '.'Event Schedule',
                'Content-Type: application/json',
                'Accept: application/json',
            ],
        ];

        if ($method == 'POST') {
            $options[CURLOPT_POST] = true;
            // json_encode(false) sent the literal body "false" on the no-payload calls.
            // Send an empty body instead, matching a plain "curl -X POST" with no -d.
            $options[CURLOPT_POSTFIELDS] = ($data === false || $data === null) ? '' : json_encode($data);
        }

        // On the hosted platform the API URL is attacker controlled and the server sits
        // next to cloud metadata endpoints, so validate and pin DNS. Selfhosted installs
        // legitimately point at LAN addresses, Docker service names and loopback, so only
        // the protocol limits above apply there. Checked per request, not at save time,
        // so it stays TOCTOU-safe against DNS rebinding.
        if (config('app.hosted') && ! empty($this->apiUrl)) {
            $pinned = UrlUtils::safePinnedCurlOptions($url);

            if ($pinned === null) {
                throw new InvoiceNinjaException(
                    __('messages.invoiceninja_url_not_allowed'),
                    'invoiceninja_url_not_allowed'
                );
            }

            // Union, never array_merge: CURLOPT_* are integer keys and array_merge would
            // renumber them to 0, 1, 2..., handing curl_setopt_array() garbage.
            $options += $pinned;
        }

        $response = curl_init();
        curl_setopt_array($response, $options);

        // Read everything we need before curl_close().
        $rawResult = curl_exec($response);
        $httpCode = (int) curl_getinfo($response, CURLINFO_HTTP_CODE);
        $redirectUrl = curl_getinfo($response, CURLINFO_REDIRECT_URL);
        $curlErrno = curl_errno($response);
        $curlError = $curlErrno ? curl_error($response) : null;
        curl_close($response);

        if ($curlErrno) {
            $message = 'Invoice Ninja API connection failed: '.$curlError.' (curl error '.$curlErrno.')';
            \Log::error($message, [
                'url' => $url,
                'route' => $route,
            ]);
            throw new InvoiceNinjaException($message, self::curlReasonKey($curlErrno));
        }

        if (in_array($httpCode, [301, 302, 303, 307, 308])) {
            // Deliberately not followed. curl downgrades POST to GET on 301/302/303 unless
            // CURLOPT_POSTREDIR is set, which would turn "create webhook" into a GET that
            // returns 200 with data while creating nothing. And X-API-TOKEN is a custom
            // header, so unlike Authorization curl would forward it to the redirect target.
            $message = 'Invoice Ninja API request was redirected (HTTP '.$httpCode.')';
            if ($redirectUrl) {
                $message .= ' to '.$redirectUrl;
            }
            \Log::error($message, [
                'url' => $url,
                'redirect_url' => $redirectUrl,
            ]);
            throw new InvoiceNinjaException($message, 'invoiceninja_error_redirect');
        }

        $result = json_decode($rawResult, true);

        if (! isset($result['data'])) {
            $message = 'Invoice Ninja API request failed (HTTP '.$httpCode.')';
            if (isset($result['message']) && is_string($result['message'])) {
                $message .= ': '.$result['message'];
            }
            \Log::error($message, [
                'url' => $url,
                'route' => $route,
                'response' => mb_substr((string) $rawResult, 0, 2000),
            ]);
            throw new InvoiceNinjaException($message, self::responseReasonKey($httpCode, $result));
        }

        return $result['data'];
    }

    /**
     * Classify a curl transport failure so the UI can explain it in plain language.
     */
    private static function curlReasonKey(int $curlErrno): string
    {
        if (in_array($curlErrno, self::CURL_TLS_ERRORS, true)) {
            return 'invoiceninja_error_tls';
        }

        if (in_array($curlErrno, self::CURL_UNREACHABLE_ERRORS, true)) {
            return 'invoiceninja_error_unreachable';
        }

        return 'invoiceninja_error_generic';
    }

    /**
     * Classify a non-success response. A body that is not JSON at all takes precedence
     * over the status code: a WAF answering with an HTML 403 must read as "blocked",
     * not as "your API token was rejected".
     */
    private static function responseReasonKey(int $httpCode, $result): string
    {
        if (! is_array($result)) {
            // Above 500 a non-JSON body is usually a genuine gateway or error page rather
            // than bot protection, and telling the user to allow requests would be wrong.
            return $httpCode >= 500 ? 'invoiceninja_error_generic' : 'invoiceninja_error_blocked';
        }

        return match ($httpCode) {
            401, 403 => 'invoiceninja_error_token',
            404 => 'invoiceninja_error_not_found',
            429 => 'invoiceninja_error_rate_limited',
            default => 'invoiceninja_error_generic',
        };
    }

    public static function convertCodeToId($currencyCode)
    {
        $currencies = [
            'USD' => 1,
            'GBP' => 2,
            'EUR' => 3,
            'ZAR' => 4,
            'DKK' => 5,
            'ILS' => 6,
            'SEK' => 7,
            'KES' => 8,
            'CAD' => 9,
            'PHP' => 10,
            'INR' => 11,
            'AUD' => 12,
            'SGD' => 13,
            'NOK' => 14,
            'NZD' => 15,
            'VND' => 16,
            'CHF' => 17,
            'GTQ' => 18,
            'MYR' => 19,
            'BRL' => 20,
            'THB' => 21,
            'NGN' => 22,
            'ARS' => 23,
            'BDT' => 24,
            'AED' => 25,
            'HKD' => 26,
            'IDR' => 27,
            'MXN' => 28,
            'EGP' => 29,
            'COP' => 30,
            'XOF' => 31,
            'CNY' => 32,
            'RWF' => 33,
            'TZS' => 34,
            'ANG' => 35,
            'TTD' => 36,
            'XCD' => 37,
            'GHS' => 38,
            'BGN' => 39,
            'AWG' => 40,
            'TRY' => 41,
            'RON' => 42,
            'HRK' => 43,
            'SAR' => 44,
            'JPY' => 45,
            'MVR' => 46,
            'CRC' => 47,
            'PKR' => 48,
            'PLN' => 49,
            'LKR' => 50,
            'CZK' => 51,
            'UYU' => 52,
            'NAD' => 53,
            'TND' => 54,
            'RUB' => 55,
            'MZN' => 56,
            'OMR' => 57,
            'UAH' => 58,
            'MOP' => 59,
            'TWD' => 60,
            'DOP' => 61,
            'CLP' => 62,
            'ISK' => 63,
            'PGK' => 64,
            'JOD' => 65,
            'MMK' => 66,
            'PEN' => 67,
            'BWP' => 68,
            'HUF' => 69,
            'UGX' => 70,
            'BBD' => 71,
            'BND' => 72,
            'GEL' => 73,
            'QAR' => 74,
            'HNL' => 75,
            'SRD' => 76,
            'BHD' => 77,
            'VEF' => 78,
            'KRW' => 79,
            'MAD' => 80,
            'JMD' => 81,
            'AOA' => 82,
            'HTG' => 83,
            'ZMW' => 84,
            'NPR' => 85,
            'XPF' => 86,
            'MUR' => 87,
            'CVE' => 88,
            'KWD' => 89,
            'DZD' => 90,
            'MKD' => 91,
            'FJD' => 92,
            'BOB' => 93,
            'ALL' => 94,
            'RSD' => 95,
            'LBP' => 96,
            'AMD' => 97,
            'AZN' => 98,
            'BAM' => 99,
            'BYN' => 100,
            'GIP' => 101,
            'MDL' => 102,
            'KZT' => 103,
            'ETB' => 104,
        ];

        return $currencies[$currencyCode] ?? null;
    }
}
