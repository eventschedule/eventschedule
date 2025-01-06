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
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function saleTickets()
    {
        return $this->hasMany(SaleTicket::class);
    }
}
