<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            // Set once an appointment booking reaches CONFIRMED, so AppointmentService::confirm()
            // is idempotent across the free path, the Stripe webhook, mark-paid, and approval.
            $table->timestamp('confirmed_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn('confirmed_at');
        });
    }
};
