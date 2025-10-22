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
        'quantity',
    ];

    protected static function booted()
    {
        static::created(function ($saleTicket) {
            $saleTicket->ticket->updateSold($saleTicket->sale->event_date, $saleTicket->quantity);
        });
    }

    public function hasBeenScanned(): bool
    {
        if (! $this->seats) {
            return false;
        }

        $seats = json_decode($this->seats, true);

        if (! is_array($seats)) {
            return false;
        }

        foreach ($seats as $value) {
            if (! empty($value)) {
                return true;
            }
        }

        return false;
    }

    public function getUsageStatusAttribute(): string
    {
        return $this->hasBeenScanned() ? 'used' : 'unused';
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }
}
