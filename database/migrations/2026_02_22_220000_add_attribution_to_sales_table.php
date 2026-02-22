<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->string('utm_source', 255)->nullable()->after('payment_amount');
            $table->string('utm_medium', 255)->nullable()->after('utm_source');
            $table->string('utm_campaign', 255)->nullable()->after('utm_medium');
            $table->unsignedBigInteger('boost_campaign_id')->nullable()->after('utm_campaign');

            $table->foreign('boost_campaign_id')
                ->references('id')
                ->on('boost_campaigns')
                ->nullOnDelete();

            $table->index('boost_campaign_id');
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropForeign(['boost_campaign_id']);
            $table->dropColumn(['utm_source', 'utm_medium', 'utm_campaign', 'boost_campaign_id']);
        });
    }
};
