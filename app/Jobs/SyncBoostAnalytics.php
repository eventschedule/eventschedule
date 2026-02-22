<?php

namespace App\Jobs;

use App\Models\BoostCampaign;
use App\Services\MetaAdsService;
use App\Services\MetaAdsServiceFake;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SyncBoostAnalytics implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    protected BoostCampaign $campaign;

    public $tries = 3;

    public $backoff = [60, 300, 900];

    public $timeout = 300;

    public $deleteWhenMissingModels = true;

    public $uniqueFor = 300;

    public function __construct(BoostCampaign $campaign)
    {
        $this->campaign = $campaign;
    }

    public function uniqueId(): string
    {
        return 'sync-boost-analytics-'.$this->campaign->id;
    }

    public function handle(): void
    {
        $campaign = $this->campaign;
        $campaign->loadMissing('ads');

        $metaService = $this->getMetaService();

        try {
            // Fetch campaign-level insights
            $insights = $metaService->fetchCampaignInsights($campaign);

            if ($insights) {
                $conversions = 0;
                if (isset($insights['actions'])) {
                    foreach ($insights['actions'] as $action) {
                        if ($action['action_type'] === 'offsite_conversion') {
                            $conversions = (int) $action['value'];
                            break;
                        }
                    }
                }

                $campaign->update([
                    'impressions' => (int) ($insights['impressions'] ?? 0),
                    'reach' => (int) ($insights['reach'] ?? 0),
                    'clicks' => (int) ($insights['clicks'] ?? 0),
                    'ctr' => (float) ($insights['ctr'] ?? 0),
                    'cpc' => (float) ($insights['cpc'] ?? 0),
                    'cpm' => (float) ($insights['cpm'] ?? 0),
                    'actual_spend' => (float) ($insights['spend'] ?? 0),
                    'conversions' => $conversions,
                    'analytics_synced_at' => now(),
                ]);
            }

            // Fetch per-ad insights
            foreach ($campaign->ads as $ad) {
                if (! $ad->meta_ad_id) {
                    continue;
                }

                $adInsights = $metaService->fetchAdInsights($ad);
                if ($adInsights) {
                    $ad->update([
                        'impressions' => (int) ($adInsights['impressions'] ?? 0),
                        'reach' => (int) ($adInsights['reach'] ?? 0),
                        'clicks' => (int) ($adInsights['clicks'] ?? 0),
                        'spend' => (float) ($adInsights['spend'] ?? 0),
                        'ctr' => (float) ($adInsights['ctr'] ?? 0),
                    ]);
                }
            }

            // Update daily analytics
            $dailyAnalytics = $campaign->daily_analytics ?? [];
            $today = now()->format('Y-m-d');

            // Get the most recent day before today to compute today's delta
            $dates = array_keys($dailyAnalytics);
            sort($dates);
            $previousDay = null;
            foreach (array_reverse($dates) as $date) {
                if ($date !== $today) {
                    $previousDay = $date;
                    break;
                }
            }
            $previousValues = $previousDay ? $dailyAnalytics[$previousDay] : null;

            $dailyAnalytics[$today] = [
                'impressions' => max(0, $campaign->impressions - ($previousValues['cumulative_impressions'] ?? 0)),
                'reach' => max(0, $campaign->reach - ($previousValues['cumulative_reach'] ?? 0)),
                'clicks' => max(0, $campaign->clicks - ($previousValues['cumulative_clicks'] ?? 0)),
                'spend' => max(0, (float) $campaign->actual_spend - ($previousValues['cumulative_spend'] ?? 0)),
                'conversions' => max(0, $campaign->conversions - ($previousValues['cumulative_conversions'] ?? 0)),
                'cumulative_impressions' => $campaign->impressions,
                'cumulative_reach' => $campaign->reach,
                'cumulative_clicks' => $campaign->clicks,
                'cumulative_spend' => (float) $campaign->actual_spend,
                'cumulative_conversions' => $campaign->conversions,
            ];
            $campaign->update(['daily_analytics' => $dailyAnalytics]);

        } catch (\Exception $e) {
            Log::error('Failed to sync boost analytics', [
                'campaign_id' => $campaign->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    protected function getMetaService(): MetaAdsService
    {
        if (! MetaAdsService::isBoostConfigured()) {
            return new MetaAdsServiceFake;
        }

        return new MetaAdsService;
    }
}
