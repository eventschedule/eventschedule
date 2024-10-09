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
        Schema::table('events', function (Blueprint $table) {
            $table->string('event_password')->nullable();
            $table->foreignId('venue_id')->nullable()->change();
        });

        Schema::table('roles', function (Blueprint $table) {
            $table->boolean('is_open')->default(true)->change();
        });

        DB::table('roles')->update(['is_open' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('event_password');
            $table->foreignId('venue_id')->nullable(false)->change();
        });
    }
};
