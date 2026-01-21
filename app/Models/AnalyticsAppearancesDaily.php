<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AnalyticsAppearancesDaily extends Model
{
    public $timestamps = false;

    protected $table = 'analytics_appearances_daily';

    protected $fillable = [
        'role_id',
        'schedule_role_id',
        'date',
        'desktop_views',
        'mobile_views',
        'tablet_views',
        'unknown_views',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    /**
     * The talent/venue being tracked
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * The schedule where the view occurred
     */
    public function scheduleRole()
    {
        return $this->belongsTo(Role::class, 'schedule_role_id');
    }

    /**
     * Increment view count for a role/schedule/date combination using upsert
     */
    public static function incrementView(int $roleId, int $scheduleRoleId, string $deviceType): void
    {
        $column = match ($deviceType) {
            'desktop' => 'desktop_views',
            'mobile' => 'mobile_views',
            'tablet' => 'tablet_views',
            default => 'unknown_views',
        };

        $date = now()->toDateString();

        DB::statement(
            "INSERT INTO analytics_appearances_daily (role_id, schedule_role_id, date, {$column})
             VALUES (?, ?, ?, 1)
             ON DUPLICATE KEY UPDATE {$column} = {$column} + 1",
            [$roleId, $scheduleRoleId, $date]
        );
    }

    /**
     * Scope to filter by the schedule where views occurred
     */
    public function scopeForSchedule($query, int $scheduleRoleId)
    {
        return $query->where('schedule_role_id', $scheduleRoleId);
    }

    /**
     * Scope to filter by the talent/venue being tracked
     */
    public function scopeForRole($query, int $roleId)
    {
        return $query->where('role_id', $roleId);
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

    /**
     * Get total views (sum of all device types)
     */
    public function getTotalViewsAttribute(): int
    {
        return $this->desktop_views + $this->mobile_views + $this->tablet_views + $this->unknown_views;
    }
}
