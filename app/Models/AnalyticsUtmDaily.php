<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AnalyticsUtmDaily extends Model
{
    public $timestamps = false;

    protected $table = 'analytics_utm_daily';

    protected $fillable = [
        'role_id',
        'date',
        'param_type',
        'param_value',
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
     * Increment view count for a role/date/param_type/param_value combination using upsert
     */
    public static function incrementView(int $roleId, string $paramType, string $paramValue): void
    {
        $date = now()->toDateString();

        DB::statement(
            'INSERT INTO analytics_utm_daily (role_id, date, param_type, param_value, views)
             VALUES (?, ?, ?, ?, 1)
             ON DUPLICATE KEY UPDATE views = views + 1',
            [$roleId, $date, $paramType, $paramValue]
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

    /**
     * Scope to filter by param type
     */
    public function scopeByParamType($query, string $paramType)
    {
        return $query->where('param_type', $paramType);
    }
}
