<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One cancelled platform subscription, and why. See the 2026_09_28_000002 migration.
 *
 * Every cancel path writes through record(), which keeps one row per subscription: the in-app
 * cancel usually lands first with a reason, and the webhooks that follow (the period ending, or a
 * portal cancel) fill gaps rather than add a second row.
 */
class SubscriptionCancellation extends Model
{
    public const UPDATED_AT = null;

    /** Offered on the in-app cancel form, in this order. */
    public const REASONS = [
        'season_over',
        'not_using',
        'too_expensive',
        'missing_feature',
        'switched_tool',
        'too_complex',
        'technical_problem',
        'other',
    ];

    /**
     * Stripe's cancellation_details.feedback values, mapped onto ours. customer_service has no
     * counterpart and lands on 'other' rather than inventing a reason nobody picked in-app.
     */
    public const STRIPE_FEEDBACK = [
        'too_expensive' => 'too_expensive',
        'missing_features' => 'missing_feature',
        'switched_service' => 'switched_tool',
        'unused' => 'not_using',
        'too_complex' => 'too_complex',
        'low_quality' => 'technical_problem',
        'customer_service' => 'other',
        'other' => 'other',
    ];

    public const SOURCES = ['app', 'portal', 'payment_failed', 'schedule_deleted', 'admin'];

    protected $fillable = [
        'role_id', 'user_id', 'stripe_subscription_id', 'source', 'reason', 'comment',
        'plan_type', 'plan_term', 'resumed_at', 'created_at',
    ];

    protected $casts = [
        'resumed_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    /**
     * Record a cancellation, once per subscription. A second report of the same subscription
     * (the webhook after an in-app cancel, the period-end deletion after either) only fills in a
     * reason or comment the first one lacked; it never replaces what the person said, and never
     * changes the source that saw it first. A cancel that was resumed and then cancelled again
     * is a new row.
     */
    public static function record(array $attributes): self
    {
        $subscriptionId = $attributes['stripe_subscription_id'] ?? null;

        $open = $subscriptionId === null ? null : static::where('stripe_subscription_id', $subscriptionId)
            ->whereNull('resumed_at')
            ->latest('id')
            ->first();

        if ($open) {
            // The in-app form is where the person actually answered, and the webhook its cancel
            // triggers can land before this request writes its row, so 'app' wins the source.
            if (($attributes['source'] ?? null) === 'app') {
                $open->source = 'app';
            }
            foreach (['reason', 'comment', 'user_id'] as $field) {
                if ($open->{$field} === null && ! empty($attributes[$field])) {
                    $open->{$field} = $attributes[$field];
                }
            }
            $open->save();

            return $open;
        }

        return static::create($attributes + ['created_at' => now()]);
    }

    /** The subscription is running again: a cancel taken back in the grace period is not churn. */
    public static function markResumed(?string $subscriptionId): void
    {
        if ($subscriptionId === null) {
            return;
        }

        static::where('stripe_subscription_id', $subscriptionId)
            ->whereNull('resumed_at')
            ->update(['resumed_at' => now()]);
    }
}
