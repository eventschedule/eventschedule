<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Payfast credentials, held per schedule OWNER alongside the other gateways' - the money reaches
     * their merchant account, not the schedule's.
     *
     * No ->after() anchors here on purpose: pinning these to a column added by a later-dated
     * migration breaks a fresh migrate in CI.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // An identifier rather than a secret, so plaintext like stripe_account_id. It also
            // arrives in every ITN, where it is compared against this value.
            $table->string('payfast_merchant_id')->nullable();

            // Both secrets, so text to hold the ciphertext the EncryptedString cast writes.
            $table->text('payfast_merchant_key')->nullable();
            $table->text('payfast_passphrase')->nullable();

            $table->boolean('payfast_sandbox')->default(false);

            // Comma list of Payfast payment-type codes (cc, ef, cp, ap, ...) the owner wants offered.
            // Empty means no restriction, which is Payfast's own default.
            $table->string('payfast_payment_types')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'payfast_merchant_id',
                'payfast_merchant_key',
                'payfast_passphrase',
                'payfast_sandbox',
                'payfast_payment_types',
            ]);
        });
    }
};
