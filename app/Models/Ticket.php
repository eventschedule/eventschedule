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
        'image_url',
        'url',
    ];

    protected $casts = [
        'custom_fields' => 'array',
        'volume_discount' => 'array',
        'sales_start_at' => 'datetime',
        'sales_end_at' => 'datetime',
        'is_addon' => 'boolean',
    ];

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

    public function updateSold($date, $quantity)
    {
        \DB::transaction(function () use ($date, $quantity) {
            $ticket = Ticket::lockForUpdate()->find($this->id);
            $sold = $ticket->sold ? json_decode($ticket->sold, true) : [];
            $sold[$date] = max(0, ($sold[$date] ?? 0) + $quantity);
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
        $data['quantity'] = $this->quantity;
        $data['max_per_order'] = $this->max_per_order ?: null;
        $data['price'] = $this->price;
        $data['description'] = $this->description ? UrlUtils::convertUrlsToLinks($this->description_html ?? $this->description) : null;
        $data['image_url'] = $this->image_url ?: null;
        $data['url'] = $this->url ?: null;

        $sold = $this->sold ? json_decode($this->sold, true) : [];
        $sold = $sold[$date] ?? 0;

        $perOrderCap = $this->max_per_order ?: 20;

        // Handle combined mode logic
        if ($this->event && ! $this->is_addon && $this->event->total_tickets_mode === 'combined' && $this->event->hasSameTicketQuantities()) {
            $totalSold = $this->event->tickets->sum(function ($ticket) use ($date) {
                $ticketSold = $ticket->sold ? json_decode($ticket->sold, true) : [];

                return $ticketSold[$date] ?? 0;
            });
            // In combined mode, the total quantity is the same as individual quantity
            $totalQuantity = $this->event->getSameTicketQuantity();
            $data['quantity'] = $totalQuantity > 0 ? max(0, min($perOrderCap, $totalQuantity - $totalSold)) : $perOrderCap;
        } else {
            $data['quantity'] = $this->quantity > 0 ? max(0, min($perOrderCap, $this->quantity - $sold)) : $perOrderCap;
        }

        $data['volume_discount'] = TicketVolumeDiscount::toGuestPayload($this->volume_discount);

        return $data;
    }
}
