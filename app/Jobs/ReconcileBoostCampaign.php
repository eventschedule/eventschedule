<?php

namespace App\Jobs;

use App\Mail\BoostCompleted;
use App\Models\BoostBillingRecord;
use App\Models\BoostCampaign;
use App\Models\Role;
use App\Services\BoostBillingService;
use App\Services\MetaAdsService;
use App\Services\MetaAdsServiceFake;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ReconcileBoostCampaign implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    protected BoostCampaign $campaign;

    public $tries = 3;

    public $backoff = [300, 3600, 14400];

    public $timeout = 300;

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

        // Refund unspent budget
        $campaign->refresh();
        if (! in_array($campaign->billing_status, ['refunded', 'partially_refunded'])) {
            if (! $campaign->stripe_payment_intent_id && $campaign->billing_status === 'charged') {
                // Credit-paid campaign - return unspent credit to role
                $actualSpend = $campaign->actual_spend ?? 0;
                $unspentBudget = $campaign->user_budget - $actualSpend;

                if ($unspentBudget > 0) {
                    $refundAmount = round($unspentBudget * (1 + $campaign->markup_rate), 2);
                    DB::transaction(function () use ($campaign, $refundAmount, $actualSpend, $unspentBudget) {
                        $role = Role::lockForUpdate()->find($campaign->role_id);
                        if (! $role) {
                            return;
                        }
                        $role->increment('boost_credit', $refundAmount);
                        BoostBillingRecord::create([
                            'boost_campaign_id' => $campaign->id,
                            'type' => 'refund',
                            'amount' => $refundAmount,
                            'meta_spend' => $actualSpend,
                            'markup_amount' => round($unspentBudget * $campaign->markup_rate, 2),
                            'status' => 'completed',
                            'notes' => 'Credit returned - unspent budget',
                        ]);
                        $campaign->update(['billing_status' => 'partially_refunded']);
                    });
                }
            } elseif (config('app.hosted') && ! config('app.is_testing')) {
                $billingService = new BoostBillingService;
                if (! $billingService->refundUnspent($campaign)) {
                    Log::warning('Boost reconciliation refund failed', [
                        'campaign_id' => $campaign->id,
                    ]);
                }
            }
        }

        $campaign->refresh();

        // Send completion email if still completed (not cancelled during reconciliation)
        if ($campaign->status === 'completed' && $campaign->user) {
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

        // Auto-increase trust limit for hosted mode
        if (config('app.hosted') && $campaign->role_id) {
            $completedCount = BoostCampaign::where('role_id', $campaign->role_id)
                ->where('status', 'completed')
                ->count();

            $newLimit = Role::calculateBoostLimitForCompletedCount($completedCount);
            $role = Role::find($campaign->role_id);

            if ($role) {
                $currentLimit = $role->boost_max_budget !== null
                    ? (float) $role->boost_max_budget
                    : (float) config('services.meta.boost_default_limit', 10);

                // Only increase, never decrease (safe for admin overrides)
                if ($newLimit > $currentLimit) {
                    $role->update(['boost_max_budget' => $newLimit]);
                    Log::info('Boost spending limit auto-increased', [
                        'role_id' => $role->id,
                        'old_limit' => $currentLimit,
                        'new_limit' => $newLimit,
                        'completed_campaigns' => $completedCount,
                    ]);
                }
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
