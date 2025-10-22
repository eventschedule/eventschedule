<?php

namespace App\Http\Requests;

use App\Models\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Rules\NoFakeEmail;

class RoleCreateRequest extends FormRequest
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
                ['required', 'string', 'email', 'max:255'],
                config('app.hosted') ? [new NoFakeEmail] : []
            ),
            //'subdomain' => ['required', 'string', 'max:255', Rule::unique(Role::class)],
            'custom_domain' => ['nullable', 'string', 'url', 'max:255'],
            'profile_image_id' => ['nullable', 'integer', 'exists:images,id'],
            'background_image_id' => ['nullable', 'integer', 'exists:images,id'],
            'header_image_id' => ['nullable', 'integer', 'exists:images,id'],
            'background_image' => ['nullable', 'string', 'max:255'],
            'contacts' => ['nullable', 'array'],
            'contacts.*.name' => ['nullable', 'string', 'max:255'],
            'contacts.*.email' => array_merge(
                ['nullable', 'string', 'email', 'max:255'],
                config('app.hosted') ? [new NoFakeEmail] : []
            ),
            'contacts.*.phone' => ['nullable', 'string', 'max:255'],
        ];
    }
}
