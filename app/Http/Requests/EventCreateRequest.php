<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesCouponDiscount;
use App\Http\Requests\Concerns\ValidatesEventCustomFields;
use App\Http\Requests\Concerns\ValidatesVenueFields;
use Illuminate\Foundation\Http\FormRequest;

class EventCreateRequest extends FormRequest
{
    use ValidatesCouponDiscount, ValidatesEventCustomFields, ValidatesVenueFields;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return array_merge([
            // The dropdown constrains this client-side, but the stored value must be a real
            // registry key: an arbitrary string would fail the MySQL enum write anyway, and a
            // provenance marker like 'rsvp' must never be selectable. Currency fit is deliberately
            // NOT validated here - a stored method can legitimately outlive a currency change, and
            // the checkout-time guards are the authority on whether it can actually charge.
            'payment_method' => ['nullable', 'string', 'in:'.implode(',', payment_gateways()->selectableKeys())],
            'name' => ['required', 'string', 'max:255'],

            'flyer_image_url' => ['image', 'max:2500'],

            'addons.*.url' => ['nullable', 'url', 'max:2000'],
            'addon_image_data.*' => ['nullable', 'string', 'max:3500000'],

            'venue_email' => ['nullable', 'email', 'max:255'],
            'venue_phone' => ['nullable', 'string', 'max:20', 'regex:/^\+[1-9]\d{1,14}$/'],
            'members.*.phone' => ['nullable', 'string', 'max:20', 'regex:/^\+[1-9]\d{1,14}$/'],
        ], $this->couponDiscountRules($this->input('coupon_discount_type')),
            $this->venueFieldRules(), $this->eventCustomFieldRules());
    }

    public function attributes(): array
    {
        return $this->eventCustomFieldAttributes();
    }
}
