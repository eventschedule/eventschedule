<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Why an organizer came to sign up, from the link they pressed (arrival_intent_from_session()):
 * `sell` from a ticketing page, `pro` / `enterprise` from a plan's button on /pricing, `guest_page`
 * from the "Create your own" card on a schedule's public page. Null for every other door. The
 * growth export reads it (signups.arrival_intent) to judge the seller pages on the sellers they bring.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'arrival_intent')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('arrival_intent', 16)->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'arrival_intent')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('arrival_intent');
            });
        }
    }
};
