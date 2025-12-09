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
            $table->enum('recurring_end_type', ['never', 'on_date', 'after_events'])->default('never')->after('days_of_week');
            $table->string('recurring_end_value', 255)->nullable()->after('recurring_end_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['recurring_end_type', 'recurring_end_value']);
        });
    }
};
