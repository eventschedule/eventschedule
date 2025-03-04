<?php

namespace App\Utils;

class GeminiUtils
{
    public static function parseEvent($details)
    {
        $apiKey = config('services.google.gemini_key');
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=" . $apiKey;
        
        $data = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => "Parse the event details from this message to the following fields:
                                    event_name,
                                    event_name_en,
                                    event_description,
                                    event_date_time (YYYY-MM-DD HH:MM format),
                                    event_address,
                                    event_city,
                                    event_state,
                                    event_postal_code,
                                    event_country_code,
                                    registration_url,       
                                    venue_name,
                                    venue_name_en,
                                    venue_email,
                                    venue_website,
                                    performer_name,
                                    performer_name_en,
                                    performer_email,
                                    performer_website,
                                    Note: make sure to return the text values in the same language as the message except for the event_name_en.
                                    " . $details]
                    ]
                ]
            ],
            'generationConfig' => [
                'response_mime_type' => 'application/json'
            ]
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => json_encode($data)
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            throw new \Exception('Gemini API request failed with status code: ' . $httpCode);
        }

        $data = json_decode($response, true);

        return json_decode($data['candidates'][0]['content']['parts'][0]['text'], true);
    }
}