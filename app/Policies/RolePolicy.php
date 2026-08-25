<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;

class RolePolicy
{
    public function update(User $user, Role $role): bool
    {
        return $user->isEditor($role->subdomain);
    }

    /**
     * Inviting people: owners and admins both. The docs say an admin may "invite new members".
     */
    public function manageMembers(User $user, Role $role): bool
    {
        return $user->isEditor($role->subdomain);
    }

    /**
     * Changing somebody else's level, or removing them: the owner alone.
     *
     * The Team tab has always hidden both controls behind $isOwner and the docs have always said
     * an admin "cannot change anyone's level, remove another member" - but the server checked
     * manageMembers(), so an admin could do either with a direct request. Removing YOURSELF is
     * not this ability; RoleController::removeMember handles that case before it authorizes.
     */
    public function manageMemberLevels(User $user, Role $role): bool
    {
        return (int) $user->id === (int) $role->user_id;
    }
}
