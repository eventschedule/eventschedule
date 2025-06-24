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
        Schema::table('users', function (Blueprint $table) {
            $table->string('payment_url')->nullable();
            $table->string('payment_secret')->nullable();
        });

        if (config('database.default') !== 'sqlite') {
            DB::statement("ALTER TABLE events MODIFY COLUMN payment_method ENUM('cash', 'stripe', 'invoiceninja', 'payment_url') DEFAULT 'cash'");
            DB::statement("ALTER TABLE sales MODIFY COLUMN payment_method ENUM('cash', 'stripe', 'invoiceninja', 'payment_url') DEFAULT 'cash'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['payment_url', 'payment_secret']);
        });

        if (config('database.default') !== 'sqlite') {
            DB::statement("ALTER TABLE events MODIFY COLUMN payment_method ENUM('cash', 'stripe', 'invoiceninja') DEFAULT 'cash'");
            DB::statement("ALTER TABLE sales MODIFY COLUMN payment_method ENUM('cash', 'stripe', 'invoiceninja') DEFAULT 'cash'");
        }
    }
};
