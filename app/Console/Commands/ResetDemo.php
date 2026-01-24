<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Services\DemoService;
use Illuminate\Console\Command;

class ResetDemo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:reset-demo';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset the demo account data (used by hourly scheduler)';

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

        // Find demo role
        $role = Role::where('subdomain', DemoService::DEMO_SUBDOMAIN)->first();

        if (! $role) {
            $this->error('Demo role not found. Run app:setup-demo first.');

            return 1;
        }

        $this->info('Resetting demo data...');
        $demoService->resetDemoData($role);
        $this->info('Demo data has been reset.');

        return 0;
    }
}
