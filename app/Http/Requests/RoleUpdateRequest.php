<?php

namespace App\Http\Requests;

use App\Models\Role;
use App\Rules\NoFakeEmail;
use App\Rules\SquareImage;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RoleUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    protected function prepareForValidation(): void
    {
        if (! $this->input('custom_domain')) {
            $this->merge(['custom_domain_mode' => null]);
        }

        // '' and null must not both be storable: Stay22Service::resolveAid() reads null as
        // "fall back to the instance operator's ID", and an empty string would compare
        // differently everywhere that distinction is made.
        if ($this->has('stay22_aid')) {
            $aid = trim((string) $this->input('stay22_aid'));
            $this->merge(['stay22_aid' => $aid === '' ? null : $aid]);
        }
    }

    public function rules(): array
    {
        $role = Role::subdomain(request()->subdomain)->firstOrFail();

        // Default-category id must be one of this schedule's enabled categories,
        // unless the column is null (in which case the system defaults apply).
        $allowedDefaultIds = collect($role->getEventCategories())->pluck('id')->all();

        return [
            'name' => ['required', 'string', 'max:255'],
            // A schedule must have a timezone (event times are captured and displayed in it). The
            // field is disabled in demo mode, so it is only enforced outside demo.
            'timezone' => array_merge(
                is_demo_mode() ? [] : ['required'],
                ['timezone']
            ),
            'email' => array_merge(
                ['required', 'string', 'email', 'max:255'],
                config('app.hosted') ? [new NoFakeEmail] : []
            ),
            'new_subdomain' => array_merge(
                is_demo_mode() ? [] : ['required'],
                ['string', 'max:50']
            ),
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^\+[1-9]\d{1,14}$/'],
            // Curator event sources. Bounded here rather than trimmed in the controller so
            // an over-long list is refused with a message instead of coming back silently
            // short - one source can move thousands of event links.
            'source_schedules' => ['nullable', 'array', 'max:'.(int) config('usage.curator_source_limit', 100)],
            'source_schedules.*' => ['nullable', 'string', 'max:255'],
            // Typed as string because saveEventSources hands each element to
            // UrlUtils::decodeId(), whose Sqids::decode(string) type hint fatals on an array.
            'source_groups' => ['nullable', 'array'],
            'source_groups.*' => ['nullable', 'string', 'max:255'],
            'custom_domain' => [
                'nullable', 'string', 'url', 'max:255',
                Rule::unique('roles', 'custom_domain')->ignore($role->id),
                function ($attribute, $value, $fail) {
                    if ($value && Role::isReservedCustomDomainHost(parse_url($value, PHP_URL_HOST))) {
                        $fail(__('messages.invalid_custom_domain'));
                    }
                },
                function ($attribute, $value, $fail) use ($role) {
                    if ($value) {
                        $host = parse_url($value, PHP_URL_HOST);
                        if ($host && Role::where('custom_domain_host', $host)->where('id', '!=', $role->id)->exists()) {
                            $fail(__('messages.custom_domain_already_taken'));
                        }
                    }
                },
            ],
            'custom_domain_mode' => [
                'nullable', 'string', 'in:redirect,direct',
                function ($attribute, $value, $fail) {
                    if ($value && ! $this->input('custom_domain')) {
                        $fail(__('messages.custom_domain_mode_requires_domain'));
                    }
                },
            ],
            'profile_image' => ['image', 'max:2500', new SquareImage],
            'background_image_url' => ['image', 'max:2500'],
            'header_image_url' => ['image', 'max:2500'],
            'email_settings.host' => ['nullable', 'string', 'max:255'],
            'email_settings.port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'email_settings.encryption' => ['nullable', 'string', 'in:tls,ssl'],
            'email_settings.username' => ['nullable', 'string', 'max:255'],
            'email_settings.password' => ['nullable', 'string', 'max:255'],
            'email_settings.from_address' => ['nullable', 'email', 'max:255'],
            'email_settings.from_name' => ['nullable', 'string', 'max:255'],
            'custom_css' => ['nullable', 'string', 'max:10000'],
            // Must be a plain hex color: it is interpolated into Vue :style expressions on
            // guest-facing pages, which the runtime compiler evaluates as JS (CSTI otherwise).
            'accent_color' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'font_family' => ['nullable', 'string', 'max:100'],
            'event_custom_fields' => ['nullable', 'array', 'max:10'],
            'event_custom_fields.*.name' => ['required_with:event_custom_fields', 'string', 'max:100'],
            'event_custom_fields.*.type' => ['required_with:event_custom_fields', 'string', 'in:string,multiline_string,switch,date,dropdown,multiselect'],
            'event_custom_fields.*.options' => ['nullable', 'string', 'max:500'],
            'event_custom_fields.*.required' => ['nullable'],
            'event_custom_fields.*.private' => ['nullable'],
            'event_custom_fields.*.show_on_request' => ['nullable'],
            'event_custom_fields.*.regex' => ['nullable', 'string', 'max:200'],
            'event_custom_fields.*.regex_hint' => ['nullable', 'string', 'max:200'],
            'event_custom_fields.*.ai_prompt' => ['nullable', 'string', 'max:500'],
            'short_description' => ['nullable', 'string', 'max:200'],
            'banner_enabled' => ['nullable', 'boolean'],
            'banner_on_event_pages' => ['nullable', 'boolean'],
            'banner_message' => ['nullable', 'string', 'max:500'],
            'banner_message_en' => ['nullable', 'string', 'max:500'],
            'slug_pattern' => ['nullable', 'string', 'max:500'],
            'event_layout' => ['nullable', 'string', 'in:calendar,list'],
            'header_style' => ['nullable', 'string', 'in:banner,compact'],
            // Auto-translation target language. Nullable (not submitted in demo mode, where the
            // selects are disabled); the controller maps the "offer translation" toggle to this
            // value, so a valid non-null code is always what actually gets persisted.
            'translation_enabled' => ['nullable', 'boolean'],
            'translation_language_code' => ['nullable', 'string', 'in:'.implode(',', array_keys(config('app.supported_languages')))],
            'direct_registration' => ['nullable', 'boolean'],
            'hide_past_events' => ['nullable', 'boolean'],
            'promotions_opt_out' => ['nullable', 'boolean'],
            'stay22_enabled' => ['nullable', 'boolean'],
            // Interpolated into the embed URL as the FIRST query parameter, so a value
            // containing '&' or '#' could append arbitrary Stay22 parameters or truncate the
            // query. This is the primary control; Stay22Service::sanitizeAid() re-checks at
            // read time because the operator-level Setting bypasses this rule entirely.
            'stay22_aid' => ['nullable', 'string', 'max:64', 'regex:/^[A-Za-z0-9._-]+$/'],
            'federation_enabled' => ['nullable', 'boolean'],
            'draft_events_default' => ['nullable', 'boolean'],
            'default_event_visibility' => ['nullable', 'string', 'in:public,draft,internal,unlisted'],
            'hide_videos' => ['nullable', 'boolean'],
            'show_accessibility_widget' => ['nullable', 'boolean'],
            'default_category_id' => ['nullable', 'integer', $allowedDefaultIds ? 'in:'.implode(',', $allowedDefaultIds) : 'in:'.implode(',', array_keys(config('app.event_categories', [])))],
            'event_categories' => ['nullable', 'array', 'max:32'],
            'event_categories.*.id' => ['required_with:event_categories', 'integer', 'min:1'],
            'event_categories.*.name' => ['required_with:event_categories', 'string', 'max:80', 'regex:/^[^<>\n\r]+$/'],
            'event_categories.*.name_en' => ['nullable', 'string', 'max:80', 'regex:/^[^<>\n\r]+$/'],
            'event_categories.*.color' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'first_day_of_week' => ['nullable', 'integer', 'min:0', 'max:6'],
            'feedback_enabled' => ['nullable', 'boolean'],
            'feedback_public' => ['nullable', 'boolean'],
            'fan_comments_enabled' => ['nullable', 'boolean'],
            'fan_photos_enabled' => ['nullable', 'boolean'],
            'fan_videos_enabled' => ['nullable', 'boolean'],
            'fan_content_require_account' => ['nullable', 'boolean'],
            'carpool_enabled' => ['nullable', 'boolean'],
            'feedback_delay_hours' => ['nullable', 'integer', 'in:1,2,6,12,24,48'],
            'gift_cards_enabled' => ['nullable', 'boolean'],
            'gift_card_amounts' => ['nullable', 'array', 'max:12'],
            'gift_card_amounts.*' => ['nullable', 'numeric', 'gt:0', 'max:99999'],
            'gift_card_currency_code' => ['nullable', 'string', 'size:3'],
            'gift_card_valid_days' => ['nullable', 'integer', 'min:1', 'max:3650'],
            'gift_card_payment_method' => ['nullable', 'in:cash,stripe,invoiceninja,payment_url'],
            'existing_sponsors' => ['nullable', 'string', 'json'],
            'sponsor_background_color' => ['nullable', 'string', 'regex:/^(transparent|#[0-9a-fA-F]{6})$/'],
            'new_sponsor_logos.*' => ['image', 'max:2500'],
            'new_sponsor_names.*' => ['nullable', 'string', 'max:100'],
            'new_sponsor_urls.*' => ['nullable', 'url', 'max:500'],
            'new_sponsor_tiers.*' => ['nullable', 'string', 'in:gold,silver,bronze'],
            'custom_labels' => ['nullable', 'array', 'max:30'],
            'custom_labels.*.value' => ['required_with:custom_labels', 'string', 'max:200'],
            'custom_labels.*.value_en' => ['nullable', 'string', 'max:200'],
            'youtube_links' => ['nullable', 'json'],
            'social_links' => ['nullable', 'json'],
            'payment_links' => ['nullable', 'json'],
            'logo_wall_order' => ['nullable', 'string', 'json', 'max:2000'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // A custom field's validation pattern is owner-authored, so reject anything PCRE
            // cannot compile here - otherwise it saves silently and is skipped at validation time,
            // leaving the owner believing the field is guarded when it is not.
            foreach ((array) $this->input('event_custom_fields') as $fieldKey => $field) {
                if (! is_array($field) || ! isset($field['regex'])) {
                    continue;
                }

                if (! \App\Utils\CustomFieldUtils::isValidPattern($field['regex'])) {
                    $validator->errors()->add(
                        "event_custom_fields.{$fieldKey}.regex",
                        __('messages.field_regex_invalid')
                    );
                }
            }
        });

        $validator->after(function ($validator) {
            $entries = $this->input('event_categories');
            if (! is_array($entries)) {
                return;
            }
            $seen = [];
            foreach ($entries as $idx => $entry) {
                if (! is_array($entry) || ! isset($entry['id'])) {
                    continue;
                }
                $id = (int) $entry['id'];
                if (isset($seen[$id])) {
                    $validator->errors()->add("event_categories.{$idx}.id", __('messages.category_ids_must_be_unique'));
                }
                $seen[$id] = true;
            }
        });
    }
}
