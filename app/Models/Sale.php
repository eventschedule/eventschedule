<?php

namespace App\Models;

use App\Jobs\NotifyWaitlist;
use App\Utils\UrlUtils;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable = [
        'event_id',
        'user_id',
        'name',
        'email',
        'phone',
        'secret',
        'event_date',
        'subdomain',
        'status',
        'payment_method',
        'payment_amount',
        'transaction_reference',
        'custom_value1',
        'custom_value2',
        'custom_value3',
        'custom_value4',
        'custom_value5',
        'custom_value6',
        'custom_value7',
        'custom_value8',
        'custom_value9',
        'custom_value10',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'boost_campaign_id',
        'newsletter_id',
        'promo_code_id',
        'discount_amount',
        'gift_card_id',
        'gift_card_amount',
        'volume_discount_amount',
        'feedback_sent_at',
        'group_id',
        'order_id',
        'guest_timezone',
        'reminder_sent_at',
        'confirmed_at',
    ];

    protected $casts = [
        'feedback_sent_at' => 'datetime',
        'reminder_sent_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    protected static bool $cascadingGroup = false;

    protected static function booted()
    {
        static::saving(function ($sale) {
            if ($sale->phone) {
                $sale->phone = \App\Utils\PhoneUtils::normalize($sale->phone);
            }

            // Stamp the moment this sale became paid. Done here rather than at the dozen-odd
            // paid-transition call sites (cash checkout, mark-paid, the Stripe / Invoice Ninja
            // webhooks, payment-url success, appointments, the API) so no path can be missed.
            // Covers both creation-as-paid (RSVP, free, appointments) and later transitions.
            if ($sale->status === 'paid' && ! $sale->paid_at) {
                $sale->paid_at = now();
            } elseif ($sale->isDirty('status') && $sale->status === 'paid' && $sale->paid_at) {
                // Re-paid after a refund or cancellation: re-stamp, or the sale would keep the
                // original month's timestamp and never consume the allowance of the month it was
                // actually paid in.
                $original = $sale->getOriginal('status');

                if (in_array($original, ['cancelled', 'refunded', 'expired'], true)) {
                    $sale->paid_at = now();
                }
            }
        });

        static::updated(function ($sale) {
            if ($sale->isDirty('status') && $sale->status === 'paid') {
                TicketWaitlist::where('event_id', $sale->event_id)
                    ->where('event_date', $sale->event_date)
                    ->where('email', $sale->email)
                    ->whereIn('status', ['waiting', 'notified'])
                    ->update(['status' => 'purchased']);

                // Cascade paid status to the rest of the order, or of the group.
                //
                // An order primary covers everything in one query because guest rows carry
                // order_id too, so this never nests an order cascade around a group one.
                $siblings = $sale->statusCascadeQuery();

                if ($siblings) {
                    // Never revive a row that was already released. Expiry, cancellation and refund
                    // each gave the seats back and restored any gift-card balance, and this raw
                    // update does not re-take them - so paying over the top of one oversells the
                    // event. Reachable on an order: an owner can cancel a single leg while the rest
                    // is still unpaid and the buyer's payment session is open. Mirrors the
                    // whereNotIn on the cancel cascade below.
                    $payable = (clone $siblings)->whereNotIn('status', ['cancelled', 'refunded', 'expired']);

                    // Raw update, so paid_at has to be set explicitly here - the saving() hook
                    // that normally stamps it does not run for a query-builder update.
                    (clone $payable)->where('status', '!=', 'paid')
                        ->update(['status' => 'paid', 'paid_at' => $sale->paid_at ?? now()]);

                    // Clear their waitlist entries (the raw update above skips booted hooks).
                    // Matched per row's OWN event and date: inside a group those equal the
                    // primary's, but the legs of a multi-event order sit on different events.
                    $rows = (clone $payable)->get(['email', 'event_id', 'event_date']);
                    foreach ($rows->groupBy(fn ($row) => $row->event_id.'|'.$row->event_date) as $occurrence) {
                        TicketWaitlist::where('event_id', $occurrence->first()->event_id)
                            ->where('event_date', $occurrence->first()->event_date)
                            ->whereIn('email', $occurrence->pluck('email'))
                            ->whereIn('status', ['waiting', 'notified'])
                            ->update(['status' => 'purchased']);
                    }
                }
            }

            if ($sale->isDirty('status') && in_array($sale->status, ['cancelled', 'refunded', 'expired'])) {
                // Appointment bookings: releasing the sale frees the slot by soft-cancelling the
                // backing event. The service is re-entrant safe (skips an already-cancelled event)
                // and defers the calendar-sync delete + guest mail to DB::afterCommit.
                if ($sale->event && $sale->event->appointment_type_id && ! $sale->event->is_cancelled) {
                    app(\App\Services\AppointmentService::class)->cancelFromSale($sale);
                }

                if ($sale->payment_method === 'rsvp' && $sale->event) {
                    $sale->event->updateRsvpSold($sale->event_date, -1);

                    // Only decrement analytics for primary/ungrouped sales (1 sale = 1 analytics entry)
                    if (! $sale->group_id || $sale->isPrimarySale()) {
                        AnalyticsEventsDaily::decrementSale($sale->event_id, 0, $sale->created_at->toDateString());
                    }
                } else {
                    foreach ($sale->saleTickets as $saleTicket) {
                        if ($saleTicket->ticket) {
                            $saleTicket->ticket->updateSold($sale->event_date, -$saleTicket->quantity);
                        }
                    }
                }

                // Give the redemption back once per LEG, matching how checkout takes it:
                // priceSaleLeg() increments times_used once per leg, but this hook runs for every
                // row, and the cancel cascade saves each guest row individually so its inventory is
                // released. Ungated, a four-seat order took 1 and gave back 4, driving the counter
                // negative and letting a max_uses-capped code be redeemed past its cap.
                if ($sale->promo_code_id && (! $sale->group_id || $sale->isPrimarySale())) {
                    PromoCode::where('id', $sale->promo_code_id)
                        ->lockForUpdate()
                        ->decrement('times_used');
                }

                // Restore the redeemed amount to the gift card. Guarded so a
                // cancelled→refunded transition doesn't credit twice, and only
                // active cards are credited (never cancelled/refunded ones).
                // The face-value cap protects against any residual double-fire.
                if ($sale->gift_card_id && $sale->gift_card_amount > 0
                    && ! in_array($sale->getOriginal('status'), ['cancelled', 'refunded', 'expired'])) {
                    $giftCard = GiftCard::where('id', $sale->gift_card_id)->lockForUpdate()->first();
                    if ($giftCard && $giftCard->status === 'active') {
                        $restore = min(
                            (float) $sale->gift_card_amount,
                            (float) $giftCard->amount - (float) $giftCard->remaining_amount
                        );
                        if ($restore > 0) {
                            $giftCard->increment('remaining_amount', $restore);
                        }
                    }
                }

                // Only dispatch waitlist notification from primary or ungrouped sales
                if (! $sale->group_id || $sale->isPrimarySale()) {
                    NotifyWaitlist::dispatch($sale->event_id, $sale->event_date);
                }

                // Cascade cancel/refund/expired to the rest of the order, or of the group.
                //
                // Unlike the paid cascade this saves each row so its own booted hooks run and
                // release that row's inventory - which is why the order case must select every row
                // in one flat query: $cascadingGroup is a single process-global flag, so a group
                // cascade nested inside an order cascade would be silently swallowed.
                $siblings = $sale->statusCascadeQuery();

                if ($siblings && ! static::$cascadingGroup) {
                    static::$cascadingGroup = true;
                    try {
                        $affected = $siblings
                            ->whereNotIn('status', ['cancelled', 'refunded', 'expired'])
                            // Expiry releases what nobody has paid for; cancel and refund
                            // deliberately do reach a paid row. Without this an order whose legs
                            // diverged - the buyer paid one leg in cash at the door while the rest
                            // stayed unpaid - had the collected leg flipped to 'expired' by the
                            // ReleaseTickets cron: seats handed back, gift-card share re-credited,
                            // no refund, and the revenue left on the books.
                            ->when($sale->status === 'expired', fn ($query) => $query->where('status', 'unpaid'))
                            ->get();
                        foreach ($affected as $sibling) {
                            $sibling->status = $sale->status;
                            $sibling->save();
                        }
                    } finally {
                        static::$cascadingGroup = false;
                    }
                }
            }
        });
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function saleTickets()
    {
        return $this->hasMany(SaleTicket::class);
    }

    /**
     * Whether this sale includes a season-pass ticket (valid for all dates of
     * a recurring event, scanned once per occurrence).
     */
    public function isPass(): bool
    {
        $this->loadMissing('saleTickets.ticket');

        return $this->saleTickets->contains(fn ($saleTicket) => $saleTicket->ticket?->is_pass);
    }

    /**
     * The booking guest's own timezone, when a recognised one was captured - otherwise null so
     * callers fall back to the schedule's zone.
     *
     * Validated on read as well as on write: BackupService restores this column straight from the
     * archive, so a legacy or hand-edited row can still hold anything.
     */
    public function guestTimezone(): ?string
    {
        return \App\Utils\AppointmentTimeUtils::resolveTimezone($this->guest_timezone);
    }

    public function boostCampaign()
    {
        return $this->belongsTo(BoostCampaign::class);
    }

    public function promoCode()
    {
        return $this->belongsTo(PromoCode::class);
    }

    public function giftCard()
    {
        return $this->belongsTo(GiftCard::class);
    }

    public function feedback()
    {
        return $this->hasOne(EventFeedback::class);
    }

    public function groupedSales()
    {
        return $this->hasMany(Sale::class, 'group_id', 'group_id');
    }

    public function guestSales()
    {
        return $this->hasMany(Sale::class, 'group_id', 'id')
            ->whereColumn('sales.id', '!=', 'sales.group_id');
    }

    public function isPrimarySale()
    {
        return $this->group_id && $this->group_id === $this->id;
    }

    /**
     * Every row a buyer paid for in one checkout, across all of its events.
     *
     * Guest rows carry order_id too, not just the leg primaries, so this is the complete set and
     * never needs a second hop through group_id.
     */
    public function orderSales()
    {
        return $this->hasMany(Sale::class, 'order_id', 'order_id');
    }

    /** The row that anchors a multi-event order: same self-referencing idiom as isPrimarySale(). */
    public function isOrderPrimary(): bool
    {
        return $this->order_id && $this->order_id === $this->id;
    }

    /**
     * One row per EVENT this checkout covered: the leg primaries of the order, or just this sale
     * when it does not anchor one.
     *
     * This is the set every per-EVENT side effect has to walk once a single payment can span
     * several events. Analytics, confirmation emails and sale.* webhooks are each attributed to one
     * event, so driving them from the order primary alone credits one event with the whole order
     * and leaves the rest with nothing.
     *
     * Guest rows are excluded on purpose - a guest's money already sits inside its leg primary's
     * legTotal*() - which makes summing legTotalPayment() over this set exactly orderTotalPayment().
     * Same primaries-and-ungrouped predicate TicketController::viewOrder() uses.
     *
     * $includeDeleted exists for the one caller that runs AFTER the rows are flagged: the
     * sale.deleted webhook. Live-rows-only there would deliver nothing at all.
     */
    public function orderLegs(bool $includeDeleted = false): \Illuminate\Support\Collection
    {
        if (! $this->isOrderPrimary()) {
            return collect([$this]);
        }

        return static::where('order_id', $this->order_id)
            ->when(! $includeDeleted, fn ($query) => $query->where('is_deleted', false))
            ->where(fn ($query) => $query->whereNull('group_id')->orWhereColumn('group_id', 'id'))
            ->orderBy('id')
            ->get();
    }

    /**
     * The rows a status change on this sale carries with it, or null when it drives no cascade.
     *
     * The order branch deliberately selects the whole order in ONE flat query rather than walking
     * leg primaries and letting each cascade its own guests. Guest rows carry order_id, so they are
     * already in this set - and the nested alternative does not work: static::$cascadingGroup is a
     * single process-global flag, so the inner group cascades would be suppressed by the outer
     * order one and those rows would never change status.
     *
     * A leg primary that is not the order primary still cascades its own guests, which is what
     * makes cancelling one leg of an order work.
     *
     * With order_id null everywhere - every sale written before this feature - this returns exactly
     * what the old `group_id && isPrimarySale()` condition did.
     */
    protected function statusCascadeQuery(): ?\Illuminate\Database\Eloquent\Builder
    {
        if ($this->isOrderPrimary()) {
            return static::where('order_id', $this->order_id)->where('id', '!=', $this->id);
        }

        if ($this->group_id && $this->isPrimarySale()) {
            return static::where('group_id', $this->group_id)->where('id', '!=', $this->id);
        }

        return null;
    }

    /*
     * Order totals. Use these only where the money genuinely spans events - what the buyer was
     * charged in one Stripe session, and the reconciliation that checks it. Anything attributed to
     * a single event (analytics, a schedule's revenue) wants legTotal*() instead, or one event's
     * figures end up carrying another's.
     */

    public function orderTotalPayment(): float
    {
        if (! $this->order_id) {
            return $this->legTotalPayment();
        }

        return (float) Sale::where('order_id', $this->order_id)
            ->where('is_deleted', false)
            ->sum('payment_amount');
    }

    public function orderTotalDiscount(): float
    {
        if (! $this->order_id) {
            return $this->legTotalDiscount();
        }

        return (float) Sale::where('order_id', $this->order_id)
            ->where('is_deleted', false)
            ->sum('discount_amount');
    }

    public function orderTotalGiftCard(): float
    {
        if (! $this->order_id) {
            return $this->legTotalGiftCard();
        }

        return (float) Sale::where('order_id', $this->order_id)
            ->where('is_deleted', false)
            ->sum('gift_card_amount');
    }

    /*
     * groupTotal*() vs legTotal*(): read this before using either.
     *
     * groupTotal*() is the sum over a whole group. Calling it on a GUEST row returns the whole
     * group's total, which is almost never what a caller wants - add it up across the group and
     * you count the money once per row.
     *
     * legTotal*() is "the amount this row is answerable for", which is what nearly every call site
     * actually means: the group total on a primary, its own value on a guest row or an ungrouped
     * sale. It is the concept the `isPrimarySale() ? groupTotalX() : own_value` ternary used to
     * spell out by hand at twenty-odd sites.
     *
     * The distinction only earns its keep once a sale can also belong to a multi-event ORDER, at
     * which point "the group total" and "what the buyer paid" stop being the same number. Reach for
     * legTotal*() by default; a site that genuinely means the whole order should say so explicitly.
     */

    public function legTotalPayment(): float
    {
        return $this->isPrimarySale()
            ? $this->groupTotalPayment()
            : (float) $this->payment_amount;
    }

    public function legTotalDiscount(): float
    {
        return $this->isPrimarySale()
            ? $this->groupTotalDiscount()
            : (float) ($this->discount_amount ?? 0);
    }

    public function legTotalGiftCard(): float
    {
        return $this->isPrimarySale()
            ? $this->groupTotalGiftCard()
            : (float) ($this->gift_card_amount ?? 0);
    }

    public function legTotalQuantity(): int
    {
        return $this->isPrimarySale()
            ? $this->groupTotalQuantity()
            : (int) $this->quantity();
    }

    public function groupTotalPayment()
    {
        if (! $this->group_id) {
            return (float) $this->payment_amount;
        }

        return (float) Sale::where('group_id', $this->group_id)
            ->where('is_deleted', false)
            ->sum('payment_amount');
    }

    public function groupTotalQuantity()
    {
        if (! $this->group_id) {
            return $this->quantity();
        }

        return (int) Sale::where('sales.group_id', $this->group_id)
            ->where('sales.is_deleted', false)
            ->join('sale_tickets', 'sales.id', '=', 'sale_tickets.sale_id')
            ->join('tickets', 'sale_tickets.ticket_id', '=', 'tickets.id')
            ->where('tickets.is_addon', false)
            ->sum('sale_tickets.quantity');
    }

    public function groupTotalDiscount()
    {
        if (! $this->group_id) {
            return (float) ($this->discount_amount ?? 0);
        }

        return (float) Sale::where('group_id', $this->group_id)
            ->where('is_deleted', false)
            ->sum('discount_amount');
    }

    public function groupTotalGiftCard()
    {
        if (! $this->group_id) {
            return (float) ($this->gift_card_amount ?? 0);
        }

        return (float) Sale::where('group_id', $this->group_id)
            ->where('is_deleted', false)
            ->sum('gift_card_amount');
    }

    public function isRsvp()
    {
        return $this->payment_method === 'rsvp';
    }

    public function calculateTotal()
    {
        return $this->saleTickets->sum(function ($saleTicket) {
            return $saleTicket->ticket->price * $saleTicket->quantity;
        });
    }

    public function quantity()
    {
        return $this->saleTickets->sum(function ($saleTicket) {
            if ($saleTicket->ticket && $saleTicket->ticket->is_addon) {
                return 0;
            }

            return $saleTicket->quantity;
        });
    }

    public function getEventUrl()
    {
        $event = $this->event;

        return $event->getGuestUrl($this->subdomain, $this->event_date);
    }

    public function toApiData($includeSecret = false)
    {
        $data = new \stdClass;

        $data->id = UrlUtils::encodeId($this->id);
        $data->event_id = UrlUtils::encodeId($this->event_id);
        $data->event_name = $this->event?->name;
        $data->subdomain = $this->subdomain;
        $data->name = $this->name;
        $data->email = $this->email;
        $data->phone = $this->phone;
        $data->event_date = $this->event_date;
        $data->status = $this->status;
        $data->payment_method = $this->payment_method;
        // Webhook contract: primary holds the group totals; guest rows zero them out so external subscribers
        // iterating a group don't double-count. Standalone (non-grouped) sales report their own values.
        if ($this->isPrimarySale()) {
            $data->payment_amount = $this->groupTotalPayment();
            $groupVolume = (float) Sale::where('group_id', $this->group_id)
                ->where('is_deleted', false)
                ->sum('volume_discount_amount');
            $data->volume_discount_amount = $groupVolume > 0 ? $groupVolume : null;
            $groupDiscount = $this->groupTotalDiscount();
            $data->discount_amount = $groupDiscount > 0 ? $groupDiscount : null;
            $groupGiftCard = $this->groupTotalGiftCard();
            $data->gift_card_amount = $groupGiftCard > 0 ? $groupGiftCard : null;
        } elseif ($this->group_id) {
            $data->payment_amount = 0.0;
            $data->volume_discount_amount = null;
            $data->discount_amount = null;
            $data->gift_card_amount = null;
        } else {
            $data->payment_amount = (float) $this->payment_amount;
            $data->volume_discount_amount = $this->volume_discount_amount !== null ? (float) $this->volume_discount_amount : null;
            $data->discount_amount = $this->discount_amount !== null ? (float) $this->discount_amount : null;
            $data->gift_card_amount = $this->gift_card_amount !== null ? (float) $this->gift_card_amount : null;
        }
        // Rows a buyer paid for in one checkout that spanned several events share this. Exposed as
        // its own field rather than by widening payment_amount: the primary/guest contract above
        // is what stops a subscriber double-counting, and inflating a leg primary's total to the
        // order's would break it for anyone summing across an order.
        $data->order_id = $this->order_id ? UrlUtils::encodeId($this->order_id) : null;
        $data->is_order_primary = $this->isOrderPrimary();

        $data->transaction_reference = $this->transaction_reference;

        // Include secret when explicitly requested (e.g. webhook payloads) or when the authenticated user is authorized
        if ($includeSecret) {
            $data->secret = $this->secret;
        } else {
            $authUser = auth()->user();
            if ($authUser && ($authUser->id === $this->user_id || $authUser->id === $this->event?->user_id)) {
                $data->secret = $this->secret;
            }
        }

        $data->created_at = $this->created_at ? $this->created_at->toIso8601String() : null;
        $data->updated_at = $this->updated_at ? $this->updated_at->toIso8601String() : null;

        // Include tickets
        $data->tickets = $this->saleTickets->filter(fn ($saleTicket) => $saleTicket->ticket)
            ->map(function ($saleTicket) {
                $row = [
                    'ticket_id' => UrlUtils::encodeId($saleTicket->ticket_id),
                    'quantity' => $saleTicket->quantity,
                    'price' => (float) $saleTicket->ticket->price,
                    'type' => $saleTicket->ticket->type,
                    'is_addon' => (bool) $saleTicket->ticket->is_addon,
                    'is_pass' => (bool) $saleTicket->ticket->is_pass,
                ];

                if ($saleTicket->ticket->is_pass) {
                    $row['pass_usage_type'] = $saleTicket->ticket->pass_usage_type;
                    $row['pass_visits_used'] = $saleTicket->passUsageCount();
                    $row['pass_max_uses'] = $saleTicket->ticket->pass_max_uses;
                    $row['pass_expires_at'] = $saleTicket->pass_expires_at?->toIso8601String();
                }

                return $row;
            })->values();

        // total_quantity stays per-row (same as before Fix 1 — primary's SaleTicket was already qty=1 then);
        // subscribers can still sum across the group to get the group total, matching the old contract.
        $data->total_quantity = $this->quantity();
        $data->group_id = $this->group_id ? UrlUtils::encodeId($this->group_id) : null;
        $data->is_primary = $this->isPrimarySale();

        return $data;
    }

    public function scopeExcludeTestEmails($query)
    {
        $testDomains = [
            '@example.com', '@example.org', '@example.net',
            '@test.com', '@test.org', '@test.net', '@localhost',
        ];

        foreach ($testDomains as $domain) {
            $query->where('email', 'not like', '%'.$domain);
        }

        return $query->whereNotNull('email')->where('email', '!=', '');
    }
}
