<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportConversation extends Model
{
    protected $fillable = [
        'user_id',
        'status',
        'last_message_at',
        'guest_token',
        'guest_name',
        'guest_email',
        'guest_page',
        'guest_country',
        'last_emailed_message_id',
    ];

    /**
     * The token is the visitor's only credential for reading the thread.
     */
    protected $hidden = [
        'guest_token',
    ];

    protected function casts(): array
    {
        return [
            'last_message_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function messages()
    {
        return $this->hasMany(SupportMessage::class)->orderBy('created_at', 'asc');
    }

    public function latestMessage()
    {
        return $this->hasOne(SupportMessage::class)->latestOfMany();
    }

    public function unreadForAdmin()
    {
        return $this->messages()->where('is_from_admin', false)->whereNull('read_at');
    }

    public function unreadForUser()
    {
        return $this->messages()->where('is_from_admin', true)->whereNull('read_at');
    }

    /**
     * A signed-out visitor from the marketing site, as opposed to an account holder in the AP.
     */
    public function isGuest(): bool
    {
        return $this->user_id === null;
    }

    public function displayName(): string
    {
        if ($this->isGuest()) {
            return $this->guest_name ?: ($this->guest_email ?: 'Website visitor');
        }

        return $this->user?->name ?: ($this->user?->email ?? '');
    }

    /**
     * What a visitor told us to call them, or null. displayName() falls back to "Website
     * visitor" for the inbox; an email subject reads better with nothing than with that.
     */
    public function visitorLabel(): ?string
    {
        return $this->guest_name ?: ($this->guest_email ?: null);
    }

    public function contactEmail(): ?string
    {
        return $this->isGuest() ? $this->guest_email : $this->user?->email;
    }

    /**
     * The cache key a polling client refreshes, read to decide whether they are still looking
     * at the chat (and so need no email) and, for a visitor, which page they are on.
     */
    public function presenceKey(): string
    {
        return $this->isGuest()
            ? "support_guest_online_{$this->id}"
            : "support_user_online_{$this->user_id}";
    }

    public function typingKey(): string
    {
        return self::typingKeyFor($this->id);
    }

    public static function typingKeyFor(int $id): string
    {
        return "support_typing_{$id}";
    }
}
