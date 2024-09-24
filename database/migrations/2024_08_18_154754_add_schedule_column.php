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
        Schema::table('roles', function (Blueprint $table) {
            $table->dropUnique('roles_email_unique');
        });

        Schema::table('events', function (Blueprint $table) {
            $table->string('days_of_week')->nullable();
            $table->string('event_url')->nullable();
            $table->foreignId('venue_id')->nullable()->change();
            $table->foreignId('role_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->unique('email');
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('days_of_week');
            $table->dropColumn('event_url');
        });
    }
};
