<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsletterRecipient extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'newsletter_id',
        'user_id',
        'email',
        'name',
        'token',
        'status',
        'sent_at',
        'error_message',
        'opened_at',
        'open_count',
        'clicked_at',
        'click_count',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'opened_at' => 'datetime',
        'clicked_at' => 'datetime',
    ];

    public function newsletter()
    {
        return $this->belongsTo(Newsletter::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function clicks()
    {
        return $this->hasMany(NewsletterClick::class);
    }

    public function recordOpen(): void
    {
        $this->increment('open_count');

        if (! $this->opened_at) {
            $this->update(['opened_at' => now()]);
        }
    }

    public function recordClick(string $url, ?string $ipAddress = null, ?string $userAgent = null): void
    {
        $this->increment('click_count');

        if (! $this->clicked_at) {
            $this->update(['clicked_at' => now()]);
        }

        $this->clicks()->create([
            'url' => $url,
            'clicked_at' => now(),
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent ? substr($userAgent, 0, 500) : null,
        ]);
    }
}
