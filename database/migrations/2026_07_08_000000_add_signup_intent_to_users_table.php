<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Why the account was created: organizer, follow, request, fan, claim,
            // ticket, subscriber, api or team. NULL = created before intent tracking
            // (treated as organizer in the onboarding funnel).
            $table->string('signup_intent', 20)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('signup_intent');
        });
    }
};
