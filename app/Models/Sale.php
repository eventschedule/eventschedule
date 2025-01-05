<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable = [
        'name',
        'email',
        'secret',
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function tickets()
    {
        return $this->hasMany(SalesTicket::class);
    }
}
