<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EventCommentSubmitRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'comment' => ['required', 'string', 'max:1000'],
            'event_part_id' => ['nullable', 'string'],
            'event_date' => ['nullable', 'string', 'date_format:Y-m-d'],
        ];
    }
}
