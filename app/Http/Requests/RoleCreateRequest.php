<?php

namespace App\Http\Requests;

use App\Models\Role;
use App\Rules\NoFakeEmail;
use App\Rules\SquareImage;
use App\Rules\UsableTimezone;
use App\Utils\TimezoneUtils;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RoleCreateRequest extends FormRequest
{
    /**
     * Unwrap a pasted link shim before max:255 measures it. has(), never unconditional: store()
     * fills from $request->all(), so merging a value for an absent key would write a null.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('website')) {
            $this->merge(['website' => UrlUtils::normalizeWebsiteUrl($this->input('website'))]);
        }

        // The first-run form posts the user's own timezone, which the browser reported at sign-up
        // and may be a backward-compat alias (Chrome: Asia/Calcutta). Store the listed name so the
        // schedule's timezone <select> has an option for it. Junk is left for the rule to refuse.
        if (is_string($this->input('timezone')) && ($timezone = TimezoneUtils::canonicalize($this->input('timezone')))) {
            $this->merge(['timezone' => $timezone]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            // A schedule must have a timezone: event times are captured and displayed in it, so a
            // null timezone makes every event's time ambiguous.
            // UsableTimezone, not the framework rule: prepareForValidation() maps the aliases it knows,
            // and a usable zone with no listed name (Etc/GMT-3) must still save rather than strand
            // the user on a field the form renders back as its own option.
            'timezone' => ['required', new UsableTimezone],
            // store() fills from $request->all() and `type` is fillable, so without this a POST
            // could create a schedule of any type at all.
            'type' => ['required', 'string', 'in:talent,venue,curator'],
            // A venue's address was required only by the browser; the column is a varchar(255).
            'address1' => ['required_if:type,venue', 'nullable', 'string', 'max:255'],
            'email' => array_merge(
                ['required', 'string', 'email', 'max:255'],
                config('app.hosted') ? [new NoFakeEmail] : []
            ),
            // 'subdomain' => ['required', 'string', 'max:255', Rule::unique(Role::class)],
            // string, not url - a scheme-less "example.com" is a legitimate stored value.
            'website' => ['nullable', 'string', 'max:255'],
            'custom_domain' => ['nullable', 'string', 'url', 'max:255'],
            'profile_image' => ['image', 'max:2500', new SquareImage],
            'background_image_url' => ['image', 'max:2500'],
            'header_image_url' => ['image', 'max:2500'],
            'custom_css' => ['nullable', 'string', 'max:10000'],
            // Must be a plain hex color: it is interpolated into Vue :style expressions on
            // guest-facing pages, which the runtime compiler evaluates as JS (CSTI otherwise).
            'accent_color' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'header_style' => ['nullable', 'string', 'in:banner,compact'],
            'list_animation' => ['nullable', 'string', Rule::in(Role::LIST_ANIMATIONS)],
            'translation_enabled' => ['nullable', 'boolean'],
            'translation_language_code' => ['nullable', 'string', 'in:'.implode(',', array_keys(config('app.supported_languages')))],
            // store() fills from $request->all(), so these need rules here too: the create page
            // renders both toggles, and a junk value would otherwise reach a NOT NULL column.
            'show_subscribe_panel' => ['sometimes', 'boolean'],
            'show_sponsors' => ['sometimes', 'boolean'],
            'show_venues_map' => ['sometimes', 'boolean'],
            'venues_map_open' => ['sometimes', 'boolean'],
            'show_event_interest' => ['sometimes', 'boolean'],
        ];
    }
}
