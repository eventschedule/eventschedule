<?php

namespace App\Models;

use App\Utils\CounterUtils;
use Illuminate\Database\Eloquent\Model;

class AnalyticsMissingDaily extends Model
{
    public $timestamps = false;

    protected $table = 'analytics_missing_daily';

    protected $fillable = [
        'role_id',
        'date',
        'slug',
        'views',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    /** Distinct slugs one visitor can open a row for on one schedule in a day. */
    private const MAX_NEW_SLUGS_PER_IP_PER_DAY = 5;

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Count one hit on an address that matched nothing.
     *
     * The slug here is attacker-controlled - it is whatever the visitor typed - so this is bounded
     * three ways rather than trusted. Bots and suspicious requests are filtered by the caller, the
     * shape is restricted to a real slug below, and a single visitor may only ever CREATE a handful
     * of distinct rows per schedule per day. Incrementing an existing row is deliberately NOT
     * capped: a genuinely broken link gets hit many times and must outrank one-shot scanner noise,
     * which is the whole point of the panel.
     */
    public static function incrementView(int $roleId, string $slug, ?string $ipHash = null): void
    {
        // A real slug, not a probe. Anything else is scanner traffic ('/wp-login.php',
        // '../../etc/passwd', a 4KB query string) and would only ever crowd out real findings.
        if (! preg_match('/^[a-z0-9][a-z0-9-]{0,190}$/', $slug)) {
            return;
        }

        $date = now()->toDateString();

        $exists = self::where('role_id', $roleId)
            ->where('date', $date)
            ->where('slug', $slug)
            ->exists();

        if (! $exists && $ipHash !== null) {
            $budget = PageView::incrementDailyCounter("analytics_missing_new:{$roleId}:{$ipHash}");

            if ($budget > self::MAX_NEW_SLUGS_PER_IP_PER_DAY) {
                return;
            }
        }

        CounterUtils::statement(
            'INSERT INTO analytics_missing_daily (role_id, date, slug, views)
             VALUES (?, ?, ?, 1)
             ON DUPLICATE KEY UPDATE views = views + 1',
            [$roleId, $date, $slug]
        );
    }
}
