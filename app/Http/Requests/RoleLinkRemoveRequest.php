<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RoleLinkRemoveRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'remove_link_type' => ['required', 'in:social_links,payment_links,youtube_links'],
            'remove_link' => ['required', 'string', 'max:1000'],
        ];
    }
}
