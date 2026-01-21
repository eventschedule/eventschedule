<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AnalyticsEventsDaily extends Model
{
    public $timestamps = false;

    protected $table = 'analytics_events_daily';

    protected $fillable = [
        'event_id',
        'date',
        'desktop_views',
        'mobile_views',
        'tablet_views',
        'unknown_views',
        'sales_count',
        'revenue',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Increment view count for an event/date combination using upsert
     */
    public static function incrementView(int $eventId, string $deviceType): void
    {
        $column = match ($deviceType) {
            'desktop' => 'desktop_views',
            'mobile' => 'mobile_views',
            'tablet' => 'tablet_views',
            default => 'unknown_views',
        };

        $date = now()->toDateString();

        DB::statement(
            "INSERT INTO analytics_events_daily (event_id, date, {$column})
             VALUES (?, ?, 1)
             ON DUPLICATE KEY UPDATE {$column} = {$column} + 1",
            [$eventId, $date]
        );
    }

    /**
     * Increment sale count and revenue for an event/date combination using upsert
     */
    public static function incrementSale(int $eventId, float $amount): void
    {
        $date = now()->toDateString();

        DB::statement(
            'INSERT INTO analytics_events_daily (event_id, date, sales_count, revenue)
             VALUES (?, ?, 1, ?)
             ON DUPLICATE KEY UPDATE sales_count = sales_count + 1, revenue = revenue + ?',
            [$eventId, $date, $amount, $amount]
        );
    }

    /**
     * Scope to filter by event
     */
    public function scopeByEvent($query, int $eventId)
    {
        return $query->where('event_id', $eventId);
    }

    /**
     * Scope to filter by multiple events
     */
    public function scopeForEvents($query, $eventIds)
    {
        return $query->whereIn('event_id', $eventIds);
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
