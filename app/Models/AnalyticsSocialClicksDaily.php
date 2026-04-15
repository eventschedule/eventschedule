<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AnalyticsSocialClicksDaily extends Model
{
    public $timestamps = false;

    protected $table = 'analytics_social_clicks_daily';

    protected $fillable = [
        'role_id',
        'date',
        'platform',
        'clicks',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Increment click count for a role/date/platform combination using upsert
     */
    public static function incrementClick(int $roleId, string $platform): void
    {
        $date = now()->toDateString();

        DB::statement(
            'INSERT INTO analytics_social_clicks_daily (role_id, date, platform, clicks)
             VALUES (?, ?, ?, 1)
             ON DUPLICATE KEY UPDATE clicks = clicks + 1',
            [$roleId, $date, $platform]
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
