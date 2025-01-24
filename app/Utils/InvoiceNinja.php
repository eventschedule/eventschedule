<?php

namespace App\Utils;

class InvoiceNinja
{
    public function __construct($apiKey, $apiUrl)
    {
        $this->apiKey = $apiKey;
        $this->apiUrl = $apiUrl;
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

    public function createClient($name, $email, $currencyCode) {
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

        return $client;
    }

    public function findClient($email, $currencyCode) {
        $clients = $this->sendRequest('clients?is_deleted=false&email=' . $email, 'GET');
        
        if (count($clients) > 0) {
            foreach ($clients as $client) {
                if ($client['settings']['currency_id'] == InvoiceNinja::convertCodeToId($currencyCode)) {
                    return $client;
                }
            }
        }

        return null;
    }

    public function createInvoice($clientId, $lineItems, $qrCodeUrl) {
        $invoice = $this->sendRequest('invoices', 'POST', [            
            'client_id' => $clientId,
            'public_notes' => '<img src="' . $qrCodeUrl . '" />',
            'line_items' => $lineItems,
        ]);

        return $invoice;
    }

    public function sendRequest($route, $method = 'GET', $data = false)
    {
        $url = $this->apiUrl;

        if ( ! $this->apiKey ) {
            return null;
        }

        if ( empty( $url ) ) {
            $url = 'https://invoicing.co/api/v1/';
        } else {
            $url = rtrim( $url, '/' );
            $url = rtrim( $url, 'api/v1' );
            $url = rtrim( $url, '/' );
            $url .= '/api/v1/';
        }

        $url .= $route;

        /*
        $args = array(
            'timeout' => '60',
            'headers' => array(
                'X-API-TOKEN' => $key,
                'X-CLIENT-PLATFORM' => 'WordPress',
                'Content-Type' => 'application/json',
            ),
            'body' => $data ? wp_json_encode($data) : null,
            'method' => $method,
        );
        */
        
        $response = curl_init();        
        curl_setopt($response, CURLOPT_URL, $url);
        curl_setopt($response, CURLOPT_RETURNTRANSFER, 1);

        if ($method == 'POST') {
            curl_setopt($response, CURLOPT_POST, 1);
            curl_setopt($response, CURLOPT_POSTFIELDS, json_encode($data));
        }

        curl_setopt($response, CURLOPT_HTTPHEADER, [
            'X-API-TOKEN: ' . $this->apiKey,
            'X-CLIENT-PLATFORM: ' . 'Event Schedule', 
            'Content-Type: application/json',
        ]);

        $result = curl_exec($response);

        curl_close($response);
        
        $result = json_decode($result, true);

        return $result['data'];
    }

    public static function convertCodeToId($currencyCode) {
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
