<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->json('volume_discount')->nullable()->after('custom_fields');
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->decimal('volume_discount_amount', 10, 2)->nullable()->after('discount_amount');
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn('volume_discount');
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn('volume_discount_amount');
        });
    }
};
