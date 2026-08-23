<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleTicket extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'sale_id',
        'ticket_id',
        'seats',
        'pass_checkins',
        'pass_usages',
        'pass_expires_at',
        'quantity',
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
    ];

    protected $casts = [
        'pass_checkins' => 'array',
        'pass_usages' => 'array',
        'pass_expires_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($saleTicket) {
            // A pass / subscription is a single redeemable unit (usage is logged
            // on the SaleTicket, not per seat), so force quantity 1 and compute
            // its expiry from the validity window at purchase time. Runs on every
            // creation path; backup restore uses saveQuietly() and is exempt.
            $ticket = $saleTicket->ticket
                ?: ($saleTicket->ticket_id ? Ticket::find($saleTicket->ticket_id) : null);

            if ($ticket && $ticket->is_pass) {
                $saleTicket->quantity = 1;
                if (! $saleTicket->pass_expires_at && $ticket->pass_valid_days) {
                    $saleTicket->pass_expires_at = now()->addDays((int) $ticket->pass_valid_days);
                }
            }
        });

        static::created(function ($saleTicket) {
            if ($saleTicket->ticket && $saleTicket->sale) {
                $saleTicket->ticket->updateSold($saleTicket->sale->event_date, $saleTicket->quantity);
            }
        });
    }

    /**
     * The physical seats this line holds, as human references.
     *
     * NOTE the near-miss: the `seats` COLUMN on this table is a JSON map of admission slot to
     * check-in timestamp and carries no location. Seat location only ever lives on
     * seating_seats.sale_ticket_id, which is what this reads.
     *
     * Empty for every quantity-based ticket, so the render sites can call it unconditionally.
     */
    public function seatLabels(): array
    {
        return \App\Models\SeatingSeat::with(['section', 'seatingTable'])
            ->where('sale_ticket_id', $this->id)
            ->orderBy('row_position')->orderBy('position')
            ->get()
            ->map(fn ($seat) => $seat->fullLabel())
            ->filter()
            ->values()
            ->all();
    }

    public function seatingSeats()
    {
        return $this->hasMany(SeatingSeat::class);
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    /**
     * The kind of a pass_usages entry: 'booking' (advance reservation),
     * 'redemption' (scanned in), or 'forfeited' (late cancellation - visit
     * consumed, seat released). Legacy entries without a kind are redemptions.
     */
    public static function usageKind(array $usage): string
    {
        return $usage['kind'] ?? 'redemption';
    }

    /**
     * Number of recorded pass / subscription redemptions (one per event-day).
     */
    public function passUsageCount(): int
    {
        return count($this->pass_usages ?? []);
    }

    /**
     * Visits remaining for a fixed-count pass, or null when not applicable
     * (unlimited / per-event / season pass).
     */
    public function passRemaining(): ?int
    {
        $ticket = $this->ticket;
        if ($ticket && $ticket->pass_usage_type === 'total' && $ticket->pass_max_uses) {
            return max(0, (int) $ticket->pass_max_uses - $this->passUsageCount());
        }

        return null;
    }

    public function passIsExpired(): bool
    {
        return $this->pass_expires_at && $this->pass_expires_at->isPast();
    }
}
