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
    protected $signature = 'app:check-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $errors = [];

        $roles = Role::with('members')->get();

        foreach ($roles as $role) {
            if (true || $role->isRegistered() && ! $role->owner()) {
                $errors[] = 'No owner for role ' . $role->id . ': ' . $role->name;
            }
        }

        if (count($errors) > 0) {
            $this->error("Errors:\n" . implode("\n", $errors));
        } else {
            $this->info('No errors found');
        }
    }
}
