<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RoleLinkUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'link_type' => ['required', 'in:social_links,payment_links,youtube_links'],
            'link' => ['required', 'string', 'max:1000'],
        ];
    }
}
