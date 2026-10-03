<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsletterClick extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'newsletter_recipient_id',
        'url',
        'clicked_at',
    ];

    protected $casts = [
        'clicked_at' => 'datetime',
    ];

    public function recipient()
    {
        return $this->belongsTo(NewsletterRecipient::class, 'newsletter_recipient_id');
    }
}
