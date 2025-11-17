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
            'roles.manage' => 'Manage system roles',
            'impersonate.use' => 'Impersonate users',
            'events.view' => 'View events',
            'events.create' => 'Create events',
            'events.update' => 'Update events',
            'events.delete' => 'Delete events',
            'events.publish' => 'Publish or unpublish events',
            'venues.view' => 'View venues',
            'venues.create' => 'Create venues',
            'venues.update' => 'Update venues',
            'venues.delete' => 'Delete venues',
            'talent.view' => 'View talent profiles',
            'talent.create' => 'Create talent profiles',
            'talent.update' => 'Update talent profiles',
            'talent.delete' => 'Delete talent profiles',
            'curators.view' => 'View curator records',
            'curators.create' => 'Create curator records',
            'curators.update' => 'Update curator records',
            'curators.delete' => 'Delete curator records',
            'tickets.view' => 'View tickets',
            'tickets.create' => 'Create tickets',
            'tickets.update' => 'Update tickets',
            'tickets.delete' => 'Delete tickets',
            'tickets.refund' => 'Refund tickets',
            'tickets.checkin' => 'Check in tickets',
            'tickets.issue' => 'Issue wallet passes',
            'orders.view' => 'View orders',
            'orders.refund' => 'Refund orders',
            'orders.export' => 'Export orders',
            'media.view' => 'View media assets',
            'media.create' => 'Create media assets',
            'media.update' => 'Update media assets',
            'media.delete' => 'Delete media assets',
            'wallet.view' => 'View wallet passes',
            'wallet.create' => 'Create wallet passes',
            'wallet.update' => 'Update wallet passes',
            'reports.view' => 'View reports',
            'reports.export' => 'Export reports',
        ];

        $permissions = collect($permissionDefinitions)->map(function ($description, $key) {
            return Permission::query()->updateOrCreate(
                ['key' => $key],
                ['description' => $description]
            );
        });

        $roles = [
            'superadmin' => 'Super Admin',
            'owner' => 'Owner',
            'admin' => 'Admin',
            'curator' => 'Curator',
            'editor' => 'Editor',
            'boxoffice' => 'Box Office',
            'door' => 'Door',
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
            'owner' => array_values(array_diff($allPermissionKeys, ['impersonate.use'])),
            'admin' => [
                'settings.manage', 'users.manage', 'roles.manage',
                'events.view', 'events.create', 'events.update', 'events.delete', 'events.publish',
                'venues.view', 'venues.create', 'venues.update', 'venues.delete',
                'talent.view', 'talent.create', 'talent.update', 'talent.delete',
                'curators.view', 'curators.create', 'curators.update', 'curators.delete',
                'tickets.view', 'tickets.create', 'tickets.update', 'tickets.delete', 'tickets.refund', 'tickets.checkin', 'tickets.issue',
                'orders.view', 'orders.refund', 'orders.export',
                'media.view', 'media.create', 'media.update', 'media.delete',
                'wallet.view', 'wallet.create', 'wallet.update',
                'reports.view', 'reports.export',
            ],
            'curator' => [
                'events.view', 'events.create', 'events.update', 'events.publish',
                'venues.view', 'venues.create', 'venues.update',
                'talent.view', 'talent.create', 'talent.update',
                'curators.view', 'curators.create', 'curators.update',
                'tickets.view', 'tickets.update',
                'orders.view',
                'media.view', 'media.create', 'media.update', 'media.delete',
                'wallet.view', 'wallet.create', 'wallet.update',
                'reports.view', 'reports.export',
            ],
            'editor' => [
                'events.view', 'events.create', 'events.update',
                'venues.view', 'venues.create', 'venues.update',
                'talent.view', 'talent.create', 'talent.update',
                'curators.view', 'curators.create', 'curators.update',
                'tickets.view', 'tickets.update',
                'orders.view',
                'media.view', 'media.create', 'media.update',
                'wallet.view', 'wallet.create', 'wallet.update',
            ],
            'boxoffice' => [
                'events.view',
                'tickets.view', 'tickets.update', 'tickets.refund', 'tickets.issue',
                'orders.view', 'orders.refund', 'orders.export',
                'media.view',
                'wallet.view', 'wallet.create',
                'reports.view', 'reports.export',
            ],
            'door' => [
                'events.view',
                'tickets.view', 'tickets.checkin',
                'orders.view',
                'wallet.view', 'wallet.update',
            ],
            'viewer' => [
                'events.view',
                'venues.view',
                'talent.view',
                'curators.view',
                'tickets.view',
                'media.view',
                'wallet.view',
            ],
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
            $ownerRole = $roleModels['owner'] ?? null;

            if ($ownerRole) {
                $firstUser = User::query()->orderBy('id')->first();

                if ($firstUser) {
                    $firstUser->systemRoles()->syncWithoutDetaching([$ownerRole->getKey()]);
                    $authorization->forgetUserPermissions($firstUser);
                }
            }
        }
    }
}
