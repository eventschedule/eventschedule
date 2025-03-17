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
            $data = $parsed;
        } else {
            $data = [$parsed];
        }

        //\Log::info("Parsed data: " . json_encode($data));

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
            'performer_website' => '',
        ];

        // Build prompt from fields
        $prompt = "Parse the event details from this message to the following fields, take your time and do the best job possible:\n";
        foreach ($fields as $field => $note) {
            $prompt .= $field . ($note ? " ({$note})" : "") . ",\n";
        }
        $prompt .= $details;

        $data = self::sendRequest($prompt);

        foreach ($data as $key => $item) {

            foreach ($fields as $field => $note) {
                // Ensure all fields exist
                if (! isset($item[$field])) {
                    $data[$key][$field] = '';
                } elseif (is_string($item[$field])) {
                    $data[$key][$field] = trim($item[$field], '*');
                }
            }

            // Handle special case for address
            if (empty($item['event_address'])) {
                if (! empty($item['event_city'])) {
                    $data[$key]['event_address'] = $item['event_city'];
                    unset($data[$key]['event_city']);
                } else if (! empty($item['event_state'])) {
                    $data[$key]['event_address'] = $item['event_state'];
                    unset($data[$key]['event_state']);
                }
            }

            // Check if the registration url is a redirect, in which case get the final url
            if (! empty($item['registration_url'])) {
                $data[$key]['registration_url'] = UrlUtils::getRedirectUrl($item['registration_url']);

                try {
                    $ch = curl_init();
                    curl_setopt_array($ch, [
                        CURLOPT_URL => $item['registration_url'],
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_MAXREDIRS => 5
                    ]);
                    $html = curl_exec($ch);
                    curl_close($ch);
                    
                    // Look for Open Graph image meta tag
                    if (preg_match('/<meta[^>]*property=["\']og:image["\'][^>]*content=["\']([^"\']*)["\']/', $html, $matches) ||
                        preg_match('/<meta[^>]*content=["\']([^"\']*)["\'][^>]*property=["\']og:image["\']/', $html, $matches)) {
                        
                        if ($imageUrl = $matches[1]) {
                            $imageContents = file_get_contents($imageUrl);
                            $extension = pathinfo(parse_url($imageUrl, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
                            $filename = '/tmp/event_' . strtolower(\Str::random(32)) . '.' . $extension;
                            
                            file_put_contents($filename, $imageContents);
                            $data[$key]['social_image'] = $filename;
                        }
                    }
                } catch (\Exception $e) {
                    // do nothing 
                }    
            }
        }

        // Commented out YouTube URL fetching
        // if ($data['performer_name']) {
        //     $data['performer_youtube_url'] = self::getPerformerYoutubeUrl($data['performer_name']);
        // }

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
        $response = $response[0];

        $value = null;

        if (is_array($response)) {
            if (isset($response['translation'])) {
                $value = $response['translation'];
            }
            if (isset($response['en'])) {
                $value = $response['en']; 
            }
        }
        
        if (is_array($value)) {
            $value = implode(' ', array_filter($value, 'trim'));
        }

        if (! $value) {
            $value = json_decode('"' . $response . '"');
        }
        
        // Then check if we have a valid string
        if (! $value || ! is_string($value)) {
            \Log::info("Error: translation response: " . json_encode($response) . " => " . $value);
            $value = null;
        }

        return $value;
    }
}   