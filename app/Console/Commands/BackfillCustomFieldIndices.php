<?php

namespace App\Console\Commands;

use App\Models\Event;
use App\Models\Role;
use App\Models\Ticket;
use Illuminate\Console\Command;

class BackfillCustomFieldIndices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:backfill-custom-field-indices {--dry-run : Show what would be changed without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Assign stable indices to existing custom fields on Roles, Events, and Tickets';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info('Running in dry-run mode - no changes will be made.');
        }

        $this->backfillRoleCustomFields($dryRun);
        $this->backfillEventCustomFields($dryRun);
        $this->backfillTicketCustomFields($dryRun);

        $this->info('Done!');

        return 0;
    }

    /**
     * Backfill indices for Role event_custom_fields.
     */
    private function backfillRoleCustomFields(bool $dryRun): void
    {
        $this->info('Processing Roles with event_custom_fields...');

        $roles = Role::whereNotNull('event_custom_fields')->get();
        $updated = 0;

        foreach ($roles as $role) {
            $fields = $role->event_custom_fields;
            if (empty($fields)) {
                continue;
            }

            $modified = false;
            $usedIndices = [];

            // First pass: collect existing indices
            foreach ($fields as $fieldKey => $fieldConfig) {
                if (isset($fieldConfig['index']) && $fieldConfig['index'] >= 1 && $fieldConfig['index'] <= 8) {
                    $usedIndices[] = $fieldConfig['index'];
                }
            }

            // Second pass: assign indices to fields without one
            $nextIndex = 1;
            foreach ($fields as $fieldKey => $fieldConfig) {
                if (! isset($fieldConfig['index']) || $fieldConfig['index'] < 1 || $fieldConfig['index'] > 8) {
                    // Find next available index
                    while (in_array($nextIndex, $usedIndices) && $nextIndex <= 8) {
                        $nextIndex++;
                    }
                    if ($nextIndex <= 8) {
                        $fields[$fieldKey]['index'] = $nextIndex;
                        $usedIndices[] = $nextIndex;
                        $nextIndex++;
                        $modified = true;
                    }
                }
            }

            if ($modified) {
                $updated++;
                if ($dryRun) {
                    $this->line("  Would update Role {$role->id} ({$role->subdomain})");
                } else {
                    $role->event_custom_fields = $fields;
                    $role->save();
                    $this->line("  Updated Role {$role->id} ({$role->subdomain})");
                }
            }
        }

        $this->info("  Roles updated: {$updated}");
    }

    /**
     * Backfill indices for Event custom_fields.
     */
    private function backfillEventCustomFields(bool $dryRun): void
    {
        $this->info('Processing Events with custom_fields...');

        $events = Event::whereNotNull('custom_fields')->get();
        $updated = 0;

        foreach ($events as $event) {
            $fields = $event->custom_fields;
            if (empty($fields)) {
                continue;
            }

            $modified = false;
            $usedIndices = [];

            // First pass: collect existing indices
            foreach ($fields as $fieldKey => $fieldConfig) {
                if (isset($fieldConfig['index']) && $fieldConfig['index'] >= 1 && $fieldConfig['index'] <= 8) {
                    $usedIndices[] = $fieldConfig['index'];
                }
            }

            // Second pass: assign indices to fields without one
            $nextIndex = 1;
            foreach ($fields as $fieldKey => $fieldConfig) {
                if (! isset($fieldConfig['index']) || $fieldConfig['index'] < 1 || $fieldConfig['index'] > 8) {
                    // Find next available index
                    while (in_array($nextIndex, $usedIndices) && $nextIndex <= 8) {
                        $nextIndex++;
                    }
                    if ($nextIndex <= 8) {
                        $fields[$fieldKey]['index'] = $nextIndex;
                        $usedIndices[] = $nextIndex;
                        $nextIndex++;
                        $modified = true;
                    }
                }
            }

            if ($modified) {
                $updated++;
                if ($dryRun) {
                    $this->line("  Would update Event {$event->id}");
                } else {
                    $event->custom_fields = $fields;
                    $event->save();
                    $this->line("  Updated Event {$event->id}");
                }
            }
        }

        $this->info("  Events updated: {$updated}");
    }

    /**
     * Backfill indices for Ticket custom_fields.
     */
    private function backfillTicketCustomFields(bool $dryRun): void
    {
        $this->info('Processing Tickets with custom_fields...');

        $tickets = Ticket::whereNotNull('custom_fields')->get();
        $updated = 0;

        foreach ($tickets as $ticket) {
            $fields = $ticket->custom_fields;
            if (empty($fields)) {
                continue;
            }

            $modified = false;
            $usedIndices = [];

            // First pass: collect existing indices
            foreach ($fields as $fieldKey => $fieldConfig) {
                if (isset($fieldConfig['index']) && $fieldConfig['index'] >= 1 && $fieldConfig['index'] <= 8) {
                    $usedIndices[] = $fieldConfig['index'];
                }
            }

            // Second pass: assign indices to fields without one
            $nextIndex = 1;
            foreach ($fields as $fieldKey => $fieldConfig) {
                if (! isset($fieldConfig['index']) || $fieldConfig['index'] < 1 || $fieldConfig['index'] > 8) {
                    // Find next available index
                    while (in_array($nextIndex, $usedIndices) && $nextIndex <= 8) {
                        $nextIndex++;
                    }
                    if ($nextIndex <= 8) {
                        $fields[$fieldKey]['index'] = $nextIndex;
                        $usedIndices[] = $nextIndex;
                        $nextIndex++;
                        $modified = true;
                    }
                }
            }

            if ($modified) {
                $updated++;
                if ($dryRun) {
                    $this->line("  Would update Ticket {$ticket->id}");
                } else {
                    $ticket->custom_fields = $fields;
                    $ticket->save();
                    $this->line("  Updated Ticket {$ticket->id}");
                }
            }
        }

        $this->info("  Tickets updated: {$updated}");
    }
}
