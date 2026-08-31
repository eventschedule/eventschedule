<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * An account-less member of a schedule's audience. See the migration for why there is no
 * unsubscribed_at column: newsletter_unsubscribes (role_id, email) is the single suppression list,
 * shared with the newsletter composer.
 */
class RoleSubscriber extends Model
{
    protected $fillable = [
        'role_id',
        'email',
        'name',
        'locale',
        'source',
        'confirmed_at',
        'token',
        'confirm_token',
        'ip_address',
    ];

    protected $casts = [
        'confirmed_at' => 'datetime',
    ];

    /**
     * Lowercased and trimmed on the way in so it matches what NewsletterService lowercases in PHP
     * when it subtracts newsletter_unsubscribes, and so the (role_id, email) unique index actually
     * catches a resubmission that differs only in case.
     */
    public function setEmailAttribute($value): void
    {
        $this->attributes['email'] = strtolower(trim((string) $value));
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /** Only confirmed rows are ever mailed. */
    public function scopeConfirmed($query)
    {
        return $query->whereNotNull('confirmed_at');
    }

    public function isConfirmed(): bool
    {
        return ! is_null($this->confirmed_at);
    }

    public static function newToken(): string
    {
        return Str::random(64);
    }
}
