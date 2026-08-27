<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesCouponDiscount;
use App\Http\Requests\Concerns\ValidatesEventCustomFields;
use App\Http\Requests\Concerns\ValidatesVenueFields;
use Illuminate\Foundation\Http\FormRequest;

class EventUpdateRequest extends FormRequest
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
            'name' => ['sometimes', 'required', 'string', 'max:255'],

            'flyer_image_url' => ['image', 'max:2500'],

            // Each max matches its column exactly - see Event::CLAMPED_COLUMNS, which a schema
            // test holds against the live widths. Length only, deliberately not 'url': only the
            // length is wrong today, whereas a format rule would start rejecting the scheme-less
            // values that currently save. Same reasoning as roles.website in RoleUpdateRequest.
            // agenda_ai_prompt is pointedly absent: it is a hidden input carried by every event
            // save, so a rule on it would reject someone editing tickets with a page-top message
            // naming a field that is not on screen. Its ceiling is the model saving hook instead.
            'event_url' => ['nullable', 'string', 'max:500'],
            'terms_url' => ['nullable', 'string', 'max:255'],
            'coupon_code' => ['nullable', 'string', 'max:255'],
            'event_password' => ['nullable', 'string', 'max:255'],

            'slug' => ['nullable', 'string', 'max:255'],

            'promo_codes' => ['nullable', 'array'],
            'promo_codes.*.code' => ['required', 'string', 'max:50'],
            'promo_codes.*.type' => ['required', 'in:percentage,fixed'],
            'promo_codes.*.value' => ['required', 'numeric', 'min:0.01'],
            'promo_codes.*.max_uses' => ['nullable', 'integer', 'min:1'],
            'promo_codes.*.expires_at' => ['nullable', 'date'],

            'tickets.*.sales_start_at' => ['nullable', 'date'],
            'tickets.*.sales_end_at' => ['nullable', 'date'],

            'addons.*.url' => ['nullable', 'url', 'max:2000'],
            'addon_image_data.*' => ['nullable', 'string', 'max:3500000'],

            'venue_email' => ['nullable', 'email', 'max:255'],
            'venue_phone' => ['nullable', 'string', 'max:20', 'regex:/^\+[1-9]\d{1,14}$/'],
            'members.*.phone' => ['nullable', 'string', 'max:20', 'regex:/^\+[1-9]\d{1,14}$/'],

            'sponsor_mode' => ['nullable', 'string', 'in:default,none,custom'],
            'event_sponsor_logos.*' => ['image', 'max:2500'],
            'event_sponsor_names.*' => ['nullable', 'string', 'max:100'],
            'event_sponsor_urls.*' => ['nullable', 'url', 'max:500'],
            'event_sponsor_tiers.*' => ['nullable', 'string', 'in:gold,silver,bronze'],

            'existing_event_sponsors' => ['nullable', 'string', 'json'],
        ], $this->couponDiscountRules($this->input('coupon_discount_type')),
            $this->venueFieldRules(), $this->eventCustomFieldRules());
    }

    public function attributes(): array
    {
        return $this->eventCustomFieldAttributes();
    }
}
