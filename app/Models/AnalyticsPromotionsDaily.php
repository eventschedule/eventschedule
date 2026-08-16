<?php

namespace App\Models;

use App\Utils\CounterUtils;
use Illuminate\Database\Eloquent\Model;

/**
 * Daily impression / click rollup for a network promotion, per host schedule.
 *
 * Follows the analytics_*_daily convention: no raw event log, one synchronous
 * INSERT ... ON DUPLICATE KEY UPDATE per event, MySQL only. Writes go through
 * CounterUtils so a counter can never break a guest page render.
 */
class AnalyticsPromotionsDaily extends Model
{
    public $timestamps = false;

    protected $table = 'analytics_promotions_daily';

    protected $fillable = [
        'boost_campaign_id',
        'host_role_id',
        'date',
        'impressions',
        'unique_visitors',
        'clicks',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    /**
     * Record one impression, optionally counting the viewer as a distinct daily visitor.
     *
     * The unique_visitors delta is bound twice rather than referenced via VALUES() in the
     * update clause - VALUES() is deprecated as of MySQL 8.0.20 and the alias form needs
     * 8.0.19+, so neither is portable enough for selfhosters.
     */
    public static function recordImpression(int $campaignId, int $hostRoleId, bool $isNewVisitor = false): void
    {
        $delta = $isNewVisitor ? 1 : 0;

        CounterUtils::statement(
            'INSERT INTO analytics_promotions_daily
                (boost_campaign_id, host_role_id, date, impressions, unique_visitors, clicks)
             VALUES (?, ?, ?, 1, ?, 0)
             ON DUPLICATE KEY UPDATE impressions = impressions + 1, unique_visitors = unique_visitors + ?',
            [$campaignId, $hostRoleId, now()->toDateString(), $delta, $delta]
        );
    }

    public static function recordClick(int $campaignId, int $hostRoleId): void
    {
        CounterUtils::statement(
            'INSERT INTO analytics_promotions_daily
                (boost_campaign_id, host_role_id, date, impressions, unique_visitors, clicks)
             VALUES (?, ?, ?, 0, 0, 1)
             ON DUPLICATE KEY UPDATE clicks = clicks + 1',
            [$campaignId, $hostRoleId, now()->toDateString()]
        );
    }

    public function scopeForCampaign($query, int $campaignId)
    {
        return $query->where('boost_campaign_id', $campaignId);
    }
}
