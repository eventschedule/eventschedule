<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleTicketEntry extends Model
{
    protected $fillable = [
        'sale_ticket_id',
        'secret',
        'seat_number',
        'scanned_at',
    ];

    protected $casts = [
        'scanned_at' => 'datetime',
    ];

    public function saleTicket()
    {
        return $this->belongsTo(SaleTicket::class);
    }
}
