<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Daily count of visitors sent from this site out to a federated instance.
 *
 * This is the evidence behind federation's core promise. Without it an operator
 * shares their customers' events, waits for approval, and gets back no sign that
 * anyone ever arrived.
 */
class FederationClicksDaily extends Model
{
    protected $table = 'federation_clicks_daily';

    protected $fillable = ['federated_instance_id', 'date', 'clicks'];

    protected $casts = [
        'date' => 'date',
        'clicks' => 'integer',
    ];

    public function instance()
    {
        return $this->belongsTo(FederatedInstance::class, 'federated_instance_id');
    }

    /**
     * Upsert-increment, matching AnalyticsSocialClicksDaily::incrementClick().
     */
    public static function incrementClick(int $instanceId): void
    {
        DB::statement(
            'INSERT INTO federation_clicks_daily (federated_instance_id, date, clicks, created_at, updated_at)
             VALUES (?, ?, 1, NOW(), NOW())
             ON DUPLICATE KEY UPDATE clicks = clicks + 1, updated_at = NOW()',
            [$instanceId, now()->toDateString()]
        );
    }

    public static function totalForInstance(int $instanceId): int
    {
        return (int) static::where('federated_instance_id', $instanceId)->sum('clicks');
    }
}
