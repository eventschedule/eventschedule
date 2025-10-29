<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Notification;
use App\Notifications\TicketCancelledNotification;
use App\Notifications\TicketPaidNotification;
use App\Notifications\TicketTimeoutNotification;
use App\Utils\NotificationUtils;

class Sale extends Model
{
    protected $casts = [
        'last_reminder_sent_at' => 'datetime',
    ];

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

            if ($sale->wasChanged('status') && $sale->status === 'cancelled') {
                $sale->loadMissing(['saleTickets.ticket', 'event.roles.members', 'event.venue.members', 'event.creatorRole.members', 'event.user']);

                self::sendCancelledNotifications($sale);
            }

            if ($sale->wasChanged('status') && $sale->status === 'paid') {
                $sale->loadMissing(['saleTickets.ticket', 'event.roles.members', 'event.venue.members', 'event.creatorRole.members', 'event.user']);

                self::sendPaidNotifications($sale);
            }

            if ($sale->wasChanged('status') && $sale->status === 'expired') {
                $sale->loadMissing(['saleTickets.ticket', 'event.roles.members', 'event.venue.members', 'event.creatorRole.members', 'event.user']);

                self::sendTimeoutNotifications($sale);
            }
        });
    }

    protected static function sendCancelledNotifications(self $sale): void
    {
        $event = $sale->event;
        $contextRole = $event->venue ?: $event->creatorRole;

        if ($sale->email) {
            Notification::route('mail', $sale->email)
                ->notify(new TicketCancelledNotification($sale, 'purchaser', $contextRole));
        }

        $notifiedUserIds = collect();
        $organizerRoles = collect([$event->creatorRole, $event->venue])->filter();

        NotificationUtils::uniqueRoleMembersWithContext($organizerRoles)->each(function (array $recipient) use (&$notifiedUserIds, $sale) {
            $recipient['user']->notify(new TicketCancelledNotification($sale, 'organizer', $recipient['role']));
            $notifiedUserIds->push($recipient['user']->id);
        });

        if ($event->user && $event->user->email && $event->user->is_subscribed !== false && ! $notifiedUserIds->contains($event->user->id)) {
            Notification::send($event->user, new TicketCancelledNotification($sale, 'organizer', $contextRole));
        }
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

        NotificationUtils::uniqueRoleMembersWithContext($organizerRoles)->each(function (array $recipient) use (&$notifiedUserIds, $sale) {
            $recipient['user']->notify(new TicketPaidNotification($sale, 'organizer', $recipient['role']));
            $notifiedUserIds->push($recipient['user']->id);
        });

        if ($event->user && $event->user->email && $event->user->is_subscribed !== false && ! $notifiedUserIds->contains($event->user->id)) {
            Notification::send($event->user, new TicketPaidNotification($sale, 'organizer', $contextRole));
        }
    }

    protected static function sendTimeoutNotifications(self $sale): void
    {
        $event = $sale->event;
        $contextRole = $event->venue ?: $event->creatorRole;

        if ($sale->email) {
            Notification::route('mail', $sale->email)
                ->notify(new TicketTimeoutNotification($sale, 'purchaser', $contextRole));
        }

        $notifiedUserIds = collect();
        $organizerRoles = collect([$event->creatorRole, $event->venue])->filter();

        NotificationUtils::uniqueRoleMembersWithContext($organizerRoles)->each(function (array $recipient) use (&$notifiedUserIds, $sale) {
            $recipient['user']->notify(new TicketTimeoutNotification($sale, 'organizer', $recipient['role']));
            $notifiedUserIds->push($recipient['user']->id);
        });

        if ($event->user && $event->user->email && $event->user->is_subscribed !== false && ! $notifiedUserIds->contains($event->user->id)) {
            Notification::send($event->user, new TicketTimeoutNotification($sale, 'organizer', $contextRole));
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

    public function hasBeenScanned(): bool
    {
        $this->loadMissing('saleTickets');

        return $this->saleTickets->contains(function (SaleTicket $saleTicket) {
            return $saleTicket->hasBeenScanned();
        });
    }

    public function getUsageStatusAttribute(): string
    {
        return $this->hasBeenScanned() ? 'used' : 'unused';
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
