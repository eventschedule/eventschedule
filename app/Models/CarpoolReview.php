<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CarpoolReview extends Model
{
    protected $fillable = [
        'carpool_offer_id',
        'reviewer_user_id',
        'reviewed_user_id',
        'rating',
        'comment',
    ];

    protected $casts = [
        'rating' => 'integer',
    ];

    public function offer()
    {
        return $this->belongsTo(CarpoolOffer::class, 'carpool_offer_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_user_id');
    }

    public function reviewed()
    {
        return $this->belongsTo(User::class, 'reviewed_user_id');
    }
}
