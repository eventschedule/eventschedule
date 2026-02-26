<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Services\DigitalOceanService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SyncDomainStatuses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-domain-statuses';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync custom domain statuses from DigitalOcean';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $doService = app(DigitalOceanService::class);
        if (! $doService->isConfigured()) {
            $this->info('DigitalOcean service not configured, skipping.');

            return;
        }

        $roles = Role::where('custom_domain_mode', 'direct')
            ->whereNotNull('custom_domain_host')
            ->where(function ($query) {
                $query->where('custom_domain_status', 'pending')
                    ->orWhereNull('custom_domain_status');
            })
            ->get();

        if ($roles->isEmpty()) {
            $this->info('No pending domains to sync.');

            return;
        }

        try {
            $doStatuses = $doService->getAppDomains();
        } catch (\Exception $e) {
            Log::error('Failed to fetch DO domain statuses for sync', ['error' => $e->getMessage()]);
            $this->error('Failed to fetch domain statuses from DigitalOcean.');

            return;
        }

        if (empty($doStatuses)) {
            $this->warn('No domains returned from DigitalOcean, skipping to avoid false failures.');

            return;
        }

        $updated = 0;
        $failed = 0;

        foreach ($roles as $role) {
            $doStatus = $doStatuses[$role->custom_domain_host] ?? null;

            if ($doStatus && $doStatus['phase'] === 'ACTIVE') {
                $role->update(['custom_domain_status' => 'active']);
                Cache::forget("custom_domain:{$role->custom_domain_host}");
                $updated++;
                $this->info("Activated: {$role->custom_domain_host}");
            } elseif (! $doStatus) {
                $role->update(['custom_domain_status' => 'failed']);
                $failed++;
                $this->warn("Failed (not found in DO): {$role->custom_domain_host}");
            }
        }

        $this->info("Sync complete. {$updated} domain(s) activated, {$failed} domain(s) failed.");
    }
}
