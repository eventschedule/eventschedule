<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleTicket extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'sale_id',
        'ticket_id',
        'quantity',        
    ];

    protected static function booted()
    {
        static::created(function ($saleTicket) {
            $saleTicket->ticket->increment('sold', $saleTicket->quantity);
        });
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
