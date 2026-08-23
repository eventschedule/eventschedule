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
    /**
     * The per-event figures a status change on this sale is answerable for.
     *
     * One entry for an ordinary sale. One entry PER LEG for an order primary, because the status
     * change cascades to every leg (Sale::statusCascadeQuery) while analytics_events_daily is keyed
     * by event: crediting or decrementing only the anchoring leg's event leaves the other events'
     * revenue stranded on the books, with nothing later to net it out.
     *
     * Read this BEFORE the save. legTotal*() filters is_deleted, so in deleteSale() in particular
     * the numbers are gone by the time the rows are flagged.
     *
     * Legs that are ALREADY released are dropped: both cascades skip cancelled/refunded/expired
     * rows, so such a leg does not transition with the rest and must not be credited or debited a
     * second time. The subject sale itself is never dropped - every caller has already asserted it
     * is unpaid or paid.
     *
     * Each leg carries its own PRE-CHANGE status, because an order's legs do not have to agree.
     * A buyer can pay one leg in cash at the door while the rest stay unpaid (Sale.php's expiry
     * cascade documents exactly that case), and the two adjustments below key off it: crediting
     * or debiting from the SUBJECT's status books revenue for legs that never moved.
     */
    private function saleAnalyticsLegs(Sale $sale): array
    {
        return $sale->orderLegs()
            ->reject(fn (Sale $leg) => in_array($leg->status, ['cancelled', 'refunded', 'expired'], true))
            ->map(fn (Sale $leg) => [
                'event_id' => $leg->event_id,
                'amount' => $leg->legTotalPayment(),
                'promo' => $leg->legTotalDiscount(),
                'date' => $leg->created_at?->toDateString(),
                'status' => $leg->status,
            ])
            ->all();
    }

    /**
     * Book revenue for the legs a paid transition actually moves.
     *
     * The paid cascade is `where('status', '!=', 'paid')`, so a leg that is already paid does not
     * transition - it was credited when it was paid, and crediting it again here books the same
     * money twice on that event. Reachable whenever an owner marks one leg paid on its own (a
     * cart leg primary has no group_id, so handleAction permits it) and then marks the anchor paid.
     */
    private function incrementSaleAnalytics(array $legs): void
    {
        foreach ($legs as $leg) {
            if ($leg['status'] === 'paid') {
                continue;
            }

            AnalyticsEventsDaily::incrementSale($leg['event_id'], $leg['amount']);

            if ($leg['promo'] > 0) {
                AnalyticsEventsDaily::incrementPromoSale($leg['event_id'], $leg['promo']);
            }
        }
    }

    /**
     * Reverse revenue for the legs that were actually carrying it.
     *
     * Mirror of the above, and the reason this is per leg rather than gated on the subject: the
     * cancel and refund cascades deliberately DO reach a paid sibling (only expiry is narrowed to
     * unpaid), so cancelling an unpaid anchor released a paid leg while leaving its revenue on the
     * books - and cancelling a paid anchor debited unpaid legs that had never been credited.
     */
    private function decrementSaleAnalytics(array $legs): void
    {
        foreach ($legs as $leg) {
            if ($leg['status'] !== 'paid') {
                continue;
            }

            AnalyticsEventsDaily::decrementSale($leg['event_id'], $leg['amount'], $leg['date']);

            if ($leg['promo'] > 0) {
                AnalyticsEventsDaily::decrementPromoSale($leg['event_id'], $leg['promo'], $leg['date']);
            }
        }
    }

    protected function markSalePaid(Sale $sale, string $reference): ?string
    {
        return DB::transaction(function () use ($sale, $reference) {
            $locked = Sale::lockForUpdate()->find($sale->id);
            if (! $locked || $locked->status !== 'unpaid') {
                return null;
            }

            $legs = $this->saleAnalyticsLegs($locked);

            $locked->status = 'paid';
            $locked->transaction_reference = $reference;
            $locked->save();

            $this->incrementSaleAnalytics($legs);

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

            $legs = $this->saleAnalyticsLegs($locked);

            $locked->status = 'refunded';
            $locked->save();

            // RSVP sales are decremented by the Sale::booted hook instead.
            if ($locked->payment_method !== 'rsvp') {
                $this->decrementSaleAnalytics($legs);
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
            $legs = $this->saleAnalyticsLegs($locked);

            $locked->status = 'cancelled';
            $locked->save();

            if ($locked->payment_method !== 'rsvp') {
                $this->decrementSaleAnalytics($legs);
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
                $legs = $this->saleAnalyticsLegs($locked);

                $locked->status = 'cancelled';
                $locked->save();

                if ($locked->payment_method !== 'rsvp') {
                    $this->decrementSaleAnalytics($legs);
                }
            } else {
                // This sale is ALREADY released, so the branch above did not run and no status
                // cascade fired - but a sibling can still be live. The expiry cascade is narrowed
                // to `unpaid` rows (Sale::booted), so a leg paid in cash at the door survives an
                // expired anchor, and `amount_mismatch` does not cascade at all. The raw
                // is_deleted update below fires NO model events, so without this those rows keep
                // their seats out of inventory forever and their revenue on the books, while
                // orderTotalPayment() silently stops counting them.
                $legs = $this->saleAnalyticsLegs($locked);

                // Same two shapes the delete cascade below uses, deliberately - Sale's own
                // statusCascadeQuery() is protected and not reachable from here.
                $siblings = null;

                if ($locked->isOrderPrimary()) {
                    $siblings = Sale::where('order_id', $locked->order_id)->where('id', '!=', $locked->id);
                } elseif ($locked->group_id && $locked->isPrimarySale()) {
                    $siblings = Sale::where('group_id', $locked->group_id)->where('id', '!=', $locked->id);
                }

                // Leg primaries and ungrouped rows only. Each one's own save cascades to its own
                // guests, so no row is visited twice - re-saving a guest another sibling's cascade
                // has just cancelled would still read dirty (the in-memory copy holds the old
                // status) and fire the release hooks a SECOND time, double-crediting the gift card
                // and over-releasing the seats.
                $live = $siblings
                    ? $siblings->whereIn('status', ['unpaid', 'paid'])
                        ->where(fn ($query) => $query->whereNull('group_id')->orWhereColumn('group_id', 'id'))
                        ->get()
                    : collect();

                foreach ($live as $sibling) {
                    $sibling->status = 'cancelled';
                    $sibling->save();
                }

                // saleAnalyticsLegs() rejects rows already cancelled/refunded/expired, so $locked
                // itself is correctly dropped - it was decremented when it was released. An
                // `amount_mismatch` row survives that filter but decrementSaleAnalytics() only
                // touches rows whose status was `paid`, and a mismatched sale was never credited.
                if ($locked->payment_method !== 'rsvp') {
                    $this->decrementSaleAnalytics($legs);
                }
            }

            $locked->is_deleted = true;
            $locked->save();

            // Every sale this delete removes from view, so their seats can be released below.
            // Collected BEFORE the cascade because that is a raw query-builder update.
            $deletedIds = [$locked->id];

            // Cascade the delete. An order primary takes the whole order - guest rows carry
            // order_id too, so this is one flat set. Deleting only this leg would leave the others
            // live: still holding inventory, still in attendee lists, still counted.
            if ($locked->isOrderPrimary()) {
                $siblings = Sale::where('order_id', $locked->order_id)->where('id', '!=', $locked->id);
                $deletedIds = array_merge($deletedIds, $siblings->pluck('id')->all());
                $siblings->update(['is_deleted' => true]);
            } elseif ($locked->group_id && $locked->isPrimarySale()) {
                $siblings = Sale::where('group_id', $locked->group_id)->where('id', '!=', $locked->id);
                $deletedIds = array_merge($deletedIds, $siblings->pluck('id')->all());
                $siblings->update(['is_deleted' => true]);
            }

            // Allocated seats go back to the map. Sale::booted only releases on a status change to
            // cancelled/refunded/expired - and an `amount_mismatch` sale takes the branch above
            // that never changes status, so its seats stayed `sold` against a row that had
            // vanished from every list, with no tooling to recover them.
            app(\App\Services\SeatHoldService::class)->releaseForSaleIds($deletedIds);

            return $pre;
        });
    }

    /**
     * One delivery per row, so a subscriber iterating a group sees every seat. The money-carrying
     * primary is dispatched first; guest rows report zero amounts (see Sale::toApiData).
     *
     * Spans the whole ORDER as well, because the status change did: a subscriber that only ever
     * heard about the anchoring leg cannot act on the events it never saw - and the webhook docs
     * tell them to sum payment_amount across an order_id, which needs every row.
     *
     * Call this with $sale already refreshed, so its status is the post-action one.
     */
    protected function dispatchSaleWebhookAcrossOrder(string $webhookEvent, Sale $sale): void
    {
        // includeDeleted: 'sale.deleted' is dispatched after deleteSale() has flagged every row, so
        // the live-rows-only default would leave a deleted order with no delivery at all.
        foreach ($sale->orderLegs(includeDeleted: true) as $leg) {
            // A leg released before this action kept its own status - both cascades skip
            // cancelled/refunded/expired rows - so announcing e.g. sale.refunded for it would tell
            // the subscriber something untrue. The subject itself always fires.
            if ($leg->id !== $sale->id && $leg->status !== $sale->status) {
                continue;
            }

            WebhookService::dispatch($webhookEvent, $leg);

            foreach ($leg->guestSales()->get() as $guestSale) {
                WebhookService::dispatch($webhookEvent, $guestSale);
            }
        }
    }
}
