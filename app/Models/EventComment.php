<?php

namespace App\Models;

use App\Traits\HasFanSubmitter;
use Illuminate\Database\Eloquent\Model;

class EventComment extends Model
{
    use HasFanSubmitter;

    protected $fillable = [
        'event_id',
        'event_part_id',
        'event_date',
        'user_id',
        'guest_name',
        'guest_email',
        'comment',
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
        return 'comment';
    }

    protected function fanContentApiFields(): array
    {
        return [
            'comment' => $this->comment,
        ];
    }
}
