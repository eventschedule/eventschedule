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

        foreach ($expiredSales as $sale) {
            \DB::transaction(function () use ($sale) {
                $sale->status = 'expired';
                $sale->save();
            });

            AuditService::log(AuditService::SALE_EXPIRED, null, 'Sale', $sale->id,
                ['status' => 'unpaid'], ['status' => 'expired'], 'auto_expire:event_id:'.$sale->event_id);
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
                    });
            })
            ->get();

        foreach ($heldSales as $sale) {
            // Re-fetch under a lock and re-check status: a webhook may have marked the sale paid
            // between the query above and here - flipping paid->expired would wrongly restore the
            // gift balance (double-credit) and release tickets on an order that was actually paid.
            $expired = \DB::transaction(function () use ($sale) {
                $locked = Sale::lockForUpdate()->find($sale->id);
                if (! $locked || $locked->status !== 'unpaid') {
                    return false;
                }
                $locked->status = 'expired';
                $locked->save();

                return true;
            });

            if ($expired) {
                AuditService::log(AuditService::SALE_EXPIRED, null, 'Sale', $sale->id,
                    ['status' => 'unpaid'], ['status' => 'expired'], 'gift_card_hold:event_id:'.$sale->event_id);
            }
        }
    }
}
