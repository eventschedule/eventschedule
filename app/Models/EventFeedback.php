<?php

namespace App\Models;

use App\Utils\UrlUtils;
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

    /**
     * Owner-facing API shape. Feedback is only reachable through an API key scoped to a
     * schedule the caller owns or administers, so the attendee's contact details are
     * included the same way they are on the sales endpoints.
     */
    public function toApiData()
    {
        $data = new \stdClass;

        $data->id = UrlUtils::encodeId($this->id);
        $data->event_id = UrlUtils::encodeId($this->event_id);
        $data->event_name = $this->event?->name;
        $data->event_date = $this->event_date;
        $data->rating = $this->rating;
        $data->comment = $this->comment;
        $data->attendee_name = $this->sale?->name;
        $data->attendee_email = $this->sale?->email;
        $data->created_at = $this->created_at?->toIso8601String();

        return $data;
    }
}
