<?php

namespace App\Jobs;

use App\Mail\BoostCompleted;
use App\Models\BoostCampaign;
use App\Services\BoostBillingService;
use App\Services\MetaAdsService;
use App\Services\MetaAdsServiceFake;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ReconcileBoostCampaign implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    protected BoostCampaign $campaign;

    public $tries = 3;

    public $backoff = [300, 3600, 14400];

    public $deleteWhenMissingModels = true;

    public $uniqueFor = 86400;

    public function __construct(BoostCampaign $campaign)
    {
        $this->campaign = $campaign;
    }

    public function uniqueId(): string
    {
        return 'reconcile-boost-'.$this->campaign->id;
    }

    public function handle(): void
    {
        $campaign = $this->campaign;
        $campaign->refresh();
        $campaign->loadMissing(['event', 'user', 'ads']);

        if ($campaign->billing_status === 'refunded' || $campaign->billing_status === 'partially_refunded') {
            return;
        }

        if ($campaign->status !== 'completed') {
            return;
        }

        // Fetch final spend data from Meta
        $metaService = $this->getMetaService();
        $insights = $metaService->fetchCampaignInsights($campaign);

        if ($insights) {
            $campaign->update([
                'actual_spend' => (float) ($insights['spend'] ?? $campaign->actual_spend ?? 0),
                'analytics_synced_at' => now(),
            ]);
        }

        // Refund unspent budget (billing service handles its own transaction/locking)
        $campaign->refresh();
        if (! in_array($campaign->billing_status, ['refunded', 'partially_refunded'])) {
            $billingService = new BoostBillingService;
            if (! $billingService->refundUnspent($campaign)) {
                Log::warning('Boost reconciliation refund failed', [
                    'campaign_id' => $campaign->id,
                ]);
            }
        }

        $campaign->refresh();

        // Send completion email if still completed (not cancelled during reconciliation)
        if ($campaign->status === 'completed') {
            try {
                Mail::to($campaign->user->email)
                    ->send(new BoostCompleted($campaign));
            } catch (\Exception $e) {
                Log::warning('Failed to send boost completed email', [
                    'campaign_id' => $campaign->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info('Boost campaign reconciled', [
            'campaign_id' => $campaign->id,
            'actual_spend' => $campaign->actual_spend,
            'user_budget' => $campaign->user_budget,
        ]);
    }

    protected function getMetaService(): MetaAdsService
    {
        if (! MetaAdsService::isBoostConfigured()) {
            return new MetaAdsServiceFake;
        }

        return new MetaAdsService;
    }
}
