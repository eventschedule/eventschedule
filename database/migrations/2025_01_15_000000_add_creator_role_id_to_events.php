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
        // Add creator_role_id column
        Schema::table('events', function (Blueprint $table) {
            $table->foreignId('creator_role_id')->nullable()->constrained('roles')->onDelete('cascade');
        });

        // Migrate existing data: set creator_role_id based on the first role_id for the user who created the event
        DB::table('events')->chunkById(100, function ($events) {
            foreach ($events as $event) {
                // Get the first role_id for the user who created the event
                $firstRole = DB::table('role_user')
                    ->where('user_id', $event->user_id)
                    ->orderBy('id')
                    ->first();

                if ($firstRole) {
                    DB::table('events')
                        ->where('id', $event->id)
                        ->update(['creator_role_id' => $firstRole->role_id]);
                }
            }
        });

        // Remove curator_id column
        Schema::table('events', function (Blueprint $table) {
            $table->dropForeign(['curator_id']);
            $table->dropColumn('curator_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add curator_id column back
        Schema::table('events', function (Blueprint $table) {
            $table->foreignId('curator_id')->nullable()->constrained('roles')->onDelete('cascade');
        });

        // Migrate data back: set curator_id based on creator_role_id if it's a curator
        DB::table('events')->chunkById(100, function ($events) {
            foreach ($events as $event) {
                if ($event->creator_role_id) {
                    $creatorRole = DB::table('roles')
                        ->where('id', $event->creator_role_id)
                        ->where('type', 'curator')
                        ->first();

                    if ($creatorRole) {
                        DB::table('events')
                            ->where('id', $event->id)
                            ->update(['curator_id' => $event->creator_role_id]);
                    }
                }
            }
        });

        // Remove creator_role_id column
        Schema::table('events', function (Blueprint $table) {
            $table->dropForeign(['creator_role_id']);
            $table->dropColumn('creator_role_id');
        });
    }
}; 