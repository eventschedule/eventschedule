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
    public static function incrementView(int $roleId, ?string $referrer, ?string $customDomain = null, ?string $sourceOverride = null): void
    {
        if ($sourceOverride) {
            $source = $sourceOverride;
            $domain = null;
        } else {
            [$source, $domain] = self::categorizeReferrer($referrer, $customDomain);
        }
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
    private static function categorizeReferrer(?string $referrer, ?string $customDomain = null): array
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

        // Check if referrer is "self" (should be treated as direct)
        if (self::isSelfReferrer($host, $customDomain)) {
            return ['direct', null];
        }

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
     * Check if the referrer host is a "self" domain (the app's own domain or the role's custom domain)
     */
    private static function isSelfReferrer(string $host, ?string $customDomain): bool
    {
        // Check custom domain
        if ($customDomain) {
            $customHost = parse_url($customDomain, PHP_URL_HOST) ?: $customDomain;
            $customHost = preg_replace('/^www\./i', '', $customHost);
            if (strcasecmp($host, $customHost) === 0) {
                return true;
            }
        }

        // Check app's own domain (from APP_URL)
        $appUrl = config('app.url');
        if ($appUrl) {
            $appHost = parse_url($appUrl, PHP_URL_HOST);
            if ($appHost) {
                $appHost = preg_replace('/^www\./i', '', $appHost);
                // Match exact domain or subdomains
                if (strcasecmp($host, $appHost) === 0 || str_ends_with(strtolower($host), '.'.strtolower($appHost))) {
                    return true;
                }
            }
        }

        // Hardcoded check for eventschedule.com (handles hosted mode)
        if (strcasecmp($host, 'eventschedule.com') === 0 || str_ends_with(strtolower($host), '.eventschedule.com')) {
            return true;
        }

        return false;
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
