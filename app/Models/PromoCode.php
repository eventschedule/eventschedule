<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromoCode extends Model
{
    protected $fillable = [
        'event_id',
        'code',
        'type',
        'value',
        'max_uses',
        'times_used',
        'expires_at',
        'is_active',
        'ticket_ids',
    ];

    protected $casts = [
        'ticket_ids' => 'array',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function isValid()
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        if ($this->max_uses !== null && $this->times_used >= $this->max_uses) {
            return false;
        }

        return true;
    }

    public function appliesToTicket($ticketId)
    {
        if (empty($this->ticket_ids)) {
            return true;
        }

        return in_array($ticketId, $this->ticket_ids);
    }

    public function calculateDiscount($saleTickets)
    {
        $eligibleSubtotal = 0;

        foreach ($saleTickets as $saleTicket) {
            if ($this->appliesToTicket($saleTicket->ticket_id)) {
                $eligibleSubtotal += $saleTicket->ticket->price * $saleTicket->quantity;
            }
        }

        if ($eligibleSubtotal <= 0) {
            return 0;
        }

        $value = max(0, $this->value);
        if ($this->type === 'percentage') {
            $value = min($value, 100);
            $discount = $eligibleSubtotal * ($value / 100);
        } else {
            $discount = $value;
        }

        return round(min($discount, $eligibleSubtotal), 2);
    }
}
