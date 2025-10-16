<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Utils\UrlUtils;
use App\Utils\MarkdownUtils;
class Ticket extends Model
{
    protected $fillable = [
        'event_id',
        'type',
        'quantity',
        'sold',
        'price',
        'description',
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
        if (! $this->sold) {
            $this->sold = json_encode([]);
        }

        $sold = json_decode($this->sold, true);
        $sold[$date] = $sold[$date] ?? 0;
        $sold[$date] += $quantity;
        $this->sold = json_encode($sold);
        $this->save();
    }

    public function toData($date = null)
    {
        $data = [];
        $data['id'] = UrlUtils::encodeId($this->id);
        $data['event_id'] = UrlUtils::encodeId($this->event_id);
        $data['type'] = $this->type;
        $data['quantity'] = $this->quantity;
        $data['price'] = $this->price;
        $data['description'] = $this->description ? UrlUtils::convertUrlsToLinks($this->description) : null;

        $sold = $this->sold ? json_decode($this->sold, true) : [];
        $sold = $sold[$date] ?? 0;
        
        // Handle combined mode logic
        if ($this->event && $this->event->total_tickets_mode === 'combined' && $this->event->hasSameTicketQuantities()) {
            $totalSold = $this->event->tickets->sum(function($ticket) use ($date) {
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
  