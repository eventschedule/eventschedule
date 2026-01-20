<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add custom_fields JSON config to events table
        Schema::table('events', function (Blueprint $table) {
            $table->text('custom_fields')->nullable();
        });

        // Add custom_fields JSON config to tickets table
        Schema::table('tickets', function (Blueprint $table) {
            $table->text('custom_fields')->nullable();
        });

        // Add custom_value columns to sales table for event-level values
        Schema::table('sales', function (Blueprint $table) {
            $table->text('custom_value1')->nullable();
            $table->text('custom_value2')->nullable();
            $table->text('custom_value3')->nullable();
            $table->text('custom_value4')->nullable();
            $table->text('custom_value5')->nullable();
            $table->text('custom_value6')->nullable();
            $table->text('custom_value7')->nullable();
            $table->text('custom_value8')->nullable();
        });

        // Add custom_value columns to sale_tickets table for ticket-level values
        Schema::table('sale_tickets', function (Blueprint $table) {
            $table->text('custom_value1')->nullable();
            $table->text('custom_value2')->nullable();
            $table->text('custom_value3')->nullable();
            $table->text('custom_value4')->nullable();
            $table->text('custom_value5')->nullable();
            $table->text('custom_value6')->nullable();
            $table->text('custom_value7')->nullable();
            $table->text('custom_value8')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('custom_fields');
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn('custom_fields');
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn([
                'custom_value1',
                'custom_value2',
                'custom_value3',
                'custom_value4',
                'custom_value5',
                'custom_value6',
                'custom_value7',
                'custom_value8',
            ]);
        });

        Schema::table('sale_tickets', function (Blueprint $table) {
            $table->dropColumn([
                'custom_value1',
                'custom_value2',
                'custom_value3',
                'custom_value4',
                'custom_value5',
                'custom_value6',
                'custom_value7',
                'custom_value8',
            ]);
        });
    }
};
