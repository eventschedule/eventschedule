<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            // Idempotency marker for the appointment reminder command (mirrors feedback_sent_at).
            $table->timestamp('reminder_sent_at')->nullable();
            // IANA timezone the guest picked their slot in, for "{local time} your time" email lines.
            $table->string('guest_timezone', 64)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['reminder_sent_at', 'guest_timezone']);
        });
    }
};
