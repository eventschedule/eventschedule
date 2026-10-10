<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Razorpay credentials, per schedule OWNER like every other gateway's.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Public by design (it ships in Razorpay's own checkout.js), so plaintext.
            $table->string('razorpay_key_id')->nullable();

            // Secrets, so text to hold the ciphertext the EncryptedString cast writes.
            $table->text('razorpay_key_secret')->nullable();
            $table->text('razorpay_webhook_secret')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['razorpay_key_id', 'razorpay_key_secret', 'razorpay_webhook_secret']);
        });
    }
};
