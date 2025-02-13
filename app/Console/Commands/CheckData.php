<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Role;
use App\Models\RoleUser;
use App\Models\Event;
class CheckData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-data {--fix=false : Attempt to fix the detected issues}';

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
        $shouldFix = $this->option('fix') == 'true';

        $roles = Role::with('members')->where('is_deleted', false)->get();

        foreach ($roles as $role) {
            if ($role->isRegistered() && ! $role->owner()) {
                $error = 'No owner for role ' . $role->id . ': ' . $role->name;
                
                if (! $shouldFix) {
                    $errors[] = $error;
                } else {
                    $this->error("Attempting to fix role {$role->id}");

                    $roleUser = RoleUser::where('role_id', $role->id)->first();

                    if ($roleUser->user_id == $role->user_id) {
                        $this->info('Found matching role_user: correcting...');
                        $roleUser->level = 'owner';
                        $roleUser->save();
                    } else {
                        $errors[] = $error;
                    }
                }
            }
        }

        $events = Event::with(['venue', 'roles'])->get();

        foreach ($events as $event) {
            if (! $event->venue && ! $event->event_url) {
                $errors[] = 'No venue or event_url for event ' . $event->id . ': ' . $event->name;
            }
        }

        if (count($errors) > 0) {
            $this->error("Errors:\n" . implode("\n", $errors));
        } else {
            $this->info('No errors found');
        }
    }
}
