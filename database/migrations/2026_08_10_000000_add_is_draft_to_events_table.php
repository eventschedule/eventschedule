<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->boolean('is_draft')->default(false)->after('is_private');
        });

        Schema::table('roles', function (Blueprint $table) {
            $table->boolean('draft_events_default')->default(false)->after('hide_past_events');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('is_draft');
        });

        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('draft_events_default');
        });
    }
};
