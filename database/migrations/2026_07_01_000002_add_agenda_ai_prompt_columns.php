<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->string('agenda_ai_prompt', 500)->nullable();
        });

        Schema::table('events', function (Blueprint $table) {
            $table->string('agenda_ai_prompt', 500)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('agenda_ai_prompt');
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('agenda_ai_prompt');
        });
    }
};
