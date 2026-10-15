<?php

namespace App\Models;

use App\Casts\EncryptedString;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * An account whose schedules boost under its own Facebook Page and Meta ad account, with every
 * boost approved and marked paid by that account before it launches. See the boost_partners
 * migration for the two connection methods.
 *
 * forRole() / forEvent() are the one rule for "does this boost go through a partner". Once a
 * partner row exists for the account that owns a schedule, a boost on that schedule can never
 * fall back to the platform's own Page, even before the partner has connected.
 */
class BoostPartner extends Model
{
    public const METHOD_PARTNER_SHARE = 'partner_share';

    public const METHOD_LOGIN = 'login';

    protected $hidden = [
        'meta_token',
        'stripe_customer_id',
        'stripe_payment_method_id',
    ];

    protected $fillable = [
        'payment_instructions',
    ];

    protected function casts(): array
    {
        return [
            'enabled_at' => 'datetime',
            'confirmed_at' => 'datetime',
            'meta_token' => EncryptedString::class,
            'meta_token_expires_at' => 'datetime',
            'connection_checked_at' => 'datetime',
            'needs_attention_at' => 'datetime',
            'fee_rate' => 'decimal:4',
            'carry_forward' => 'decimal:2',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function campaigns()
    {
        return $this->hasMany(BoostCampaign::class);
    }

    public function statements()
    {
        return $this->hasMany(BoostStatement::class);
    }

    /**
     * The partner covering a schedule: the one belonging to the account that owns it.
     */
    public static function forRole(?Role $role): ?self
    {
        if (! $role || ! $role->user_id || ! config('app.hosted')) {
            return null;
        }

        return static::where('user_id', $role->user_id)->whereNotNull('enabled_at')->first();
    }

    /**
     * The partner covering an event, decided by the schedule that OWNS the event and never by
     * the schedule a boost is started from: an event owned by a partner's schedule could
     * otherwise be boosted through a venue, performer or curator schedule it is also on, under
     * the platform's Page and billing.
     */
    public static function forEvent(?Event $event): ?self
    {
        if (! $event || ! config('app.hosted')) {
            return null;
        }

        $roleId = $event->creator_role_id
            ?: DB::table('event_role')->where('event_id', $event->id)->orderBy('id')->value('role_id');

        return $roleId ? static::forRole(Role::find($roleId)) : null;
    }

    public static function forUser(?User $user): ?self
    {
        if (! $user || ! config('app.hosted')) {
            return null;
        }

        return static::where('user_id', $user->id)->whereNotNull('enabled_at')->first();
    }

    public function hasAssets(): bool
    {
        return ! empty($this->ad_account_id) && ! empty($this->page_id);
    }

    /**
     * Whether a request can be sent to Meta now: assets chosen and, for a partner share, the
     * one-time confirmation given. A login confirms itself.
     */
    public function isUsable(): bool
    {
        // A connection whose last check failed is never usable (a disabled ad account, a
        // currency that does not match the requests waiting on it), and both methods need their
        // confirmation: an admin's for a partner share, a passed check for a login.
        if (! $this->hasAssets() || $this->needs_attention_at || $this->connection_error || ! $this->confirmed_at) {
            return false;
        }

        if ($this->connection_method === self::METHOD_LOGIN) {
            return ! empty($this->meta_token);
        }

        return $this->connection_method === self::METHOD_PARTNER_SHARE;
    }

    /**
     * not_connected | waiting | connected | needs_attention, for the status chip.
     */
    public function connectionState(): string
    {
        if (! $this->hasAssets()) {
            return 'not_connected';
        }

        if ($this->needs_attention_at || $this->connection_error) {
            return 'needs_attention';
        }

        return $this->isUsable() ? 'connected' : 'waiting';
    }

    /**
     * The token a campaign under this partner is sent with: the partner's own after a login,
     * the platform's system-user token for a partner share.
     */
    /**
     * The partner's own token after a login (empty when Meta withdrew it), or null for a
     * partner share, which runs on the platform's system-user token.
     */
    public function accessToken(): ?string
    {
        if ($this->connection_method === self::METHOD_LOGIN) {
            return (string) $this->meta_token;
        }

        return null;
    }

    public function hasCard(): bool
    {
        return ! empty($this->stripe_payment_method_id);
    }

    public function displayName(): string
    {
        return $this->page_name ?: ($this->user?->name ?? '');
    }

    public function pendingRequestCount(): int
    {
        return $this->campaigns()->whereIn('status', ['pending_approval', 'awaiting_payment'])->count();
    }
}
