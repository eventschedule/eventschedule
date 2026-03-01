<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->datetime('sales_end_at')->nullable()->after('description');
        });

        // Copy each event's ticket_sales_end_at to all its non-deleted tickets
        DB::statement('
            UPDATE tickets
            JOIN events ON tickets.event_id = events.id
            SET tickets.sales_end_at = events.ticket_sales_end_at
            WHERE events.ticket_sales_end_at IS NOT NULL
            AND tickets.is_deleted = 0
        ');

        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('ticket_sales_end_at');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->datetime('ticket_sales_end_at')->nullable();
        });

        // Copy the latest sales_end_at from each event's tickets back to the event
        DB::statement('
            UPDATE events
            JOIN (
                SELECT event_id, MAX(sales_end_at) as latest_sales_end_at
                FROM tickets
                WHERE sales_end_at IS NOT NULL AND is_deleted = 0
                GROUP BY event_id
            ) t ON events.id = t.event_id
            SET events.ticket_sales_end_at = t.latest_sales_end_at
        ');

        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn('sales_end_at');
        });
    }
};
