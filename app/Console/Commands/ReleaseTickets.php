<?php

namespace App\Console\Commands;

use App\Models\GiftCard;
use App\Models\Sale;
use App\Services\AuditService;
use Illuminate\Console\Command;

class ReleaseTickets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:release-tickets';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Per-event expiry (owner opt-in via expire_unpaid_tickets). This loop has NO cash
        // exclusion, so a cash sale - including a partial cash gift-card redemption - on an event
        // with expire_unpaid_tickets set IS auto-expired here once past that window (a single,
        // correct restore; the gift-hold loop below excludes cash to avoid double-processing).
        // The "cash is never auto-expired" rule holds only for the default (opt-out) window.
        $expiredSales = Sale::where('status', 'unpaid')
            ->where(function ($q) {
                $q->whereNull('group_id')->orWhereColumn('group_id', 'id');
            })
            ->whereHas('event', function ($query) {
                $query->where('events.expire_unpaid_tickets', '>', 0)
                    ->whereRaw('TIMESTAMPDIFF(HOUR, sales.created_at, NOW()) >= events.expire_unpaid_tickets');
            })
            ->get();

        // A leg of a multi-event order can never expire on its own. The buyer pays once, so
        // releasing one event's seats while the rest of the order stays unpaid leaves an order
        // that can never complete - and hands those seats to someone else while the buyer's
        // payment session is still open. Expiry is driven from the order primary instead, so the
        // whole order goes when the SHORTEST leg's window elapses.
        $handled = [];

        foreach ($expiredSales as $sale) {
            $targetId = $this->expiryTargetId($sale);

            if (isset($handled[$targetId])) {
                continue;
            }
            $handled[$targetId] = true;

            // Re-read under lock: the target may be a different row than the one selected, and a
            // second elapsed leg of the same order must not expire it twice.
            $expired = \DB::transaction(function () use ($targetId) {
                $target = Sale::lockForUpdate()->find($targetId);

                if (! $target || $target->status !== 'unpaid') {
                    return null;
                }

                $target->status = 'expired';
                $target->save();

                return $target;
            });

            if ($expired) {
                AuditService::log(AuditService::SALE_EXPIRED, null, 'Sale', $expired->id,
                    ['status' => 'unpaid'], ['status' => 'expired'], 'auto_expire:event_id:'.$expired->event_id);
            }
        }

        $this->releaseGiftCardHolds();
    }

    /**
     * Two gift-card cleanups on a fixed 48h window (not the per-event
     * expire_unpaid_tickets opt-in):
     *
     * 1. Unpaid non-cash gift card purchases are cancelled. Cash cards are never
     *    auto-cancelled - the owner collects payment in person and marks them paid.
     * 2. Unpaid sales that redeemed a gift card are expired regardless of the
     *    event's expire_unpaid_tickets setting, so an abandoned Stripe checkout
     *    doesn't hold the deducted balance indefinitely. Expiring the sale fires
     *    the Sale::booted() restore hook, returning the balance to the card.
     */
    private function releaseGiftCardHolds(): void
    {
        $staleCards = GiftCard::where('status', 'unpaid')
            ->where('payment_method', '!=', 'cash')
            ->whereRaw('TIMESTAMPDIFF(HOUR, created_at, NOW()) >= 48')
            ->get();

        foreach ($staleCards as $card) {
            $cancelled = \DB::transaction(function () use ($card) {
                $locked = GiftCard::lockForUpdate()->find($card->id);
                if (! $locked || $locked->status !== 'unpaid') {
                    return false;
                }
                $locked->status = 'cancelled';
                $locked->save();

                return true;
            });

            if ($cancelled) {
                AuditService::log(AuditService::GIFT_CARD_CANCELLED, null, 'GiftCard', $card->id,
                    ['status' => 'unpaid'], ['status' => 'cancelled'], 'auto_expire:role_id:'.$card->role_id);
            }
        }

        // Select the primary/ungrouped sale of any group that redeemed a gift card. The gift
        // share may live on a GUEST row (when the primary seat's net was 0), so match on "any
        // row in the group carries gift_card_amount", not on the primary carrying gift_card_id.
        // Cash sales are excluded: like the card loop above, cash orders are never auto-expired -
        // a partial cash redemption's remainder is settled in person and the owner cancels it.
        $heldSales = Sale::where('sales.status', 'unpaid')
            ->where('sales.payment_method', '!=', 'cash')
            ->where(function ($q) {
                $q->whereNull('sales.group_id')->orWhereColumn('sales.group_id', 'sales.id');
            })
            ->whereRaw('TIMESTAMPDIFF(HOUR, sales.created_at, NOW()) >= 48')
            ->where(function ($q) {
                $q->where('sales.gift_card_amount', '>', 0)
                    ->orWhereExists(function ($sub) {
                        $sub->selectRaw('1')->from('sales as gs')
                            ->whereColumn('gs.group_id', 'sales.id')
                            ->where('gs.gift_card_amount', '>', 0);
                    })
                    // Across a multi-event order the card is applied leg by leg, so the share may
                    // sit on a leg other than the one being examined. Without this, a leg whose
                    // own share happened to be zero never releases the hold and the balance stays
                    // locked up indefinitely.
                    ->orWhereExists(function ($sub) {
                        $sub->selectRaw('1')->from('sales as os')
                            ->whereColumn('os.order_id', 'sales.order_id')
                            ->whereNotNull('os.order_id')
                            ->where('os.gift_card_amount', '>', 0);
                    });
            })
            ->get();

        $handled = [];

        foreach ($heldSales as $sale) {
            // Same rule as the window loop above: an order expires as a unit, from its primary.
            $targetId = $this->expiryTargetId($sale);

            if (isset($handled[$targetId])) {
                continue;
            }
            $handled[$targetId] = true;

            // Re-fetch under a lock and re-check status: a webhook may have marked the sale paid
            // between the query above and here - flipping paid->expired would wrongly restore the
            // gift balance (double-credit) and release tickets on an order that was actually paid.
            $expired = \DB::transaction(function () use ($targetId) {
                $locked = Sale::lockForUpdate()->find($targetId);
                if (! $locked || $locked->status !== 'unpaid') {
                    return null;
                }
                $locked->status = 'expired';
                $locked->save();

                return $locked;
            });

            if ($expired) {
                AuditService::log(AuditService::SALE_EXPIRED, null, 'Sale', $expired->id,
                    ['status' => 'unpaid'], ['status' => 'expired'], 'gift_card_hold:event_id:'.$expired->event_id);
            }
        }
    }

    /**
     * The row whose expiry releases this sale: its order's anchor, or itself.
     *
     * Falls back to the sale itself when the anchor has vanished. sales.order_id now carries an
     * ON DELETE SET NULL self-reference, so that should no longer happen - but rows written before
     * that migration can still hold a dangling id, and this loop is the only thing that ever
     * returns their seats and gift-card holds. Silently resolving to a missing row meant they were
     * skipped on every run, forever.
     */
    private function expiryTargetId(Sale $sale): int
    {
        if (! $sale->order_id || $sale->order_id === $sale->id) {
            return $sale->id;
        }

        if (! Sale::whereKey($sale->order_id)->exists()) {
            \Log::warning('Sale points at a missing order anchor; expiring it on its own', [
                'sale_id' => $sale->id,
                'order_id' => $sale->order_id,
            ]);

            return $sale->id;
        }

        return $sale->order_id;
    }
}
