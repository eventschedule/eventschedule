<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One campaign's spend and fee for the month a line is billed for. `period` is the month the
 * spend happened in: an adjustment line on the November statement carries period 2026-10.
 */
class BoostStatementLine extends Model
{
    protected $fillable = [
        'boost_statement_id',
        'boost_campaign_id',
        'period',
        'spend',
        'fee',
        'adjustment',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'spend' => 'decimal:2',
            'fee' => 'decimal:2',
            'adjustment' => 'decimal:2',
        ];
    }

    public function statement()
    {
        return $this->belongsTo(BoostStatement::class, 'boost_statement_id');
    }

    public function campaign()
    {
        return $this->belongsTo(BoostCampaign::class, 'boost_campaign_id');
    }
}
