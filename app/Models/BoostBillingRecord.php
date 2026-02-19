<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BoostBillingRecord extends Model
{
    protected $fillable = [
        'boost_campaign_id',
        'type',
        'amount',
        'meta_spend',
        'markup_amount',
        'stripe_payment_intent_id',
        'stripe_refund_id',
        'status',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'meta_spend' => 'decimal:2',
        'markup_amount' => 'decimal:2',
    ];

    public function campaign()
    {
        return $this->belongsTo(BoostCampaign::class, 'boost_campaign_id');
    }

    public function isCharge()
    {
        return $this->type === 'charge';
    }

    public function isRefund()
    {
        return $this->type === 'refund';
    }
}
