<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->text('custom_value9')->nullable();
            $table->text('custom_value10')->nullable();
        });

        Schema::table('sale_tickets', function (Blueprint $table) {
            $table->text('custom_value9')->nullable();
            $table->text('custom_value10')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['custom_value9', 'custom_value10']);
        });

        Schema::table('sale_tickets', function (Blueprint $table) {
            $table->dropColumn(['custom_value9', 'custom_value10']);
        });
    }
};
