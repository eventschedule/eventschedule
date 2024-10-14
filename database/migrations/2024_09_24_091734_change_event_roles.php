<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Migrate existing data
        DB::table('events')->get()->each(function ($event) {
            DB::table('event_role')->insert([
                'event_id' => $event->id,
                'role_id' => $event->role_id,
            ]);
        });

        // Drop the existing foreign key and column after data migration
        Schema::table('events', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropColumn('role_id');
            $table->string('name')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->foreignId('role_id')->constrained()->onDelete('cascade');
            $table->dropColumn('name');
        });
    }
};
