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
        if (! $this->opened_at) {
            $this->opened_at = now();
        }
        $this->open_count++;
        $this->save();
    }

    public function recordClick(string $url, ?string $ipAddress = null, ?string $userAgent = null): void
    {
        if (! $this->clicked_at) {
            $this->clicked_at = now();
        }
        $this->click_count++;
        $this->save();

        $this->clicks()->create([
            'url' => $url,
            'clicked_at' => now(),
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent ? substr($userAgent, 0, 500) : null,
        ]);
    }
}
