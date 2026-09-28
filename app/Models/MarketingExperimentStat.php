<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Daily per-variant counters for a marketing-site A/B test. Written by
 * App\Utils\HeroExperiment::recordEvent() from the homepage's browser beacons; signups are
 * not stored here, they are read from users.hero_variant.
 */
class MarketingExperimentStat extends Model
{
    public $timestamps = false;

    protected $table = 'marketing_experiment_stats';

    protected $fillable = [
        'experiment',
        'variant',
        'date',
        'visitors',
        'clicks',
    ];

    protected $casts = [
        'date' => 'date',
    ];
}
