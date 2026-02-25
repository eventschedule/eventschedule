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
    ];

    protected $casts = [
        'options' => 'array',
        'is_active' => 'boolean',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function votes()
    {
        return $this->hasMany(EventPollVote::class);
    }

    public function getResults(): array
    {
        return $this->votes()
            ->selectRaw('option_index, COUNT(*) as count')
            ->groupBy('option_index')
            ->pluck('count', 'option_index')
            ->toArray();
    }

    public function getUserVote(int $userId): ?int
    {
        $vote = $this->votes()->where('user_id', $userId)->first();

        return $vote ? $vote->option_index : null;
    }
}
