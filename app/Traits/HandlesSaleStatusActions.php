<?php

namespace App\Traits;

use App\Models\AnalyticsEventsDaily;
use App\Models\Sale;
use App\Services\WebhookService;
use Illuminate\Support\Facades\DB;

/**
 * The status transitions an owner can drive on a sale, shared by the admin portal
 * (TicketController::handleAction) and the API (ApiSaleController).
 *
 * These two used to carry their own copies, and the copies had drifted in two ways that both
 * cost money:
 *
 *  - The API decremented analytics by the row's own payment_amount rather than legTotalPayment(),
 *    so refunding a grouped order through the API gave back only the primary seat's share and
 *    left the guests' revenue on the books.
 *  - Only the API's mark_paid re-read the sale under lockForUpdate. Its refund and cancel mutated
 *    an instance read before the request, so a webhook or the ReleaseTickets cron landing in
 *    between could be overwritten with a stale status - firing Sale::booted's restore hook twice
 *    and double-crediting a redeemed gift card.
 *
 * Every method here re-reads the sale under lockForUpdate and re-asserts its own precondition
 * INSIDE the transaction, then returns the pre-transition status, or null when the sale was not
 * in a state the action applies to. Callers own their own authorization, audit logging and
 * response shaping - and must ->refresh() their outer instance afterwards, since the mutation
 * happens on the locked copy.
 */
trait HandlesSaleStatusActions
{
    protected function markSalePaid(Sale $sale, string $reference): ?string
    {
        return DB::transaction(function () use ($sale, $reference) {
            $locked = Sale::lockForUpdate()->find($sale->id);
            if (! $locked || $locked->status !== 'unpaid') {
                return null;
            }

            $analyticsAmount = $locked->legTotalPayment();
            $promoTotal = $locked->legTotalDiscount();

            $locked->status = 'paid';
            $locked->transaction_reference = $reference;
            $locked->save();

            AnalyticsEventsDaily::incrementSale($locked->event_id, $analyticsAmount);
            if ($promoTotal > 0) {
                AnalyticsEventsDaily::incrementPromoSale($locked->event_id, $promoTotal);
            }

            return 'unpaid';
        });
    }

    protected function refundSale(Sale $sale): ?string
    {
        return DB::transaction(function () use ($sale) {
            $locked = Sale::lockForUpdate()->find($sale->id);
            if (! $locked || $locked->status !== 'paid') {
                return null;
            }

            $analyticsDate = $locked->created_at->toDateString();
            $analyticsAmount = $locked->legTotalPayment();
            $promoTotal = $locked->legTotalDiscount();

            $locked->status = 'refunded';
            $locked->save();

            // RSVP sales are decremented by the Sale::booted hook instead.
            if ($locked->payment_method !== 'rsvp') {
                AnalyticsEventsDaily::decrementSale($locked->event_id, $analyticsAmount, $analyticsDate);

                if ($promoTotal > 0) {
                    AnalyticsEventsDaily::decrementPromoSale($locked->event_id, $promoTotal, $analyticsDate);
                }
            }

            return 'paid';
        });
    }

    protected function cancelSale(Sale $sale): ?string
    {
        return DB::transaction(function () use ($sale) {
            $locked = Sale::lockForUpdate()->find($sale->id);
            if (! $locked || ! in_array($locked->status, ['unpaid', 'paid'])) {
                return null;
            }

            $pre = $locked->status;
            $wasPaid = $pre === 'paid';
            $analyticsAmount = $locked->legTotalPayment();
            $promoTotal = $locked->legTotalDiscount();

            $locked->status = 'cancelled';
            $locked->save();

            if ($wasPaid && $locked->payment_method !== 'rsvp') {
                $analyticsDate = $locked->created_at->toDateString();
                AnalyticsEventsDaily::decrementSale($locked->event_id, $analyticsAmount, $analyticsDate);

                if ($promoTotal > 0) {
                    AnalyticsEventsDaily::decrementPromoSale($locked->event_id, $promoTotal, $analyticsDate);
                }
            }

            return $pre;
        });
    }

    /**
     * Unlike the others this never returns null for a live sale: deleting an already
     * cancelled/refunded/expired sale is a no-op on status but still a real delete.
     */
    protected function deleteSale(Sale $sale): ?string
    {
        return DB::transaction(function () use ($sale) {
            $locked = Sale::lockForUpdate()->find($sale->id);
            if (! $locked) {
                return null;
            }

            $pre = $locked->status;

            // Cancel first so Sale::booted releases the ticket inventory.
            if (in_array($locked->status, ['unpaid', 'paid'])) {
                $wasPaid = $locked->status === 'paid';
                $analyticsAmount = $locked->legTotalPayment();
                $promoTotal = $locked->legTotalDiscount();

                $locked->status = 'cancelled';
                $locked->save();

                if ($wasPaid && $locked->payment_method !== 'rsvp') {
                    $analyticsDate = $locked->created_at->toDateString();
                    AnalyticsEventsDaily::decrementSale($locked->event_id, $analyticsAmount, $analyticsDate);

                    if ($promoTotal > 0) {
                        AnalyticsEventsDaily::decrementPromoSale($locked->event_id, $promoTotal, $analyticsDate);
                    }
                }
            }

            $locked->is_deleted = true;
            $locked->save();

            // Cascade the delete. An order primary takes the whole order - guest rows carry
            // order_id too, so this is one flat set. Deleting only this leg would leave the others
            // live: still holding inventory, still in attendee lists, still counted.
            if ($locked->isOrderPrimary()) {
                Sale::where('order_id', $locked->order_id)
                    ->where('id', '!=', $locked->id)
                    ->update(['is_deleted' => true]);
            } elseif ($locked->group_id && $locked->isPrimarySale()) {
                Sale::where('group_id', $locked->group_id)
                    ->where('id', '!=', $locked->id)
                    ->update(['is_deleted' => true]);
            }

            return $pre;
        });
    }

    /**
     * One delivery per row, so a subscriber iterating a group sees every seat. The money-carrying
     * primary is dispatched first; guest rows report zero amounts (see Sale::toApiData).
     */
    protected function dispatchSaleWebhookAcrossGroup(string $webhookEvent, Sale $sale): void
    {
        WebhookService::dispatch($webhookEvent, $sale);

        if ($sale->group_id && $sale->isPrimarySale()) {
            foreach (Sale::where('group_id', $sale->group_id)->where('id', '!=', $sale->id)->get() as $guestSale) {
                WebhookService::dispatch($webhookEvent, $guestSale);
            }
        }
    }
}
