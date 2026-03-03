<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Referral extends Model
{
    protected $fillable = [
        'referrer_user_id',
        'referred_user_id',
        'referred_role_id',
        'plan_type',
        'status',
        'subscribed_at',
        'qualified_at',
        'credited_at',
        'credited_role_id',
    ];

    protected $casts = [
        'subscribed_at' => 'datetime',
        'qualified_at' => 'datetime',
        'credited_at' => 'datetime',
    ];

    public function referrer()
    {
        return $this->belongsTo(User::class, 'referrer_user_id');
    }

    public function referredUser()
    {
        return $this->belongsTo(User::class, 'referred_user_id');
    }

    public function referredRole()
    {
        return $this->belongsTo(Role::class, 'referred_role_id');
    }

    public function creditedRole()
    {
        return $this->belongsTo(Role::class, 'credited_role_id');
    }
}
