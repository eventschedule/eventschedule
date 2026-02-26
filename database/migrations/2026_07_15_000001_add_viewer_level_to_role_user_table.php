<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE role_user MODIFY COLUMN level ENUM('owner', 'admin', 'viewer', 'follower') NOT NULL DEFAULT 'follower'");
    }

    public function down(): void
    {
        // Convert any viewer rows to admin before reverting enum
        DB::table('role_user')->where('level', 'viewer')->update(['level' => 'admin']);
        DB::statement("ALTER TABLE role_user MODIFY COLUMN level ENUM('owner', 'admin', 'follower') NOT NULL DEFAULT 'follower'");
    }
};
