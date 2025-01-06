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
        'date',
    ];

    protected static function booted()
    {
        static::updated(function ($sale) {
            if ($sale->isDirty('status') && ($sale->status === 'cancelled' || $sale->status === 'refunded')) {
                foreach ($sale->saleTickets as $saleTicket) {
                    $saleTicket->ticket->decrement('sold', $saleTicket->quantity);
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
}
