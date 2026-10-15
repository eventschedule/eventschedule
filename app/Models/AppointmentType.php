<?php

namespace App\Models;

use App\Services\Payments\PaymentGatewayManager;
use App\Utils\UrlUtils;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AppointmentType extends Model
{
    /**
     * A URL-safe, non-empty, per-schedule-unique booking slug.
     *
     * The slug is the `/book/{typeSlug}` path segment and the column carries no unique index,
     * so a duplicate does not error - `AppointmentController::resolveBookableType()` just
     * takes the first match and the guest is quietly booked onto the wrong type, at the wrong
     * duration and price. Str::slug() returns "" for Hebrew, CJK and similar, which made every
     * non-Latin type on a schedule collide exactly that way.
     *
     * Romanizes before giving up, so Hebrew types get readable slugs rather than a run of
     * `appointment`, `appointment-2`, `appointment-3`.
     */
    public static function uniqueSlug(Role $role, ?string $name, ?string $preferred = null): string
    {
        $base = '';

        foreach ([$preferred, $name] as $candidate) {
            $candidate = trim((string) $candidate);

            if ($candidate === '') {
                continue;
            }

            $base = Str::slug($candidate);

            if ($base === '') {
                $base = Str::slug(Role::transliterateToAscii($candidate));
            }

            if ($base !== '') {
                break;
            }
        }

        $base = rtrim(Str::limit($base, 180, ''), '-') ?: 'appointment';

        $slug = $base;
        $i = 2;

        while (self::where('role_id', $role->id)->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }

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
        'paid_grandfathered_at' => 'datetime',
        'host_released_at' => 'datetime',
        'host_user_id' => 'integer',
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

    /**
     * The team member this type is booked with, or none (AppointmentHostService). Not fillable:
     * set only by AppointmentTypeController, which checks the person is an owner or admin here.
     */
    public function host()
    {
        return $this->belongsTo(User::class, 'host_user_id');
    }

    /**
     * Per-instance answers to hostIsCurrent(), keyed by the host they were asked about: a list
     * page asks it of every type more than once, and a save may change the host in between.
     *
     * @var array<int, bool>
     */
    protected array $hostIsCurrentCache = [];

    /**
     * Whether the type's host may still host it: an owner or admin of this schedule. True for a
     * type with no host. Asked again at booking (AppointmentService::book()), because a host can
     * leave between the page and the post.
     */
    public function hostIsCurrent(): bool
    {
        $hostId = (int) $this->host_user_id;
        if (! $hostId) {
            return true;
        }

        return $this->hostIsCurrentCache[$hostId] ??= \App\Services\AppointmentHostService::isEligible((int) $this->role_id, $hostId);
    }

    /** The host's name as a guest sees it ("with Sam"), or null. Never their address. */
    public function hostName(): ?string
    {
        if (! $this->host_user_id) {
            return null;
        }

        $name = trim((string) $this->host?->name);

        return $name !== '' ? $name : null;
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
     * Whether this type may take money.
     *
     * The appointment mirror of Event::canSellPaidTickets(), with the same four arms in the same
     * order: a free type books on every tier, a priced one needs Pro. This predicate answers only
     * the money question - how MANY types a schedule may offer is Role::appointmentTypeLimit(),
     * applied separately in bookableAppointmentTypes().
     *
     * Fails closed on a schedule-less type. role_id is constrained() and not nullable, so that
     * cannot happen today, but the predicate must not be the thing that assumes it.
     */
    public function canTakePayment(): bool
    {
        // Selfhost resolves to the top tier, so it short-circuits before anything can deny it.
        if (! config('app.hosted')) {
            return true;
        }

        $role = $this->role;

        // The stamp is one-time, set by the 2026_09_21 migration for every priced type that
        // existed when charging became a Pro feature. See that migration for why it is stored
        // rather than re-derived.
        if ($role?->isPro() || $this->paid_grandfathered_at !== null) {
            return true;
        }

        return is_demo_role($role);
    }

    /**
     * Whether a booking on this method waits for an online payment: unconfirmed, not remindable
     * and not reschedulable until paid, and released if the hold lapses. True for every registered
     * gateway except one paid by the owner's own instructions (cash today), which is settled by hand
     * and confirms at booking time. Asked of the driver rather than of a name, as
     * PaymentGatewayManager asks every capability.
     *
     * Takes the stored method rather than a type because callers hold an event or a sale, whose
     * payment_method was copied from the type when the booking was made.
     */
    public static function paysOnline(?string $method): bool
    {
        $driver = app(PaymentGatewayManager::class)->get($method);

        return $driver !== null && ! $driver->usesPaymentInstructions();
    }

    /**
     * Hours an unpaid hold survives before app:release-tickets frees the slot, as the gateway
     * decides (PaymentGatewayDriver::appointmentHoldHours()): an hour for Stripe, a day for the
     * payment URL and Payfast, never for cash/free (ReleaseTickets has no cash exclusion).
     */
    public function expireHours(): int
    {
        return app(PaymentGatewayManager::class)->get($this->payment_method)?->appointmentHoldHours() ?? 0;
    }

    /**
     * Whether the configured paid method is usable by the owner. Stripe and the payment URL keep
     * their appointment-specific checks (the URL also needs its secret, for the success callback);
     * any other gateway must take bookings at all, and be connected and able to take this currency
     * and price, which is the same test the event Payment dropdown applies
     * (PaymentGatewayManager::availableFor()).
     */
    public function paymentMethodAvailable(): bool
    {
        $user = $this->role?->user;
        if (! $user) {
            return false;
        }

        return match ($this->payment_method) {
            // Stripe's floor as well: below it the session cannot be created, and the booking
            // would hold its slot and fail at checkout.
            'stripe' => $user->canAcceptStripePayments() && $this->clearsFloorOf('stripe'),
            'payment_url' => (bool) ($user->payment_url && $user->payment_secret),
            null, 'cash' => true,
            default => app(PaymentGatewayManager::class)->get($this->payment_method)?->supportsAppointments()
                && array_key_exists(
                    $this->payment_method,
                    app(PaymentGatewayManager::class)->availableFor($user, $this->currency_code, (float) $this->price),
                ),
        };
    }

    /** Whether the price is at least the gateway's own minimum in this currency, if it has one. */
    private function clearsFloorOf(string $gateway): bool
    {
        [$minimum] = app(PaymentGatewayManager::class)->get($gateway)?->amountLimits(strtoupper((string) ($this->currency_code ?: 'USD'))) ?? [null];

        return $minimum === null || (float) $this->price >= $minimum;
    }

    /**
     * A type a guest can actually book: active, not deleted, and either free or priced with both
     * the plan and a working payment method behind it. Misconfigured paid types are hidden from
     * the guest surface, and so are priced ones on a schedule that may not charge.
     *
     * This is the Ticket::isSellable() analogue and the ONLY enforcement point the booking path
     * needs: AppointmentController::book() resolves through Role::bookableAppointmentTypes(),
     * which filters on this. Deliberately not consulted by AppointmentRescheduleGate, which must
     * keep working for a booking already taken.
     */
    public function isBookable(): bool
    {
        if (! $this->is_active || $this->is_deleted) {
            return false;
        }

        // A host who has left the team: releaseHost() switches such a type off, so this only
        // matters in the moment between the two. No plan check here: a schedule that dropped
        // from Enterprise keeps its host types bookable, because a booking is not team access.
        if (! $this->hostIsCurrent()) {
            return false;
        }

        return $this->isFree()
            || ($this->canTakePayment() && $this->paymentMethodAvailable());
    }

    public function hashedId(): string
    {
        return UrlUtils::encodeId($this->id);
    }
}
