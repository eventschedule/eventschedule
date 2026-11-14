<?php

namespace App\Console\Commands;

use App\Services\MarketingCopyWatch;
use Illuminate\Console\Command;

/**
 * Look at a few sites that may be serving a copy of the marketing pages (MarketingCopyWatch).
 * Nexus only; both cron rails run it daily.
 */
class CheckMarketingCopies extends Command
{
    protected $signature = 'app:check-marketing-copies {--limit= : Hosts to look at, at most}';

    protected $description = 'Look for sites serving a copy of the marketing pages (nexus only)';

    public function handle(): int
    {
        if (! MarketingCopyWatch::enabled()) {
            $this->info('Not the nexus: nothing to do.');

            return self::SUCCESS;
        }

        $limit = max(1, min(100, (int) ($this->option('limit') ?: MarketingCopyWatch::PER_RUN)));
        $result = MarketingCopyWatch::run($limit);

        $this->info("Federated hosts added: {$result['added']}, looked at: {$result['checked']}, copies: {$result['copies']}, forgotten: {$result['forgotten']}.");

        return self::SUCCESS;
    }
}
