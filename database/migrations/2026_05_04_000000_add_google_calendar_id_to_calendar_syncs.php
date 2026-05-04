<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('calendar_syncs', function (Blueprint $table) {
            $table->string('google_calendar_id')->nullable()->after('google_event_id');
        });
    }

    public function down(): void
    {
        Schema::table('calendar_syncs', function (Blueprint $table) {
            $table->dropColumn('google_calendar_id');
        });
    }
};
