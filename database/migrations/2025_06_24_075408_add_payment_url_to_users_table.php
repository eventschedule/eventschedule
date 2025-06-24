<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

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

        // For SQLite, add a new column with the updated constraint
        if (config('database.default') === 'sqlite') {
            // Update events table
            DB::statement('ALTER TABLE events ADD COLUMN payment_method_new varchar check ("payment_method_new" in ("cash", "stripe", "invoiceninja", "payment_url")) not null default "cash"');
            DB::statement('UPDATE events SET payment_method_new = payment_method');
            DB::statement('ALTER TABLE events DROP COLUMN payment_method');
            DB::statement('ALTER TABLE events RENAME COLUMN payment_method_new TO payment_method');
            
            DB::statement('ALTER TABLE sales ADD COLUMN payment_method_new varchar check ("payment_method_new" in ("cash", "stripe", "invoiceninja", "payment_url")) not null default "cash"');
            DB::statement('UPDATE sales SET payment_method_new = payment_method');
            DB::statement('ALTER TABLE sales DROP COLUMN payment_method');
            DB::statement('ALTER TABLE sales RENAME COLUMN payment_method_new TO payment_method');
        } else {
            // For other databases, use ALTER TABLE
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

        if (config('database.default') === 'sqlite') {
            // Revert events table
            DB::statement('ALTER TABLE events ADD COLUMN payment_method_old varchar check ("payment_method_old" in ("cash", "stripe", "invoiceninja")) not null default "cash"');
            DB::statement('UPDATE events SET payment_method_old = CASE WHEN payment_method = "payment_url" THEN "cash" ELSE payment_method END');
            DB::statement('ALTER TABLE events DROP COLUMN payment_method');
            DB::statement('ALTER TABLE events RENAME COLUMN payment_method_old TO payment_method');
            
            // Revert sales table
            DB::statement('ALTER TABLE sales ADD COLUMN payment_method_old varchar check ("payment_method_old" in ("cash", "stripe", "invoiceninja")) not null default "cash"');
            DB::statement('UPDATE sales SET payment_method_old = CASE WHEN payment_method = "payment_url" THEN "cash" ELSE payment_method END');
            DB::statement('ALTER TABLE sales DROP COLUMN payment_method');
            DB::statement('ALTER TABLE sales RENAME COLUMN payment_method_old TO payment_method');
        } else {
            // For other databases, use ALTER TABLE
            DB::statement("ALTER TABLE events MODIFY COLUMN payment_method ENUM('cash', 'stripe', 'invoiceninja') DEFAULT 'cash'");
            DB::statement("ALTER TABLE sales MODIFY COLUMN payment_method ENUM('cash', 'stripe', 'invoiceninja') DEFAULT 'cash'");
        }
    }
};
