<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportMessage extends Model
{
    protected $fillable = [
        'support_conversation_id',
        'user_id',
        'body',
        'is_from_admin',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'is_from_admin' => 'boolean',
            'read_at' => 'datetime',
        ];
    }

    public function conversation()
    {
        return $this->belongsTo(SupportConversation::class, 'support_conversation_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
