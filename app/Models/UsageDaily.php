<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class UsageDaily extends Model
{
    public $timestamps = false;

    protected $table = 'usage_daily';

    protected $fillable = [
        'date',
        'operation',
        'role_id',
        'count',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    /**
     * Increment count for a date/operation/role combination using upsert
     */
    public static function record(string $operation, int $roleId = 0): void
    {
        $date = now()->toDateString();

        DB::statement(
            'INSERT INTO usage_daily (date, operation, role_id, `count`)
             VALUES (?, ?, ?, 1)
             ON DUPLICATE KEY UPDATE `count` = `count` + 1',
            [$date, $operation, $roleId]
        );
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
     * Scope to filter by operation
     */
    public function scopeForOperation($query, string $operation)
    {
        return $query->where('operation', $operation);
    }

    /**
     * Scope to filter by role
     */
    public function scopeForRole($query, int $roleId)
    {
        return $query->where('role_id', $roleId);
    }
}
