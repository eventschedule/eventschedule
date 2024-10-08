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

        Schema::table('event_role', function (Blueprint $table) {
            $table->boolean('is_accepted')->default(false);
        });

        DB::table('event_role')->get()->each(function ($eventRole) {
            $eventRole->is_accepted = true;
            $eventRole->save();
        }); 
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

        Schema::table('event_role', function (Blueprint $table) {
            $table->dropColumn('is_accepted');
        });
    }
};
