<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A partner boost's spend on one day, in the ad account's timezone, as Meta's daily insights
 * report it. The monthly statement is summed from these rows.
 */
class BoostSpendDay extends Model
{
    protected $fillable = [
        'boost_campaign_id',
        'day',
        'spend',
        'currency',
    ];

    protected function casts(): array
    {
        return [
            'day' => 'date',
            'spend' => 'decimal:2',
        ];
    }

    public function campaign()
    {
        return $this->belongsTo(BoostCampaign::class, 'boost_campaign_id');
    }
}
