<?php

namespace App\Http\Requests;

use App\Models\Role;
use App\Rules\NoFakeEmail;
use App\Rules\SquareImage;
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
            // A schedule must have a timezone: event times are captured and displayed in it, so a
            // null timezone makes every event's time ambiguous.
            'timezone' => ['required', 'timezone'],
            'email' => array_merge(
                ['required', 'string', 'email', 'max:255'],
                config('app.hosted') ? [new NoFakeEmail] : []
            ),
            // 'subdomain' => ['required', 'string', 'max:255', Rule::unique(Role::class)],
            'custom_domain' => ['nullable', 'string', 'url', 'max:255'],
            'profile_image' => ['image', 'max:2500', new SquareImage],
            'background_image_url' => ['image', 'max:2500'],
            'header_image_url' => ['image', 'max:2500'],
            'custom_css' => ['nullable', 'string', 'max:10000'],
        ];
    }
}
