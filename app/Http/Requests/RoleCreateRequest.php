<?php

namespace App\Http\Requests;

use App\Models\Role;
use App\Rules\NoFakeEmail;
use App\Rules\SquareImage;
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
            'timezone' => ['required', 'timezone'],
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
            'translation_enabled' => ['nullable', 'boolean'],
            'translation_language_code' => ['nullable', 'string', 'in:'.implode(',', array_keys(config('app.supported_languages')))],
        ];
    }
}
