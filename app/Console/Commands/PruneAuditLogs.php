<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use App\Services\AuditService;
use Illuminate\Console\Command;

class PruneAuditLogs extends Command
{
    /**
     * Actions kept forever. They are rare (a handful a day at most), and for each the audit row is
     * the ONLY record that the thing happened: a subscription's checkout source and every plan
     * change (subscription.*, admin.plan_update), when a selling trial started (the roles column is
     * overwritten), a placeholder schedule being claimed, a payment gateway or calendar being
     * connected or removed. The growth payload reads several of these; pruned at 90 days, its
     * history silently turned into zeros that read as "nothing happened".
     */
    public const KEEP_ACTIONS = [
        AuditService::SUBSCRIPTION_CREATE,
        AuditService::SUBSCRIPTION_SWAP,
        AuditService::SUBSCRIPTION_CANCEL,
        AuditService::SUBSCRIPTION_RESUME,
        AuditService::TICKET_TRIAL_START,
        AuditService::ADMIN_PLAN_UPDATE,
        AuditService::SCHEDULE_CLAIM,
        AuditService::STRIPE_LINK,
        AuditService::STRIPE_UNLINK,
        AuditService::GOOGLE_CALENDAR_CONNECT,
        AuditService::GOOGLE_CALENDAR_DISCONNECT,
        AuditService::MICROSOFT_CALENDAR_CONNECT,
        AuditService::MICROSOFT_CALENDAR_DISCONNECT,
    ];

    protected $signature = 'audit:prune {--days=90 : Number of days to retain}';

    protected $description = 'Prune audit log entries older than the specified number of days';

    public function handle(): int
    {
        $days = (int) $this->option('days');

        $deleted = AuditLog::where('created_at', '<', now()->subDays($days))
            ->whereNotIn('action', self::KEEP_ACTIONS)
            ->delete();

        $this->info("Pruned {$deleted} audit log entries older than {$days} days.");

        return Command::SUCCESS;
    }
}
