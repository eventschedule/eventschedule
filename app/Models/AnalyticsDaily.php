<?php

namespace App\Models;

use App\Utils\CounterUtils;
use Illuminate\Database\Eloquent\Model;

class AnalyticsDaily extends Model
{
    public $timestamps = false;

    protected $table = 'analytics_daily';

    protected $fillable = [
        'role_id',
        'date',
        'desktop_views',
        'mobile_views',
        'tablet_views',
        'unknown_views',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Increment view count for a role/date combination using upsert.
     *
     * Called once per public page view from PageView::recordView(). The write goes through
     * CounterUtils so a deadlock on the hot (role_id, date) row retries and then fails
     * quietly - it used to propagate and 500 the guest page it was counting.
     */
    public static function incrementView(int $roleId, string $deviceType): void
    {
        $column = match ($deviceType) {
            'desktop' => 'desktop_views',
            'mobile' => 'mobile_views',
            'tablet' => 'tablet_views',
            default => 'unknown_views',
        };

        $date = now()->toDateString();

        CounterUtils::statement(
            "INSERT INTO analytics_daily (role_id, date, {$column})
             VALUES (?, ?, 1)
             ON DUPLICATE KEY UPDATE {$column} = {$column} + 1",
            [$roleId, $date]
        );
    }

    /**
     * Scope to filter by role
     */
    public function scopeByRole($query, int $roleId)
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
