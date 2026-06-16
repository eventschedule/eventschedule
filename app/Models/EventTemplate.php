<?php

namespace App\Models;

use App\Utils\UrlUtils;
use Illuminate\Database\Eloquent\Model;

class EventTemplate extends Model
{
    protected $fillable = [
        'role_id',
        'user_id',
        'name',
        'template_data',
    ];

    protected $casts = [
        'template_data' => 'array',
    ];

    public function role()
    {
        return $this->belongsTo(\App\Models\Role::class);
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function encodeId()
    {
        return UrlUtils::encodeId($this->id);
    }
}
