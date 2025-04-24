<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Notifications\Notifiable;
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
        'stripe_account_id',
        'api_key',
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
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            $model->email = strtolower($model->email);
        });

        static::updating(function ($user) {
            if ($user->isDirty('email') && (config('app.hosted'))) {
                $user->email_verified_at = null;
                $user->sendEmailVerificationNotification();
            }
        });
    }
    
    public function sendEmailVerificationNotification()
    {
        $this->notify(new CustomVerifyEmail('user'));
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

    public function venues()
    {
        return $this->member()->type('venue');
    }

    public function schedules()
    {
        return $this->member()->type('talent');
    }

    public function curators()
    {
        return $this->member()->type('curator');
    }

    public function editableCurators()
    {
        return $this->roles()
                    ->whereIn('type', ['curator'])
                    ->get()
                    ->filter(function ($role) {
                        return $role->is_open 
                            || $role->pivot->level == 'admin' 
                            || $role->pivot->level == 'owner';
                    });
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
        if ($this->id == $event->user_id) {
            return true;
        }

        if ($event->venue && $this->isMember($event->venue->subdomain)) {
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
        if (! $value) {
            return '';
        }

        if (config('app.hosted') && config('filesystems.default') == 'do_spaces') {
            return 'https://eventschedule.nyc3.cdn.digitaloceanspaces.com/' . $value;
        } else if (config('filesystems.default') == 'local') {
            return url('/storage/' . $value);
        } else {
            return $value;
        }
    }

    public function isAdmin(): bool
    {
        return $this->id == 1;
    }
}
