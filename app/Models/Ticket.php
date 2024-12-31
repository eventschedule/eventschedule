<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $fillable = [
        'type',
        'quantity',
        'sold',
        'price',
        'description',
    ];

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}