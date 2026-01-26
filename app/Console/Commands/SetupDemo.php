<?php

namespace App\Console\Commands;

use App\Services\DemoService;
use Illuminate\Console\Command;

class SetupDemo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:setup-demo';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set up the demo account with sample data';

    /**
     * Execute the console command.
     */
    public function handle(DemoService $demoService)
    {
        // Check if demo mode is allowed
        if (! config('app.hosted') && ! config('app.is_testing')) {
            $this->error('Demo mode is only available in hosted or testing environments.');

            return 1;
        }

        $this->info('Setting up demo account...');

        // Get or create demo user
        $user = $demoService->getOrCreateDemoUser();
        $this->info('Demo user ready: '.$user->email);

        // Get or create demo role
        $role = $demoService->getOrCreateDemoRole($user);
        $this->info('Demo role ready: '.$role->subdomain);

        // Populate or reset demo data
        if ($role->events()->count() === 0) {
            $this->info('Populating demo data...');
            $demoService->populateDemoData($role);
            $this->info('Demo data has been populated.');
        } else {
            $this->info('Resetting existing demo data...');
            $demoService->resetDemoData($role);
            $this->info('Demo data has been reset.');
        }

        $this->info('Demo setup complete!');
        $this->info('Demo URL: '.route('role.view_guest', ['subdomain' => DemoService::DEMO_SUBDOMAIN]));

        return 0;
    }
}
