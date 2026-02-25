<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EventPhotoSubmitRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'photo' => ['required', 'image', 'max:5000', 'mimes:jpg,jpeg,png,gif,webp'],
            'event_part_id' => ['nullable', 'string'],
            'event_date' => ['nullable', 'string', 'date_format:Y-m-d'],
        ];
    }
}
