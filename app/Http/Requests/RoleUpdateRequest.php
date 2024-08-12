<?php

namespace App\Http\Requests;

use App\Models\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Utils\UrlUtils;
use App\Rules\NoEmailAlias;
use App\Rules\NoFakeEmail;
use App\Rules\SquareImage;

class RoleUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        $role = Role::subdomain(request()->subdomain)->firstOrFail();

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique(Role::class)->ignore($role->id), new NoEmailAlias, new NoFakeEmail],
            'subdomain' => ['required', 'string', 'max:255', Rule::unique(Role::class)->ignore($this->route('subdomain'), 'subdomain')],
            'profile_image' => ['image', 'max:2500', new SquareImage],
            'background_image_url' => ['image', 'max:2500'],
        ];
    }
}
