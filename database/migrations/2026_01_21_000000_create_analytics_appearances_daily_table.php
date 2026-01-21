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
        Schema::create('analytics_appearances_daily', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained()->onDelete('cascade');        // talent/venue being tracked
            $table->foreignId('schedule_role_id')->constrained('roles')->onDelete('cascade'); // schedule where viewed
            $table->date('date');
            $table->unsignedInteger('desktop_views')->default(0);
            $table->unsignedInteger('mobile_views')->default(0);
            $table->unsignedInteger('tablet_views')->default(0);
            $table->unsignedInteger('unknown_views')->default(0);

            $table->unique(['role_id', 'schedule_role_id', 'date']);
            $table->index(['schedule_role_id', 'date']); // For curator queries
            $table->index('date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analytics_appearances_daily');
    }
};
