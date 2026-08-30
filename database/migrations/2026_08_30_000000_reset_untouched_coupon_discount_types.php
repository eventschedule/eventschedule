<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Clear the coupon discount type off rows that never chose one.
     *
     * The type select on the event form sits in a v-show div, not v-if, so it is in the DOM
     * and submitted on every save - including ticketed and RSVP events, where the field is
     * invisible. Between the column shipping and the default flipping to a fixed amount, that
     * stamped 'percentage' onto any event saved for an unrelated reason, and those rows would
     * keep opening on % instead of picking up the new default.
     *
     * Only rows with no discount value are touched. The type is unread when the value is null
     * (Event::getFormattedCouponDiscountAttribute short-circuits on it), so this cannot change
     * what any event displays. The whereNotNull keeps the write to the rows actually stamped
     * rather than making this a full-table update.
     */
    public function up(): void
    {
        DB::table('events')
            ->whereNull('coupon_discount')
            ->whereNotNull('coupon_discount_type')
            ->update(['coupon_discount_type' => null]);
    }

    public function down(): void
    {
        // Nothing to restore: the values cleared here were never chosen by anyone, and the
        // rows they sat on have no discount for the type to describe.
    }
};
