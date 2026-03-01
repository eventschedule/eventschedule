<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventFeedback extends Model
{
    protected $table = 'event_feedbacks';

    protected $fillable = [
        'event_id',
        'sale_id',
        'event_date',
        'rating',
        'comment',
    ];

    protected $casts = [
        'rating' => 'integer',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }
}
