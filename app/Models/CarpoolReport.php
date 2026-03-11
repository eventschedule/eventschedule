<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CarpoolReport extends Model
{
    protected $fillable = [
        'reporter_user_id',
        'reported_user_id',
        'carpool_offer_id',
        'reason',
    ];

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_user_id');
    }

    public function reported()
    {
        return $this->belongsTo(User::class, 'reported_user_id');
    }

    public function offer()
    {
        return $this->belongsTo(CarpoolOffer::class, 'carpool_offer_id');
    }
}
