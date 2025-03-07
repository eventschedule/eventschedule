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
        Schema::table('events', function (Blueprint $table) {
            $table->string('slug')->nullable()->index();
        });

        DB::table('events')->get()->each(function ($event) {
            DB::table('events')
                ->where('id', $event->id)
                ->update([
                    'slug' => \Str::slug($event->name),
                ]);
        });

        Schema::table('events', function (Blueprint $table) {
            $table->string('slug')->nullable(false)->change();
        });

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
        Schema::table('events', function (Blueprint $table) {
            $table->dropIndex(['slug']);
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('slug');
        });

        Schema::table('sale_tickets', function (Blueprint $table) {
            $table->dropColumn('quantity');
        });
    }
};
