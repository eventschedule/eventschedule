<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('boost_campaigns', function (Blueprint $table) {
            $table->unique('stripe_payment_intent_id');
        });
    }

    public function down(): void
    {
        Schema::table('boost_campaigns', function (Blueprint $table) {
            $table->dropUnique(['stripe_payment_intent_id']);
        });
    }
};
