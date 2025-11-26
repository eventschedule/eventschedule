<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\SystemRole;
use App\Services\Authorization\AuthorizationService;
use App\Notifications\VerifyEmail as CustomVerifyEmail;

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
        'profile_image_url',
        'api_key',
        'google_id',
        'google_token',
        'google_refresh_token',
        'google_token_expires_at',
        'facebook_id',
        'facebook_token',
        'facebook_token_expires_at',
        'stripe_account_id',
        'stripe_company_name',
        'stripe_completed_at',
        'invoiceninja_api_key',
        'invoiceninja_api_url',
        'invoiceninja_company_name',
        'invoiceninja_webhook_secret',
        'payment_url',
        'payment_secret',
        'is_subscribed',
        'profile_image_id',
        'sso_subject',
        'password_hash',
        'totp_secret',
        'totp_recovery_codes',
        'totp_confirmed_at',
        'last_login_at',
        'venue_scope',
        'curator_scope',
        'talent_scope',
        'venue_ids',
        'curator_ids',
        'talent_ids',
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
        'payment_secret',
        'google_token',
        'google_refresh_token',
        'facebook_token',
        'totp_secret',
        'totp_recovery_codes',
        'password_hash',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            $model->email = strtolower($model->email);

            if ($model->isDirty('password')) {
                $model->password_hash = $model->password;
            }
        });

        static::updating(function ($user) {
            if ($user->isDirty('email') && (config('app.hosted'))) {
                $user->email_verified_at = null;
                $user->sendEmailVerificationNotification();
            }
        });

        static::created(function (User $user) {
            if (! Schema::hasTable('user_roles')) {
                return;
            }

            if (DB::table('user_roles')->exists()) {
                return;
            }

            $superRole = SystemRole::where('slug', 'superadmin')->first();

            if (! $superRole) {
                return;
            }

            $user->systemRoles()->syncWithoutDetaching([$superRole->id]);

            if (app()->bound(AuthorizationService::class)) {
                app(AuthorizationService::class)->forgetUserPermissions($user);
            }
        });
    }
    
    public function sendEmailVerificationNotification()
    {
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
            'password' => 'hashed',
            'google_token_expires_at' => 'datetime',
            'facebook_token_expires_at' => 'datetime',
            'stripe_completed_at' => 'datetime',
            'is_subscribed' => 'boolean',
            'totp_confirmed_at' => 'datetime',
            'totp_recovery_codes' => 'array',
            'last_login_at' => 'datetime',
            'venue_ids' => 'array',
            'curator_ids' => 'array',
            'talent_ids' => 'array',
        ];
    }

    public function hasSystemRoleSlug(string $slug): bool
    {
        if ($this->relationLoaded('systemRoles')) {
            return $this->systemRoles->contains('slug', $slug);
        }

        return $this->systemRoles()->where('slug', $slug)->exists();
    }

    public function systemRoles(): BelongsToMany
    {
        return $this->belongsToMany(SystemRole::class, 'user_roles', 'user_id', 'role_id')
            ->withTimestamps();
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class)
                    ->withTimestamps()
                    ->withPivot('level')
                    ->where('is_deleted', false)
                    ->orderBy('name');
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
        return $this->roles()->wherePivotIn('level', ['owner', 'admin']);
    }

    public function following()
    {
        return $this->roles()->wherePivot('level', 'follower');
    }

    public function profileImage(): BelongsTo
    {
        return $this->belongsTo(Image::class, 'profile_image_id');
    }

    public function hasPermission(string $permissionKey): bool
    {
        if (! app()->bound(AuthorizationService::class)) {
            return false;
        }

        return app(AuthorizationService::class)->userHasPermission($this, $permissionKey);
    }

    public function hasAnyPermission(string ...$permissionKeys): bool
    {
        if (count($permissionKeys) === 1 && is_array($permissionKeys[0])) {
            $permissionKeys = $permissionKeys[0];
        }

        if (! app()->bound(AuthorizationService::class)) {
            return false;
        }

        return app(AuthorizationService::class)->userHasAnyPermission($this, $permissionKeys);
    }

    public function venues()
    {
        return $this->member()->type('venue');
    }

    public function talents()
    {
        return $this->member()->type('talent');
    }

    public function curators()
    {
        return $this->member()->type('curator');
    }

    public function allCurators()
    {
        return $this->roles()
                    ->where('type', 'curator')
                    ->where(function($query) {
                        $query->whereIn('roles.id', function($subquery) {
                            $subquery->select('role_id')
                                    ->from('role_user')
                                    ->where('user_id', $this->id)
                                    ->whereIn('level', ['owner', 'admin']);
                        })
                        ->orWhere(function($q) {
                            $q->whereIn('roles.id', function($subquery) {
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

    public function isMember($subdomain): bool
    {
        return $this->member()->where('subdomain', $subdomain)->exists();
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

        foreach ($event->roles as $role) {
            if ($this->isMember($role->subdomain)) {
                return true;
            }
        }

        return false;
    }

    public function getProfileImageUrlAttribute($value)
    {
        if ($this->relationLoaded('profileImage') || $this->profile_image_id) {
            $url = optional($this->profileImage)->url();
            if ($url) {
                return $url;
            }
        }

        if (! $value) {
            return '';
        }

        return storage_asset_url($value);
    }

    public function isAdmin(): bool
    {
        if ($this->hasPermission('settings.manage') || $this->hasPermission('resources.manage')) {
            return true;
        }

        return $this->hasLegacyAdminAccess();
    }

    public function hasLegacyAdminAccess(): bool
    {
        if (! $this->shouldUseLegacyAdminFallback()) {
            return false;
        }

        if (config('app.debug')) {
            return true;
        }

        if (config('app.hosted')) {
            return in_array($this->id, [1, 26]);
        }

        return $this->id == 1;
    }

    protected function shouldUseLegacyAdminFallback(): bool
    {
        static $shouldFallback;

        if ($shouldFallback !== null) {
            return $shouldFallback;
        }

        if (! Schema::hasTable('user_roles')) {
            return $shouldFallback = true;
        }

        return $shouldFallback = DB::table('user_roles')->count() === 0;
    }

    /**
     * Get the user's first name
     *
     * @return string
     */
    public function firstName(): string
    {
        if (!$this->name) {
            return 'there';
        }
        
        $nameParts = explode(' ', trim($this->name));
        return $nameParts[0] ?: 'there';
    }

    public function getResourceScope(string $type): string
    {
        return match ($type) {
            'venue' => $this->venue_scope ?? 'all',
            'curator' => $this->curator_scope ?? 'all',
            'talent' => $this->talent_scope ?? 'all',
            default => 'all',
        };
    }

    public function getResourceScopeIds(string $type): array
    {
        $ids = match ($type) {
            'venue' => $this->venue_ids,
            'curator' => $this->curator_ids,
            'talent' => $this->talent_ids,
            default => [],
        };

        return array_values(array_filter(is_array($ids) ? $ids : [], 'is_numeric'));
    }

    public function setResourceScopeIds(string $type, array $ids): void
    {
        $normalized = array_values(array_unique(array_map('intval', $ids)));

        $column = match ($type) {
            'venue' => 'venue_ids',
            'curator' => 'curator_ids',
            'talent' => 'talent_ids',
            default => null,
        };

        if ($column) {
            $this->setAttribute($column, $normalized);
        }
    }

    public function visibleRolesQuery(string $type): Builder
    {
        $query = Role::query()
            ->type($type)
            ->where('is_deleted', false);

        if ($this->hasSystemRoleSlug('superadmin')) {
            return $query;
        }

        $scope = $this->getResourceScope($type);
        $scopeIds = $this->getResourceScopeIds($type);

        return $query->where(function (Builder $builder) use ($scope, $scopeIds) {
            if ($scope === 'all') {
                $builder->whereRaw('1 = 1');
            } elseif ($scope === 'selected' && ! empty($scopeIds)) {
                $builder->whereIn('roles.id', $scopeIds);
            } else {
                $builder->whereRaw('0 = 1');
            }

            $builder->orWhereHas('users', function (Builder $memberQuery) {
                $memberQuery->where('users.id', $this->id);
            });
        });
    }

    public function canManageResource(Role $role): bool
    {
        if (! in_array($role->type, ['venue', 'talent', 'curator'], true)) {
            return false;
        }

        if ($this->hasSystemRoleSlug('superadmin')) {
            return true;
        }

        if (! $this->hasSystemRoleSlug('admin') || ! $this->hasPermission('resources.manage')) {
            return false;
        }

        return $this->resourceWithinScope($role);
    }

    public function canViewResource(Role $role): bool
    {
        if (! in_array($role->type, ['venue', 'talent', 'curator'], true)) {
            return false;
        }

        if ($this->hasSystemRoleSlug('superadmin')) {
            return true;
        }

        if ($this->hasSystemRoleSlug('admin') && $this->hasPermission('resources.manage')) {
            return $this->resourceWithinScope($role);
        }

        if (! $this->hasSystemRoleSlug('viewer') || ! $this->hasPermission('resources.view')) {
            return false;
        }

        return $this->resourceWithinScope($role);
    }

    public function addResourceToScope(Role $role): void
    {
        if (! in_array($role->type, ['venue', 'talent', 'curator'], true)) {
            return;
        }

        if ($this->getResourceScope($role->type) !== 'selected') {
            return;
        }

        $ids = $this->getResourceScopeIds($role->type);

        if (! in_array((int) $role->id, $ids, true)) {
            $ids[] = (int) $role->id;
            $this->setResourceScopeIds($role->type, $ids);
            $this->save();
        }
    }

    protected function resourceWithinScope(Role $role): bool
    {
        $scope = $this->getResourceScope($role->type);

        if ($scope === 'all') {
            return true;
        }

        return in_array((int) $role->id, $this->getResourceScopeIds($role->type), true);
    }
}
