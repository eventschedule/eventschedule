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
        Schema::table('analytics_events_daily', function (Blueprint $table) {
            $table->unsignedInteger('sales_count')->default(0);
            $table->decimal('revenue', 13, 3)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('analytics_events_daily', function (Blueprint $table) {
            $table->dropColumn(['sales_count', 'revenue']);
        });
    }
};
