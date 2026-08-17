<?php

namespace App\Services;

use App\Models\Sale;
use App\Traits\HandlesSaleStatusActions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * The one way a gateway reports that a sale has been paid for.
 *
 * Every rail used to carry its own copy of this: two in StripeController, three in
 * InvoiceNinjaController, one for the free path and one for the payment-URL return. They had already
 * drifted, and each new gateway meant a seventh copy of a sequence where forgetting a single step
 * loses money silently - revenue not booked, a confirmation email never sent, a boost conversion
 * never reported.
 *
 * The order below is load-bearing:
 *
 *  - lock first, so a webhook and the buyer's own return cannot both settle the same sale;
 *  - refuse a released sale before anything else, because expiry has already given the seats back
 *    and restored any gift-card balance, and marking it paid does not re-take them;
 *  - reconcile the amount before marking paid, so a short payment lands in `amount_mismatch` for an
 *    owner to look at rather than quietly counting as settled;
 *  - read the legs BEFORE the save, because the paid cascade rewrites their statuses;
 *  - fire emails and webhooks AFTER the transaction commits, so nothing is announced that a
 *    rollback then unmakes.
 */
class SaleSettlementService
{
    use HandlesSaleStatusActions;

    /**
     * Amounts are compared with a tolerance rather than for equality: the gateway rounds to its own
     * smallest unit, and our per-seat and per-leg totals are summed from decimals, so an exact match
     * cannot be relied on even when nothing is wrong.
     */
    private const AMOUNT_TOLERANCE = 0.01;

    /**
     * Settle a sale against a gateway's report of payment.
     *
     * @param  string|null  $reference  the gateway's own id for the payment; also the key its webhook
     *                                  uses to find this sale again, so it must be the durable one.
     *                                  Null leaves transaction_reference untouched, for rails that
     *                                  have no such id.
     * @param  float|null  $receivedAmount  what the gateway says was paid, in major units. Null skips
     *                                      reconciliation, for rails that report no amount at all
     *                                      (cash, a manual mark-paid, a free order).
     * @param  string  $context  short slug for the audit trail, e.g. 'payfast'
     * @param  string|null  $usageMetric  a UsageTrackingService constant, when the rail is metered
     * @return string one of paid|already_paid|released|amount_mismatch|missing
     */
    public function settle(
        Sale $sale,
        ?string $reference,
        ?float $receivedAmount,
        string $context,
        ?string $usageMetric = null,
    ): string {
        $outcome = DB::transaction(function () use ($sale, $reference, $receivedAmount, $context, $usageMetric) {
            $locked = Sale::lockForUpdate()->find($sale->id);

            if (! $locked) {
                return 'missing';
            }

            // Idempotent by design. Gateways retry, and the buyer's return can race the callback.
            if ($locked->status === 'paid') {
                return 'already_paid';
            }

            // A released sale must never be revived. Expiry already returned the seats and any
            // gift-card balance, so flipping expired -> paid oversells the event and double-spends
            // the card. A multi-event order widens the window: one leg's expiry can elapse while the
            // order's single checkout session is still open.
            if (in_array($locked->status, ['expired', 'cancelled', 'refunded'], true)) {
                Log::warning('Payment callback for a released sale - not marking paid', [
                    'sale_id' => $locked->id,
                    'status' => $locked->status,
                    'gateway' => $context,
                ]);

                return 'released';
            }

            $expected = (float) ($locked->isOrderPrimary()
                ? $locked->orderTotalPayment()
                : $locked->legTotalPayment());

            if ($receivedAmount !== null && abs($receivedAmount - $expected) > self::AMOUNT_TOLERANCE) {
                Log::error('Payment amount mismatch - sale NOT marked as paid', [
                    'sale_id' => $locked->id,
                    'gateway' => $context,
                    'expected_amount' => $expected,
                    'received_amount' => $receivedAmount,
                    'reference' => $reference,
                ]);

                $previous = $locked->status;
                $locked->status = 'amount_mismatch';
                if ($reference !== null) {
                    $locked->transaction_reference = $reference;
                }
                $locked->save();

                AuditService::log(AuditService::SALE_PAID, $locked->user_id, 'Sale', $locked->id,
                    ['status' => $previous], ['status' => 'amount_mismatch'],
                    $context.'_amount_mismatch:event_id:'.$locked->event_id);

                return 'amount_mismatch';
            }

            // Read before the save: the paid cascade rewrites sibling statuses, and each leg's
            // PRE-change status is what decides whether it earns its event anything. A leg already
            // paid by hand was credited then, and crediting it again books the same money twice.
            $legs = $this->saleAnalyticsLegs($locked);
            $wasPaid = $locked->orderLegs()
                ->mapWithKeys(fn (Sale $leg) => [$leg->id => $leg->status === 'paid'])
                ->all();

            // Preserve per-seat payment_amount on grouped primaries, and on order primaries where
            // $receivedAmount is the WHOLE order: writing it onto the one leg that anchors the order
            // would count every other leg twice in orderTotalPayment(), the sales table and revenue.
            if ($receivedAmount !== null && ! $locked->isPrimarySale() && ! $locked->isOrderPrimary()) {
                $locked->payment_amount = $receivedAmount;
            }

            $previous = $locked->status;
            $locked->status = 'paid';
            if ($reference !== null) {
                $locked->transaction_reference = $reference;
            }
            $locked->save();

            AuditService::log(AuditService::SALE_PAID, $locked->user_id, 'Sale', $locked->id,
                ['status' => $previous], ['status' => 'paid'], $context.':event_id:'.$locked->event_id);

            $this->incrementSaleAnalytics($legs);

            // Attributed per event for the same reason as analytics. Read after the save so these
            // are the post-cascade statuses, and skipped for a leg that was already paid, which
            // reported its conversion when it was.
            foreach ($locked->orderLegs() as $leg) {
                if ($leg->status !== 'paid' || ($wasPaid[$leg->id] ?? false)) {
                    continue;
                }

                app()->make(MetaAdsService::class)->sendSaleConversion($leg, (float) $leg->legTotalPayment());
            }

            if ($usageMetric) {
                UsageTrackingService::track($usageMetric);
            }

            return 'paid';
        });

        if ($outcome === 'paid') {
            // Outside the transaction: a webhook delivery or an email cannot be rolled back, so
            // announcing a payment the transaction then abandons is unrecoverable.
            $sale->refresh();

            $this->dispatchSaleWebhookAcrossOrder('sale.paid', $sale);

            (new EmailService)->sendSaleConfirmationEmails($sale);
        }

        return $outcome;
    }
}
