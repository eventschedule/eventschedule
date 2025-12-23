<?php

namespace App\Console\Commands;

use App\Models\Role;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ExtendYearlyPlans extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:extend-plans';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Extend plan_expires by one year for roles with plan_term=year where plan_expires is within a year';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting yearly plan extension...');

        $now = Carbon::now();
        $oneYearFromNow = $now->copy()->addYear();

        // Find roles where:
        // - plan_term is 'year'
        // - plan_expires is not null
        // - plan_expires is >= today (not expired)
        // - plan_expires is <= today + 1 year (expires within a year)
        $roles = Role::where('plan_term', 'year')
                    ->whereNotNull('plan_expires')
                    ->where('plan_expires', '>=', $now->format('Y-m-d'))
                    ->where('plan_expires', '<=', $oneYearFromNow->format('Y-m-d'))
                    ->get();

        if ($roles->isEmpty()) {
            $this->info('No roles found that need plan extension.');
            return 0;
        }

        $this->info("Found {$roles->count()} role(s) to extend.");

        $extended = 0;
        $errors = 0;

        foreach ($roles as $role) {
            $this->info("Processing role: {$role->subdomain} (ID: {$role->id})");
            $this->info("  - Current plan_expires: {$role->plan_expires}");

            try {
                // Parse the current expiration date and add one year
                $currentExpires = Carbon::parse($role->plan_expires);
                $newExpires = $currentExpires->copy()->addYear();

                // Update the role
                $role->plan_expires = $newExpires->format('Y-m-d');
                $role->save();

                $this->info("  - ✅ Plan extended successfully. New plan_expires: {$role->plan_expires}");
                $extended++;

            } catch (\Exception $e) {
                $this->error("  - ❌ Failed to extend plan for role {$role->subdomain}: {$e->getMessage()}");
                Log::error('Failed to extend yearly plan', [
                    'role_id' => $role->id,
                    'role_subdomain' => $role->subdomain,
                    'error' => $e->getMessage(),
                ]);
                $errors++;
            }
        }

        $this->info("\nPlan extension completed:");
        $this->info("  - Extended: {$extended}");
        $this->info("  - Errors: {$errors}");

        if ($errors > 0) {
            return 1;
        }

        return 0;
    }
}


