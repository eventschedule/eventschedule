<?php

namespace App\Policies;

use App\Models\Image;
use App\Models\User;

class ImagePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->exists;
    }

    public function view(User $user, Image $image): bool
    {
        if (is_null($image->user_id)) {
            return true;
        }

        return $this->canManage($user, $image);
    }

    public function create(User $user): bool
    {
        return $user->exists;
    }

    public function update(User $user, Image $image): bool
    {
        if (is_null($image->user_id)) {
            return $user->isAdmin();
        }

        return $this->canManage($user, $image);
    }

    public function delete(User $user, Image $image): bool
    {
        if (is_null($image->user_id)) {
            return $user->isAdmin();
        }

        return $this->canManage($user, $image);
    }

    private function canManage(User $user, Image $image): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return (int) $image->user_id === (int) $user->id;
    }
}
