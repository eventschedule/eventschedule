<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            // What the coupon is worth, so the event page can say "15% off" rather than
            // making guests click through to the external platform to find out.
            $table->decimal('coupon_discount', 13, 3)->nullable()->after('coupon_code');
            // 'percentage' or 'fixed' - same vocabulary as the promo_codes rules. Null on
            // rows that predate this column, which reads as 'percentage'.
            $table->string('coupon_discount_type', 20)->nullable()->after('coupon_discount');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['coupon_discount', 'coupon_discount_type']);
        });
    }
};
