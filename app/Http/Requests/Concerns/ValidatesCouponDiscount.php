<?php

namespace App\Http\Requests\Concerns;

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
     */
    protected function couponDiscountRules(?string $type): array
    {
        return [
            'coupon_discount_type' => ['nullable', 'in:percentage,fixed'],
            'coupon_discount' => ['nullable', 'numeric', 'min:0',
                $type === 'percentage' ? 'max:100' : 'max:99999999'],
        ];
    }
}
