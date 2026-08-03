<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Groups the sale rows a buyer paid for in ONE checkout that spanned several events.
     *
     * Deliberately separate from group_id, which already means "one row per named guest within a
     * single event". A leg of a multi-event order can itself use individual tickets, so the two
     * levels have to be expressible at once.
     *
     * Every row in an order carries order_id - leg primaries and guest rows alike - so the status
     * cascades stay a single flat update rather than a group cascade nested inside an order
     * cascade. Sale::$cascadingGroup is one process-global flag and would silently swallow the
     * inner one.
     *
     * Anchored on group_id rather than a later column: this migration is dated before the
     * 2026_08_10/11 sales migrations and would fail a fresh migrate if it referenced theirs.
     */
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->unsignedBigInteger('order_id')->nullable()->after('group_id');
            $table->index('order_id');
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropIndex(['order_id']);
            $table->dropColumn('order_id');
        });
    }
};
