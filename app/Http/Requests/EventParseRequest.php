<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EventParseRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'event_details' => ['nullable', 'string', 'max:10000'],
            'details_image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,gif,webp', 'max:10240'],
        ];
    }
}
