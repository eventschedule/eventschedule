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
            $table->foreignId('venue_id')->nullable(false)->change();
            $table->foreignId('role_id')->nullable(false)->change();
        });        

        Schema::table('roles', function (Blueprint $table) {
            $table->string('name')->nullable()->change();
            $table->string('email')->nullable()->change();
            $table->boolean('is_open')->default(false);            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->string('email')->nullable(false)->change();
            $table->string('name')->nullable(false)->change();
            $table->dropColumn('is_open');
        });

        Schema::table('events', function (Blueprint $table) {
            $table->foreignId('venue_id')->nullable()->change();
            $table->foreignId('role_id')->nullable()->change();
        });
    }
};
