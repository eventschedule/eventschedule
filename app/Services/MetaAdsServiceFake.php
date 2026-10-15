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

    // ---------------------------------------------------------------------------------------
    // Boost partners. Every call is recorded with the ad account and Page this instance was
    // built with, so a test can assert that a partner's campaign never went to the platform's
    // own Page. Set $failWith to make the next call throw that exception, and $adStates /
    // $dailySpend to script what Meta reports.
    // ---------------------------------------------------------------------------------------

    /** @var array<int, array{method: string, ad_account: string, page: string, token: string, args: array}> */
    public static array $calls = [];

    public static ?\Throwable $failWith = null;

    /** @var array<string, array{status: ?string, rejection_reason: ?string}> keyed by meta ad id, or '*' */
    public static array $adStates = [];

    /** @var array<string, float> Y-m-d => spend, for every campaign */
    public static array $dailySpend = [];

    public static array $sharedAssets = ['ad_account' => ['permitted_tasks' => ['MANAGE', 'ADVERTISE', 'ANALYZE']], 'page' => ['permitted_tasks' => ['ADVERTISE']]];

    public static array $accountDetails = ['name' => 'Test ad account', 'currency' => 'USD', 'timezone_name' => 'America/New_York', 'account_status' => 1, 'business' => ['name' => 'Test Business']];

    /** Run while an ad's state is being read: a test's way to land a Stop or an edit mid-sync. */
    public static ?\Closure $onAdState = null;

    /** Fail only calls made with this ad account (one partner failing while another answers). */
    public static ?string $failForAccount = null;

    public static function resetRecorder(): void
    {
        static::$calls = [];
        static::$failWith = null;
        static::$adStates = [];
        static::$dailySpend = [];
        static::$onAdState = null;
        static::$failForAccount = null;
    }

    protected function record(string $method, array $args = []): void
    {
        static::$calls[] = [
            'method' => $method,
            'ad_account' => $this->adAccountId,
            'page' => $this->pageId,
            'token' => $this->accessToken,
            'args' => $args,
        ];

        if (static::$failWith && static::$failForAccount !== null) {
            if ($this->adAccountId === static::$failForAccount) {
                throw static::$failWith;
            }

            return;
        }

        if (static::$failWith) {
            $e = static::$failWith;
            static::$failWith = null;
            throw $e;
        }
    }

    public function createPartnerCampaign(BoostCampaign $campaign, \App\Models\BoostAdVersion $version, ?callable $onCampaignCreated = null): array
    {
        $this->record('createPartnerCampaign', ['campaign' => $campaign->id, 'version' => $version->version]);

        $metaCampaignId = $campaign->meta_campaign_id ?: 'fake_campaign_'.Str::random(10);
        if (! $campaign->meta_campaign_id && $onCampaignCreated) {
            $onCampaignCreated($metaCampaignId);
        }
        $metaAdSetId = $campaign->meta_adset_id ?: 'fake_adset_'.Str::random(10);
        $campaign->forceFill(['meta_adset_id' => $metaAdSetId])->save();

        $ad = $campaign->ads()->first();
        if ($ad && ! $ad->meta_ad_id) {
            $ad->update(['meta_ad_id' => 'fake_ad_'.Str::random(10), 'meta_creative_id' => 'fake_creative_'.Str::random(10), 'status' => 'active']);
        }

        return ['meta_campaign_id' => $metaCampaignId, 'meta_adset_id' => $metaAdSetId];
    }

    public function updateCreative(BoostAd $ad): void
    {
        $this->record('updateCreative', ['ad' => $ad->id, 'headline' => $ad->headline]);
        $ad->update(['image_hash' => $ad->image_hash ?: 'fake_hash_'.Str::random(10), 'meta_creative_id' => 'fake_creative_'.Str::random(10)]);
    }

    public function updateAdSet(string $adSetId, ?float $lifetimeBudget = null, ?\Carbon\CarbonInterface $endTime = null, ?array $targeting = null, ?string $currency = null): void
    {
        $this->record('updateAdSet', ['adset' => $adSetId, 'budget' => $lifetimeBudget, 'end' => $endTime?->toIso8601String()]);
    }

    public function setStatus(string $objectId, string $status): void
    {
        $this->record('setStatus', ['id' => $objectId, 'status' => $status]);
    }

    public function adState(string $metaAdId): array
    {
        $this->record('adState', ['ad' => $metaAdId]);
        if (static::$onAdState) {
            (static::$onAdState)($metaAdId);
        }

        return static::$adStates[$metaAdId] ?? static::$adStates['*'] ?? ['status' => 'ACTIVE', 'rejection_reason' => null];
    }

    public function campaignTotals(BoostCampaign $campaign): ?array
    {
        $this->record('campaignTotals', ['campaign' => $campaign->id]);
        $spend = array_sum(static::$dailySpend);

        return ['impressions' => '1000', 'reach' => '700', 'clicks' => '20', 'ctr' => '2.0', 'cpc' => '0.45', 'cpm' => '5.50', 'spend' => number_format($spend, 2, '.', ''), 'actions' => []];
    }

    public function dailySpend(BoostCampaign $campaign, \Carbon\CarbonInterface $since, \Carbon\CarbonInterface $until): array
    {
        $this->record('dailySpend', ['campaign' => $campaign->id]);

        return array_filter(static::$dailySpend, fn ($day) => $day >= $since->format('Y-m-d') && $day <= $until->format('Y-m-d'), ARRAY_FILTER_USE_KEY);
    }

    public function adAccountDetails(): array
    {
        $this->record('adAccountDetails');

        return static::$accountDetails;
    }

    public function pageDetails(): array
    {
        $this->record('pageDetails');

        return ['name' => 'Test Page'];
    }

    public function assignSharedAssets(string $businessId, string $systemUserId): void
    {
        $this->record('assignSharedAssets', ['business' => $businessId]);
    }

    public function sharedAssetTasks(string $businessId): array
    {
        $this->record('sharedAssetTasks', ['business' => $businessId]);

        return static::$sharedAssets;
    }

    public function listOwnAssets(): array
    {
        $this->record('listOwnAssets');

        return [
            'pages' => [['id' => '111', 'name' => 'Test Page', 'tasks' => ['ADVERTISE']]],
            'ad_accounts' => [['id' => 'act_222', 'name' => 'Test ad account', 'currency' => 'USD', 'timezone_name' => 'America/New_York', 'account_status' => 1]],
        ];
    }
}
