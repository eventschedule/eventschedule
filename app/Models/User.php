<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Notifications\Notifiable;

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
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            $model->email = strtolower($model->email);
        });

        static::updating(function ($user) {
            if ($user->isDirty('email')) {
                $user->email_verified_at = null;
                $user->sendEmailVerificationNotification();
            }
        });
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
        ];
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class)
                    ->withPivot('level');
    }

    public function member()
    {
        return $this->belongsToMany(Role::class)
                    ->withPivot('level')
                    ->wherePivotIn('level', ['owner', 'admin']);
    }

    public function following()
    {
        return $this->belongsToMany(Role::class)
                    ->withPivot('level')
                    ->wherePivot('level', 'follower');
    }

    public function venues()
    {
        return $this->member()->type('venue');
    }

    public function talent()
    {
        return $this->member()->type('talent');
    }

    public function vendors()
    {
        return $this->member()->type('vendor');
    }

    public function followingVenues()
    {
        return $this->following()->type('venue');
    }

    public function followingTalent()
    {
        return $this->following()->type('talent');
    }

    public function followingVendors()
    {
        return $this->following()->type('vendor');
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

    public function canEditEvent($event)
    {
        return $this->isMember($event->venue->subdomain) || $this->isMember($event->role->subdomain);
    }

    public function getProfileImageUrlAttribute($value)
    {
        if (! $value) {
            return '';
        }

        return config('filesystems.default') == 'local' ? url('/storage/' . $value) : $value;
    }
}
