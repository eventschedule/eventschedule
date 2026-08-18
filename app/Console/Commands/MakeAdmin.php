<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\AuditService;
use Illuminate\Console\Command;

/**
 * Grant instance admin, or list who already has it.
 *
 * users.is_admin is only ever set in three places - migration 2026_01_21_000000 (user id 1),
 * migration 2026_07_01_000008 (lowest id, non-hosted only) and RegisteredUserController when
 * the very first account signs up. There is no screen for it, so an operator who is not that
 * first user has no way in to /admin at all, and the docs told them to write raw SQL.
 */
class MakeAdmin extends Command
{
    protected $signature = 'app:make-admin {email? : The email address of the user to promote}';

    protected $description = 'Grant instance admin access to a user, or list the current admins';

    public function handle(): int
    {
        // Deliberately NOT gated on nexus or hosted, unlike AppController::update(). This needs
        // shell access on the server, which already outranks anything the flag grants - the same
        // operator can do it in `php artisan tinker`. A gate here would only lock out the
        // legitimate operator of an install where nobody is an admin yet, which is the whole
        // reason the command exists.
        $email = $this->argument('email');

        if (! $email) {
            return $this->listAdmins();
        }

        $user = User::where('email', $email)->first();

        if (! $user) {
            $this->error('No user found with the email '.$email);

            return self::FAILURE;
        }

        if ($user->isAdmin()) {
            $this->info($user->email.' is already an instance admin.');

            return self::SUCCESS;
        }

        // is_admin is $guarded and absent from $fillable, so it can only be set by direct
        // property assignment - the same way RegisteredUserController does it.
        $user->is_admin = true;
        $user->save();

        AuditService::log(
            AuditService::ADMIN_GRANT,
            $user->id,
            User::class,
            $user->id,
            ['is_admin' => false],
            ['is_admin' => true],
            json_encode(['source' => 'console'])
        );

        $this->info($user->email.' is now an instance admin.');

        if (! $user->password) {
            $this->warn('This account has no password (it signed in with Google or Facebook). The admin panel asks for a password before it opens, so set one under Settings first.');
        }

        return self::SUCCESS;
    }

    protected function listAdmins(): int
    {
        $admins = User::where('is_admin', true)->orderBy('id')->get(['id', 'name', 'email']);

        if ($admins->isEmpty()) {
            $this->warn('No user is an instance admin. Run app:make-admin {email} to grant it.');

            return self::SUCCESS;
        }

        $this->info('Current instance admins:');
        $this->table(['ID', 'Name', 'Email'], $admins->map(fn ($user) => [$user->id, $user->name, $user->email])->all());

        return self::SUCCESS;
    }
}
