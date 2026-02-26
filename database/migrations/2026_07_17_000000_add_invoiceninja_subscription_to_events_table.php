<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('invoiceninja_subscription_id')->nullable()->after('payment_method');
            $table->string('invoiceninja_subscription_url')->nullable()->after('invoiceninja_subscription_id');
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->string('invoiceninja_product_id')->nullable()->after('custom_fields');
        });

        // Normalize payment_link_v2 and payment_link_v3 to payment_link
        DB::table('users')
            ->whereIn('invoiceninja_mode', ['payment_link_v2', 'payment_link_v3'])
            ->update(['invoiceninja_mode' => 'payment_link']);
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['invoiceninja_subscription_id', 'invoiceninja_subscription_url']);
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn('invoiceninja_product_id');
        });
    }
};
