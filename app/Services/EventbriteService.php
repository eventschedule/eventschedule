<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EventbriteService
{
    protected string $baseUrl = 'https://www.eventbriteapi.com/v3';

    protected string $token;

    public function __construct(string $token)
    {
        $this->token = $token;
    }

    /**
     * Validate token and get current user info
     */
    public function getMe(): array
    {
        return $this->apiGet('users/me');
    }

    /**
     * Get all events for an organization with auto-pagination
     */
    public function getOrganizationEvents(string $orgId): array
    {
        $allEvents = [];
        $page = 1;
        $maxEvents = 500;

        do {
            $response = $this->apiGet("organizations/{$orgId}/events", [
                'expand' => 'venue,ticket_classes,logo',
                'page_size' => 50,
                'page' => $page,
            ]);

            $events = $response['events'] ?? [];
            $allEvents = array_merge($allEvents, $events);

            $hasMore = $response['pagination']['has_more_items'] ?? false;
            $page++;
        } while ($hasMore && count($allEvents) < $maxEvents && $page <= 20);

        return array_slice($allEvents, 0, $maxEvents);
    }

    /**
     * Get full HTML description for an event
     */
    public function getEventDescription(string $eventId): string
    {
        $response = $this->apiGet("events/{$eventId}/description");

        return $response['description'] ?? '';
    }

    /**
     * Download an image to temp storage
     */
    public function downloadImage(string $imageUrl): ?string
    {
        try {
            $parsed = parse_url($imageUrl);
            if (! in_array($parsed['scheme'] ?? '', ['http', 'https'])) {
                return null;
            }

            $host = $parsed['host'] ?? '';
            $ip = gethostbyname($host);
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
                return null;
            }

            $response = Http::timeout(30)->get($imageUrl);
            if (! $response->successful()) {
                return null;
            }

            // Validate file size (5MB limit, matching UrlUtils::downloadImageSecurely)
            if (strlen($response->body()) > 5 * 1024 * 1024) {
                return null;
            }

            // Validate content type
            $contentType = trim(explode(';', $response->header('Content-Type') ?? '')[0]);
            if (! in_array($contentType, ['image/jpeg', 'image/png', 'image/gif', 'image/webp'])) {
                return null;
            }

            // Validate actual image data
            if (getimagesizefromstring($response->body()) === false) {
                return null;
            }

            $tempDir = storage_path('app/temp');
            if (! is_dir($tempDir)) {
                mkdir($tempDir, 0700, true);
            }

            $extension = strtolower(pathinfo(parse_url($imageUrl, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg');
            if (! in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                $extension = 'jpg';
            }
            $filename = 'eventbrite_'.uniqid().'.'.$extension;
            $path = $tempDir.'/'.$filename;

            file_put_contents($path, $response->body());

            return $filename;
        } catch (\Exception $e) {
            Log::error('Eventbrite: Failed to download image', [
                'url' => $imageUrl,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Map Eventbrite category ID to app category ID
     */
    public static function mapCategory(?string $eventbriteCategoryId): ?int
    {
        if (! $eventbriteCategoryId) {
            return null;
        }

        // Eventbrite category IDs mapped to app categories (1-12)
        $map = [
            '103' => 1,   // Music -> Art & Culture
            '105' => 1,   // Performing & Visual Arts -> Art & Culture
            '101' => 2,   // Business & Professional -> Business Networking
            '113' => 3,   // Community & Culture -> Community
            '104' => 4,   // Film, Media & Entertainment -> Concerts
            '110' => 5,   // Science & Technology -> Education
            '111' => 5,   // Travel & Outdoor -> Education
            '109' => 5,   // Hobbies & Special Interest -> Education
            '108' => 6,   // Food & Drink -> Food & Drink
            '107' => 7,   // Health & Wellness -> Health & Fitness
            '106' => 7,   // Sports & Fitness -> Health & Fitness
            '115' => 8,   // Family & Education -> Parties & Festivals
            '116' => 8,   // Seasonal & Holiday -> Parties & Festivals
            '114' => 9,   // Religion & Spirituality -> Personal Growth
            '102' => 10,  // Science & Tech -> Sports (fallback)
            '112' => 11,  // Charity & Causes -> Spirituality
            '199' => null, // Other
        ];

        return $map[$eventbriteCategoryId] ?? null;
    }

    /**
     * Get organizations for a user
     */
    public function getOrganizations(string $userId): array
    {
        $response = $this->apiGet("users/{$userId}/organizations");

        return $response['organizations'] ?? [];
    }

    /**
     * API GET request
     */
    protected function apiGet(string $endpoint, array $params = []): array
    {
        $response = Http::withToken($this->token)
            ->timeout(30)
            ->get("{$this->baseUrl}/{$endpoint}/", $params);

        if (! $response->successful()) {
            $error = $response->json('error_description', $response->json('error', 'Unknown error'));
            throw new \Exception($error);
        }

        return $response->json();
    }
}
