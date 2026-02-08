<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use Illuminate\Console\Command;

class PruneAuditLogs extends Command
{
    protected $signature = 'audit:prune {--days=90 : Number of days to retain}';

    protected $description = 'Prune audit log entries older than the specified number of days';

    public function handle(): int
    {
        $days = (int) $this->option('days');

        $deleted = AuditLog::where('created_at', '<', now()->subDays($days))->delete();

        $this->info("Pruned {$deleted} audit log entries older than {$days} days.");

        return Command::SUCCESS;
    }
}
