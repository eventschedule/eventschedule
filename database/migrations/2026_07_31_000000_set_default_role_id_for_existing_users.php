<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('
            UPDATE users
            SET default_role_id = (
                SELECT role_id
                FROM role_user
                WHERE role_user.user_id = users.id
                  AND role_user.level = "owner"
                ORDER BY role_user.id ASC
                LIMIT 1
            )
            WHERE default_role_id IS NULL
              AND EXISTS (
                SELECT 1 FROM role_user
                WHERE role_user.user_id = users.id
                  AND role_user.level = "owner"
              )
        ');
    }

    public function down(): void
    {
        // No safe way to distinguish backfilled values from intentionally set ones
    }
};
