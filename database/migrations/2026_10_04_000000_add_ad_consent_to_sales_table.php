<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Whether the buyer had granted the marketing cookie category when they checked out. The Meta
     * Conversions API is told about a sale only when this is true (MetaAdsService::
     * sendSaleConversion): it sends a hash of the buyer's email to Meta, which is advertising
     * measurement and needs the same consent as the browser Pixel. Null for every sale made
     * before it was recorded, which counts as no.
     *
     * lock_wait_timeout is short, as in the migration after this one, so a busy sales table makes
     * the deploy fail and retry rather than queue every checkout behind the ALTER.
     */
    public function up(): void
    {
        if (Schema::hasColumn('sales', 'ad_consent')) {
            return;
        }

        $previous = DB::selectOne('SELECT @@SESSION.lock_wait_timeout AS value')->value;
        DB::statement('SET SESSION lock_wait_timeout = 10');

        try {
            Schema::table('sales', function (Blueprint $table) {
                $table->boolean('ad_consent')->nullable();
            });
        } finally {
            DB::statement('SET SESSION lock_wait_timeout = '.(int) $previous);
        }
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
