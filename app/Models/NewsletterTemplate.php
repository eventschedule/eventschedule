<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsletterTemplate extends Model
{
    protected $fillable = [
        'role_id',
        'user_id',
        'name',
        'blocks',
        'style_settings',
        'template',
        'is_system',
    ];

    protected $casts = [
        'blocks' => 'array',
        'style_settings' => 'array',
        'is_system' => 'boolean',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
