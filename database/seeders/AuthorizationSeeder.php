<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\SystemRole;
use App\Models\User;
use App\Services\Authorization\AuthorizationService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AuthorizationSeeder extends Seeder
{
    public function run(): void
    {
        $permissionDefinitions = [
            'settings.manage' => 'Manage system settings',
            'users.manage' => 'Invite and manage users',
            'resources.manage' => 'Create and update venues, talent, and curators within scope',
            'resources.view' => 'View venues, talent, and curators within scope',
        ];

        $permissions = collect($permissionDefinitions)->map(function ($description, $key) {
            return Permission::query()->updateOrCreate(
                ['key' => $key],
                ['description' => $description]
            );
        });

        $roles = [
            'superadmin' => 'Super Admin',
            'admin' => 'Admin',
            'viewer' => 'Viewer',
        ];

        $roleModels = collect($roles)->map(function ($name, $slug) {
            return SystemRole::query()->updateOrCreate(
                ['slug' => $slug],
                ['name' => $name]
            );
        });

        $allPermissionKeys = $permissions->pluck('key')->all();

        $rolePermissions = [
            'superadmin' => $allPermissionKeys,
            'admin' => ['resources.manage', 'resources.view'],
            'viewer' => ['resources.view'],
        ];

        /** @var AuthorizationService $authorization */
        $authorization = app(AuthorizationService::class);

        foreach ($rolePermissions as $slug => $permissionKeys) {
            $role = $roleModels[$slug] ?? null;

            if (! $role) {
                continue;
            }

            $permissionIds = $permissions
                ->whereIn('key', $permissionKeys)
                ->pluck('id')
                ->all();

            $role->permissions()->sync($permissionIds);
            $authorization->forgetRolePermissions($role->getKey());
        }

        if (! DB::table('user_roles')->exists()) {
            $superRole = $roleModels['superadmin'] ?? null;

            if ($superRole) {
                $firstUser = User::query()->orderBy('id')->first();

                if ($firstUser) {
                    $firstUser->systemRoles()->syncWithoutDetaching([$superRole->getKey()]);
                    $authorization->forgetUserPermissions($firstUser);
                }
            }
        }
    }
}
