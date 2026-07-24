<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            // Links a booking Event to its appointment type. Plain index, no FK: appointment
            // types are never hard-deleted (soft delete via is_deleted), so a cascade would
            // never fire and only risks surprises on the large Event lifecycle.
            $table->unsignedBigInteger('appointment_type_id')->nullable();
            $table->index('appointment_type_id');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropIndex(['appointment_type_id']);
            $table->dropColumn('appointment_type_id');
        });
    }
};
