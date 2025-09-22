<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Notification;
use App\Notifications\TicketPaidNotification;
use App\Utils\NotificationUtils;

class Sale extends Model
{
    protected $fillable = [
        'event_id',
        'name',
        'email',
        'secret',
        'event_date',
        'subdomain',
    ];

    protected static function booted()
    {
        static::updated(function (self $sale) {
            if ($sale->wasChanged('status') && in_array($sale->status, ['cancelled', 'refunded', 'expired'])) {
                $sale->loadMissing('saleTickets.ticket');

                foreach ($sale->saleTickets as $saleTicket) {
                    $saleTicket->ticket->updateSold($sale->event_date, -$saleTicket->quantity);
                }
            }

            if ($sale->wasChanged('status') && $sale->status === 'paid') {
                $sale->loadMissing(['saleTickets.ticket', 'event.roles.members', 'event.venue.members', 'event.creatorRole.members', 'event.user']);

                self::sendPaidNotifications($sale);
            }
        });
    }

    protected static function sendPaidNotifications(self $sale): void
    {
        $event = $sale->event;
        $contextRole = $event->venue ?: $event->creatorRole;

        if ($sale->email) {
            Notification::route('mail', $sale->email)
                ->notify(new TicketPaidNotification($sale, 'purchaser', $contextRole));
        }

        $notifiedUserIds = collect();
        $organizerRoles = collect([$event->creatorRole, $event->venue])->filter();

        foreach ($organizerRoles as $organizerRole) {
            $members = NotificationUtils::roleMembers($organizerRole);

            if ($members->isNotEmpty()) {
                Notification::send($members, new TicketPaidNotification($sale, 'organizer', $organizerRole));
                $notifiedUserIds = $notifiedUserIds->merge($members->pluck('id'));
            }
        }

        if ($event->user && $event->user->email && $event->user->is_subscribed !== false && ! $notifiedUserIds->contains($event->user->id)) {
            Notification::send($event->user, new TicketPaidNotification($sale, 'organizer', $contextRole));
        }
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

        return $event->getGuestUrl($this->subdomain);
    }
}
