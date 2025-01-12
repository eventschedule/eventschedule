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

    public function createClient($name, $email, $currencyCode, $qrCodeUrl) {
        $parts = explode(' ', $name);
        $lastName = array_pop($parts); 
        $firstName = implode(' ', $parts);

        $client = $this->sendRequest('clients', 'POST', [
            'currency_code' => $currencyCode,                    
            'public_notes' => '<img src="' . $qrCodeUrl . '" />',
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

    public function createInvoice($clientId, $lineItems) {
        $invoice = $this->sendRequest('invoices', 'POST', [
            'client_id' => $clientId,
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
}
