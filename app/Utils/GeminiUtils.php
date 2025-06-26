<?php

namespace App\Utils;

use App\Utils\UrlUtils;
use Carbon\Carbon;

class GeminiUtils
{
    private static function sendRequest($prompt, $imageData = null)
    {
        $apiKey = config('services.google.gemini_key');
        $model = 'gemini-2.0-flash';
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key=" . $apiKey;
        
        $data = [
            'contents' => [
                [
                    'parts' => []
                ]
            ],
            'generationConfig' => [
                'response_mime_type' => 'application/json'
            ]
        ];

        // Add image data if provided
        if ($imageData) {
            $data['contents'][0]['parts'][] = [
                'inline_data' => [
                    'mime_type' => 'image/jpeg',
                    'data' => base64_encode($imageData)
                ]
            ];
        }

        // Add text prompt
        if ($prompt) {
            $data['contents'][0]['parts'][] = ['text' => $prompt];
        }

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
            \Log::error("Gemini API error response: " . $response);
            throw new \Exception('Gemini API request failed with status code: ' . $httpCode);
        }

        $data = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            \Log::error("Failed to parse Gemini API response: " . $response);
            throw new \Exception('Invalid JSON response from Gemini API');
        }

        if (!isset($data['candidates'][0]['content']['parts'][0]['text'])) {
            \Log::error("Unexpected Gemini API response structure: " . json_encode($data));
            throw new \Exception('Unexpected response structure from Gemini API');
        }

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

    public static function parseEvent($details, $imageData = null)
    {
        // Define fields and their descriptions
        $fields = [
            'event_name' => '',
            'event_name_en' => 'only if the event_name is not English',
            'event_date_time' => 'YYYY-MM-DD HH:MM',
            'event_duration' => 'in hours',
            'event_details' => '',
            'event_address' => 'Just the street address, do not include the city or state',
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
        $prompt = "Parse the event details from this " . ($imageData ? "image and " : "") . "message to the following fields, take your time and do the best job possible:\n";
        foreach ($fields as $field => $note) {
            $prompt .= $field . ($note ? " ({$note})" : "") . ",\n";
        }
        $prompt .= $details;

        $now = Carbon::now(auth()->user() ? auth()->user()->timezone : 'UTC');
        $thisMonth = $now->format('M Y');
        
        $prompt .= "\nThe date today is {$now->format('M d, Y')}.";

        if ($now->format('n') == 12) {
            $nextMonth = $now->copy()->addMonth()->format('M Y'); 
            $prompt .= "\nThe event date is either {$thisMonth} or {$nextMonth}.";
        } else {
            $nextMonth = $now->copy()->addMonth()->format('M Y');
            $prompt .= "\nThe event date is either {$thisMonth} or {$nextMonth}.";
        }

        $prompt .= "\nIf there is no time, use 8pm as the default time.";
        $prompt .= "\nIf there are multiple performers create multiple events.";

        // Use gemini-1.5-flash for both text and image inputs
        $data = self::sendRequest($prompt, $imageData);

        foreach ($data as $key => $item) {

            foreach ($fields as $field => $note) {
                // Ensure all fields exist
                if (! isset($item[$field])) {
                    $data[$key][$field] = '';
                } elseif (is_string($item[$field])) {
                    $data[$key][$field] = trim($item[$field], '*');
                }

                if ($data[$key][$field] == strtoupper($data[$key][$field])) {
                    $data[$key][$field] = $item[$field] = ucwords(strtolower($data[$key][$field]));
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

            // Convert performer data to array format
            if (!empty($item['performer_name'])) {
                $data[$key]['performers'] = [[
                    'name' => $item['performer_name'],
                    'name_en' => $item['performer_name_en'] ?? '',
                    'email' => $item['performer_email'] ?? '',
                    'website' => $item['performer_website'] ?? '',
                ]];
                
                // Remove old single performer fields
                unset($data[$key]['performer_name']);
                unset($data[$key]['performer_name_en']);
                unset($data[$key]['performer_email']);
                unset($data[$key]['performer_website']);
            }
        }

        // Combine events with same time and address
        $combinedData = [];
        foreach ($data as $event) {
            $key = $event['event_date_time'] . '|' . $event['event_address'];
            
            if (isset($combinedData[$key])) {
                // Add performer to existing event if not already present
                if (!empty($event['performers'])) {
                    foreach ($event['performers'] as $performer) {
                        $exists = false;
                        foreach ($combinedData[$key]['performers'] as $existingPerformer) {
                            if ($existingPerformer['name'] === $performer['name']) {
                                $exists = true;
                                break;
                            }
                        }
                        if (!$exists) {
                            $combinedData[$key]['performers'][] = $performer;
                        }
                    }
                }
            } else {
                // Initialize performers array if not set
                if (!isset($event['performers'])) {
                    $event['performers'] = [];
                }
                $combinedData[$key] = $event;
            }
        }

        $data = array_values($combinedData);

        foreach ($data as $key => $item) {
            // Check if the registration url is a redirect
            if (! empty($item['registration_url'])) {
                $links = UrlUtils::getUrlMetadata($item['registration_url']);
                $data[$key]['registration_url'] = $links['redirect_url'];
                $data[$key]['social_image'] = $links['image_path'];
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
        return self::searchYouTube($performerName);
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
            // If still array, try to extract a string
            if (is_array($value)) {
                $value = implode(' ', array_filter($value, 'is_string'));
            }
            // If value is still null, try to implode all string values in $response
            if (!$value) {
                $value = implode(' ', array_filter($response, 'is_string'));
            }
        } elseif (is_string($response)) {
            $value = $response;
        }

        // If value is still not a string, log and set to null
        if (!is_string($value) || !$value) {
            \Log::info("Error: translation response: " . json_encode($response) . " => " . $value);
            $value = null;
        }

        return $value;
    }

    /**
     * Batch translate group names to English using Gemini API
     */
    public static function translateGroupNames($groupNames, $fromLanguage = 'auto')
    {
        if (empty($groupNames)) {
            return [];
        }

        // Create a single prompt for batch translation
        $prompt = "Translate these group names from {$fromLanguage} to English. Return a JSON object where each key is the original name and the value is the English translation:\n";
        $prompt .= json_encode($groupNames);

        try {
            $response = self::sendRequest($prompt);
            
            // sendRequest returns an array, get the first item
            if (!empty($response) && is_array($response)) {
                $translations = $response[0];
                
                if (is_array($translations)) {
                    return $translations;
                }
            }
        } catch (\Exception $e) {
            \Log::error('Group name translation failed: ' . $e->getMessage());
        }

        // Fallback: return empty translations if API fails
        return [];
    }

    public static function searchYouTube($performerName)
    {
        // Validate performer name input
        if (empty($performerName) || strlen($performerName) > 100) {
            return null;
        }
        
        // Validate API key
        $apiKey = config('services.google.backend');
        if (!$apiKey) {
            return null;
        }
        
        $url = "https://www.googleapis.com/youtube/v3/search"
            . "?key=" . $apiKey
            . "&q=" . urlencode($performerName)
            . "&type=video"
            . "&order=relevance"
            . "&maxResults=1";
            
        // Use secure cURL instead of file_get_contents
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_USERAGENT => 'EventSchedule/1.0',
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_PROTOCOLS => CURLPROTO_HTTPS, // Only HTTPS for Google API
            CURLOPT_MAXFILESIZE => 1048576, // 1MB limit
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($response === false || $httpCode !== 200) {
            return null;
        }
        
        $data = json_decode($response, true);

        if (isset($data['items'][0]['id']['videoId'])) {
            $videoId = $data['items'][0]['id']['videoId'];
            // Validate video ID format
            if (preg_match('/^[a-zA-Z0-9_-]{11}$/', $videoId)) {
                return "https://www.youtube.com/watch?v=" . $videoId;
            }
        }

        return null;
    }

    public static function generateBlogPost($topic)
    {
        // Randomly select a length to vary content length
        $lengths = ['short', 'medium', 'long'];
        $length = $lengths[array_rand($lengths)];
        
        $prompt = "Generate a blog post about '{$topic}' with the following specifications:
        
        Tone: professional
        Length: {$length} (short: 300-500 words, medium: 800-1200 words, long: 1500-2000 words)
        
        Please return a JSON object with the following structure:
        {
            \"title\": \"SEO-optimized title (50-60 characters)\",
            \"content\": \"HTML formatted content with proper headings (h1, h2, h3), paragraphs, and lists. Include practical tips, examples, and actionable advice.\",
            \"excerpt\": \"Brief summary (150-160 characters)\",
            \"tags\": [\"tag1\", \"tag2\", \"tag3\", \"tag4\", \"tag5\"],
            \"meta_title\": \"SEO meta title (50-60 characters)\",
            \"meta_description\": \"SEO meta description (150-160 characters)\"
        }
        
        Requirements:
        - Make the content engaging and informative
        - Include practical tips and actionable advice
        - Use proper HTML formatting with h1, h2, h3 tags
        - Include bullet points and numbered lists where appropriate
        - Make it relevant to event scheduling and management
        - Ensure the content is original and valuable
        - Include a call-to-action at the end
        - Make it SEO-friendly with relevant keywords
        - Always maintain a professional tone";

        try {
            $data = self::sendRequest($prompt);
            
            if (isset($data[0])) {
                $result = $data[0];
                
                // Ensure all required fields exist
                $result['title'] = $result['title'] ?? 'Blog Post about ' . $topic;
                $result['content'] = $result['content'] ?? '<p>Content about ' . $topic . ' will be generated here.</p>';
                $result['excerpt'] = $result['excerpt'] ?? 'A brief summary about ' . $topic;
                $result['tags'] = $result['tags'] ?? ['events', 'scheduling'];
                $result['meta_title'] = $result['meta_title'] ?? $result['title'];
                $result['meta_description'] = $result['meta_description'] ?? $result['excerpt'];
                
                return $result;
            }
            
            throw new \Exception('Invalid response structure from Gemini API');
            
        } catch (\Exception $e) {
            \Log::error('Failed to generate blog post: ' . $e->getMessage());
            
            // Return fallback content
            return [
                'title' => 'Blog Post about ' . $topic,
                'content' => '<h1>' . $topic . '</h1><p>This is a placeholder for content about ' . $topic . '. Please edit this content to add your own insights and information.</p>',
                'excerpt' => 'A brief summary about ' . $topic,
                'tags' => ['events', 'scheduling', 'management'],
                'meta_title' => 'Blog Post about ' . $topic,
                'meta_description' => 'A brief summary about ' . $topic
            ];
        }
    }
}   