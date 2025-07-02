<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add group_id to event_role if it doesn't exist
        if (!Schema::hasColumn('event_role', 'group_id')) {
            Schema::table('event_role', function (Blueprint $table) {
                
                $table->foreignId('group_id')->nullable()->constrained()->onDelete('set null');
            });
        }

        // Remove group_id from events table
        if (Schema::hasColumn('events', 'group_id')) {
            Schema::table('events', function (Blueprint $table) {
                $table->dropForeign(['group_id']);
                $table->dropColumn('group_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add group_id column back to events table if not exists
        if (!Schema::hasColumn('events', 'group_id')) {
            Schema::table('events', function (Blueprint $table) {
                $table->foreignId('group_id')->nullable()->constrained()->onDelete('set null');
            });
        }
        // Remove group_id from event_role if exists
        if (Schema::hasColumn('event_role', 'group_id')) {
            Schema::table('event_role', function (Blueprint $table) {
                $table->dropForeign(['group_id']);
                $table->dropColumn('group_id');
            });
        }
    }
}; 