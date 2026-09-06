<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesCouponDiscount;
use App\Http\Requests\Concerns\ValidatesEventCustomFields;
use App\Http\Requests\Concerns\ValidatesVenueFields;
use App\Models\Event;
use App\Utils\UrlUtils;
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
        ], $this->couponDiscountRules($this->input('coupon_discount_type'), $this->storedCouponDiscountType()),
            $this->venueFieldRules(), $this->eventCustomFieldRules());
    }

    /**
     * The type already stored on the row, which is what an omitted coupon_discount_type
     * leaves in place - see couponDiscountRules(). A value() rather than a model: this runs
     * on every event update and only the one column is wanted.
     */
    private function storedCouponDiscountType(): ?string
    {
        $id = UrlUtils::decodeId($this->route('hash'));

        return $id ? Event::whereKey($id)->value('coupon_discount_type') : null;
    }

    /**
     * An owner-typed slug must not silently collide with one of their own events.
     *
     * EventRepo::uniqueSlugFor() suffixes a GENERATED slug, but doing that to a typed one would
     * hand back an address the owner did not ask for - SocialShortLinkTest records the same
     * position for short links ("a suffixed address is not one an owner would print"). So this
     * rejects instead.
     *
     * Only a CHANGED slug is checked, mirroring RoleUpdateRequest::validateShortLinkSlugs(): an
     * event that has quietly carried a duplicate for months must not start failing every unrelated
     * save because of it.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $typed = \App\Utils\SlugUtils::slugOrRomanize((string) $this->input('slug'));

            if (! $this->filled('slug') || $typed === '') {
                return;
            }

            $id = UrlUtils::decodeId($this->route('hash'));
            $event = $id ? Event::find($id) : null;

            if (! $event || $typed === $event->slug) {
                return;
            }

            $roleIds = $event->roles()->pluck('roles.id')->all();
            if ($event->creator_role_id) {
                $roleIds[] = $event->creator_role_id;
            }

            if (! $roleIds) {
                return;
            }

            $clash = Event::where('slug', $typed)
                ->where('id', '!=', $event->id)
                ->where(function ($q) use ($roleIds) {
                    $q->whereIn('creator_role_id', $roleIds)
                        ->orWhereHas('roles', fn ($r) => $r->whereIn('roles.id', $roleIds));
                })
                ->exists();

            if ($clash) {
                $validator->errors()->add('slug', __('messages.event_slug_taken'));
            }
        });
    }

    public function attributes(): array
    {
        return $this->eventCustomFieldAttributes();
    }
}
