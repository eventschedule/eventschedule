<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CarpoolRequest extends Model
{
    protected $fillable = [
        'carpool_offer_id',
        'user_id',
        'message',
        'status',
        'reminder_sent_at',
    ];

    protected $casts = [
        'reminder_sent_at' => 'datetime',
    ];

    public function offer()
    {
        return $this->belongsTo(CarpoolOffer::class, 'carpool_offer_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
