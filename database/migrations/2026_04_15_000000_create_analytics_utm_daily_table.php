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
        Schema::create('analytics_utm_daily', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->string('param_type', 20);   // 'source', 'medium', 'campaign', 'content', 'term'
            $table->string('param_value', 255);
            $table->unsignedInteger('views')->default(0);

            $table->unique(['role_id', 'date', 'param_type', 'param_value'], 'utm_daily_unique');
            $table->index(['role_id', 'date']);
            $table->index('date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analytics_utm_daily');
    }
};
