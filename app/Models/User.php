<?php

namespace App\Models;

use App\Casts\EncryptedString;
use App\Notifications\VerifyEmail as CustomVerifyEmail;
use App\Services\DemoService;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'timezone',
        'language_code',
        'stripe_account_id',
        'api_key',
        'api_key_hash',
        'invoiceninja_api_key',
        'invoiceninja_api_url',
        'invoiceninja_webhook_secret',
        'invoiceninja_mode',
        'payment_url',
        'payment_secret',
        'is_subscribed',
        // Note: is_admin intentionally NOT in $fillable to prevent mass assignment attacks
        // Admin status should only be set explicitly via $user->is_admin = true
        'google_id',
        'google_oauth_id',
        'google_token',
        'google_refresh_token',
        'google_token_expires_at',
        'facebook_id',
        'facebook_token',
        'facebook_token_expires_at',
        'phone',
        'phone_verified_at',
        'email_verified_at',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_content',
        'utm_term',
        'referrer_url',
        'landing_page',
        'referral_code',
        'referred_by_user_id',
        'use_24_hour_time',
        'two_factor_confirmed_at',
        'admin_newsletter_unsubscribed_at',
        'default_role_id',
        'dashboard_config',
        'carpool_agreed_at',
        'carpool_notifications_enabled',
    ];

    /**
     * The attributes that are guarded from mass assignment.
     *
     * @var array<int, string>
     */
    protected $guarded = [
        'is_admin', // Prevent privilege escalation via mass assignment
        'two_factor_secret', // Set explicitly in TwoFactorController only
        'two_factor_recovery_codes', // Set explicitly in TwoFactorController only
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'stripe_account_id',
        'invoiceninja_api_key',
        'invoiceninja_api_url',
        'invoiceninja_webhook_secret',
        'api_key',
        'api_key_hash',
        'payment_secret',
        'google_token',
        'google_refresh_token',
        'google_token_expires_at',
        'facebook_id',
        'facebook_token',
        'facebook_token_expires_at',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            $model->email = strtolower($model->email);
            if ($model->phone) {
                $model->phone = \App\Utils\PhoneUtils::normalize($model->phone);
            }
            if (! config('app.hosted') && $model->isDirty('phone')) {
                $model->phone_verified_at = $model->phone ? now() : null;
            }
        });

        static::updating(function ($user) {
            if ($user->isDirty('email') && (config('app.hosted'))) {
                $user->email_verified_at = null;
                $user->sendEmailVerificationNotification();
            }

            if ($user->isDirty('phone') && config('app.hosted')) {
                $user->phone_verified_at = null;
            }
        });
    }

    public function sendEmailVerificationNotification()
    {
        // Don't send if email is already verified
        if ($this->hasVerifiedEmail()) {
            return;
        }

        // Only send verification email if user is subscribed
        if ($this->is_subscribed !== false) {
            $this->notify(new CustomVerifyEmail('user'));
        }
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'google_token_expires_at' => 'datetime',
            'facebook_token_expires_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'use_24_hour_time' => 'boolean',
            'invoiceninja_api_key' => EncryptedString::class,
            'invoiceninja_webhook_secret' => EncryptedString::class,
            'google_token' => EncryptedString::class,
            'google_refresh_token' => EncryptedString::class,
            'facebook_token' => EncryptedString::class,
            'payment_secret' => EncryptedString::class,
            'api_key_expires_at' => 'datetime',
            'two_factor_secret' => EncryptedString::class,
            'two_factor_recovery_codes' => EncryptedString::class,
            'two_factor_confirmed_at' => 'datetime',
            'admin_newsletter_unsubscribed_at' => 'datetime',
            'dashboard_config' => 'array',
            'carpool_agreed_at' => 'datetime',
            'carpool_notifications_enabled' => 'boolean',
        ];
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class)
            ->withTimestamps()
            ->withPivot('level')
            ->where('is_deleted', false)
            ->orderBy('name');
    }

    public function defaultRole()
    {
        return $this->belongsTo(Role::class, 'default_role_id');
    }

    public function countRoles()
    {
        return count($this->roles()->get());
    }

    public function owner()
    {
        return $this->roles()->wherePivotIn('level', ['owner']);
    }

    public function member()
    {
        return $this->roles()->wherePivotIn('level', ['owner', 'admin', 'viewer']);
    }

    public function editor()
    {
        return $this->roles()->wherePivotIn('level', ['owner', 'admin']);
    }

    public function following()
    {
        return $this->roles()->wherePivot('level', 'follower');
    }

    public function carpoolOffers()
    {
        return $this->hasMany(CarpoolOffer::class);
    }

    public function carpoolRequests()
    {
        return $this->hasMany(CarpoolRequest::class);
    }

    public function supportConversation()
    {
        return $this->hasOne(SupportConversation::class);
    }

    public function hasAgreedToCarpool()
    {
        return $this->carpool_agreed_at !== null;
    }

    public function carpoolRating()
    {
        return once(fn () => CarpoolReview::where('reviewed_user_id', $this->id)->avg('rating'));
    }

    public function carpoolReviewCount()
    {
        return once(fn () => CarpoolReview::where('reviewed_user_id', $this->id)->count());
    }

    public function venues()
    {
        return $this->editor()->type('venue');
    }

    public function talents()
    {
        return $this->editor()->type('talent');
    }

    public function curators()
    {
        return $this->editor()->type('curator');
    }

    public function allCurators()
    {
        return $this->roles()
            ->where('type', 'curator')
            ->where(function ($query) {
                $query->whereIn('roles.id', function ($subquery) {
                    $subquery->select('role_id')
                        ->from('role_user')
                        ->where('user_id', $this->id)
                        ->whereIn('level', ['owner', 'admin']);
                })
                    ->orWhere(function ($q) {
                        $q->whereIn('roles.id', function ($subquery) {
                            $subquery->select('role_id')
                                ->from('role_user')
                                ->where('user_id', $this->id)
                                ->where('level', 'follower');
                        })
                            ->where('accept_requests', true);
                    });
            })
            ->orderBy('name')
            ->get();
    }

    public function tickets()
    {
        return $this->hasMany(Sale::class);
    }

    public function webhooks()
    {
        return $this->hasMany(Webhook::class);
    }

    public function claimRolesByPhone(string $phone): bool
    {
        $phone = \App\Utils\PhoneUtils::normalize($phone);
        if (! $phone) {
            return false;
        }

        $claimed = false;

        DB::transaction(function () use ($phone, &$claimed) {
            // Clear phone from other users first to maintain uniqueness
            $phoneMatchUsers = User::where('phone', $phone)
                ->where('id', '!=', $this->id)
                ->whereNull('password')
                ->whereNull('google_id')
                ->whereNull('google_oauth_id')
                ->whereNull('facebook_id')
                ->lockForUpdate()
                ->get();

            // The query above already filters for stub users (all auth fields null),
            // so every user in this collection is guaranteed to be a stub
            foreach ($phoneMatchUsers as $phoneUser) {
                foreach ($phoneUser->roles()->withPivot('level')->get() as $role) {
                    if (! $this->roles()->where('roles.id', $role->id)->exists()) {
                        $this->roles()->attach($role->id, ['level' => $role->pivot->level, 'created_at' => now()]);
                        $claimed = true;
                    }
                }
                $phoneUser->roles()->detach();
                $phoneUser->default_role_id = null;
                $phoneUser->password = null;
                $phoneUser->phone = null;
                $phoneUser->phone_verified_at = null;
                $phoneUser->saveQuietly();
            }

            // Use saveQuietly to bypass boot hooks that null phone_verified_at when phone changes
            // Skip if user already has a different verified phone to avoid silent overwrite
            if ($this->phone && $this->phone !== $phone && $this->hasVerifiedPhone()) {
                // Keep existing phone, still claim roles below
            } else {
                try {
                    $this->phone = $phone;
                    $this->phone_verified_at = now();
                    $this->saveQuietly();
                } catch (\Illuminate\Database\QueryException $e) {
                    // Phone uniqueness conflict - skip setting phone but continue claiming roles
                    report($e);
                    $this->refresh();
                }
            }

            // Claim unclaimed schedules (Role records) with this phone
            $phoneClaims = Role::where('phone', $phone)
                ->whereNull('user_id')
                ->where('created_at', '>=', now()->subYear())
                ->lockForUpdate()
                ->get();

            foreach ($phoneClaims as $claimRole) {
                $claimRole->user_id = $this->id;
                $claimRole->phone_verified_at = now();
                $claimRole->save();

                $this->roles()->syncWithoutDetaching([$claimRole->id => ['level' => 'owner', 'created_at' => now()]]);

                if (! $this->default_role_id) {
                    $this->default_role_id = $claimRole->id;
                    $this->saveQuietly();
                }

                $claimed = true;
            }
        });

        return $claimed;
    }

    public function isMember($subdomain): bool
    {
        return $this->member()->where('subdomain', $subdomain)->exists();
    }

    public function isEditor($subdomain): bool
    {
        return $this->editor()->where('subdomain', $subdomain)->exists();
    }

    public function isViewer($subdomain): bool
    {
        return $this->roles()->where('subdomain', $subdomain)->wherePivot('level', 'viewer')->exists();
    }

    public function isFollowing($subdomain): bool
    {
        return $this->following()->where('subdomain', $subdomain)->exists();
    }

    public function isConnected($subdomain): bool
    {
        return $this->roles()->where('subdomain', $subdomain)->exists();
    }

    public function paymentUrlHost()
    {
        $host = parse_url($this->payment_url, PHP_URL_HOST);

        $host = str_replace('www.', '', $host);

        return $host;
    }

    public function paymentUrlMobileOnly()
    {
        $host = $this->paymentUrlHost();

        $mobileOnly = [
            'venmo.com',
            'cash.app',
            'paytm.me',
            'phon.pe',
            'bitpay.co.il',
            'payboxapp.com',
            'qr.alipay.com',
            'tikkie.me',
        ];

        return in_array($host, $mobileOnly);
    }

    public function canEditEvent($event)
    {
        if ($this->id == $event->user_id) {
            return true;
        }

        // Check if user has owner or admin role level for any role associated with this event
        // (not just any member - followers should not be able to edit)
        foreach ($event->roles as $role) {
            $pivot = $this->roles()
                ->where('roles.id', $role->id)
                ->wherePivotIn('level', ['owner', 'admin'])
                ->first();

            if ($pivot) {
                return true;
            }
        }

        return false;
    }

    public function canScanEvent($event)
    {
        if ($this->canEditEvent($event)) {
            return true;
        }

        // Viewers can also scan tickets
        foreach ($event->roles as $role) {
            $pivot = $this->roles()
                ->where('roles.id', $role->id)
                ->wherePivot('level', 'viewer')
                ->first();

            if ($pivot) {
                return true;
            }
        }

        return false;
    }

    public function getProfileImageUrlAttribute($value)
    {
        if (! $value) {
            return '';
        }

        if (str_starts_with($value, 'http')) {
            return $value;
        }

        if (config('app.hosted') && config('filesystems.default') == 'do_spaces') {
            return 'https://eventschedule.nyc3.cdn.digitaloceanspaces.com/'.$value;
        } elseif (config('filesystems.default') == 'local') {
            return url('/storage/'.$value);
        } else {
            return $value;
        }
    }

    public function isAdmin(): bool
    {
        return (bool) $this->is_admin;
    }

    public function hasVerifiedPhone(): bool
    {
        return ! is_null($this->phone_verified_at);
    }

    public function isStub(): bool
    {
        return is_null($this->password)
            && is_null($this->google_id)
            && is_null($this->google_oauth_id)
            && is_null($this->facebook_id);
    }

    /**
     * Get the user's first name
     */
    public function firstName(): string
    {
        if (! $this->name) {
            return 'there';
        }

        $nameParts = explode(' ', trim($this->name));

        return $nameParts[0] ?: 'there';
    }

    /**
     * Check if user has Google Calendar connected
     */
    public function hasGoogleCalendarConnected(): bool
    {
        return ! is_null($this->google_token) && ! is_null($this->google_refresh_token);
    }

    /**
     * Check if user has two-factor authentication enabled and confirmed.
     */
    public function hasTwoFactorEnabled(): bool
    {
        return ! is_null($this->two_factor_secret) && ! is_null($this->two_factor_confirmed_at);
    }

    /**
     * Check if user has a password set (vs Google-only OAuth user)
     */
    public function hasPassword(): bool
    {
        return $this->password !== null;
    }

    /**
     * Check if user's language is RTL (right-to-left)
     */
    public function isRtl(): bool
    {
        $languageCode = $this->language_code;

        if (DemoService::isDemoUser($this) && session()->has('demo_language')) {
            $languageCode = session('demo_language');
        }

        return in_array($languageCode, ['ar', 'he']);
    }

    /**
     * Check if user can accept Stripe payments
     * In hosted mode: requires Stripe Connect completed
     * In self-hosted mode: requires platform Stripe keys configured
     */
    public function canAcceptStripePayments(): bool
    {
        if (config('app.hosted')) {
            return (bool) $this->stripe_completed_at;
        }

        return (bool) config('services.stripe_platform.secret');
    }

    public function referrals()
    {
        return $this->hasMany(Referral::class, 'referrer_user_id');
    }

    public function referredBy()
    {
        return $this->belongsTo(User::class, 'referred_by_user_id');
    }

    public function getSignupSourceDisplay(): array
    {
        $primary = null;
        $parts = [];

        if ($this->referred_by_user_id && $this->referredBy) {
            $primary = 'Referral: '.$this->referredBy->name;
        } elseif (! empty($this->utm_source)) {
            $primary = ucwords(strtolower($this->utm_source));
            if (! empty($this->utm_medium)) {
                $primary .= ' / '.$this->utm_medium;
            }
        }

        $referrerHost = null;
        if (! empty($this->referrer_url)) {
            $host = parse_url($this->referrer_url, PHP_URL_HOST);
            if ($host) {
                $referrerHost = preg_replace('/^www\./', '', $host);
            }
        }

        if (! $primary && $referrerHost) {
            $primary = $referrerHost;
        } elseif ($referrerHost) {
            $parts[] = $referrerHost;
        }

        if (! empty($this->landing_page)) {
            $parts[] = $this->landing_page;
        }

        return [
            'primary' => $primary ?? 'Direct',
            'secondary' => implode(' -> ', $parts),
            'landing' => $this->landing_page,
        ];
    }

    public function getOrCreateReferralCode(): string
    {
        if ($this->referral_code) {
            return $this->referral_code;
        }

        do {
            $code = strtolower(substr(str_replace(['/', '+', '='], '', base64_encode(random_bytes(6))), 0, 8));
        } while (self::where('referral_code', $code)->exists());

        $this->referral_code = $code;
        $this->save();

        return $code;
    }

    public function referralUrl(): string
    {
        return url('/?ref='.$this->getOrCreateReferralCode());
    }
}
