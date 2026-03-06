<?php

namespace App\Models;

use App\Jobs\NotifyWaitlist;
use App\Utils\UrlUtils;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable = [
        'event_id',
        'user_id',
        'name',
        'email',
        'phone',
        'secret',
        'event_date',
        'subdomain',
        'status',
        'payment_method',
        'payment_amount',
        'transaction_reference',
        'custom_value1',
        'custom_value2',
        'custom_value3',
        'custom_value4',
        'custom_value5',
        'custom_value6',
        'custom_value7',
        'custom_value8',
        'custom_value9',
        'custom_value10',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'boost_campaign_id',
        'newsletter_id',
        'promo_code_id',
        'discount_amount',
        'feedback_sent_at',
        'group_id',
    ];

    protected $casts = [
        'feedback_sent_at' => 'datetime',
    ];

    protected static bool $cascadingGroup = false;

    protected static function booted()
    {
        static::updated(function ($sale) {
            if ($sale->isDirty('status') && $sale->status === 'paid') {
                TicketWaitlist::where('event_id', $sale->event_id)
                    ->where('event_date', $sale->event_date)
                    ->where('email', $sale->email)
                    ->whereIn('status', ['waiting', 'notified'])
                    ->update(['status' => 'purchased']);

                // Cascade paid status to grouped sales
                if ($sale->group_id && $sale->isPrimarySale()) {
                    Sale::where('group_id', $sale->group_id)
                        ->where('id', '!=', $sale->id)
                        ->where('status', '!=', 'paid')
                        ->update(['status' => 'paid']);

                    // Clear waitlist entries for guest emails (raw update above skips booted hooks)
                    $guestEmails = Sale::where('group_id', $sale->group_id)
                        ->where('id', '!=', $sale->id)
                        ->pluck('email');
                    if ($guestEmails->isNotEmpty()) {
                        TicketWaitlist::where('event_id', $sale->event_id)
                            ->where('event_date', $sale->event_date)
                            ->whereIn('email', $guestEmails)
                            ->whereIn('status', ['waiting', 'notified'])
                            ->update(['status' => 'purchased']);
                    }
                }
            }

            if ($sale->isDirty('status') && in_array($sale->status, ['cancelled', 'refunded', 'expired'])) {
                if ($sale->payment_method === 'rsvp') {
                    $sale->event->updateRsvpSold($sale->event_date, -1);
                    if (! $sale->group_id || $sale->isPrimarySale()) {
                        AnalyticsEventsDaily::decrementSale($sale->event_id, 0);
                    }
                } else {
                    foreach ($sale->saleTickets as $saleTicket) {
                        $saleTicket->ticket->updateSold($sale->event_date, -$saleTicket->quantity);
                    }
                }

                if ($sale->promo_code_id) {
                    PromoCode::where('id', $sale->promo_code_id)
                        ->lockForUpdate()
                        ->decrement('times_used');
                }

                // Only dispatch waitlist notification from primary or ungrouped sales
                if (! $sale->group_id || $sale->isPrimarySale()) {
                    NotifyWaitlist::dispatch($sale->event_id, $sale->event_date);
                }

                // Cascade cancel/refund/expired to grouped sales
                if ($sale->group_id && $sale->isPrimarySale() && ! static::$cascadingGroup) {
                    static::$cascadingGroup = true;
                    try {
                        $groupedSales = Sale::where('group_id', $sale->group_id)
                            ->where('id', '!=', $sale->id)
                            ->whereNotIn('status', ['cancelled', 'refunded', 'expired'])
                            ->get();
                        foreach ($groupedSales as $groupSale) {
                            $groupSale->status = $sale->status;
                            $groupSale->save();
                        }
                    } finally {
                        static::$cascadingGroup = false;
                    }
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

    public function boostCampaign()
    {
        return $this->belongsTo(BoostCampaign::class);
    }

    public function promoCode()
    {
        return $this->belongsTo(PromoCode::class);
    }

    public function feedback()
    {
        return $this->hasOne(EventFeedback::class);
    }

    public function groupedSales()
    {
        return $this->hasMany(Sale::class, 'group_id', 'group_id');
    }

    public function isPrimarySale()
    {
        return $this->group_id && $this->group_id === $this->id;
    }

    public function isRsvp()
    {
        return $this->payment_method === 'rsvp';
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

    public function toApiData($includeSecret = false)
    {
        $data = new \stdClass;

        $data->id = UrlUtils::encodeId($this->id);
        $data->event_id = UrlUtils::encodeId($this->event_id);
        $data->event_name = $this->event?->name;
        $data->subdomain = $this->subdomain;
        $data->name = $this->name;
        $data->email = $this->email;
        $data->phone = $this->phone;
        $data->event_date = $this->event_date;
        $data->status = $this->status;
        $data->payment_method = $this->payment_method;
        $data->payment_amount = (float) $this->payment_amount;
        $data->transaction_reference = $this->transaction_reference;

        // Include secret when explicitly requested (e.g. webhook payloads) or when the authenticated user is authorized
        if ($includeSecret) {
            $data->secret = $this->secret;
        } else {
            $authUser = auth()->user();
            if ($authUser && ($authUser->id === $this->user_id || $authUser->id === $this->event?->user_id)) {
                $data->secret = $this->secret;
            }
        }

        $data->created_at = $this->created_at ? $this->created_at->toIso8601String() : null;
        $data->updated_at = $this->updated_at ? $this->updated_at->toIso8601String() : null;

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
