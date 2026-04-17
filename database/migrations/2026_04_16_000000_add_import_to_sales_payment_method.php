<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE sales MODIFY COLUMN payment_method ENUM('cash', 'stripe', 'invoiceninja', 'payment_url', 'rsvp', 'import') DEFAULT 'cash'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE sales MODIFY COLUMN payment_method ENUM('cash', 'stripe', 'invoiceninja', 'payment_url', 'rsvp') DEFAULT 'cash'");
    }
};
