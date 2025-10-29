<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleTicket extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'sale_id',
        'ticket_id',
        'quantity',
    ];

    protected static function booted()
    {
        static::created(function ($saleTicket) {
            $saleTicket->ticket->updateSold($saleTicket->sale->event_date, $saleTicket->quantity);
        });
    }

    public function entries()
    {
        return $this->hasMany(SaleTicketEntry::class);
    }

    public function hasBeenScanned(): bool
    {
        return $this->entries()->whereNotNull('scanned_at')->exists();
    }

    public function getUsageStatusAttribute(): string
    {
        if ($this->relationLoaded('entries')) {
            $scanned = $this->entries->contains(function (SaleTicketEntry $entry) {
                return $entry->scanned_at !== null;
            });

            return $scanned ? 'used' : 'unused';
        }

        return $this->hasBeenScanned() ? 'used' : 'unused';
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }
}
