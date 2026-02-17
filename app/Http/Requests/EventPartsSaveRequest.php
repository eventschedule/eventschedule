<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EventPartsSaveRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'event_id' => ['required', 'string'],
            'parts' => ['required', 'array'],
            'parts.*.name' => ['required', 'string', 'max:255'],
            'parts.*.description' => ['nullable', 'string', 'max:1000'],
            'parts.*.start_time' => ['nullable', 'string', 'max:10'],
            'parts.*.end_time' => ['nullable', 'string', 'max:10'],
        ];
    }
}
