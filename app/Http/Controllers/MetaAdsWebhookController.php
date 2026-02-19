<?php

namespace App\Http\Controllers;

use App\Jobs\ReconcileBoostCampaign;
use App\Jobs\SyncBoostAnalytics;
use App\Mail\BoostRejected;
use App\Models\BoostCampaign;
use App\Services\BoostBillingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class MetaAdsWebhookController extends Controller
{
    public function verify(Request $request)
    {
        $mode = $request->query('hub_mode');
        $token = $request->query('hub_verify_token');
        $challenge = $request->query('hub_challenge');

        $verifyToken = config('services.meta.webhook_verify_token');
        if (! $verifyToken) {
            return response('Forbidden', 403);
        }

        if ($mode === 'subscribe' && hash_equals($verifyToken, $token ?? '')) {
            return response($challenge, 200)->header('Content-Type', 'text/plain');
        }

        return response('Forbidden', 403);
    }

    public function handle(Request $request)
    {
        if (! $this->verifySignature($request)) {
            Log::warning('Invalid Meta webhook signature', ['ip' => $request->ip()]);

            return response('Unauthorized', 401);
        }

        $payload = $request->all();

        foreach ($payload['entry'] ?? [] as $entry) {
            foreach ($entry['changes'] ?? [] as $change) {
                $this->processChange($change);
            }
        }

        return response('OK', 200);
    }

    private function verifySignature(Request $request): bool
    {
        $signature = $request->header('X-Hub-Signature-256');
        if (! $signature) {
            return false;
        }

        $secret = config('services.meta.app_secret');
        if (! $secret) {
            return false;
        }

        $expected = 'sha256='.hash_hmac('sha256', $request->getContent(), $secret);

        return hash_equals($expected, $signature);
    }

    private function processChange(array $change): void
    {
        $field = $change['field'] ?? '';
        $value = $change['value'] ?? [];

        try {
            match ($field) {
                'ad_account' => $this->handleAdAccountChange($value),
                'campaign' => $this->handleCampaignChange($value),
                'ad' => $this->handleAdChange($value),
                default => Log::info('Unhandled Meta webhook field', ['field' => $field]),
            };
        } catch (\Exception $e) {
            Log::error('Error processing Meta webhook', [
                'field' => $field,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function handleAdAccountChange(array $value): void
    {
        // Ad account level changes (spending limits, etc.)
        Log::info('Meta ad account change', ['value' => $value]);
    }

    private function handleCampaignChange(array $value): void
    {
        $metaCampaignId = $value['id'] ?? null;
        if (! $metaCampaignId) {
            return;
        }

        $campaign = BoostCampaign::where('meta_campaign_id', $metaCampaignId)->first();
        if (! $campaign) {
            return;
        }

        $effectiveStatus = $value['effective_status'] ?? null;

        if ($effectiveStatus === 'COMPLETED') {
            $updated = BoostCampaign::where('id', $campaign->id)
                ->whereIn('status', ['active', 'paused'])
                ->update([
                    'status' => 'completed',
                    'meta_status' => $effectiveStatus,
                ]);

            if ($updated > 0) {
                SyncBoostAnalytics::dispatch($campaign);
                ReconcileBoostCampaign::dispatch($campaign)->delay(now()->addHours(24));
            }
        }
    }

    private function handleAdChange(array $value): void
    {
        $metaAdId = $value['id'] ?? null;
        if (! $metaAdId) {
            return;
        }

        $ad = \App\Models\BoostAd::where('meta_ad_id', $metaAdId)->first();
        if (! $ad) {
            return;
        }

        $campaign = $ad->campaign;
        $effectiveStatus = $value['effective_status'] ?? null;

        if ($effectiveStatus === 'DISAPPROVED') {
            $ad->meta_status = 'DISAPPROVED';
            $ad->meta_rejection_reason = ! empty($value['issues_info'][0]['message'])
                ? $value['issues_info'][0]['message']
                : 'Ad policy violation';
            $ad->save();

            // If all ads are rejected, reject the campaign (atomic to prevent race with sync command)
            $totalAds = $campaign->ads()->count();
            $rejectedAds = $campaign->ads()->where('meta_status', 'DISAPPROVED')->count();
            $allRejected = $totalAds > 0 && $rejectedAds === $totalAds;

            if ($allRejected) {
                $updated = BoostCampaign::where('id', $campaign->id)
                    ->whereIn('status', ['active', 'paused', 'pending_payment'])
                    ->update([
                        'status' => 'rejected',
                        'meta_rejection_reason' => $ad->meta_rejection_reason,
                    ]);

                if ($updated > 0) {
                    $campaign->refresh();

                    $billingService = app()->make(BoostBillingService::class);
                    $refunded = $billingService->refundFull($campaign);

                    if (! $refunded) {
                        Log::error('Boost campaign rejected via webhook but refund failed', [
                            'campaign_id' => $campaign->id,
                        ]);
                    }

                    $campaign->loadMissing('user');
                    try {
                        Mail::to($campaign->user->email)->send(new BoostRejected($campaign, $refunded));
                    } catch (\Exception $e) {
                        Log::warning('Failed to send boost rejected email via webhook', [
                            'campaign_id' => $campaign->id,
                            'error' => $e->getMessage(),
                        ]);
                    }

                    Log::info('Boost campaign rejected via webhook', ['campaign_id' => $campaign->id, 'refunded' => $refunded]);
                }
            }
        } elseif ($effectiveStatus === 'ACTIVE') {
            $ad->meta_status = 'ACTIVE';
            $ad->save();
        }
    }
}
