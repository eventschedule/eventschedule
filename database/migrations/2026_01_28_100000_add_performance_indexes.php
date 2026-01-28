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
        Schema::table('event_role', function (Blueprint $table) {
            $table->index('event_id');
        });

        Schema::table('events', function (Blueprint $table) {
            $table->index('starts_at');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_role', function (Blueprint $table) {
            $table->dropIndex(['event_id']);
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropIndex(['starts_at']);
            $table->dropIndex(['created_at']);
        });
    }
};
