<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('agenda_image_url')->nullable()->after('agenda_ai_prompt');
        });

        Schema::table('roles', function (Blueprint $table) {
            $table->boolean('agenda_save_image')->nullable()->after('agenda_show_description');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('agenda_image_url');
        });

        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('agenda_save_image');
        });
    }
};
