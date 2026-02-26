<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('analytics_events_daily', function (Blueprint $table) {
            $table->unsignedInteger('promo_sales_count')->default(0)->after('revenue');
            $table->decimal('promo_discount_total', 13, 3)->default(0)->after('promo_sales_count');
        });
    }

    public function down(): void
    {
        Schema::table('analytics_events_daily', function (Blueprint $table) {
            $table->dropColumn(['promo_sales_count', 'promo_discount_total']);
        });
    }
};
