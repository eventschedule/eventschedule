<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Services\CuratorSourceService;
use App\Utils\UrlUtils;
use Illuminate\Console\Command;

class SyncCuratorSources extends Command
{
    protected $signature = 'app:sync-curator-sources {--role_id= : Reconcile a single curator by id}';

    protected $description = 'Link every event published by a curator\'s source schedules onto the curator, and unlink the ones that no longer qualify';

    public function handle(CuratorSourceService $sources): int
    {
        $roleId = $this->option('role_id');

        if ($roleId && ! is_numeric($roleId)) {
            $roleId = UrlUtils::decodeId($roleId);
        }

        $curator = null;

        if ($roleId) {
            $curator = Role::find($roleId);

            if (! $curator) {
                $this->error("No schedule with id {$roleId}.");

                return self::FAILURE;
            }
        }

        $result = $sources->reconcile($curator);

        // Silent when there was nothing to do: this runs every five minutes and would
        // otherwise fill the scheduler log.
        if ($result['added'] || $result['removed']) {
            $this->info("Linked {$result['added']} event(s), unlinked {$result['removed']}.");
        }

        return self::SUCCESS;
    }
}
