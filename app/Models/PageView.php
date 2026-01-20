<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class PageView extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'role_id',
        'event_id',
        'viewed_at',
        'ip_hash',
        'user_agent',
        'device_type',
    ];

    protected $casts = [
        'viewed_at' => 'datetime',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function scopeByRole($query, $roleId)
    {
        return $query->where('role_id', $roleId);
    }

    public function scopeByEvent($query, $eventId)
    {
        return $query->where('event_id', $eventId);
    }

    public function scopeInDateRange($query, $start, $end)
    {
        return $query->whereBetween('viewed_at', [$start, $end]);
    }

    public function scopeForRoles($query, $roleIds)
    {
        return $query->whereIn('role_id', $roleIds);
    }

    /**
     * Detect device type from user agent string
     */
    public static function detectDeviceType(?string $userAgent): string
    {
        if (!$userAgent) {
            return 'unknown';
        }

        $userAgent = strtolower($userAgent);

        // Check for tablets first (they often include mobile keywords)
        if (preg_match('/tablet|ipad|playbook|silk/i', $userAgent)) {
            return 'tablet';
        }

        // Check for mobile devices
        if (preg_match('/mobile|iphone|ipod|android.*mobile|windows phone|blackberry|opera mini|opera mobi/i', $userAgent)) {
            return 'mobile';
        }

        // Check for Android without mobile (likely tablet)
        if (preg_match('/android/i', $userAgent) && !preg_match('/mobile/i', $userAgent)) {
            return 'tablet';
        }

        // Default to desktop for everything else
        return 'desktop';
    }

    /**
     * Record a page view
     */
    public static function recordView(Role $role, ?Event $event, Request $request): self
    {
        $userAgent = $request->userAgent();

        return self::create([
            'role_id' => $role->id,
            'event_id' => $event?->id,
            'viewed_at' => now(),
            'ip_hash' => $request->ip() ? hash('sha256', $request->ip() . config('app.key')) : null,
            'user_agent' => $userAgent ? substr($userAgent, 0, 500) : null,
            'device_type' => self::detectDeviceType($userAgent),
        ]);
    }
}
