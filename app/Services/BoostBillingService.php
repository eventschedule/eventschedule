<?php

namespace App\Services;

use App\Models\BoostBillingRecord;
use App\Models\BoostCampaign;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BoostBillingService
{
    /**
     * Confirm a payment was successful and update records
     */
    public function confirmPayment(BoostCampaign $campaign, string $paymentIntentId): bool
    {
        try {
            $stripe = new \Stripe\StripeClient(config('services.stripe_platform.secret'));
            $paymentIntent = $stripe->paymentIntents->retrieve($paymentIntentId);

            if ($paymentIntent->status === 'succeeded') {
                // Always save the payment intent ID so refunds can be issued if needed
                $campaign->update(['stripe_payment_intent_id' => $paymentIntentId]);

                // Verify the payment amount matches the expected cost
                $expectedCents = (int) round($campaign->getTotalCost() * 100);
                if ($paymentIntent->amount !== $expectedCents) {
                    Log::error('Boost Billing: Payment amount mismatch', [
                        'campaign_id' => $campaign->id,
                        'expected_cents' => $expectedCents,
                        'actual_cents' => $paymentIntent->amount,
                    ]);

                    // Record the charge for audit trail even on mismatch
                    DB::transaction(function () use ($campaign, $paymentIntentId, $paymentIntent, $expectedCents) {
                        $campaign->update([
                            'total_charged' => $paymentIntent->amount / 100,
                            'billing_status' => 'charged',
                        ]);

                        BoostBillingRecord::create([
                            'boost_campaign_id' => $campaign->id,
                            'type' => 'charge',
                            'amount' => $paymentIntent->amount / 100,
                            'meta_spend' => 0,
                            'markup_amount' => 0,
                            'stripe_payment_intent_id' => $paymentIntentId,
                            'status' => 'completed',
                            'notes' => 'Amount mismatch - expected: '.($expectedCents / 100),
                        ]);
                    });

                    return false;
                }

                DB::transaction(function () use ($campaign, $paymentIntentId) {
                    $campaign->update([
                        'total_charged' => $campaign->getTotalCost(),
                        'billing_status' => 'charged',
                    ]);

                    // Create billing record for the charge
                    BoostBillingRecord::create([
                        'boost_campaign_id' => $campaign->id,
                        'type' => 'charge',
                        'amount' => $campaign->getTotalCost(),
                        'meta_spend' => $campaign->user_budget,
                        'markup_amount' => $campaign->getMarkupAmount(),
                        'stripe_payment_intent_id' => $paymentIntentId,
                        'status' => 'completed',
                    ]);
                });

                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Boost Billing: Failed to confirm payment', [
                'campaign_id' => $campaign->id,
                'payment_intent_id' => $paymentIntentId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Issue a full refund (for rejected/failed campaigns before any spend)
     */
    public function refundFull(BoostCampaign $campaign): bool
    {
        return DB::transaction(function () use ($campaign) {
            $campaign = BoostCampaign::lockForUpdate()->find($campaign->id);

            if (! $campaign->stripe_payment_intent_id) {
                return false;
            }

            if (in_array($campaign->billing_status, ['refunded', 'partially_refunded'])) {
                return false;
            }

            try {
                $stripe = new \Stripe\StripeClient(config('services.stripe_platform.secret'));

                $refund = $stripe->refunds->create([
                    'payment_intent' => $campaign->stripe_payment_intent_id,
                    'metadata' => [
                        'boost_campaign_id' => $campaign->id,
                        'reason' => 'full_refund',
                    ],
                ], [
                    'idempotency_key' => "boost_refund_full_{$campaign->id}",
                ]);

                $refundAmount = $campaign->total_charged ?? $campaign->getTotalCost();

                BoostBillingRecord::create([
                    'boost_campaign_id' => $campaign->id,
                    'type' => 'refund',
                    'amount' => $refundAmount,
                    'meta_spend' => 0,
                    'markup_amount' => 0,
                    'stripe_refund_id' => $refund->id,
                    'status' => 'completed',
                    'notes' => 'Full refund - campaign rejected or failed before spend',
                ]);

                $updateData = ['billing_status' => 'refunded'];
                if ($campaign->total_charged !== null) {
                    $updateData['total_charged'] = $refundAmount;
                }
                $campaign->update($updateData);

                return true;
            } catch (\Exception $e) {
                Log::error('Boost Billing: Failed to issue full refund', [
                    'campaign_id' => $campaign->id,
                    'error' => $e->getMessage(),
                ]);

                return false;
            }
        });
    }

    /**
     * Cancel a payment intent that was never confirmed/charged
     */
    public function cancelPaymentIntent(BoostCampaign $campaign): bool
    {
        if (! $campaign->stripe_payment_intent_id) {
            return false;
        }

        try {
            $stripe = new \Stripe\StripeClient(config('services.stripe_platform.secret'));
            $paymentIntent = $stripe->paymentIntents->retrieve($campaign->stripe_payment_intent_id);

            if ($paymentIntent->status === 'succeeded') {
                return $this->refundFull($campaign);
            }

            if (in_array($paymentIntent->status, ['requires_payment_method', 'requires_confirmation', 'requires_action', 'processing'])) {
                $stripe->paymentIntents->cancel($campaign->stripe_payment_intent_id);
            }

            $campaign->update(['billing_status' => 'cancelled']);

            return true;
        } catch (\Exception $e) {
            Log::error('Boost Billing: Failed to cancel payment intent', [
                'campaign_id' => $campaign->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Refund unspent budget on campaign completion
     */
    public function refundUnspent(BoostCampaign $campaign): bool
    {
        return DB::transaction(function () use ($campaign) {
            $campaign = BoostCampaign::lockForUpdate()->find($campaign->id);

            if (! $campaign->stripe_payment_intent_id || ! $campaign->total_charged) {
                return false;
            }

            if (in_array($campaign->billing_status, ['refunded', 'partially_refunded'])) {
                return false;
            }

            $actualSpend = $campaign->actual_spend ?? 0;
            $unspentBudget = $campaign->user_budget - $actualSpend;

            if ($unspentBudget <= 0) {
                return false;
            }

            $refundAmount = round($unspentBudget * (1 + $campaign->markup_rate), 2);
            $refundAmountCents = (int) round($refundAmount * 100);

            try {
                $stripe = new \Stripe\StripeClient(config('services.stripe_platform.secret'));

                $refund = $stripe->refunds->create([
                    'payment_intent' => $campaign->stripe_payment_intent_id,
                    'amount' => $refundAmountCents,
                    'metadata' => [
                        'boost_campaign_id' => $campaign->id,
                        'reason' => 'unspent_budget',
                        'actual_spend' => $actualSpend,
                    ],
                ], [
                    'idempotency_key' => "boost_refund_unspent_{$campaign->id}",
                ]);

                BoostBillingRecord::create([
                    'boost_campaign_id' => $campaign->id,
                    'type' => 'refund',
                    'amount' => $refundAmount,
                    'meta_spend' => $actualSpend,
                    'markup_amount' => round($unspentBudget * $campaign->markup_rate, 2),
                    'stripe_refund_id' => $refund->id,
                    'status' => 'completed',
                    'notes' => "Partial refund - unspent budget: {$campaign->getCurrencySymbol()}{$unspentBudget}",
                ]);

                $campaign->update([
                    'billing_status' => 'partially_refunded',
                ]);

                return true;
            } catch (\Exception $e) {
                Log::error('Boost Billing: Failed to refund unspent budget', [
                    'campaign_id' => $campaign->id,
                    'error' => $e->getMessage(),
                ]);

                return false;
            }
        });
    }
}
