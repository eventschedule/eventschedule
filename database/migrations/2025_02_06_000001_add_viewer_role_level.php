<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE `role_user` MODIFY `level` ENUM('owner','admin','viewer','follower') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE `role_user` MODIFY `level` ENUM('owner','admin','follower') NOT NULL");
    }
};
