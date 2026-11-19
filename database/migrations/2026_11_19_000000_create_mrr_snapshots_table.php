<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One row a day of what the dashboard's Revenue card counted (RecurringRevenue::recordSnapshot()),
 * so the MRR chart reads the past as it was instead of rebuilding it from subscription dates at
 * today's prices.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('mrr_snapshots')) {
            return;
        }

        Schema::create('mrr_snapshots', function (Blueprint $table) {
            $table->id();
            $table->date('day')->unique();
            $table->decimal('mrr', 12, 2);
            $table->decimal('arr', 12, 2);
            $table->unsignedInteger('billing_count');
            $table->unsignedInteger('trialing_count');
            $table->decimal('trial_mrr', 12, 2);
            $table->timestamp('recorded_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mrr_snapshots');
    }
};
