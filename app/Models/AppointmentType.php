<?php

namespace App\Models;

use App\Utils\UrlUtils;
use Illuminate\Database\Eloquent\Model;

class AppointmentType extends Model
{
    protected $fillable = [
        'role_id',
        'name',
        'slug',
        'description',
        'duration_minutes',
        'slot_interval_minutes',
        'buffer_before_minutes',
        'buffer_after_minutes',
        'min_notice_hours',
        'max_advance_days',
        'weekly_windows',
        'date_overrides',
        'location_type',
        'location_address',
        'location_url',
        'location_phone',
        'price',
        'currency_code',
        'payment_method',
        'requires_approval',
        'capacity',
        'custom_fields',
        'ask_phone',
        'require_phone',
        'is_active',
        'is_deleted',
    ];

    protected $casts = [
        'weekly_windows' => 'array',
        'date_overrides' => 'array',
        'custom_fields' => 'array',
        'price' => 'decimal:2',
        'duration_minutes' => 'integer',
        'slot_interval_minutes' => 'integer',
        'buffer_before_minutes' => 'integer',
        'buffer_after_minutes' => 'integer',
        'min_notice_hours' => 'integer',
        'max_advance_days' => 'integer',
        'capacity' => 'integer',
        'requires_approval' => 'boolean',
        'ask_phone' => 'boolean',
        'require_phone' => 'boolean',
        'is_active' => 'boolean',
        'is_deleted' => 'boolean',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function events()
    {
        return $this->hasMany(Event::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->where('is_deleted', false);
    }

    /**
     * The schedule's timezone drives all slot math (windows are wall-clock in it).
     * Mirrors Event::scheduleTimezone() so bookings and events agree.
     */
    public function timezone(): string
    {
        return $this->role?->timezone ?: config('app.timezone');
    }

    /** Step between candidate slot starts; defaults to the appointment duration. */
    public function stepMinutes(): int
    {
        return $this->slot_interval_minutes ?: $this->duration_minutes;
    }

    public function isFree(): bool
    {
        return (float) $this->price <= 0;
    }

    /**
     * Hours an unpaid hold survives before app:release-tickets frees the slot.
     * Cash/free never auto-expire (ReleaseTickets has no cash exclusion).
     */
    public function expireHours(): int
    {
        return match ($this->payment_method) {
            'stripe' => 1,
            'payment_url' => 24,
            default => 0,
        };
    }

    /**
     * Whether the configured paid method is usable by the owner. Mirrors
     * Role::giftCardPaymentMethodAvailable(); appointments support stripe / payment_url / cash.
     */
    public function paymentMethodAvailable(): bool
    {
        $user = $this->role?->user;
        if (! $user) {
            return false;
        }

        return match ($this->payment_method) {
            'stripe' => $user->canAcceptStripePayments(),
            'payment_url' => (bool) ($user->payment_url && $user->payment_secret),
            default => true, // cash
        };
    }

    /**
     * A type a guest can actually book: active, not deleted, and either free or with a
     * working payment method. Misconfigured paid types are hidden from the guest surface.
     */
    public function isBookable(): bool
    {
        if (! $this->is_active || $this->is_deleted) {
            return false;
        }

        return $this->isFree() || $this->paymentMethodAvailable();
    }

    public function hashedId(): string
    {
        return UrlUtils::encodeId($this->id);
    }
}
