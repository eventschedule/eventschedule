<?php

namespace App\Console\Commands;

use App\Models\WebhookDelivery;
use Illuminate\Console\Command;

class CleanupWebhookDeliveries extends Command
{
    protected $signature = 'app:cleanup-webhook-deliveries {--days=30 : Number of days to retain}';

    protected $description = 'Delete webhook delivery logs older than the specified number of days';

    public function handle(): int
    {
        $days = (int) $this->option('days');

        if ($days < 1) {
            $this->error('Days must be at least 1.');

            return Command::FAILURE;
        }

        $deleted = WebhookDelivery::where('created_at', '<', now()->subDays($days))->delete();

        $this->info("Deleted {$deleted} webhook delivery logs older than {$days} days.");

        return Command::SUCCESS;
    }
}
