<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Installment plans are configured per EVENT, not per ticket.
     *
     * The split happens on the post-discount order total, the card mandate is one mandate, and
     * the Stripe minimum-charge guard is one guard - so a per-ticket flag would have had states
     * that silently do nothing (enable it on "Full course" but not "Single tasting", a buyer
     * picks one of each, and the option vanishes with no explanation). It also keeps the column
     * out of Ticket::toClonePayload() and the twenty-odd other ticket-field copy sites: an event
     * column propagates through clone and templates automatically, because
     * EventRepo::buildClonePayload() loops $event->getFillable().
     */
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->boolean('installments_enabled')->default(false);
            $table->unsignedTinyInteger('installment_count')->nullable();

            // The final payment must clear this many days before the event starts. Defaulting to
            // 0 would let the last charge land the day before the doors open (or, on a recurring
            // event bought late, after them), leaving no time to chase a decline before the buyer
            // is refused at the door. 14 is the default and 7 the enforced floor.
            $table->unsignedSmallInteger('installment_final_days_before')->default(14);

            // Covers the real per-ticket motivation - "offer it on the EUR 900 course, not the
            // EUR 40 tasting" - with one field instead of per-ticket combinatorics.
            $table->decimal('installment_min_order_amount', 13, 3)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn([
                'installments_enabled',
                'installment_count',
                'installment_final_days_before',
                'installment_min_order_amount',
            ]);
        });
    }
};
