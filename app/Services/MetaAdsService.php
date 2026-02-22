<?php

namespace App\Services;

use App\Models\BoostAd;
use App\Models\BoostCampaign;
use App\Models\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MetaAdsService
{
    protected string $baseUrl;

    protected string $accessToken;

    protected string $adAccountId;

    protected string $apiVersion;

    public function __construct()
    {
        $this->apiVersion = config('services.meta.api_version', 'v21.0');
        $this->baseUrl = "https://graph.facebook.com/{$this->apiVersion}";
        $this->accessToken = config('services.meta.access_token', '');
        $this->adAccountId = config('services.meta.ad_account_id', '');
    }

    public static function isBoostConfigured(): bool
    {
        return ! empty(config('services.meta.access_token'));
    }

    public function isConfigured(): bool
    {
        return ! empty($this->accessToken) && ! empty($this->adAccountId);
    }

    /**
     * Create a full campaign (Campaign + AdSet + Ad) on Meta
     */
    public function createCampaign(BoostCampaign $campaign): array
    {
        $metaCampaignId = null;

        try {
            // 1. Create Campaign
            $campaignResponse = $this->apiPost("act_{$this->adAccountId}/campaigns", [
                'name' => $campaign->name,
                'objective' => $campaign->objective,
                'status' => 'PAUSED',
                'special_ad_categories' => [],
            ]);

            $metaCampaignId = $campaignResponse['id'];

            // 2. Create Ad Set
            $adSetData = [
                'name' => $campaign->name.' - Ad Set',
                'campaign_id' => $metaCampaignId,
                'billing_event' => 'IMPRESSIONS',
                'optimization_goal' => $this->getOptimizationGoal($campaign->objective),
                'targeting' => json_encode($campaign->targeting ?? $this->getDefaultTargeting()),
                'status' => 'PAUSED',
            ];

            if ($campaign->budget_type === 'daily') {
                $adSetData['daily_budget'] = (int) round($campaign->daily_budget * 100);
            } else {
                $adSetData['lifetime_budget'] = (int) round($campaign->lifetime_budget * 100);
            }

            if ($campaign->scheduled_start) {
                $adSetData['start_time'] = $campaign->scheduled_start->toIso8601String();
            }
            if ($campaign->scheduled_end) {
                $adSetData['end_time'] = $campaign->scheduled_end->toIso8601String();
            }

            if ($campaign->placements) {
                $existing = json_decode($adSetData['targeting'], true);
                $existing['publisher_platforms'] = $campaign->placements;
                $adSetData['targeting'] = json_encode($existing);
            }

            $adSetResponse = $this->apiPost("act_{$this->adAccountId}/adsets", $adSetData);
            $metaAdSetId = $adSetResponse['id'];

            // 3. Create Ad(s)
            $adIds = [];
            foreach ($campaign->ads as $ad) {
                $adId = $this->createAd($ad, $metaAdSetId);
                $adIds[] = $adId;
            }

            // 4. Activate campaign
            $this->apiPost($metaCampaignId, [
                'status' => 'ACTIVE',
            ]);

            // Also activate the ad set
            $this->apiPost($metaAdSetId, [
                'status' => 'ACTIVE',
            ]);

            return [
                'meta_campaign_id' => $metaCampaignId,
                'meta_adset_id' => $metaAdSetId,
                'ad_ids' => $adIds,
            ];
        } catch (\Exception $e) {
            // Clean up the orphaned Meta campaign (cascades to adsets/ads)
            if ($metaCampaignId) {
                try {
                    $this->apiDelete($metaCampaignId);
                } catch (\Exception $cleanupError) {
                    Log::warning('Meta Ads: Failed to clean up orphaned campaign', [
                        'campaign_id' => $campaign->id,
                        'meta_campaign_id' => $metaCampaignId,
                        'error' => $cleanupError->getMessage(),
                    ]);
                }
            }

            Log::error('Meta Ads: Failed to create campaign', [
                'campaign_id' => $campaign->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Create a single ad within an ad set
     */
    protected function createAd(BoostAd $ad, string $adSetId): string
    {
        // Upload image if needed
        $imageHash = $ad->image_hash;
        if (! $imageHash && $ad->image_url) {
            $imageHash = $this->uploadImage($ad->image_url);
            if (! $imageHash) {
                throw new \Exception('Failed to upload ad image');
            }
            $ad->update(['image_hash' => $imageHash]);
        }

        if (! $imageHash) {
            throw new \Exception('Ad has no image: neither image_hash nor image_url is set');
        }

        // Create creative
        $creativeData = [
            'name' => $ad->headline.' - Creative',
            'object_story_spec' => json_encode([
                'page_id' => config('services.meta.page_id'),
                'link_data' => [
                    'message' => $ad->primary_text,
                    'link' => $ad->destination_url,
                    'name' => $ad->headline,
                    'description' => $ad->description,
                    'call_to_action' => [
                        'type' => $ad->call_to_action,
                    ],
                    'image_hash' => $imageHash,
                ],
            ]),
        ];

        $creativeResponse = $this->apiPost("act_{$this->adAccountId}/adcreatives", $creativeData);
        $metaCreativeId = $creativeResponse['id'];

        // Create ad
        $adResponse = $this->apiPost("act_{$this->adAccountId}/ads", [
            'name' => $ad->headline,
            'adset_id' => $adSetId,
            'creative' => json_encode(['creative_id' => $metaCreativeId]),
            'status' => 'ACTIVE',
        ]);

        $ad->update([
            'meta_ad_id' => $adResponse['id'],
            'meta_creative_id' => $metaCreativeId,
            'status' => 'active',
        ]);

        return $adResponse['id'];
    }

    /**
     * Upload an image to Meta's ad image library
     */
    public function uploadImage(string $imageUrl): ?string
    {
        try {
            // Download the image
            $imageResponse = Http::timeout(30)->get($imageUrl);
            if (! $imageResponse->successful()) {
                Log::error('Meta Ads: Failed to download image', [
                    'image_url' => $imageUrl,
                    'status' => $imageResponse->status(),
                ]);

                return null;
            }
            $imageContents = $imageResponse->body();
            $tempPath = storage_path('app/temp/boost_'.uniqid().'.jpg');

            $tempDir = dirname($tempPath);
            if (! is_dir($tempDir)) {
                mkdir($tempDir, 0700, true);
            }

            file_put_contents($tempPath, $imageContents);

            // Resize to 1080x1080 for feed
            $this->resizeImage($tempPath, 1080, 1080);

            // Upload to Meta
            $response = Http::withToken($this->accessToken)
                ->attach('filename', file_get_contents($tempPath), 'boost_image.jpg')
                ->post("{$this->baseUrl}/act_{$this->adAccountId}/adimages");

            // Clean up temp file
            @unlink($tempPath);

            $data = $response->json();
            if (isset($data['images'])) {
                $imageData = reset($data['images']);

                return $imageData['hash'] ?? null;
            }

            return null;
        } catch (\Exception $e) {
            if (isset($tempPath) && file_exists($tempPath)) {
                @unlink($tempPath);
            }
            Log::error('Meta Ads: Failed to upload image', [
                'image_url' => $imageUrl,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Resize an image to fit Meta ad specs (center crop)
     */
    protected function resizeImage(string $path, int $width, int $height): void
    {
        $info = getimagesize($path);
        if (! $info) {
            return;
        }

        $sourceWidth = $info[0];
        $sourceHeight = $info[1];
        $mime = $info['mime'];

        $source = match ($mime) {
            'image/jpeg' => imagecreatefromjpeg($path),
            'image/png' => imagecreatefrompng($path),
            'image/webp' => imagecreatefromwebp($path),
            default => null,
        };

        if (! $source) {
            return;
        }

        // Center crop to square
        $cropSize = min($sourceWidth, $sourceHeight);
        $cropX = ($sourceWidth - $cropSize) / 2;
        $cropY = ($sourceHeight - $cropSize) / 2;

        $dest = imagecreatetruecolor($width, $height);
        imagecopyresampled($dest, $source, 0, 0, (int) $cropX, (int) $cropY, $width, $height, $cropSize, $cropSize);
        imagejpeg($dest, $path, 90);

        imagedestroy($source);
        imagedestroy($dest);
    }

    /**
     * Pause a campaign on Meta
     */
    public function pauseCampaign(BoostCampaign $campaign): bool
    {
        try {
            $this->apiPost($campaign->meta_campaign_id, ['status' => 'PAUSED']);

            return true;
        } catch (\Exception $e) {
            Log::error('Meta Ads: Failed to pause campaign', [
                'campaign_id' => $campaign->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Resume a paused campaign on Meta
     */
    public function resumeCampaign(BoostCampaign $campaign): bool
    {
        try {
            $this->apiPost($campaign->meta_campaign_id, ['status' => 'ACTIVE']);

            return true;
        } catch (\Exception $e) {
            Log::error('Meta Ads: Failed to resume campaign', [
                'campaign_id' => $campaign->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Delete a campaign on Meta
     */
    public function deleteCampaign(BoostCampaign $campaign): bool
    {
        try {
            $this->apiDelete($campaign->meta_campaign_id);

            return true;
        } catch (\Exception $e) {
            Log::error('Meta Ads: Failed to delete campaign', [
                'campaign_id' => $campaign->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Fetch campaign-level insights
     */
    public function fetchCampaignInsights(BoostCampaign $campaign): ?array
    {
        try {
            $response = $this->apiGet("{$campaign->meta_campaign_id}/insights", [
                'fields' => 'impressions,reach,clicks,ctr,cpc,cpm,spend,actions',
                'date_preset' => 'maximum',
            ]);

            return $response['data'][0] ?? null;
        } catch (\Exception $e) {
            Log::error('Meta Ads: Failed to fetch campaign insights', [
                'campaign_id' => $campaign->id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Fetch per-ad insights
     */
    public function fetchAdInsights(BoostAd $ad): ?array
    {
        try {
            $response = $this->apiGet("{$ad->meta_ad_id}/insights", [
                'fields' => 'impressions,reach,clicks,ctr,spend',
            ]);

            return $response['data'][0] ?? null;
        } catch (\Exception $e) {
            Log::error('Meta Ads: Failed to fetch ad insights', [
                'ad_id' => $ad->id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Check the status of a campaign on Meta (detect rejections)
     */
    public function checkAdStatus(BoostCampaign $campaign): array
    {
        try {
            $response = $this->apiGet($campaign->meta_campaign_id, [
                'fields' => 'status,effective_status',
            ]);

            $statuses = [];
            foreach ($campaign->ads as $ad) {
                if (! $ad->meta_ad_id) {
                    continue;
                }
                $adResponse = $this->apiGet($ad->meta_ad_id, [
                    'fields' => 'status,effective_status,ad_review_feedback',
                ]);
                $statuses[] = [
                    'id' => $ad->meta_ad_id,
                    'status' => $adResponse['effective_status'] ?? $adResponse['status'] ?? null,
                    'rejection_reason' => $adResponse['ad_review_feedback']['global']['review_feedback'] ?? null,
                ];
            }

            return [
                'campaign_status' => $response['effective_status'] ?? $response['status'] ?? null,
                'ad_statuses' => $statuses,
            ];
        } catch (\Exception $e) {
            Log::error('Meta Ads: Failed to check ad status', [
                'campaign_id' => $campaign->id,
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Generate Quick Boost defaults based on event data
     */
    public function generateQuickBoostDefaults(Event $event): array
    {
        $event->loadMissing(['roles', 'tickets']);
        $venue = $event->venue;
        $hasEventUrl = ! empty($event->event_url);
        $hasVenue = $venue !== null;

        // Determine event type
        if ($hasVenue && $hasEventUrl) {
            $eventType = 'hybrid';
        } elseif ($hasEventUrl) {
            $eventType = 'online';
        } else {
            $eventType = 'in_person';
        }

        // Targeting
        $targeting = $this->buildTargeting($event, $eventType, $venue);

        // Creative
        $creative = $this->buildCreative($event, $eventType, $venue);

        // Budget suggestion
        $budget = $this->suggestBudget($event, $eventType);

        // Duration
        $duration = $this->calculateDuration($event);

        // CTA
        $cta = $this->suggestCta($event);

        // Destination URL
        $destinationUrl = $event->getGuestUrl(false, null, true);

        return [
            'event_type' => $eventType,
            'targeting' => $targeting,
            'headline' => $creative['headline'],
            'primary_text' => $creative['primary_text'],
            'description' => $creative['description'],
            'call_to_action' => $cta,
            'budget' => $budget,
            'duration_days' => $duration['days'],
            'scheduled_start' => $duration['start'],
            'scheduled_end' => $duration['end'],
            'destination_url' => $destinationUrl,
            'image_url' => $event->getImageUrl(),
            'warnings' => $this->getWarnings($event, $eventType, $duration),
        ];
    }

    protected function buildTargeting(Event $event, string $eventType, $venue): array
    {
        $targeting = [
            'age_min' => 18,
            'age_max' => 65,
        ];

        if ($eventType === 'in_person' || $eventType === 'hybrid') {
            if ($venue && $venue->geo_lat && $venue->geo_lon) {
                $targeting['geo_locations'] = [
                    'custom_locations' => [
                        [
                            'latitude' => (float) $venue->geo_lat,
                            'longitude' => (float) $venue->geo_lon,
                            'radius' => 25,
                            'distance_unit' => 'mile',
                        ],
                    ],
                ];
            } elseif ($venue && $venue->country_code) {
                $targeting['geo_locations'] = [
                    'countries' => [strtoupper($venue->country_code)],
                ];
            }
        } else {
            // Online event: country-level or worldwide
            $role = $event->roles->first();
            if ($role && $role->country_code) {
                $targeting['geo_locations'] = [
                    'countries' => [strtoupper($role->country_code)],
                ];
            } elseif ($role && $role->language_code === 'en') {
                $targeting['geo_locations'] = [
                    'countries' => ['US', 'GB', 'CA', 'AU'],
                ];
            }
        }

        // Fallback: Meta requires geo_locations in targeting
        if (empty($targeting['geo_locations'])) {
            $targeting['geo_locations'] = [
                'location_types' => ['home'],
                'countries' => ['US', 'GB', 'CA', 'AU'],
            ];
        }

        // Infer interests from event category
        $interests = $this->inferInterests($event);
        if (! empty($interests)) {
            $targeting['interests'] = $interests;
        }

        return $targeting;
    }

    protected function buildCreative(Event $event, string $eventType, $venue): array
    {
        $eventName = $event->translatedName();
        $venueName = $venue ? $venue->translatedName() : '';
        $city = $venue ? $venue->translatedCity() : '';

        switch ($eventType) {
            case 'online':
                $platformHint = '';
                if ($event->event_url) {
                    $domain = $event->getEventUrlDomain();
                    if (str_contains($domain, 'youtube')) {
                        $platformHint = ' on YouTube';
                    } elseif (str_contains($domain, 'zoom')) {
                        $platformHint = ' on Zoom';
                    }
                }

                return [
                    'headline' => mb_substr(__('messages.boost_headline_online', ['name' => $eventName]), 0, 40),
                    'primary_text' => mb_substr(__('messages.boost_text_online', ['name' => $eventName, 'platform' => $platformHint]), 0, 125),
                    'description' => mb_substr(__('messages.boost_desc_online'), 0, 30),
                ];

            case 'hybrid':
                return [
                    'headline' => mb_substr($eventName, 0, 40),
                    'primary_text' => mb_substr(__('messages.boost_text_hybrid', ['name' => $eventName, 'venue' => $venueName]), 0, 125),
                    'description' => mb_substr($city ?: $venueName, 0, 30),
                ];

            default: // in_person
                $location = $city ? "{$venueName}, {$city}" : $venueName;

                return [
                    'headline' => mb_substr(__('messages.boost_headline_in_person', ['name' => $eventName, 'location' => $location]), 0, 40),
                    'primary_text' => mb_substr(__('messages.boost_text_in_person', ['name' => $eventName, 'venue' => $venueName]), 0, 125),
                    'description' => mb_substr($location ?: __('messages.boost_desc_live_event'), 0, 30),
                ];
        }
    }

    protected function suggestBudget(Event $event, string $eventType): float
    {
        $isTicketed = $event->tickets_enabled && $event->tickets->isNotEmpty();
        $ticketPrice = $isTicketed ? $event->tickets->min('price') : 0;

        if ($eventType === 'online') {
            $base = $isTicketed ? min(35, $ticketPrice * 1.5) : 15;

            return max(config('services.meta.min_budget'), min($base, 300));
        }

        $base = $isTicketed ? min(50, $ticketPrice * 2) : 25;

        return max(config('services.meta.min_budget'), min($base, 500));
    }

    protected function calculateDuration(Event $event): array
    {
        $now = now();
        $start = $now->copy();

        if ($event->starts_at) {
            $eventDate = \Carbon\Carbon::parse($event->starts_at);
            $daysUntilEvent = $now->diffInDays($eventDate, false);

            if ($daysUntilEvent <= 0) {
                // Event already happened
                $end = $now->copy()->addDays(3);
            } else {
                $end = $eventDate->copy();
            }

            $days = (int) max(3, min(14, $start->diffInDays($end)));
            $end = $start->copy()->addDays($days);
        } else {
            $days = 7;
            $end = $start->copy()->addDays($days);
        }

        return [
            'days' => $days,
            'start' => $start,
            'end' => $end,
        ];
    }

    protected function suggestCta(Event $event): string
    {
        $isTicketed = $event->tickets_enabled && $event->tickets->isNotEmpty();
        $isOnline = ! empty($event->event_url) && ! $event->venue;

        if ($isTicketed) {
            return $isOnline ? 'SIGN_UP' : 'GET_TICKETS';
        }

        return 'LEARN_MORE';
    }

    protected function getWarnings(Event $event, string $eventType, array $duration): array
    {
        $warnings = [];

        if (! $event->venue && ! $event->event_url) {
            $warnings[] = __('messages.boost_warning_no_location');
        }

        if (! $event->getImageUrl()) {
            $warnings[] = __('messages.boost_warning_no_image');
        }

        if (! $event->description) {
            $warnings[] = __('messages.boost_warning_no_description');
        }

        if ($event->starts_at) {
            $hoursUntil = now()->diffInHours(\Carbon\Carbon::parse($event->starts_at), false);
            if ($hoursUntil > 0 && $hoursUntil < 24) {
                $warnings[] = __('messages.boost_warning_too_soon');
            } elseif ($hoursUntil > 90 * 24) {
                $warnings[] = __('messages.boost_warning_too_far');
            }
        }

        if ($eventType === 'online' && ! $event->tickets_enabled) {
            $warnings[] = __('messages.boost_warning_no_tickets_online');
        }

        return $warnings;
    }

    protected function inferInterests(Event $event): array
    {
        $categoryMap = [
            1 => [['id' => '6003139266461', 'name' => 'Music']],
            2 => [['id' => '6003384593206', 'name' => 'Comedy']],
            3 => [['id' => '6003316788671', 'name' => 'Theater']],
            4 => [['id' => '6003397425735', 'name' => 'Dance']],
            5 => [['id' => '6003020834693', 'name' => 'Art']],
            6 => [['id' => '6003107902433', 'name' => 'Food and drink']],
            7 => [['id' => '6003013517882', 'name' => 'Nightlife']],
            8 => [['id' => '6003659420716', 'name' => 'Fitness and wellness']],
            9 => [['id' => '6003348604030', 'name' => 'Education']],
            10 => [['id' => '6003012621273', 'name' => 'Technology']],
        ];

        return $categoryMap[$event->category_id] ?? [];
    }

    /**
     * Search Meta interests (for advanced targeting)
     */
    public function searchInterests(string $query): array
    {
        try {
            $response = $this->apiGet('search', [
                'type' => 'adinterest',
                'q' => $query,
            ]);

            return $response['data'] ?? [];
        } catch (\Exception $e) {
            Log::error('Meta Ads: Interest search failed', ['error' => $e->getMessage()]);

            return [];
        }
    }

    /**
     * Estimate reach for given targeting
     */
    public function estimateReach(array $targeting): ?array
    {
        try {
            $response = $this->apiGet("act_{$this->adAccountId}/reachestimate", [
                'targeting_spec' => json_encode($targeting),
            ]);

            return $response['data'] ?? null;
        } catch (\Exception $e) {
            Log::error('Meta Ads: Reach estimation failed', ['error' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * Send a Conversions API event to Meta
     */
    public function sendConversionEvent(string $eventName, array $eventData, ?string $pixelId = null): bool
    {
        $pixelId = $pixelId ?? config('services.meta.pixel_id');
        if (! $pixelId || ! $this->accessToken) {
            return false;
        }

        try {
            $response = Http::timeout(30)->withToken($this->accessToken)
                ->post("{$this->baseUrl}/{$pixelId}/events", [
                    'data' => [
                        [
                            'event_name' => $eventName,
                            'event_time' => time(),
                            'action_source' => 'website',
                            'event_id' => $eventData['event_id'] ?? uniqid('es_'),
                            'user_data' => $eventData['user_data'] ?? [],
                            'custom_data' => $eventData['custom_data'] ?? [],
                        ],
                    ],
                ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Meta CAPI: Failed to send event', [
                'event_name' => $eventName,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    protected function getOptimizationGoal(string $objective): string
    {
        return match ($objective) {
            'OUTCOME_TRAFFIC' => 'LINK_CLICKS',
            'OUTCOME_ENGAGEMENT' => 'POST_ENGAGEMENT',
            default => 'REACH',
        };
    }

    protected function getDefaultTargeting(): array
    {
        return [
            'age_min' => 18,
            'age_max' => 65,
            'geo_locations' => [
                'countries' => ['US'],
            ],
        ];
    }

    /**
     * API GET request
     */
    protected function apiGet(string $endpoint, array $params = []): array
    {
        $response = Http::withToken($this->accessToken)->timeout(30)->get("{$this->baseUrl}/{$endpoint}", $params);

        if (! $response->successful()) {
            $error = $response->json('error.message', 'Unknown error');
            throw new \Exception("Meta API error: {$error}");
        }

        return $response->json();
    }

    /**
     * API POST request
     */
    protected function apiPost(string $endpoint, array $data = []): array
    {
        $response = Http::withToken($this->accessToken)->timeout(30)->post("{$this->baseUrl}/{$endpoint}", $data);

        if (! $response->successful()) {
            $error = $response->json('error.message', 'Unknown error');
            throw new \Exception("Meta API error: {$error}");
        }

        return $response->json();
    }

    /**
     * API DELETE request
     */
    protected function apiDelete(string $endpoint): array
    {
        $response = Http::withToken($this->accessToken)->timeout(30)->delete("{$this->baseUrl}/{$endpoint}");

        if (! $response->successful()) {
            $error = $response->json('error.message', 'Unknown error');
            throw new \Exception("Meta API error: {$error}");
        }

        return $response->json();
    }
}
