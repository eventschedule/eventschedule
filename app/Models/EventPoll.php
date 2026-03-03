<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventPoll extends Model
{
    protected $fillable = [
        'event_id',
        'question',
        'options',
        'is_active',
        'sort_order',
        'allow_user_options',
        'require_option_approval',
        'pending_options',
    ];

    protected $casts = [
        'options' => 'array',
        'is_active' => 'boolean',
        'allow_user_options' => 'boolean',
        'require_option_approval' => 'boolean',
        'pending_options' => 'array',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function votes()
    {
        return $this->hasMany(EventPollVote::class);
    }

    public function getResults(?string $eventDate = null): array
    {
        $query = $this->votes()
            ->selectRaw('option_index, COUNT(*) as count');

        if ($eventDate !== null) {
            $query->where('event_date', $eventDate);
        }

        return $query->groupBy('option_index')
            ->pluck('count', 'option_index')
            ->toArray();
    }

    public function getUserVote(int $userId, ?string $eventDate = null): ?int
    {
        $query = $this->votes()->where('user_id', $userId);

        if ($eventDate !== null) {
            $query->where('event_date', $eventDate);
        }

        $vote = $query->first();

        return $vote ? $vote->option_index : null;
    }
}
