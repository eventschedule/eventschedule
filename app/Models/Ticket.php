<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Utils\UrlUtils;

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

    public function toData()
    {
        $data = [];
        $data['event_id'] = UrlUtils::encodeId($this->event_id);
        $data['type'] = $this->type;
        $data['quantity'] = $this->quantity;
        $data['sold'] = $this->sold;
        $data['price'] = $this->price;
        $data['description'] = $this->description;

        return $data;
    }
}
