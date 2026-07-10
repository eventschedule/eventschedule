<?php

namespace App\Models;

use App\Services\TicketVolumeDiscount;
use App\Utils\MarkdownUtils;
use App\Utils\UrlUtils;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            $model->description_html = MarkdownUtils::convertToHtml($model->description);
        });
    }

    protected $fillable = [
        'event_id',
        'type',
        'quantity',
        'max_per_order',
        'sold',
        'price',
        'description',
        'sales_start_at',
        'sales_end_at',
        'custom_fields',
        'volume_discount',
        'is_addon',
        'is_pass',
        'pass_usage_type',
        'pass_max_uses',
        'pass_valid_days',
        'pass_scope',
        'pass_scope_group_id',
        'pass_event_ids',
        'pass_allow_booking',
        'pass_seats_per_occurrence',
        'pass_admits_per_event',
        'image_url',
        'url',
    ];

    protected $casts = [
        'custom_fields' => 'array',
        'volume_discount' => 'array',
        'sales_start_at' => 'datetime',
        'sales_end_at' => 'datetime',
        'is_addon' => 'boolean',
        'is_pass' => 'boolean',
        'pass_max_uses' => 'integer',
        'pass_valid_days' => 'integer',
        'pass_scope_group_id' => 'integer',
        'pass_event_ids' => 'array',
        'pass_allow_booking' => 'boolean',
        'pass_seats_per_occurrence' => 'integer',
        'pass_admits_per_event' => 'integer',
    ];

    /**
     * How many people this pass admits at each event (including the holder),
     * resolved to at least 1. A value above 1 lets the holder bring guests, so
     * the QR may be scanned that many times per event. Independent of the visit
     * allowance - each event still counts as a single use.
     */
    public function admitsPerEvent(): int
    {
        return max(1, (int) ($this->pass_admits_per_event ?: 1));
    }

    public function isSalesNotStarted()
    {
        return $this->sales_start_at && $this->sales_start_at->isFuture();
    }

    public function isSalesEnded()
    {
        return $this->sales_end_at && $this->sales_end_at->isPast();
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Resolve the set of event IDs a subscription/pass covers, within its home
     * schedule. `all_events`/`sub_schedule` are resolved dynamically so events
     * created after the pass was sold are covered automatically; `specific_events`
     * is the fixed list the owner picked.
     */
    public function coveredEventIds(?Role $schedule = null): array
    {
        switch ($this->pass_scope) {
            case 'all_events':
                return $schedule
                    ? $schedule->events()->pluck('events.id')->map(fn ($id) => (int) $id)->all()
                    : [];
            case 'sub_schedule':
                if (! $schedule || ! $this->pass_scope_group_id) {
                    return [];
                }

                return $schedule->events()
                    ->wherePivot('group_id', $this->pass_scope_group_id)
                    ->pluck('events.id')->map(fn ($id) => (int) $id)->all();
            case 'specific_events':
                $ids = array_map('intval', $this->pass_event_ids ?? []);

                // Without a home schedule there is nothing to intersect against, so the stored
                // ids cannot be trusted - deny, as `all_events` and `sub_schedule` already do.
                // Returning them raw would cover any event id in the system, and would let a
                // reservation be written for an event outside the pass's schedules, where
                // Event::computePassReservedSeats() cannot see it.
                if (! $schedule || empty($ids)) {
                    return [];
                }

                // Intersect with the home schedule's events so a stale or forged id
                // belonging to another tenant can never be reported as covered.
                return $schedule->events()
                    ->whereIn('events.id', $ids)
                    ->pluck('events.id')->map(fn ($id) => (int) $id)->all();
            case 'this_event':
            default:
                return [(int) $this->event_id];
        }
    }

    /**
     * Whether this pass can be redeemed at the given event. Coverage always
     * resolves within the pass's own schedule, so a covered event must belong
     * to $schedule (cross-tenant safety).
     */
    public function covers(Event $event, ?Role $schedule = null): bool
    {
        switch ($this->pass_scope) {
            case 'all_events':
                return $schedule
                    ? $schedule->events()->where('events.id', $event->id)->exists()
                    : false;
            case 'sub_schedule':
                return $schedule && $this->pass_scope_group_id
                    ? $schedule->events()
                        ->wherePivot('group_id', $this->pass_scope_group_id)
                        ->where('events.id', $event->id)->exists()
                    : false;
            case 'specific_events':
                if (! in_array((int) $event->id, array_map('intval', $this->pass_event_ids ?? []), true)) {
                    return false;
                }

                // Even when the id is in the stored list, require the event to
                // belong to the pass's home schedule (defends against a stale or
                // forged cross-tenant id). With no schedule to check against, deny -
                // matching all_events / sub_schedule above.
                return $schedule
                    ? $schedule->events()->where('events.id', $event->id)->exists()
                    : false;
            case 'this_event':
            default:
                return (int) $event->id === (int) $this->event_id;
        }
    }

    public function getImageUrlAttribute($value)
    {
        if (! $value) {
            return '';
        }

        if (config('app.hosted') && config('filesystems.default') == 'do_spaces') {
            return 'https://eventschedule.nyc3.cdn.digitaloceanspaces.com/'.$value;
        } elseif (in_array(config('filesystems.default'), ['local', 'public'])) {
            return url('/storage/'.$value);
        } else {
            return $value;
        }
    }

    /**
     * The key under which this ticket's sold count is tracked. Passes are not
     * per-date (one QR covers the whole recurring series), so their inventory
     * lives in a single bucket rather than per occurrence date.
     */
    public function soldKey($date)
    {
        return $this->is_pass ? 'pass' : $date;
    }

    /**
     * Number sold for a given occurrence date (or the whole pass pool for passes).
     */
    public function soldCountFor($date): int
    {
        $sold = $this->sold ? json_decode($this->sold, true) : [];

        return (int) ($sold[$this->soldKey($date)] ?? 0);
    }

    public function updateSold($date, $quantity)
    {
        \DB::transaction(function () use ($date, $quantity) {
            $ticket = Ticket::lockForUpdate()->find($this->id);
            $key = $ticket->soldKey($date);
            $sold = $ticket->sold ? json_decode($ticket->sold, true) : [];
            $sold[$key] = max(0, ($sold[$key] ?? 0) + $quantity);
            $ticket->sold = json_encode($sold);
            $ticket->save();
        });
    }

    public function lineGrossSubtotal(int $quantity): float
    {
        return (float) $this->price * max(0, $quantity);
    }

    public function volumeDiscountAmountForQuantity(int $quantity): float
    {
        if ($this->is_addon) {
            return 0.0;
        }

        return TicketVolumeDiscount::volumeDiscountAmount(
            $this->volume_discount,
            (float) $this->price,
            $quantity,
            TicketVolumeDiscount::decimalsForTicket($this)
        );
    }

    public function lineSubtotalAfterVolumeDiscount(int $quantity): float
    {
        if ($this->is_addon) {
            return $this->lineGrossSubtotal($quantity);
        }

        return TicketVolumeDiscount::lineSubtotalAfterVolume(
            $this->volume_discount,
            (float) $this->price,
            $quantity,
            TicketVolumeDiscount::decimalsForTicket($this)
        );
    }

    public function toData($date = null)
    {
        $data = [];
        $data['id'] = UrlUtils::encodeId($this->id);
        $data['event_id'] = UrlUtils::encodeId($this->event_id);
        $data['type'] = $this->type;
        $data['is_addon'] = (bool) $this->is_addon;
        $data['is_pass'] = (bool) $this->is_pass;
        if ($this->is_pass) {
            $data['pass_usage_type'] = $this->pass_usage_type;
            $data['pass_max_uses'] = $this->pass_max_uses ?: null;
            $data['pass_valid_days'] = $this->pass_valid_days ?: null;
            $data['pass_scope'] = $this->pass_scope;
            $data['pass_allow_booking'] = (bool) $this->pass_allow_booking;
            $data['pass_admits_per_event'] = $this->admitsPerEvent();
            $data['pass_covered_count'] = $this->pass_scope === 'specific_events'
                ? count($this->pass_event_ids ?? [])
                : null;
        }
        $data['quantity'] = $this->quantity;
        $data['max_per_order'] = $this->max_per_order ?: null;
        $data['price'] = $this->price;
        $data['description'] = $this->description ? UrlUtils::convertUrlsToLinks($this->description_html ?? $this->description) : null;
        $data['image_url'] = $this->image_url ?: null;
        $data['url'] = $this->url ?: null;

        $sold = $this->soldCountFor($date);

        $perOrderCap = $this->max_per_order ?: 20;

        // Passes and add-ons use only their own pool. A regular seat ticket is bounded
        // by both its own pool AND the shared per-occurrence house (which subtracts pass
        // advance-bookings): for combined mode the house is always the tighter bound, for
        // individual mode both apply, and for non-pass events the house never binds
        // tighter than the per-ticket limit (so behavior is unchanged there).
        if ($this->is_pass || $this->is_addon || ! $this->event) {
            $data['quantity'] = $this->quantity > 0 ? max(0, min($perOrderCap, $this->quantity - $sold)) : $perOrderCap;
        } else {
            $houseRemaining = $this->event->occurrenceSeatsRemaining($date);
            $ownRemaining = $this->quantity > 0 ? max(0, $this->quantity - $sold) : null;
            $bounds = array_filter([$ownRemaining, $houseRemaining], fn ($v) => $v !== null);
            $data['quantity'] = empty($bounds) ? $perOrderCap : max(0, min($perOrderCap, min($bounds)));
        }

        $data['volume_discount'] = TicketVolumeDiscount::toGuestPayload($this->volume_discount);

        return $data;
    }
}
