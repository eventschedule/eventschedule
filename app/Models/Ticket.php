<?php

namespace App\Models;

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
        'sold',
        'price',
        'description',
        'custom_fields',
    ];

    protected $casts = [
        'custom_fields' => 'array',
    ];

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function updateSold($date, $quantity)
    {
        \DB::transaction(function () use ($date, $quantity) {
            $ticket = Ticket::lockForUpdate()->find($this->id);
            $sold = $ticket->sold ? json_decode($ticket->sold, true) : [];
            $sold[$date] = ($sold[$date] ?? 0) + $quantity;
            $ticket->sold = json_encode($sold);
            $ticket->save();
        });
    }

    public function toData($date = null)
    {
        $data = [];
        $data['id'] = UrlUtils::encodeId($this->id);
        $data['event_id'] = UrlUtils::encodeId($this->event_id);
        $data['type'] = $this->type;
        $data['quantity'] = $this->quantity;
        $data['price'] = $this->price;
        $data['description'] = $this->description ? UrlUtils::convertUrlsToLinks($this->description_html ?? $this->description) : null;

        $sold = $this->sold ? json_decode($this->sold, true) : [];
        $sold = $sold[$date] ?? 0;

        // Handle combined mode logic
        if ($this->event && $this->event->total_tickets_mode === 'combined' && $this->event->hasSameTicketQuantities()) {
            $totalSold = $this->event->tickets->sum(function ($ticket) use ($date) {
                $ticketSold = $ticket->sold ? json_decode($ticket->sold, true) : [];

                return $ticketSold[$date] ?? 0;
            });
            // In combined mode, the total quantity is the same as individual quantity
            $totalQuantity = $this->event->getSameTicketQuantity();
            $data['quantity'] = $totalQuantity > 0 ? max(0, min(20, $totalQuantity - $totalSold)) : 20;
        } else {
            $data['quantity'] = $this->quantity > 0 ? max(0, min(20, $this->quantity - $sold)) : 20;
        }

        return $data;
    }
}
