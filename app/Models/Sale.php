<?php

namespace App\Models;

use App\Utils\UrlUtils;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable = [
        'event_id',
        'user_id',
        'name',
        'email',
        'secret',
        'event_date',
        'subdomain',
        'status',
        'payment_method',
        'payment_amount',
        'custom_value1',
        'custom_value2',
        'custom_value3',
        'custom_value4',
        'custom_value5',
        'custom_value6',
        'custom_value7',
        'custom_value8',
    ];

    protected static function booted()
    {
        static::updated(function ($sale) {
            if ($sale->isDirty('status') && in_array($sale->status, ['cancelled', 'refunded', 'expired'])) {
                foreach ($sale->saleTickets as $saleTicket) {
                    $saleTicket->ticket->updateSold($sale->event_date, -$saleTicket->quantity);
                }
            }
        });
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function saleTickets()
    {
        return $this->hasMany(SaleTicket::class);
    }

    public function calculateTotal()
    {
        return $this->saleTickets->sum(function ($saleTicket) {
            return $saleTicket->ticket->price * $saleTicket->quantity;
        });
    }

    public function quantity()
    {
        return $this->saleTickets->sum(function ($saleTicket) {
            return $saleTicket->quantity;
        });
    }

    public function getEventUrl()
    {
        $event = $this->event;

        return $event->getGuestUrl($this->subdomain, $this->event_date);
    }

    public function toApiData()
    {
        $data = new \stdClass;

        $data->id = UrlUtils::encodeId($this->id);
        $data->event_id = UrlUtils::encodeId($this->event_id);
        $data->name = $this->name;
        $data->email = $this->email;
        $data->event_date = $this->event_date;
        $data->status = $this->status;
        $data->payment_method = $this->payment_method;
        $data->payment_amount = (float) $this->payment_amount;
        $data->transaction_reference = $this->transaction_reference;
        $data->secret = $this->secret;
        $data->created_at = $this->created_at ? $this->created_at->toISOString() : null;
        $data->updated_at = $this->updated_at ? $this->updated_at->toISOString() : null;

        // Include tickets
        $data->tickets = $this->saleTickets->map(function ($saleTicket) {
            return [
                'ticket_id' => UrlUtils::encodeId($saleTicket->ticket_id),
                'quantity' => $saleTicket->quantity,
                'price' => (float) $saleTicket->ticket->price,
                'type' => $saleTicket->ticket->type,
            ];
        })->values();

        $data->total_quantity = $this->quantity();

        return $data;
    }
}
