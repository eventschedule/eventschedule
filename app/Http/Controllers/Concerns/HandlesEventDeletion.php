<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Event;
use App\Models\User;
use App\Notifications\DeletedEventNotification;
use App\Utils\NotificationUtils;
use Illuminate\Support\Facades\Notification;

trait HandlesEventDeletion
{
    /**
     * Handle event deletion with notifications to all relevant parties
     */
    protected function handleEventDeletion(Event $event, User $user): void
    {
        $event->loadMissing(['roles.members', 'venue.members', 'creatorRole.members', 'sales']);

        // Notify talent roles
        $talentRoles = $event->roles->filter(fn ($roleModel) => $roleModel->isTalent());

        NotificationUtils::uniqueRoleMembersWithContext($talentRoles)->each(function (array $recipient) use ($event, $user) {
            $recipient['user']->notify(new DeletedEventNotification($event, $user, 'talent', $recipient['role']));
        });

        // Notify organizers (venue and creator)
        $organizerRoles = collect([$event->creatorRole, $event->venue])->filter();

        NotificationUtils::uniqueRoleMembersWithContext($organizerRoles)->each(function (array $recipient) use ($event, $user) {
            $recipient['user']->notify(new DeletedEventNotification($event, $user, 'organizer', $recipient['role']));
        });

        // Notify ticket purchasers
        $purchaserEmails = NotificationUtils::purchaserEmails($event);

        if ($purchaserEmails->isNotEmpty()) {
            Notification::route('mail', $purchaserEmails->all())
                ->notify(new DeletedEventNotification($event, $user, 'purchaser', $event->venue));
        }

        $event->delete();
    }
}
