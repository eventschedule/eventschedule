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
        DB::statement("ALTER TABLE `roles` MODIFY `type` ENUM('venue', 'talent', 'vendor', 'curator') NOT NULL");

        Schema::create('event_role', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->foreignId('role_id')->constrained()->onDelete('cascade');
            $table->unique(['role_id', 'event_id']);
        });

        Schema::table('events', function (Blueprint $table) {
            $table->string('flyer_image_url')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("UPDATE `roles` SET type = 'talent' WHERE type = 'curator';");
        DB::statement("ALTER TABLE `roles` MODIFY `type` ENUM('venue', 'talent', 'vendor') NOT NULL");

        Schema::dropIfExists('event_role');

        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('flyer_image_url');        
        });        
    }
};
