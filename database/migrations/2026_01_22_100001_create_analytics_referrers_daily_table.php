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
        Schema::create('analytics_referrers_daily', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->string('source', 50);  // 'direct', 'search', 'social', 'email', 'other'
            $table->string('domain', 255)->nullable();  // e.g., 'facebook.com', 'google.com'
            $table->unsignedInteger('views')->default(0);

            $table->unique(['role_id', 'date', 'source', 'domain'], 'referrers_daily_unique');
            $table->index(['role_id', 'date']);
            $table->index('date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analytics_referrers_daily');
    }
};
