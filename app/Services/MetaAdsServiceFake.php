<?php

namespace App\Services;

use App\Models\BoostAd;
use App\Models\BoostCampaign;
use Illuminate\Support\Str;

class MetaAdsServiceFake extends MetaAdsService
{
    public function isConfigured(): bool
    {
        return true;
    }

    public function createCampaign(BoostCampaign $campaign): array
    {
        $adIds = [];
        foreach ($campaign->ads as $ad) {
            $fakeAdId = 'fake_ad_'.Str::random(10);
            $ad->update([
                'meta_ad_id' => $fakeAdId,
                'meta_creative_id' => 'fake_creative_'.Str::random(10),
                'status' => 'active',
            ]);
            $adIds[] = $fakeAdId;
        }

        return [
            'meta_campaign_id' => 'fake_campaign_'.Str::random(10),
            'meta_adset_id' => 'fake_adset_'.Str::random(10),
            'ad_ids' => $adIds,
        ];
    }

    public function pauseCampaign(BoostCampaign $campaign): bool
    {
        return true;
    }

    public function resumeCampaign(BoostCampaign $campaign): bool
    {
        return true;
    }

    public function deleteCampaign(BoostCampaign $campaign): bool
    {
        return true;
    }

    public function uploadImage(string $imageUrl): ?string
    {
        return 'fake_hash_'.Str::random(10);
    }

    public function fetchCampaignInsights(BoostCampaign $campaign): ?array
    {
        $daysRunning = max(1, $campaign->created_at->diffInDays(now()));
        $baseImpressions = $daysRunning * rand(200, 800);

        return [
            'impressions' => (string) $baseImpressions,
            'reach' => (string) round($baseImpressions * 0.7),
            'clicks' => (string) round($baseImpressions * 0.02),
            'ctr' => number_format($baseImpressions > 0 ? 2.0 : 0, 4),
            'cpc' => number_format(0.45, 2),
            'cpm' => number_format(5.50, 2),
            'spend' => number_format(min($campaign->user_budget * 0.6, $daysRunning * 5), 2),
            'actions' => [
                ['action_type' => 'offsite_conversion', 'value' => (string) rand(0, 5)],
            ],
        ];
    }

    public function fetchAdInsights(BoostAd $ad): ?array
    {
        return [
            'impressions' => (string) rand(100, 500),
            'reach' => (string) rand(70, 350),
            'clicks' => (string) rand(2, 20),
            'ctr' => number_format(rand(10, 50) / 10, 4),
            'spend' => number_format(rand(1, 10), 2),
        ];
    }

    public function checkAdStatus(BoostCampaign $campaign): array
    {
        return [
            'campaign_status' => 'ACTIVE',
            'ad_statuses' => [],
        ];
    }

    public function searchInterests(string $query): array
    {
        return [
            ['id' => '6003139266461', 'name' => 'Music', 'audience_size' => 1200000000],
            ['id' => '6003384593206', 'name' => 'Live events', 'audience_size' => 800000000],
            ['id' => '6003348604030', 'name' => 'Entertainment', 'audience_size' => 900000000],
        ];
    }

    public function estimateReach(array $targeting): ?array
    {
        return [
            'users' => rand(50000, 500000),
            'estimate_ready' => true,
        ];
    }

    public function sendConversionEvent(string $eventName, array $eventData, ?string $pixelId = null): bool
    {
        return true;
    }
}
