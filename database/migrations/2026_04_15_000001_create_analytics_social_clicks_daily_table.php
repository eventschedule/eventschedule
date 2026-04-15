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
        Schema::create('analytics_social_clicks_daily', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->string('platform', 30);
            $table->unsignedInteger('clicks')->default(0);

            $table->unique(['role_id', 'date', 'platform'], 'social_clicks_daily_unique');
            $table->index(['role_id', 'date']);
            $table->index('date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analytics_social_clicks_daily');
    }
};
