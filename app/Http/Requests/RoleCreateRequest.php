<?php

namespace App\Http\Requests;

use App\Models\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique(Role::class)],
            //'subdomain' => ['required', 'string', 'max:255', Rule::unique(Role::class)],
            'profile_image' => ['image', 'max:2500'],
            'background_image_url' => ['image', 'max:2500'],
        ];
    }
}
