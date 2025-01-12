<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable = [
        'event_id',
        'name',
        'email',
        'secret',
        'event_date',
    ];

    protected static function booted()
    {
        static::updated(function ($sale) {
            if ($sale->isDirty('status') && ($sale->status === 'cancelled' || $sale->status === 'refunded')) {
                foreach ($sale->saleTickets as $saleTicket) {
                    $saleTicket->ticket->updateSold($sale->event_date, -$saleTicket->quantity());
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

    public function calculateTotal()
    {
        return $this->saleTickets->sum(function ($saleTicket) {
            return $saleTicket->ticket->price * $saleTicket->quantity();
        });
    }
}
