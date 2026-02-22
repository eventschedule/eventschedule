<?php

namespace App\Jobs;

use App\Mail\BoostCreated;
use App\Mail\BoostRejected;
use App\Models\BoostCampaign;
use App\Services\BoostBillingService;
use App\Services\MetaAdsService;
use App\Services\MetaAdsServiceFake;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CreateBoostCampaign implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    protected BoostCampaign $campaign;

    public $tries = 3;

    public $backoff = [30, 120, 300];

    public $deleteWhenMissingModels = true;

    public $uniqueFor = 3600;

    public function __construct(BoostCampaign $campaign)
    {
        $this->campaign = $campaign;
    }

    public function uniqueId(): string
    {
        return 'create-boost-'.$this->campaign->id;
    }

    public function handle(): void
    {
        $campaign = $this->campaign;
        $campaign->refresh();
        $campaign->loadMissing(['event', 'role', 'ads', 'user']);

        if ($campaign->status !== 'active') {
            return;
        }

        // Idempotency guard: skip if already created on Meta
        if ($campaign->meta_campaign_id) {
            return;
        }

        $metaService = $this->getMetaService();

        $result = $metaService->createCampaign($campaign);

        try {
            $campaign->update([
                'meta_campaign_id' => $result['meta_campaign_id'],
                'meta_adset_id' => $result['meta_adset_id'],
                'meta_status' => 'ACTIVE',
                'meta_synced_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Meta campaign created but DB update failed - marking as failed to prevent duplicate', [
                'campaign_id' => $campaign->id,
                'meta_campaign_id' => $result['meta_campaign_id'] ?? null,
                'error' => $e->getMessage(),
            ]);

            try {
                $campaign->update([
                    'status' => 'failed',
                    'meta_status' => 'ERROR',
                    'meta_rejection_reason' => 'Created on Meta but failed to save locally: '.$e->getMessage(),
                ]);
            } catch (\Exception $updateError) {
                Log::critical('Failed to mark campaign as failed after Meta creation - requires manual cleanup', [
                    'campaign_id' => $campaign->id,
                    'meta_campaign_id' => $result['meta_campaign_id'] ?? null,
                    'original_error' => $e->getMessage(),
                    'update_error' => $updateError->getMessage(),
                ]);
            }

            $this->fail($e);

            return;
        }

        // Send confirmation email
        if ($campaign->user) {
            try {
                Mail::to($campaign->user->email)
                    ->send(new BoostCreated($campaign));
            } catch (\Exception $e) {
                Log::warning('Failed to send boost created email', [
                    'campaign_id' => $campaign->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info('Boost campaign created on Meta', [
            'campaign_id' => $campaign->id,
            'meta_campaign_id' => $result['meta_campaign_id'],
        ]);
    }

    /**
     * Handle permanent failure after all retries exhausted.
     */
    public function failed(?\Throwable $exception): void
    {
        $campaign = $this->campaign;
        $campaign->refresh();
        $campaign->loadMissing(['user']);

        // Don't overwrite if already handled (cancelled, etc.)
        if (! in_array($campaign->status, ['active', 'pending_payment'])) {
            return;
        }

        Log::error('Failed to create boost campaign on Meta after all retries', [
            'campaign_id' => $campaign->id,
            'error' => $exception?->getMessage(),
        ]);

        $campaign->update([
            'status' => 'failed',
            'meta_status' => 'ERROR',
            'meta_rejection_reason' => $exception?->getMessage(),
        ]);

        // Issue full refund
        $refunded = false;
        if (config('app.hosted') && ! config('app.is_testing')) {
            $billingService = new BoostBillingService;
            $refunded = $billingService->refundFull($campaign);

            if (! $refunded) {
                Log::error('Boost campaign failed but refund also failed', [
                    'campaign_id' => $campaign->id,
                ]);
            }
        }

        // Send rejection email
        if ($campaign->user) {
            try {
                Mail::to($campaign->user->email)
                    ->send(new BoostRejected($campaign, $refunded));
            } catch (\Exception $emailError) {
                Log::warning('Failed to send boost rejected email', [
                    'campaign_id' => $campaign->id,
                    'error' => $emailError->getMessage(),
                ]);
            }
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
