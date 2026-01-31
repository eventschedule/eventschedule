<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class CheckEncryption extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-encryption';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for invalid encrypted data in the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $errors = [];

        $this->checkUserFields($errors);
        $this->checkRoleFields($errors);

        if (count($errors) > 0) {
            $this->table(
                ['Model', 'Field', 'Record ID', 'User Name', 'User Email'],
                $errors
            );
        } else {
            $this->info('No invalid encrypted data found.');
        }
    }

    private function checkUserFields(array &$errors): void
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
                    $errors[] = ['User', $field, $user->id, $user->name, $user->email];
                }
            }
        }
    }

    private function checkRoleFields(array &$errors): void
    {
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
                    $owner = $this->getRoleOwner($role->id);
                    $errors[] = [
                        'Role',
                        $field,
                        $role->id,
                        $owner->name ?? 'N/A',
                        $owner->email ?? 'N/A',
                    ];
                }
            }
        }
    }

    private function getRoleOwner(int $roleId): ?object
    {
        return DB::table('role_user')
            ->join('users', 'users.id', '=', 'role_user.user_id')
            ->where('role_user.role_id', $roleId)
            ->where('role_user.level', 'owner')
            ->first(['users.name', 'users.email']);
    }
}
