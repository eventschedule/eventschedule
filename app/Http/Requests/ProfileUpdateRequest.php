<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Rules\NoFakeEmail;
use App\Rules\UsableTimezone;
use App\Utils\TimezoneUtils;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Store the listed name for a backward-compat alias (Asia/Calcutta -> Asia/Kolkata): every
     * schedule this user creates afterwards copies it, and the schedule form's rule and select
     * are both built from the listed names.
     */
    protected function prepareForValidation(): void
    {
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
            'email' => array_merge(
                ['required', 'string', 'email', 'max:255', Rule::unique(User::class)->ignore($this->user()->id)],
                config('app.hosted') ? [new NoFakeEmail] : []
            ),
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^\+[1-9]\d{1,14}$/', Rule::unique(User::class)->ignore($this->user()->id)],
            'timezone' => ['required', 'string', 'max:255', new UsableTimezone],
            'language_code' => ['required', 'string', 'in:'.implode(',', array_keys(config('app.supported_languages', ['en' => 'english'])))],
            'profile_image' => ['image', 'max:2500'],
            'use_24_hour_time' => ['nullable', 'boolean'],
            'default_role_id' => ['nullable', 'integer', 'exists:roles,id'],
        ];
    }
}
