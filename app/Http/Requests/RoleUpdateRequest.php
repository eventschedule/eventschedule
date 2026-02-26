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
    }

    public function rules(): array
    {
        $role = Role::subdomain(request()->subdomain)->firstOrFail();

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => array_merge(
                ['required', 'string', 'email', 'max:255'],
                config('app.hosted') ? [new NoFakeEmail] : []
            ),
            'new_subdomain' => array_merge(
                is_demo_mode() ? [] : ['required'],
                ['string', 'max:50']
            ),
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^\+[1-9]\d{1,14}$/'],
            'custom_domain' => [
                'nullable', 'string', 'url', 'max:255',
                Rule::unique('roles', 'custom_domain')->ignore($role->id),
                function ($attribute, $value, $fail) {
                    if ($value && str_contains(parse_url($value, PHP_URL_HOST) ?? '', 'eventschedule.com')) {
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
            'font_family' => ['nullable', 'string', 'max:100'],
            'event_custom_fields' => ['nullable', 'array', 'max:8'],
            'event_custom_fields.*.name' => ['required_with:event_custom_fields', 'string', 'max:100'],
            'event_custom_fields.*.type' => ['required_with:event_custom_fields', 'string', 'in:string,multiline_string,switch,date,dropdown'],
            'event_custom_fields.*.options' => ['nullable', 'string', 'max:500'],
            'event_custom_fields.*.required' => ['nullable'],
            'event_custom_fields.*.ai_prompt' => ['nullable', 'string', 'max:500'],
            'short_description' => ['nullable', 'string', 'max:200'],
            'slug_pattern' => ['nullable', 'string', 'max:500'],
            'event_layout' => ['nullable', 'string', 'in:calendar,list'],
            'direct_registration' => ['nullable', 'boolean'],
            'first_day_of_week' => ['nullable', 'integer', 'min:0', 'max:6'],
        ];
    }
}
