<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Nexus-side state for the welcome email an operator gets once their install is approved.
 *
 * welcomed_at: set when the welcome is QUEUED, and used as the claim that stops a double-clicked
 * or bulk approval from sending it twice. Existing rows stay null on purpose - installs approved
 * before this shipped never got the welcome, and the admin screen offers to send it to them.
 * Released again if the send finally fails on the worker (SendFederationWelcome::failed()).
 *
 * welcomed_email: the address it went to, so the admin screen can say when the operator has since
 * given a different one - a mistyped address otherwise never gets the steps.
 *
 * locale: the language of the admin who switched sharing on, sent at registration, so the
 * welcome quotes the app's own labels in the language the operator sees them in. Null means
 * the install predates the field, and the mail falls back to the app's fallback locale.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('federated_instances', function (Blueprint $table) {
            // No ->after(): column order is cosmetic.
            $table->timestamp('welcomed_at')->nullable();
            $table->string('welcomed_email')->nullable();
            $table->string('locale', 8)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('federated_instances', function (Blueprint $table) {
            $table->dropColumn(['welcomed_at', 'welcomed_email', 'locale']);
        });
    }
};
