<?php

namespace App\Console\Commands;

use App\Models\Event;
use App\Models\Role;
use App\Models\RoleUser;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class CheckData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-data {check? : The specific check to run} {--fix : Attempt to fix the detected issues}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check data and optionally fix issues';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $errors = [];
        $shouldFix = $this->option('fix');
        $check = $this->argument('check');

        if (! $check || $check === 'role-ownership') {
            $this->checkRoleOwnership($errors, $shouldFix);
        }
        if (! $check || $check === 'event-slugs' || $check === 'event-subdomains') {
            $this->checkEvents($errors, $check);
        }
        if (! $check || $check === 'creator-roles') {
            $this->checkEventCreatorRoles($errors, $shouldFix);
        }
        if (! $check || $check === 'encryption') {
            $this->checkEncryption($errors);
        }
        if (! $check || $check === 'orphaned-events') {
            $this->checkOrphanedEvents($errors);
        }
        if (! $check || $check === 'unverified-roles') {
            $this->checkUnverifiedClaimedRoles($errors);
        }
        if (! $check || $check === 'role-subdomains') {
            $this->checkRoleSubdomains($errors);
        }

        if (count($errors) > 0) {
            $this->error('Errors found:');
            $this->info(implode("\n", $errors));
        } else {
            $this->info('No errors found');
        }
    }

    private function checkRoleOwnership(array &$errors, bool $shouldFix): void
    {
        $roles = Role::with('members')->where('is_deleted', false)->get();

        foreach ($roles as $role) {
            if ($role->isClaimed() && ! $role->owner()) {
                $error = 'No owner for role '.$role->id.': '.$role->name;

                if (! $shouldFix) {
                    $errors[] = $error;
                } else {
                    $this->error("Attempting to fix role {$role->id}");

                    $roleUser = RoleUser::where('role_id', $role->id)->first();

                    if ($roleUser && $roleUser->user_id == $role->user_id) {
                        $this->info('Found matching role_user: correcting...');
                        $roleUser->level = 'owner';
                        $roleUser->save();
                    } else {
                        $errors[] = $error;
                    }
                }
            }
        }
    }

    private function checkEvents(array &$errors, ?string $check): void
    {
        $events = Event::with(['venue', 'roles', 'user'])->get();

        foreach ($events as $event) {
            if ((! $check || $check === 'event-slugs') && ! $event->slug) {
                $errors[] = 'No slug for event '.$event->id.': '.$event->name.' ('.$event->user->id.': '.$event->user->name.')';
            }

            if ((! $check || $check === 'event-subdomains')) {
                $data = $event->getGuestUrlData();

                if (! $data['subdomain']) {
                    $error = 'No subdomain for event '.$event->id.': '.$event->name.' ('.$event->user->id.': '.$event->user->name.') - ';

                    foreach ($event->roles as $role) {
                        $error .= $role->name.' ('.$role->type.'), ';
                    }

                    $error = rtrim($error, ', ');

                    $errors[] = $error;
                }
            }
        }
    }

    private function checkEventCreatorRoles(array &$errors, bool $shouldFix): void
    {
        $events = Event::with('roles')->whereNotNull('creator_role_id')->get();

        foreach ($events as $event) {
            $roleIds = $event->roles->pluck('id')->toArray();

            if (! in_array($event->creator_role_id, $roleIds)) {
                $error = 'Creator role not in event roles for event '.$event->id.': '.$event->name;

                if ($shouldFix) {
                    $claimedRole = $event->roles->first(function ($role) {
                        return $role->isClaimed();
                    });

                    if ($claimedRole) {
                        $event->creator_role_id = $claimedRole->id;
                        $event->save();
                        $this->info("Fixed creator_role_id for event {$event->id} to role {$claimedRole->id}");
                    } else {
                        $errors[] = $error;
                    }
                } else {
                    $errors[] = $error;
                }
            }
        }
    }

    private function checkEncryption(array &$errors): void
    {
        $users = DB::table('users')
            ->whereNotNull('invoiceninja_api_key')
            ->orWhereNotNull('invoiceninja_webhook_secret')
            ->get(['id', 'name', 'email', 'invoiceninja_api_key', 'invoiceninja_webhook_secret']);

        foreach ($users as $user) {
            foreach (['invoiceninja_api_key', 'invoiceninja_webhook_secret'] as $field) {
                if ($user->$field === null) {
                    continue;
                }

                try {
                    Crypt::decryptString($user->$field);
                } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
                    $errors[] = "Invalid encrypted {$field} for user {$user->id}: {$user->name} ({$user->email})";
                }
            }
        }

        $roles = DB::table('roles')
            ->whereNotNull('email_settings')
            ->orWhereNotNull('caldav_settings')
            ->get(['id', 'email_settings', 'caldav_settings']);

        foreach ($roles as $role) {
            foreach (['email_settings', 'caldav_settings'] as $field) {
                if ($role->$field === null) {
                    continue;
                }

                try {
                    Crypt::decryptString($role->$field);
                } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
                    $owner = DB::table('role_user')
                        ->join('users', 'users.id', '=', 'role_user.user_id')
                        ->where('role_user.role_id', $role->id)
                        ->where('role_user.level', 'owner')
                        ->first(['users.name', 'users.email']);

                    $ownerName = $owner->name ?? 'N/A';
                    $ownerEmail = $owner->email ?? 'N/A';
                    $errors[] = "Invalid encrypted {$field} for role {$role->id} (owner: {$ownerName}, {$ownerEmail})";
                }
            }
        }
    }

    private function checkOrphanedEvents(array &$errors): void
    {
        $events = Event::doesntHave('roles')->get();

        foreach ($events as $event) {
            $errors[] = 'No roles for event '.$event->id.': '.$event->name;
        }
    }

    private function checkUnverifiedClaimedRoles(array &$errors): void
    {
        $roles = Role::whereNotNull('user_id')
            ->whereNull('email_verified_at')
            ->whereNull('phone_verified_at')
            ->get();

        foreach ($roles as $role) {
            $errors[] = 'Unverified role with owner '.$role->id.': '.$role->name;
        }
    }

    private function checkRoleSubdomains(array &$errors): void
    {
        $roles = Role::where('is_deleted', false)->where(function ($query) {
            $query->whereNull('subdomain')->orWhere('subdomain', '');
        })->get();

        foreach ($roles as $role) {
            $errors[] = 'No subdomain for role '.$role->id.': '.$role->name;
        }
    }
}
