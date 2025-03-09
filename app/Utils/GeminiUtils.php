<?php

namespace App\Utils;

use App\Utils\UrlUtils;

class GeminiUtils
{
    private static function sendRequest($prompt, $model = 'gemini-2.0-flash')
    {
        $apiKey = config('services.google.gemini_key');
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key=" . $apiKey;
        
        $data = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
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
        $text = $data['candidates'][0]['content']['parts'][0]['text'];
        $parsed = json_decode($text, true);
        
        // Handle both array and object response formats
        if (is_array($parsed) && isset($parsed[0])) {
            $data = $parsed[0];
        } else {
            $data = $parsed;
        }
        
        return $data;
    }

    public static function parseEvent($details)
    {
        // Define fields and their descriptions
        $fields = [
            'event_name' => '',
            'event_name_en' => 'only if the event_name is not English',
            'event_date_time' => 'YYYY-MM-DD HH:MM format',
            'event_duration' => 'in hours',
            'event_address' => '',
            'event_address_en' => 'English translation, only if the event address is not English',
            'event_city' => '',
            'event_city_en' => 'English translation, only if the event city is not English',
            'event_state' => '',
            'event_state_en' => 'English translation, only if the event state is not English',
            'event_postal_code' => '',
            'event_country_code' => '',
            'registration_url' => '',
            'venue_name' => '',
            'venue_name_en' => 'English translation, only if the venue name is not English',
            'venue_email' => '',
            'venue_website' => '',
            'performer_name' => '',
            'performer_name_en' => 'English translation, only if the performer name is not English',
            'performer_email' => '',
            'performer_website' => ''
        ];

        // Build prompt from fields
        $prompt = "Parse the event details from this message to the following fields, take your time and do the best job possible:\n";
        foreach ($fields as $field => $note) {
            $prompt .= $field . ($note ? " ({$note})" : "") . ",\n";
        }
        $prompt .= $details;

        $data = self::sendRequest($prompt);

        // Process results
        foreach ($fields as $field => $note) {
            // Ensure all fields exist
            if (!isset($data[$field])) {
                $data[$field] = '';
                continue;
            }
            
            // Trim asterisks if string value
            if (is_string($data[$field])) {
                $data[$field] = trim($data[$field], '*');
            }
        }

        // Handle special case for address
        if (!$data['event_address']) {
            if ($data['event_city']) {
                $data['event_address'] = $data['event_city'];
                unset($data['event_city']);
            } else if ($data['event_state']) {
                $data['event_address'] = $data['event_state'];
                unset($data['event_state']);
            }
        }

        // Check if the registration url is a redirect, in which case get the final url
        if ($data['registration_url']) {
            $data['registration_url'] = UrlUtils::getRedirectUrl($data['registration_url']);
        }

        // Commented out YouTube URL fetching
        if (false && $data['performer_name']) {
            $data['performer_youtube_url'] = self::getPerformerYoutubeUrl($data['performer_name']);
        }

        return $data;
    }

    private static function getPerformerYoutubeUrl($performerName)
    {
        $url = "https://www.googleapis.com/youtube/v3/search"
            . "?key=" . config('services.google.backend')
            . "&q=" . urlencode($performerName)
            . "&type=video"
            . "&order=relevance"
            . "&maxResults=1";
            
        $response = file_get_contents($url);
        $data = json_decode($response, true);

        if (isset($data['items'][0]['id']['videoId'])) {
            return "https://www.youtube.com/watch?v=" . $data['items'][0]['id']['videoId'];
        }

        return null;
    }

    public static function translate($text, $from = 'auto', $to = 'en')
    {
        $prompt = "Translate this text from {$from} to {$to}. Return only the translation as a JSON string:\n{$text}";
        
        $response = self::sendRequest($prompt);
        
        if (is_array($response) && isset($response['translation'])) {
            return $response['translation'];
        }

        return $response;
    }
}   