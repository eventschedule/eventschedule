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
        Schema::create('venue_rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venue_id')->constrained('roles')->onDelete('cascade');
            $table->string('name');
            $table->text('details')->nullable();
            $table->timestamps();
        });

        Schema::table('event_role', function (Blueprint $table) {
            $table->foreignId('room_id')->nullable()->after('group_id')->constrained('venue_rooms')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_role', function (Blueprint $table) {
            if (Schema::hasColumn('event_role', 'room_id')) {
                $table->dropConstrainedForeignId('room_id');
            }
        });

        Schema::dropIfExists('venue_rooms');
    }
};
