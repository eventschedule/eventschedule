<?php

namespace App\Models;

use App\Traits\HasFanSubmitter;
use Illuminate\Database\Eloquent\Model;

class EventVideo extends Model
{
    use HasFanSubmitter;

    protected $fillable = [
        'event_id',
        'event_part_id',
        'event_date',
        'user_id',
        'guest_name',
        'guest_email',
        'youtube_url',
        'is_approved',
    ];

    protected $casts = [
        'is_approved' => 'boolean',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function eventPart()
    {
        return $this->belongsTo(EventPart::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected function fanContentType(): string
    {
        return 'video';
    }

    protected function fanContentApiFields(): array
    {
        return [
            'youtube_url' => $this->youtube_url,
            'embed_url' => \App\Utils\UrlUtils::getYouTubeEmbed($this->youtube_url),
        ];
    }
}
