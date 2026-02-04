<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('recurring_frequency')->nullable()->after('days_of_week');
            $table->integer('recurring_interval')->nullable()->after('recurring_frequency');
        });

        // Set existing recurring events to 'weekly'
        DB::table('events')
            ->whereNotNull('days_of_week')
            ->update(['recurring_frequency' => 'weekly']);
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['recurring_frequency', 'recurring_interval']);
        });
    }
};
