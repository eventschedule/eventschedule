<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GiftCard extends Model
{
    public const CODE_ALPHABET = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

    public const CODE_LENGTH = 12;

    protected $fillable = [
        'role_id',
        'code',
        'secret',
        'amount',
        'remaining_amount',
        'currency_code',
        'status',
        'payment_method',
        'transaction_reference',
        'purchaser_name',
        'purchaser_email',
        'recipient_name',
        'recipient_email',
        'message',
        'valid_days',
        'activated_at',
        'expires_at',
    ];

    protected $casts = [
        'amount' => 'decimal:3',
        'remaining_amount' => 'decimal:3',
        'activated_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public static function generateCode(): string
    {
        do {
            $code = '';
            for ($i = 0; $i < self::CODE_LENGTH; $i++) {
                $code .= self::CODE_ALPHABET[random_int(0, strlen(self::CODE_ALPHABET) - 1)];
            }
        } while (self::where('code', $code)->exists());

        return $code;
    }

    public static function normalizeCode(?string $input): string
    {
        return strtoupper(preg_replace('/[^A-Za-z0-9]/', '', (string) $input));
    }

    public function formattedCode(): string
    {
        return implode('-', str_split($this->code, 4));
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function isDepleted(): bool
    {
        return (float) $this->remaining_amount <= 0;
    }

    public function isRedeemable(?string $currencyCode = null): bool
    {
        if ($this->status !== 'active' || $this->isExpired() || $this->isDepleted()) {
            return false;
        }

        if ($currencyCode !== null && strcasecmp($this->currency_code, $currencyCode) !== 0) {
            return false;
        }

        return true;
    }

    /**
     * Stored status plus the computed expired/depleted states, for UI pills.
     */
    public function displayStatus(): string
    {
        if ($this->status === 'active') {
            if ($this->isExpired()) {
                return 'expired';
            }
            if ($this->isDepleted()) {
                return 'depleted';
            }
        }

        return $this->status;
    }

    /**
     * Mark the card paid. The expiry window starts at activation (not purchase)
     * so cards awaiting cash payment don't silently burn validity days.
     * Callers must hold a lock and have checked the status precondition.
     */
    public function activate(?string $transactionReference = null): void
    {
        $this->status = 'active';
        $this->activated_at = now();
        $this->expires_at = $this->valid_days ? now()->addDays($this->valid_days) : null;
        if ($transactionReference !== null) {
            $this->transaction_reference = $transactionReference;
        }
        $this->save();
    }

    public function getViewUrl(): string
    {
        return route('gift_card.view', [
            'gift_card_id' => \App\Utils\UrlUtils::encodeId($this->id),
            'secret' => $this->secret,
        ]);
    }
}
