<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleTicket extends Model
{
    protected $fillable = [
        'quantity',        
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }
}
