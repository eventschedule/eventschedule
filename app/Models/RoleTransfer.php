<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * A pending or resolved schedule ownership handover (discussion #119).
 *
 * The token in the emailed link identifies the offer; it never authorises it. Accepting
 * additionally requires being signed in as `to_email`, which is the "email-based
 * verification" half of the flow.
 */
class RoleTransfer extends Model
{
    /** How long an offer stays open. Evaluated at read time, like roles.plan_expires. */
    public const EXPIRY_DAYS = 7;

    protected $fillable = [
        'role_id',
        'from_user_id',
        'to_email',
        'to_user_id',
        'status',
        'keep_previous_owner',
        'expires_at',
        'responded_at',
    ];

    protected $casts = [
        'keep_previous_owner' => 'boolean',
        'expires_at' => 'datetime',
        'responded_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($transfer) {
            if (! $transfer->token) {
                $transfer->token = Str::random(48);
            }

            if (! $transfer->expires_at) {
                $transfer->expires_at = now()->addDays(self::EXPIRY_DAYS);
            }
        });

        static::saving(function ($transfer) {
            if ($transfer->to_email) {
                $transfer->to_email = strtolower(trim($transfer->to_email));
            }
        });
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function fromUser()
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    public function toUser()
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }

    /**
     * Still awaiting a decision. Expiry is not a stored status - nothing sweeps the
     * table - so every read has to ask, and scopeOpen() below is the query mirror.
     * Keep the two in sync.
     */
    public function isOpen(): bool
    {
        return $this->status === 'pending'
            && $this->expires_at
            && $this->expires_at->isFuture();
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'pending')->where('expires_at', '>', now());
    }

    /** Does this offer belong to the signed-in user? The token alone is never enough. */
    public function isFor(?User $user): bool
    {
        return $user && strtolower($user->email) === $this->to_email;
    }
}
