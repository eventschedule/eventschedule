<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AnalyticsReferrersDaily extends Model
{
    public $timestamps = false;

    protected $table = 'analytics_referrers_daily';

    protected $fillable = [
        'role_id',
        'date',
        'source',
        'domain',
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
     * Increment view count for a role/date/source/domain combination using upsert
     */
    public static function incrementView(int $roleId, ?string $referrer): void
    {
        [$source, $domain] = self::categorizeReferrer($referrer);
        $date = now()->toDateString();

        // Use empty string for null domain to make unique constraint work
        $domainKey = $domain ?? '';

        DB::statement(
            'INSERT INTO analytics_referrers_daily (role_id, date, source, domain, views)
             VALUES (?, ?, ?, ?, 1)
             ON DUPLICATE KEY UPDATE views = views + 1',
            [$roleId, $date, $source, $domainKey]
        );
    }

    /**
     * Categorize a referrer URL into source type and domain
     */
    private static function categorizeReferrer(?string $referrer): array
    {
        if (! $referrer) {
            return ['direct', null];
        }

        $host = parse_url($referrer, PHP_URL_HOST);

        if (! $host) {
            return ['direct', null];
        }

        // Remove www. prefix for cleaner domain names
        $host = preg_replace('/^www\./i', '', $host);

        // Search engines
        if (preg_match('/google\.|bing\.|yahoo\.|duckduckgo\.|baidu\.|yandex\./i', $host)) {
            return ['search', $host];
        }

        // Social media
        if (preg_match('/facebook\.|fb\.|twitter\.|x\.com|instagram\.|linkedin\.|tiktok\.|pinterest\.|reddit\.|t\.co/i', $host)) {
            return ['social', $host];
        }

        // Email clients (common webmail)
        if (preg_match('/mail\.|outlook\.|gmail\.|mailchimp\.|campaign-archive\./i', $host)) {
            return ['email', $host];
        }

        return ['other', $host];
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
     * Scope to filter by source type
     */
    public function scopeBySource($query, string $source)
    {
        return $query->where('source', $source);
    }
}
