<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Match Role::ownEvents(): the first attachment owns a legacy event without a creator.
        // A schedule merely listing another organizer's show gets no private directory records.
        DB::statement(<<<'SQL'
            INSERT IGNORE INTO backstage_entries (schedule_id, participant_id)
            SELECT DISTINCT owner.id, person.id
            FROM events ev
            JOIN roles owner ON owner.id = COALESCE(ev.creator_role_id, (
                SELECT first_link.role_id FROM event_role first_link
                WHERE first_link.event_id = ev.id ORDER BY first_link.id LIMIT 1
            ))
            JOIN event_role lineup ON lineup.event_id = ev.id
            JOIN roles person ON person.id = lineup.role_id
            WHERE person.type = 'talent' AND person.is_deleted = 0
              AND owner.is_deleted = 0 AND person.id <> owner.id
            SQL);
    }

    public function down(): void
    {
        // These are ordinary directory entries now, possibly holding contacts and files.
    }
};
