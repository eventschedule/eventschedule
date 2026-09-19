<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The address a push claims, when it is not the one on record.
 *
 * ApiFederationController::authenticateInstance() used to compare the reported site_url
 * against the record, raise flagged_at and then throw the reported value away - so the
 * admin screen asked an operator to confirm an address it could not show them, and
 * nothing anywhere could adopt it. Keeping it is what makes the flag resolvable.
 *
 * Written by the push path only. A flag with this column still null came from
 * register(), which rewrites site_url itself and sends the instance back to pending,
 * and that is how the two flag sources stay distinguishable.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('federated_instances', function (Blueprint $table) {
            $table->string('reported_site_url')->nullable()->after('site_url');
        });
    }

    public function down(): void
    {
        Schema::table('federated_instances', function (Blueprint $table) {
            $table->dropColumn('reported_site_url');
        });
    }
};
