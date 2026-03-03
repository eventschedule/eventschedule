<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->boolean('fan_content_enabled')->default(true);
        });

        Schema::table('events', function (Blueprint $table) {
            $table->boolean('fan_content_enabled')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('fan_content_enabled');
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('fan_content_enabled');
        });
    }
};
