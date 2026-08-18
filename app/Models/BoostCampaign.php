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
        'channel',
        'meta_campaign_id',
        'meta_adset_id',
        'name',
        'objective',
        'status',
        'moderation_status',
        'moderation_notes',
        'moderated_by',
        'moderated_at',
        'pricing_model',
        'unit_rate_micros',
        'budget_micros',
        'network_targeting',
        'exhausted_at',
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
        'network_targeting' => 'array',
        'daily_analytics' => 'array',
        'moderated_at' => 'datetime',
        'exhausted_at' => 'datetime',
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

    /**
     * A promotion served on this platform rather than bought through Meta.
     *
     * Anything that talks to the Meta API, or reads a meta_* column as meaningful, must
     * check this first - see the channel guards across the boost jobs and commands.
     */
    public function isNetwork(): bool
    {
        return $this->channel === 'network';
    }

    public function scopeNetwork($query)
    {
        return $query->where('channel', 'network');
    }

    public function scopeMeta($query)
    {
        return $query->where('channel', 'meta');
    }

    public function isAwaitingReview(): bool
    {
        return $this->moderation_status === 'pending';
    }

    /**
     * Budget still available to deliver against, in micros.
     */
    public function remainingMicros(): int
    {
        return max(0, (int) $this->budget_micros - (int) $this->spent_micros);
    }

    /**
     * Delivered spend as a currency amount, for the columns and refund paths that predate
     * micros (user_budget / actual_spend are decimals).
     */
    public function spentAmount(): float
    {
        return round(((int) $this->spent_micros) / 1_000_000, 2);
    }

    /**
     * What one impression costs. CPC campaigns are free to impress - only the click bills -
     * which is also why they skip the atomic debit on the render path entirely.
     */
    public function impressionCostMicros(): int
    {
        return $this->pricing_model === 'cpm' ? intdiv((int) $this->unit_rate_micros, 1000) : 0;
    }

    public function clickCostMicros(): int
    {
        return $this->pricing_model === 'cpc' ? (int) $this->unit_rate_micros : 0;
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

    /**
     * Campaigns that still hold the advertiser's money, for the deletion paths.
     *
     * 'completed' is in here and NOT in the concurrency cap's list, which is the distinction
     * that matters: completeFinishedCampaigns() flips a campaign to completed immediately but
     * only dispatches its refund 24 hours later, and boost_campaigns.event_id is
     * cascadeOnDelete (as are boost_billing_records). A campaign that ended on schedule with
     * most of its budget unspent - the normal case, since completion fires on scheduled_end and
     * event-end, not just exhaustion - was therefore one event deletion away from having the
     * money and the entire ledger trail erased.
     *
     * The billing_status filter keeps an already-settled campaign out, so deleting does not
     * rewrite the status of something that was correctly refunded days ago.
     */
    public function scopeUnsettled($query)
    {
        return $query
            ->whereIn('status', ['active', 'paused', 'pending_payment', 'pending_review', 'completed'])
            ->where(function ($q) {
                $q->whereNotIn('billing_status', ['refunded', 'partially_refunded'])
                    ->orWhereNull('billing_status');
            });
    }

    /**
     * Network campaigns an operator can still act on in the review queue.
     *
     * Scoped on `status` as well as `moderation_status` because cancelling writes only
     * `status` - it never clears `moderation_status`. Without this a campaign the advertiser
     * cancelled (and was refunded for) stays in the queue and the "needs attention" badge
     * forever, and approving it would set `status` back to 'active' and re-serve a campaign
     * whose money has already gone back.
     */
    public function scopeAwaitingReview($query)
    {
        return $query->network()
            ->where('moderation_status', 'pending')
            ->whereIn('status', ['pending_review', 'pending_payment', 'active', 'paused']);
    }

    /**
     * 'pending_review' is a network-only status: a campaign that has been PAID FOR and is
     * waiting on an operator to approve it. Leaving it out meant an advertiser could neither
     * cancel their own money back nor have it refunded when their schedule was deleted, and
     * every advertiser's first campaigns land in exactly that state.
     */
    public function canBeCancelled()
    {
        return in_array($this->status, ['active', 'paused', 'pending_payment', 'pending_review']);
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
        return static::currencySymbol($this->currency_code);
    }

    /**
     * The symbol for a currency code, for screens that price something before a campaign
     * exists - the purchase form quotes rates, budgets and credit with no row to read from.
     *
     * Delegates to MoneyUtils, which is the one symbol table in the app. This used to be a
     * private match on EUR/GBP that rendered every other currency - ZAR included - as a
     * dollar sign, so PROMOTIONS_CURRENCY could only really be set to three values.
     */
    public static function currencySymbol(?string $code): string
    {
        return \App\Utils\MoneyUtils::symbol($code);
    }

    public function getBudgetUtilization()
    {
        if (! $this->user_budget || $this->user_budget == 0) {
            return 0;
        }

        return min(100, round((($this->actual_spend ?? 0) / $this->user_budget) * 100, 1));
    }
}
