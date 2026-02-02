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
        Schema::table('sales', function (Blueprint $table) {
            $table->index(['event_id', 'secret']);
            $table->index('status');
            $table->index(['payment_method', 'transaction_reference']);
            $table->index('event_date');
        });

        Schema::table('event_role', function (Blueprint $table) {
            $table->index('google_event_id');
        });

        Schema::table('newsletters', function (Blueprint $table) {
            $table->index('sent_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropIndex(['event_id', 'secret']);
            $table->dropIndex(['status']);
            $table->dropIndex(['payment_method', 'transaction_reference']);
            $table->dropIndex(['event_date']);
        });

        Schema::table('event_role', function (Blueprint $table) {
            $table->dropIndex(['google_event_id']);
        });

        Schema::table('newsletters', function (Blueprint $table) {
            $table->dropIndex(['sent_at']);
        });
    }
};
