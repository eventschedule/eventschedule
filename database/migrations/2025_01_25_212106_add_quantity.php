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
        Schema::table('sale_tickets', function (Blueprint $table) {
            $table->integer('quantity')->default(0);
        });

        DB::table('sale_tickets')->get()->each(function ($saleTicket) {
            DB::table('sale_tickets')
                ->where('id', $saleTicket->id)
                ->update([
                    'quantity' => count(json_decode($saleTicket->seats, true))
                ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sale_tickets', function (Blueprint $table) {
            $table->dropColumn('quantity');
        });
    }
};
