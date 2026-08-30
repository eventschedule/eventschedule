<?php

namespace App\Http\Requests\Concerns;

use App\Models\Event;

trait ValidatesCouponDiscount
{
    /**
     * Rules for the external-mode coupon discount.
     *
     * The value is display-only - we never redeem it - but the percentage branch still has
     * to be bounded, because "150% off" would render on the event page as fact.
     *
     * Shared by the two event FormRequests and the two AI-import endpoints. The import
     * endpoints take a bare Request and validate almost nothing, so without this a
     * non-numeric value would reach the decimal column and blow up as a QueryException.
     *
     * $storedType is what the row already says, and only an update can supply it. A partial
     * update may send the amount and omit the type, and saveEvent()'s fill() leaves the stored
     * type untouched - so the value is rendered under THAT type, not under whatever this
     * request did or did not say. Keying the ceiling off the request alone let
     * `coupon_discount: 150` through against a row still marked 'percentage'.
     */
    protected function couponDiscountRules(?string $type, ?string $storedType = null): array
    {
        $effective = $type ?: ($storedType ?: Event::DEFAULT_COUPON_DISCOUNT_TYPE);

        return [
            'coupon_discount_type' => ['nullable', 'in:percentage,fixed'],
            'coupon_discount' => ['nullable', 'numeric', 'min:0',
                $effective === 'percentage' ? 'max:100' : 'max:99999999'],
        ];
    }
}
