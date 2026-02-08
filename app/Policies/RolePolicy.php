<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;

class RolePolicy
{
    public function update(User $user, Role $role): bool
    {
        return $user->isMember($role->subdomain);
    }

    public function manageMembers(User $user, Role $role): bool
    {
        return $user->id == $role->user_id;
    }
}
