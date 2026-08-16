<?php

namespace App\Models;

use App\Utils\CounterUtils;
use Illuminate\Database\Eloquent\Model;

/**
 * Daily impression / click rollup for a network promotion, per visitor country.
 *
 * Country only. GeoIpService reads database/geoip/dbip-country-lite.mmdb, which contains
 * no subdivision records, so region or province reporting is not possible without shipping
 * a city-level database. Advertiser-facing region data comes from the host schedule's own
 * declared location instead, and the two are reported as separate, clearly labelled
 * dimensions - never conflated.
 */
class PromotionLocationsDaily extends Model
{
    public $timestamps = false;

    protected $table = 'promotion_locations_daily';

    protected $fillable = [
        'boost_campaign_id',
        'date',
        'country_code',
        'impressions',
        'clicks',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    /**
     * @param  string  $metric  'impression' or 'click' - resolved through a whitelist so the
     *                          column name can never come from caller-supplied input.
     */
    public static function record(int $campaignId, ?string $countryCode, string $metric = 'impression'): void
    {
        // No resolvable country (no GeoIP database, or a private address) means there is
        // nothing to attribute; the campaign-level rollup still counts the event.
        if (! $countryCode) {
            return;
        }

        $column = match ($metric) {
            'click' => 'clicks',
            default => 'impressions',
        };

        $other = $column === 'clicks' ? 'impressions' : 'clicks';

        CounterUtils::statement(
            "INSERT INTO promotion_locations_daily
                (boost_campaign_id, date, country_code, {$column}, {$other})
             VALUES (?, ?, ?, 1, 0)
             ON DUPLICATE KEY UPDATE {$column} = {$column} + 1",
            [$campaignId, now()->toDateString(), strtoupper($countryCode)]
        );
    }

    public function scopeForCampaign($query, int $campaignId)
    {
        return $query->where('boost_campaign_id', $campaignId);
    }
}
