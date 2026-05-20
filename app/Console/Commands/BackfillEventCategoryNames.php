<?php

namespace App\Console\Commands;

use App\Models\Event;
use App\Models\Role;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackfillEventCategoryNames extends Command
{
    protected $signature = 'app:backfill-event-category-names {--dry-run : Show what would be changed without making changes} {--chunk=500}';

    protected $description = 'Populate events.category_name from the creator schedule (or system defaults) for rows where it is null.';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $chunkSize = max(50, (int) $this->option('chunk'));

        $systemDefaults = config('app.event_categories', []);
        $roleCache = [];
        $updated = 0;
        $skipped = 0;

        Event::query()
            ->whereNotNull('category_id')
            ->whereNull('category_name')
            ->select(['id', 'category_id', 'creator_role_id', 'category_name'])
            ->chunkById($chunkSize, function ($events) use (&$updated, &$skipped, &$roleCache, $systemDefaults, $dryRun) {
                foreach ($events as $event) {
                    $name = null;

                    if ($event->creator_role_id) {
                        if (! array_key_exists($event->creator_role_id, $roleCache)) {
                            $roleCache[$event->creator_role_id] = Role::find($event->creator_role_id);
                        }
                        $role = $roleCache[$event->creator_role_id];
                        if ($role) {
                            $name = $role->getCategoryName((int) $event->category_id);
                        }
                    }

                    if (! $name) {
                        $name = $systemDefaults[$event->category_id] ?? null;
                    }

                    if (! $name) {
                        $skipped++;

                        continue;
                    }

                    if ($dryRun) {
                        $this->line("  Would set event {$event->id} category_name = {$name}");
                        $updated++;

                        continue;
                    }

                    DB::table('events')->where('id', $event->id)->update(['category_name' => $name]);
                    $updated++;
                }
            });

        $this->info("Updated: {$updated}, skipped (no resolvable name): {$skipped}");

        return self::SUCCESS;
    }
}
