<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One month's fee for a boost partner: a line per campaign that spent that month, adjustment
 * lines for spend Meta corrected after an earlier month was billed, and the charge to the
 * partner's card.
 *
 * status: pending (not yet charged), paid, failed (a charge was declined and will be retried),
 * action_required (the card needs the partner to authenticate: "Pay now"), carried (nothing to
 * charge this month, or less than Stripe's minimum; the amount moves to the next statement).
 */
class BoostStatement extends Model
{
    protected $hidden = [
        'stripe_payment_intent_id',
    ];

    protected $fillable = [
        'boost_partner_id',
        'period',
        'currency',
        'total_spend',
        'total_fee',
        'total_adjustments',
        'carried_in',
        'charged_amount',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'total_spend' => 'decimal:2',
            'total_fee' => 'decimal:2',
            'total_adjustments' => 'decimal:2',
            'carried_in' => 'decimal:2',
            'charged_amount' => 'decimal:2',
            'charged_at' => 'datetime',
            'last_attempt_at' => 'datetime',
            'mailed_at' => 'datetime',
        ];
    }

    public function partner()
    {
        return $this->belongsTo(BoostPartner::class, 'boost_partner_id');
    }

    public function lines()
    {
        return $this->hasMany(BoostStatementLine::class);
    }

    public function amountDue(): float
    {
        return round((float) $this->total_fee + (float) $this->total_adjustments + (float) $this->carried_in, 2);
    }

    public function isSettled(): bool
    {
        return in_array($this->status, ['paid', 'carried'], true);
    }
}
