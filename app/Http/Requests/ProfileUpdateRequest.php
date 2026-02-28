<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Rules\NoFakeEmail;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
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
            'timezone' => ['required', 'string', 'max:255'],
            'language_code' => ['required', 'string', 'in:'.implode(',', config('app.supported_languages', ['en']))],
            'profile_image' => ['image', 'max:2500'],
            'use_24_hour_time' => ['nullable', 'boolean'],
            'default_role_id' => ['nullable', 'integer', 'exists:roles,id'],
        ];
    }
}
