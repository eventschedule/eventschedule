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
            $saleTicket->ticket->updateSold($saleTicket->sale->event_date, $saleTicket->quantity());
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

    public function quantity()
    {
        return count(json_decode($this->seats, true));
    }
}
