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
        $data['description'] = $this->description;

        $sold = $this->sold ? json_decode($this->sold, true) : [];
        $sold = $sold[$date] ?? 0;
        $data['quantity'] = $this->quantity > 0 ? max(0, min(20, $this->quantity - $sold)) : 20;

        return $data;
    }
}
  