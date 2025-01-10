<?php

namespace App\Utils;

class InvoiceNinja
{
    public function __construct($apiKey)
    {
        $this->apiKey = $apiKey;
    }

    public function getCompany()
    {
        $company = $this->sendRequest('companies/current', 'POST');

        return $company;
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

    public function createInvoice($clientId, $lineItems) {
        $invoice = $this->sendRequest('invoices', 'POST', [
            'client_id' => $clientId,
            'line_items' => $lineItems,
        ]);

        return $invoice;
    }

    public function sendRequest($route, $method = 'GET', $data = false)
    {
        // $key = esc_attr( get_option( 'invoiceninja_api_token' ) );
        // $url = esc_attr( get_option( 'invoiceninja_api_url' ) );
        $url = '';

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
            curl_setopt($response, CURLOPT_POSTFIELDS, $data);
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
