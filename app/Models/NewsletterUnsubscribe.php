<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsletterUnsubscribe extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'role_id',
        'email',
        'unsubscribed_at',
    ];

    protected $casts = [
        'unsubscribed_at' => 'datetime',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }
}
