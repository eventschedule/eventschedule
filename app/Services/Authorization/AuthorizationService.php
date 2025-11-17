<?php

namespace App\Services\Authorization;

use App\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class AuthorizationService
{
    public function warmUserPermissions(User $user): array
    {
        if (! $this->permissionsAvailable()) {
            return [];
        }

        $roleIds = $user->systemRoles()->pluck('auth_roles.id');

        $permissions = collect();

        foreach ($roleIds as $roleId) {
            $permissions = $permissions->merge($this->permissionsForRole((int) $roleId));
        }

        $permissionSet = $permissions->unique()->values()->all();

        Cache::put(
            $this->userCacheKey($user->getKey()),
            $permissionSet,
            now()->addSeconds($this->userCacheTtl())
        );

        return $permissionSet;
    }

    public function userHasPermission(User $user, string $permissionKey): bool
    {
        if ($this->legacyAdminAccessApplies($user)) {
            return true;
        }

        if (! $this->permissionsAvailable()) {
            return false;
        }

        $permissions = Cache::get($this->userCacheKey($user->getKey()));

        if ($permissions === null) {
            $permissions = $this->warmUserPermissions($user);
        }

        return in_array($permissionKey, $permissions, true);
    }

    public function userHasAnyPermission(User $user, array $permissionKeys): bool
    {
        $permissionKeys = array_values(array_filter($permissionKeys));

        if ($permissionKeys === []) {
            return true;
        }

        foreach ($permissionKeys as $permissionKey) {
            if ($this->userHasPermission($user, $permissionKey)) {
                return true;
            }
        }

        return false;
    }

    public function forgetUserPermissions(User $user): void
    {
        Cache::forget($this->userCacheKey($user->getKey()));
    }

    public function forgetRolePermissions(int $roleId): void
    {
        Cache::forget($this->roleCacheKey($roleId));
    }

    protected function permissionsForRole(int $roleId): array
    {
        if (! $this->permissionsAvailable()) {
            return [];
        }

        return Cache::rememberForever($this->roleCacheKey($roleId), function () use ($roleId) {
            return Permission::query()
                ->select('permissions.key')
                ->join('role_permissions', 'permissions.id', '=', 'role_permissions.permission_id')
                ->where('role_permissions.role_id', $roleId)
                ->pluck('key')
                ->unique()
                ->values()
                ->all();
        });
    }

    protected function legacyAdminAccessApplies(User $user): bool
    {
        return method_exists($user, 'hasLegacyAdminAccess') && $user->hasLegacyAdminAccess();
    }

    protected function userCacheKey(int $userId): string
    {
        $prefix = config('authorization.cache.user_permissions_prefix', 'auth:user');

        return sprintf('%s:%d:permissions', $prefix, $userId);
    }

    protected function roleCacheKey(int $roleId): string
    {
        $prefix = config('authorization.cache.role_permissions_prefix', 'auth:role');

        return sprintf('%s:%d:permissions', $prefix, $roleId);
    }

    protected function userCacheTtl(): int
    {
        return max((int) config('authorization.cache.user_permissions_ttl', 3600), 60);
    }

    protected function permissionsAvailable(): bool
    {
        static $result;

        if ($result !== null) {
            return $result;
        }

        return $result = Schema::hasTable('permissions')
            && Schema::hasTable('auth_roles')
            && Schema::hasTable('user_roles');
    }
}
