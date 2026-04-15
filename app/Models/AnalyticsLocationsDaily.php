<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AnalyticsLocationsDaily extends Model
{
    public $timestamps = false;

    protected $table = 'analytics_locations_daily';

    protected $fillable = [
        'role_id',
        'date',
        'country_code',
        'views',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Increment view count for a role/date/country combination using upsert
     */
    public static function incrementView(int $roleId, string $countryCode): void
    {
        $date = now()->toDateString();

        DB::statement(
            'INSERT INTO analytics_locations_daily (role_id, date, country_code, views)
             VALUES (?, ?, ?, 1)
             ON DUPLICATE KEY UPDATE views = views + 1',
            [$roleId, $date, $countryCode]
        );
    }

    /**
     * Scope to filter by multiple roles
     */
    public function scopeForRoles($query, $roleIds)
    {
        return $query->whereIn('role_id', $roleIds);
    }

    /**
     * Scope to filter by date range
     */
    public function scopeInDateRange($query, $start, $end)
    {
        return $query->whereBetween('date', [
            $start->toDateString(),
            $end->toDateString(),
        ]);
    }
}
