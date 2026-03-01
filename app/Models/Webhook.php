<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Webhook extends Model
{
    protected $fillable = [
        'user_id',
        'url',
        'secret',
        'event_types',
        'is_active',
        'description',
        'last_triggered_at',
    ];

    protected $casts = [
        'event_types' => 'array',
        'is_active' => 'boolean',
        'secret' => 'encrypted',
        'last_triggered_at' => 'datetime',
    ];

    public const EVENT_TYPES = [
        'sale.created',
        'sale.paid',
        'sale.refunded',
        'sale.cancelled',
        'event.created',
        'event.updated',
        'event.deleted',
        'ticket.scanned',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function deliveries()
    {
        return $this->hasMany(WebhookDelivery::class);
    }

    public function subscribesTo(string $eventType): bool
    {
        if (empty($this->event_types)) {
            return true;
        }

        return in_array($eventType, $this->event_types);
    }
}
