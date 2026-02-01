<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsletterSegmentUser extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'newsletter_segment_id',
        'user_id',
        'email',
        'name',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function segment()
    {
        return $this->belongsTo(NewsletterSegment::class, 'newsletter_segment_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
