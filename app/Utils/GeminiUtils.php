<?php

namespace App\Utils;

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

        \Log::info($response);

        $data = json_decode($response, true);
        $data = json_decode($data['candidates'][0]['content']['parts'][0]['text'], true);
        $data = $data[0];
        
        return $data;
    }

    public static function parseEvent($details)
    {
        $prompt = "Parse the event details from this message to the following fields:
                    event_name,
                    event_name_en,
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
                    
                    Some notes:
                    - Make sure to return the text values in the same language as the message except for the event_name_en
                    - Only provide a value for event_name_en if the value for event_name is not English 
                    - If the event_date_time is not in the message or you think it's before " . (date('Y') - 1) . ", set it to null
                    " . $details;

        $data = self::sendRequest($prompt);

        if (! $data['event_address']) {
            if ($data['event_city']) {
                $data['event_address'] = $data['event_city'];
                unset($data['event_city']);
            } else if ($data['event_state']) {
                $data['event_address'] = $data['event_state'];
                unset($data['event_state']);
            }
        }

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
}   