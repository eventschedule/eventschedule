<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * PayPal credentials, held per schedule OWNER alongside the other gateways' - the money reaches
     * their PayPal account, not the schedule's.
     *
     * No ->after() anchors here on purpose: pinning these to a column added by a later-dated
     * migration breaks a fresh migrate in CI.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Public by design - it ships inside PayPal's own JS SDK - so plaintext, like
            // payfast_merchant_id and stripe_account_id.
            $table->string('paypal_client_id')->nullable();

            // The one secret, so text to hold the ciphertext the EncryptedString cast writes.
            $table->text('paypal_client_secret')->nullable();

            $table->boolean('paypal_sandbox')->default(false);

            // Returned by PayPal when we register the webhook listener, and required as an input to
            // the verify-webhook-signature call. An identifier, not a secret, so plaintext and not
            // redacted from the audit log.
            //
            // Deliberately NOT declared in PayPalGateway::credentialFields(): the driver registers
            // and stores it, an owner never types it, and a "Webhook ID" input on the settings tab
            // would be jargon nobody can act on. The consequence to remember is that
            // credentialsFor() builds its array from credentialFields(), so this column is absent
            // from it and the webhook path reads it off the owner directly.
            $table->string('paypal_webhook_id')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'paypal_client_id',
                'paypal_client_secret',
                'paypal_sandbox',
                'paypal_webhook_id',
            ]);
        });
    }
};
