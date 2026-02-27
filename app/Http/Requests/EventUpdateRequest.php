<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EventUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'flyer_image_url' => ['image', 'max:2500'],
            'slug' => ['nullable', 'string', 'max:255'],

            'promo_codes' => ['nullable', 'array'],
            'promo_codes.*.code' => ['required', 'string', 'max:50'],
            'promo_codes.*.type' => ['required', 'in:percentage,fixed'],
            'promo_codes.*.value' => ['required', 'numeric', 'min:0.01'],
            'promo_codes.*.max_uses' => ['nullable', 'integer', 'min:1'],
            'promo_codes.*.expires_at' => ['nullable', 'date'],
        ];
    }
}
