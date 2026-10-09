<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;

class EventPolicy
{
    public function update(User $user, Event $event): bool
    {
        return $user->canEditEvent($event);
    }

    /** Deleting, cancelling and restoring: the event's own people, not every schedule it is on. */
    public function delete(User $user, Event $event): bool
    {
        return $user->runsEvent($event);
    }
}
