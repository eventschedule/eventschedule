<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Role;
class CheckData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-data {--fix : Attempt to fix the detected issues}';

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

        $roles = Role::with('members')->get();

        foreach ($roles as $role) {
            if ($role->isRegistered() && ! $role->owner()) {
                $errors[] = 'No owner for role ' . $role->id . ': ' . $role->name;
                
                if ($shouldFix) {
                    // Add your fix logic here
                    $this->info("Attempting to fix role {$role->id}");
                }
            }
        }

        if (count($errors) > 0) {
            $this->error("Errors:\n" . implode("\n", $errors));
        } else {
            $this->info('No errors found');
        }
    }
}
