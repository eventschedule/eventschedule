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
        // Create schedule-level analytics table
        Schema::create('analytics_daily', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->unsignedInteger('desktop_views')->default(0);
            $table->unsignedInteger('mobile_views')->default(0);
            $table->unsignedInteger('tablet_views')->default(0);
            $table->unsignedInteger('unknown_views')->default(0);

            $table->unique(['role_id', 'date']);
            $table->index('date');
        });

        // Create event-level analytics table
        Schema::create('analytics_events_daily', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->unsignedInteger('desktop_views')->default(0);
            $table->unsignedInteger('mobile_views')->default(0);
            $table->unsignedInteger('tablet_views')->default(0);
            $table->unsignedInteger('unknown_views')->default(0);

            $table->unique(['event_id', 'date']);
            $table->index('date');
        });

        // Drop the old page_views table
        Schema::dropIfExists('page_views');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate page_views table
        Schema::create('page_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained()->onDelete('cascade');
            $table->foreignId('event_id')->nullable()->constrained()->onDelete('cascade');
            $table->timestamp('viewed_at');
            $table->string('ip_hash', 64)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->enum('device_type', ['desktop', 'mobile', 'tablet', 'unknown'])->default('unknown');

            $table->index(['role_id', 'viewed_at']);
            $table->index(['event_id', 'viewed_at']);
            $table->index('viewed_at');
        });

        // Drop the new tables
        Schema::dropIfExists('analytics_events_daily');
        Schema::dropIfExists('analytics_daily');
    }
};
