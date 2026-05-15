<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('tickets', 'volume_discount')) {
            Schema::table('tickets', function (Blueprint $table) {
                $table->json('volume_discount')->nullable()->after('custom_fields');
            });
        }

        if (! Schema::hasColumn('sales', 'volume_discount_amount')) {
            if (Schema::hasColumn('sales', 'discount_amount')) {
                Schema::table('sales', function (Blueprint $table) {
                    $table->decimal('volume_discount_amount', 10, 2)->nullable()->after('discount_amount');
                });
            } else {
                Schema::table('sales', function (Blueprint $table) {
                    $table->decimal('volume_discount_amount', 10, 2)->nullable();
                });
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('tickets', 'volume_discount')) {
            Schema::table('tickets', function (Blueprint $table) {
                $table->dropColumn('volume_discount');
            });
        }

        if (Schema::hasColumn('sales', 'volume_discount_amount')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->dropColumn('volume_discount_amount');
            });
        }
    }
};
