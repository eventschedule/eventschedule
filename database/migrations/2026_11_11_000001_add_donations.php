<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Donations at checkout (Tickets > Checkout, "Ask for a donation"): the event's ask, and what each
 * sale gave. A free ticket with a donation is how an event lets people pay what they want.
 *
 * events (well below its row limit, unlike roles): the switch, up to three suggested amounts in the
 * event's currency, whether a buyer may type their own, and the short line above the amounts.
 * sales.donation_amount: what the buyer added, already inside payment_amount, so settlement,
 * refunds and every gateway that charges the order total need nothing of their own. Nullable,
 * so the column is added in place on MySQL 8 without copying the table.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('events', 'donations_enabled')) {
            Schema::table('events', function (Blueprint $table) {
                $table->boolean('donations_enabled')->default(false);
                $table->json('donation_amounts')->nullable();
                $table->boolean('donation_custom')->default(true);
                $table->string('donation_line', 120)->nullable();
            });
        }

        if (! Schema::hasColumn('sales', 'donation_amount')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->decimal('donation_amount', 13, 3)->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn('donation_amount');
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['donations_enabled', 'donation_amounts', 'donation_custom', 'donation_line']);
        });
    }
};
