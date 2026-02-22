<?php

namespace App\Console\Commands;

use App\Jobs\ReconcileBoostCampaign;
use App\Jobs\SyncBoostAnalytics;
use App\Mail\BoostBudgetAlert;
use App\Mail\BoostRejected;
use App\Models\BoostCampaign;
use App\Services\BoostBillingService;
use App\Services\MetaAdsService;
use App\Services\MetaAdsServiceFake;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SyncBoostCampaigns extends Command
{
    protected $signature = 'boost:sync';

    protected $description = 'Sync boost campaign statuses and analytics from Meta';

    public function handle()
    {
        $metaService = $this->getMetaService();

        $campaigns = BoostCampaign::whereIn('status', ['active', 'paused'])
            ->whereNotNull('meta_campaign_id')
            ->with(['ads', 'user'])
            ->get();

        $this->info("Syncing {$campaigns->count()} campaigns...");

        foreach ($campaigns as $campaign) {
            try {
                $this->syncCampaignStatus($campaign, $metaService);
                $this->checkBudgetAlert($campaign);

                if ($campaign->status === 'active') {
                    SyncBoostAnalytics::dispatch($campaign);
                }
            } catch (\Exception $e) {
                Log::error('Failed to sync boost campaign', [
                    'campaign_id' => $campaign->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->checkCompletedCampaigns();
        $this->recoverStalePendingPayments();

        $this->info('Sync complete.');
    }

    private function getMetaService(): MetaAdsService
    {
        if (! MetaAdsService::isBoostConfigured()) {
            return app()->make(MetaAdsServiceFake::class);
        }

        return app()->make(MetaAdsService::class);
    }

    private function syncCampaignStatus(BoostCampaign $campaign, MetaAdsService $metaService): void
    {
        $status = $metaService->checkAdStatus($campaign);

        if (! $status) {
            return;
        }

        $campaign->meta_status = $status['campaign_status'] ?? $campaign->meta_status;
        $campaign->meta_synced_at = now();

        // Check for ad rejections
        foreach ($status['ad_statuses'] ?? [] as $adStatus) {
            $ad = $campaign->ads->firstWhere('meta_ad_id', $adStatus['id']);
            if ($ad && ($adStatus['status'] ?? '') === 'DISAPPROVED') {
                $ad->meta_status = 'DISAPPROVED';
                $ad->meta_rejection_reason = $adStatus['rejection_reason'] ?? null;
                $ad->save();
            }
        }

        // If all ads rejected, mark campaign as rejected (atomic to prevent race with webhook)
        $totalAds = $campaign->ads()->count();
        $rejectedAds = $campaign->ads()->where('meta_status', 'DISAPPROVED')->count();
        $allRejected = $totalAds > 0 && $rejectedAds === $totalAds;

        if ($allRejected) {
            $rejectionReason = $campaign->ads()->whereNotNull('meta_rejection_reason')->value('meta_rejection_reason');

            $updated = BoostCampaign::where('id', $campaign->id)
                ->where('status', '!=', 'rejected')
                ->update([
                    'status' => 'rejected',
                    'meta_rejection_reason' => $rejectionReason,
                ]);

            if ($updated > 0) {
                $campaign->refresh();

                $metaService->pauseCampaign($campaign);

                $billingService = app()->make(BoostBillingService::class);
                $refunded = $billingService->refundFull($campaign);

                if (! $refunded) {
                    Log::error('Boost campaign rejected but refund failed', [
                        'campaign_id' => $campaign->id,
                    ]);
                }

                try {
                    Mail::to($campaign->user->email)->send(new BoostRejected($campaign, $refunded));
                } catch (\Exception $e) {
                    Log::warning('Failed to send boost rejected email', [
                        'campaign_id' => $campaign->id,
                        'error' => $e->getMessage(),
                    ]);
                }

                Log::info('Boost campaign rejected', ['campaign_id' => $campaign->id, 'refunded' => $refunded]);
            }

            return;
        }

        // Check if campaign completed (end date reached or budget exhausted)
        if ($campaign->scheduled_end && $campaign->scheduled_end->isPast()) {
            $updated = BoostCampaign::where('id', $campaign->id)
                ->whereIn('status', ['active', 'paused'])
                ->update(['status' => 'completed']);

            if ($updated > 0) {
                $campaign->refresh();
                SyncBoostAnalytics::dispatch($campaign);
                ReconcileBoostCampaign::dispatch($campaign)->delay(now()->addHours(24));
            }

            return;
        }

        BoostCampaign::where('id', $campaign->id)
            ->whereNotIn('status', ['cancelled', 'failed', 'rejected'])
            ->update([
                'meta_status' => $campaign->meta_status,
                'meta_synced_at' => $campaign->meta_synced_at,
            ]);
    }

    private function checkBudgetAlert(BoostCampaign $campaign): void
    {
        if ($campaign->status !== 'active') {
            return;
        }

        $utilization = $campaign->getBudgetUtilization();
        if ($utilization >= 75 && ! $campaign->budget_alert_sent_at) {
            $updated = BoostCampaign::where('id', $campaign->id)
                ->whereNull('budget_alert_sent_at')
                ->where('status', 'active')
                ->update(['budget_alert_sent_at' => now()]);

            if ($updated > 0) {
                try {
                    Mail::to($campaign->user->email)->send(new BoostBudgetAlert($campaign));
                } catch (\Exception $e) {
                    Log::warning('Failed to send boost budget alert', [
                        'campaign_id' => $campaign->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }
    }

    private function checkCompletedCampaigns(): void
    {
        BoostCampaign::where('status', 'completed')
            ->where('billing_status', 'charged')
            ->where('updated_at', '<=', now()->subHours(24))
            ->chunkById(50, function ($campaigns) {
                foreach ($campaigns as $campaign) {
                    ReconcileBoostCampaign::dispatch($campaign);
                }
            });
    }

    private function recoverStalePendingPayments(): void
    {
        $staleCampaigns = BoostCampaign::where('status', 'pending_payment')
            ->where('created_at', '<=', now()->subMinutes(30))
            ->get();

        if ($staleCampaigns->isEmpty()) {
            return;
        }

        $this->info("Recovering {$staleCampaigns->count()} stale pending_payment campaigns...");

        $billingService = app()->make(BoostBillingService::class);

        foreach ($staleCampaigns as $campaign) {
            try {
                if (config('app.is_testing')) {
                    // In testing mode, activate stale campaigns without Stripe check
                    $campaign->update(['status' => 'active']);
                    \App\Jobs\CreateBoostCampaign::dispatch($campaign);
                    Log::info('Recovered stale pending_payment campaign (testing) - activated', ['campaign_id' => $campaign->id]);

                    continue;
                }

                // Check if the Stripe payment actually succeeded
                if ($campaign->stripe_payment_intent_id) {
                    $stripe = new \Stripe\StripeClient(config('services.stripe_platform.secret'));
                    $paymentIntent = $stripe->paymentIntents->retrieve($campaign->stripe_payment_intent_id);

                    if ($paymentIntent->status === 'succeeded') {
                        // Payment was charged - confirm and activate the campaign
                        $confirmed = $billingService->confirmPayment($campaign, $campaign->stripe_payment_intent_id);
                        if ($confirmed) {
                            $campaign->update(['status' => 'active']);
                            \App\Jobs\CreateBoostCampaign::dispatch($campaign);
                            Log::info('Recovered stale pending_payment campaign - activated', ['campaign_id' => $campaign->id]);

                            continue;
                        }
                    }
                }

                // Payment was not successful - cancel the campaign
                $campaign->update(['status' => 'failed']);

                if ($campaign->stripe_payment_intent_id) {
                    $billingService->cancelPaymentIntent($campaign);
                }

                Log::info('Recovered stale pending_payment campaign - cancelled', ['campaign_id' => $campaign->id]);
            } catch (\Exception $e) {
                Log::error('Failed to recover stale pending_payment campaign', [
                    'campaign_id' => $campaign->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
