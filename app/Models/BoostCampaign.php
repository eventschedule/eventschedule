<?php

namespace App\Models;

use App\Utils\UrlUtils;
use Illuminate\Database\Eloquent\Model;

class BoostCampaign extends Model
{
    protected $hidden = [
        'stripe_payment_intent_id',
        'daily_analytics',
    ];

    protected $fillable = [
        'event_id',
        'role_id',
        'user_id',
        'meta_campaign_id',
        'meta_adset_id',
        'name',
        'objective',
        'status',
        'meta_status',
        'meta_rejection_reason',
        'daily_budget',
        'lifetime_budget',
        'budget_type',
        'currency_code',
        'scheduled_start',
        'scheduled_end',
        'targeting',
        'placements',
        'user_budget',
        'total_charged',
        'actual_spend',
        'stripe_payment_intent_id',
        'billing_status',
        'impressions',
        'reach',
        'clicks',
        'ctr',
        'cpc',
        'cpm',
        'conversions',
        'daily_analytics',
        'analytics_synced_at',
        'meta_synced_at',
        'budget_alert_sent_at',
    ];

    protected $casts = [
        'targeting' => 'array',
        'placements' => 'array',
        'daily_analytics' => 'array',
        'daily_budget' => 'decimal:2',
        'lifetime_budget' => 'decimal:2',
        'user_budget' => 'decimal:2',
        'markup_rate' => 'decimal:4',
        'total_charged' => 'decimal:2',
        'actual_spend' => 'decimal:2',
        'ctr' => 'decimal:4',
        'cpc' => 'decimal:2',
        'cpm' => 'decimal:2',
        'scheduled_start' => 'datetime',
        'scheduled_end' => 'datetime',
        'analytics_synced_at' => 'datetime',
        'meta_synced_at' => 'datetime',
        'budget_alert_sent_at' => 'datetime',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function ads()
    {
        return $this->hasMany(BoostAd::class);
    }

    public function billingRecords()
    {
        return $this->hasMany(BoostBillingRecord::class);
    }

    public function hashedId()
    {
        return UrlUtils::encodeId($this->id);
    }

    public function isActive()
    {
        return $this->status === 'active';
    }

    public function isPaused()
    {
        return $this->status === 'paused';
    }

    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    public function isFailed()
    {
        return in_array($this->status, ['failed', 'rejected']);
    }

    public function canBePaused()
    {
        return $this->status === 'active';
    }

    public function canBeResumed()
    {
        return $this->status === 'paused';
    }

    public function canBeCancelled()
    {
        return in_array($this->status, ['active', 'paused', 'pending_payment']);
    }

    public function getTotalCost()
    {
        return round($this->user_budget * (1 + $this->markup_rate), 2);
    }

    public function getMarkupAmount()
    {
        return round($this->user_budget * $this->markup_rate, 2);
    }

    public function getCurrencySymbol(): string
    {
        return match ($this->currency_code) {
            'EUR' => "\u{20AC}",
            'GBP' => "\u{00A3}",
            default => '$',
        };
    }

    public function getBudgetUtilization()
    {
        if (! $this->user_budget || $this->user_budget == 0) {
            return 0;
        }

        return min(100, round((($this->actual_spend ?? 0) / $this->user_budget) * 100, 1));
    }
}
