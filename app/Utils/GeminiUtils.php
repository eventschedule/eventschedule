<?php

namespace App\Utils;

use App\Models\EventType;
use App\Models\Role;
use App\Models\Event;
use App\Models\RoleUser;
use App\Models\MediaAsset;
use App\Utils\ImageUtils;
use App\Utils\UrlUtils;
use Carbon\Carbon;

class GeminiUtils
{
    private static function sendRequest($prompt, $imageData = null)
    {
        if (str_starts_with($prompt, 'Translate')) {
            $model = 'gemini-2.0-flash';
        } else {
            $model = 'gemini-2.5-flash';
        }
        
        $apiKey = config('services.google.gemini_key');
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
            // Use ImageUtils to get the correct MIME type
            $imageUrl = 'temp_image'; // Placeholder for format detection
            $detectedFormat = ImageUtils::detectImageFormat($imageData, $imageUrl);
            $mimeType = ImageUtils::getImageMimeType($detectedFormat);
            
            $data['contents'][0]['parts'][] = [
                'inline_data' => [
                    'mime_type' => $mimeType,
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
            
            // Try to extract the specific error message from the response
            $errorData = json_decode($response, true);
            if (json_last_error() === JSON_ERROR_NONE && isset($errorData['error']['message'])) {
                throw new \Exception($errorData['error']['message']);
            }
            
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

    public static function parseEvent($role, $details, $file = null)
    {
        $imageData = null;
        $filename = null;
        
        if ($file) {
            // Check if this is a file created from image data (not a real uploaded file)
            $isFromImageData = $file->getClientOriginalName() === 'event_image.jpg' || 
                              $file->getClientOriginalName() === 'event_image.png' || 
                              $file->getClientOriginalName() === 'event_image.gif' || 
                              $file->getClientOriginalName() === 'event_image.webp';
            
            if ($isFromImageData) {
                // File was created from image data, so we can trust it
                $imageData = file_get_contents($file->getRealPath());
                $imageUrl = $file->getClientOriginalName();
                $detectedFormat = ImageUtils::detectImageFormat($imageData, $imageUrl);
                
                // Check if detected format is supported
                $allowedFormats = ['jpeg', 'jpg', 'png', 'gif', 'webp'];
                if (!in_array($detectedFormat, $allowedFormats)) {
                    $imageData = null;
                } else {
                    // Use the file directly since it's already a temporary file
                    $filename = $file->getRealPath();
                    
                    // Validate that it's actually an image
                    $imageInfo = getimagesize($filename);
                    if ($imageInfo === false) {
                        $imageData = null;
                        $filename = null;
                    }
                }
            } else {
                // This is a real uploaded file, validate it
                ImageUtils::validateUploadedFile($file);
                
                // Read file content securely
                $imageData = file_get_contents($file->getRealPath());
                
                // Use ImageUtils to detect format and validate
                $imageUrl = $file->getClientOriginalName();
                $detectedFormat = ImageUtils::detectImageFormat($imageData, $imageUrl);
                
                // Check if detected format is supported
                $allowedFormats = ['jpeg', 'jpg', 'png', 'gif', 'webp'];
                if (!in_array($detectedFormat, $allowedFormats)) {
                    $imageData = null;
                } else {
                    // Generate secure filename with correct extension
                    $extension = ImageUtils::getImageExtension($detectedFormat);
                    $filename = '/tmp/event_' . strtolower(\Str::random(32)) . '.' . $extension;
                    
                    // Use move_uploaded_file for security
                    if (!move_uploaded_file($file->getRealPath(), $filename)) {
                        $imageData = null;
                        $filename = null;
                    }
                    
                    // Validate that it's actually an image
                    $imageInfo = getimagesize($filename);
                    if ($imageInfo === false) {
                        unlink($filename); // Remove invalid file
                        $imageData = null;
                        $filename = null;
                    }
                }
            }
        }

        // Get available categories
        $categories = [];
        $categoryVariants = [];

        try {
            $eventTypes = EventType::ordered();

            if ($eventTypes->isNotEmpty()) {
                foreach ($eventTypes as $eventType) {
                    $categories[$eventType->id] = $eventType->translatedName('en');

                    $variants = $eventType->translations ?? [];
                    $variants['name'] = $eventType->name;
                    $variants['en'] = $eventType->translatedName('en');

                    $categoryVariants[$eventType->id] = collect($variants)
                        ->filter(fn ($value) => is_string($value) && $value !== '')
                        ->unique()
                        ->values()
                        ->all();
                }
            }
        } catch (\Throwable $exception) {
            // Fall back to configuration-defined categories below.
        }

        if (empty($categories)) {
            $fallbackCategories = config('app.event_categories', []);

            foreach ($fallbackCategories as $id => $name) {
                $categories[$id] = $name;
                $categoryVariants[$id] = [$name];
            }
        }

        $categoryNames = array_values($categories);

        // Define fields and their descriptions
        $fields = [
            'event_name' => '',
            'event_name_en' => 'only if the event_name is not English',
            'event_date_time' => 'YYYY-MM-DD HH:MM',
            'event_duration' => 'in hours',
            'event_details' => 'In original language, using markdown formatting with as much accurate text as possible describing the event',
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
            'category_name' => 'Choose the most appropriate category from: ' . implode(', ', $categoryNames),
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

                if (is_string($data[$key][$field]) && $data[$key][$field] == strtoupper($data[$key][$field])) {
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

            // Convert category name to category ID
            if (!empty($item['category_name'])) {
                $categoryName = trim($item['category_name']);
                $categoryId = null;

                // Try exact match first across all known variants
                foreach ($categoryVariants as $id => $names) {
                    foreach ($names as $name) {
                        if (strcasecmp($name, $categoryName) === 0) {
                            $categoryId = $id;
                            break 2;
                        }
                    }
                }

                // If no exact match, try partial match
                if (!$categoryId) {
                    foreach ($categoryVariants as $id => $names) {
                        foreach ($names as $name) {
                            if (stripos($name, $categoryName) !== false || stripos($categoryName, $name) !== false) {
                                $categoryId = $id;
                                break 2;
                            }
                        }
                    }
                }

                // If still no match, try fuzzy matching
                if (!$categoryId) {
                    $bestMatch = null;
                    $bestScore = 0;

                    foreach ($categoryVariants as $id => $names) {
                        foreach ($names as $name) {
                            $score = similar_text(strtolower($name), strtolower($categoryName), $percent);

                            if ($percent > $bestScore) {
                                $bestScore = $percent;
                                $bestMatch = $id;
                            }
                        }
                    }

                    // Only use fuzzy match if similarity is above 70%
                    if ($bestScore > 70) {
                        $categoryId = $bestMatch;
                    }
                }

                $data[$key]['category_id'] = $categoryId;
                unset($data[$key]['category_name']);
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

        foreach ($data as $key => $item) {
            if ($imageData && empty($data[$key]['social_image'])) {
                $data[$key]['social_image'] = $filename;
            }

            if ($role->isVenue()) {
                $data[$key]['venue_id'] = UrlUtils::encodeId($role->id);
                $data[$key]['event_address'] = $role->address1;
            } elseif (! empty($item['event_city']) && (! empty($item['venue_name']) || ! empty($item['event_address']))) {
                $venue = Role::where('is_deleted', false)
                        ->where('country_code', $role->country_code)
                        ->where('city', $item['event_city'])
                        ->where(function($query) use ($item) {
                            $query->when(! empty($item['venue_name']), function($q) use ($item) {
                                $q->where('name', $item['venue_name']);
                            })
                            ->when(! empty($item['venue_name_en']), function($q) use ($item) {
                                $q->orWhere('name_en', $item['venue_name_en']); 
                            });
                        })                        
                        ->where(function($query) use ($item) {
                            $query->when(! empty($item['event_address']), function($q) use ($item) {
                                $q->where('address1', $item['event_address']);
                            })
                            ->when(! empty($item['event_address_en']), function($q) use ($item) {
                                $q->orWhere('address1_en', $item['event_address_en']); 
                            });
                        })                                                
                        ->where('type', 'venue')
                        ->orderBy('id')
                        ->first();

                if ($venue) {
                    $data[$key]['venue_id'] = UrlUtils::encodeId($venue->id);
                }
            }

            if ($role->isCurator()) {
                if (empty($item['event_country_code'])) {
                    $data[$key]['event_country_code'] = $role->country_code;
                }
            }

            if ($role->isTalent()) {
                $data[$key]['talent_id'] = UrlUtils::encodeId($role->id);
            } elseif (! empty($item['performers'])) {
                                
                foreach ($item['performers'] as $index => $performer) {                    
                    $talent = Role::where('is_deleted', false)
                        ->where(function($query) use ($performer) {
                            $query->where('name', $performer['name'])
                                ->when(! empty($performer['name_en']), function($q) use ($performer) {
                                    $q->orWhere('name_en', $performer['name_en']);
                                });
                        })
                        ->where('type', 'talent')
                        ->where('country_code', $role->country_code)
                        ->orderByRaw('CASE WHEN email IS NOT NULL THEN 0 ELSE 1 END')
                        ->first();

                    if ($talent) {
                        $data[$key]['performers'][$index]['talent_id'] = UrlUtils::encodeId($talent->id);
                    }
                }
            }

            if (! empty($item['event_date_time'])) {
                try {
                    $eventDate = Carbon::parse($item['event_date_time']);
                } catch (\Exception $e) {
                    $data[$key]['event_date_time'] = null;
                    continue;
                }
                if ($eventDate->lt(now()->subDays(3)) || $eventDate->diffInMonths(now()) > 2) {
                    $data[$key]['event_date_time'] = null;
                }
            }

            // Check if the event is already imported
            $eventUrl = null;
            $event = Event::where('registration_url', $item['registration_url'])->first();
            if ($event) {
                $data[$key]['event_url'] = $event->getGuestUrl();
                $data[$key]['event_id'] = UrlUtils::encodeId($event->id);
                $data[$key]['is_curated'] = $role->isCurator() && $event->roles->contains($role->id);
            }

            // Check for similar events at the same time
            if (! $event && ! empty($item['event_date_time'])) {
                $timezone = $role->user->timezone;
                $eventDate = Carbon::parse($item['event_date_time'], $timezone)->setTimezone('UTC');
                $query = Event::where('starts_at', $eventDate);
                
                // Check for same venue address
                if (! empty($item['event_address'])) {
                    $similarByAddress = (clone $query)
                        ->whereHas('roles', function($q) use ($item) {
                            $q->where('type', 'venue')
                              ->where('address1', $item['event_address']);
                        })
                        ->first();

                    if ($similarByAddress) {
                        $data[$key]['event_url'] = $similarByAddress->getGuestUrl();
                        $data[$key]['event_id'] = UrlUtils::encodeId($similarByAddress->id);
                    }
                }

                // Check for same performer name
                if ((! empty($item['performers'][0]['name']) ?? null) && empty($data[$key]['event_url'])) {
                    $similarByPerformer = (clone $query)
                        ->whereHas('roles', function($q) use ($item) {
                            $q->where('type', 'talent')
                              ->where('name', 'like', '%' . $item['performers'][0]['name'] . '%')
                              ->when(!empty($item['performers'][0]['name_en']), function($q) use ($item) {
                                  $q->orWhere('name', 'like', '%' . $item['performers'][0]['name_en'] . '%');
                              });
                        })
                        ->first();

                    if ($similarByPerformer) {
                        $data[$key]['event_url'] = $similarByPerformer->getGuestUrl();
                        $data[$key]['event_id'] = UrlUtils::encodeId($similarByPerformer->id);
                    }
                }
            }
        }


        return $data;
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

    public static function searchYouTube($query, $maxResults = 6)
    {
        // Always limit to maximum 6 videos for consistency
        $maxResults = min($maxResults, 6);
        
        // Validate query input
        if (empty($query) || strlen($query) > 100) {
            return [];
        }
        
        // Validate API key
        $apiKey = config('services.google.backend');
        if (!$apiKey) {
            return [];
        }
        
        // First, search for videos
        $searchUrl = "https://www.googleapis.com/youtube/v3/search"
            . "?key=" . $apiKey
            . "&q=" . urlencode($query)
            . "&type=video"
            . "&order=relevance"
            . "&maxResults=" . $maxResults
            . "&part=snippet"
            . "&regionCode=IL";        // Israel region
            
        // Use secure cURL instead of file_get_contents
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $searchUrl,
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
            \Log::error('Failed to search YouTube: ' . json_encode($response));
            return [];
        }
        
        $data = json_decode($response, true);

        if (!isset($data['items']) || !is_array($data['items'])) {
            return [];
        }
        
        $videos = [];
        $videoIds = [];
        
        // Extract video IDs and basic info
        foreach ($data['items'] as $item) {
            if (isset($item['id']['videoId']) && isset($item['snippet'])) {
                $videoId = $item['id']['videoId'];
                $snippet = $item['snippet'];
                
                // Validate video ID format
                if (preg_match('/^[a-zA-Z0-9_-]{11}$/', $videoId)) {
                    $videoIds[] = $videoId;
                    $videos[$videoId] = [
                        'id' => $videoId,
                        'title' => html_entity_decode($snippet['title'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                        'description' => html_entity_decode($snippet['description'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                        'channelTitle' => html_entity_decode($snippet['channelTitle'] ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                        'thumbnail' => $snippet['thumbnails']['medium']['url'] ?? null,
                        'url' => "https://www.youtube.com/watch?v=" . $videoId,
                        'publishedAt' => $snippet['publishedAt'] ?? null,
                        'viewCount' => 0,
                        'likeCount' => 0
                    ];
                }
            }
        }
        
        // If we have video IDs, try to get statistics
        if (!empty($videoIds)) {
            $videoIdsString = implode(',', $videoIds);
            $statsUrl = "https://www.googleapis.com/youtube/v3/videos"
                . "?key=" . $apiKey
                . "&id=" . $videoIdsString
                . "&part=statistics";
                
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $statsUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_CONNECTTIMEOUT => 5,
                CURLOPT_USERAGENT => 'EventSchedule/1.0',
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
                CURLOPT_PROTOCOLS => CURLPROTO_HTTPS,
                CURLOPT_MAXFILESIZE => 1048576,
            ]);
            
            $statsResponse = curl_exec($ch);
            $statsHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($statsResponse !== false && $statsHttpCode === 200) {
                $statsData = json_decode($statsResponse, true);
                
                if (isset($statsData['items']) && is_array($statsData['items'])) {
                    foreach ($statsData['items'] as $item) {
                        if (isset($item['id']) && isset($item['statistics'])) {
                            $videoId = $item['id'];
                            $statistics = $item['statistics'];
                            
                            if (isset($videos[$videoId])) {
                                $videos[$videoId]['viewCount'] = (int)($statistics['viewCount'] ?? 0);
                                $videos[$videoId]['likeCount'] = (int)($statistics['likeCount'] ?? 0);
                            }
                        }
                    }
                }
            }
        }

        // Sort videos by view count in descending order
        $sortedVideos = array_values($videos);
        usort($sortedVideos, function($a, $b) {
            return $b['viewCount'] - $a['viewCount'];
        });

        // Ensure we never return more than 6 videos
        return array_slice($sortedVideos, 0, 6);
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
            \"meta_description\": \"SEO meta description (150-160 characters)\",
            \"image_category\": \"business|wellness|sports|music|networking|family|productivity|nature|arts|general\"
        }
        
        Requirements:
        - Try not to seem like a robot
        - Never use em dashes 
        - Make the content engaging and informative
        - Include practical tips and actionable advice
        - Use proper HTML formatting with h1, h2, h3 tags
        - Include bullet points and numbered lists where appropriate
        - Make it relevant to event scheduling and ticketing
        - Ensure the content is original and valuable
        - Make it SEO-friendly with relevant keywords
        - Always maintain a professional tone
        - Add 1 or 2 links in the text where relevant to 'https://www.eventschedule.com' with 'Event Schedule' as the text
        - For image_category, choose the most appropriate category based on the topic:
          * business: for business, professional, corporate topics
          * wellness: for health, wellness, meditation, yoga topics
          * sports: for sports, fitness, athletics topics
          * music: for music, arts, entertainment topics
          * networking: for networking, social, community topics
          * family: for family, children, parenting topics
          * productivity: for productivity, time management, organization topics
          * nature: for outdoor, nature, environmental topics
          * arts: for creative, artistic, cultural topics
          * general: for general topics that don't fit other categories";

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
                $result['image_category'] = $result['image_category'] ?? 'general';
                
                // Select appropriate image based on category
                $result['featured_image'] = self::selectImageForCategory($result['image_category']);
                
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
                'meta_description' => 'A brief summary about ' . $topic,
                'featured_image' => self::selectImageForCategory('general')
            ];
        }
    }

    private static function selectImageForCategory($category)
    {
        $imageMap = [
            'business' => ['Lets_do_Business.png', 'Network_Summit.png', 'Synergy.png'],
            'wellness' => ['Yoga_and_Wellness.png', 'Peaceful_Studio.png', 'Meditation.png', 'Mindful.png'],
            'sports' => ['Sports_Centre.png', 'Fitness_Morning.png', 'Arena.png', 'Sports_and_Youth.png'],
            'music' => ['Music_Potential.png', 'The_Stage_Awaits.png', 'Ready_to_Dance.png'],
            'networking' => ['Network_Summit.png', 'Networking_and_Bagels.png', 'People_of_the_World.png'],
            'family' => ['Kids_Bonanza.png', 'Sports_and_Youth.png'],
            'productivity' => ['5am_Club.png', 'Chess_Vibrancy.png', 'Warming_Up.png'],
            'nature' => ['Nature_Calls.png', 'Flowerful_Life.png'],
            'arts' => ['Literature.png', 'Music_Potential.png', 'The_Stage_Awaits.png'],
            'general' => ['Lets_do_Business.png', 'All_Hands_on_Deck.png', 'Flowerful_Life.png', 'Chill_Evening.png']
        ];

        $filenames = $imageMap[$category] ?? $imageMap['general'];

        $assets = MediaAsset::query()
            ->where('folder', 'headers')
            ->whereIn('original_filename', $filenames)
            ->get();

        if ($assets->isEmpty()) {
            $assets = MediaAsset::query()
                ->where('folder', 'headers')
                ->get();
        }

        if ($assets->isEmpty()) {
            return null;
        }

        return $assets->random()->path;
    }

    
    
}   