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
     * @param  bool  $onlyWhenUnpaid  refuse anything that is not exactly `unpaid`, rather than only
     *                                `paid` and the released statuses. The difference is
     *                                `amount_mismatch`: a sale parked there was flagged for a human to
     *                                look at, and on the Invoice Ninja rails a retry has never been
     *                                able to settle it. Keeping that gate strict means a flagged sale
     *                                cannot quietly clear itself.
     * @return string one of paid|already_paid|released|amount_mismatch|missing|not_settleable
     */
    public function settle(
        Sale $sale,
        ?string $reference,
        ?float $receivedAmount,
        string $context,
        ?string $usageMetric = null,
        bool $onlyWhenUnpaid = false,
    ): string {
        /** @var list<array{0: Sale, 1: float}> $conversions */
        $conversions = [];

        $outcome = DB::transaction(function () use ($sale, $reference, $receivedAmount, $context, $onlyWhenUnpaid, &$conversions) {
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

            // Everything else - `amount_mismatch` in practice - is refused too when the caller asked
            // for the strict gate. Reported separately from 'released' so a caller can tell a sale
            // awaiting review apart from one whose seats have already gone back.
            if ($onlyWhenUnpaid && $locked->status !== 'unpaid') {
                Log::warning('Payment callback for a sale that is not unpaid - not marking paid', [
                    'sale_id' => $locked->id,
                    'status' => $locked->status,
                    'gateway' => $context,
                ]);

                return 'not_settleable';
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

            // Preserve per-seat payment_amount on grouped primaries, and on order primaries where
            // $receivedAmount is the WHOLE order: writing it onto the one leg that anchors the order
            // would count every other leg twice in orderTotalPayment(), the sales table and revenue.
            //
            // Assigned BEFORE the analytics legs are read, and that ordering is load-bearing. For an
            // ungrouped sale the gateway's figure lands in payment_amount, and a later refund debits
            // whatever is stored there (HandlesSaleStatusActions::decrementSaleAnalytics reads
            // legTotalPayment() at refund time). Crediting our pre-overwrite figure instead would
            // leave the difference - up to the reconciliation tolerance, since anything larger is
            // rejected above - stranded on the event's revenue forever.
            if ($receivedAmount !== null && ! $locked->isPrimarySale() && ! $locked->isOrderPrimary()) {
                $locked->payment_amount = $receivedAmount;
            }

            // Read before the save: the paid cascade rewrites sibling statuses, and each leg's
            // PRE-change status is what decides whether it earns its event anything. A leg already
            // paid by hand was credited then, and crediting it again books the same money twice.
            $legs = $this->saleAnalyticsLegs($locked);
            $wasPaid = $locked->orderLegs()
                ->mapWithKeys(fn (Sale $leg) => [$leg->id => $leg->status === 'paid'])
                ->all();

            $previous = $locked->status;
            $locked->status = 'paid';
            if ($reference !== null) {
                $locked->transaction_reference = $reference;
            }
            $locked->save();

            AuditService::log(AuditService::SALE_PAID, $locked->user_id, 'Sale', $locked->id,
                ['status' => $previous], ['status' => 'paid'], $context.':event_id:'.$locked->event_id);

            $this->incrementSaleAnalytics($legs);

            // Collected here, reported after the commit. Read after the save so these are the
            // post-cascade statuses, and skipped for a leg that was already paid, which reported its
            // conversion when it was.
            foreach ($locked->orderLegs() as $leg) {
                if ($leg->status !== 'paid' || ($wasPaid[$leg->id] ?? false)) {
                    continue;
                }

                $conversions[] = [$leg, (float) $leg->legTotalPayment()];
            }

            return 'paid';
        });

        if ($outcome === 'paid') {
            // Everything below runs AFTER the commit, and none of it may run before.
            //
            // Emails and webhooks because they cannot be unsent: announcing a payment the transaction
            // then abandons is unrecoverable.
            //
            // The Meta conversion and the usage counter because of the opposite hazard - they can
            // break the transaction. MetaAdsService does a synchronous Http::timeout(30) call, which
            // held the sale's row lock open for up to half a minute per leg, long enough for the
            // gateway's own webhook timeout to fire and retry into the lock. And
            // UsageTrackingService::track() swallows \Exception, while CounterUtils deliberately
            // re-throws inside a transaction: a deadlock on the shared usage_daily counter row would
            // be caught and discarded after MySQL had already rolled the whole settlement back,
            // leaving settle() to report 'paid', email a ticket and fire sale.paid for a sale still
            // sitting unpaid in the database - which ReleaseTickets would then expire, reselling the
            // seats. Neither belongs anywhere near a transaction that owns money.
            $sale->refresh();

            foreach ($conversions as [$leg, $amount]) {
                app()->make(MetaAdsService::class)->sendSaleConversion($leg, $amount);
            }

            if ($usageMetric) {
                UsageTrackingService::track($usageMetric);
            }

            $this->dispatchSaleWebhookAcrossOrder('sale.paid', $sale);

            (new EmailService)->sendSaleConfirmationEmails($sale);
        }

        return $outcome;
    }
}
