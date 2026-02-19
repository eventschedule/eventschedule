<?php

namespace App\Models;

use App\Utils\UrlUtils;
use Illuminate\Database\Eloquent\Model;

class BoostAd extends Model
{
    protected $fillable = [
        'boost_campaign_id',
        'meta_ad_id',
        'meta_creative_id',
        'headline',
        'primary_text',
        'description',
        'call_to_action',
        'image_url',
        'image_hash',
        'destination_url',
        'variant',
        'is_winner',
        'status',
        'meta_status',
        'meta_rejection_reason',
        'impressions',
        'reach',
        'clicks',
        'spend',
        'ctr',
    ];

    protected $casts = [
        'is_winner' => 'boolean',
        'spend' => 'decimal:2',
        'ctr' => 'decimal:4',
    ];

    public function campaign()
    {
        return $this->belongsTo(BoostCampaign::class, 'boost_campaign_id');
    }

    public function hashedId()
    {
        return UrlUtils::encodeId($this->id);
    }

    public function isRejected()
    {
        return $this->status === 'rejected' || $this->meta_status === 'DISAPPROVED';
    }
}
