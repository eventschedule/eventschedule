<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Whether the buyer had granted the marketing cookie category when they checked out. The Meta
     * Conversions API is told about a sale only when this is true (MetaAdsService::
     * sendSaleConversion): it sends a hash of the buyer's email to Meta, which is advertising
     * measurement and needs the same consent as the browser Pixel. Null for every sale made
     * before it was recorded, which counts as no.
     */
    public function up(): void
    {
        if (Schema::hasColumn('sales', 'ad_consent')) {
            return;
        }

        Schema::table('sales', function (Blueprint $table) {
            $table->boolean('ad_consent')->nullable();
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('sales', 'ad_consent')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->dropColumn('ad_consent');
            });
        }
    }
};
