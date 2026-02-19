<?php

namespace App\Policies;

use App\Models\BoostCampaign;
use App\Models\Event;
use App\Models\User;

class BoostCampaignPolicy
{
    public function create(User $user, Event $event): bool
    {
        return $user->canEditEvent($event);
    }

    public function update(User $user, BoostCampaign $campaign): bool
    {
        if ($user->id === $campaign->user_id) {
            return true;
        }

        // Check if user is admin/owner of the campaign's role
        $pivot = $user->roles()
            ->where('roles.id', $campaign->role_id)
            ->wherePivotIn('level', ['owner', 'admin'])
            ->first();

        return $pivot !== null;
    }

    public function delete(User $user, BoostCampaign $campaign): bool
    {
        return $this->update($user, $campaign);
    }
}
