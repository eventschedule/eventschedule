<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsletterAbTest extends Model
{
    protected $fillable = [
        'role_id',
        'name',
        'test_field',
        'sample_percentage',
        'winner_criteria',
        'winner_wait_hours',
        'winner_selected_at',
        'winner_variant',
        'status',
    ];

    protected $casts = [
        'winner_selected_at' => 'datetime',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function newsletters()
    {
        return $this->hasMany(Newsletter::class, 'ab_test_id');
    }

    public function variantA()
    {
        return $this->hasOne(Newsletter::class, 'ab_test_id')->where('ab_variant', 'A');
    }

    public function variantB()
    {
        return $this->hasOne(Newsletter::class, 'ab_test_id')->where('ab_variant', 'B');
    }
}
